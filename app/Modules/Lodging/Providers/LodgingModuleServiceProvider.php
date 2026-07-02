<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Providers;

use App\Modules\Lodging\Services\AvailabilityService;
use App\Modules\Lodging\Services\PricingService;
use App\Modules\Lodging\Services\ReservationService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events\TenancyInitialized;

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

        // Public reservation API — deferred until a tenant with `lodging`
        // enabled is initialized, so kurumsal tenants stay free of the routes.
        Event::listen(TenancyInitialized::class, function (TenancyInitialized $event): void {
            $tenant = $event->tenancy->tenant;

            if (! method_exists($tenant, 'hasModule') || ! $tenant->hasModule(self::SLUG)) {
                return;
            }

            $this->bootTenant();
        });
    }

    /**
     * Called once per request when the active tenant has `lodging` enabled.
     */
    protected function bootTenant(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}
