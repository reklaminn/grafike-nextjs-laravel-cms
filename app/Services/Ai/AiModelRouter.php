<?php

namespace App\Services\Ai;

use App\Models\Tenant;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * High-level entry point for AI calls. Combines:
 *
 *   1. **Feature-driven defaults** — each feature key in config('ai.features')
 *      declares its tier ("simple" | "complex"), max_tokens, temperature.
 *      Callers pass the feature key and stay agnostic to the actual model.
 *
 *   2. **Tenant-aware provider/model/key selection** — delegated to
 *      TenantAiResolver so BYOK and per-tenant model overrides keep working.
 *
 *   3. **Provider fallback** — when the primary provider returns a transient
 *      failure (rate limit, 5xx, timeout) the router retries with the next
 *      provider in `config('ai.fallback.chain')`. Non-recoverable errors
 *      (auth, validation) bubble out without further retries.
 *
 * Typical usage from an admin endpoint:
 *
 *   $response = $router->generate(
 *       feature: 'seo.meta',
 *       prompt: 'Sayfa içeriği: …',
 *       system: 'Türkçe SEO meta tag\'leri üret.',
 *       tenant: tenant(),
 *   );
 */
class AiModelRouter
{
    public function __construct(
        private readonly AiManager $manager,
        private readonly TenantAiResolver $tenantResolver,
        private readonly array $config,
    ) {
    }

    /**
     * Resolve everything required to make a call without actually firing it.
     * Useful for logging, dry-runs, and quota pre-checks (FAZ 3.4).
     *
     * @return array{
     *     provider:string, model:string, api_key:?string, byok:bool,
     *     tier:string, feature:string, max_tokens:int, temperature:float
     * }
     */
    public function resolve(string $feature, ?string $tierOverride = null, ?Tenant $tenant = null): array
    {
        $featureCfg = $this->featureConfig($feature);
        $tier       = $tierOverride ?: $featureCfg['tier'];

        $tenantConfig = $this->tenantResolver->resolve($tenant, $tier);

        return [
            'provider'    => $tenantConfig['provider'],
            'model'       => $tenantConfig['model'],
            'api_key'     => $tenantConfig['api_key'],
            'byok'        => $tenantConfig['byok'],
            'tier'        => $tier,
            'feature'     => $feature,
            'max_tokens'  => (int) $featureCfg['max_tokens'],
            'temperature' => (float) $featureCfg['temperature'],
        ];
    }

    /**
     * Convenience generate() — single-turn (user prompt + optional system).
     * For multi-turn or richer payloads use generateWithRequest().
     */
    public function generate(
        string $feature,
        string $prompt,
        ?string $system = null,
        ?string $tier = null,
        ?Tenant $tenant = null,
        array $metadata = [],
    ): AiResponse {
        $cfg = $this->resolve($feature, $tier, $tenant);

        $request = new AiRequest(
            model:          $cfg['model'],
            messages:       [AiMessage::user($prompt)],
            system:         $system,
            maxTokens:      $cfg['max_tokens'],
            temperature:    $cfg['temperature'],
            metadata:       array_merge($metadata, [
                'feature'   => $feature,
                'tier'      => $cfg['tier'],
                'tenant_id' => $tenant?->getKey(),
                'byok'      => $cfg['byok'],
            ]),
            apiKeyOverride: $cfg['api_key'],
        );

        return $this->dispatch($cfg['provider'], $request, $cfg['tier']);
    }

    /**
     * For callers that already have a fully-built AiRequest (multi-turn,
     * custom temperature, etc). Router only handles provider routing and
     * fallback; AiRequest is forwarded as-is.
     */
    public function generateWithRequest(
        string $feature,
        AiRequest $request,
        ?string $tier = null,
        ?Tenant $tenant = null,
    ): AiResponse {
        $cfg = $this->resolve($feature, $tier, $tenant);

        // If the caller did not specify a model, fill it from the routed tier.
        $effective = $request->model !== ''
            ? $request
            : new AiRequest(
                model:          $cfg['model'],
                messages:       $request->messages,
                system:         $request->system,
                maxTokens:      $request->maxTokens,
                temperature:    $request->temperature,
                metadata:       $request->metadata,
                apiKeyOverride: $request->apiKeyOverride ?: $cfg['api_key'],
            );

        return $this->dispatch($cfg['provider'], $effective, $cfg['tier']);
    }

    /**
     * @return array{tier:string, max_tokens:int, temperature:float}
     */
    private function featureConfig(string $feature): array
    {
        $features = $this->config['features'] ?? [];
        if (! isset($features[$feature])) {
            throw new InvalidArgumentException(
                "AI feature [{$feature}] is not registered in config('ai.features')."
            );
        }
        $cfg = $features[$feature];

        return [
            'tier'        => $cfg['tier']        ?? 'simple',
            'max_tokens'  => $cfg['max_tokens']  ?? (int) ($this->config['defaults']['max_tokens']  ?? 1024),
            'temperature' => $cfg['temperature'] ?? (float) ($this->config['defaults']['temperature'] ?? 0.7),
        ];
    }

    /**
     * Run the request against the primary provider; on transient failure
     * walk down the fallback chain. Successful (or unrecoverable) responses
     * return immediately.
     */
    private function dispatch(string $primaryProvider, AiRequest $request, string $tier): AiResponse
    {
        $fallback = $this->config['fallback'] ?? ['enabled' => false];

        // No fallback configured → single shot, propagate any exception.
        if (! ($fallback['enabled'] ?? false)) {
            return $this->manager->provider($primaryProvider)->generate($request);
        }

        $chain        = $this->buildChain($primaryProvider, (array) ($fallback['chain'] ?? []));
        $maxAttempts  = (int) ($fallback['max_attempts'] ?? 3);
        $retryOn      = (array) ($fallback['on_status_codes'] ?? []);
        $lastError    = null;
        $attemptCount = 0;

        foreach ($chain as $providerName) {
            if ($attemptCount >= $maxAttempts) {
                break;
            }
            $attemptCount++;

            // Re-resolve model + key per provider. BYOK keys are scoped to
            // the primary provider; fallbacks use the system key for that
            // vendor so the tenant is never charged for an unfamiliar one.
            $isPrimary = $providerName === $primaryProvider;
            $effective = new AiRequest(
                model:          $isPrimary
                    ? $request->model
                    : $this->safeResolveModel($tier, $providerName, $request->model),
                messages:       $request->messages,
                system:         $request->system,
                maxTokens:      $request->maxTokens,
                temperature:    $request->temperature,
                metadata:       array_merge($request->metadata, [
                    'fallback_used' => ! $isPrimary,
                    'attempted_provider' => $providerName,
                ]),
                apiKeyOverride: $isPrimary ? $request->apiKeyOverride : null,
            );

            try {
                return $this->manager->provider($providerName)->generate($effective);
            } catch (AiProviderException $e) {
                $lastError = $e;

                // Non-recoverable (auth, validation, etc) → stop immediately.
                if ($e->statusCode !== null && ! in_array($e->statusCode, $retryOn, true)) {
                    throw $e;
                }

                Log::warning('AI provider fallback', [
                    'failed_provider' => $providerName,
                    'status'          => $e->statusCode,
                    'message'         => $e->getMessage(),
                    'next_in_chain'   => $chain[array_search($providerName, $chain, true) + 1] ?? null,
                ]);
                // Try next provider in chain.
            }
        }

        throw $lastError ?? new AiProviderException(
            'All AI providers in the fallback chain failed.',
            $primaryProvider,
        );
    }

    /**
     * @param  array<int, string>  $chain
     * @return array<int, string>
     */
    private function buildChain(string $primary, array $chain): array
    {
        // Primary first, then remaining providers in their declared order,
        // deduped. Unknown chain members are silently dropped.
        $known = array_keys($this->config['providers'] ?? []);
        $rest  = array_values(array_diff(array_intersect($chain, $known), [$primary]));

        return array_values(array_unique([$primary, ...$rest]));
    }

    private function safeResolveModel(string $tier, string $providerName, string $fallbackModel): string
    {
        try {
            return $this->manager->resolveModel($tier, $providerName);
        } catch (InvalidArgumentException) {
            return $fallbackModel; // chain provider missing tier → reuse primary's model name
        }
    }
}
