<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Concrete departure / time slot of a Tour.  See migration for the
 * capacity-locking semantics.
 *
 * Booking-side concurrency (CapacityLockService) reads `capacity_left`
 * under SELECT ... FOR UPDATE — see Phase 2 of the plan.
 */
class TourDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id', 'starts_at', 'ends_at',
        'capacity_total', 'capacity_left',
        'price_override', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'      => 'datetime',
            'ends_at'        => 'datetime',
            'capacity_total' => 'integer',
            'capacity_left'  => 'integer',
            'price_override' => 'integer',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->where('starts_at', '>=', now());
    }

    public function scopeOpen(Builder $q): Builder
    {
        return $q->where('status', 'open')->where('capacity_left', '>', 0);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Effective price for this departure (minor units of the parent
     * tour's currency).  Returns the override when set; otherwise the
     * parent's base_price.
     */
    public function effectivePriceMinor(): int
    {
        if ($this->price_override !== null) {
            return (int) $this->price_override;
        }

        return (int) ($this->tour?->base_price ?? 0);
    }

    public function isSoldOut(): bool
    {
        return $this->capacity_left <= 0 || $this->status === 'sold_out';
    }
}
