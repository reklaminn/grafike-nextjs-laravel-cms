<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * AI kullanım planları — central DB'den yönetilir.
 * AiQuotaService ve PackageController'da config('ai.plans') yerine kullanılır.
 */
class AiPlan extends Model
{
    protected $connection = 'central';
    protected $primaryKey = 'key';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'key', 'label',
        'monthly_requests', 'monthly_tokens', 'monthly_cost_usd',
        'sort_order', 'is_default',
    ];

    protected $casts = [
        'monthly_requests' => 'integer',
        'monthly_tokens'   => 'integer',
        'monthly_cost_usd' => 'float',
        'sort_order'       => 'integer',
        'is_default'       => 'boolean',
    ];

    // ─── Static helpers ──────────────────────────────────────────────────────

    /**
     * Tüm planları key => config-array formatında döndürür.
     * config('ai.plans') yerine kullan.
     */
    public static function allKeyed(): array
    {
        return Cache::remember('ai_plans.all', 300, function () {
            return static::orderBy('sort_order')->get()
                ->keyBy('key')
                ->map(fn (self $p) => $p->toConfigArray())
                ->all();
        });
    }

    /** Varsayılan plan anahtarı. */
    public static function defaultKey(): string
    {
        return Cache::remember('ai_plans.default', 300, function () {
            $row = static::where('is_default', true)->orderBy('sort_order')->first()
                ?? static::orderBy('sort_order')->first();

            return $row?->key ?? config('ai.default_plan', 'free');
        });
    }

    public static function get(string $key): ?array
    {
        return static::allKeyed()[$key] ?? null;
    }

    public static function clearCache(): void
    {
        Cache::forget('ai_plans.all');
        Cache::forget('ai_plans.default');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function toConfigArray(): array
    {
        return [
            'label'            => $this->label,
            'monthly_requests' => $this->monthly_requests,
            'monthly_tokens'   => $this->monthly_tokens,
            'monthly_cost_usd' => $this->monthly_cost_usd,
        ];
    }

    protected static function booted(): void
    {
        $flush = fn () => static::clearCache();
        static::saved($flush);
        static::deleted($flush);
    }
}
