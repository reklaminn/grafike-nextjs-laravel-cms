<?php

declare(strict_types=1);

namespace App\Modules\Tours\Enums;

/**
 * Booking lifecycle state machine.
 *
 *   pending   — initial state, capacity NOT yet decremented (transient,
 *               only exists between "user submitted wizard" and
 *               "QuoteService validated availability")
 *   reserved  — capacity decremented, awaiting payment (20-minute hold).
 *               Expires back to `expired` via ExpireUnpaidReservation job.
 *   confirmed — payment captured, booking is final.
 *   completed — departure date passed; locked for reporting.
 *   cancelled — voluntarily cancelled by user/admin; subject to refund policy.
 *   refunded  — terminal state after refund flow finishes successfully.
 *   expired   — hold timed out without payment; capacity already released.
 *
 * Transitions are enforced by BookingStateMachine in Phase 2.  This enum
 * is the source of truth for valid statuses; the state machine just
 * defines the allowed edges.
 */
enum BookingStatus: string
{
    case Pending   = 'pending';
    case Reserved  = 'reserved';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded  = 'refunded';
    case Expired   = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Beklemede',
            self::Reserved  => 'Rezerve (Ödeme Bekleniyor)',
            self::Confirmed => 'Onaylandı',
            self::Completed => 'Tamamlandı',
            self::Cancelled => 'İptal Edildi',
            self::Refunded  => 'İade Edildi',
            self::Expired   => 'Süresi Doldu',
        };
    }

    /**
     * Statuses that still hold capacity on the tour_date.  Used by
     * CapacityLockService to compute the live capacity_left.
     */
    public function holdsCapacity(): bool
    {
        return match ($this) {
            self::Reserved, self::Confirmed, self::Completed => true,
            default                                          => false,
        };
    }

    /**
     * Terminal statuses — booking cannot move out of these on its own.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed, self::Cancelled, self::Refunded, self::Expired => true,
            default                                                         => false,
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }
}
