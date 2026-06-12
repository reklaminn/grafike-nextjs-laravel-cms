<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Info-only extras master — vize ücreti, havaalanı vergisi gibi
 * tekrarlanan, online tahsil edilmeyen kalemler.  Tour Tab 3'teki
 * "Ekstra Aktivite ve Ücretler" dropdown'unun kaynağı.
 *
 * Master'dan seçildiğinde TourExtra'ya copy-snapshot olarak yazılır
 * (Phase 1.5.c'de tour_extras tablosuna `info_extra_id` FK eklenecek
 * + pricing_mode enum'a 'info_only' değeri).
 */
class TenantInfoExtra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tenant_info_extras';

    protected $fillable = [
        'slug', 'default_amount', 'default_currency',
        'per_person', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'integer',
            'per_person'     => 'boolean',
            'sort_order'     => 'integer',
            'is_active'      => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TenantInfoExtraTranslation::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    public function translationFor(int $languageId): ?TenantInfoExtraTranslation
    {
        return $this->translations()
            ->where('language_id', $languageId)
            ->first()
            ?? $this->translations()->first();
    }

    public function defaultAmountMajor(): float
    {
        return $this->default_amount / 100;
    }
}
