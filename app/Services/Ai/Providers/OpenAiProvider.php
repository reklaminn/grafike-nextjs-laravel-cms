<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\AiProvider;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Dtos\AiResponse;
use App\Services\Ai\Dtos\AiUsage;
use App\Services\Ai\Exceptions\AiProviderException;
use BadMethodCallException;
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
            $messages[] = ['role' => $message->role, 'content' => $message->content];
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
        throw new BadMethodCallException(
            'OpenAiProvider::stream() not implemented yet (planned in FAZ 3.6).',
        );
    }
}
