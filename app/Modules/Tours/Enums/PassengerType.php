<?php

declare(strict_types=1);

namespace App\Modules\Tours\Enums;

/**
 * Passenger classification — drives the price tier lookup and any
 * age-bracket business rules (e.g. children below 6 free on certain tours).
 *
 * Operators define the age boundaries on the Tour itself (via tour_price_tiers
 * → `conditions_json`) so the enum stays generic.  The codes are stable
 * because they're persisted on `booking_passengers.passenger_type`.
 */
enum PassengerType: string
{
    case Adult  = 'adult';
    case Child  = 'child';
    case Infant = 'infant';
    case Senior = 'senior';

    public function label(): string
    {
        return match ($this) {
            self::Adult  => 'Yetişkin',
            self::Child  => 'Çocuk',
            self::Infant => 'Bebek',
            self::Senior => 'Yaşlı (65+)',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }
}
