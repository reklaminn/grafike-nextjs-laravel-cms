<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking\Pricing\Calculators;

use App\Modules\Tours\Exceptions\PriceNotAvailableException;
use App\Modules\Tours\Models\TourCabinPrice;
use App\Modules\Tours\Services\Booking\Pricing\DTOs\PassengerSet;
use App\Modules\Tours\Services\Booking\Pricing\PriceCalculator;

/**
 * Kişi Toplama (1+2+3+4) calculation.  Eski sistemin alternatif mode'u —
 * her yolcuya o pozisyonun tier price'ı uygulanır:
 *
 *   1. yolcu (adult): price_single
 *   2. yolcu (adult): price_double
 *   3. yolcu (adult): price_triple
 *   4. yolcu (adult): price_quad
 *   5+ yolcu (adult): price_quad (sınır aşımı — defensive)
 *
 *   Çocuk ek: count × price_child
 *   Bebek ek: count × price_baby
 *
 * Doublex2'den farkı: 2 yolcu = single + double, 3 yolcu = single+double+triple,
 * yani **her yolcu kendi pozisyon tier'ı**.  Doublex2'de 2 yolcu için
 * double × 2 idi.
 */
class PersonSumCalculator implements PriceCalculator
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
        $adultCount = $dist['adult'];

        // Her yetişkin için pozisyonuna göre tier
        for ($position = 1; $position <= $adultCount; $position++) {
            $tierPrice = match (true) {
                $position === 1 => $price->price_single,
                $position === 2 => $price->price_double,
                $position === 3 => $price->price_triple,
                default         => $price->price_quad,   // 4 ve sonrası quad
            };

            $tierLabel = match (true) {
                $position === 1 => 'single',
                $position === 2 => 'double',
                $position === 3 => 'triple',
                default         => 'quad',
            };

            $total += $this->requirePrice($price, $tierPrice, $tierLabel);
        }

        // Çocuk + bebek
        if ($dist['child'] > 0) {
            $total += $this->requirePrice($price, $price->price_child, 'child') * $dist['child'];
        }
        if ($dist['baby'] > 0) {
            $total += $this->requirePrice($price, $price->price_baby, 'baby') * $dist['baby'];
        }

        return $total;
    }

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
