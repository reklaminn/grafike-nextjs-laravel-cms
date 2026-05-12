<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\SectionTemplate;
use App\Services\Ai\AiManager;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use App\Services\Ai\TenantAiResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Streaming block-edit endpoint (FAZ 3.6).
 *
 * Emits Server-Sent Events as the model generates tokens. Frontend
 * uses EventSource (or a fetch reader) to render the text live —
 * "typewriter" UX that lets the admin abort early if the rewrite is
 * obviously going the wrong way.
 *
 * Wire format:
 *   event: delta
 *   data: {"text":"merhaba "}
 *
 *   event: delta
 *   data: {"text":"dünya"}
 *
 *   event: done
 *   data: {"provider":"anthropic","model":"haiku","input_tokens":120,"output_tokens":35,"stop_reason":"end_turn"}
 *
 *   event: error
 *   data: {"error_code":"quota_exceeded","message":"…"}
 *
 * Quota: pre-check (assertWithinQuota) BEFORE opening the stream;
 * post-record after the stream closes with the actual token usage from
 * the final SSE event. If the client aborts mid-stream, we still
 * persist whatever tokens were consumed.
 *
 * Note: this controller is intentionally separate from BlockEditController
 * because streaming makes JSON-shape post-processing impossible — we
 * stream raw text deltas, and the frontend assembles the rewritten
 * content client-side. Schema-aware text-only filtering happens via the
 * prompt itself (system message tells the model to return same JSON).
 */
class StreamBlockEditController extends Controller
{
    public function __invoke(
        Request $request,
        AiManager $manager,
        TenantAiResolver $tenantResolver,
        AiQuotaService $quota,
    ): StreamedResponse {
        $validated = $request->validate([
            'prompt'              => 'required|string|max:2000',
            'tier'                => 'nullable|in:simple,complex',
            'system'              => 'nullable|string|max:2000',
            'section_template_id' => 'nullable|integer',
        ]);

        $tier   = $validated['tier'] ?? 'simple';
        $tenant = tenancy()->initialized ? tenant() : null;

        // Quota pre-check — refuse the connection BEFORE streaming opens.
        try {
            $quota->assertWithinQuota($tenant, estimatedTokens: 1000);
        } catch (AiQuotaExceededException $e) {
            return $this->errorStream('quota_exceeded', $e->getMessage(), 402);
        }

        $resolved = $tenantResolver->resolve($tenant, $tier);

        // Optional system prompt enrichment from section template.
        $system = $validated['system'] ?? null;
        if (! empty($validated['section_template_id'])) {
            $tpl = SectionTemplate::query()->find($validated['section_template_id']);
            if ($tpl && is_array($tpl->schema_json)) {
                $schemaHint = "Schema keys: ".implode(', ', array_keys($tpl->schema_json));
                $system = trim(($system ?: '')."\n\n".$schemaHint);
            }
        }

        return response()->stream(function () use (
            $manager, $resolved, $validated, $system, $quota, $tenant
        ) {
            $aiRequest = new AiRequest(
                model:          $resolved['model'],
                messages:       [AiMessage::user($validated['prompt'])],
                system:         $system,
                maxTokens:      1500,
                temperature:    0.7,
                metadata:       ['source' => 'stream.block-edit', 'tenant_id' => $tenant?->getKey(), 'tier' => $resolved['tier'] ?? null],
                apiKeyOverride: $resolved['api_key'],
            );

            try {
                $response = $manager->provider($resolved['provider'])->stream(
                    $aiRequest,
                    function (string $textChunk) {
                        $this->sse('delta', ['text' => $textChunk]);
                    },
                );

                // Final usage event
                $this->sse('done', [
                    'provider'      => $response->provider,
                    'model'         => $response->model,
                    'input_tokens'  => $response->usage->inputTokens,
                    'output_tokens' => $response->usage->outputTokens,
                    'stop_reason'   => $response->stopReason,
                ]);

                // Persist usage for billing/dashboards
                $quota->recordSuccess(
                    tenant:       $tenant,
                    feature:      'block.edit',
                    response:     $response,
                    byok:         (bool) ($resolved['byok'] ?? false),
                    fallbackUsed: false,
                    extraMetadata: ['streamed' => true, 'tier' => $resolved['tier'] ?? null],
                );
            } catch (Throwable $e) {
                report($e);
                $this->sse('error', ['message' => $e->getMessage()]);

                // Still record failure for ops visibility
                $quota->recordFailure(
                    tenant:    $tenant,
                    feature:   'block.edit',
                    provider:  $resolved['provider'],
                    model:     $resolved['model'],
                    error:     $e,
                    byok:      (bool) ($resolved['byok'] ?? false),
                    extraMetadata: ['streamed' => true],
                );
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no', // disable nginx buffering — chunks arrive immediately
            'Connection'        => 'keep-alive',
        ]);
    }

    /** Emit a single SSE event. */
    private function sse(string $event, array $payload): void
    {
        echo "event: {$event}\n";
        echo 'data: '.json_encode($payload, JSON_UNESCAPED_UNICODE)."\n\n";
        if (ob_get_level() > 0) {
            @ob_flush();
        }
        @flush();
    }

    private function errorStream(string $code, string $message, int $status): StreamedResponse
    {
        return response()->stream(function () use ($code, $message) {
            echo "event: error\n";
            echo 'data: '.json_encode(['error_code' => $code, 'message' => $message], JSON_UNESCAPED_UNICODE)."\n\n";
            @ob_flush();
            @flush();
        }, $status, [
            'Content-Type'  => 'text/event-stream',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
