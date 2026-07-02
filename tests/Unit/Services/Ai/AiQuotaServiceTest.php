<?php

namespace Tests\Unit\Services\Ai;

use App\Models\AiUsage;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\Dtos\AiUsage as AiUsageDto;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class AiQuotaServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Point the central connection at the in-memory SQLite the test
        // suite already uses, so AiUsage model (which is bound to `central`)
        // can read/write here without touching the production MariaDB host.
        config([
            'database.connections.central' => [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ],
        ]);
        DB::purge('central');

        // Build the ai_usage schema by hand (the migration is keyed to the
        // central connection too; reusing it here keeps the schema honest).
        Schema::connection('central')->create('ai_usage', function ($table) {
            $table->id();
            $table->string('tenant_id', 100)->nullable()->index();
            $table->string('feature', 64)->index();
            $table->string('provider', 32);
            $table->string('model', 128);
            $table->string('tier', 16)->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('cached_input_tokens')->nullable();
            $table->unsignedInteger('total_tokens')->default(0);
            $table->decimal('cost_usd', 12, 6)->default(0);
            $table->boolean('byok')->default(false);
            $table->boolean('fallback_used')->default(false);
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    private function service(array $overrides = []): AiQuotaService
    {
        return new AiQuotaService(
            plans: $overrides['plans'] ?? [
                'free' => [
                    'label'            => 'Free',
                    'monthly_requests' => 10,
                    'monthly_tokens'   => 1_000,
                    'monthly_cost_usd' => 0.05,
                ],
                'pro' => [
                    'label'            => 'Pro',
                    'monthly_requests' => 1000,
                    'monthly_tokens'   => 1_000_000,
                    'monthly_cost_usd' => 50.00,
                ],
                'enterprise' => [
                    'label'            => 'Enterprise',
                    'monthly_requests' => null,
                    'monthly_tokens'   => null,
                    'monthly_cost_usd' => null,
                ],
            ],
            defaultPlan: $overrides['default_plan'] ?? 'free',
            pricing: $overrides['pricing'] ?? [
                'anthropic' => [
                    'claude-haiku-4-5' => ['input' => 1.00, 'output' => 5.00],
                ],
                'openai' => [
                    'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
                ],
            ],
        );
    }

    /**
     * Build a stub tenant exposing the minimal AI surface the quota service
     * needs (id + plan + BYOK helpers). Keeps tests DB-free for the tenant
     * model itself.
     */
    private function tenant(array $data = []): object
    {
        $defaults = [
            'id'                 => 'acme',
            'plan'               => 'free',
            'use_byok'           => false,
            'preferred_provider' => null,
            'api_keys'           => [],
        ];
        $data = array_replace($defaults, $data);

        return new class($data) extends \App\Models\Tenant {
            private array $stub;
            public function __construct(array $stub)
            {
                $this->stub = $stub;
            }
            public function getKey(): string
            {
                return (string) $this->stub['id'];
            }
            public function aiPlan(): string
            {
                return $this->stub['plan'];
            }
            public function isUsingByokAi(): bool
            {
                return (bool) $this->stub['use_byok'];
            }
            public function preferredAiProvider(): ?string
            {
                return $this->stub['preferred_provider'];
            }
            public function hasAiApiKey(string $provider): bool
            {
                return ! empty($this->stub['api_keys'][$provider]);
            }
        };
    }

    private function aiResponse(string $provider, string $model, int $in, int $out): AiResponse
    {
        return new AiResponse(
            content:  'ok',
            model:    $model,
            provider: $provider,
            usage:    new AiUsageDto($in, $out),
        );
    }

    // ────────────────────────────────────────────────────────────────

    public function test_calculate_cost_uses_pricing_matrix(): void
    {
        $svc = $this->service();

        // 1000 input tokens × $1/M + 500 output × $5/M = 0.001 + 0.0025 = 0.0035
        $this->assertEqualsWithDelta(
            0.0035,
            $svc->calculateCost('anthropic', 'claude-haiku-4-5', 1000, 500),
            0.0000001,
        );
    }

    public function test_calculate_cost_returns_zero_for_unknown_model(): void
    {
        $this->assertSame(0.0, $this->service()->calculateCost('anthropic', 'imaginary-model', 100, 100));
    }

    public function test_record_success_writes_row_with_correct_cost(): void
    {
        $svc      = $this->service();
        $tenant   = $this->tenant(['id' => 'acme']);
        $response = $this->aiResponse('anthropic', 'claude-haiku-4-5', 1000, 200);

        $row = $svc->recordSuccess($tenant, 'seo.meta', $response, byok: false, fallbackUsed: false, extraMetadata: ['tier' => 'simple']);

        $this->assertNotNull($row->id);
        $this->assertSame('acme', $row->tenant_id);
        $this->assertSame('seo.meta', $row->feature);
        $this->assertSame('anthropic', $row->provider);
        $this->assertSame(1000, $row->input_tokens);
        $this->assertSame(200, $row->output_tokens);
        $this->assertSame(1200, $row->total_tokens);
        $this->assertEqualsWithDelta(0.002, (float) $row->cost_usd, 0.0001);
        $this->assertTrue($row->success);
        $this->assertFalse($row->byok);
        $this->assertFalse($row->fallback_used);
    }

    public function test_record_cache_hit_writes_free_row_with_savings(): void
    {
        $svc      = $this->service();
        $tenant   = $this->tenant(['id' => 'acme']);
        // The cached response carries the original token counts.
        $response = $this->aiResponse('anthropic', 'claude-haiku-4-5', 1000, 200);

        $row = $svc->recordCacheHit($tenant, 'seo.meta', $response, byok: false, extraMetadata: ['tier' => 'simple']);

        // No API call happened → billable tokens & cost are zero (won't burn quota).
        $this->assertSame(0, $row->input_tokens);
        $this->assertSame(0, $row->total_tokens);
        $this->assertEqualsWithDelta(0.0, (float) $row->cost_usd, 0.0000001);
        $this->assertTrue($row->success);

        // Savings surfaced in metadata for the dashboard.
        $this->assertTrue($row->metadata['cache_hit']);
        $this->assertSame(1200, $row->metadata['saved_tokens']);
        $this->assertEqualsWithDelta(0.002, (float) $row->metadata['saved_cost_usd'], 0.0001);
    }

    public function test_cache_hit_does_not_count_against_token_or_cost_quota(): void
    {
        $svc    = $this->service();
        $tenant = $this->tenant(['id' => 'acme']);

        $svc->recordCacheHit($tenant, 'seo.meta', $this->aiResponse('anthropic', 'claude-haiku-4-5', 5000, 5000));

        $usage = $svc->currentUsage($tenant);
        // Tokens & cost stay at zero — a cache hit is free.
        $this->assertSame(0, $usage['tokens']);
        $this->assertEqualsWithDelta(0.0, $usage['cost_usd'], 0.0000001);
    }

    public function test_record_failure_writes_zero_token_row_with_error(): void
    {
        $svc = $this->service();
        $row = $svc->recordFailure(
            tenant:   $this->tenant(['id' => 'acme']),
            feature:  'page.create',
            provider: 'anthropic',
            model:    'claude-sonnet-4-6',
            error:    new RuntimeException('rate limit'),
        );

        $this->assertFalse($row->success);
        $this->assertSame(0, $row->total_tokens);
        $this->assertSame('rate limit', $row->error_message);
    }

    public function test_current_usage_excludes_byok_and_failures(): void
    {
        $svc    = $this->service();
        $tenant = $this->tenant(['id' => 'acme']);

        // 3 billable success rows
        for ($i = 0; $i < 3; $i++) {
            $svc->recordSuccess($tenant, 'seo.meta', $this->aiResponse('anthropic', 'claude-haiku-4-5', 100, 50));
        }
        // 1 BYOK success — must NOT count
        $svc->recordSuccess($tenant, 'seo.meta', $this->aiResponse('anthropic', 'claude-haiku-4-5', 200, 100), byok: true);
        // 1 billable failure — currently INCLUDED (we count attempts; cost is 0)
        $svc->recordFailure($tenant, 'seo.meta', 'anthropic', 'claude-haiku-4-5', new RuntimeException('x'));

        $usage = $svc->currentUsage($tenant);

        // 3 success + 1 failure = 4 billable requests (BYOK excluded)
        $this->assertSame(4, $usage['requests']);
        // tokens: 3 × 150 = 450 (failure had 0 tokens, BYOK excluded)
        $this->assertSame(450, $usage['tokens']);
        // cost: 3 × ((100×1 + 50×5)/1M) = 3 × 0.00035 = 0.00105
        $this->assertEqualsWithDelta(0.00105, $usage['cost_usd'], 0.0001);
    }

    public function test_current_usage_filters_to_current_month(): void
    {
        $svc    = $this->service();
        $tenant = $this->tenant(['id' => 'acme']);

        // One row last month — must NOT count
        $oldRow = $svc->recordSuccess($tenant, 'seo.meta', $this->aiResponse('anthropic', 'claude-haiku-4-5', 100, 50));
        AiUsage::query()
            ->where('id', $oldRow->id)
            ->update(['created_at' => CarbonImmutable::now()->subMonth()]);

        // One row this month
        $svc->recordSuccess($tenant, 'seo.meta', $this->aiResponse('anthropic', 'claude-haiku-4-5', 100, 50));

        $usage = $svc->currentUsage($tenant);
        $this->assertSame(1, $usage['requests']);
    }

    public function test_assert_within_quota_throws_when_requests_exceeded(): void
    {
        $svc    = $this->service();
        $tenant = $this->tenant(['id' => 'acme', 'plan' => 'free']); // requests=10

        for ($i = 0; $i < 10; $i++) {
            $svc->recordSuccess($tenant, 'seo.meta', $this->aiResponse('anthropic', 'claude-haiku-4-5', 1, 1));
        }

        try {
            $svc->assertWithinQuota($tenant);
            $this->fail('Expected AiQuotaExceededException');
        } catch (AiQuotaExceededException $e) {
            $this->assertSame(AiQuotaExceededException::LIMIT_REQUESTS, $e->limitType);
            $this->assertSame('free', $e->plan);
            $this->assertSame(10, $e->used);
            $this->assertSame(10, $e->limit);
        }
    }

    public function test_assert_within_quota_throws_when_token_estimate_would_exceed(): void
    {
        $svc    = $this->service();
        $tenant = $this->tenant(['id' => 'acme', 'plan' => 'free']); // tokens=1000

        // Use up 900 tokens (well under request limit but close to token cap)
        $svc->recordSuccess($tenant, 'seo.meta', $this->aiResponse('anthropic', 'claude-haiku-4-5', 800, 100));

        // Asking for another 200 → 900 + 200 = 1100 > 1000 → must throw
        $this->expectException(AiQuotaExceededException::class);
        $svc->assertWithinQuota($tenant, estimatedTokens: 200);
    }

    public function test_assert_within_quota_throws_when_cost_cap_reached(): void
    {
        $svc    = $this->service();
        $tenant = $this->tenant(['id' => 'acme', 'plan' => 'free']); // cost=$0.05

        // Sonnet is not in our pricing table for this test (only haiku),
        // so manually insert a high-cost row.
        AiUsage::create([
            'tenant_id'     => 'acme',
            'feature'       => 'page.create',
            'provider'      => 'anthropic',
            'model'         => 'claude-haiku-4-5',
            'input_tokens'  => 1,
            'output_tokens' => 1,
            'total_tokens'  => 2,
            'cost_usd'      => 0.06, // > 0.05 cap
            'byok'          => false,
            'success'       => true,
            'created_at'    => now(),
        ]);

        try {
            $svc->assertWithinQuota($tenant);
            $this->fail('Expected AiQuotaExceededException');
        } catch (AiQuotaExceededException $e) {
            $this->assertSame(AiQuotaExceededException::LIMIT_COST, $e->limitType);
        }
    }

    public function test_assert_within_quota_passes_below_limits(): void
    {
        $svc    = $this->service();
        $tenant = $this->tenant(['id' => 'acme', 'plan' => 'free']);

        $svc->recordSuccess($tenant, 'seo.meta', $this->aiResponse('anthropic', 'claude-haiku-4-5', 100, 50));

        // No throw expected
        $svc->assertWithinQuota($tenant);
        $this->expectNotToPerformAssertions();
    }

    public function test_byok_tenant_with_key_is_exempt(): void
    {
        $svc    = $this->service();
        $tenant = $this->tenant([
            'id' => 'acme',
            'use_byok' => true,
            'preferred_provider' => 'anthropic',
            'api_keys' => ['anthropic' => 'tenant-key'],
            'plan' => 'free',
        ]);

        // Way over any limit — but BYOK means we don't check
        for ($i = 0; $i < 20; $i++) {
            $svc->recordSuccess($tenant, 'seo.meta', $this->aiResponse('anthropic', 'claude-haiku-4-5', 100, 100), byok: true);
        }

        $svc->assertWithinQuota($tenant);
        $this->expectNotToPerformAssertions();
    }

    public function test_byok_enabled_without_key_falls_back_to_quota_check(): void
    {
        $svc    = $this->service();
        $tenant = $this->tenant([
            'id'                 => 'acme',
            'use_byok'           => true,
            'preferred_provider' => 'anthropic',
            'api_keys'           => [], // empty
            'plan'               => 'free',
        ]);

        // Hit request cap → must throw even though BYOK is "on"
        for ($i = 0; $i < 10; $i++) {
            $svc->recordSuccess($tenant, 'seo.meta', $this->aiResponse('anthropic', 'claude-haiku-4-5', 1, 1));
        }

        $this->expectException(AiQuotaExceededException::class);
        $svc->assertWithinQuota($tenant);
    }

    public function test_enterprise_plan_has_no_limits(): void
    {
        $svc    = $this->service();
        $tenant = $this->tenant(['id' => 'acme', 'plan' => 'enterprise']);

        for ($i = 0; $i < 50; $i++) {
            $svc->recordSuccess($tenant, 'seo.meta', $this->aiResponse('anthropic', 'claude-haiku-4-5', 1000, 1000));
        }

        $svc->assertWithinQuota($tenant);
        $this->expectNotToPerformAssertions();
    }

    public function test_null_tenant_is_exempt(): void
    {
        // CLI / system callers without a tenant should never be blocked.
        $this->service()->assertWithinQuota(null, estimatedTokens: 1_000_000);
        $this->expectNotToPerformAssertions();
    }

    public function test_plan_for_falls_back_to_default_when_tenant_plan_unknown(): void
    {
        $svc    = $this->service();
        $tenant = $this->tenant(['plan' => 'mythical-tier']);

        $plan = $svc->planFor($tenant);
        $this->assertSame('mythical-tier', $plan['name']);
        $this->assertSame(10, $plan['monthly_requests'], 'should fall back to free plan limits');
    }
}
