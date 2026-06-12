<?php

namespace App\Services\Ai\Streaming;

/**
 * Incremental SSE parser for Anthropic Messages API stream responses.
 *
 * Anthropic streams in this format (one event per blank-line group):
 *
 *   event: message_start
 *   data: {"type":"message_start","message":{"model":"…","usage":{...}}}
 *
 *   event: content_block_start
 *   data: {"type":"content_block_start","index":0,"content_block":{"type":"text"}}
 *
 *   event: content_block_delta
 *   data: {"type":"content_block_delta","index":0,"delta":{"type":"text_delta","text":"merhaba"}}
 *
 *   event: message_delta
 *   data: {"type":"message_delta","delta":{"stop_reason":"end_turn"},"usage":{"output_tokens":42}}
 *
 *   event: message_stop
 *   data: {"type":"message_stop"}
 *
 * `feed()` is called with each chunk read from the underlying HTTP stream;
 * it accumulates incomplete events in $buffer and emits complete ones.
 * `text_delta` payloads invoke the user-supplied `$onDelta($text)` callback.
 * The parser tracks the assembled final text, model name, usage counts,
 * and stop_reason; caller pulls them via `finalState()` after the stream
 * closes.
 *
 * Pure function-style: no I/O, no globals — testable with hand-crafted
 * SSE strings.
 */
class AnthropicSseParser
{
    private string $buffer = '';
    private string $finalText = '';
    private ?string $model = null;
    private ?string $stopReason = null;
    private int $inputTokens = 0;
    private int $outputTokens = 0;
    private ?int $cacheReadTokens = null;

    /** @var array<int, array<string, mixed>>  Last raw event for debugging */
    private array $lastEvents = [];

    /**
     * Feed a new chunk of bytes from the wire. Emits text deltas through
     * the callback. Safe to call with partial events — incomplete events
     * stay in the buffer until completed.
     *
     * @param  \Closure(string $textChunk): void  $onDelta
     */
    public function feed(string $chunk, \Closure $onDelta): void
    {
        $this->buffer .= $chunk;

        // SSE messages are separated by blank lines ("\n\n").
        while (($eol = strpos($this->buffer, "\n\n")) !== false) {
            $message = substr($this->buffer, 0, $eol);
            $this->buffer = substr($this->buffer, $eol + 2);
            $this->handleMessage($message, $onDelta);
        }
    }

    /**
     * Drain any remaining (unterminated) message and return final state.
     *
     * @return array{content:string, model:?string, stop_reason:?string,
     *               input_tokens:int, output_tokens:int, cached_input_tokens:?int}
     */
    public function finalState(\Closure $onDelta): array
    {
        // Some servers send the last event without trailing \n\n.
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
            'cached_input_tokens' => $this->cacheReadTokens,
        ];
    }

    // ────────────────────────────────────────────────────────────────────

    private function handleMessage(string $raw, \Closure $onDelta): void
    {
        // An SSE message is one or more "field: value" lines.
        // We only care about the `data: ` field.
        $dataPayload = null;
        foreach (preg_split('/\r?\n/', $raw) as $line) {
            if (str_starts_with($line, 'data:')) {
                $dataPayload = ltrim(substr($line, 5));
                break; // Anthropic uses single-line JSON data
            }
        }
        if ($dataPayload === null || $dataPayload === '') {
            return;
        }

        $event = json_decode($dataPayload, true);
        if (! is_array($event)) {
            return; // malformed — silently drop
        }
        $this->lastEvents[] = $event;
        $type = $event['type'] ?? null;

        if ($type === 'message_start') {
            $msg = $event['message'] ?? [];
            $this->model        = $msg['model'] ?? $this->model;
            $usage              = $msg['usage'] ?? [];
            $this->inputTokens  = (int) ($usage['input_tokens']  ?? $this->inputTokens);
            $this->outputTokens = (int) ($usage['output_tokens'] ?? $this->outputTokens);
            if (isset($usage['cache_read_input_tokens'])) {
                $this->cacheReadTokens = (int) $usage['cache_read_input_tokens'];
            }
            return;
        }

        if ($type === 'content_block_delta') {
            $delta = $event['delta'] ?? [];
            if (($delta['type'] ?? null) === 'text_delta' && isset($delta['text'])) {
                $text = (string) $delta['text'];
                if ($text !== '') {
                    $this->finalText .= $text;
                    $onDelta($text);
                }
            }
            return;
        }

        if ($type === 'message_delta') {
            $delta = $event['delta'] ?? [];
            if (isset($delta['stop_reason'])) {
                $this->stopReason = (string) $delta['stop_reason'];
            }
            // Output tokens get their final count here
            $usage = $event['usage'] ?? [];
            if (isset($usage['output_tokens'])) {
                $this->outputTokens = (int) $usage['output_tokens'];
            }
            return;
        }
        // message_start, content_block_start, content_block_stop,
        // message_stop, ping — no state mutation required.
    }
}
