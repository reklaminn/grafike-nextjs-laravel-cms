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
 * Global destinasyon master'ı.  Tenant `Destination` kaynağı.  Port m2m
 * pivot library_port'a referans verir; import'ta destinasyonun portları
 * da turun port kütüphanesinden resolve edilir.
 */
class LibraryDestination extends Model
{
    use SoftDeletes;

    protected $connection = 'central';
    protected $table = 'library_destinations';

    protected $fillable = [
        'legacy_id', 'slug', 'latitude', 'longitude',
        'compatible_tour_types', 'cover_url', 'gallery_urls',
        'is_active', 'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'legacy_id'             => 'integer',
            'latitude'              => 'decimal:7',
            'longitude'             => 'decimal:7',
            'compatible_tour_types' => 'array',
            'gallery_urls'          => 'array',
            'is_active'             => 'boolean',
            'is_featured'           => 'boolean',
            'sort_order'            => 'integer',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(LibraryDestinationTranslation::class);
    }

    public function ports(): BelongsToMany
    {
        return $this->belongsToMany(
            LibraryPort::class,
            'library_destination_ports',
            'library_destination_id',
            'library_port_id'
        )->withPivot('sort_order')->withTimestamps()->orderBy('library_destination_ports.sort_order');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order');
    }

    public function translationFor(int $languageId): ?LibraryDestinationTranslation
    {
        return $this->translations()->where('language_id', $languageId)->first()
            ?? $this->translations()->first();
    }
}
