<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Providers;

use App\Http\Middleware\InitializeTenancyForPublicApi;
use App\Http\Middleware\MeterTenantUsage;
use App\Http\Middleware\UseSiteHostHeader;
use App\Modules\Lodging\Services\AvailabilityService;
use App\Modules\Lodging\Services\PricingService;
use App\Modules\Lodging\Services\ReservationService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Lodging (Konaklama) module service provider.
 *
 * Mirrors ToursModuleServiceProvider: registered globally in
 * bootstrap/providers.php, but tenant-scoped runtime resources (the public
 * reservation API) only wire up when a request lands on a tenant that has
 * the `lodging` module enabled.  Admin routes load unconditionally and are
 * gated per-request by the `tenant.module:lodging` middleware.
 */
class LodgingModuleServiceProvider extends ServiceProvider
{
    /** Module slug — matches the key in config/tenant_modules.php. */
    public const SLUG = 'lodging';

    public function register(): void
    {
        $this->app->singleton(AvailabilityService::class);
        $this->app->singleton(PricingService::class);
        $this->app->singleton(ReservationService::class);
    }

    public function boot(): void
    {
        // Views under the `lodging::` namespace (admin + mail).
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'lodging');

        // Admin routes — loaded unconditionally (central context); per-request
        // gating via the `tenant.module:lodging` middleware on the group.
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');

        // Public reservation API — KOŞULSUZ boot'ta yüklenir ki `route:cache`
        // ile serileştirilsin. (Önceki desen: TenancyInitialized event'inde
        // loadRoutesFrom → `route:cache` sonrası no-op → rotalar tamamen
        // kaybolur → /api/v1/lodging/* 404 → daireler/rezervasyon boş gelir.)
        // Çekirdek tenant_api ile AYNI yığın (host header → tenancy init →
        // metering) + modülü olmayan tenant'ı 404'e düşüren modül kapısı.
        Route::middleware([
            UseSiteHostHeader::class,
            InitializeTenancyForPublicApi::class,
            MeterTenantUsage::class,
            'tenant.module.active:' . self::SLUG,
        ])->group(__DIR__ . '/../routes/api.php');
    }
}
