<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Geçici kota yükseltmesi (central DB).
 *
 * Aktiflik tamamen tarih aralığına bağlıdır — süre dolunca otomatik
 * etkisizleşir, geri alma job'ı gerekmez.
 */
class QuotaExtension extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id', 'extra_requests_per_day', 'starts_at', 'ends_at', 'reason', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at'   => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->starts_at <= now() && $this->ends_at >= now();
    }
}
