<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Opt-in add-on for a Tour (transfer, insurance, photo package, …).
 *
 * `pricing_mode` decides whether the price is multiplied by passenger
 * count (per_passenger) or added once to the booking total (per_booking).
 */
class TourExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id', 'code', 'name', 'description',
        'pricing_mode', 'price',
        'is_required', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price'       => 'integer',
            'is_required' => 'boolean',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function bookingExtras(): HasMany
    {
        return $this->hasMany(BookingExtra::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }
}
