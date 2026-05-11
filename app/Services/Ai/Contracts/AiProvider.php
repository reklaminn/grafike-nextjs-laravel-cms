<?php

namespace App\Services\Ai\Contracts;

use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Dtos\AiResponse;
use Closure;

/**
 * AI provider adapter contract.
 *
 * Every provider (Anthropic, OpenAI, OpenRouter, future…) implements this
 * so that callers stay agnostic to the underlying vendor SDK.
 */
interface AiProvider
{
    /**
     * Short identifier — e.g. "anthropic", "openai", "openrouter".
     */
    public function name(): string;

    /**
     * Single-shot generation. Blocks until the response is fully returned.
     */
    public function generate(AiRequest $request): AiResponse;

    /**
     * Streamed generation. Calls $onDelta($textChunk) for each incremental
     * token chunk, then returns the assembled final AiResponse.
     *
     * Implementations that have not yet wired streaming MAY throw
     * \BadMethodCallException — see FAZ 3.6.
     */
    public function stream(AiRequest $request, Closure $onDelta): AiResponse;
}
