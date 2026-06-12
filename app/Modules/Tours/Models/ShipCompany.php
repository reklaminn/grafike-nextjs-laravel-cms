<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Cruise / ferry operator master.
 *
 * `name` brand olduğu için ana tabloda; description + meta_* translation
 * tablosunda.  `uses_cabin_groups` flag company-level CabinGroup
 * pattern'inin opt-in switch'i (kullanıcı kararı — büyük filolar için).
 *
 * Spatie media: logo (singleFile).
 */
class ShipCompany extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'source_library_id',
        'slug', 'name',
        'company_type', 'operator', 'founded_year', 'headquarters', 'website',
        'uses_cabin_groups', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'founded_year'        => 'integer',
            'uses_cabin_groups'   => 'boolean',
            'is_active'           => 'boolean',
            'sort_order'          => 'integer',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function translations(): HasMany
    {
        return $this->hasMany(ShipCompanyTranslation::class);
    }

    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class);
    }

    public function cabinGroups(): HasMany
    {
        return $this->hasMany(CabinGroup::class);
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

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function translationFor(int $languageId): ?ShipCompanyTranslation
    {
        return $this->translations()
            ->where('language_id', $languageId)
            ->first()
            ?? $this->translations()->first();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }
}
