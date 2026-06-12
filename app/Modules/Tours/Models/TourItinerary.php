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
        'tour_id', 'language_id', 'origin_port_id', 'title', 'summary',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Tur Çıkış Şehri (hareket limanı) — Port master referansı (Phase 1.5.f).
     */
    public function originPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'origin_port_id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(TourItineraryDay::class)->orderBy('day_number');
    }
}
