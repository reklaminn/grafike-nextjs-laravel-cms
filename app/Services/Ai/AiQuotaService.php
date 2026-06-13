<?php

namespace App\Services\Ai;

use App\Models\AiUsage;
use App\Models\Tenant;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Quota enforcement + usage recording for AI calls.
 *
 * Two responsibilities:
 *   1. `assertWithinQuota($tenant)` — throws AiQuotaExceededException
 *      when a tenant is at or above one of the three monthly limits
 *      (requests / tokens / cost). BYOK tenants are exempt.
 *   2. `record(...)` — write one row to the ai_usage table after each
 *      call. Both success and failure are recorded so the dashboard
 *      can surface error rates.
 *
 * Cost calculation uses config('ai.pricing') — vendor's USD-per-million-
 * tokens rates. Models not listed in pricing cost $0 in our accounting,
 * but token counts are still recorded.
 */
class AiQuotaService
{
    public function __construct(
        private readonly array $plans,
        private readonly string $defaultPlan,
        private readonly array $pricing,
    ) {
    }

    /**
     * Current month's usage for a tenant, summed from billable rows only.
     * BYOK rows are excluded — they don't count against the agency.
     *
     * @return array{requests:int, tokens:int, cost_usd:float, period:string}
     */
    public function currentUsage(Tenant $tenant, ?Carbon $month = null): array
    {
        $monthStart = ($month ?? CarbonImmutable::now()->startOfMonth())->copy()->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        $row = AiUsage::query()
            ->billable()
            ->where('tenant_id', (string) $tenant->getKey())
            ->betweenDates($monthStart, $monthEnd)
            ->selectRaw('COUNT(*) AS req, COALESCE(SUM(total_tokens), 0) AS tok, COALESCE(SUM(cost_usd), 0) AS cost')
            ->first();

        return [
            'requests' => (int) ($row->req ?? 0),
            'tokens'   => (int) ($row->tok ?? 0),
            'cost_usd' => round((float) ($row->cost ?? 0), 6),
            'period'   => $monthStart->format('Y-m'),
        ];
    }

    /**
     * Plan config for a tenant. Falls back to the default plan when the
     * tenant's stored plan name doesn't exist in config (e.g. plan was
     * renamed/retired between releases).
     *
     * @return array{label:string, monthly_requests:?int, monthly_tokens:?int, monthly_cost_usd:?float}
     */
    public function planFor(Tenant $tenant): array
    {
        $planName = $tenant->aiPlan();
        $plan     = $this->plans[$planName] ?? $this->plans[$this->defaultPlan] ?? [];

        return [
            'name'             => $planName,
            'label'            => $plan['label']            ?? ucfirst($planName),
            'monthly_requests' => $plan['monthly_requests'] ?? null,
            'monthly_tokens'   => $plan['monthly_tokens']   ?? null,
            'monthly_cost_usd' => isset($plan['monthly_cost_usd']) ? (float) $plan['monthly_cost_usd'] : null,
        ];
    }

    /**
     * Returns true when the tenant is exempt from quota (BYOK active AND
     * a key is on file for the preferred provider). BYOK requests still
     * get logged via record(), but bypass this check.
     */
    public function isExempt(?Tenant $tenant): bool
    {
        if (! $tenant) {
            return true; // no tenant context → don't enforce
        }
        if (! $tenant->isUsingByokAi()) {
            return false;
        }
        $provider = $tenant->preferredAiProvider() ?? 'anthropic';

        return $tenant->hasAiApiKey($provider);
    }

    /**
     * Throws AiQuotaExceededException when the tenant has reached any of
     * the three caps for the current month. Use BEFORE making the API call.
     *
     * The `$estimatedTokens` argument lets callers pre-check a known-large
     * request so we don't make the API call only to discover we should
     * have blocked it. Pass 0 for a pure "current usage vs limits" check.
     */
    public function assertWithinQuota(?Tenant $tenant, int $estimatedTokens = 0): void
    {
        if ($this->isExempt($tenant)) {
            return;
        }
        /** @var Tenant $tenant — narrowed by isExempt() returning false above */

        $plan  = $this->planFor($tenant);
        $usage = $this->currentUsage($tenant);
        $id    = (string) $tenant->getKey();

        if ($plan['monthly_requests'] !== null && $usage['requests'] >= $plan['monthly_requests']) {
            throw AiQuotaExceededException::requests($id, $plan['name'], $usage['requests'], (int) $plan['monthly_requests']);
        }
        if ($plan['monthly_tokens'] !== null && ($usage['tokens'] + $estimatedTokens) > $plan['monthly_tokens']) {
            throw AiQuotaExceededException::tokens($id, $plan['name'], $usage['tokens'], (int) $plan['monthly_tokens']);
        }
        if ($plan['monthly_cost_usd'] !== null && $usage['cost_usd'] >= $plan['monthly_cost_usd']) {
            throw AiQuotaExceededException::cost($id, $plan['name'], $usage['cost_usd'], (float) $plan['monthly_cost_usd']);
        }
    }

    /**
     * Calculate USD cost for a completed response using the pricing matrix.
     * Returns 0.0 when the model isn't listed (acceptable — counters still
     * record token counts and the dashboard surfaces "unpriced" calls).
     *
     * Lookup order:
     *   1. Exact match  → e.g. "claude-haiku-4-5"
     *   2. Prefix match → e.g. "claude-haiku-4-5-20250514" matches "claude-haiku-4-5"
     *      (Anthropic returns versioned IDs for alias model names)
     */
    public function calculateCost(string $provider, string $model, int $inputTokens, int $outputTokens): float
    {
        $providerRates = $this->pricing[$provider] ?? [];

        // 1. Exact match
        $rate = $providerRates[$model] ?? null;

        // 2. Prefix match — API may return versioned IDs like "claude-haiku-4-5-20250514"
        if (! is_array($rate)) {
            foreach ($providerRates as $configModel => $configRate) {
                if (str_starts_with($model, $configModel)) {
                    $rate = $configRate;
                    break;
                }
            }
        }

        if (! is_array($rate)) {
            return 0.0;
        }

        return round(
            ($inputTokens * ((float) ($rate['input'] ?? 0)) + $outputTokens * ((float) ($rate['output'] ?? 0))) / 1_000_000,
            6,
        );
    }

    /**
     * Persist one usage row from a successful AI response.
     */
    public function recordSuccess(
        ?Tenant $tenant,
        string $feature,
        AiResponse $response,
        bool $byok = false,
        bool $fallbackUsed = false,
        array $extraMetadata = [],
    ): AiUsage {
        return AiUsage::create([
            'tenant_id'           => $tenant?->getKey() ? (string) $tenant->getKey() : null,
            'feature'             => $feature,
            'provider'            => $response->provider,
            'model'               => $response->model,
            'tier'                => $response->raw['_tier'] ?? ($extraMetadata['tier'] ?? null),
            'input_tokens'        => $response->usage->inputTokens,
            'output_tokens'       => $response->usage->outputTokens,
            'cached_input_tokens' => $response->usage->cachedInputTokens,
            'total_tokens'        => $response->usage->totalTokens(),
            'cost_usd'            => $this->calculateCost(
                $response->provider,
                $response->model,
                $response->usage->inputTokens,
                $response->usage->outputTokens,
            ),
            'byok'                => $byok,
            'fallback_used'       => $fallbackUsed,
            'success'             => true,
            'metadata'            => $extraMetadata ?: null,
            'created_at'          => now(),
        ]);
    }

    /**
     * Persist a usage row for a CACHE HIT (FAZ 3.5). No API call was made,
     * so cost and billable tokens are 0 — the response came straight from
     * the response cache. We still write a row (success=true) so the
     * dashboard counts the request and surfaces `cache_hit` + the savings
     * we avoided paying (`saved_tokens` / `saved_cost_usd` in metadata).
     */
    public function recordCacheHit(
        ?Tenant $tenant,
        string $feature,
        AiResponse $response,
        bool $byok = false,
        array $extraMetadata = [],
    ): AiUsage {
        $savedCost = $this->calculateCost(
            $response->provider,
            $response->model,
            $response->usage->inputTokens,
            $response->usage->outputTokens,
        );

        return AiUsage::create([
            'tenant_id'     => $tenant?->getKey() ? (string) $tenant->getKey() : null,
            'feature'       => $feature,
            'provider'      => $response->provider,
            'model'         => $response->model,
            'tier'          => $extraMetadata['tier'] ?? null,
            'input_tokens'  => 0,
            'output_tokens' => 0,
            'total_tokens'  => 0,
            'cost_usd'      => 0,
            'byok'          => $byok,
            'fallback_used' => false,
            'success'       => true,
            'metadata'      => array_merge($extraMetadata, [
                'cache_hit'      => true,
                'saved_tokens'   => $response->usage->totalTokens(),
                'saved_cost_usd' => $savedCost,
            ]),
            'created_at'    => now(),
        ]);
    }

    /**
     * Persist one usage row for a failed call. Token counters are zero
     * since the API didn't return; cost is 0 too. We still record the
     * row so the dashboard can show error rates and rate-limit incidents.
     */
    public function recordFailure(
        ?Tenant $tenant,
        string $feature,
        string $provider,
        string $model,
        Throwable $error,
        bool $byok = false,
        array $extraMetadata = [],
    ): AiUsage {
        return AiUsage::create([
            'tenant_id'     => $tenant?->getKey() ? (string) $tenant->getKey() : null,
            'feature'       => $feature,
            'provider'      => $provider,
            'model'         => $model,
            'tier'          => $extraMetadata['tier'] ?? null,
            'input_tokens'  => 0,
            'output_tokens' => 0,
            'total_tokens'  => 0,
            'cost_usd'      => 0,
            'byok'          => $byok,
            'fallback_used' => $extraMetadata['fallback_used'] ?? false,
            'success'       => false,
            'error_message' => mb_substr($error->getMessage(), 0, 1000),
            'metadata'      => $extraMetadata ?: null,
            'created_at'    => now(),
        ]);
    }
}
