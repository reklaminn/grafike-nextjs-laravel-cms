<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models\Library;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CENTRAL DB — Cruise Kütüphanesi (Phase 1.5.h)
 *
 * Global kabin master'ı.  Tenant `Cabin`'in merkezi kaynağı.  Kategori
 * FK yerine `cabin_category_slug` tutar — import'ta tenant cabin_category
 * id'sine slug ile resolve edilir.
 */
class LibraryCabin extends Model
{
    use SoftDeletes;

    protected $connection = 'central';
    protected $table = 'library_cabins';

    protected $fillable = [
        'legacy_id', 'library_ship_id', 'cabin_category_slug',
        'brand_subcategory', 'code', 'deck_name',
        'max_capacity', 'base_price_per_person', 'image_urls',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'legacy_id'             => 'integer',
            'max_capacity'          => 'integer',
            'base_price_per_person' => 'integer',
            'image_urls'            => 'array',
            'is_active'             => 'boolean',
            'sort_order'            => 'integer',
        ];
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(LibraryShip::class, 'library_ship_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(LibraryCabinTranslation::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            LibraryCabinGroup::class,
            'library_cabin_group_cabin',
            'library_cabin_id',
            'library_cabin_group_id'
        )->withPivot('sort_order')->withTimestamps();
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order');
    }

    public function translationFor(int $languageId): ?LibraryCabinTranslation
    {
        return $this->translations()->where('language_id', $languageId)->first()
            ?? $this->translations()->first();
    }
}
