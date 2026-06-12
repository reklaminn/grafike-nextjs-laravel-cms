<?php

declare(strict_types=1);

namespace App\Modules\Tours\StateMachines;

use App\Modules\Tours\Enums\BookingStatus;
use App\Modules\Tours\Exceptions\InvalidBookingTransitionException;
use App\Modules\Tours\Models\Booking;

/**
 * Hand-rolled FSM for Booking lifecycle.
 *
 * Why not winzou/state-machine?  One dependency fewer, transition
 * table is small enough to keep in code, and we avoid an opaque
 * abstraction layer when debugging refund edge cases at 3am.
 *
 * Transition diagram:
 *
 *   pending ── reserve() ─→ reserved ── confirm() ─→ confirmed ── complete() ─→ completed
 *                                │                       │
 *                                │                       ├─ cancel() ─→ cancelled
 *                                │                       │
 *                                │                       └─ refund() ─→ refunded
 *                                │
 *                                └─ expire() ─→ expired
 *                                └─ cancel() ─→ cancelled
 */
class BookingStateMachine
{
    /**
     * Allowed transitions table.  Key = from, value = list of allowed
     * to-states.  Anything not listed is rejected.
     *
     * @var array<string, array<int, BookingStatus>>
     */
    private const TRANSITIONS = [
        'pending'   => [BookingStatus::Reserved,  BookingStatus::Cancelled],
        'reserved'  => [BookingStatus::Confirmed, BookingStatus::Expired, BookingStatus::Cancelled],
        'confirmed' => [BookingStatus::Completed, BookingStatus::Cancelled, BookingStatus::Refunded],
        'completed' => [BookingStatus::Refunded],  // post-trip refund window
        'cancelled' => [],
        'refunded'  => [],
        'expired'   => [],
    ];

    public function canTransition(BookingStatus $from, BookingStatus $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from->value] ?? [], true);
    }

    /**
     * Apply transition + persist + return refreshed model.
     *
     * @throws InvalidBookingTransitionException when the move is not allowed.
     */
    public function transition(Booking $booking, BookingStatus $to): Booking
    {
        $from = $booking->status instanceof BookingStatus
            ? $booking->status
            : BookingStatus::from((string) $booking->status);

        if (! $this->canTransition($from, $to)) {
            throw new InvalidBookingTransitionException(from: $from, to: $to);
        }

        $booking->status = $to;

        // Stamp lifecycle timestamps so callers don't have to.
        $now = now();
        match ($to) {
            BookingStatus::Reserved  => $booking->reserved_at  = $booking->reserved_at  ?? $now,
            BookingStatus::Confirmed => $booking->confirmed_at = $booking->confirmed_at ?? $now,
            BookingStatus::Completed => $booking->completed_at = $booking->completed_at ?? $now,
            BookingStatus::Cancelled => $booking->cancelled_at = $booking->cancelled_at ?? $now,
            default                  => null,
        };

        $booking->save();

        return $booking->refresh();
    }
}
