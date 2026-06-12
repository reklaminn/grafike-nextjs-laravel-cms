<?php

declare(strict_types=1);

namespace App\Modules\Tours\Jobs;

use App\Modules\Tours\Services\Booking\BookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Delayed job that releases seats held by a still-unpaid reservation.
 *
 * Scheduled at booking creation time with
 * `delay(now()->addMinutes(reservation_hold_minutes))`.  When it fires,
 * BookingService::expireIfStillReserved() guards against the race
 * where the user paid just before the timeout — if so the booking is
 * already Confirmed and the job is a no-op.
 *
 * Idempotent: safe to retry; the state machine refuses a no-op
 * transition silently inside BookingService.
 */
class ExpireUnpaidReservationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public readonly int $bookingId)
    {
    }

    public function handle(BookingService $service): void
    {
        try {
            $service->expireIfStillReserved($this->bookingId);
        } catch (Throwable $e) {
            Log::error('ExpireUnpaidReservationJob failed', [
                'booking_id' => $this->bookingId,
                'error'      => $e->getMessage(),
            ]);
            throw $e; // let queue retry
        }
    }
}
