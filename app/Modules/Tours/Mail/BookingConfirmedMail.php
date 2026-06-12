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
 * "Ödemeniz alındı, rezervasyon onaylandı" — sent after BookingConfirmed
 * fires.  Phase 3 admin will attach the voucher PDF via Spatie media
 * library; Phase 2 keeps it text-only.
 *
 * View: resources/views/emails/tours/confirmed.blade.php
 */
class BookingConfirmedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Booking $booking)
    {
    }

    public function envelope(): Envelope
    {
        $ref = $this->booking->booking_ref;

        return new Envelope(
            subject: "Rezervasyon Onaylandı — {$ref}",
            metadata: [
                'booking_ref' => (string) $ref,
                'kind'        => 'tours.confirmed',
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tours.confirmed',
            with: [
                'booking'  => $this->booking,
                'totalPaid' => $this->booking->amount_paid / 100,
                'currency'  => $this->booking->currency,
            ],
        );
    }
}
