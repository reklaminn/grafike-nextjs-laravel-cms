<?php

declare(strict_types=1);

namespace App\Modules\Tours\Events;

use App\Modules\Tours\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired by ExpireUnpaidReservationJob when a Reserved booking
 * timed out without payment.  Capacity is already released.
 *
 * Default listener: SendBookingNotificationListener → sends the
 * "rezervasyon süreniz doldu, isterseniz tekrar deneyin" email.
 */
class BookingExpired
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly int $passengerCount = 0,
    ) {
    }
}
