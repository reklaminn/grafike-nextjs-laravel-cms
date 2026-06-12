<?php

declare(strict_types=1);

namespace App\Modules\Payments\Services;

use App\Models\Tenant;
use App\Modules\Payments\Exceptions\MissingBYOKKeysException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * Tenant.data.iyzico_keys → in-memory decrypted credentials.
 *
 * Mirrors the existing AI BYOK pattern (Tenant::aiApiKey()):
 *   • Keys are stored encrypted via Laravel's Crypt facade (APP_KEY).
 *   • Decryption happens at call time only — no caching, no statics.
 *   • Resolver is the ONLY place that calls Crypt::decryptString on
 *     these keys; gateways receive the plain values through the
 *     IyzicoCredentials value object and never read tenant.data themselves.
 *
 * Shape on disk:
 *   data.iyzico_keys: {
 *     api_key:    "<encrypted>",
 *     secret_key: "<encrypted>",
 *     sandbox:    true | false
 *   }
 */
class IyzicoBYOKResolver
{
    public function resolve(Tenant|BaseTenant $tenant): IyzicoCredentials
    {
        $bag = $this->extractBag($tenant);

        $apiKey    = $this->decrypt($bag['api_key']    ?? null);
        $secretKey = $this->decrypt($bag['secret_key'] ?? null);

        if ($apiKey === null || $secretKey === null) {
            throw new MissingBYOKKeysException(
                'Tenant Iyzico API anahtarlarını ayarlamamış (tenant: '
                . $tenant->getTenantKey() . ').'
            );
        }

        $sandbox = (bool) ($bag['sandbox'] ?? config('payments.gateways.iyzico.default_sandbox', true));

        return new IyzicoCredentials(
            tenantId:  (string) $tenant->getTenantKey(),
            apiKey:    $apiKey,
            secretKey: $secretKey,
            sandbox:   $sandbox,
        );
    }

    /**
     * True when the tenant has SOME key on file (without decrypting).
     * Useful for admin UI badges.
     */
    public function isConfigured(Tenant|BaseTenant $tenant): bool
    {
        $bag = $this->extractBag($tenant);

        return ! empty($bag['api_key']) && ! empty($bag['secret_key']);
    }

    private function extractBag(Tenant|BaseTenant $tenant): array
    {
        $raw = $tenant->getAttribute('iyzico_keys');

        return is_array($raw) ? $raw : [];
    }

    private function decrypt(?string $encrypted): ?string
    {
        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException $e) {
            // Key rotation / corruption — surface to ops but don't leak
            // ciphertext.
            report($e);
            return null;
        }
    }
}
