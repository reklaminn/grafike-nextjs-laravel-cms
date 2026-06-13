<?php

namespace Tests\Unit\Services\Ai;

use App\Models\AiUsage;
use App\Services\Ai\AiUsageReporter;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AiUsageReporterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reuse the same in-memory sqlite for the central connection so
        // AiUsage (which is bound to `central`) can read/write.
        config([
            'database.connections.central' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        DB::purge('central');

        Schema::connection('central')->create('ai_usage', function ($t) {
            $t->id();
            $t->string('tenant_id', 100)->nullable()->index();
            $t->string('feature', 64)->index();
            $t->string('provider', 32);
            $t->string('model', 128);
            $t->string('tier', 16)->nullable();
            $t->unsignedInteger('input_tokens')->default(0);
            $t->unsignedInteger('output_tokens')->default(0);
            $t->unsignedInteger('cached_input_tokens')->nullable();
            $t->unsignedInteger('total_tokens')->default(0);
            $t->decimal('cost_usd', 12, 6)->default(0);
            $t->boolean('byok')->default(false);
            $t->boolean('fallback_used')->default(false);
            $t->boolean('success')->default(true);
            $t->text('error_message')->nullable();
            $t->json('metadata')->nullable();
            $t->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Seed a usage row at an explicit datetime.
     */
    private function seedRow(array $attrs = []): AiUsage
    {
        return AiUsage::create(array_merge([
            'tenant_id'     => 'acme',
            'feature'       => 'seo.meta',
            'provider'      => 'anthropic',
            'model'         => 'claude-haiku-4-5',
            'input_tokens'  => 100,
            'output_tokens' => 50,
            'total_tokens'  => 150,
            'cost_usd'      => 0.001,
            'byok'          => false,
            'fallback_used' => false,
            'success'       => true,
            'created_at'    => now(),
        ], $attrs));
    }

    // ───────────────────────────────────────────────────────────────

    public function test_totals_for_current_month_excludes_byok_from_cost(): void
    {
        $this->seedRow(['cost_usd' => 0.010, 'byok' => false]);
        $this->seedRow(['cost_usd' => 5.000, 'byok' => true]); // tenant-paid, must NOT count

        $totals = (new AiUsageReporter())->totalsForCurrentMonth();

        $this->assertSame(1, $totals['requests']);
        $this->assertEqualsWithDelta(0.010, $totals['cost_usd'], 0.0001);
    }

    public function test_totals_compute_byok_ratio_from_successful_rows(): void
    {
        $this->seedRow(['byok' => true]);
        $this->seedRow(['byok' => true]);
        $this->seedRow(['byok' => false]);
        $this->seedRow(['byok' => false, 'success' => false]); // failures excluded

        $totals = (new AiUsageReporter())->totalsForCurrentMonth();

        // 2 BYOK / 3 successful = 66.67%
        $this->assertEqualsWithDelta(66.67, $totals['byok_ratio'], 0.05);
    }

    public function test_totals_track_error_rate(): void
    {
        $this->seedRow();
        $this->seedRow();
        $this->seedRow(['success' => false, 'error_message' => 'rate limit']);
        $this->seedRow(['success' => false, 'error_message' => 'server down']);

        $totals = (new AiUsageReporter())->totalsForCurrentMonth();
        // 2 failed / 4 total = 50%
        $this->assertEqualsWithDelta(50.0, $totals['error_rate'], 0.01);
    }

    public function test_totals_count_fallback_events(): void
    {
        $this->seedRow();
        $this->seedRow(['fallback_used' => true]);
        $this->seedRow(['fallback_used' => true]);

        $this->assertSame(2, (new AiUsageReporter())->totalsForCurrentMonth()['fallback_count']);
    }

    public function test_totals_compute_month_over_month_delta(): void
    {
        $prevMonth = CarbonImmutable::now()->subMonth()->startOfMonth()->addDays(2);
        $this->seedRow(['cost_usd' => 0.10, 'created_at' => $prevMonth]);
        $this->seedRow(['cost_usd' => 0.20]); // this month

        $totals = (new AiUsageReporter())->totalsForCurrentMonth();

        // (0.20 - 0.10) / 0.10 * 100 = 100.0
        $this->assertEqualsWithDelta(100.0, $totals['cost_delta_pct'], 0.1);
    }

    public function test_totals_handle_zero_prev_baseline(): void
    {
        // No previous month rows; this month has 1 → delta is null (no baseline)
        $this->seedRow(['cost_usd' => 0.05]);

        $totals = (new AiUsageReporter())->totalsForCurrentMonth();

        $this->assertNull($totals['cost_delta_pct']);
    }

    public function test_daily_trend_returns_zero_filled_30_day_window(): void
    {
        // One row 5 days ago and one today
        $this->seedRow(['created_at' => CarbonImmutable::now()->subDays(5)]);
        $this->seedRow(); // today

        $trend = (new AiUsageReporter())->dailyTrend(null, days: 30);

        $this->assertCount(30, $trend);
        $this->assertGreaterThan(0, array_sum(array_column($trend, 'requests')));
        // Specifically: 2 days have requests, the other 28 have 0
        $nonZero = array_filter($trend, fn ($d) => $d['requests'] > 0);
        $this->assertCount(2, $nonZero);
    }

    public function test_feature_breakdown_groups_and_sorts(): void
    {
        $this->seedRow(['feature' => 'seo.meta',    'cost_usd' => 0.005]);
        $this->seedRow(['feature' => 'seo.meta',    'cost_usd' => 0.005]);
        $this->seedRow(['feature' => 'page.create', 'cost_usd' => 0.080]);

        $rows = (new AiUsageReporter())->featureBreakdown();

        $this->assertCount(2, $rows);
        // Sorted by cost desc
        $this->assertSame('page.create', $rows[0]['feature']);
        $this->assertSame('seo.meta',    $rows[1]['feature']);
        $this->assertSame(2, $rows[1]['requests']);
        // seo.meta: 2 requests out of 3 total = 66.7%
        $this->assertEqualsWithDelta(66.7, $rows[1]['pct'], 0.3);
    }

    public function test_provider_breakdown_tracks_fallback_pct(): void
    {
        $this->seedRow(['provider' => 'anthropic']);
        $this->seedRow(['provider' => 'anthropic']);
        $this->seedRow(['provider' => 'openai', 'fallback_used' => true]); // served by fallback

        $rows = (new AiUsageReporter())->providerBreakdown();

        $anthropic = collect($rows)->firstWhere('provider', 'anthropic');
        $openai    = collect($rows)->firstWhere('provider', 'openai');

        $this->assertSame(2, $anthropic['requests']);
        $this->assertEqualsWithDelta(0.0, $anthropic['fallback_pct'], 0.01);
        $this->assertSame(1, $openai['requests']);
        $this->assertEqualsWithDelta(100.0, $openai['fallback_pct'], 0.01);
    }

    public function test_top_expensive_calls_returns_sorted_by_cost(): void
    {
        $this->seedRow(['cost_usd' => 0.01]);
        $this->seedRow(['cost_usd' => 0.50]);
        $this->seedRow(['cost_usd' => 0.05]);

        $top = (new AiUsageReporter())->topExpensiveCalls(null, limit: 2);

        $this->assertCount(2, $top);
        $this->assertEqualsWithDelta(0.50, (float) $top[0]->cost_usd, 0.0001);
        $this->assertEqualsWithDelta(0.05, (float) $top[1]->cost_usd, 0.0001);
    }

    public function test_top_tenants_groups_by_tenant_id_and_sorts_by_cost(): void
    {
        $this->seedRow(['tenant_id' => 'clinic1', 'cost_usd' => 0.10]);
        $this->seedRow(['tenant_id' => 'clinic1', 'cost_usd' => 0.05]);
        $this->seedRow(['tenant_id' => 'lawyer1', 'cost_usd' => 0.30]);
        // null tenant — must be skipped
        $this->seedRow(['tenant_id' => null,      'cost_usd' => 5.00]);

        $top = (new AiUsageReporter())->topTenants(limit: 5);

        $this->assertCount(2, $top);
        $this->assertSame('lawyer1', $top[0]['tenant_id']);
        $this->assertSame('clinic1', $top[1]['tenant_id']);
        $this->assertEqualsWithDelta(0.15, $top[1]['cost'], 0.0001);
    }

    public function test_tenant_scoping_filters_results(): void
    {
        $this->seedRow(['tenant_id' => 'a', 'cost_usd' => 0.10]);
        $this->seedRow(['tenant_id' => 'b', 'cost_usd' => 0.20]);

        $tenantA = new \App\Models\Tenant();
        $tenantA->id = 'a';

        $totals = (new AiUsageReporter())->totalsForCurrentMonth($tenantA);
        $this->assertSame(1, $totals['requests']);
        $this->assertEqualsWithDelta(0.10, $totals['cost_usd'], 0.0001);
    }

    public function test_cache_stats_reports_hit_rate_and_savings(): void
    {
        // 2 real API calls + 3 cache hits.
        $this->seedRow(['cost_usd' => 0.001]);
        $this->seedRow(['cost_usd' => 0.001]);
        for ($i = 0; $i < 3; $i++) {
            $this->seedRow([
                'input_tokens'  => 0,
                'output_tokens' => 0,
                'total_tokens'  => 0,
                'cost_usd'      => 0,
                'metadata'      => ['cache_hit' => true, 'saved_tokens' => 150, 'saved_cost_usd' => 0.001],
            ]);
        }

        $stats = (new AiUsageReporter())->cacheStats();

        $this->assertSame(3, $stats['hits']);
        $this->assertSame(5, $stats['requests']);
        $this->assertEqualsWithDelta(60.0, $stats['hit_rate'], 0.01);   // 3/5
        $this->assertSame(450, $stats['saved_tokens']);                 // 3 × 150
        $this->assertEqualsWithDelta(0.003, $stats['saved_cost_usd'], 0.0001);
    }

    public function test_cache_stats_zero_when_no_hits(): void
    {
        $this->seedRow(['cost_usd' => 0.001]);

        $stats = (new AiUsageReporter())->cacheStats();
        $this->assertSame(0, $stats['hits']);
        $this->assertEqualsWithDelta(0.0, $stats['hit_rate'], 0.01);
        $this->assertEqualsWithDelta(0.0, $stats['saved_cost_usd'], 0.0001);
    }
}
