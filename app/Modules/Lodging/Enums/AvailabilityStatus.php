<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Enums;

/**
 * Why a room-type is unavailable on a given date range.
 *
 *   blocked — manual operator block (maintenance, long-stay guest, off-season)
 *   booked  — consumed by a confirmed reservation (source=reservation)
 *
 * Both count against `RoomType.unit_count` when computing "dolu" days.
 */
enum AvailabilityStatus: string
{
    case Blocked = 'blocked';
    case Booked  = 'booked';

    public function label(): string
    {
        return match ($this) {
            self::Blocked => 'Bloklu',
            self::Booked  => 'Dolu (rezervasyon)',
        };
    }
}
