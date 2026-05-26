<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use App\Modules\Tours\Enums\PassengerType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One traveller on a Booking.
 *
 * Identity (TCKN / passport) collected at booking time because Turkish
 * tour operators must list passengers on manifests and (for cruises)
 * report names to the port authority.  Stored as-is — KVKK governs
 * retention (Phase 5 will add a redaction cron for old bookings).
 */
class BookingPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'passenger_type',
        'first_name', 'last_name',
        'id_type', 'id_number', 'nationality',
        'date_of_birth', 'gender',
        'tour_cabin_type_id', 'is_lead',
        'price_tier_id', 'price', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'passenger_type' => PassengerType::class,
            'date_of_birth'  => 'date',
            'is_lead'        => 'boolean',
            'price'          => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function cabinType(): BelongsTo
    {
        return $this->belongsTo(TourCabinType::class, 'tour_cabin_type_id');
    }

    public function priceTier(): BelongsTo
    {
        return $this->belongsTo(TourPriceTier::class, 'price_tier_id');
    }

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
