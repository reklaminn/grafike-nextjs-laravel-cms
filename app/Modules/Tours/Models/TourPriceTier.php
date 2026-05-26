<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use App\Modules\Tours\Enums\PassengerType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Price tier — what an "adult on this cruise in this cabin" costs.
 *
 * `conditions_json` is the extensibility hook: QuoteService (Phase 2)
 * reads it to apply early-bird, age-bracket and group-size rules
 * without us needing schema changes for each new pricing dimension.
 *
 * Tier resolution is "highest priority that matches".
 */
class TourPriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id', 'passenger_type', 'tour_cabin_type_id',
        'price', 'conditions_json', 'priority', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'passenger_type'  => PassengerType::class,
            'price'           => 'integer',
            'conditions_json' => 'array',
            'priority'        => 'integer',
            'is_active'       => 'boolean',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function cabinType(): BelongsTo
    {
        return $this->belongsTo(TourCabinType::class, 'tour_cabin_type_id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }
}
