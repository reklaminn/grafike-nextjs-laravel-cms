<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models\Library;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CENTRAL DB — Cruise Kütüphanesi (Phase 1.5.h)
 *
 * Global liman master'ı (~1000 liman).  Tenant `Port`'un merkezi kaynağı.
 */
class LibraryPort extends Model
{
    use SoftDeletes;

    protected $connection = 'central';
    protected $table = 'library_ports';

    protected $fillable = [
        'legacy_id', 'slug', 'country_code',
        'latitude', 'longitude', 'population', 'video_url', 'timezone',
        'cover_url', 'gallery_urls', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'legacy_id'    => 'integer',
            'latitude'     => 'decimal:7',
            'longitude'    => 'decimal:7',
            'population'   => 'integer',
            'gallery_urls' => 'array',
            'is_active'    => 'boolean',
            'sort_order'   => 'integer',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(LibraryPortTranslation::class);
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(
            LibraryDestination::class,
            'library_destination_ports',
            'library_port_id',
            'library_destination_id'
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

    public function translationFor(int $languageId): ?LibraryPortTranslation
    {
        return $this->translations()->where('language_id', $languageId)->first()
            ?? $this->translations()->first();
    }
}
