<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking\Pricing\Calculators;

use App\Modules\Tours\Exceptions\PriceNotAvailableException;
use App\Modules\Tours\Models\TourCabinPrice;
use App\Modules\Tours\Services\Booking\Pricing\DTOs\PassengerSet;
use App\Modules\Tours\Services\Booking\Pricing\PriceCalculator;

/**
 * Tek Kabin Fiyatı calculation.  Eski sistemin Charter/villa modu —
 * flat kabin fiyatı, yolcu sayısı önemsiz.
 *
 * Mantık:
 *   Toplam = price_single (flat cabin price)
 *
 * Tek satır kullanıldığı için (1+2+3+4 expansion yok) Suite/villa
 * tipi paket fiyatlandırma için ideal.  per_reservation pricing mode
 * ile birlikte kullanılır.
 *
 * Yolcu sayısı kontrolü yapılmaz — capacity_limit ayrı bir konu
 * (CapacityLockService).
 */
class FlatCabinCalculator implements PriceCalculator
{
    public function calculate(TourCabinPrice $price, PassengerSet $passengers): int
    {
        if ($price->price_single === null) {
            throw new PriceNotAvailableException(
                "TourCabinPrice [{$price->id}] için flat kabin fiyatı (price_single) tanımlanmamış.",
                cabinId: $price->cabin_id,
                reason: 'missing_tier:flat_cabin',
            );
        }

        return (int) $price->price_single;
    }
}
