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

/**
 * Company-level cabin selection bundle.  Phase 1.5.a refactor (kullanıcı
 * düzeltmesi sonrası) — paket tur için ayrı havuz değil, **tour pricing
 * setup'ta toplu cabin seçimi** için filtre/grup mekanizması.
 *
 * Kullanım:
 *   1. Admin → Gemi Firması (Azamara) → "Yeni Kabin Grubu" → "Paket Tur Kabinleri"
 *   2. Azamara'nın gemilerindeki cabin'lerden istediklerini bu gruba ekler (m2m)
 *   3. Tour pricing setup'ında admin grup seçer → o gruba dahil cabin'lerin
 *      Tour.ship ile kesişimi matrix tablosuna yerleştirilir
 *
 * Sadece ShipCompany.uses_cabin_groups=true ise anlamlı.
 */
class CabinGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ship_company_id', 'slug',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(ShipCompany::class, 'ship_company_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CabinGroupTranslation::class);
    }

    public function cabins(): BelongsToMany
    {
        return $this->belongsToMany(
            Cabin::class,
            'cabin_group_cabin',
            'cabin_group_id',
            'cabin_id'
        )->withPivot('sort_order')->withTimestamps()->orderBy('pivot_sort_order');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeForCompany(Builder $q, int $companyId): Builder
    {
        return $q->where('ship_company_id', $companyId);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function translationFor(int $languageId): ?CabinGroupTranslation
    {
        return $this->translations()
            ->where('language_id', $languageId)
            ->first()
            ?? $this->translations()->first();
    }

    /**
     * Cabin'leri belirli bir Ship'e göre filtrele.  Tour pricing setup'ta
     * "Tour.ship'e ait olan cabin'leri göster" filtresi için.
     *
     * @return \Illuminate\Support\Collection<int, Cabin>
     */
    public function cabinsForShip(int $shipId): \Illuminate\Support\Collection
    {
        return $this->cabins()->where('cabins.ship_id', $shipId)->get();
    }
}
