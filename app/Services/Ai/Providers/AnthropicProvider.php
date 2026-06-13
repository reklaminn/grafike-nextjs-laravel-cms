<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\AiProvider;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\Dtos\AiUsage;
use App\Services\Ai\Exceptions\AiProviderException;
use App\Services\Ai\Streaming\AnthropicSseParser;
use Closure;
use Illuminate\Support\Facades\Http;

/**
 * Anthropic Messages API adapter.
 *
 * @see https://docs.anthropic.com/en/api/messages
 */
class AnthropicProvider implements AiProvider
{
    /**
     * @param  array{api_key:?string, base_url:string, api_version:string, models:array<string,string>}  $config
     */
    public function __construct(private readonly array $config)
    {
    }

    public function name(): string
    {
        return 'anthropic';
    }

    public function generate(AiRequest $request): AiResponse
    {
        $apiKey = $request->apiKeyOverride ?: ($this->config['api_key'] ?? null);
        if (! $apiKey) {
            throw AiProviderException::missingApiKey($this->name());
        }

        $payload = [
            'model'       => $request->model,
            'max_tokens'  => $request->maxTokens,
            'temperature' => $request->temperature,
            'messages'    => $this->normalizeMessages($request->messages),
        ];

        if ($request->system) {
            $payload['system'] = $this->buildSystem($request->system);
        }

        $response = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => $this->config['api_version'] ?? '2023-06-01',
            'content-type'      => 'application/json',
        ])
            ->timeout((int) config('ai.defaults.timeout', 60))
            ->post(rtrim($this->config['base_url'], '/').'/messages', $payload);

        if ($response->failed()) {
            throw AiProviderException::fromHttpFailure(
                $this->name(),
                $response->status(),
                $response->body(),
            );
        }

        $data = $response->json() ?? [];
        $text = collect($data['content'] ?? [])
            ->filter(fn ($block) => ($block['type'] ?? null) === 'text')
            ->pluck('text')
            ->implode('');

        return new AiResponse(
            content:    $text,
            model:      $data['model'] ?? $request->model,
            provider:   $this->name(),
            usage:      new AiUsage(
                inputTokens:       (int) ($data['usage']['input_tokens'] ?? 0),
                outputTokens:      (int) ($data['usage']['output_tokens'] ?? 0),
                cachedInputTokens: isset($data['usage']['cache_read_input_tokens'])
                    ? (int) $data['usage']['cache_read_input_tokens']
                    : null,
            ),
            stopReason: $data['stop_reason'] ?? null,
            raw:        $data,
        );
    }

    public function stream(AiRequest $request, Closure $onDelta): AiResponse
    {
        $apiKey = $request->apiKeyOverride ?: ($this->config['api_key'] ?? null);
        if (! $apiKey) {
            throw AiProviderException::missingApiKey($this->name());
        }

        $payload = [
            'model'       => $request->model,
            'max_tokens'  => $request->maxTokens,
            'temperature' => $request->temperature,
            'messages'    => $this->normalizeMessages($request->messages),
            'stream'      => true,
        ];
        if ($request->system) {
            $payload['system'] = $this->buildSystem($request->system);
        }

        // Laravel Http facade supports Guzzle stream option; the PSR-7
        // body stays open and we read it incrementally as the model
        // generates tokens.
        $response = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => $this->config['api_version'] ?? '2023-06-01',
            'content-type'      => 'application/json',
            'accept'            => 'text/event-stream',
        ])
            ->withOptions(['stream' => true])
            ->timeout((int) config('ai.defaults.timeout', 60))
            ->post(rtrim($this->config['base_url'], '/').'/messages', $payload);

        if ($response->failed()) {
            // Drain whatever's left to surface a useful error body.
            $body = (string) $response->toPsrResponse()->getBody();
            throw AiProviderException::fromHttpFailure($this->name(), $response->status(), $body);
        }

        return $this->consumeStream($response, $request->model, $onDelta);
    }

    /**
     * Drive the SSE parser from the open response body and assemble the
     * final AiResponse.
     */
    private function consumeStream(\Illuminate\Http\Client\Response $response, string $requestedModel, Closure $onDelta): AiResponse
    {
        $body   = $response->toPsrResponse()->getBody();
        $parser = new AnthropicSseParser();

        while (! $body->eof()) {
            $chunk = $body->read(4096);
            if ($chunk === '') {
                // No data available right now — yield once and continue
                usleep(10_000); // 10ms
                continue;
            }
            $parser->feed($chunk, $onDelta);
        }

        $state = $parser->finalState($onDelta);

        return new AiResponse(
            content:    $state['content'],
            model:      $state['model'] ?? $requestedModel,
            provider:   $this->name(),
            usage:      new AiUsage(
                inputTokens:       $state['input_tokens'],
                outputTokens:      $state['output_tokens'],
                cachedInputTokens: $state['cached_input_tokens'],
            ),
            stopReason: $state['stop_reason'],
            raw:        ['streamed' => true],
        );
    }

    /**
     * Build the `system` field. For long, stable system prompts we attach
     * Anthropic's native prompt caching (`cache_control: ephemeral`) so the
     * shared prefix is billed at ~10% on repeat calls within the 5-minute
     * TTL. This is SEPARATE from our Redis response cache (FAZ 3.5): the two
     * compose — native caching cuts input cost, the response cache skips the
     * call entirely. Short prompts stay a plain string (below the model's
     * minimum cacheable prefix, cache_control would be silently ignored).
     *
     * @return string|array<int, array<string,mixed>>
     */
    private function buildSystem(string $system): string|array
    {
        $promptCache = (array) config('ai.cache.prompt_cache', []);
        $enabled     = (bool) ($promptCache['enabled'] ?? false);
        $minChars    = (int) ($promptCache['min_system_chars'] ?? 12000);

        if (! $enabled || mb_strlen($system) < $minChars) {
            return $system;
        }

        return [[
            'type'          => 'text',
            'text'          => $system,
            'cache_control' => ['type' => 'ephemeral'],
        ]];
    }

    /**
     * Anthropic does not accept "system" inside the messages array; if a
     * caller passes a system-role message we silently coerce it to user.
     *
     * @param  array<int, AiMessage>  $messages
     * @return array<int, array{role:string, content:string}>
     */
    /**
     * Normalize messages for Anthropic API.
     * Supports plain-text and multimodal (image) content.
     */
    private function normalizeMessages(array $messages): array
    {
        return collect($messages)
            ->map(fn (AiMessage $m) => [
                'role'    => $m->role === 'system' ? 'user' : $m->role,
                'content' => is_array($m->content)
                    ? $this->buildAnthropicContent($m->content)
                    : $m->content,
            ])
            ->values()
            ->all();
    }

    /**
     * Convert internal multimodal blocks to Anthropic content format.
     *
     * @param  array<int, array{type:string,...}>  $blocks
     * @return array<int, array>
     */
    private function buildAnthropicContent(array $blocks): array
    {
        return array_map(function (array $block): array {
            if ($block['type'] === 'image') {
                return [
                    'type'   => 'image',
                    'source' => [
                        'type'       => 'base64',
                        'media_type' => $block['mimeType'] ?? 'image/jpeg',
                        'data'       => $block['base64'],
                    ],
                ];
            }
            // text block (or any future type)
            return ['type' => 'text', 'text' => $block['text'] ?? ''];
        }, $blocks);
    }
}
