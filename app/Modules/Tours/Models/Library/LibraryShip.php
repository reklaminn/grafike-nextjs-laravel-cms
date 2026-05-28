<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models\Library;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CENTRAL DB — Cruise Kütüphanesi (Phase 1.5.h)
 *
 * Global gemi master'ı.  Tenant `Ship`'in merkezi kaynağı.
 */
class LibraryShip extends Model
{
    use SoftDeletes;

    protected $connection = 'central';
    protected $table = 'library_ships';

    protected $fillable = [
        'legacy_id', 'library_ship_company_id', 'slug', 'name',
        'star_rating', 'local_agent', 'flag_country_code', 'imo_number',
        'year_built', 'passenger_capacity', 'crew_count', 'deck_count',
        'tonnage', 'length_m', 'beam_m', 'cruise_speed_knots',
        'facilities', 'cover_url', 'gallery_urls',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'legacy_id'          => 'integer',
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
            'gallery_urls'       => 'array',
            'is_active'          => 'boolean',
            'sort_order'         => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(LibraryShipCompany::class, 'library_ship_company_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(LibraryShipTranslation::class);
    }

    public function cabins(): HasMany
    {
        return $this->hasMany(LibraryCabin::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    public function translationFor(int $languageId): ?LibraryShipTranslation
    {
        return $this->translations()->where('language_id', $languageId)->first()
            ?? $this->translations()->first();
    }
}
