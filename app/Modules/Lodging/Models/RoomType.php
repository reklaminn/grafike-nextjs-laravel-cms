<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * RoomType — a sellable unit category of a property.
 *
 * Tenant-scoped (lives in tenant DB; no `tenant_id`, no `$connection`).
 * Availability is adet-based via `unit_count` (see AvailabilityService).
 *
 * Images: admin uploads go to the Spatie `gallery` collection; seeders may
 * instead set the `images` JSON column with tenant-asset paths.  imageUrls()
 * merges both (Spatie first).
 */
class RoomType extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'slug', 'name', 'summary', 'description',
        'capacity_min', 'capacity_max', 'size_m2', 'bedrooms', 'bathrooms',
        'base_price', 'currency', 'unit_count',
        'amenities', 'images', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity_min' => 'integer',
            'capacity_max' => 'integer',
            'size_m2'      => 'integer',
            'bedrooms'     => 'integer',
            'bathrooms'    => 'integer',
            'base_price'   => 'decimal:2',
            'unit_count'   => 'integer',
            'amenities'    => 'array',
            'images'       => 'array',
            'sort_order'   => 'integer',
            'is_active'    => 'boolean',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(RoomAvailability::class);
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
     * Public image URLs — Spatie `gallery` media first, then the `images`
     * JSON column (seeder-provided tenant-asset paths).
     *
     * @return array<int, string>
     */
    public function imageUrls(): array
    {
        $media = $this->getMedia('gallery')->map(fn ($m) => $m->getUrl())->all();

        if ($media !== []) {
            return array_values($media);
        }

        return array_values(array_filter((array) ($this->images ?? [])));
    }

    // ─── Spatie MediaLibrary ─────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
    }
}
