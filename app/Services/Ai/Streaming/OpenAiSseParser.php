<?php

namespace App\Services\Ai\Streaming;

/**
 * Incremental SSE parser for OpenAI Chat Completions (and OpenRouter,
 * which uses the same wire format).
 *
 * Format (one event per blank-line group):
 *
 *   data: {"choices":[{"delta":{"content":"merhaba"}}]}
 *
 *   data: {"choices":[{"delta":{"content":" dünya"}}]}
 *
 *   data: {"choices":[{"finish_reason":"stop"}]}
 *
 *   data: [DONE]
 *
 * Token usage in OpenAI streams only appears when caller requests it
 * (stream_options.include_usage=true). When present, the final chunk
 * contains a `usage` object outside `choices`. We harvest that here.
 *
 * @see https://platform.openai.com/docs/api-reference/chat/streaming
 */
class OpenAiSseParser
{
    private string $buffer = '';
    private string $finalText = '';
    private ?string $model = null;
    private ?string $stopReason = null;
    private int $inputTokens = 0;
    private int $outputTokens = 0;
    private ?int $cachedTokens = null;
    private bool $sawDone = false;

    public function feed(string $chunk, \Closure $onDelta): void
    {
        $this->buffer .= $chunk;

        while (($eol = strpos($this->buffer, "\n\n")) !== false) {
            $message = substr($this->buffer, 0, $eol);
            $this->buffer = substr($this->buffer, $eol + 2);
            $this->handleMessage($message, $onDelta);
        }
    }

    /**
     * @return array{content:string, model:?string, stop_reason:?string,
     *               input_tokens:int, output_tokens:int, cached_input_tokens:?int}
     */
    public function finalState(\Closure $onDelta): array
    {
        if (trim($this->buffer) !== '') {
            $this->handleMessage($this->buffer, $onDelta);
            $this->buffer = '';
        }

        return [
            'content'             => $this->finalText,
            'model'               => $this->model,
            'stop_reason'         => $this->stopReason,
            'input_tokens'        => $this->inputTokens,
            'output_tokens'       => $this->outputTokens,
            'cached_input_tokens' => $this->cachedTokens,
        ];
    }

    public function isDone(): bool
    {
        return $this->sawDone;
    }

    // ────────────────────────────────────────────────────────────────────

    private function handleMessage(string $raw, \Closure $onDelta): void
    {
        // OpenAI/OpenRouter typically use single-line "data: …" events.
        $dataPayload = null;
        foreach (preg_split('/\r?\n/', $raw) as $line) {
            if (str_starts_with($line, 'data:')) {
                $dataPayload = ltrim(substr($line, 5));
                break;
            }
        }
        if ($dataPayload === null || $dataPayload === '') {
            return;
        }

        // Stream-end sentinel
        if ($dataPayload === '[DONE]') {
            $this->sawDone = true;
            return;
        }

        $event = json_decode($dataPayload, true);
        if (! is_array($event)) {
            return;
        }

        // Track model name when first reported
        if (! $this->model && isset($event['model'])) {
            $this->model = (string) $event['model'];
        }

        // Token usage (only present when caller asked include_usage=true)
        if (isset($event['usage']) && is_array($event['usage'])) {
            $u = $event['usage'];
            $this->inputTokens  = (int) ($u['prompt_tokens']     ?? $this->inputTokens);
            $this->outputTokens = (int) ($u['completion_tokens'] ?? $this->outputTokens);
            if (isset($u['prompt_tokens_details']['cached_tokens'])) {
                $this->cachedTokens = (int) $u['prompt_tokens_details']['cached_tokens'];
            }
        }

        $choices = $event['choices'] ?? [];
        if (! is_array($choices)) {
            return;
        }
        foreach ($choices as $choice) {
            $delta = $choice['delta'] ?? [];
            if (isset($delta['content']) && $delta['content'] !== '') {
                $text = (string) $delta['content'];
                $this->finalText .= $text;
                $onDelta($text);
            }
            if (isset($choice['finish_reason']) && $choice['finish_reason'] !== null) {
                $this->stopReason = (string) $choice['finish_reason'];
            }
        }
    }
}
