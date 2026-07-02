<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Models;

use App\Modules\Lodging\Enums\ReservationSource;
use App\Modules\Lodging\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Reservation — a visitor's stay request.  Created via ReservationService
 * (public API / admin), transitioned confirmed/cancelled by the operator.
 */
class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'room_type_id',
        'guest_name', 'guest_phone', 'guest_email',
        'checkin', 'checkout', 'nights',
        'adults', 'children',
        'est_total', 'currency',
        'message', 'status', 'source', 'admin_note', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'checkin'   => 'date',
            'checkout'  => 'date',
            'nights'    => 'integer',
            'adults'    => 'integer',
            'children'  => 'integer',
            'est_total' => 'decimal:2',
            'status'    => ReservationStatus::class,
            'source'    => ReservationSource::class,
            'meta'      => 'array',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(RoomAvailability::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', ReservationStatus::Pending->value);
    }
}
