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
 * Global company-level kabin grubu master'ı.  Tenant `CabinGroup` kaynağı.
 */
class LibraryCabinGroup extends Model
{
    use SoftDeletes;

    protected $connection = 'central';
    protected $table = 'library_cabin_groups';

    protected $fillable = [
        'legacy_id', 'library_ship_company_id', 'slug', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'legacy_id'  => 'integer',
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(LibraryShipCompany::class, 'library_ship_company_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(LibraryCabinGroupTranslation::class);
    }

    public function cabins(): BelongsToMany
    {
        return $this->belongsToMany(
            LibraryCabin::class,
            'library_cabin_group_cabin',
            'library_cabin_group_id',
            'library_cabin_id'
        )->withPivot('sort_order')->withTimestamps()->orderBy('library_cabin_group_cabin.sort_order');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order');
    }

    public function translationFor(int $languageId): ?LibraryCabinGroupTranslation
    {
        return $this->translations()->where('language_id', $languageId)->first()
            ?? $this->translations()->first();
    }
}
