<?php

declare(strict_types=1);

namespace App\Modules\Payments\Providers;

use App\Modules\Payments\Contracts\PaymentGateway;
use App\Modules\Payments\Gateways\IyzicoGateway;
use App\Modules\Payments\Services\PaymentDispatcher;
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

    /**
     * Container bindings — registered at boot, unconditional.  The
     * default-gateway slug is read from config so admins can swap
     * provider order without touching code.
     */
    public function register(): void
    {
        // Bind the default PaymentGateway → resolved via dispatcher.
        $this->app->bind(PaymentGateway::class, function ($app) {
            return $app->make(PaymentDispatcher::class)->gateway();
        });

        // Dispatcher is a singleton — its gateway map is constructed
        // from config('payments.gateways') so adding a new gateway is
        // a config-only change at this layer (the implementation class
        // is autowired by the container).
        $this->app->singleton(PaymentDispatcher::class, function ($app) {
            $gateways = [];
            foreach ((array) config('payments.gateways') as $slug => $cfg) {
                $class = $cfg['class'] ?? null;
                if (is_string($class) && class_exists($class)) {
                    $gateways[$slug] = $app->make($class);
                }
            }

            return new PaymentDispatcher(
                gateways: $gateways,
                default:  (string) config('payments.default_gateway', 'iyzico'),
            );
        });

        // IyzicoGateway is a regular singleton — Iyzico has no
        // per-instance state, BYOK resolution happens at call time.
        $this->app->singleton(IyzicoGateway::class);
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

    /**
     * Tenant-scoped wiring — webhook + callback routes only exist for
     * tenants that have payments (and therefore Tours or Commerce)
     * enabled.  No-op for pure "kurumsal" tenants.
     */
    protected function bootTenant(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}
