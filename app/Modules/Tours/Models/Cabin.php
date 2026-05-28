<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Ship'in gerçek kabin envanteri.  Phase 1.5.a refactor sonucu —
 * Phase 1'deki per-tour `TourCabinType` modelinin yerine geçer.
 *
 * Cabin → Ship FK zorunlu (her cabin bir geminin).
 * Cabin → CabinCategory FK zorunlu (endüstri standart tip).
 * Cabin ↔ CabinGroup m2m (company-level selection bundle, opt-in).
 *
 * Pricing override için Tour'a doğrudan bağlanmaz — TourCabinPrice
 * matrix'i (Phase 1.5.b'de gelecek) Cabin'e referans verir.
 */
class Cabin extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'source_library_id',
        'ship_id', 'cabin_category_id', 'brand_subcategory',
        'code', 'deck_name', 'max_capacity',
        'base_price_per_person',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'max_capacity'          => 'integer',
            'base_price_per_person' => 'integer',
            'is_active'             => 'boolean',
            'sort_order'            => 'integer',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CabinCategory::class, 'cabin_category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CabinTranslation::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            CabinGroup::class,
            'cabin_group_cabin',
            'cabin_id',
            'cabin_group_id'
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

    public function scopeForShip(Builder $q, int $shipId): Builder
    {
        return $q->where('ship_id', $shipId);
    }

    public function scopeForCategory(Builder $q, int $categoryId): Builder
    {
        return $q->where('cabin_category_id', $categoryId);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function translationFor(int $languageId): ?CabinTranslation
    {
        return $this->translations()
            ->where('language_id', $languageId)
            ->first()
            ?? $this->translations()->first();
    }

    public function basePriceMajor(): float
    {
        return $this->base_price_per_person / 100;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('floor_plan')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);
    }
}
