<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * One day within a TourItinerary.  Carries optional geo + arrival/
 * departure times (relevant for cruise ports) and a meals summary
 * string (e.g. "Breakfast, Lunch on board").
 *
 * Image attached via Spatie media-library — frontend renders a small
 * thumbnail next to each day in the timeline.
 */
class TourItineraryDay extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'tour_itinerary_id', 'day_number', 'title', 'description',
        'location', 'geo_lat', 'geo_lng',
        'arrival_time', 'departure_time', 'meals',
    ];

    protected function casts(): array
    {
        return [
            'day_number'     => 'integer',
            'arrival_time'   => 'datetime:H:i',
            'departure_time' => 'datetime:H:i',
            'geo_lat'        => 'decimal:7',
            'geo_lng'        => 'decimal:7',
        ];
    }

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(TourItinerary::class, 'tour_itinerary_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('day_image')->singleFile();
    }
}
