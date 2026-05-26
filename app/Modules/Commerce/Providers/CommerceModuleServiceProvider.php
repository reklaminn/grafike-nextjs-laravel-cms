<?php

declare(strict_types=1);

namespace App\Modules\Commerce\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events\TenancyInitialized;

/**
 * Commerce module service provider.
 *
 * ─── Phase 6 module — not yet shipped ────────────────────────────────────
 *
 * Commerce is the second vertical on the roadmap (after Tours).  Its
 * service provider class exists today so the modular monolith
 * infrastructure can be validated end-to-end before Phase 1 starts.
 *
 * In config/tenant_modules.php the entry is `user_installable => false`
 * so it does NOT show up in the admin UI; artisan
 * `tenant:module:install commerce` still works for testing.
 *
 * Phase 6 implementation will populate:
 *   - Models: Product, Variant, Category, Cart, Order, Shipment, Refund
 *   - Iyzico checkout reuse (PaymentsModule)
 *   - Discount/Coupon polymorphic engine (shared with Tours)
 *   - Admin Livewire CRUD + Next.js storefront sections
 */
class CommerceModuleServiceProvider extends ServiceProvider
{
    public const SLUG = 'commerce';

    public function register(): void
    {
        // Phase 6: bindings.
    }

    public function boot(): void
    {
        Event::listen(TenancyInitialized::class, function (TenancyInitialized $event): void {
            $tenant = $event->tenancy->tenant;

            if (! method_exists($tenant, 'hasModule') || ! $tenant->hasModule(self::SLUG)) {
                return;
            }

            $this->bootTenant();
        });
    }

    protected function bootTenant(): void
    {
        // Phase 6.
    }
}
