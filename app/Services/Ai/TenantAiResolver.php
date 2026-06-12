<?php

namespace App\Services\Ai;

use App\Models\Tenant;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Dtos\AiResponse;

/**
 * Bridges a tenant's BYOK / preferred-provider settings with the raw
 * AiManager. Two responsibilities:
 *
 *   1. resolve()  — decide which provider, model, and API key to use for
 *                   a given tenant + tier ("simple" | "complex").
 *   2. generate() — convenience wrapper that runs the full lookup and
 *                   forwards to the provider, returning the AiResponse.
 *
 * When a tenant has `use_byok = true` AND a key on file for the chosen
 * provider, the system uses the tenant's key (quota does not count toward
 * the agency). Otherwise the system-level key from config/ai.php is used.
 */
class TenantAiResolver
{
    public function __construct(private readonly AiManager $manager)
    {
    }

    /**
     * @return array{provider:string, model:string, api_key:?string, byok:bool}
     */
    public function resolve(?Tenant $tenant, string $tier = 'simple'): array
    {
        $defaultProvider = $this->manager->defaultProvider();

        // BYOK path: tenant supplied a key, use their preferred provider/model.
        if ($tenant && $tenant->isUsingByokAi()) {
            $provider = $tenant->preferredAiProvider() ?? $defaultProvider;
            $apiKey   = $tenant->aiApiKey($provider);

            if ($apiKey) {
                return [
                    'provider' => $provider,
                    'model'    => $tenant->preferredAiModel($provider, $tier)
                                  ?? $this->manager->resolveModel($tier, $provider),
                    'api_key'  => $apiKey,
                    'byok'     => true,
                ];
            }
            // No key on file → silently fall through to system defaults
            // rather than failing. (UI surfaces the missing-key state.)
        }

        return [
            'provider' => $defaultProvider,
            'model'    => $this->manager->resolveModel($tier, $defaultProvider),
            'api_key'  => null,
            'byok'     => false,
        ];
    }

    /**
     * One-shot: resolve, augment the AiRequest with tenant-specific model +
     * API key (if not already specified), and delegate to the provider.
     *
     * Caller's explicit `model` and `apiKeyOverride` always win over the
     * tenant defaults so per-call overrides remain possible.
     */
    public function generate(?Tenant $tenant, AiRequest $request, string $tier = 'simple'): AiResponse
    {
        $config = $this->resolve($tenant, $tier);

        $effective = new AiRequest(
            model:          $request->model !== '' ? $request->model : $config['model'],
            messages:       $request->messages,
            system:         $request->system,
            maxTokens:      $request->maxTokens,
            temperature:    $request->temperature,
            metadata:       array_merge($request->metadata, [
                'tenant_id'   => $tenant?->getKey(),
                'tier'        => $tier,
                'byok'        => $config['byok'],
                'resolved_at' => now()->toIso8601String(),
            ]),
            apiKeyOverride: $request->apiKeyOverride ?: $config['api_key'],
        );

        return $this->manager->provider($config['provider'])->generate($effective);
    }
}
