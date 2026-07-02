<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Mail;

use App\Modules\Lodging\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Operator notification for a new reservation request.
 *
 * Sent inline by ReservationService (mirrors the form system's inline
 * notification pattern).  Delivery uses the tenant's SMTP profile via the
 * MailTenancyBootstrapper, so no mailer config is needed here.
 */
class ReservationRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Reservation $reservation,
        public readonly ?string $roomTypeName = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Yeni Rezervasyon Talebi: ' . $this->reservation->code,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'lodging::mail.reservation-request',
            with: [
                'reservation'  => $this->reservation,
                'roomTypeName' => $this->roomTypeName,
            ],
        );
    }
}
