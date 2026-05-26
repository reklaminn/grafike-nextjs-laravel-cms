<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Cruise-only cabin category.  Capacity is `cabin_count × beds_per_cabin`,
 * pre-computed into `capacity_total` so a single COUNT query can total
 * the ship's bed inventory without arithmetic.
 *
 * Phase 2 booking flow picks the cabin assignment when a cruise booking
 * is created (booking_passengers.cabin_id FK).  Validation that the
 * picked cabin still has free beds happens inside CapacityLockService.
 */
class TourCabinType extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'tour_id', 'code', 'name', 'description',
        'beds_per_cabin', 'cabin_count', 'capacity_total',
        'price_modifier', 'deck', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'beds_per_cabin' => 'integer',
            'cabin_count'    => 'integer',
            'capacity_total' => 'integer',
            'price_modifier' => 'integer',
            'sort_order'     => 'integer',
            'is_active'      => 'boolean',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(TourPriceTier::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cabin_photos');
        $this->addMediaCollection('deck_plan')->singleFile();
    }
}
