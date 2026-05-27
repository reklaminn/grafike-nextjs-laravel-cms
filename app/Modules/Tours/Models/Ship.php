<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Specific cruise / ferry ship — belongs to a ShipCompany.
 *
 * Rich teknik veri seti: yapım yılı, kapasite, mürettebat, tonaj,
 * boyutlar, hız, facilities (JSON config'den seçilen slug'lar).
 *
 * Cabin master entity'sinin parent'ı — `Cabin.ship_id` zorunlu FK.
 *
 * star_rating bizim eklediğimiz alan (eski sistemde yapılı değildi,
 * sadece tour ismi conventionu'ydu) — filtre + frontend için yararlı.
 *
 * Spatie media: gallery (gemi fotoğrafları), deck_plans (kat planları),
 * cover (kapak görseli).
 */
class Ship extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'ship_company_id', 'slug', 'name',
        'star_rating', 'local_agent', 'flag_country_code', 'imo_number',
        'year_built', 'passenger_capacity', 'crew_count', 'deck_count',
        'tonnage', 'length_m', 'beam_m', 'cruise_speed_knots',
        'facilities',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'star_rating'        => 'integer',
            'year_built'         => 'integer',
            'passenger_capacity' => 'integer',
            'crew_count'         => 'integer',
            'deck_count'         => 'integer',
            'tonnage'            => 'integer',
            'length_m'           => 'decimal:2',
            'beam_m'             => 'decimal:2',
            'cruise_speed_knots' => 'decimal:1',
            'facilities'         => 'array',
            'is_active'          => 'boolean',
            'sort_order'         => 'integer',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(ShipCompany::class, 'ship_company_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ShipTranslation::class);
    }

    public function cabins(): HasMany
    {
        return $this->hasMany(Cabin::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    public function scopeForCompany(Builder $q, int $companyId): Builder
    {
        return $q->where('ship_company_id', $companyId);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function translationFor(int $languageId): ?ShipTranslation
    {
        return $this->translations()
            ->where('language_id', $languageId)
            ->first()
            ?? $this->translations()->first();
    }

    /**
     * Country flag CDN URL — config/ship_facilities.php conventionu yerine
     * flagcdn.com kullanır.  flag_country_code yoksa null.
     */
    public function flagUrl(): ?string
    {
        if (! $this->flag_country_code) {
            return null;
        }
        return sprintf('https://flagcdn.com/%s.svg', strtolower($this->flag_country_code));
    }

    /**
     * Has the given facility slug?  Façade for clean if-checks.
     *
     *   $ship->hasFacility('casino')
     */
    public function hasFacility(string $slug): bool
    {
        return is_array($this->facilities) && in_array($slug, $this->facilities, true);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('deck_plans')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);
    }
}
