<?php

namespace App\Services\Ai;

use App\Models\AiUsage;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Aggregate queries against central.ai_usage for the FAZ 3.7 dashboards.
 *
 * Two consumer audiences:
 *   - Tenant detail screen: pass $tenant → all numbers scoped to that one.
 *   - Agency global dashboard: pass null → aggregates across all tenants.
 *
 * BYOK rows are excluded from cost/request counters by default because
 * those calls bill the tenant directly, not the agency. The `byokRatio()`
 * helper reports the BYOK share separately.
 *
 * All numeric results are returned as plain arrays of scalars so views
 * (and Chart.js) can consume them without further massaging.
 */
class AiUsageReporter
{
    /**
     * KPI tiles for the top of the dashboard.
     *
     * @return array{
     *     period:string,
     *     requests:int, requests_prev:int, requests_delta_pct:?float,
     *     tokens:int,   tokens_prev:int,   tokens_delta_pct:?float,
     *     cost_usd:float, cost_prev:float, cost_delta_pct:?float,
     *     error_rate:float, byok_ratio:float, fallback_count:int
     * }
     */
    public function totalsForCurrentMonth(?Tenant $tenant = null): array
    {
        $now      = CarbonImmutable::now();
        $thisStart = $now->startOfMonth();
        $thisEnd   = $now->endOfMonth();
        $prevStart = $now->subMonth()->startOfMonth();
        $prevEnd   = $now->subMonth()->endOfMonth();

        $this_ = $this->aggregateBetween($tenant, $thisStart, $thisEnd);
        $prev_ = $this->aggregateBetween($tenant, $prevStart, $prevEnd);

        // Error rate from ALL rows (including failures), not just billable
        $allQ = $this->scoped(AiUsage::query(), $tenant)->betweenDates($thisStart, $thisEnd);
        $totalAll  = (clone $allQ)->count();
        $failedAll = (clone $allQ)->where('success', false)->count();
        $errorRate = $totalAll > 0 ? round(($failedAll / $totalAll) * 100, 2) : 0.0;

        // BYOK share — count among ALL successful rows for the period
        $okQ      = $this->scoped(AiUsage::query(), $tenant)->where('success', true)->betweenDates($thisStart, $thisEnd);
        $okTotal  = (clone $okQ)->count();
        $okByok   = (clone $okQ)->where('byok', true)->count();
        $byokPct  = $okTotal > 0 ? round(($okByok / $okTotal) * 100, 2) : 0.0;

        // Fallback usage count (also signal — non-zero = primary had issues)
        $fallbackCount = $this->scoped(AiUsage::query(), $tenant)
            ->betweenDates($thisStart, $thisEnd)
            ->where('fallback_used', true)
            ->count();

        return [
            'period'             => $thisStart->format('Y-m'),
            'requests'           => $this_['requests'],
            'requests_prev'      => $prev_['requests'],
            'requests_delta_pct' => $this->deltaPct($prev_['requests'], $this_['requests']),
            'tokens'             => $this_['tokens'],
            'tokens_prev'        => $prev_['tokens'],
            'tokens_delta_pct'   => $this->deltaPct($prev_['tokens'], $this_['tokens']),
            'cost_usd'           => $this_['cost'],
            'cost_prev'          => $prev_['cost'],
            'cost_delta_pct'     => $this->deltaPct($prev_['cost'], $this_['cost']),
            'error_rate'         => $errorRate,
            'byok_ratio'         => $byokPct,
            'fallback_count'     => $fallbackCount,
        ];
    }

    /**
     * Per-day series for line/bar chart. Zero-fills missing days so the
     * chart x-axis is continuous.
     *
     * @return array<int, array{date:string, requests:int, tokens:int, cost:float}>
     */
    public function dailyTrend(?Tenant $tenant = null, int $days = 30): array
    {
        $end   = CarbonImmutable::now()->endOfDay();
        $start = $end->copy()->subDays($days - 1)->startOfDay();

        $rows = $this->scoped(AiUsage::query(), $tenant)
            ->billable()
            ->betweenDates($start, $end)
            ->selectRaw('DATE(created_at) AS day,
                         COUNT(*)            AS requests,
                         COALESCE(SUM(total_tokens), 0) AS tokens,
                         COALESCE(SUM(cost_usd), 0)     AS cost')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $out = [];
        $period = CarbonPeriod::create($start, '1 day', $end);
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $r   = $rows->get($key);
            $out[] = [
                'date'     => $key,
                'requests' => (int) ($r->requests ?? 0),
                'tokens'   => (int) ($r->tokens ?? 0),
                'cost'     => round((float) ($r->cost ?? 0), 6),
            ];
        }

        return $out;
    }

    /**
     * Feature → totals breakdown for the current month (or any month).
     *
     * @return array<int, array{feature:string, requests:int, tokens:int, cost:float, pct:float}>
     */
    public function featureBreakdown(?Tenant $tenant = null, int $monthsAgo = 0): array
    {
        [$start, $end] = $this->monthBounds($monthsAgo);

        $rows = $this->scoped(AiUsage::query(), $tenant)
            ->billable()
            ->betweenDates($start, $end)
            ->selectRaw('feature,
                         COUNT(*)            AS requests,
                         COALESCE(SUM(total_tokens), 0) AS tokens,
                         COALESCE(SUM(cost_usd), 0)     AS cost')
            ->groupBy('feature')
            ->orderByDesc('cost')
            ->get();

        $totalRequests = $rows->sum('requests');

        return $rows->map(fn ($r) => [
            'feature'  => (string) $r->feature,
            'requests' => (int) $r->requests,
            'tokens'   => (int) $r->tokens,
            'cost'     => round((float) $r->cost, 6),
            'pct'      => $totalRequests > 0 ? round(($r->requests / $totalRequests) * 100, 1) : 0.0,
        ])->all();
    }

    /**
     * Provider → totals breakdown (current month). Useful for spotting
     * which vendor is being hit hardest + whether OpenRouter cost-savings
     * are paying off.
     *
     * @return array<int, array{provider:string, requests:int, cost:float, fallback_pct:float}>
     */
    public function providerBreakdown(?Tenant $tenant = null, int $monthsAgo = 0): array
    {
        [$start, $end] = $this->monthBounds($monthsAgo);

        $rows = $this->scoped(AiUsage::query(), $tenant)
            ->billable()
            ->betweenDates($start, $end)
            ->selectRaw('provider,
                         COUNT(*) AS requests,
                         COALESCE(SUM(cost_usd), 0) AS cost,
                         COALESCE(SUM(CASE WHEN fallback_used = 1 THEN 1 ELSE 0 END), 0) AS fallbacks')
            ->groupBy('provider')
            ->orderByDesc('cost')
            ->get();

        return $rows->map(fn ($r) => [
            'provider'     => (string) $r->provider,
            'requests'     => (int) $r->requests,
            'cost'         => round((float) $r->cost, 6),
            'fallback_pct' => $r->requests > 0
                ? round(((int) $r->fallbacks / (int) $r->requests) * 100, 1)
                : 0.0,
        ])->all();
    }

    /**
     * The N most expensive single calls in the time window — useful for
     * prompting "this user's prompts are wasteful, help them tighten".
     *
     * @return array<int, AiUsage>
     */
    public function topExpensiveCalls(?Tenant $tenant = null, int $limit = 10, int $days = 30): array
    {
        $end   = CarbonImmutable::now();
        $start = $end->copy()->subDays($days);

        return $this->scoped(AiUsage::query(), $tenant)
            ->betweenDates($start, $end)
            ->orderByDesc('cost_usd')
            ->limit($limit)
            ->get()
            ->all();
    }

    /**
     * Last N calls. Successful + failed, newest first. For the timeline table.
     *
     * @return array<int, AiUsage>
     */
    public function recentCalls(?Tenant $tenant = null, int $limit = 20): array
    {
        return $this->scoped(AiUsage::query(), $tenant)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    /**
     * Agency-only view: top tenants by spend in the current month.
     *
     * @return array<int, array{tenant_id:string, requests:int, tokens:int, cost:float}>
     */
    public function topTenants(int $limit = 10, int $monthsAgo = 0): array
    {
        [$start, $end] = $this->monthBounds($monthsAgo);

        return AiUsage::query()
            ->billable()
            ->betweenDates($start, $end)
            ->whereNotNull('tenant_id')
            ->selectRaw('tenant_id,
                         COUNT(*)            AS requests,
                         COALESCE(SUM(total_tokens), 0) AS tokens,
                         COALESCE(SUM(cost_usd), 0)     AS cost')
            ->groupBy('tenant_id')
            ->orderByDesc('cost')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'tenant_id' => (string) $r->tenant_id,
                'requests'  => (int) $r->requests,
                'tokens'    => (int) $r->tokens,
                'cost'      => round((float) $r->cost, 6),
            ])->all();
    }

    /**
     * Response-cache effectiveness (FAZ 3.5). Cache hits are recorded as
     * success rows with cost_usd=0 and metadata.cache_hit=true; the savings
     * we avoided paying live in metadata.saved_cost_usd / saved_tokens.
     *
     * Implemented DB-agnostically (no JSON-path SQL): zero-cost successful
     * rows are a small set (cache hits + unpriced models), so we filter them
     * in PHP. Keeps the query identical on SQLite (tests) and MariaDB (prod).
     *
     * @return array{hits:int, requests:int, hit_rate:float, saved_cost_usd:float, saved_tokens:int}
     */
    public function cacheStats(?Tenant $tenant = null, int $monthsAgo = 0): array
    {
        [$start, $end] = $this->monthBounds($monthsAgo);

        $okQ = $this->scoped(AiUsage::query(), $tenant)
            ->where('success', true)
            ->betweenDates($start, $end);

        $totalRequests = (clone $okQ)->count();

        $hits = (clone $okQ)
            ->where('cost_usd', 0)
            ->get(['metadata'])
            ->filter(fn (AiUsage $r) => (bool) ($r->metadata['cache_hit'] ?? false));

        $savedCost   = $hits->sum(fn (AiUsage $r) => (float) ($r->metadata['saved_cost_usd'] ?? 0));
        $savedTokens = $hits->sum(fn (AiUsage $r) => (int) ($r->metadata['saved_tokens'] ?? 0));

        return [
            'hits'           => $hits->count(),
            'requests'       => $totalRequests,
            'hit_rate'       => $totalRequests > 0 ? round(($hits->count() / $totalRequests) * 100, 1) : 0.0,
            'saved_cost_usd' => round((float) $savedCost, 6),
            'saved_tokens'   => (int) $savedTokens,
        ];
    }

    // ────────────────────────────────────────────────────────────────────

    /**
     * @return array{requests:int, tokens:int, cost:float}
     */
    private function aggregateBetween(?Tenant $tenant, CarbonInterface $start, CarbonInterface $end): array
    {
        $row = $this->scoped(AiUsage::query(), $tenant)
            ->billable()
            ->betweenDates($start, $end)
            ->selectRaw('COUNT(*) AS requests,
                         COALESCE(SUM(total_tokens), 0) AS tokens,
                         COALESCE(SUM(cost_usd), 0)     AS cost')
            ->first();

        return [
            'requests' => (int) ($row->requests ?? 0),
            'tokens'   => (int) ($row->tokens ?? 0),
            'cost'     => round((float) ($row->cost ?? 0), 6),
        ];
    }

    private function scoped(Builder $query, ?Tenant $tenant): Builder
    {
        if ($tenant) {
            $query->where('tenant_id', (string) $tenant->getKey());
        }

        return $query;
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function monthBounds(int $monthsAgo): array
    {
        $anchor = CarbonImmutable::now()->subMonths($monthsAgo);

        return [$anchor->startOfMonth(), $anchor->endOfMonth()];
    }

    private function deltaPct(int|float $prev, int|float $now): ?float
    {
        if ($prev == 0) {
            return $now > 0 ? null : 0.0; // null = no baseline (this is the first month)
        }

        return round((($now - $prev) / $prev) * 100, 1);
    }
}
