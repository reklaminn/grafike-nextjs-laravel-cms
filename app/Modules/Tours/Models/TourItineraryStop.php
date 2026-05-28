<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One stop within a TourItineraryDay (Phase 1.5.c).
 *
 * Multi-stop pattern (1,1,1,2,3,3):
 *   - Day 1-3: tek port (örn. İstanbul'da overnight)
 *   - Day 4:   iki port (Mykonos sabah + Patmos öğleden sonra)
 *   - Day 5-6: tek port (Santorini'de overnight)
 *
 * Cruise turları için kanonik kaynak: port_id (Port master).
 * Paket/günlük turlar için fallback: location string + manuel geo.
 *
 * Frontend render hiyerarşisi:
 *   port_id varsa → Port master'dan ad + ülke (translations) + bayrak
 *   yoksa         → location + geo_lat/geo_lng (Google Maps embed)
 */
class TourItineraryStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_itinerary_day_id',
        'port_id',
        'point_type',
        'title',
        'accommodation',
        'description',
        'location',
        'geo_lat',
        'geo_lng',
        'sort_order',
        'arrival_time',
        'departure_time',
        'dwell_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sort_order'     => 'integer',
            'arrival_time'   => 'datetime:H:i',
            'departure_time' => 'datetime:H:i',
            'geo_lat'        => 'decimal:7',
            'geo_lng'        => 'decimal:7',
            'dwell_minutes'  => 'integer',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(TourItineraryDay::class, 'tour_itinerary_day_id');
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }

    /**
     * Display label — port_id varsa Port master, yoksa location string.
     * Translations'ı zaten eager-load etmek arayanın sorumluluğunda;
     * burada lazy çağırırsak N+1'e gireriz.
     */
    public function displayLocation(?int $languageId = null): string
    {
        if ($this->port) {
            // Port master kullanılıyorsa translation katmanı oradan gelir.
            // Çağıran $stop->port->translationFor($lang)?->name kullanmalı.
            return $this->port->slug;
        }

        return (string) ($this->location ?? '—');
    }
}
