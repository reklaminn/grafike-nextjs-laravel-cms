<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Services;

use App\Modules\Lodging\Models\RoomType;
use Carbon\CarbonImmutable;

/**
 * Estimated-total calculator.
 *
 * MVP: nights × base_price.  A single optional discount rule can be applied
 * here later (rate_rules table is deferred per spec §9).  Keep the shape
 * stable — the public API and admin both consume the returned array.
 */
class PricingService
{
    /**
     * @return array{nights:int, subtotal:float, discount:float, total:float, currency:string}
     */
    public function estimate(RoomType $roomType, string $checkin, string $checkout): array
    {
        $nights = $this->nights($checkin, $checkout);
        $base   = (float) $roomType->base_price;

        $subtotal = round($base * $nights, 2);
        $discount = 0.0; // reserved for a future single rate rule

        return [
            'nights'   => $nights,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total'    => round($subtotal - $discount, 2),
            'currency' => $roomType->currency ?: 'TRY',
        ];
    }

    /**
     * Whole nights between checkin (inclusive) and checkout (exclusive).
     * Always at least 1.
     */
    public function nights(string $checkin, string $checkout): int
    {
        $in  = CarbonImmutable::parse($checkin)->startOfDay();
        $out = CarbonImmutable::parse($checkout)->startOfDay();

        return max(1, (int) $in->diffInDays($out));
    }
}
