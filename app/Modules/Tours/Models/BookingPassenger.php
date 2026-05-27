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
 *
 * Phase 1.5.b refactor:
 *   - cabin_id (FK to new Cabin model, nullable for non-cruise)
 *   - tour_cabin_price_id (FK to TourCabinPrice — pricing snapshot for audit)
 *
 * Previous Phase 1 fields removed:
 *   - tour_cabin_type_id (table dropped)
 *   - price_tier_id (table dropped)
 */
class BookingPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'passenger_type',
        'first_name', 'last_name',
        'id_type', 'id_number', 'nationality',
        'date_of_birth', 'gender',
        'cabin_id', 'tour_cabin_price_id',
        'is_lead', 'price', 'notes',
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

    // ─── Relations ────────────────────────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Phase 1.5.b refactor — eski `cabinType()` → yeni `cabin()`.
     * Master Cabin model'ine (Ship.cabins) referans.
     */
    public function cabin(): BelongsTo
    {
        return $this->belongsTo(Cabin::class);
    }

    /**
     * Phase 1.5.b refactor — eski `priceTier()` → yeni `tourCabinPrice()`.
     * TourCabinPrice matrix satırına snapshot referans (audit için).
     */
    public function tourCabinPrice(): BelongsTo
    {
        return $this->belongsTo(TourCabinPrice::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
