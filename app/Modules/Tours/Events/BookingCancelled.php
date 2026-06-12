<?php

declare(strict_types=1);

namespace App\Modules\Tours\Events;

use App\Modules\Tours\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when an admin / user cancels a Reserved or Confirmed booking.
 *
 * Capacity has been released by BookingService BEFORE this event
 * dispatches, so listeners are free to side-effect (email customer,
 * write to ledger, etc.) knowing the seat is back in the pool.
 *
 * $passengerCount is the count AT TIME OF CANCELLATION (captured
 * before passengers were soft-deleted, if that ever happens).
 */
class BookingCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly ?string $reason = null,
        public readonly int $passengerCount = 0,
    ) {
    }
}
