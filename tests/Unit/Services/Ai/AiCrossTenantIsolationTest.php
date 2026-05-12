<?php

namespace Tests\Unit\Services\Ai;

use App\Models\AiUsage;
use App\Models\Tenant;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\AiUsageReporter;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\UsesSqliteCentralDb;
use Tests\TestCase;

/**
 * Cross-tenant isolation guarantees for the AI billing layer (FAZ 1.9).
 *
 * The whole point of multi-tenant + BYOK is that tenant A's usage and
 * credentials never leak into tenant B's view. Any regression that
 * relaxes the tenant_id scope here would either:
 *   - Show one tenant's costs to another (privacy breach)
 *   - Drain one tenant's quota for another's calls (billing fraud)
 *
 * Each test sets up two distinct tenants with disjoint usage and
 * verifies the boundary holds.
 */
class AiCrossTenantIsolationTest extends TestCase
{
    use UsesSqliteCentralDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpCentralSqlite();
        $this->buildCentralTable('ai_usage');
        $this->buildCentralTable('tenants');
    }

    /**
     * Insert a tenants row directly (bypass stancl events) and return a
     * model instance fit for the helper assertions.
     */
    private function makeTenant(string $id, array $data = []): Tenant
    {
        DB::connection('central')->table('tenants')->insert([
            'id'         => $id,
            'data'       => json_encode($data, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Tenant::query()->findOrFail($id);
    }

    private function seedUsage(string $tenantId, array $attrs = []): void
    {
        AiUsage::create(array_merge([
            'tenant_id'     => $tenantId,
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

    private function quotaService(): AiQuotaService
    {
        return new AiQuotaService(
            plans: [
                'free' => ['monthly_requests' => 10, 'monthly_tokens' => 1000, 'monthly_cost_usd' => 0.05, 'label' => 'Free'],
                'pro'  => ['monthly_requests' => 1000, 'monthly_tokens' => 1_000_000, 'monthly_cost_usd' => 50.0, 'label' => 'Pro'],
            ],
            defaultPlan: 'free',
            pricing: ['anthropic' => ['claude-haiku-4-5' => ['input' => 1.0, 'output' => 5.0]]],
        );
    }

    // ────────────────────────────────────────────────────────────────

    public function test_tenant_a_usage_does_not_appear_in_tenant_b_totals(): void
    {
        $a = $this->makeTenant('clinic');
        $b = $this->makeTenant('lawyer');

        // 5 calls for clinic, 2 for lawyer
        for ($i = 0; $i < 5; $i++) $this->seedUsage('clinic');
        for ($i = 0; $i < 2; $i++) $this->seedUsage('lawyer');

        $svc = $this->quotaService();

        $this->assertSame(5, $svc->currentUsage($a)['requests']);
        $this->assertSame(2, $svc->currentUsage($b)['requests']);
    }

    public function test_tenant_a_hitting_quota_does_not_block_tenant_b(): void
    {
        $a = $this->makeTenant('clinic'); // default plan: free (10 req limit)
        $b = $this->makeTenant('lawyer');

        // Burn clinic's request quota with tiny token rows so we only
        // exhaust the request count, not the 1000-token cap.
        $tiny = ['input_tokens' => 1, 'output_tokens' => 1, 'total_tokens' => 2, 'cost_usd' => 0.0001];
        for ($i = 0; $i < 10; $i++) $this->seedUsage('clinic', $tiny);

        $svc = $this->quotaService();

        // Clinic blocked
        $blocked = false;
        try { $svc->assertWithinQuota($a); }
        catch (AiQuotaExceededException $e) { $blocked = true; }
        $this->assertTrue($blocked, 'tenant A should be over quota');

        // Lawyer untouched — still 0 / 10. No-op call should not throw.
        $exception = null;
        try { $svc->assertWithinQuota($b); }
        catch (\Throwable $e) { $exception = $e; }
        $this->assertNull($exception, 'tenant B should NOT be blocked by tenant A\'s usage');
    }

    public function test_byok_usage_for_one_tenant_does_not_count_against_another(): void
    {
        $a = $this->makeTenant('byok-clinic');
        $b = $this->makeTenant('normal-lawyer');

        // BYOK for clinic — should never count
        for ($i = 0; $i < 20; $i++) $this->seedUsage('byok-clinic', ['byok' => true, 'cost_usd' => 1.00]);
        // Normal for lawyer
        for ($i = 0; $i < 3; $i++) $this->seedUsage('normal-lawyer');

        $svc = $this->quotaService();

        $this->assertSame(0, $svc->currentUsage($a)['requests'], 'BYOK rows excluded from clinic counters');
        $this->assertEqualsWithDelta(0.0, $svc->currentUsage($a)['cost_usd'], 0.0001);

        $this->assertSame(3, $svc->currentUsage($b)['requests'], 'lawyer counters unaffected by clinic activity');
    }

    public function test_failed_call_in_one_tenant_does_not_alter_others_request_count(): void
    {
        $a = $this->makeTenant('a');
        $b = $this->makeTenant('b');

        // Failed call as the service records it: 0 tokens, 0 cost.
        $this->seedUsage('a', [
            'success' => false, 'error_message' => 'rate limit',
            'input_tokens' => 0, 'output_tokens' => 0, 'total_tokens' => 0, 'cost_usd' => 0,
        ]);
        $this->seedUsage('b');

        $svc = $this->quotaService();

        // Failed call still counts toward request count (the API attempt billed).
        $this->assertSame(1, $svc->currentUsage($a)['requests']);
        $this->assertSame(1, $svc->currentUsage($b)['requests']);
        $this->assertSame(0, $svc->currentUsage($a)['tokens'], 'failed call had 0 tokens');
        $this->assertSame(150, $svc->currentUsage($b)['tokens'], 'B unaffected by A\'s failure');
    }

    public function test_reporter_topTenants_reports_each_tenant_separately(): void
    {
        $this->makeTenant('high');
        $this->makeTenant('low');

        for ($i = 0; $i < 10; $i++) $this->seedUsage('high', ['cost_usd' => 0.50]);
        for ($i = 0; $i < 2;  $i++) $this->seedUsage('low',  ['cost_usd' => 0.10]);

        $top = (new AiUsageReporter())->topTenants(limit: 5);
        $this->assertCount(2, $top);

        // Ordered by cost desc
        $this->assertSame('high', $top[0]['tenant_id']);
        $this->assertEqualsWithDelta(5.0, $top[0]['cost'], 0.01);

        $this->assertSame('low', $top[1]['tenant_id']);
        $this->assertEqualsWithDelta(0.2, $top[1]['cost'], 0.01);
    }

    public function test_dailytrend_scoped_to_tenant_doesnt_leak_other_tenants_data(): void
    {
        $a = $this->makeTenant('a');
        $b = $this->makeTenant('b');

        // 5 today for A, 100 today for B
        for ($i = 0; $i < 5;  $i++) $this->seedUsage('a');
        for ($i = 0; $i < 100; $i++) $this->seedUsage('b');

        $trendA = (new AiUsageReporter())->dailyTrend($a, days: 7);
        $totalA = array_sum(array_column($trendA, 'requests'));
        $this->assertSame(5, $totalA, 'tenant A trend should not contain B rows');

        $trendB = (new AiUsageReporter())->dailyTrend($b, days: 7);
        $totalB = array_sum(array_column($trendB, 'requests'));
        $this->assertSame(100, $totalB);
    }

    public function test_global_dashboard_aggregates_all_tenants(): void
    {
        $this->makeTenant('a');
        $this->makeTenant('b');
        $this->makeTenant('c');

        for ($i = 0; $i < 3; $i++) $this->seedUsage('a');
        for ($i = 0; $i < 7; $i++) $this->seedUsage('b');
        for ($i = 0; $i < 2; $i++) $this->seedUsage('c');

        // Null tenant → agency-wide aggregation
        $totals = (new AiUsageReporter())->totalsForCurrentMonth(null);
        $this->assertSame(12, $totals['requests']);
    }

    public function test_quota_isolated_when_tenants_share_same_plan(): void
    {
        // Two tenants both on "free" — their counters must stay separate.
        // Use tiny token counts so we only hit the request limit (10), not
        // the 1000-token cap on the free plan.
        $a = $this->makeTenant('a-free');
        $b = $this->makeTenant('b-free');

        $tiny = ['input_tokens' => 1, 'output_tokens' => 1, 'total_tokens' => 2, 'cost_usd' => 0.0001];
        for ($i = 0; $i < 9; $i++) $this->seedUsage('a-free', $tiny); // 9/10
        $svc = $this->quotaService();

        // A is at 9/10 — still under limit (request + token both fine)
        $exA1 = null;
        try { $svc->assertWithinQuota($a); }
        catch (\Throwable $e) { $exA1 = $e; }
        $this->assertNull($exA1, 'tenant A at 9/10 should still be under quota');

        // B has 0 usage — definitely under limit
        $exB1 = null;
        try { $svc->assertWithinQuota($b); }
        catch (\Throwable $e) { $exB1 = $e; }
        $this->assertNull($exB1, 'tenant B at 0/10 should be under quota');

        // Now push A over
        $this->seedUsage('a-free', $tiny);
        $blocked = false;
        try { $svc->assertWithinQuota($a); }
        catch (AiQuotaExceededException $e) { $blocked = true; }
        $this->assertTrue($blocked, 'tenant A at 10/10 should be blocked');

        // B is STILL fine (0/10), even though A is blocked
        $exB2 = null;
        try { $svc->assertWithinQuota($b); }
        catch (\Throwable $e) { $exB2 = $e; }
        $this->assertNull($exB2, 'tenant B must NOT be blocked by A\'s exhaustion');
    }
}
