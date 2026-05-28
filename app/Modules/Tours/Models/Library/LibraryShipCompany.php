<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models\Library;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CENTRAL DB — Cruise Kütüphanesi (Phase 1.5.h)
 *
 * Global gemi firması master'ı.  Tenant `ShipCompany`'nin merkezi kaynağı;
 * LibraryImporter ile tenant DB'sine kopyalanır.  Her zaman `central`
 * connection üzerinden okunur/yazılır (tenancy initialize olsa bile).
 */
class LibraryShipCompany extends Model
{
    use SoftDeletes;

    protected $connection = 'central';
    protected $table = 'library_ship_companies';

    protected $fillable = [
        'legacy_id', 'slug', 'name',
        'company_type', 'operator', 'founded_year', 'headquarters', 'website',
        'logo_url', 'uses_cabin_groups', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'legacy_id'         => 'integer',
            'founded_year'      => 'integer',
            'uses_cabin_groups' => 'boolean',
            'is_active'         => 'boolean',
            'sort_order'        => 'integer',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(LibraryShipCompanyTranslation::class);
    }

    public function ships(): HasMany
    {
        return $this->hasMany(LibraryShip::class);
    }

    public function cabinGroups(): HasMany
    {
        return $this->hasMany(LibraryCabinGroup::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    public function translationFor(int $languageId): ?LibraryShipCompanyTranslation
    {
        return $this->translations()->where('language_id', $languageId)->first()
            ?? $this->translations()->first();
    }
}
