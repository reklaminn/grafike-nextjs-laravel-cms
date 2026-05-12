<?php

namespace Tests\Unit\Services\Ai\Streaming;

use App\Services\Ai\Streaming\AnthropicSseParser;
use Tests\TestCase;

/**
 * Streaming protocol robustness (FAZ 3.6).
 *
 * Anthropic's SSE format can arrive in arbitrary chunks because the
 * transport (Guzzle/PHP stream read) doesn't respect message boundaries.
 * The parser must buffer incomplete events and emit complete ones only,
 * regardless of where the chunk split happens.
 */
class AnthropicSseParserTest extends TestCase
{
    private function captureDeltas(): array
    {
        return [
            'deltas' => [],
            'fn'     => function ($text) use (&$deltas) { /* placeholder */ },
        ];
    }

    public function test_parses_complete_stream_in_single_chunk(): void
    {
        $stream = implode("\n\n", [
            'event: message_start'."\n".'data: {"type":"message_start","message":{"model":"claude-haiku-4-5","usage":{"input_tokens":12,"output_tokens":0}}}',
            'event: content_block_start'."\n".'data: {"type":"content_block_start","index":0,"content_block":{"type":"text","text":""}}',
            'event: content_block_delta'."\n".'data: {"type":"content_block_delta","index":0,"delta":{"type":"text_delta","text":"Merhaba "}}',
            'event: content_block_delta'."\n".'data: {"type":"content_block_delta","index":0,"delta":{"type":"text_delta","text":"dünya"}}',
            'event: message_delta'."\n".'data: {"type":"message_delta","delta":{"stop_reason":"end_turn"},"usage":{"output_tokens":2}}',
            'event: message_stop'."\n".'data: {"type":"message_stop"}',
        ])."\n\n";

        $deltas = [];
        $cb = function ($text) use (&$deltas) { $deltas[] = $text; };

        $parser = new AnthropicSseParser();
        $parser->feed($stream, $cb);
        $state = $parser->finalState($cb);

        $this->assertSame(['Merhaba ', 'dünya'], $deltas);
        $this->assertSame('Merhaba dünya', $state['content']);
        $this->assertSame('claude-haiku-4-5', $state['model']);
        $this->assertSame('end_turn', $state['stop_reason']);
        $this->assertSame(12, $state['input_tokens']);
        $this->assertSame(2, $state['output_tokens']);
    }

    public function test_handles_chunks_that_split_mid_event(): void
    {
        // Simulate Guzzle returning byte chunks that don't align with
        // message boundaries — this is the realistic case in production.
        $full = "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"text_delta\",\"text\":\"merhaba \"}}\n\n".
                "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"text_delta\",\"text\":\"dünya\"}}\n\n";

        $deltas = [];
        $cb = function ($t) use (&$deltas) { $deltas[] = $t; };
        $parser = new AnthropicSseParser();

        // Split into 30-char chunks
        for ($i = 0; $i < strlen($full); $i += 30) {
            $parser->feed(substr($full, $i, 30), $cb);
        }
        $state = $parser->finalState($cb);

        $this->assertSame(['merhaba ', 'dünya'], $deltas);
        $this->assertSame('merhaba dünya', $state['content']);
    }

    public function test_handles_unterminated_last_event_via_finalState(): void
    {
        // No trailing \n\n on last message — server closed early.
        $stream = "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"text_delta\",\"text\":\"foo\"}}";

        $deltas = [];
        $cb = function ($t) use (&$deltas) { $deltas[] = $t; };
        $parser = new AnthropicSseParser();
        $parser->feed($stream, $cb);
        $state = $parser->finalState($cb);

        $this->assertSame(['foo'], $deltas);
        $this->assertSame('foo', $state['content']);
    }

    public function test_ignores_non_text_delta_types(): void
    {
        // Anthropic sometimes sends input_json_delta or other tool-related
        // deltas — parser must skip them, not crash.
        $stream = "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"input_json_delta\",\"partial_json\":\"{\\\"q\\\": \\\"hi\\\"}\"}}\n\n".
                  "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"text_delta\",\"text\":\"merhaba\"}}\n\n";

        $deltas = [];
        $cb = function ($t) use (&$deltas) { $deltas[] = $t; };
        $parser = new AnthropicSseParser();
        $parser->feed($stream, $cb);
        $state = $parser->finalState($cb);

        $this->assertSame(['merhaba'], $deltas, 'only text_delta types should emit');
        $this->assertSame('merhaba', $state['content']);
    }

    public function test_silently_drops_malformed_json(): void
    {
        $stream = "event: content_block_delta\ndata: {this is not json\n\n".
                  "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"text_delta\",\"text\":\"recovered\"}}\n\n";

        $deltas = [];
        $cb = function ($t) use (&$deltas) { $deltas[] = $t; };
        $parser = new AnthropicSseParser();
        $parser->feed($stream, $cb);
        $state = $parser->finalState($cb);

        // First event ignored, parser recovered for the second
        $this->assertSame(['recovered'], $deltas);
    }

    public function test_message_delta_overrides_output_token_count(): void
    {
        // message_start gives a placeholder usage; message_delta gives final.
        $stream = "event: message_start\ndata: {\"type\":\"message_start\",\"message\":{\"model\":\"haiku\",\"usage\":{\"input_tokens\":10,\"output_tokens\":0}}}\n\n".
                  "event: message_delta\ndata: {\"type\":\"message_delta\",\"delta\":{\"stop_reason\":\"end_turn\"},\"usage\":{\"output_tokens\":42}}\n\n";

        $deltas = [];
        $cb = function ($t) use (&$deltas) { $deltas[] = $t; };
        $parser = new AnthropicSseParser();
        $parser->feed($stream, $cb);
        $state = $parser->finalState($cb);

        $this->assertSame(42, $state['output_tokens'], 'final output_tokens from message_delta');
        $this->assertSame(10, $state['input_tokens']);
    }

    public function test_captures_cache_read_tokens_when_present(): void
    {
        $stream = "event: message_start\ndata: {\"type\":\"message_start\",\"message\":{\"model\":\"haiku\",\"usage\":{\"input_tokens\":5,\"output_tokens\":0,\"cache_read_input_tokens\":100}}}\n\n";

        $deltas = [];
        $parser = new AnthropicSseParser();
        $parser->feed($stream, function ($t) use (&$deltas) { $deltas[] = $t; });
        $state = $parser->finalState(function () {});

        $this->assertSame(100, $state['cached_input_tokens']);
    }

    public function test_empty_text_deltas_are_skipped(): void
    {
        // Anthropic occasionally emits empty text_delta — don't fire callback.
        $stream = "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"text_delta\",\"text\":\"\"}}\n\n".
                  "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"text_delta\",\"text\":\"foo\"}}\n\n";

        $deltas = [];
        $parser = new AnthropicSseParser();
        $parser->feed($stream, function ($t) use (&$deltas) { $deltas[] = $t; });

        $this->assertSame(['foo'], $deltas);
    }
}
