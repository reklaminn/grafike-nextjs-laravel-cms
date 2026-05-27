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
 * Destination master — frontend landing page'leri için geo-aggregated
 * tur grupları.  Örnek: "Yunan Adaları" landing page Pire/Mikonos/Patmos
 * port'larını + bu destinasyonu kapsayan cruise tour'larını gösterir.
 *
 * Phase 1'deki TourCategory'den (hiyerarşik yapısal) AYRI bir entity.
 * Domain behavior'ları farklı:
 *   - Port'larla m2m (geo aggregation)
 *   - compatible_tour_types ile cruise/package/daily filter
 *   - Frontend landing page için zengin içerik
 */
class Destination extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'slug', 'latitude', 'longitude',
        'compatible_tour_types',
        'is_active', 'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'latitude'              => 'decimal:7',
            'longitude'             => 'decimal:7',
            'compatible_tour_types' => 'array',
            'is_active'             => 'boolean',
            'is_featured'           => 'boolean',
            'sort_order'            => 'integer',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function translations(): HasMany
    {
        return $this->hasMany(DestinationTranslation::class);
    }

    public function ports(): BelongsToMany
    {
        return $this->belongsToMany(
            Port::class,
            'destination_ports',
            'destination_id',
            'port_id'
        )->withPivot('sort_order')->withTimestamps()->orderBy('destination_ports.sort_order');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }

    public function scopeForTourType(Builder $q, string $tourType): Builder
    {
        // JSON contains for `compatible_tour_types` array — or null (boş = hepsi uygun)
        return $q->where(function ($w) use ($tourType) {
            $w->whereNull('compatible_tour_types')
                ->orWhereJsonContains('compatible_tour_types', $tourType);
        });
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function translationFor(int $languageId): ?DestinationTranslation
    {
        return $this->translations()
            ->where('language_id', $languageId)
            ->first()
            ?? $this->translations()->first();
    }

    public function isCompatibleWith(string $tourType): bool
    {
        $allowed = $this->compatible_tour_types;
        return $allowed === null || empty($allowed) || in_array($tourType, $allowed, true);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }
}
