<?php

namespace Tests\Unit\Services\Ai;

use App\Models\Tenant;
use App\Services\Ai\AiPromptCache;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\Dtos\AiUsage;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AiPromptCacheTest extends TestCase
{
    private function cache(array $overrides = []): AiPromptCache
    {
        return new AiPromptCache(array_replace([
            'enabled'   => true,
            'store'     => null, // default store = array in tests
            'prefix'    => 'test_ai',
            'lock_wait' => 1,
        ], $overrides));
    }

    private function request(string $prompt = 'hi', string $model = 'a-haiku', ?string $system = null, float $temp = 0.3, int $max = 300): AiRequest
    {
        return new AiRequest(
            model:       $model,
            messages:    [AiMessage::user($prompt)],
            system:      $system,
            maxTokens:   $max,
            temperature: $temp,
        );
    }

    private function response(string $content = 'cached answer'): AiResponse
    {
        return new AiResponse(
            content:  $content,
            model:    'a-haiku',
            provider: 'anthropic',
            usage:    new AiUsage(inputTokens: 10, outputTokens: 8),
        );
    }

    private function tenant(string $id): Tenant
    {
        $t = new Tenant();
        $t->id = $id;

        return $t;
    }

    // ── Key determinism & isolation ────────────────────────────────────

    public function test_same_request_produces_same_key(): void
    {
        $cache = $this->cache();
        $k1 = $cache->cacheKey('anthropic', $this->request(), $this->tenant('t1'));
        $k2 = $cache->cacheKey('anthropic', $this->request(), $this->tenant('t1'));

        $this->assertSame($k1, $k2);
    }

    public function test_different_tenant_produces_different_key(): void
    {
        $cache = $this->cache();
        $a = $cache->cacheKey('anthropic', $this->request(), $this->tenant('t1'));
        $b = $cache->cacheKey('anthropic', $this->request(), $this->tenant('t2'));

        $this->assertNotSame($a, $b, 'tenant id must be part of the key (isolation)');
    }

    public function test_key_varies_by_prompt_model_system_temperature_and_max_tokens(): void
    {
        $cache = $this->cache();
        $t = $this->tenant('t1');
        $base = $cache->cacheKey('anthropic', $this->request(), $t);

        $this->assertNotSame($base, $cache->cacheKey('anthropic', $this->request(prompt: 'other'), $t));
        $this->assertNotSame($base, $cache->cacheKey('anthropic', $this->request(model: 'a-sonnet'), $t));
        $this->assertNotSame($base, $cache->cacheKey('anthropic', $this->request(system: 'sys'), $t));
        $this->assertNotSame($base, $cache->cacheKey('anthropic', $this->request(temp: 0.9), $t));
        $this->assertNotSame($base, $cache->cacheKey('anthropic', $this->request(max: 500), $t));
        $this->assertNotSame($base, $cache->cacheKey('openai', $this->request(), $t), 'provider is part of the key');
    }

    public function test_central_context_keyed_when_no_tenant(): void
    {
        $cache = $this->cache();
        $key = $cache->cacheKey('anthropic', $this->request(), null);

        $this->assertStringContainsString(':central:', $key);
    }

    public function test_image_content_hashed_in_key_not_inlined(): void
    {
        $cache = $this->cache();
        $req = new AiRequest(
            model: 'a-haiku',
            messages: [AiMessage::userWithImage('describe', base64_encode(str_repeat('X', 5000)), 'image/png')],
        );
        $key = $cache->cacheKey('anthropic', $req, $this->tenant('t1'));

        // Key is a bounded sha-based string, not megabytes of base64.
        $this->assertLessThan(200, strlen($key));
        // Different image → different key
        $req2 = new AiRequest(
            model: 'a-haiku',
            messages: [AiMessage::userWithImage('describe', base64_encode(str_repeat('Y', 5000)), 'image/png')],
        );
        $this->assertNotSame($key, $cache->cacheKey('anthropic', $req2, $this->tenant('t1')));
    }

    // ── remember() behavior ────────────────────────────────────────────

    public function test_first_call_misses_then_second_call_hits(): void
    {
        $cache = $this->cache();
        $tenant = $this->tenant('t1');
        $req = $this->request();
        $calls = 0;

        $first = $cache->remember('anthropic', $req, $tenant, 60, function () use (&$calls) {
            $calls++;

            return $this->response('first');
        });

        $second = $cache->remember('anthropic', $req, $tenant, 60, function () use (&$calls) {
            $calls++;

            return $this->response('SHOULD-NOT-RUN');
        });

        $this->assertFalse($first['hit']);
        $this->assertTrue($second['hit']);
        $this->assertSame(1, $calls, 'generator must run only once; second call served from cache');
        $this->assertSame('first', $second['response']->content);
        $this->assertTrue($second['response']->raw['_cache_hit'] ?? false);
    }

    public function test_disabled_cache_always_runs_generator(): void
    {
        $cache = $this->cache(['enabled' => false]);
        $calls = 0;
        $gen = function () use (&$calls) { $calls++; return $this->response(); };

        $cache->remember('anthropic', $this->request(), $this->tenant('t1'), 60, $gen);
        $cache->remember('anthropic', $this->request(), $this->tenant('t1'), 60, $gen);

        $this->assertSame(2, $calls, 'disabled cache must not store/serve');
    }

    public function test_zero_ttl_bypasses_cache(): void
    {
        $cache = $this->cache();
        $calls = 0;
        $gen = function () use (&$calls) { $calls++; return $this->response(); };

        $cache->remember('anthropic', $this->request(), $this->tenant('t1'), 0, $gen);
        $cache->remember('anthropic', $this->request(), $this->tenant('t1'), 0, $gen);

        $this->assertSame(2, $calls, 'ttl=0 means caching off for this feature');
    }

    public function test_empty_content_is_not_cached(): void
    {
        $cache = $this->cache();
        $tenant = $this->tenant('t1');
        $req = $this->request();
        $calls = 0;

        // First produces an empty response → must not be stored.
        $cache->remember('anthropic', $req, $tenant, 60, function () use (&$calls) {
            $calls++;

            return $this->response('');
        });
        // Second still a miss (empty wasn't cached).
        $r2 = $cache->remember('anthropic', $req, $tenant, 60, function () use (&$calls) {
            $calls++;

            return $this->response('real');
        });

        $this->assertSame(2, $calls);
        $this->assertFalse($r2['hit']);
    }

    public function test_tenants_do_not_share_cached_responses(): void
    {
        $cache = $this->cache();
        $req = $this->request();
        $calls = 0;
        $gen = function () use (&$calls) { $calls++; return $this->response('answer-'.$calls); };

        $a = $cache->remember('anthropic', $req, $this->tenant('t1'), 60, $gen);
        $b = $cache->remember('anthropic', $req, $this->tenant('t2'), 60, $gen);

        $this->assertFalse($a['hit']);
        $this->assertFalse($b['hit'], 'tenant t2 must NOT see tenant t1 cached answer');
        $this->assertSame(2, $calls);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Cache::store()->flush();
    }
}
