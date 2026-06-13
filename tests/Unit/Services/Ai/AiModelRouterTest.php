<?php

namespace Tests\Unit\Services\Ai;

use App\Services\Ai\AiManager;
use App\Services\Ai\AiModelRouter;
use App\Services\Ai\AiPromptCache;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\Dtos\AiUsage as AiUsageDto;
use App\Services\Ai\Exceptions\AiProviderException;
use App\Services\Ai\TenantAiResolver;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class AiModelRouterTest extends TestCase
{
    private function baseConfig(array $overrides = []): array
    {
        return array_replace_recursive([
            'default_provider' => 'anthropic',
            'defaults' => ['max_tokens' => 256, 'temperature' => 0.7, 'timeout' => 5],
            'providers' => [
                'anthropic' => [
                    'driver' => 'anthropic',
                    'api_key' => 'sys-anthropic',
                    'base_url' => 'https://api.anthropic.com/v1',
                    'api_version' => '2023-06-01',
                    'models' => ['simple' => 'a-haiku', 'complex' => 'a-sonnet'],
                ],
                'openai' => [
                    'driver' => 'openai',
                    'api_key' => 'sys-openai',
                    'base_url' => 'https://api.openai.com/v1',
                    'organization' => null,
                    'models' => ['simple' => 'o-mini', 'complex' => 'o-full'],
                ],
                'openrouter' => [
                    'driver' => 'openrouter',
                    'api_key' => 'sys-or',
                    'base_url' => 'https://openrouter.ai/api/v1',
                    'models' => ['simple' => 'or-simple', 'complex' => 'or-complex'],
                ],
            ],
            'features' => [
                'seo.meta'       => ['tier' => 'simple',  'max_tokens' => 300, 'temperature' => 0.3],
                'page.create'    => ['tier' => 'complex', 'max_tokens' => 2000, 'temperature' => 0.6],
                'block.template' => ['tier' => 'complex', 'max_tokens' => 1500, 'temperature' => 0.5],
            ],
            'fallback' => [
                'enabled'         => true,
                'chain'           => ['anthropic', 'openai', 'openrouter'],
                'on_status_codes' => [429, 500, 502, 503, 504],
                'max_attempts'    => 3,
            ],
        ], $overrides);
    }

    private function router(array $overrides = []): AiModelRouter
    {
        $config   = $this->baseConfig($overrides);
        $manager  = new AiManager($config);
        $tenantR  = new TenantAiResolver($manager);
        $quota    = $this->stubQuota();
        // Cache disabled by default ('enabled' missing → false): existing
        // routing/fallback tests must see no caching behavior.
        $cache    = new AiPromptCache($config['cache'] ?? []);

        return new AiModelRouter($manager, $tenantR, $quota, $cache, $config);
    }

    /**
     * Quota stub that never touches the DB. Test cases that care about
     * quota enforcement (AiQuotaServiceTest) test the real service in
     * isolation; the router tests only verify that calls happen in the
     * right order and don't blow up when quota is wired in.
     */
    private function stubQuota(): AiQuotaService
    {
        return new class extends AiQuotaService {
            public function __construct()
            {
                parent::__construct(plans: [], defaultPlan: 'free', pricing: []);
            }

            public function assertWithinQuota(?\App\Models\Tenant $tenant, int $estimatedTokens = 0): void
            {
                // no-op
            }

            public function recordSuccess(
                ?\App\Models\Tenant $tenant,
                string $feature,
                AiResponse $response,
                bool $byok = false,
                bool $fallbackUsed = false,
                array $extraMetadata = [],
            ): \App\Models\AiUsage {
                return new \App\Models\AiUsage(); // in-memory, not persisted
            }

            public function recordFailure(
                ?\App\Models\Tenant $tenant,
                string $feature,
                string $provider,
                string $model,
                \Throwable $error,
                bool $byok = false,
                array $extraMetadata = [],
            ): \App\Models\AiUsage {
                return new \App\Models\AiUsage();
            }

            public function recordCacheHit(
                ?\App\Models\Tenant $tenant,
                string $feature,
                AiResponse $response,
                bool $byok = false,
                array $extraMetadata = [],
            ): \App\Models\AiUsage {
                return new \App\Models\AiUsage();
            }
        };
    }

    public function test_resolves_feature_to_tier_and_parameters(): void
    {
        $cfg = $this->router()->resolve('seo.meta');

        $this->assertSame('simple',     $cfg['tier']);
        $this->assertSame('anthropic',  $cfg['provider']);
        $this->assertSame('a-haiku',    $cfg['model']);
        $this->assertSame(300,          $cfg['max_tokens']);
        $this->assertEqualsWithDelta(0.3, $cfg['temperature'], 0.001);
        $this->assertFalse($cfg['byok']);
    }

    public function test_complex_feature_routes_to_sonnet_tier(): void
    {
        $cfg = $this->router()->resolve('page.create');

        $this->assertSame('complex', $cfg['tier']);
        $this->assertSame('a-sonnet', $cfg['model']);
        $this->assertSame(2000, $cfg['max_tokens']);
    }

    public function test_tier_override_wins_over_feature_default(): void
    {
        $cfg = $this->router()->resolve('seo.meta', tierOverride: 'complex');

        $this->assertSame('complex', $cfg['tier']);
        $this->assertSame('a-sonnet', $cfg['model']);
        // max_tokens still comes from feature config (300), not from tier
        $this->assertSame(300, $cfg['max_tokens']);
    }

    public function test_throws_when_feature_unknown(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/is not registered/');
        $this->router()->resolve('made.up.feature');
    }

    public function test_generate_makes_single_call_when_primary_succeeds(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model'   => 'a-haiku',
                'content' => [['type' => 'text', 'text' => 'meta üretildi']],
                'usage'   => ['input_tokens' => 10, 'output_tokens' => 8],
            ], 200),
        ]);

        $response = $this->router()->generate(
            feature: 'seo.meta',
            prompt: 'Sayfa içeriği özet…',
            system: 'TR SEO meta üret.',
        );

        $this->assertSame('anthropic', $response->provider);
        $this->assertSame('meta üretildi', $response->content);

        Http::assertSentCount(1);
        Http::assertSent(fn ($req) => str_contains($req->url(), 'api.anthropic.com'));
    }

    public function test_fallback_skips_primary_after_retryable_failure(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => 'overloaded'], 503),
            'api.openai.com/*' => Http::response([
                'model'   => 'o-mini',
                'choices' => [['message' => ['content' => 'fallback'], 'finish_reason' => 'stop']],
                'usage'   => ['prompt_tokens' => 3, 'completion_tokens' => 1],
            ], 200),
        ]);

        $response = $this->router()->generate(
            feature: 'seo.meta',
            prompt: 'hi',
        );

        $this->assertSame('openai', $response->provider);
        $this->assertSame('o-mini', $response->model);
        $this->assertSame('fallback', $response->content);

        Http::assertSentCount(2);
    }

    public function test_non_recoverable_status_does_not_fallback(): void
    {
        // 401 unauthorized — auth problem, not transient → must NOT fallback.
        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => 'unauthorized'], 401),
            'api.openai.com/*'    => Http::response(['ok' => true], 200),
        ]);

        try {
            $this->router()->generate(feature: 'seo.meta', prompt: 'hi');
            $this->fail('Expected AiProviderException');
        } catch (AiProviderException $e) {
            $this->assertSame(401, $e->statusCode);
            $this->assertSame('anthropic', $e->provider);
        }

        Http::assertSentCount(1, 'OpenAI must NOT be called for non-transient failures');
    }

    public function test_walks_full_chain_until_one_succeeds(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => 'rate'], 429),
            'api.openai.com/*'    => Http::response(['error' => 'down'], 503),
            'openrouter.ai/*'     => Http::response([
                'model' => 'or-simple',
                'choices' => [['message' => ['content' => 'from openrouter'], 'finish_reason' => 'stop']],
                'usage'  => ['prompt_tokens' => 2, 'completion_tokens' => 3],
            ], 200),
        ]);

        $response = $this->router()->generate(feature: 'seo.meta', prompt: 'x');

        $this->assertSame('openrouter', $response->provider);
        $this->assertSame('from openrouter', $response->content);

        Http::assertSentCount(3);
    }

    public function test_all_providers_failing_throws_last_error(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => 'a'], 503),
            'api.openai.com/*'    => Http::response(['error' => 'b'], 503),
            'openrouter.ai/*'     => Http::response(['error' => 'c'], 503),
        ]);

        try {
            $this->router()->generate(feature: 'seo.meta', prompt: 'x');
            $this->fail('Expected AiProviderException');
        } catch (AiProviderException $e) {
            $this->assertSame(503, $e->statusCode);
        }

        Http::assertSentCount(3);
    }

    public function test_max_attempts_caps_chain_walk(): void
    {
        $router = $this->router(['fallback' => ['max_attempts' => 2]]);

        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => 'a'], 503),
            'api.openai.com/*'    => Http::response(['error' => 'b'], 503),
            'openrouter.ai/*'     => Http::response([
                'model' => 'or-simple',
                'choices' => [['message' => ['content' => 'should not reach'], 'finish_reason' => 'stop']],
                'usage'  => ['prompt_tokens' => 1, 'completion_tokens' => 1],
            ], 200),
        ]);

        try {
            $router->generate(feature: 'seo.meta', prompt: 'x');
            $this->fail('Expected failure when max_attempts < chain length');
        } catch (AiProviderException $e) {
            $this->assertSame(503, $e->statusCode);
        }

        Http::assertSentCount(2, 'router must stop after max_attempts');
    }

    public function test_fallback_disabled_propagates_first_failure(): void
    {
        $router = $this->router(['fallback' => ['enabled' => false]]);

        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => 'down'], 503),
            'api.openai.com/*'    => Http::response(['ok' => true], 200),
        ]);

        try {
            $router->generate(feature: 'seo.meta', prompt: 'x');
            $this->fail('Expected AiProviderException');
        } catch (AiProviderException $e) {
            $this->assertSame(503, $e->statusCode);
        }

        Http::assertSentCount(1);
    }

    public function test_unknown_provider_in_chain_is_dropped_silently(): void
    {
        $router = $this->router([
            'fallback' => ['chain' => ['anthropic', 'made-up-provider', 'openai']],
        ]);

        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => 'rate'], 429),
            'api.openai.com/*'    => Http::response([
                'model'   => 'o-mini',
                'choices' => [['message' => ['content' => 'ok'], 'finish_reason' => 'stop']],
                'usage'   => ['prompt_tokens' => 1, 'completion_tokens' => 1],
            ], 200),
        ]);

        $response = $router->generate(feature: 'seo.meta', prompt: 'hi');

        $this->assertSame('openai', $response->provider);
        Http::assertSentCount(2);
    }

    public function test_byok_key_is_scoped_to_primary_provider_only(): void
    {
        // Replace anthropic with a spy that fails; openai with a spy that
        // captures the request to assert byok key was NOT propagated.
        $router = $this->router();
        $manager = (function () use ($router) {
            $ref = new \ReflectionClass($router);
            $prop = $ref->getProperty('manager');
            $prop->setAccessible(true);

            return $prop->getValue($router);
        })();

        $manager->extend('anthropic', function () {
            return new class implements \App\Services\Ai\Contracts\AiProvider {
                public function name(): string { return 'anthropic'; }
                public function generate(\App\Services\Ai\Dtos\AiRequest $r): AiResponse
                {
                    throw AiProviderException::fromHttpFailure('anthropic', 503, '{}');
                }
                public function stream(\App\Services\Ai\Dtos\AiRequest $r, \Closure $f): AiResponse { throw new \BadMethodCallException(); }
            };
        });

        $captured = null;
        $manager->extend('openai', function () use (&$captured) {
            return new class($captured) implements \App\Services\Ai\Contracts\AiProvider {
                public function __construct(public &$captured) {}
                public function name(): string { return 'openai'; }
                public function generate(\App\Services\Ai\Dtos\AiRequest $r): AiResponse
                {
                    $this->captured = $r;
                    return new AiResponse('ok', $r->model, 'openai', new AiUsageDto(1, 1));
                }
                public function stream(\App\Services\Ai\Dtos\AiRequest $r, \Closure $f): AiResponse { throw new \BadMethodCallException(); }
            };
        });

        $response = $router->generateWithRequest(
            feature: 'seo.meta',
            request: new \App\Services\Ai\Dtos\AiRequest(
                model: 'a-haiku',
                messages: [\App\Services\Ai\Dtos\AiMessage::user('hi')],
                apiKeyOverride: 'tenant-byok-key',
            ),
        );

        $this->assertSame('openai', $response->provider);
        $this->assertNotNull($captured, 'openai spy should have received the request');
        $this->assertNull(
            $captured->apiKeyOverride,
            'BYOK key must NOT be propagated to fallback providers — tenant should not be charged for an unfamiliar vendor.'
        );
        $this->assertTrue($captured->metadata['fallback_used']);
    }

    public function test_response_cache_serves_second_identical_call_without_api(): void
    {
        // seo.meta gets a 60-min TTL; cache enabled.
        $router = $this->router([
            'cache'    => ['enabled' => true, 'prefix' => 'rt_ai', 'lock_wait' => 1],
            'features' => ['seo.meta' => ['tier' => 'simple', 'max_tokens' => 300, 'temperature' => 0.3, 'cache_ttl' => 60]],
        ]);

        \Illuminate\Support\Facades\Cache::store()->flush();

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model'   => 'a-haiku',
                'content' => [['type' => 'text', 'text' => 'cached meta']],
                'usage'   => ['input_tokens' => 10, 'output_tokens' => 8],
            ], 200),
        ]);

        $first  = $router->generate(feature: 'seo.meta', prompt: 'aynı içerik', system: 'TR SEO');
        $second = $router->generate(feature: 'seo.meta', prompt: 'aynı içerik', system: 'TR SEO');

        $this->assertSame('cached meta', $first->content);
        $this->assertSame('cached meta', $second->content);
        $this->assertTrue($second->raw['_cache_hit'] ?? false, 'second call must be served from cache');

        Http::assertSentCount(1, 'identical second request must NOT hit the provider');
    }

    public function test_zero_ttl_feature_is_not_cached(): void
    {
        // page.create has cache_ttl 0 → every call hits the API.
        $router = $this->router([
            'cache'    => ['enabled' => true, 'prefix' => 'rt_ai', 'lock_wait' => 1],
            'features' => ['page.create' => ['tier' => 'complex', 'max_tokens' => 2000, 'temperature' => 0.6, 'cache_ttl' => 0]],
        ]);

        \Illuminate\Support\Facades\Cache::store()->flush();

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model'   => 'a-sonnet',
                'content' => [['type' => 'text', 'text' => 'fresh page']],
                'usage'   => ['input_tokens' => 5, 'output_tokens' => 5],
            ], 200),
        ]);

        $router->generate(feature: 'page.create', prompt: 'same', system: 'sys');
        $router->generate(feature: 'page.create', prompt: 'same', system: 'sys');

        Http::assertSentCount(2, 'creative feature (ttl=0) must never be cached');
    }

    public function test_long_system_prompt_gets_native_cache_control(): void
    {
        // AnthropicProvider::buildSystem reads the GLOBAL config helper.
        config()->set('ai.cache.prompt_cache', ['enabled' => true, 'min_system_chars' => 100]);

        $router = $this->router(['cache' => ['enabled' => false]]); // response cache off — isolate native caching

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model'   => 'a-haiku',
                'content' => [['type' => 'text', 'text' => 'ok']],
                'usage'   => ['input_tokens' => 1, 'output_tokens' => 1],
            ], 200),
        ]);

        $longSystem = str_repeat('Bu uzun ve stabil bir system prompt. ', 20); // > 100 chars
        $router->generate(feature: 'seo.meta', prompt: 'hi', system: $longSystem);

        Http::assertSent(function ($req) {
            $body = $req->data();
            $system = $body['system'] ?? null;

            return is_array($system)
                && ($system[0]['cache_control']['type'] ?? null) === 'ephemeral'
                && ($system[0]['text'] ?? null) !== null;
        });
    }

    public function test_short_system_prompt_stays_plain_string(): void
    {
        config()->set('ai.cache.prompt_cache', ['enabled' => true, 'min_system_chars' => 100000]);

        $router = $this->router(['cache' => ['enabled' => false]]);

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model'   => 'a-haiku',
                'content' => [['type' => 'text', 'text' => 'ok']],
                'usage'   => ['input_tokens' => 1, 'output_tokens' => 1],
            ], 200),
        ]);

        $router->generate(feature: 'seo.meta', prompt: 'hi', system: 'kısa');

        Http::assertSent(fn ($req) => is_string($req->data()['system'] ?? null));
    }

    public function test_chain_remembers_primary_first_even_if_chain_starts_elsewhere(): void
    {
        // default_provider = anthropic; chain starts with openai
        $router = $this->router([
            'fallback' => ['chain' => ['openai', 'openrouter']],
        ]);

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model'   => 'a-haiku',
                'content' => [['type' => 'text', 'text' => 'primary served']],
                'usage'   => ['input_tokens' => 1, 'output_tokens' => 1],
            ], 200),
        ]);

        $response = $router->generate(feature: 'seo.meta', prompt: 'hi');

        $this->assertSame('anthropic', $response->provider, 'primary should be tried first regardless of chain order');
        Http::assertSentCount(1);
    }
}
