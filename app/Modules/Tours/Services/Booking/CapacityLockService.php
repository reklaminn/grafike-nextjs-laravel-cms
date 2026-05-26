<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking;

use App\Modules\Tours\Exceptions\CapacityExhaustedException;
use App\Modules\Tours\Models\TourDate;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Race-safe capacity decrement on `tour_dates`.
 *
 * The critical section runs inside a DB transaction with
 * `SELECT … FOR UPDATE` so two concurrent reservations for the last
 * remaining seat are serialised: the first wins, the second sees the
 * decremented capacity_left and raises CapacityExhaustedException.
 *
 * Why a row lock and not optimistic concurrency?
 *   - Race window is short (microseconds).
 *   - We want the second caller to RECEIVE THE ERROR, not silently retry.
 *   - MariaDB / MySQL native row locks are mature and well-understood.
 *
 * The transaction also flips `status` to `sold_out` when capacity
 * drops to zero, so listings can filter without re-counting.
 */
class CapacityLockService
{
    /**
     * Atomically reserve `$count` seats on the given tour_date.
     *
     * @return TourDate refreshed model showing the new capacity_left
     *
     * @throws CapacityExhaustedException if not enough seats remain.
     */
    public function reserve(int $tourDateId, int $count): TourDate
    {
        if ($count < 1) {
            throw new \InvalidArgumentException('Cannot reserve a non-positive seat count.');
        }

        return DB::transaction(function () use ($tourDateId, $count) {
            /** @var TourDate|null $date */
            $date = TourDate::query()
                ->where('id', $tourDateId)
                ->lockForUpdate()
                ->first();

            if (! $date) {
                throw new CapacityExhaustedException(
                    "TourDate {$tourDateId} not found",
                    tourDateId: $tourDateId,
                    requested:  $count,
                    available:  0,
                );
            }

            if ($date->status !== 'open') {
                throw new CapacityExhaustedException(
                    "TourDate {$tourDateId} is {$date->status}, not bookable",
                    tourDateId: $tourDateId,
                    requested:  $count,
                    available:  0,
                );
            }

            if ($date->capacity_left < $count) {
                throw new CapacityExhaustedException(
                    "TourDate {$tourDateId} has {$date->capacity_left} seats, {$count} requested",
                    tourDateId: $tourDateId,
                    requested:  $count,
                    available:  (int) $date->capacity_left,
                );
            }

            $date->capacity_left -= $count;

            // Mark sold_out so the listing filters can use a single
            // column rather than re-evaluating capacity_left > 0.
            if ($date->capacity_left === 0) {
                $date->status = 'sold_out';
            }

            $date->save();

            return $date->refresh();
        }, attempts: 5);
    }

    /**
     * Release previously-held seats back into capacity_left.
     *
     * Called by:
     *   - ExpireUnpaidReservationJob when the hold window lapses
     *   - BookingService when an admin cancels a reservation
     *   - Refund flow on full cancellation
     *
     * Idempotent — caller should pass the exact seat count the
     * booking holds and never call twice for the same booking.
     */
    public function release(int $tourDateId, int $count): TourDate
    {
        if ($count < 1) {
            throw new \InvalidArgumentException('Cannot release a non-positive seat count.');
        }

        return DB::transaction(function () use ($tourDateId, $count) {
            /** @var TourDate|null $date */
            $date = TourDate::query()
                ->where('id', $tourDateId)
                ->lockForUpdate()
                ->first();

            if (! $date) {
                throw new \RuntimeException("TourDate {$tourDateId} not found during release");
            }

            // Cap at capacity_total — defensive in case of a double-
            // release bug we have not yet found.
            $new = min(
                $date->capacity_total,
                $date->capacity_left + $count,
            );

            $date->capacity_left = $new;

            // Re-open if we previously marked sold_out.
            if ($date->status === 'sold_out' && $new > 0) {
                $date->status = 'open';
            }

            $date->save();

            return $date->refresh();
        }, attempts: 5);
    }

    /**
     * Read the current capacity_left value without a write.  Used by
     * the QuoteService validation step (cheap pre-check before the
     * expensive reserve()).
     */
    public function currentCapacity(int $tourDateId): int
    {
        return (int) (TourDate::query()
            ->where('id', $tourDateId)
            ->value('capacity_left') ?? 0);
    }
}
