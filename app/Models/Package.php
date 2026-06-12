<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Tenant abonelik paketleri — central DB'den yönetilir.
 *
 * allKeyed() / defaultKey() / get() : kod genelinde config('packages.*')
 * çağrılarının yerini alır. Sonuçlar 5 dk cachelenir; PackageController
 * kayıt sonrası cache'i temizler.
 */
class Package extends Model
{
    protected $connection = 'central';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key', 'label', 'ai_plan',
        'max_users', 'max_storage_mb', 'max_requests_per_day',
        'modules', 'sort_order', 'is_default',
    ];

    protected $casts = [
        'max_users'            => 'integer',
        'max_storage_mb'       => 'integer',
        'max_requests_per_day' => 'integer',
        'modules'              => 'array',
        'sort_order'           => 'integer',
        'is_default'           => 'boolean',
    ];

    // ─── Static helpers ──────────────────────────────────────────────────────

    /**
     * Tüm paketleri key => config-array formatında döndürür.
     * config('packages.packages') yerine kullan.
     */
    public static function allKeyed(): array
    {
        return Cache::remember('packages.all', 300, function () {
            return static::orderBy('sort_order')->get()
                ->keyBy('key')
                ->map(fn (self $p) => $p->toConfigArray())
                ->all();
        });
    }

    /**
     * Varsayılan paket anahtarı.
     * config('packages.default') yerine kullan.
     */
    public static function defaultKey(): string
    {
        return Cache::remember('packages.default', 300, function () {
            $row = static::where('is_default', true)->orderBy('sort_order')->first()
                ?? static::orderBy('sort_order')->first();

            return $row?->key ?? config('packages.default', 'basic');
        });
    }

    /**
     * Tek paket config dizisi; bulunamazsa null.
     * config("packages.packages.{$key}") yerine kullan.
     */
    public static function get(string $key): ?array
    {
        return static::allKeyed()[$key] ?? null;
    }

    /** Cache temizle (kayıt / güncelleme / silme sonrası çağrılır). */
    public static function clearCache(): void
    {
        Cache::forget('packages.all');
        Cache::forget('packages.default');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function toConfigArray(): array
    {
        return [
            'label'                => $this->label,
            'ai_plan'              => $this->ai_plan,
            'max_users'            => $this->max_users,
            'max_storage_mb'       => $this->max_storage_mb,
            'max_requests_per_day' => $this->max_requests_per_day,
            'modules'              => (array) ($this->modules ?? []),
        ];
    }

    protected static function booted(): void
    {
        $flush = fn () => static::clearCache();
        static::saved($flush);
        static::deleted($flush);
    }
}
