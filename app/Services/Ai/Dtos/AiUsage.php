<?php

namespace App\Services\Ai\Dtos;

/**
 * Token-level usage stats returned by the provider. Used for billing,
 * quota tracking, and the usage dashboard (FAZ 3.7).
 */
final class AiUsage
{
    public function __construct(
        public readonly int $inputTokens,
        public readonly int $outputTokens,
        public readonly ?int $cachedInputTokens = null,
    ) {
    }

    public function totalTokens(): int
    {
        return $this->inputTokens + $this->outputTokens;
    }

    public function toArray(): array
    {
        return [
            'input_tokens'         => $this->inputTokens,
            'output_tokens'        => $this->outputTokens,
            'cached_input_tokens'  => $this->cachedInputTokens,
            'total_tokens'         => $this->totalTokens(),
        ];
    }
}
