<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Models;

use App\Modules\Lodging\Enums\AvailabilityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RoomAvailability — a date range (checkout-exclusive) that consumes
 * `qty` units of a RoomType, either as a manual block or as a derived
 * row from a confirmed Reservation.
 */
class RoomAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id', 'start_date', 'end_date',
        'qty', 'status', 'source', 'reservation_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'qty'        => 'integer',
            'status'     => AvailabilityStatus::class,
        ];
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
