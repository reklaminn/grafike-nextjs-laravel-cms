<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use App\Modules\Tours\Enums\CalculationMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-cabin person-tier price matrix row.  Eski sistem Tab 4'teki
 * pricing matrix tablosunun gerçek karşılığı.
 *
 * Cruise için her kabin × her fiyat grubu için 1 row; non-cruise için
 * `cabin_id` NULL (generic price).
 *
 * 6 price column (single / double / triple / quad / child / baby) —
 * NULL = "Sorunuz" (admin fiyat girmemiş, manuel teklif).
 *
 * `calculation_method` belirler ki bu satırın price'larından total
 * nasıl hesaplanır (3 strategy — PriceCalculator interface).
 *
 * Phase 1.5.b'de Phase 1'in `TourPriceTier` modelinin yerine geçer.
 */
class TourCabinPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_price_group_id', 'cabin_id',
        'price_definition', 'calculation_method',
        'price_single', 'price_double', 'price_triple', 'price_quad',
        'price_child', 'price_baby',
        'currency',
        'child_age_min', 'child_age_max',
        'baby_age_min', 'baby_age_max',
        'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'calculation_method' => CalculationMethod::class,
            'price_single'       => 'integer',
            'price_double'       => 'integer',
            'price_triple'       => 'integer',
            'price_quad'         => 'integer',
            'price_child'        => 'integer',
            'price_baby'         => 'integer',
            'child_age_min'      => 'integer',
            'child_age_max'      => 'integer',
            'baby_age_min'       => 'integer',
            'baby_age_max'       => 'integer',
            'sort_order'         => 'integer',
            'is_active'          => 'boolean',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function group(): BelongsTo
    {
        return $this->belongsTo(TourPriceGroup::class, 'tour_price_group_id');
    }

    public function cabin(): BelongsTo
    {
        return $this->belongsTo(Cabin::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeForCabin(Builder $q, ?int $cabinId): Builder
    {
        return $q->where('cabin_id', $cabinId);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Bu satırda en az 1 fiyat dolu mu?  Hepsi NULL ise "Sorunuz" demek
     * — QuoteService kullanıcıya manuel teklif yönlendirir.
     */
    public function hasAnyPrice(): bool
    {
        return $this->price_single !== null
            || $this->price_double !== null
            || $this->price_triple !== null
            || $this->price_quad   !== null
            || $this->price_child  !== null
            || $this->price_baby   !== null;
    }

    /**
     * Belirli passenger position (1-4) için tier price'ını al.
     * 1=single, 2=double, 3=triple, 4=quad.
     */
    public function priceForPosition(int $position): ?int
    {
        return match ($position) {
            1 => $this->price_single,
            2 => $this->price_double,
            3 => $this->price_triple,
            4 => $this->price_quad,
            default => null,
        };
    }

    /**
     * Verilen yaş çocuk mu, bebek mi, yetişkin mi?
     */
    public function classifyAge(?int $age): string
    {
        if ($age === null) {
            return 'adult';
        }
        if ($age >= $this->baby_age_min && $age <= $this->baby_age_max) {
            return 'baby';
        }
        if ($age >= $this->child_age_min && $age <= $this->child_age_max) {
            return 'child';
        }
        return 'adult';
    }
}
