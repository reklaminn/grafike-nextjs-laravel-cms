<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tour marketing tag — Tab 1'deki "Tur Seçenekleri" çoklu checkbox
 * listesi.  Tour'a m2m bağlanır (Phase 1.5.c).
 *
 * TourCategory'den (yapısal hiyerarşik) ve Campaign'den (tarih + indirim)
 * farklı semantik: marketing feature etiketi.
 */
class TourTag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug', 'icon',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TourTagTranslation::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    public function translationFor(int $languageId): ?TourTagTranslation
    {
        return $this->translations()
            ->where('language_id', $languageId)
            ->first()
            ?? $this->translations()->first();
    }
}
