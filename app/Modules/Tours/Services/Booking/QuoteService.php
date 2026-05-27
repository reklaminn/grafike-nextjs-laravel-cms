<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking;

use App\Modules\Tours\Enums\PassengerType;
use App\Modules\Tours\Exceptions\PriceNotAvailableException;
use App\Modules\Tours\Exceptions\QuoteValidationException;
use App\Modules\Tours\Models\Tour;
use App\Modules\Tours\Models\TourCabinPrice;
use App\Modules\Tours\Models\TourDate;
use App\Modules\Tours\Models\TourExtra;
use App\Modules\Tours\Models\TourPriceGroup;
use App\Modules\Tours\Services\Booking\DTOs\BookingDraft;
use App\Modules\Tours\Services\Booking\DTOs\PassengerDraft;
use App\Modules\Tours\Services\Booking\DTOs\Quote;
use App\Modules\Tours\Services\Booking\DTOs\QuoteLineItem;
use App\Modules\Tours\Services\Booking\Pricing\CalculatorFactory;
use App\Modules\Tours\Services\Booking\Pricing\DTOs\PassengerSet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Phase 1.5.b REWRITE.
 *
 * Pure (read-only) price computation for a BookingDraft using the new
 * 5-dimensional pricing model:
 *
 *   Date → TourPriceGroup (named, multi-date bundle)
 *        → TourCabinPrice  (per-cabin 6-tier matrix + calculation method)
 *        → PriceCalculator (3 strategies, dispatched by CalculatorFactory)
 *
 * Cruise vs non-cruise pricing distinction:
 *   - Cruise (tour.type=cruise) — every passenger has a `cabin_id`,
 *     passengers grouped by cabin, each cabin priced independently
 *     using its TourCabinPrice row.
 *   - Non-cruise (package / daily / ferry) — no cabin selection,
 *     all passengers priced from a single "generic" TourCabinPrice
 *     row (cabin_id NULL).
 *
 * Phase 5'te `CampaignApplier` ile entegre olacak (discount engine).
 * Bu service NEVER writes to DB — quote-preview API endpoint'i için güvenli.
 */
class QuoteService
{
    public function __construct(
        private readonly CalculatorFactory $calculatorFactory,
    ) {
    }

    public function compute(Tour $tour, TourDate $date, BookingDraft $draft): Quote
    {
        $this->validate($tour, $date, $draft);

        $currency = (string) $tour->currency;
        $lines    = [];

        // ─── 1. Find applicable TourPriceGroup ────────────────────────────────
        $priceGroup = $this->resolvePriceGroup($tour, $date, $draft);

        if ($priceGroup === null) {
            throw new PriceNotAvailableException(
                "Bu tarih için aktif fiyat grubu tanımlanmamış (Tour ID {$tour->id}, Date {$date->id}).",
                tourId: $tour->id,
                tourDateId: $date->id,
                reason: 'no_price_group',
            );
        }

        // Override currency if the price group itself has one (Phase 5 multi-currency)
        // Şu an Group seviyesinde currency yok; TourCabinPrice'tan alacağız aşağıda.

        // ─── 2. Compute passenger costs ───────────────────────────────────────
        $passengerTotal = $this->computePassengerCosts(
            $tour, $date, $priceGroup, $draft, $lines, $currency,
        );

        // ─── 3. Extras ────────────────────────────────────────────────────────
        $extrasTotal = $this->computeExtrasCosts($tour, $draft, $lines);

        // ─── 4. Totals ────────────────────────────────────────────────────────
        // Phase 5'te discount engine + tax burada uygulanır.
        $discountTotal = 0;
        $taxTotal      = 0;

        $total = max(0, $passengerTotal + $extrasTotal + $discountTotal + $taxTotal);

        // Phase 5: per-tour deposit policy.  Şimdilik full payment.
        $depositDue = $total;

        return new Quote(
            currency:          $currency,
            subtotal:          $passengerTotal,
            extrasTotal:       $extrasTotal,
            discountTotal:     $discountTotal,
            taxTotal:          $taxTotal,
            total:             $total,
            depositDueAmount:  $depositDue,
            lines:             $lines,
        );
    }

    // ─── Internals ────────────────────────────────────────────────────────────

    private function validate(Tour $tour, TourDate $date, BookingDraft $draft): void
    {
        $errors = [];

        if ($date->tour_id !== $tour->id) {
            $errors[] = "tour_date {$date->id} does not belong to tour {$tour->id}";
        }

        if ($draft->passengerCount() === 0) {
            $errors[] = 'En az bir yolcu girilmeli.';
        }

        // Cruise tours: every passenger must pick a cabin
        // Non-cruise: passengers should NOT have a cabin (data hygiene)
        foreach ($draft->passengers as $idx => $passenger) {
            if ($tour->isCruise() && $passenger->cabinTypeId === null) {
                $errors[] = 'Yolcu #' . ($idx + 1) . ' için kabin seçimi zorunlu (cruise).';
            }
            if (! $tour->isCruise() && $passenger->cabinTypeId !== null) {
                $errors[] = 'Yolcu #' . ($idx + 1) . ' için kabin seçilemez (paket/günlük tur).';
            }
        }

        $leadCount = count(array_filter(
            $draft->passengers,
            fn (PassengerDraft $p) => $p->isLead,
        ));
        if ($leadCount === 0) {
            $errors[] = 'Lead yolcu işaretlenmemiş.';
        }
        if ($leadCount > 1) {
            $errors[] = 'Birden fazla lead yolcu olamaz.';
        }

        if ($errors !== []) {
            throw new QuoteValidationException(implode(' ', $errors), $errors);
        }
    }

    /**
     * Bu tarih için aktif TourPriceGroup'u bul.  Birden fazla grup
     * varsa en yüksek sort_order'lı (öncelikli) alınır.
     *
     * Phase 5'te BookingDraft.priceGroupId opsiyonel olarak kabul edilir
     * (müşteri sepete eklerken "Erken Rezervasyon Fiyatı vs Standart"
     * arasında seçim yapar).
     */
    private function resolvePriceGroup(Tour $tour, TourDate $date, BookingDraft $draft): ?TourPriceGroup
    {
        return TourPriceGroup::query()
            ->forTour($tour->id)
            ->forDate($date->id)
            ->active()
            ->orderByDesc('sort_order')
            ->first();
    }

    /**
     * Cruise tours: cabin'lere göre grup-by, her cabin için ayrı total
     * hesabı.  Non-cruise: tek "generic" TourCabinPrice satırı tüm
     * passenger'lara uygulanır.
     */
    private function computePassengerCosts(
        Tour $tour,
        TourDate $date,
        TourPriceGroup $priceGroup,
        BookingDraft $draft,
        array &$lines,
        string $currency,
    ): int {
        $total = 0;

        if ($tour->isCruise()) {
            // Cruise: passengers grouped by cabin_id
            $byCabin = collect($draft->passengers)
                ->groupBy(fn (PassengerDraft $p) => (int) $p->cabinTypeId);

            foreach ($byCabin as $cabinId => $passengers) {
                $cabinPrice = $priceGroup->priceForCabin($cabinId);
                if ($cabinPrice === null) {
                    throw new PriceNotAvailableException(
                        "Bu fiyat grubunda cabin #{$cabinId} için fiyat tanımlanmamış.",
                        tourId: $tour->id,
                        tourDateId: $date->id,
                        cabinId: $cabinId,
                        reason: 'no_cabin_price',
                    );
                }

                $cabinTotal = $this->priceCabin($cabinPrice, $passengers->all(), $lines);
                $total += $cabinTotal;
            }
        } else {
            // Non-cruise: single generic price row (cabin_id NULL)
            $genericPrice = $priceGroup->priceForCabin(null);
            if ($genericPrice === null) {
                throw new PriceNotAvailableException(
                    'Bu fiyat grubunda generic (kabin yok) fiyat tanımlanmamış.',
                    tourId: $tour->id,
                    tourDateId: $date->id,
                    reason: 'no_generic_price',
                );
            }

            $total = $this->priceCabin($genericPrice, $draft->passengers, $lines);
        }

        return $total;
    }

    /**
     * Bir cabin (veya generic) için verilen passengerset'i fiyatla.
     * CalculatorFactory ile uygun strategy seç, total hesapla, line item
     * olarak ekle.
     *
     * @param  TourCabinPrice $cabinPrice
     * @param  array<int, PassengerDraft> $passengers
     */
    private function priceCabin(TourCabinPrice $cabinPrice, array $passengers, array &$lines): int
    {
        $ages   = [];
        $labels = [];

        foreach ($passengers as $p) {
            $ages[]   = $this->ageFromBirthdate($p->dateOfBirth);
            $labels[] = trim($p->firstName . ' ' . $p->lastName);
        }

        $passengerSet = new PassengerSet(
            ages: $ages,
            labels: $labels,
            cabinId: $cabinPrice->cabin_id,
        );

        $calculator = $this->calculatorFactory->forMethod($cabinPrice->calculation_method);
        $cabinTotal = $calculator->calculate($cabinPrice, $passengerSet);

        $cabinLabel = $cabinPrice->cabin_id !== null
            ? "Cabin #{$cabinPrice->cabin_id}"
            : 'Generic';

        $lines[] = new QuoteLineItem(
            kind:      'passenger',
            label:     sprintf('%s — %d yolcu (%s)',
                $cabinLabel,
                count($passengers),
                $cabinPrice->calculation_method?->label() ?? 'standart'
            ),
            unitPrice: $cabinTotal,
            quantity:  1,
            subtotal:  $cabinTotal,
            meta:      [
                'tour_cabin_price_id' => $cabinPrice->id,
                'cabin_id'            => $cabinPrice->cabin_id,
                'passenger_count'     => count($passengers),
                'calc_method'         => $cabinPrice->calculation_method?->value,
            ],
        );

        return $cabinTotal;
    }

    /**
     * Extras computation — mantığı Phase 1'den korunuyor.
     */
    private function computeExtrasCosts(Tour $tour, BookingDraft $draft, array &$lines): int
    {
        $total = 0;
        $extraIds = collect($draft->extras)->pluck('extraId')->all();

        if ($extraIds === []) {
            return 0;
        }

        $extras = TourExtra::query()
            ->where('tour_id', $tour->id)
            ->active()
            ->whereIn('id', $extraIds)
            ->get()
            ->keyBy('id');

        foreach ($draft->extras as $sel) {
            /** @var TourExtra|null $extra */
            $extra = $extras->get($sel->extraId);
            if (! $extra) {
                continue;
            }

            $perUnit = (int) $extra->price;
            $effectiveUnit = $extra->pricing_mode === 'per_passenger'
                ? $perUnit * $draft->passengerCount()
                : $perUnit;

            $subtotal = $effectiveUnit * $sel->quantity;
            $total   += $subtotal;

            $lines[] = new QuoteLineItem(
                kind:      'extra',
                label:     (string) $extra->name,
                unitPrice: $effectiveUnit,
                quantity:  $sel->quantity,
                subtotal:  $subtotal,
                meta:      ['extra_id' => $extra->id],
            );
        }

        return $total;
    }

    private function ageFromBirthdate(?string $dob): ?int
    {
        if (! $dob) {
            return null;
        }
        try {
            return (int) Carbon::parse($dob)->diffInYears(now());
        } catch (\Throwable) {
            return null;
        }
    }
}
