<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Tours\Pricing;

use App\Modules\Tours\Enums\CalculationMethod;
use App\Modules\Tours\Exceptions\PriceNotAvailableException;
use App\Modules\Tours\Models\TourCabinPrice;
use App\Modules\Tours\Services\Booking\Pricing\CalculatorFactory;
use App\Modules\Tours\Services\Booking\Pricing\Calculators\FlatCabinCalculator;
use App\Modules\Tours\Services\Booking\Pricing\Calculators\PersonSumCalculator;
use App\Modules\Tours\Services\Booking\Pricing\Calculators\StandartDoublex2Calculator;
use App\Modules\Tours\Services\Booking\Pricing\DTOs\PassengerSet;
use PHPUnit\Framework\TestCase;

/**
 * Phase 1.5.b — 3 calculator strategy için pure unit testler.
 *
 * Tüm fiyatlar kuruş cinsinden (minor units).  Test verisi:
 *   - price_single  = 100,000 (1,000 TL)
 *   - price_double  =  50,000 (500 TL — per person)
 *   - price_triple  =  40,000 (400 TL — 3rd person)
 *   - price_quad    =  30,000 (300 TL — 4th person)
 *   - price_child   =  20,000 (200 TL)
 *   - price_baby    =   5,000 (50 TL)
 *
 * Yaş aralıkları: child 2-11, baby 0-1 (default)
 */
class PricingCalculatorTest extends TestCase
{
    private TourCabinPrice $price;

    protected function setUp(): void
    {
        parent::setUp();

        // Test cabin price — full matrix
        $this->price = new TourCabinPrice();
        $this->price->forceFill([
            'id'             => 1,
            'cabin_id'       => 42,
            'price_single'   => 100_000,
            'price_double'   =>  50_000,
            'price_triple'   =>  40_000,
            'price_quad'     =>  30_000,
            'price_child'    =>  20_000,
            'price_baby'     =>   5_000,
            'child_age_min'  => 2,
            'child_age_max'  => 11,
            'baby_age_min'   => 0,
            'baby_age_max'   => 1,
            'currency'       => 'TRY',
            'calculation_method' => CalculationMethod::StandartDoublex2,
        ]);
    }

    // ─── StandartDoublex2 ────────────────────────────────────────────────────

    public function test_doublex2_solo_adult_pays_single(): void
    {
        $calc = new StandartDoublex2Calculator();
        $set  = new PassengerSet(ages: [30]);

        $this->assertSame(100_000, $calc->calculate($this->price, $set));
    }

    public function test_doublex2_two_adults_use_double_x2(): void
    {
        $calc = new StandartDoublex2Calculator();
        $set  = new PassengerSet(ages: [30, 35]);

        // double × 2 = 50,000 × 2 = 100,000
        $this->assertSame(100_000, $calc->calculate($this->price, $set));
    }

    public function test_doublex2_three_adults_double_x2_plus_triple(): void
    {
        $calc = new StandartDoublex2Calculator();
        $set  = new PassengerSet(ages: [30, 35, 40]);

        // double × 2 + triple = 100,000 + 40,000 = 140,000
        $this->assertSame(140_000, $calc->calculate($this->price, $set));
    }

    public function test_doublex2_four_adults_full_progression(): void
    {
        $calc = new StandartDoublex2Calculator();
        $set  = new PassengerSet(ages: [30, 35, 40, 45]);

        // double × 2 + triple + quad = 100,000 + 40,000 + 30,000 = 170,000
        $this->assertSame(170_000, $calc->calculate($this->price, $set));
    }

    public function test_doublex2_two_adults_plus_one_child(): void
    {
        $calc = new StandartDoublex2Calculator();
        $set  = new PassengerSet(ages: [30, 35, 8]); // 8 yaşında çocuk

        // (double × 2) + child = 100,000 + 20,000 = 120,000
        $this->assertSame(120_000, $calc->calculate($this->price, $set));
    }

    public function test_doublex2_two_adults_plus_baby(): void
    {
        $calc = new StandartDoublex2Calculator();
        $set  = new PassengerSet(ages: [30, 35, 1]); // 1 yaşında bebek

        // (double × 2) + baby = 100,000 + 5,000 = 105,000
        $this->assertSame(105_000, $calc->calculate($this->price, $set));
    }

    public function test_doublex2_throws_when_required_tier_is_null(): void
    {
        $calc = new StandartDoublex2Calculator();
        $this->price->price_single = null; // Sorunuz
        $set = new PassengerSet(ages: [30]);

        $this->expectException(PriceNotAvailableException::class);
        $calc->calculate($this->price, $set);
    }

    // ─── PersonSum ────────────────────────────────────────────────────────────

    public function test_person_sum_solo_pays_single(): void
    {
        $calc = new PersonSumCalculator();
        $set  = new PassengerSet(ages: [30]);

        $this->assertSame(100_000, $calc->calculate($this->price, $set));
    }

    public function test_person_sum_two_adults_single_plus_double(): void
    {
        $calc = new PersonSumCalculator();
        $set  = new PassengerSet(ages: [30, 35]);

        // 1st = single, 2nd = double → 100,000 + 50,000 = 150,000
        $this->assertSame(150_000, $calc->calculate($this->price, $set));
    }

    public function test_person_sum_four_adults_all_tiers(): void
    {
        $calc = new PersonSumCalculator();
        $set  = new PassengerSet(ages: [30, 35, 40, 45]);

        // single + double + triple + quad
        // 100,000 + 50,000 + 40,000 + 30,000 = 220,000
        $this->assertSame(220_000, $calc->calculate($this->price, $set));
    }

    public function test_person_sum_with_children_and_babies(): void
    {
        $calc = new PersonSumCalculator();
        // 2 adult + 1 çocuk (8 yaş) + 1 bebek (1 yaş)
        $set  = new PassengerSet(ages: [30, 35, 8, 1]);

        // 2 adult positional: single + double = 100,000 + 50,000
        // + child (1 × 20,000) + baby (1 × 5,000)
        // = 100,000 + 50,000 + 20,000 + 5,000 = 175,000
        $this->assertSame(175_000, $calc->calculate($this->price, $set));
    }

    // ─── FlatCabin ────────────────────────────────────────────────────────────

    public function test_flat_cabin_uses_single_price_regardless_of_count(): void
    {
        $calc = new FlatCabinCalculator();

        // 1 person
        $this->assertSame(100_000, $calc->calculate($this->price, new PassengerSet(ages: [30])));

        // 4 people — fiyat aynı (flat)
        $this->assertSame(100_000, $calc->calculate($this->price, new PassengerSet(ages: [30, 35, 40, 8])));

        // Charter — 20 kişi
        $this->assertSame(100_000, $calc->calculate(
            $this->price,
            new PassengerSet(ages: array_fill(0, 20, 30)),
        ));
    }

    public function test_flat_cabin_throws_when_price_null(): void
    {
        $calc = new FlatCabinCalculator();
        $this->price->price_single = null;

        $this->expectException(PriceNotAvailableException::class);
        $calc->calculate($this->price, new PassengerSet(ages: [30]));
    }

    // ─── PassengerSet.distribution ───────────────────────────────────────────

    public function test_passenger_set_distributes_by_age_brackets(): void
    {
        $set = new PassengerSet(ages: [30, 25, 8, 11, 0, 1, null]);

        $dist = $set->distribution(childMin: 2, childMax: 11, babyMin: 0, babyMax: 1);

        $this->assertSame(['adult' => 3, 'child' => 2, 'baby' => 2], $dist);
        // 30, 25, null → 3 adult
        // 8, 11        → 2 child
        // 0, 1         → 2 baby
    }

    // ─── CalculatorFactory dispatch ──────────────────────────────────────────

    public function test_factory_dispatches_correct_strategy_per_method(): void
    {
        $factory = new CalculatorFactory(
            new StandartDoublex2Calculator(),
            new PersonSumCalculator(),
            new FlatCabinCalculator(),
        );

        $this->assertInstanceOf(
            StandartDoublex2Calculator::class,
            $factory->forMethod(CalculationMethod::StandartDoublex2),
        );
        $this->assertInstanceOf(
            PersonSumCalculator::class,
            $factory->forMethod(CalculationMethod::PersonSum),
        );
        $this->assertInstanceOf(
            FlatCabinCalculator::class,
            $factory->forMethod(CalculationMethod::FlatCabin),
        );
    }

    public function test_factory_slug_dispatch_throws_on_unknown(): void
    {
        $factory = new CalculatorFactory(
            new StandartDoublex2Calculator(),
            new PersonSumCalculator(),
            new FlatCabinCalculator(),
        );

        $this->expectException(\InvalidArgumentException::class);
        $factory->forSlug('nonexistent');
    }

    public function test_factory_slug_dispatch_returns_correct_strategy(): void
    {
        $factory = new CalculatorFactory(
            new StandartDoublex2Calculator(),
            new PersonSumCalculator(),
            new FlatCabinCalculator(),
        );

        $this->assertInstanceOf(
            FlatCabinCalculator::class,
            $factory->forSlug('flat_cabin'),
        );
        $this->assertInstanceOf(
            PersonSumCalculator::class,
            $factory->forSlug('person_sum'),
        );
    }
}
