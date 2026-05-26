<?php

declare(strict_types=1);

namespace App\Modules\Payments\Services;

use App\Modules\Payments\Contracts\PaymentGateway;
use App\Modules\Payments\Exceptions\PaymentGatewayException;

/**
 * Resolves a PaymentGateway implementation from a slug.
 *
 * Today: only `iyzico`.
 * Tomorrow (Phase 5+): per-tenant gateway routing — read
 * Tenant.data.preferred_gateway, fall back to config default.
 *
 * The dispatcher is the single place that knows about the gateway
 * registry; callers (BookingService, IyzicoWebhookController, …) ask
 * for a gateway by slug and stay decoupled.
 */
class PaymentDispatcher
{
    public function __construct(
        /** @var array<string, PaymentGateway> Keyed by gateway slug */
        private readonly array $gateways,
        private readonly string $default,
    ) {
    }

    public function gateway(?string $slug = null): PaymentGateway
    {
        $key = $slug ?: $this->default;

        if (! isset($this->gateways[$key])) {
            throw new PaymentGatewayException("Unknown payment gateway: {$key}");
        }

        return $this->gateways[$key];
    }

    /** @return array<int, string> */
    public function knownSlugs(): array
    {
        return array_keys($this->gateways);
    }
}
