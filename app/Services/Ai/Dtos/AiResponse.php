<?php

namespace App\Services\Ai\Dtos;

/**
 * Provider-neutral response from an AI generation call.
 */
final class AiResponse
{
    /**
     * @param  string  $content       Plain-text completion.
     * @param  string  $model         Actual model that produced the response (may differ from requested if provider routed).
     * @param  string  $provider      Provider name (e.g. "anthropic").
     * @param  AiUsage  $usage         Token counts for billing/quota.
     * @param  string|null  $stopReason  Provider-reported reason (e.g. "stop_sequence", "max_tokens").
     * @param  array<string, mixed>  $raw         Raw JSON-decoded response for debugging / advanced use.
     */
    public function __construct(
        public readonly string $content,
        public readonly string $model,
        public readonly string $provider,
        public readonly AiUsage $usage,
        public readonly ?string $stopReason = null,
        public readonly array $raw = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'content'     => $this->content,
            'model'       => $this->model,
            'provider'    => $this->provider,
            'usage'       => $this->usage->toArray(),
            'stop_reason' => $this->stopReason,
        ];
    }
}
