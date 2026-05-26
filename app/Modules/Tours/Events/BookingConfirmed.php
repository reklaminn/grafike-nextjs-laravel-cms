<?php

declare(strict_types=1);

namespace App\Modules\Tours\Events;

use App\Modules\Tours\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired after a successful payment capture moves Reserved → Confirmed.
 *
 * Default listener: SendBookingNotificationListener → sends the
 * "rezervasyonunuz onaylandı" mail; Phase 3 admin will add voucher
 * PDF generation listener on this same event.
 */
class BookingConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly int $paidAmount,
        public readonly ?string $gatewayPaymentId = null,
    ) {
    }
}
