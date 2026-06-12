<?php

namespace App\Services\Ai\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Thrown when a provider call fails for any reason: HTTP error, malformed
 * payload, missing API key, rate limit, etc.
 *
 * Callers should catch this rather than provider-specific SDK exceptions
 * so that swapping providers does not change error-handling code.
 */
class AiProviderException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $provider = '',
        public readonly ?int $statusCode = null,
        public readonly ?array $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode ?? 0, $previous);
    }

    public static function missingApiKey(string $provider): self
    {
        return new self(
            message: "AI provider [{$provider}] has no API key configured. Set the relevant *_API_KEY env var or pass apiKeyOverride.",
            provider: $provider,
        );
    }

    public static function fromHttpFailure(string $provider, int $status, string $body): self
    {
        $decoded = json_decode($body, true);

        return new self(
            message: "AI provider [{$provider}] returned HTTP {$status}: ".substr($body, 0, 500),
            provider: $provider,
            statusCode: $status,
            responseBody: is_array($decoded) ? $decoded : null,
        );
    }
}
