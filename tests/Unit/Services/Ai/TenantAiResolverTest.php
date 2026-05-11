<?php

namespace Tests\Unit\Services\Ai;

use App\Services\Ai\AiManager;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\Dtos\AiUsage;
use App\Services\Ai\TenantAiResolver;
use Closure;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;

/**
 * The resolver does not hit any DB, so we use a barebones PHPUnit TestCase
 * with stub objects that mimic the Tenant model's AI helper surface.
 * That keeps this test independent of the full Laravel kernel bootstrap.
 */
class TenantAiResolverTest extends \Tests\TestCase
{
    private function manager(): AiManager
    {
        return new AiManager([
            'default_provider' => 'anthropic',
            'defaults' => ['timeout' => 5],
            'providers' => [
                'anthropic' => [
                    'driver'      => 'anthropic',
                    'api_key'     => 'system-anthropic',
                    'base_url'    => 'https://api.anthropic.com/v1',
                    'api_version' => '2023-06-01',
                    'models'      => ['simple' => 'sys-haiku', 'complex' => 'sys-sonnet'],
                ],
                'openai' => [
                    'driver'   => 'openai',
                    'api_key'  => 'system-openai',
                    'base_url' => 'https://api.openai.com/v1',
                    'organization' => null,
                    'models'   => ['simple' => 'sys-4o-mini', 'complex' => 'sys-4o'],
                ],
            ],
        ]);
    }

    /**
     * Build a stub tenant with just the AI surface the resolver needs.
     */
    private function tenant(array $overrides = []): object
    {
        $defaults = [
            'id'                 => 'demo',
            'use_byok'           => false,
            'preferred_provider' => null,
            'api_keys'           => [],
            'models'             => [],
        ];
        $data = array_replace($defaults, $overrides);

        return new class($data) extends \App\Models\Tenant {
            private array $stubData;

            public function __construct(array $data)
            {
                $this->stubData = $data;
                // Bypass parent::__construct() to avoid stancl DB lookups.
            }

            public function getKey(): string
            {
                return (string) $this->stubData['id'];
            }

            public function isUsingByokAi(): bool
            {
                return (bool) $this->stubData['use_byok'];
            }

            public function preferredAiProvider(): ?string
            {
                return $this->stubData['preferred_provider'];
            }

            public function preferredAiModel(string $provider, string $tier): ?string
            {
                return $this->stubData['models'][$provider][$tier] ?? null;
            }

            public function aiApiKey(string $provider): ?string
            {
                return $this->stubData['api_keys'][$provider] ?? null;
            }
        };
    }

    public function test_resolves_to_system_defaults_when_tenant_is_null(): void
    {
        $resolver = new TenantAiResolver($this->manager());

        $config = $resolver->resolve(null, 'simple');

        $this->assertSame('anthropic', $config['provider']);
        $this->assertSame('sys-haiku', $config['model']);
        $this->assertNull($config['api_key']);
        $this->assertFalse($config['byok']);
    }

    public function test_resolves_to_system_defaults_when_byok_disabled(): void
    {
        $tenant = $this->tenant(['use_byok' => false]);
        $resolver = new TenantAiResolver($this->manager());

        $config = $resolver->resolve($tenant, 'complex');

        $this->assertSame('anthropic', $config['provider']);
        $this->assertSame('sys-sonnet', $config['model']);
        $this->assertNull($config['api_key']);
        $this->assertFalse($config['byok']);
    }

    public function test_falls_back_to_system_when_byok_enabled_but_no_key_stored(): void
    {
        $tenant = $this->tenant([
            'use_byok' => true,
            'preferred_provider' => 'openai',
            'api_keys' => [], // empty
        ]);

        $config = (new TenantAiResolver($this->manager()))->resolve($tenant, 'simple');

        $this->assertSame('anthropic', $config['provider'], 'fallback to system default provider');
        $this->assertFalse($config['byok']);
        $this->assertNull($config['api_key']);
    }

    public function test_uses_tenant_key_and_preferred_provider_when_byok_enabled(): void
    {
        $tenant = $this->tenant([
            'use_byok' => true,
            'preferred_provider' => 'openai',
            'api_keys' => ['openai' => 'tenant-openai-key'],
        ]);

        $config = (new TenantAiResolver($this->manager()))->resolve($tenant, 'simple');

        $this->assertSame('openai', $config['provider']);
        $this->assertSame('sys-4o-mini', $config['model'], 'falls back to system model when tenant has no override');
        $this->assertSame('tenant-openai-key', $config['api_key']);
        $this->assertTrue($config['byok']);
    }

    public function test_tenant_model_override_wins_over_system_model(): void
    {
        $tenant = $this->tenant([
            'use_byok' => true,
            'preferred_provider' => 'openai',
            'api_keys' => ['openai' => 'tk'],
            'models'   => ['openai' => ['simple' => 'gpt-tenant-tier']],
        ]);

        $config = (new TenantAiResolver($this->manager()))->resolve($tenant, 'simple');

        $this->assertSame('gpt-tenant-tier', $config['model']);
    }

    public function test_generate_sends_byok_key_to_provider(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'model' => 'sys-4o-mini',
                'choices' => [['message' => ['content' => 'hi'], 'finish_reason' => 'stop']],
                'usage'  => ['prompt_tokens' => 1, 'completion_tokens' => 1],
            ], 200),
        ]);

        $tenant = $this->tenant([
            'use_byok' => true,
            'preferred_provider' => 'openai',
            'api_keys' => ['openai' => 'tenant-byok-xyz'],
        ]);

        $resolver = new TenantAiResolver($this->manager());

        $response = $resolver->generate($tenant, new AiRequest(
            model: '',                       // empty → resolver fills from tier
            messages: [AiMessage::user('hi')],
        ), 'simple');

        $this->assertSame('openai', $response->provider);
        $this->assertSame('hi', $response->content);

        Http::assertSent(fn ($req) => $req->hasHeader('Authorization', 'Bearer tenant-byok-xyz'));
    }

    public function test_generate_records_tenant_id_in_metadata(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model'   => 'sys-haiku',
                'content' => [['type' => 'text', 'text' => 'ok']],
                'usage'   => ['input_tokens' => 1, 'output_tokens' => 1],
            ], 200),
        ]);

        $tenant = $this->tenant(['id' => 'acme-clinic']);

        // We cannot read metadata from the final AiResponse, but we can
        // verify the tenant id reached the provider request body by
        // injecting a spy provider via AiManager::extend().
        $manager = $this->manager();
        $captured = null;
        $manager->extend('anthropic', function (array $cfg) use (&$captured) {
            return new class($cfg, $captured) implements \App\Services\Ai\Contracts\AiProvider {
                public function __construct(private array $cfg, public &$captured) {}
                public function name(): string { return 'anthropic'; }
                public function generate(\App\Services\Ai\Dtos\AiRequest $r): AiResponse
                {
                    $this->captured = $r;
                    return new AiResponse('ok', $r->model, 'anthropic', new AiUsage(1, 1));
                }
                public function stream(\App\Services\Ai\Dtos\AiRequest $r, Closure $f): AiResponse { throw new \BadMethodCallException(); }
            };
        });

        $resolver = new TenantAiResolver($manager);
        $resolver->generate($tenant, new AiRequest('', [AiMessage::user('hi')]), 'simple');

        $this->assertNotNull($captured);
        $this->assertSame('acme-clinic', $captured->metadata['tenant_id']);
        $this->assertSame('simple', $captured->metadata['tier']);
        $this->assertFalse($captured->metadata['byok']);
    }
}
