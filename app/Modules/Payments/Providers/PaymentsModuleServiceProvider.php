<?php

declare(strict_types=1);

namespace App\Modules\Payments\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events\TenancyInitialized;

/**
 * Payments module service provider.
 *
 * Payments is a SHARED infrastructure module — it does not stand alone
 * (no admin nav of its own), it's pulled in transitively when a tenant
 * enables Tours or Commerce.  It owns the gateway abstraction
 * (Iyzico → Stripe → PayTR …) plus the polymorphic payment + refund
 * tables on the tenant DB.
 *
 * Each tenant supplies their own Iyzico API keys via BYOK — stored
 * encrypted under `Tenant.data.iyzico_keys`.  Platform never sees raw
 * keys (Crypt facade, same pattern as ai_settings).
 *
 * ─── Phase 0 status ──────────────────────────────────────────────────────
 *
 * Skeleton only.  The Iyzico gateway lands in Phase 2.
 */
class PaymentsModuleServiceProvider extends ServiceProvider
{
    public const SLUG = 'payments';

    public function register(): void
    {
        // Phase 2: bind PaymentGateway interface to IyzicoGateway.
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
        // Phase 2+: webhook routes, gateway-specific bindings.
    }
}
