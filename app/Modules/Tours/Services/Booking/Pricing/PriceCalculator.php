<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking\Pricing;

use App\Modules\Tours\Models\TourCabinPrice;
use App\Modules\Tours\Services\Booking\Pricing\DTOs\PassengerSet;

/**
 * Strategy interface — TourCabinPrice matrix'inden bir PassengerSet için
 * toplam ücreti hesaplar.  Tour.pricing_mode + TourCabinPrice.calculation_method
 * kombinasyonuna göre uygun strategy seçilir (CalculatorFactory).
 *
 * Tüm price hesabı **minor units** (kuruş) ile yapılır — int return.
 *
 * NULL price ("Sorunuz") karşılaşılırsa PriceNotAvailableException atılır
 * (QuoteService → 422 + manual quote redirect).
 */
interface PriceCalculator
{
    /**
     * Toplam tutarı hesapla (kuruş cinsinden, integer).
     *
     * @throws \App\Modules\Tours\Exceptions\PriceNotAvailableException
     */
    public function calculate(TourCabinPrice $price, PassengerSet $passengers): int;
}
