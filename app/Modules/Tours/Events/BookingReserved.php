<?php

declare(strict_types=1);

namespace App\Modules\Tours\Events;

use App\Modules\Tours\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when BookingService::createReservation() succeeds and the
 * booking enters Reserved state with capacity held.
 *
 * Default listener: SendBookingNotificationListener → sends the
 * "rezervasyonunuzu aldık, ödemenizi bekliyoruz" email.
 */
class BookingReserved
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Booking $booking)
    {
    }
}
