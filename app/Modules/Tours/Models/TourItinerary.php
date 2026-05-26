<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Per-language itinerary header for a Tour.  See migration for shape.
 * Day rows live in TourItineraryDay.
 */
class TourItinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id', 'language_id', 'title', 'summary',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(TourItineraryDay::class)->orderBy('day_number');
    }
}
