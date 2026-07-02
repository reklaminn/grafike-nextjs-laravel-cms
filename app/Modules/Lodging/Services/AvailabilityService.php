<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Services;

use App\Modules\Lodging\Models\RoomAvailability;
use App\Modules\Lodging\Models\RoomType;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;

/**
 * Availability math for the lodging module.
 *
 * A room-type is "dolu" (full) on a given night when the sum of consumed
 * units (manual blocks + confirmed-reservation rows) reaches its
 * `unit_count`.  All ranges are checkout-EXCLUSIVE, matching hotel-night
 * semantics: a stay of 10→12 occupies nights 10 and 11, not 12.
 */
class AvailabilityService
{
    /**
     * Nights (Y-m-d) in [$from, $to) that are fully booked for $roomType.
     *
     * @return array<int, string>  sorted, unique "Y-m-d" strings
     */
    public function bookedDates(RoomType $roomType, string $from, string $to): array
    {
        $start = CarbonImmutable::parse($from)->startOfDay();
        $end   = CarbonImmutable::parse($to)->startOfDay();

        if ($end <= $start) {
            return [];
        }

        // Per-night consumed-unit tally across every overlapping row.
        $perNight = [];

        $rows = RoomAvailability::query()
            ->where('room_type_id', $roomType->id)
            ->where('start_date', '<', $end->toDateString())
            ->where('end_date', '>', $start->toDateString())
            ->get(['start_date', 'end_date', 'qty']);

        foreach ($rows as $row) {
            $rowStart = CarbonImmutable::parse($row->start_date)->startOfDay();
            $rowEnd   = CarbonImmutable::parse($row->end_date)->startOfDay();

            // Clamp the row to the queried window.
            $clampStart = $rowStart->greaterThan($start) ? $rowStart : $start;
            $clampEnd   = $rowEnd->lessThan($end) ? $rowEnd : $end;

            foreach (CarbonPeriod::create($clampStart, '1 day', $clampEnd->subDay()) as $night) {
                $key = $night->toDateString();
                $perNight[$key] = ($perNight[$key] ?? 0) + max(1, (int) $row->qty);
            }
        }

        $capacity = max(1, (int) $roomType->unit_count);

        $full = [];
        foreach ($perNight as $date => $consumed) {
            if ($consumed >= $capacity) {
                $full[] = $date;
            }
        }

        sort($full);

        return $full;
    }

    /**
     * True when every night of [$checkin, $checkout) has at least one free
     * unit of $roomType.
     */
    public function isRangeAvailable(RoomType $roomType, string $checkin, string $checkout): bool
    {
        $booked = $this->bookedDates($roomType, $checkin, $checkout);

        return $booked === [];
    }
}
