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
 * OpenRouter adapter.
 *
 * OpenRouter is a unified gateway that speaks the OpenAI Chat Completions
 * schema; the only differences are the base URL and two recommended
 * attribution headers (HTTP-Referer + X-Title).
 *
 * @see https://openrouter.ai/docs
 */
class OpenRouterProvider implements AiProvider
{
    /**
     * @param  array{api_key:?string, base_url:string, models:array<string,string>}  $config
     */
    public function __construct(private readonly array $config)
    {
    }

    public function name(): string
    {
        return 'openrouter';
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

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type'  => 'application/json',
            // OpenRouter attribution — appears in the rankings dashboard.
            'HTTP-Referer'  => (string) config('app.url', ''),
            'X-Title'       => (string) config('app.name', 'iraspa-cms'),
        ])
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
                inputTokens:  (int) ($data['usage']['prompt_tokens'] ?? 0),
                outputTokens: (int) ($data['usage']['completion_tokens'] ?? 0),
            ),
            stopReason: $choice['finish_reason'] ?? null,
            raw:        $data,
        );
    }

    public function stream(AiRequest $request, Closure $onDelta): AiResponse
    {
        throw new BadMethodCallException(
            'OpenRouterProvider::stream() not implemented yet (planned in FAZ 3.6).',
        );
    }
}
