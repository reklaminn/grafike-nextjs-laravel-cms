<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Global cabin category — endüstri standart tip ayraçları.
 *
 * Cabin entity'sinden zorunlu FK ile bağlanır.  5 default seed
 * (CabinCategorySeeder): inside / outside / balcony / ocean_view / suite.
 *
 * Multi-language: name + description PortTranslation pattern'inde
 * ayrı tabloda.  Slug global (TR/EN aynı slug, sadece display farklı).
 */
class CabinCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'sort_order', 'icon', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function translations(): HasMany
    {
        return $this->hasMany(CabinCategoryTranslation::class);
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
        return $q->orderBy('sort_order')->orderBy('id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Translation for the given language with sensible fallback.
     * Falls back to first available translation if the requested
     * language is not yet localized.
     */
    public function translationFor(int $languageId): ?CabinCategoryTranslation
    {
        return $this->translations()
            ->where('language_id', $languageId)
            ->first()
            ?? $this->translations()->first();
    }
}
