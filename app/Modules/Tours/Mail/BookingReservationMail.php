<?php

declare(strict_types=1);

namespace App\Modules\Tours\Mail;

use App\Modules\Tours\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * "Rezervasyonunuzu aldık, ödemenizi bekliyoruz" — sent immediately
 * after BookingReserved fires.  Includes the payment link so the
 * customer can complete checkout within the 20-minute hold window.
 *
 * View: resources/views/emails/tours/reservation.blade.php
 * (Phase 5 will localise + replace with Markdown mailables; the
 * Phase 2 template is intentionally minimal.)
 */
class BookingReservationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly ?string $paymentUrl = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $ref = $this->booking->booking_ref;

        return new Envelope(
            subject: "Rezervasyon Alındı — {$ref}",
            metadata: [
                'booking_ref' => (string) $ref,
                'kind'        => 'tours.reservation',
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tours.reservation',
            with: [
                'booking'     => $this->booking,
                'paymentUrl'  => $this->paymentUrl,
                'expiresAt'   => $this->booking->hold_expires_at,
                'totalAmount' => $this->booking->totalAmountMajor(),
                'currency'    => $this->booking->currency,
            ],
        );
    }
}
