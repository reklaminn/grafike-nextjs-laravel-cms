<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One add-on attached to a Booking.  Name and price are snapshotted at
 * booking time so the line item stays stable even if the parent
 * TourExtra is later edited.
 */
class BookingExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'tour_extra_id',
        'name_snapshot', 'unit_price', 'quantity', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'quantity'   => 'integer',
            'subtotal'   => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function tourExtra(): BelongsTo
    {
        return $this->belongsTo(TourExtra::class);
    }
}
