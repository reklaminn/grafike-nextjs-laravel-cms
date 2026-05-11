<?php

namespace App\Services\Ai\Dtos;

/**
 * A single message in the conversation.
 *
 * `role` is one of: 'user' | 'assistant' | 'system'.
 * Note: system messages are usually folded into AiRequest::$system rather
 * than appearing here, but the role is allowed for forward compatibility.
 */
final class AiMessage
{
    public function __construct(
        public readonly string $role,
        public readonly string $content,
    ) {
    }

    public static function user(string $content): self
    {
        return new self('user', $content);
    }

    public static function assistant(string $content): self
    {
        return new self('assistant', $content);
    }

    public static function system(string $content): self
    {
        return new self('system', $content);
    }

    public function toArray(): array
    {
        return ['role' => $this->role, 'content' => $this->content];
    }
}
