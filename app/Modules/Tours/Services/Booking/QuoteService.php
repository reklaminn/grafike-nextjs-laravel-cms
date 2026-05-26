<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking;

use App\Modules\Tours\Enums\PassengerType;
use App\Modules\Tours\Exceptions\QuoteValidationException;
use App\Modules\Tours\Models\Tour;
use App\Modules\Tours\Models\TourDate;
use App\Modules\Tours\Models\TourExtra;
use App\Modules\Tours\Models\TourPriceTier;
use App\Modules\Tours\Services\Booking\DTOs\BookingDraft;
use App\Modules\Tours\Services\Booking\DTOs\PassengerDraft;
use App\Modules\Tours\Services\Booking\DTOs\Quote;
use App\Modules\Tours\Services\Booking\DTOs\QuoteLineItem;
use Illuminate\Support\Carbon;

/**
 * Pure (read-only) price computation for a BookingDraft.
 *
 * Inputs:
 *   - BookingDraft (passengers + extras + cabin selections)
 *   - Tour + TourDate (loaded by the caller / passed in)
 *
 * Output:
 *   - Quote with line-by-line breakdown + grand total
 *
 * This service NEVER writes to the database — it's safe to call from
 * a quote-preview API endpoint without side effects.  The actual
 * persistence happens in BookingService after a confirmed Quote is
 * paired with a CapacityLockService::reserve() call.
 */
class QuoteService
{
    public function compute(Tour $tour, TourDate $date, BookingDraft $draft): Quote
    {
        $this->validate($tour, $date, $draft);

        $currency = (string) $tour->currency;

        $lines = [];
        $passengerTotal = 0;

        foreach ($draft->passengers as $idx => $passenger) {
            $tier  = $this->resolveTier($tour, $passenger, $date);
            $price = $this->passengerPrice($tour, $date, $passenger, $tier);

            $lines[] = new QuoteLineItem(
                kind:      'passenger',
                label:     $this->passengerLabel($passenger, $tier),
                unitPrice: $price,
                quantity:  1,
                subtotal:  $price,
                meta:      [
                    'passenger_idx' => $idx,
                    'tier_id'       => $tier?->id,
                    'cabin_id'      => $passenger->cabinTypeId,
                ],
            );

            $passengerTotal += $price;
        }

        // Extras
        $extrasTotal = 0;
        $extraIds = collect($draft->extras)->pluck('extraId')->all();
        $extraModels = $extraIds === []
            ? collect()
            : TourExtra::query()
                ->where('tour_id', $tour->id)
                ->active()
                ->whereIn('id', $extraIds)
                ->get()
                ->keyBy('id');

        foreach ($draft->extras as $sel) {
            /** @var TourExtra|null $extra */
            $extra = $extraModels->get($sel->extraId);
            if (! $extra) {
                continue;
            }

            $perUnit = (int) $extra->price;
            // per_passenger applies the price to every passenger
            // before applying the user-selected quantity.  Typical
            // use-case is "havalimanı transferi: 1 kişi başına 150 TL".
            $effectiveUnit = $extra->pricing_mode === 'per_passenger'
                ? $perUnit * $draft->passengerCount()
                : $perUnit;

            $subtotal = $effectiveUnit * $sel->quantity;
            $extrasTotal += $subtotal;

            $lines[] = new QuoteLineItem(
                kind:      'extra',
                label:     (string) $extra->name,
                unitPrice: $effectiveUnit,
                quantity:  $sel->quantity,
                subtotal:  $subtotal,
                meta:      ['extra_id' => $extra->id],
            );
        }

        // Phase 2 ships no discount / tax engine — those land in Phase 5.
        $discountTotal = 0;
        $taxTotal      = 0;

        $total = max(0, $passengerTotal + $extrasTotal + $discountTotal + $taxTotal);

        // Phase 5 will surface a per-tour deposit policy (e.g. 30% now,
        // rest 14 days before departure).  For Phase 2 the deposit
        // equals the full amount — the customer pays in full on
        // booking, matching the simplest cash-flow model.
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

        // Cruise tours require every passenger to pick a cabin type;
        // package/daily tours forbid it (data hygiene).
        foreach ($draft->passengers as $idx => $passenger) {
            if ($tour->isCruise() && $passenger->cabinTypeId === null) {
                $errors[] = "Yolcu #" . ($idx + 1) . " için kabin seçimi zorunlu (cruise).";
            }
            if (! $tour->isCruise() && $passenger->cabinTypeId !== null) {
                $errors[] = "Yolcu #" . ($idx + 1) . " için kabin seçilemez (paket/günlük tur).";
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
     * Pick the highest-priority active price tier that matches the
     * passenger's type + cabin (cruises) + age window (conditions_json).
     */
    private function resolveTier(Tour $tour, PassengerDraft $passenger, TourDate $date): ?TourPriceTier
    {
        $tiers = $tour->priceTiers()
            ->active()
            ->where('passenger_type', $passenger->type->value)
            ->orderByDesc('priority')
            ->get();

        $age = $this->ageFromBirthdate($passenger->dateOfBirth);

        foreach ($tiers as $tier) {
            // Cabin-scoped tier (cruise only)
            if ($tier->tour_cabin_type_id !== null && $tier->tour_cabin_type_id !== $passenger->cabinTypeId) {
                continue;
            }

            $conditions = is_array($tier->conditions_json) ? $tier->conditions_json : [];

            if (isset($conditions['min_age']) && ($age === null || $age < (int) $conditions['min_age'])) {
                continue;
            }
            if (isset($conditions['max_age']) && ($age === null || $age > (int) $conditions['max_age'])) {
                continue;
            }
            if (isset($conditions['early_bird_days'])) {
                $cutoff = $date->starts_at->copy()->subDays((int) $conditions['early_bird_days']);
                if (now()->greaterThan($cutoff)) {
                    continue;
                }
            }

            return $tier;
        }

        return null;
    }

    private function passengerPrice(Tour $tour, TourDate $date, PassengerDraft $passenger, ?TourPriceTier $tier): int
    {
        if ($tier !== null) {
            return (int) $tier->price;
        }

        // No tier match → fall back to date override OR base price,
        // plus cabin modifier (cruise only).
        $base = $date->effectivePriceMinor();

        if ($passenger->cabinTypeId !== null) {
            $cabin = $tour->cabinTypes->firstWhere('id', $passenger->cabinTypeId);
            if ($cabin) {
                $base += (int) $cabin->price_modifier;
            }
        }

        return max(0, $base);
    }

    private function passengerLabel(PassengerDraft $passenger, ?TourPriceTier $tier): string
    {
        $name = trim($passenger->firstName . ' ' . $passenger->lastName);
        $type = $passenger->type->label();

        return $name !== '' ? "{$name} ({$type})" : $type;
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
