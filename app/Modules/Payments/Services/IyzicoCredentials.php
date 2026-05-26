<?php

declare(strict_types=1);

namespace App\Modules\Payments\Services;

/**
 * Plain-credential bag, returned by IyzicoBYOKResolver only.
 *
 * Marked `final` + readonly so once instantiated the credentials cannot
 * be mutated.  Caller scope keeps the lifetime short (single HTTP call).
 */
final class IyzicoCredentials
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $apiKey,
        public readonly string $secretKey,
        public readonly bool $sandbox,
    ) {
    }

    public function baseUrl(): string
    {
        return $this->sandbox
            ? (string) config('payments.gateways.iyzico.base_url_sandbox')
            : (string) config('payments.gateways.iyzico.base_url_production');
    }
}
