<?php

namespace Tests\Unit\Services\Ai\Streaming;

use App\Services\Ai\Streaming\OpenAiSseParser;
use Tests\TestCase;

class OpenAiSseParserTest extends TestCase
{
    public function test_parses_complete_stream_in_single_chunk(): void
    {
        $stream = implode("\n\n", [
            'data: {"id":"x","model":"gpt-4o-mini","choices":[{"delta":{"role":"assistant"},"finish_reason":null,"index":0}]}',
            'data: {"id":"x","model":"gpt-4o-mini","choices":[{"delta":{"content":"merhaba "},"finish_reason":null,"index":0}]}',
            'data: {"id":"x","model":"gpt-4o-mini","choices":[{"delta":{"content":"dünya"},"finish_reason":null,"index":0}]}',
            'data: {"id":"x","model":"gpt-4o-mini","choices":[{"delta":{},"finish_reason":"stop","index":0}]}',
            'data: {"id":"x","choices":[],"usage":{"prompt_tokens":5,"completion_tokens":2,"total_tokens":7}}',
            'data: [DONE]',
        ])."\n\n";

        $deltas = [];
        $cb = function ($t) use (&$deltas) { $deltas[] = $t; };
        $parser = new OpenAiSseParser();
        $parser->feed($stream, $cb);
        $state = $parser->finalState($cb);

        $this->assertSame(['merhaba ', 'dünya'], $deltas);
        $this->assertSame('merhaba dünya', $state['content']);
        $this->assertSame('gpt-4o-mini', $state['model']);
        $this->assertSame('stop', $state['stop_reason']);
        $this->assertSame(5, $state['input_tokens']);
        $this->assertSame(2, $state['output_tokens']);
        $this->assertTrue($parser->isDone());
    }

    public function test_handles_chunks_that_split_mid_event(): void
    {
        $full = 'data: {"choices":[{"delta":{"content":"foo"},"finish_reason":null,"index":0}]}'."\n\n".
                'data: {"choices":[{"delta":{"content":"bar"},"finish_reason":null,"index":0}]}'."\n\n";

        $deltas = [];
        $cb = function ($t) use (&$deltas) { $deltas[] = $t; };
        $parser = new OpenAiSseParser();

        for ($i = 0; $i < strlen($full); $i += 25) {
            $parser->feed(substr($full, $i, 25), $cb);
        }
        $state = $parser->finalState($cb);

        $this->assertSame(['foo', 'bar'], $deltas);
        $this->assertSame('foobar', $state['content']);
    }

    public function test_done_sentinel_stops_consumption(): void
    {
        $stream = 'data: {"choices":[{"delta":{"content":"hi"},"finish_reason":"stop","index":0}]}'."\n\n".
                  'data: [DONE]'."\n\n";

        $parser = new OpenAiSseParser();
        $parser->feed($stream, function () {});
        $parser->finalState(function () {});

        $this->assertTrue($parser->isDone());
    }

    public function test_empty_content_deltas_skipped_but_not_emit(): void
    {
        // First chunk has role only (no content), second has content
        $stream = 'data: {"choices":[{"delta":{"role":"assistant"},"finish_reason":null,"index":0}]}'."\n\n".
                  'data: {"choices":[{"delta":{"content":"merhaba"},"finish_reason":null,"index":0}]}'."\n\n";

        $deltas = [];
        $parser = new OpenAiSseParser();
        $parser->feed($stream, function ($t) use (&$deltas) { $deltas[] = $t; });

        $this->assertSame(['merhaba'], $deltas, 'role-only delta should not fire callback');
    }

    public function test_silently_drops_malformed_event(): void
    {
        $stream = "data: not json\n\n".
                  'data: {"choices":[{"delta":{"content":"ok"},"finish_reason":null,"index":0}]}'."\n\n";

        $deltas = [];
        $parser = new OpenAiSseParser();
        $parser->feed($stream, function ($t) use (&$deltas) { $deltas[] = $t; });

        $this->assertSame(['ok'], $deltas);
    }

    public function test_captures_cached_prompt_tokens_when_present(): void
    {
        $stream = 'data: {"choices":[],"usage":{"prompt_tokens":50,"completion_tokens":10,"prompt_tokens_details":{"cached_tokens":40}}}'."\n\n";

        $parser = new OpenAiSseParser();
        $parser->feed($stream, function () {});
        $state = $parser->finalState(function () {});

        $this->assertSame(50, $state['input_tokens']);
        $this->assertSame(10, $state['output_tokens']);
        $this->assertSame(40, $state['cached_input_tokens']);
    }

    public function test_finish_reason_captured_from_choice(): void
    {
        $stream = 'data: {"choices":[{"delta":{"content":"."},"finish_reason":"length","index":0}]}'."\n\n";

        $parser = new OpenAiSseParser();
        $parser->feed($stream, function () {});
        $state = $parser->finalState(function () {});

        $this->assertSame('length', $state['stop_reason']);
    }
}
