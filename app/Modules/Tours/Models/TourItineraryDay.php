<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * One day within a TourItinerary.
 *
 * Phase 1.5.c refactor: port-spesifik bilgiler (location, geo, arrival/
 * departure time) buradan kaldırıldı; artık TourItineraryStop child
 * tablosunda yaşıyor.  Day satırı sadece gün-seviyesi başlık + özet
 * + meals taşır.
 *
 * Multi-stop motivasyon — 1,1,1,2,3,3 pattern: aynı gün birden çok
 * port (Day 4'te Mykonos + Patmos), peş peşe günler aynı port
 * (overnight in Istanbul Day 2-3).  Tek-port-tek-day modeli bunu
 * temsil edemiyordu.
 *
 * Image attached via Spatie media-library — frontend renders a small
 * thumbnail next to each day in the timeline.
 */
class TourItineraryDay extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'tour_itinerary_id', 'day_number', 'title', 'description', 'meals',
    ];

    protected function casts(): array
    {
        return [
            'day_number' => 'integer',
        ];
    }

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(TourItinerary::class, 'tour_itinerary_id');
    }

    /**
     * Gün içindeki port/lokasyon ziyaretleri (Phase 1.5.c).
     * Cruise turları için port_id zorunlu; paket/günlük turlar için
     * location string fallback kullanılabilir (model nullable).
     */
    public function stops(): HasMany
    {
        return $this->hasMany(TourItineraryStop::class)->orderBy('sort_order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('day_image')->singleFile();
    }
}
