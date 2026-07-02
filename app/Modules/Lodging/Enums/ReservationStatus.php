<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Enums;

/**
 * Lifecycle of a lodging reservation request.
 *
 *   pending   — visitor submitted, awaiting operator action (default)
 *   confirmed — operator accepted; contributes a `booked` availability row
 *   cancelled — declined / withdrawn; frees any held availability
 */
enum ReservationStatus: string
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Bekliyor',
            self::Confirmed => 'Onaylandı',
            self::Cancelled => 'İptal',
        };
    }

    /** Tailwind badge classes for the admin inbox. */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending   => 'bg-amber-100 text-amber-700',
            self::Confirmed => 'bg-green-100 text-green-700',
            self::Cancelled => 'bg-gray-200 text-gray-600',
        };
    }
}
