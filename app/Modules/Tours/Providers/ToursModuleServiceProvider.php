<?php

declare(strict_types=1);

namespace App\Modules\Tours\Providers;

use App\Http\Middleware\InitializeTenancyForPublicApi;
use App\Http\Middleware\MeterTenantUsage;
use App\Http\Middleware\UseSiteHostHeader;
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
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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

        // Admin views — registered under the `tours::` namespace so
        // controllers reference them as `tours::admin.tours.index`
        // without colliding with the host app's view tree.
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'tours');

        // Admin routes — loaded unconditionally because they run in
        // central context (no tenant request to gate against here);
        // per-request gating happens via the `tenant.module:tours`
        // middleware on the route group itself.
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');

        // Public booking API — KOŞULSUZ boot'ta yüklenir ki `route:cache` ile
        // serileştirilsin. (Önceki desen TenancyInitialized event'inde
        // loadRoutesFrom idi → `route:cache` sonrası no-op → /api/v1/tours/*
        // 404 olurdu; Lodging'de bu üretimde daireler'i boşalttı — aynı hata.)
        // Çekirdek tenant_api ile AYNI yığın + modülü olmayan tenant'ı 404'e
        // düşüren istek-başına modül kapısı.
        Route::middleware([
            UseSiteHostHeader::class,
            InitializeTenancyForPublicApi::class,
            MeterTenantUsage::class,
            'tenant.module.active:' . self::SLUG,
        ])->group(__DIR__ . '/../routes/api.php');
    }
}
