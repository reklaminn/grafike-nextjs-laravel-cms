<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasFactory;

    /**
     * Tenant model — no $connection override needed.
     * stancl/tenancy's DatabaseTenancyBootstrapper automatically switches the
     * default connection to the active tenant's database per request.
     * No site_id column — the tenant DB already isolates per site.
     */
    protected $fillable = ['key', 'value', 'group', 'type'];

    /**
     * Get a setting value for the current tenant.
     * Cache key includes no site_id — stancl's CacheTenancyBootstrapper
     * automatically adds the tenant prefix (e.g. "tenant_nuhcicek:setting_logo").
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting_{$key}";

        return Cache::remember($cacheKey, 600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value for the current tenant.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("setting_{$key}");
    }
}
