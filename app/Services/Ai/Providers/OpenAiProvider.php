<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\AiProvider;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\Dtos\AiUsage;
use App\Services\Ai\Exceptions\AiProviderException;
use App\Services\Ai\Streaming\OpenAiSseParser;
use Closure;
use Illuminate\Support\Facades\Http;

/**
 * OpenAI Chat Completions API adapter.
 *
 * @see https://platform.openai.com/docs/api-reference/chat
 */
class OpenAiProvider implements AiProvider
{
    /**
     * @param  array{api_key:?string, base_url:string, organization:?string, models:array<string,string>}  $config
     */
    public function __construct(private readonly array $config)
    {
    }

    public function name(): string
    {
        return 'openai';
    }

    public function generate(AiRequest $request): AiResponse
    {
        $apiKey = $request->apiKeyOverride ?: ($this->config['api_key'] ?? null);
        if (! $apiKey) {
            throw AiProviderException::missingApiKey($this->name());
        }

        $messages = [];
        if ($request->system) {
            $messages[] = ['role' => 'system', 'content' => $request->system];
        }
        foreach ($request->messages as $message) {
            /** @var AiMessage $message */
            $messages[] = [
                'role'    => $message->role,
                'content' => is_array($message->content)
                    ? $this->buildOpenAiContent($message->content)
                    : $message->content,
            ];
        }

        $headers = [
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type'  => 'application/json',
        ];
        if (! empty($this->config['organization'])) {
            $headers['OpenAI-Organization'] = $this->config['organization'];
        }

        $response = Http::withHeaders($headers)
            ->timeout((int) config('ai.defaults.timeout', 60))
            ->post(rtrim($this->config['base_url'], '/').'/chat/completions', [
                'model'       => $request->model,
                'messages'    => $messages,
                'max_tokens'  => $request->maxTokens,
                'temperature' => $request->temperature,
            ]);

        if ($response->failed()) {
            throw AiProviderException::fromHttpFailure(
                $this->name(),
                $response->status(),
                $response->body(),
            );
        }

        $data    = $response->json() ?? [];
        $choice  = $data['choices'][0] ?? [];
        $message = $choice['message'] ?? [];

        return new AiResponse(
            content:    (string) ($message['content'] ?? ''),
            model:      $data['model'] ?? $request->model,
            provider:   $this->name(),
            usage:      new AiUsage(
                inputTokens:       (int) ($data['usage']['prompt_tokens'] ?? 0),
                outputTokens:      (int) ($data['usage']['completion_tokens'] ?? 0),
                cachedInputTokens: isset($data['usage']['prompt_tokens_details']['cached_tokens'])
                    ? (int) $data['usage']['prompt_tokens_details']['cached_tokens']
                    : null,
            ),
            stopReason: $choice['finish_reason'] ?? null,
            raw:        $data,
        );
    }

    public function stream(AiRequest $request, Closure $onDelta): AiResponse
    {
        $apiKey = $request->apiKeyOverride ?: ($this->config['api_key'] ?? null);
        if (! $apiKey) {
            throw AiProviderException::missingApiKey($this->name());
        }

        $messages = [];
        if ($request->system) {
            $messages[] = ['role' => 'system', 'content' => $request->system];
        }
        foreach ($request->messages as $message) {
            /** @var AiMessage $message */
            $messages[] = [
                'role'    => $message->role,
                'content' => is_array($message->content)
                    ? $this->buildOpenAiContent($message->content)
                    : $message->content,
            ];
        }

        $headers = [
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'text/event-stream',
        ];
        if (! empty($this->config['organization'])) {
            $headers['OpenAI-Organization'] = $this->config['organization'];
        }

        $response = Http::withHeaders($headers)
            ->withOptions(['stream' => true])
            ->timeout((int) config('ai.defaults.timeout', 60))
            ->post(rtrim($this->config['base_url'], '/').'/chat/completions', [
                'model'          => $request->model,
                'messages'       => $messages,
                'max_tokens'     => $request->maxTokens,
                'temperature'    => $request->temperature,
                'stream'         => true,
                // Ask OpenAI to include the final usage block — vital for
                // accurate quota accounting on streamed calls.
                'stream_options' => ['include_usage' => true],
            ]);

        if ($response->failed()) {
            $body = (string) $response->toPsrResponse()->getBody();
            throw AiProviderException::fromHttpFailure($this->name(), $response->status(), $body);
        }

        return $this->consumeStream($response, $request->model, $onDelta);
    }

    private function consumeStream(\Illuminate\Http\Client\Response $response, string $requestedModel, Closure $onDelta): AiResponse
    {
        $body   = $response->toPsrResponse()->getBody();
        $parser = new OpenAiSseParser();

        while (! $body->eof()) {
            $chunk = $body->read(4096);
            if ($chunk === '') {
                usleep(10_000);
                continue;
            }
            $parser->feed($chunk, $onDelta);
            if ($parser->isDone()) {
                break;
            }
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
     * Convert internal multimodal blocks to OpenAI content format.
     *
     * @param  array<int, array{type:string,...}>  $blocks
     * @return array<int, array>
     */
    private function buildOpenAiContent(array $blocks): array
    {
        return array_map(function (array $block): array {
            if ($block['type'] === 'image') {
                $mimeType = $block['mimeType'] ?? 'image/jpeg';
                return [
                    'type'      => 'image_url',
                    'image_url' => ['url' => "data:{$mimeType};base64,{$block['base64']}"],
                ];
            }
            return ['type' => 'text', 'text' => $block['text'] ?? ''];
        }, $blocks);
    }
}
