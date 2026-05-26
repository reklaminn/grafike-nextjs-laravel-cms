<?php

declare(strict_types=1);

namespace App\Modules\Tours\Providers;

use App\Modules\Tours\Events\BookingCancelled;
use App\Modules\Tours\Events\BookingConfirmed;
use App\Modules\Tours\Events\BookingExpired;
use App\Modules\Tours\Events\BookingReserved;
use App\Modules\Tours\Listeners\SendBookingNotificationListener;
use App\Modules\Tours\Services\Booking\BookingService;
use App\Modules\Tours\Services\Booking\CapacityLockService;
use App\Modules\Tours\Services\Booking\QuoteService;
use App\Modules\Tours\StateMachines\BookingStateMachine;
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

    /**
     * Central-context bindings — registered for every request,
     * regardless of whether the tenant has Tours enabled.  Adding
     * the services here lets central artisan commands (Phase 3 admin
     * sync scripts) resolve them.
     */
    public function register(): void
    {
        $this->app->singleton(CapacityLockService::class);
        $this->app->singleton(QuoteService::class);
        $this->app->singleton(BookingStateMachine::class);
        $this->app->singleton(BookingService::class);
    }

    public function boot(): void
    {
        // Lifecycle event → email listener.  Bound at boot regardless
        // of tenant so a queued event handler running in central
        // context still finds the listener.  The actual emails go out
        // via the tenant's SmtpProfile, which the mailable resolves
        // lazily inside Mail::to(...).
        Event::listen(BookingReserved::class,  SendBookingNotificationListener::class);
        Event::listen(BookingConfirmed::class, SendBookingNotificationListener::class);
        Event::listen(BookingCancelled::class, SendBookingNotificationListener::class);
        Event::listen(BookingExpired::class,   SendBookingNotificationListener::class);

        // Tenant-scoped resource registration is deferred until a tenant
        // is actually initialized and we know it has Tours enabled.
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
     * Routes load here so a "kurumsal" tenant's URL space stays free
     * of /api/v1/tours/* endpoints it doesn't need.
     */
    protected function bootTenant(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}
