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
    /**
     * @param  string|array<int,array{type:string,...}>  $content
     *   - string  → plain text message
     *   - array   → multimodal blocks: [{type:'text',text:...}, {type:'image',base64:...,mimeType:...}]
     */
    public function __construct(
        public readonly string $role,
        public readonly string|array $content,
    ) {
    }

    public static function user(string $content): self
    {
        return new self('user', $content);
    }

    /**
     * Vision message: text prompt + a reference image (base64-encoded).
     *
     * @param  string  $text      The prompt/instruction alongside the image.
     * @param  string  $base64    Raw base64 string (no data-URI prefix).
     * @param  string  $mimeType  e.g. 'image/jpeg', 'image/png', 'image/webp'.
     */
    public static function userWithImage(string $text, string $base64, string $mimeType = 'image/jpeg'): self
    {
        return new self('user', [
            ['type' => 'text',  'text'     => $text],
            ['type' => 'image', 'base64'   => $base64, 'mimeType' => $mimeType],
        ]);
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
