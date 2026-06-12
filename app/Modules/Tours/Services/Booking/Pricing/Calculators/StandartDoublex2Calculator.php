<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking\Pricing\Calculators;

use App\Modules\Tours\Exceptions\PriceNotAvailableException;
use App\Modules\Tours\Models\TourCabinPrice;
use App\Modules\Tours\Services\Booking\Pricing\DTOs\PassengerSet;
use App\Modules\Tours\Services\Booking\Pricing\PriceCalculator;

/**
 * Standart (Doublex2) calculation.  Eski sistemin default davranışı —
 * 2-kişilik kabinde kişi başı double rate.
 *
 * Mantık:
 *   - 1 passenger:   price_single (solo cabin use)
 *   - 2 passengers:  price_double × 2
 *   - 3 passengers:  price_double × 2 + price_triple (3. kişi tier'ı)
 *   - 4 passengers:  price_double × 2 + price_triple + price_quad
 *
 * Çocuk + bebek ek olarak adult sayımına dahil edilmez — kendi
 * tier price'larına göre eklenir (price_child × count, price_baby × count).
 *
 * Yaş aralıkları TourCabinPrice'taki child_age_min/max + baby_age_min/max
 * ile belirlenir; bu calculator PassengerSet.distribution() ile sınıflandırır.
 */
class StandartDoublex2Calculator implements PriceCalculator
{
    public function calculate(TourCabinPrice $price, PassengerSet $passengers): int
    {
        $dist = $passengers->distribution(
            $price->child_age_min,
            $price->child_age_max,
            $price->baby_age_min,
            $price->baby_age_max,
        );

        $total = 0;

        // Adults — Doublex2 mantığıyla
        $adultCount = $dist['adult'];

        if ($adultCount === 0) {
            // Yalnız bebek/çocuk olmaz, bu durumu skip et
        } elseif ($adultCount === 1) {
            $total += $this->requirePrice($price, $price->price_single, 'single');
        } else {
            // 2+ adults — first 2 use double rate × 2
            $total += $this->requirePrice($price, $price->price_double, 'double') * 2;

            // 3rd adult → triple tier
            if ($adultCount >= 3) {
                $total += $this->requirePrice($price, $price->price_triple, 'triple');
            }

            // 4th adult → quad tier
            if ($adultCount >= 4) {
                $total += $this->requirePrice($price, $price->price_quad, 'quad');
            }

            // 5+ adults: cap at quad price for each extra (defensive — UI
            // generally limits to 4 in single cabin, but if it happens we
            // re-use quad rate for additional adults)
            for ($i = 5; $i <= $adultCount; $i++) {
                $total += $this->requirePrice($price, $price->price_quad, 'quad');
            }
        }

        // Children + babies — kendi tier'larında, count × tier price
        if ($dist['child'] > 0) {
            $total += $this->requirePrice($price, $price->price_child, 'child') * $dist['child'];
        }
        if ($dist['baby'] > 0) {
            $total += $this->requirePrice($price, $price->price_baby, 'baby') * $dist['baby'];
        }

        return $total;
    }

    /**
     * NULL = "Sorunuz" — manuel teklif istemek lazım.
     */
    private function requirePrice(TourCabinPrice $price, ?int $value, string $tierLabel): int
    {
        if ($value === null) {
            throw new PriceNotAvailableException(
                "TourCabinPrice [{$price->id}] için '{$tierLabel}' tier fiyatı tanımlanmamış (Sorunuz).",
                cabinId: $price->cabin_id,
                reason: "missing_tier:{$tierLabel}",
            );
        }
        return (int) $value;
    }
}
