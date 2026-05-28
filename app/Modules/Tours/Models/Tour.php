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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 *   - Cabin master moved to per-ship Cabin model (Phase 1.5.a refactor).
 *     Cruise tours reference Ship via tour.ship_id (Phase 1.5.c) and
 *     query cabins via $tour->ship->cabins.
 *   - Pricing uses TourPriceGroup → TourCabinPrice matrix (Phase 1.5.b).
 *     Old TourPriceTier model dropped.
 *
 * Spatie integrations:
 *   - HasMedia / InteractsWithMedia — gallery, cover, brochure PDF on R2
 *     (per Phase 5.6 of the plan: tenant prefix = cms-uploads/tenants/{id}/).
 */
class Tour extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, HasTourType;

    protected $fillable = [
        'type', 'slug', 'sku', 'tour_category_id', 'ship_id', 'status',
        'currency', 'base_price', 'capacity_default',
        'duration_value', 'duration_unit',
        'pricing_mode', 'sales_status',
        'includes_flight', 'flight_info',
        'type_config', 'search_index',
        'sort_order', 'is_featured', 'structured_data_json',
        'copied_from_tour_id',
    ];

    protected function casts(): array
    {
        return [
            'type'                 => TourType::class,
            'pricing_mode'         => PricingMode::class,
            'sales_status'         => SalesStatus::class,
            'base_price'           => 'integer',
            'capacity_default'     => 'integer',
            'duration_value'       => 'integer',
            'type_config'          => 'array',
            'flight_info'          => 'array',
            'structured_data_json' => 'array',
            'includes_flight'      => 'boolean',
            'is_featured'          => 'boolean',
            'sort_order'           => 'integer',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class, 'tour_category_id');
    }

    /**
     * Secondary categories — bir tur birden çok kategoride görünebilsin
     * diye m2m pivot (Phase 1.5.c).  Primary kategori `tour_category_id`
     * URL slug + breadcrumb için; secondary'ler ek filtreleme/koleksiyon
     * için.  Frontend kategori sayfası primary OR pivot ile sorgular.
     */
    public function secondaryCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            TourCategory::class,
            'tour_category_tour',
            'tour_id',
            'tour_category_id'
        )->withPivot('sort_order')->withTimestamps();
    }

    /**
     * Cruise turları için Ship master referansı (Phase 1.5.c).
     * App-level guard: cruise type için NOT NULL; paket/günlük NULL.
     */
    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    /**
     * Destinasyon m2m — bir tur birden çok destinasyon kapsayabilir
     * (örn. "Akdeniz Yaz" → Yunan Adaları + İtalya).  Phase 1.5.c.
     */
    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(
            Destination::class,
            'tour_destinations',
            'tour_id',
            'destination_id'
        )->withPivot('sort_order')->withTimestamps()->orderBy('tour_destinations.sort_order');
    }

    /**
     * Pazarlama etiketleri — kategoriden farklı taksonomi (Phase 1.5.c).
     * "Erken Rezervasyon", "Aile Dostu", "Romantik" gibi duygusal/segment
     * etiketler.  Phase 1.5.a'da 12 default tag TourTagSeeder ile eklendi.
     */
    public function marketingTags(): BelongsToMany
    {
        return $this->belongsToMany(
            TourTag::class,
            'tour_marketing_tags',
            'tour_id',
            'tour_tag_id'
        )->withPivot('sort_order')->withTimestamps()->orderBy('tour_marketing_tags.sort_order');
    }

    /**
     * "Tur Kopyala" akışında kaynak tur referansı (Phase 1.5.c).
     * Audit trail + en sık kopyalanan tur tipi istatistiği için.
     */
    public function copiedFromTour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'copied_from_tour_id');
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
