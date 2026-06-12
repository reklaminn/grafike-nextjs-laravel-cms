<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking\Pricing;

use App\Modules\Tours\Enums\CalculationMethod;
use App\Modules\Tours\Services\Booking\Pricing\Calculators\FlatCabinCalculator;
use App\Modules\Tours\Services\Booking\Pricing\Calculators\PersonSumCalculator;
use App\Modules\Tours\Services\Booking\Pricing\Calculators\StandartDoublex2Calculator;
use InvalidArgumentException;

/**
 * Factory — CalculationMethod enum'a göre uygun PriceCalculator strategy
 * döndürür.
 *
 * Singleton kullanım için QuoteService DI ile inject edilir;
 * her çağrıda yeni instance gerekmiyor, calculator'lar stateless.
 */
class CalculatorFactory
{
    public function __construct(
        private readonly StandartDoublex2Calculator $doublex2,
        private readonly PersonSumCalculator $personSum,
        private readonly FlatCabinCalculator $flatCabin,
    ) {
    }

    public function forMethod(CalculationMethod $method): PriceCalculator
    {
        return match ($method) {
            CalculationMethod::StandartDoublex2 => $this->doublex2,
            CalculationMethod::PersonSum        => $this->personSum,
            CalculationMethod::FlatCabin        => $this->flatCabin,
        };
    }

    /**
     * String'den enum'a dönüş + factory.  DB'den okunan eski string
     * değerler için (cast yapılmamış raw fetch).
     */
    public function forSlug(string $slug): PriceCalculator
    {
        $method = CalculationMethod::tryFrom($slug)
            ?? throw new InvalidArgumentException("Unknown CalculationMethod slug: {$slug}");

        return $this->forMethod($method);
    }
}
