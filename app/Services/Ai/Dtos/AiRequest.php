<?php

namespace App\Services\Ai\Dtos;

/**
 * Provider-neutral request payload.
 *
 * The AiManager builds this from a caller's intent and hands it to the
 * chosen provider. The provider adapts it into its native API shape.
 */
final class AiRequest
{
    /**
     * @param  string  $model                Resolved model name (e.g. "claude-sonnet-4-6").
     * @param  array<int, AiMessage>  $messages    Conversation history; the last one is usually the user's prompt.
     * @param  string|null  $system               Optional system prompt (Anthropic-style separate field).
     * @param  int  $maxTokens                    Hard upper bound on output tokens.
     * @param  float  $temperature                Sampling temperature; lower = more deterministic.
     * @param  array<string, mixed>  $metadata    Free-form tags for logging/quota attribution (tenant id, feature, etc).
     * @param  string|null  $apiKeyOverride       BYOK — if set, the provider uses this key instead of the config one.
     */
    public function __construct(
        public readonly string $model,
        public readonly array $messages,
        public readonly ?string $system = null,
        public readonly int $maxTokens = 1024,
        public readonly float $temperature = 0.7,
        public readonly array $metadata = [],
        public readonly ?string $apiKeyOverride = null,
    ) {
    }

    /**
     * Convenience factory for a single-turn "system + user" request.
     */
    public static function singleTurn(
        string $model,
        string $userPrompt,
        ?string $system = null,
        int $maxTokens = 1024,
        float $temperature = 0.7,
        array $metadata = [],
        ?string $apiKeyOverride = null,
    ): self {
        return new self(
            model: $model,
            messages: [AiMessage::user($userPrompt)],
            system: $system,
            maxTokens: $maxTokens,
            temperature: $temperature,
            metadata: $metadata,
            apiKeyOverride: $apiKeyOverride,
        );
    }
}
