<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use App\Models\Member;
use App\Modules\Tours\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A booking is the contract between a customer (guest or Member) and
 * the agency for one departure of one tour.
 *
 * Domain rules of note:
 *   - Money is stored in minor units (kuruş).  Always compute totals on
 *     the integer columns; use the *Major() helpers only for display.
 *   - Capacity is held against the linked tour_date via row-locked
 *     decrement (Phase 2 — CapacityLockService).
 *   - State transitions are enforced by BookingStateMachine (Phase 2);
 *     callers should not write to `status` directly.
 *   - `customer_snapshot` keeps the booking self-contained even when
 *     the optional Member is later deleted (KVKK right-to-erasure).
 */
class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_ref', 'tour_date_id', 'member_id', 'status',
        'currency', 'subtotal', 'extras_total', 'discount_total',
        'tax_total', 'total_amount', 'amount_paid', 'amount_refunded',
        'deposit_due_amount', 'balance_due_at',
        'locale', 'customer_snapshot',
        'reserved_at', 'confirmed_at', 'cancelled_at', 'completed_at',
        'hold_expires_at',
        'internal_notes', 'source', 'utm_params',
    ];

    protected function casts(): array
    {
        return [
            'status'             => BookingStatus::class,
            'subtotal'           => 'integer',
            'extras_total'       => 'integer',
            'discount_total'     => 'integer',
            'tax_total'          => 'integer',
            'total_amount'       => 'integer',
            'amount_paid'        => 'integer',
            'amount_refunded'    => 'integer',
            'deposit_due_amount' => 'integer',
            'balance_due_at'     => 'datetime',
            'customer_snapshot'  => 'array',
            'utm_params'         => 'array',
            'reserved_at'        => 'datetime',
            'confirmed_at'       => 'datetime',
            'cancelled_at'       => 'datetime',
            'completed_at'       => 'datetime',
            'hold_expires_at'    => 'datetime',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function tourDate(): BelongsTo
    {
        return $this->belongsTo(TourDate::class);
    }

    /**
     * Convenience: hop straight to the parent Tour through the
     * tour_date join.  Use this for `with('tour')` eager loading on
     * admin listing pages; the chain bookings → tour_dates → tours
     * is satisfied without N+1.
     */
    public function tour(): HasOneThrough
    {
        return $this->hasOneThrough(
            related:         Tour::class,
            through:         TourDate::class,
            firstKey:        'id',           // tour_dates.id matches bookings.tour_date_id
            secondKey:       'id',           // tours.id matches tour_dates.tour_id
            localKey:        'tour_date_id', // local pointer on bookings
            secondLocalKey:  'tour_id',      // pointer on tour_dates
        );
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(BookingPassenger::class);
    }

    public function leadPassenger(): HasOne
    {
        return $this->hasOne(BookingPassenger::class)->where('is_lead', true);
    }

    public function extras(): HasMany
    {
        return $this->hasMany(BookingExtra::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeWithStatus(Builder $q, BookingStatus|string $status): Builder
    {
        $value = $status instanceof BookingStatus ? $status->value : $status;

        return $q->where('status', $value);
    }

    public function scopeHoldingCapacity(Builder $q): Builder
    {
        // Statuses that still occupy capacity on tour_date.
        return $q->whereIn('status', ['reserved', 'confirmed', 'completed']);
    }

    public function scopeAwaitingPayment(Builder $q): Builder
    {
        return $q->where('status', 'reserved');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isGuest(): bool
    {
        return $this->member_id === null;
    }

    public function totalAmountMajor(): float
    {
        return $this->total_amount / 100;
    }

    public function amountDueMajor(): float
    {
        return max(0, $this->total_amount - $this->amount_paid) / 100;
    }

    public function isFullyPaid(): bool
    {
        return $this->amount_paid >= $this->total_amount;
    }

    /**
     * Generate a tenant-unique, human-friendly booking reference.
     * Format: TUR-{YYYY}-{6 random uppercase chars} (uniqueness verified
     * by caller against the unique index on booking_ref).
     */
    public static function generateReference(): string
    {
        $year = now()->format('Y');
        $rand = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

        return "TUR-{$year}-{$rand}";
    }
}
