<?php

declare(strict_types=1);

namespace App\Modules\Tours\Listeners;

use App\Modules\Tours\Events\BookingCancelled;
use App\Modules\Tours\Events\BookingConfirmed;
use App\Modules\Tours\Events\BookingExpired;
use App\Modules\Tours\Events\BookingReserved;
use App\Modules\Tours\Mail\BookingConfirmedMail;
use App\Modules\Tours\Mail\BookingReservationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Single listener subscribed to all four lifecycle events; dispatches
 * the right Mailable depending on the event type.  Keeping them in
 * one class makes it easy to add a feature flag (e.g. "disable
 * customer emails for tenant X") in one place.
 *
 * ShouldQueue so an SMTP outage does not stall the BookingService
 * transaction-committing thread.
 */
class SendBookingNotificationListener implements ShouldQueue
{
    public function handle(object $event): void
    {
        $email = $this->customerEmail($event);
        if ($email === null) {
            // Guest booking with malformed snapshot — log but do not crash.
            Log::warning('SendBookingNotificationListener: no customer email', [
                'event' => $event::class,
                'booking_id' => method_exists($event, 'booking') ? $event->booking?->id : null,
            ]);
            return;
        }

        match (true) {
            $event instanceof BookingReserved  => Mail::to($email)->send(new BookingReservationMail(
                booking:    $event->booking,
                paymentUrl: $this->paymentUrlFor($event->booking),
            )),
            $event instanceof BookingConfirmed => Mail::to($email)->send(new BookingConfirmedMail($event->booking)),
            $event instanceof BookingCancelled => null, // Phase 5: cancellation email
            $event instanceof BookingExpired   => null, // Phase 5: expiry follow-up
            default                            => null,
        };
    }

    private function customerEmail(object $event): ?string
    {
        $booking = $event->booking ?? null;
        if (! $booking) {
            return null;
        }
        $snap = is_array($booking->customer_snapshot) ? $booking->customer_snapshot : [];

        return $snap['email'] ?? $booking->member?->email ?? null;
    }

    /**
     * Frontend payment-resume URL for the email CTA.  Format mirrors
     * the booking wizard route on Next.js (Phase 4):
     *   https://{tenant}/tr/turlar/{tour-slug}/rezervasyon/{booking_ref}
     */
    private function paymentUrlFor(\App\Modules\Tours\Models\Booking $booking): ?string
    {
        $tourSlug = $booking->tourDate?->tour?->slug;
        if (! $tourSlug) {
            return null;
        }

        $base = rtrim(config('cms.frontend_url', config('app.url')), '/');
        $locale = $booking->locale ?: 'tr';

        return sprintf('%s/%s/turlar/%s/rezervasyon/%s', $base, $locale, $tourSlug, $booking->booking_ref);
    }
}
