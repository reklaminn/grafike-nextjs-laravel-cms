<?php

namespace Tests\Unit\Services\Ai;

use App\Services\Ai\AiManager;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Exceptions\AiProviderException;
use App\Services\Ai\Providers\AnthropicProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use App\Services\Ai\Providers\OpenRouterProvider;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class AiManagerTest extends TestCase
{
    private function manager(array $overrides = []): AiManager
    {
        $config = array_replace_recursive([
            'default_provider' => 'anthropic',
            'defaults' => ['max_tokens' => 256, 'temperature' => 0.7, 'timeout' => 30],
            'providers' => [
                'anthropic' => [
                    'driver'      => 'anthropic',
                    'api_key'     => 'test-anthropic',
                    'base_url'    => 'https://api.anthropic.com/v1',
                    'api_version' => '2023-06-01',
                    'models'      => ['simple' => 'haiku-x', 'complex' => 'sonnet-x'],
                ],
                'openai' => [
                    'driver'   => 'openai',
                    'api_key'  => 'test-openai',
                    'base_url' => 'https://api.openai.com/v1',
                    'organization' => null,
                    'models'   => ['simple' => '4o-mini', 'complex' => '4o'],
                ],
                'openrouter' => [
                    'driver'   => 'openrouter',
                    'api_key'  => 'test-or',
                    'base_url' => 'https://openrouter.ai/api/v1',
                    'models'   => ['simple' => 'or-simple', 'complex' => 'or-complex'],
                ],
            ],
        ], $overrides);

        return new AiManager($config);
    }

    public function test_resolves_default_provider_and_returns_anthropic_adapter(): void
    {
        $provider = $this->manager()->provider();
        $this->assertInstanceOf(AnthropicProvider::class, $provider);
        $this->assertSame('anthropic', $provider->name());
    }

    public function test_resolves_named_providers(): void
    {
        $manager = $this->manager();
        $this->assertInstanceOf(OpenAiProvider::class, $manager->provider('openai'));
        $this->assertInstanceOf(OpenRouterProvider::class, $manager->provider('openrouter'));
    }

    public function test_memoizes_resolved_providers(): void
    {
        $manager = $this->manager();
        $this->assertSame($manager->provider('openai'), $manager->provider('openai'));
    }

    public function test_throws_when_provider_is_unknown(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->manager()->provider('does-not-exist');
    }

    public function test_resolves_model_by_tier(): void
    {
        $manager = $this->manager();
        $this->assertSame('haiku-x', $manager->resolveModel('simple'));
        $this->assertSame('sonnet-x', $manager->resolveModel('complex'));
        $this->assertSame('4o-mini', $manager->resolveModel('simple', 'openai'));
        $this->assertSame('or-complex', $manager->resolveModel('complex', 'openrouter'));
    }

    public function test_throws_when_tier_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->manager()->resolveModel('mythical');
    }

    public function test_anthropic_provider_makes_http_call_and_parses_response(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'id'    => 'msg_1',
                'model' => 'haiku-x',
                'content' => [
                    ['type' => 'text', 'text' => 'merhaba dünya'],
                ],
                'stop_reason' => 'end_turn',
                'usage' => ['input_tokens' => 12, 'output_tokens' => 3, 'cache_read_input_tokens' => 0],
            ], 200),
        ]);

        $response = $this->manager()->provider('anthropic')->generate(new AiRequest(
            model: 'haiku-x',
            messages: [AiMessage::user('selam')],
            maxTokens: 64,
        ));

        $this->assertSame('merhaba dünya', $response->content);
        $this->assertSame('anthropic', $response->provider);
        $this->assertSame(12, $response->usage->inputTokens);
        $this->assertSame(3, $response->usage->outputTokens);
        $this->assertSame(15, $response->usage->totalTokens());

        Http::assertSent(function ($request) {
            return $request->hasHeader('x-api-key', 'test-anthropic')
                && str_contains($request->url(), '/messages')
                && data_get($request->data(), 'model') === 'haiku-x';
        });
    }

    public function test_anthropic_provider_uses_byok_override(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'model' => 'haiku-x',
                'content' => [['type' => 'text', 'text' => 'ok']],
                'usage' => ['input_tokens' => 1, 'output_tokens' => 1],
            ], 200),
        ]);

        $this->manager()->provider('anthropic')->generate(new AiRequest(
            model: 'haiku-x',
            messages: [AiMessage::user('hi')],
            apiKeyOverride: 'tenant-byok-key',
        ));

        Http::assertSent(fn ($req) => $req->hasHeader('x-api-key', 'tenant-byok-key'));
    }

    public function test_openai_provider_parses_chat_completion(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'model' => '4o-mini',
                'choices' => [[
                    'message'       => ['role' => 'assistant', 'content' => 'hello'],
                    'finish_reason' => 'stop',
                ]],
                'usage' => ['prompt_tokens' => 8, 'completion_tokens' => 1],
            ], 200),
        ]);

        $response = $this->manager()->provider('openai')->generate(new AiRequest(
            model: '4o-mini',
            messages: [AiMessage::user('hi')],
            system: 'Be brief.',
        ));

        $this->assertSame('hello', $response->content);
        $this->assertSame('openai', $response->provider);
        $this->assertSame(8, $response->usage->inputTokens);
        $this->assertSame(1, $response->usage->outputTokens);

        Http::assertSent(function ($req) {
            $first = $req->data()['messages'][0] ?? [];

            return $req->hasHeader('Authorization', 'Bearer test-openai')
                && $first === ['role' => 'system', 'content' => 'Be brief.'];
        });
    }

    public function test_openrouter_provider_sends_attribution_headers(): void
    {
        config(['app.url' => 'https://example.test', 'app.name' => 'TestApp']);

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'model' => 'or-simple',
                'choices' => [['message' => ['content' => 'yo'], 'finish_reason' => 'stop']],
                'usage'  => ['prompt_tokens' => 2, 'completion_tokens' => 1],
            ], 200),
        ]);

        $this->manager()->provider('openrouter')->generate(new AiRequest(
            model: 'or-simple',
            messages: [AiMessage::user('ping')],
        ));

        Http::assertSent(fn ($req) => $req->hasHeader('HTTP-Referer', 'https://example.test')
            && $req->hasHeader('X-Title', 'TestApp')
            && $req->hasHeader('Authorization', 'Bearer test-or'));
    }

    public function test_missing_api_key_throws_typed_exception(): void
    {
        $manager = $this->manager(['providers' => ['anthropic' => ['api_key' => null]]]);

        $this->expectException(AiProviderException::class);
        $this->expectExceptionMessageMatches('/has no API key/i');

        $manager->provider('anthropic')->generate(new AiRequest(
            model: 'haiku-x',
            messages: [AiMessage::user('hi')],
        ));
    }

    public function test_http_failure_is_wrapped_in_provider_exception(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => 'bad'], 401),
        ]);

        try {
            $this->manager()->provider('anthropic')->generate(new AiRequest(
                model: 'haiku-x',
                messages: [AiMessage::user('hi')],
            ));
            $this->fail('Expected AiProviderException');
        } catch (AiProviderException $e) {
            $this->assertSame('anthropic', $e->provider);
            $this->assertSame(401, $e->statusCode);
            $this->assertIsArray($e->responseBody);
        }
    }

    public function test_extend_registers_custom_driver(): void
    {
        $manager = $this->manager([
            'providers' => ['custom' => ['driver' => 'echo', 'api_key' => 'x', 'models' => ['simple' => 's']]],
        ]);

        $manager->extend('echo', function (array $config, string $name) {
            return new class($name) implements \App\Services\Ai\Contracts\AiProvider {
                public function __construct(private string $name)
                {
                }

                public function name(): string
                {
                    return $this->name;
                }

                public function generate(\App\Services\Ai\Dtos\AiRequest $request): \App\Services\Ai\Dtos\AiResponse
                {
                    return new \App\Services\Ai\Dtos\AiResponse(
                        content: 'echo: '.$request->messages[0]->content,
                        model:    $request->model,
                        provider: $this->name,
                        usage:    new \App\Services\Ai\Dtos\AiUsage(1, 1),
                    );
                }

                public function stream(\App\Services\Ai\Dtos\AiRequest $request, \Closure $onDelta): \App\Services\Ai\Dtos\AiResponse
                {
                    throw new \BadMethodCallException();
                }
            };
        });

        $response = $manager->provider('custom')->generate(new AiRequest(
            model: 's',
            messages: [AiMessage::user('hey')],
        ));

        $this->assertSame('echo: hey', $response->content);
    }
}
