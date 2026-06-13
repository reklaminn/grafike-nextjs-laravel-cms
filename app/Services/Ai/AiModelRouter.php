<?php

namespace App\Services\Ai;

use App\Models\Tenant;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

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
        private readonly AiQuotaService $quota,
        private readonly AiPromptCache $promptCache,
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
        ?string $imageBase64 = null,
        string $imageMimeType = 'image/jpeg',
    ): AiResponse {
        $cfg = $this->resolve($feature, $tier, $tenant);

        // Enforce monthly quota BEFORE making the API call. BYOK tenants
        // are exempt; the service handles that internally.
        $this->quota->assertWithinQuota($tenant, estimatedTokens: $cfg['max_tokens']);

        $message = $imageBase64
            ? AiMessage::userWithImage($prompt, $imageBase64, $imageMimeType)
            : AiMessage::user($prompt);

        $request = new AiRequest(
            model:          $cfg['model'],
            messages:       [$message],
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

        return $this->dispatchAndRecord($feature, $cfg, $request, $tenant);
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

        $this->quota->assertWithinQuota($tenant, estimatedTokens: $request->maxTokens);

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

        return $this->dispatchAndRecord($feature, $cfg, $effective, $tenant);
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
     * Per-feature response-cache TTL in minutes (0 = caching disabled for
     * this feature). FAZ 3.5.
     */
    private function featureCacheTtl(string $feature): int
    {
        return (int) ($this->config['features'][$feature]['cache_ttl'] ?? 0);
    }

    /**
     * Run the request against the primary provider; on transient failure
     * walk down the fallback chain; in either case record exactly one
     * AiUsage row for billing/dashboard purposes.
     *
     * BYOK accounting: only the primary call counts as BYOK. If a tenant
     * supplied an Anthropic key and we fall back to OpenAI, the OpenAI
     * call uses the system key, so the row is logged with byok=false and
     * (importantly) DOES count toward the agency quota — but this is rare
     * by design (only transient primary failures trigger it).
     */
    private function dispatchAndRecord(
        string $feature,
        array $cfg,
        AiRequest $request,
        ?Tenant $tenant,
    ): AiResponse {
        $ttl = $this->featureCacheTtl($feature);

        // FAZ 3.5 — yanıt önbelleği. Cache açık ve bu feature için TTL>0 ise
        // önce önbelleğe bak; isabette API çağrısı YOK, ücretsiz satır yaz.
        // Miss/disabled durumunda gerçek dispatch çalışır (kendi satırını yazar).
        if ($this->promptCache->enabled() && $ttl > 0) {
            $result = $this->promptCache->remember(
                provider:   $cfg['provider'],
                request:    $request,
                tenant:     $tenant,
                ttlMinutes: $ttl,
                generate:   fn () => $this->dispatch($feature, $cfg, $request, $tenant),
            );

            if ($result['hit']) {
                $this->quota->recordCacheHit(
                    tenant:        $tenant,
                    feature:       $feature,
                    response:      $result['response'],
                    byok:          (bool) $cfg['byok'],
                    extraMetadata: ['tier' => $cfg['tier'], 'requested_provider' => $cfg['provider']],
                );
            }

            return $result['response'];
        }

        return $this->dispatch($feature, $cfg, $request, $tenant);
    }

    /**
     * Run the request against the primary provider with fallback chain and
     * record exactly one AiUsage row. Extracted from dispatchAndRecord so the
     * response cache (FAZ 3.5) can wrap it as the cache-miss producer.
     */
    private function dispatch(
        string $feature,
        array $cfg,
        AiRequest $request,
        ?Tenant $tenant,
    ): AiResponse {
        $primaryProvider = $cfg['provider'];
        $byokOnPrimary   = (bool) $cfg['byok'];
        $tier            = $cfg['tier'];
        $fallback        = $this->config['fallback'] ?? ['enabled' => false];
        $baseMeta        = ['tier' => $tier, 'requested_provider' => $primaryProvider];

        // No fallback configured → single shot, record outcome, propagate.
        if (! ($fallback['enabled'] ?? false)) {
            try {
                $response = $this->manager->provider($primaryProvider)->generate($request);
                $this->quota->recordSuccess(
                    tenant:        $tenant,
                    feature:       $feature,
                    response:      $response,
                    byok:          $byokOnPrimary,
                    fallbackUsed:  false,
                    extraMetadata: $baseMeta,
                );

                return $response;
            } catch (Throwable $e) {
                $this->quota->recordFailure(
                    tenant:        $tenant,
                    feature:       $feature,
                    provider:      $primaryProvider,
                    model:         $request->model,
                    error:         $e,
                    byok:          $byokOnPrimary,
                    extraMetadata: $baseMeta,
                );
                throw $e;
            }
        }

        $chain        = $this->buildChain($primaryProvider, (array) ($fallback['chain'] ?? []));
        $maxAttempts  = (int) ($fallback['max_attempts'] ?? 3);
        $retryOn      = (array) ($fallback['on_status_codes'] ?? []);
        $lastError    = null;
        $lastProvider = $primaryProvider;
        $lastModel    = $request->model;
        $attemptCount = 0;

        foreach ($chain as $providerName) {
            if ($attemptCount >= $maxAttempts) {
                break;
            }
            $attemptCount++;

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
                    'fallback_used'      => ! $isPrimary,
                    'attempted_provider' => $providerName,
                ]),
                apiKeyOverride: $isPrimary ? $request->apiKeyOverride : null,
            );
            $lastProvider = $providerName;
            $lastModel    = $effective->model;

            try {
                $response = $this->manager->provider($providerName)->generate($effective);

                // Only the primary keeps the BYOK flag; fallback servers used
                // the agency's system key, so they DO count toward the quota.
                $this->quota->recordSuccess(
                    tenant:        $tenant,
                    feature:       $feature,
                    response:      $response,
                    byok:          $isPrimary && $byokOnPrimary,
                    fallbackUsed:  ! $isPrimary,
                    extraMetadata: $baseMeta + ['served_by' => $providerName],
                );

                return $response;
            } catch (AiProviderException $e) {
                $lastError = $e;

                // Non-recoverable (auth, validation, etc) → record + stop.
                if ($e->statusCode !== null && ! in_array($e->statusCode, $retryOn, true)) {
                    $this->quota->recordFailure(
                        tenant:        $tenant,
                        feature:       $feature,
                        provider:      $providerName,
                        model:         $effective->model,
                        error:         $e,
                        byok:          $isPrimary && $byokOnPrimary,
                        extraMetadata: $baseMeta + ['attempted_provider' => $providerName],
                    );
                    throw $e;
                }

                Log::warning('AI provider fallback', [
                    'failed_provider' => $providerName,
                    'status'          => $e->statusCode,
                    'message'         => $e->getMessage(),
                    'next_in_chain'   => $chain[array_search($providerName, $chain, true) + 1] ?? null,
                ]);
                // Try next provider in chain (no row yet — we'll record on
                // either success of next provider, or final exhaustion).
            }
        }

        // Whole chain failed — record one row tagged with the last attempted
        // provider so the dashboard can show "rate limit storm on day X".
        $finalError = $lastError ?? new AiProviderException(
            'All AI providers in the fallback chain failed.',
            $primaryProvider,
        );
        $this->quota->recordFailure(
            tenant:        $tenant,
            feature:       $feature,
            provider:      $lastProvider,
            model:         $lastModel,
            error:         $finalError,
            byok:          false, // BYOK irrelevant when nothing succeeded
            extraMetadata: $baseMeta + ['all_providers_failed' => true, 'attempts' => $attemptCount],
        );
        throw $finalError;
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
