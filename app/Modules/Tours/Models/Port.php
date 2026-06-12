<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Port master.  Cruise/ferry itinerary stops, destination port'ları,
 * tour origin/destination port'ları için merkezi referans.
 *
 * Editorial entity — sadece koordinat değil:
 *   - population, video_url, timezone (frontend port detay sayfası için)
 *   - country_code → CDN flag (flagcdn.com convention)
 *   - gallery + cover media (port fotoğrafları)
 *
 * Cabin → Ship → Itinerary stop → Port chain'inde Port her stop'a FK
 * verilir (string yerine).
 *
 * Destination ↔ Port m2m: bir Destination birden çok port'u kapsar
 * (örn. "Yunan Adaları" → Pire, Mikonos, Patmos, Santorini).
 */
class Port extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'source_library_id',
        'slug', 'country_code',
        'latitude', 'longitude',
        'population', 'video_url', 'timezone',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'latitude'   => 'decimal:7',
            'longitude'  => 'decimal:7',
            'population' => 'integer',
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function translations(): HasMany
    {
        return $this->hasMany(PortTranslation::class);
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(
            Destination::class,
            'destination_ports',
            'port_id',
            'destination_id'
        )->withPivot('sort_order')->withTimestamps();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    public function scopeInCountry(Builder $q, string $code): Builder
    {
        return $q->where('country_code', strtoupper($code));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function translationFor(int $languageId): ?PortTranslation
    {
        return $this->translations()
            ->where('language_id', $languageId)
            ->first()
            ?? $this->translations()->first();
    }

    /**
     * Country flag from public CDN.  country_code yoksa null.
     * Manuel bayrak alanı tutmuyoruz — eski sistemde elle URL girilirdi,
     * yeni sistem ISO kodundan otomatik üretir.
     */
    public function flagUrl(): ?string
    {
        if (! $this->country_code) {
            return null;
        }
        return sprintf('https://flagcdn.com/%s.svg', strtolower($this->country_code));
    }

    /**
     * Has geo coordinates fully set?  Auto-map generation için kontrol.
     */
    public function hasGeo(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }
}
