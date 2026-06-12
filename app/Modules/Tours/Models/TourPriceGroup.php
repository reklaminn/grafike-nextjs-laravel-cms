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
 * Named pricing group — eski sistem Tab 4 "Yeni Fiyat Grubu Ekle"
 * formunun karşılığı.
 *
 * 1 Tour → N TourPriceGroup (örn. "Yaz 2026", "Erken Rezervasyon")
 * 1 TourPriceGroup → N TourDate via pivot (bulk-assign)
 * 1 TourPriceGroup → N TourCabinPrice (her cabin için matrix satırı)
 *
 * Aynı TourDate'e birden çok TourPriceGroup atanabilir (alternatif
 * fiyatlandırma) — müşteri sepete eklerken hangisinin geçerli olduğunu
 * QuoteService belirler (öncelik: sort_order, kampanya tarih aralığı vs).
 */
class TourPriceGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tour_id',
        'min_persons', 'adult_priority', 'capacity_quota', 'campaign_text',
        'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_persons'      => 'integer',
            'adult_priority'   => 'boolean',
            'capacity_quota'   => 'integer',
            'sort_order'       => 'integer',
            'is_active'        => 'boolean',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TourPriceGroupTranslation::class);
    }

    public function dates(): BelongsToMany
    {
        return $this->belongsToMany(
            TourDate::class,
            'tour_price_group_tour_date',
            'tour_price_group_id',
            'tour_date_id'
        )->withPivot('sort_order')->withTimestamps();
    }

    public function cabinPrices(): HasMany
    {
        return $this->hasMany(TourCabinPrice::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeForTour(Builder $q, int $tourId): Builder
    {
        return $q->where('tour_id', $tourId);
    }

    public function scopeForDate(Builder $q, int $tourDateId): Builder
    {
        return $q->whereHas('dates', fn ($w) => $w->where('tour_dates.id', $tourDateId));
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function translationFor(int $languageId): ?TourPriceGroupTranslation
    {
        return $this->translations()
            ->where('language_id', $languageId)
            ->first()
            ?? $this->translations()->first();
    }

    /**
     * Bu grupta belirli bir cabin için TourCabinPrice satırını al.
     * Cruise dışı turlarda (cabin_id NULL) — generic price'ı döner.
     */
    public function priceForCabin(?int $cabinId): ?TourCabinPrice
    {
        return $this->cabinPrices()
            ->where('cabin_id', $cabinId)
            ->where('is_active', true)
            ->first();
    }
}
