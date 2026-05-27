<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use App\Modules\Tours\Enums\PricingMode;
use App\Modules\Tours\Enums\SalesStatus;
use App\Modules\Tours\Enums\TourType;
use App\Modules\Tours\Models\Concerns\HasTourType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

/**
 * Tour — the canonical entity that an agency sells (cruise / package / daily).
 *
 * Architectural notes:
 *   - Tenant-scoped (lives in tenant DB; no `tenant_id` column).
 *   - Polymorphic single-table: see HasTourType trait + TourType enum.
 *   - Translations live in `tour_translations` (one row per language).
 *   - Departures live in `tour_dates` (capacity decremented atomically
 *     under DB lock by CapacityLockService in Phase 2).
 *   - Cabin types only meaningful for cruises; query via $tour->cabinTypes.
 *
 * Spatie integrations:
 *   - HasMedia / InteractsWithMedia — gallery, cover, brochure PDF on R2
 *     (per Phase 5.6 of the plan: tenant prefix = cms-uploads/tenants/{id}/).
 */
class Tour extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, HasTourType;

    protected $fillable = [
        'type', 'slug', 'tour_category_id', 'status',
        'currency', 'base_price', 'capacity_default',
        'pricing_mode', 'sales_status',
        'type_config', 'search_index',
        'sort_order', 'is_featured', 'structured_data_json',
    ];

    protected function casts(): array
    {
        return [
            'type'                 => TourType::class,
            'pricing_mode'         => PricingMode::class,
            'sales_status'         => SalesStatus::class,
            'base_price'           => 'integer',
            'capacity_default'     => 'integer',
            'type_config'          => 'array',
            'structured_data_json' => 'array',
            'is_featured'          => 'boolean',
            'sort_order'           => 'integer',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class, 'tour_category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TourTranslation::class);
    }

    public function dates(): HasMany
    {
        return $this->hasMany(TourDate::class)->orderBy('starts_at');
    }

    public function upcomingDates(): HasMany
    {
        return $this->dates()->where('starts_at', '>=', now())->where('status', 'open');
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(TourItinerary::class);
    }

    /**
     * Pricing groups — Tab 4'te admin'in oluşturduğu named price groups
     * ("Yaz 2026 Fiyatları" gibi).  Her grup N tarihe atanır, her grup
     * N TourCabinPrice satırı içerir (cabin × person-tier matrix).
     *
     * Phase 1.5.b refactor — eski `priceTiers()` relation'ının yerine.
     */
    public function priceGroups(): HasMany
    {
        return $this->hasMany(TourPriceGroup::class)->orderBy('sort_order');
    }

    public function extras(): HasMany
    {
        return $this->hasMany(TourExtra::class)->orderBy('sort_order');
    }

    public function includes(): HasMany
    {
        return $this->hasMany(TourInclude::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published');
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Convenience: fetch the translation row for a language, falling
     * back to the tenant's default language if not present.  Returns
     * null only if the tour has no translations at all.
     */
    public function translationFor(int $languageId): ?TourTranslation
    {
        return $this->translations()
            ->where('language_id', $languageId)
            ->first()
            ?? $this->translations()->first();
    }

    /**
     * Price in major units (TL, EUR, …) — pretty for display, lossy for
     * arithmetic.  Always do money math on the integer base_price.
     */
    public function basePriceMajor(): float
    {
        return $this->base_price / 100;
    }

    // ─── Spatie MediaLibrary ─────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('brochure')->acceptsMimeTypes(['application/pdf']);
    }
}
