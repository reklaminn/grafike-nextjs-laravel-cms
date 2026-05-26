<?php

declare(strict_types=1);

namespace App\Modules\Tours\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events\TenancyInitialized;

/**
 * Tours module service provider.
 *
 * Registered globally in bootstrap/providers.php so its class autoloads
 * regardless of tenant context.  However, **runtime resources** (routes,
 * tenant-scoped views, Livewire components) are wired up ONLY when a
 * request lands on a tenant that has the `tours` module enabled.  This
 * keeps "kurumsal" tenants free of any Tours-specific overhead.
 *
 * Central-context things that always need to be reachable (admin nav
 * partials, artisan commands shared with the platform, package config
 * publishing) can be registered unconditionally in `register()` / `boot()`.
 *
 * ─── Phase 0 status ──────────────────────────────────────────────────────
 *
 * This is a skeleton.  No routes, views, or models have shipped yet — the
 * module's data layer arrives in Phase 1 and its admin/frontend surfaces
 * in Phases 3 / 4.  The class exists today so:
 *
 *   - config/tenant_modules.php can reference it
 *   - bootstrap/providers.php can autoload it
 *   - artisan tenant:module:install tours validates against a real class
 *
 * Phase 1+ filling-in checklist (when you come back):
 *   1. Register module migrations path
 *   2. loadRoutesFrom(__DIR__.'/../routes/admin.php') — guarded
 *   3. loadRoutesFrom(__DIR__.'/../routes/api.php') — guarded
 *   4. loadViewsFrom(__DIR__.'/../resources/views', 'tours')
 *   5. Livewire::component('tours::admin.tours.index', …)
 *   6. Commands: $this->commands([...]) for Tours-specific artisan
 */
class ToursModuleServiceProvider extends ServiceProvider
{
    /**
     * Module slug — matches the key in config/tenant_modules.php.
     */
    public const SLUG = 'tours';

    public function register(): void
    {
        // No central-context bindings yet.
    }

    public function boot(): void
    {
        // Tenant-scoped resource registration is deferred until a tenant
        // is actually initialized and we know it has Tours enabled.  See
        // bootTenant() for what runs at that point.
        Event::listen(TenancyInitialized::class, function (TenancyInitialized $event): void {
            $tenant = $event->tenancy->tenant;

            if (! method_exists($tenant, 'hasModule') || ! $tenant->hasModule(self::SLUG)) {
                return;
            }

            $this->bootTenant();
        });
    }

    /**
     * Called once per request when the active tenant has `tours` enabled.
     * Phase 1+ implementation lands here.
     */
    protected function bootTenant(): void
    {
        // Phase 1+: load tenant routes, views, Livewire components.
    }
}
