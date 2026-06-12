<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\SectionTemplate;
use App\Services\Ai\AiBlockEditor;
use App\Services\Ai\AiManager;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use App\Services\Ai\TenantAiResolver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Streaming block-edit endpoint (FAZ 3.6).
 *
 * Aynı parametreleri BlockEditController gibi alır (action, content, schema …)
 * ama yanıtı Server-Sent Events olarak akar.
 *
 * SSE wire format:
 *   event: delta
 *   data: {"text":"merhaba "}
 *
 *   event: done
 *   data: {"content":{...merged...},"provider":"anthropic","model":"haiku","input_tokens":120,"output_tokens":35}
 *
 *   event: error
 *   data: {"error_code":"quota_exceeded","message":"…"}
 *
 * Frontend ReadableStream ile delta chunk'larını gösterir (typewriter efekt),
 * done event'indeki content'i settingsDraft.content'e yazar.
 */
class StreamBlockEditController extends Controller
{
    public function __invoke(
        Request $request,
        AiManager $manager,
        TenantAiResolver $tenantResolver,
        AiQuotaService $quota,
        AiBlockEditor $editor,
    ): StreamedResponse {
        $actions = array_keys($editor->availableActions());

        $validated = $request->validate([
            'content'             => 'required|array',
            'action'              => ['required', 'string', Rule::in([...$actions, 'custom'])],
            'custom_prompt'       => 'nullable|string|max:500',
            'schema'              => 'nullable|array',
            'section_template_id' => 'nullable|integer',
        ]);

        $tenant = tenancy()->initialized ? tenant() : null;

        // Quota pre-check — refuse before the stream opens.
        try {
            $quota->assertWithinQuota($tenant, estimatedTokens: 1000);
        } catch (AiQuotaExceededException $e) {
            return $this->errorStream('quota_exceeded', $e->getMessage(), 402);
        }

        // Build prompt using the same logic as BlockEditController / AiBlockEditor.
        $schema = $validated['schema'] ?? null;
        if ($schema === null && ! empty($validated['section_template_id'])) {
            $tpl = SectionTemplate::query()->find($validated['section_template_id']);
            if ($tpl && is_array($tpl->schema_json)) {
                $schema = $tpl->schema_json;
            }
        }

        try {
            $parts = $editor->buildPromptParts(
                content:      $validated['content'],
                schema:       $schema,
                action:       $validated['action'],
                customPrompt: $validated['custom_prompt'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return $this->errorStream('invalid_request', $e->getMessage(), 422);
        }

        // Nothing editable → return original content immediately without a stream.
        if ($parts['user'] === null) {
            return response()->stream(function () use ($validated) {
                $this->sse('done', [
                    'content'       => $validated['content'],
                    'input_tokens'  => 0,
                    'output_tokens' => 0,
                    'note'          => 'Bu blokta düzenlenebilir metin alanı bulunamadı.',
                ]);
            }, 200, $this->sseHeaders());
        }

        $resolved = $tenantResolver->resolve($tenant, 'simple');

        return response()->stream(function () use (
            $manager, $resolved, $parts, $validated, $schema, $quota, $tenant, $editor
        ) {
            $accumulated = '';

            $aiRequest = new AiRequest(
                model:          $resolved['model'],
                messages:       [AiMessage::user($parts['user'])],
                system:         $parts['system'],
                maxTokens:      1500,
                temperature:    0.7,
                metadata:       ['source' => 'stream.block-edit', 'tenant_id' => $tenant?->getKey()],
                apiKeyOverride: $resolved['api_key'],
            );

            try {
                $response = $manager->provider($resolved['provider'])->stream(
                    $aiRequest,
                    function (string $chunk) use (&$accumulated) {
                        $accumulated .= $chunk;
                        $this->sse('delta', ['text' => $chunk]);
                    },
                );

                // Parse accumulated JSON + merge onto original content.
                try {
                    $mergedContent = $editor->applyRawOutput(
                        $validated['content'],
                        $accumulated,
                        $schema,
                    );
                } catch (Throwable) {
                    $mergedContent = null; // frontend falls back to no-op
                }

                $this->sse('done', [
                    'content'       => $mergedContent,
                    'provider'      => $response->provider,
                    'model'         => $response->model,
                    'input_tokens'  => $response->usage->inputTokens,
                    'output_tokens' => $response->usage->outputTokens,
                    'stop_reason'   => $response->stopReason,
                ]);

                $quota->recordSuccess(
                    tenant:        $tenant,
                    feature:       'block.edit',
                    response:      $response,
                    byok:          (bool) ($resolved['byok'] ?? false),
                    fallbackUsed:  false,
                    extraMetadata: ['streamed' => true, 'action' => $validated['action']],
                );
            } catch (Throwable $e) {
                report($e);
                $this->sse('error', ['message' => $e->getMessage()]);

                $quota->recordFailure(
                    tenant:        $tenant,
                    feature:       'block.edit',
                    provider:      $resolved['provider'],
                    model:         $resolved['model'],
                    error:         $e,
                    byok:          (bool) ($resolved['byok'] ?? false),
                    extraMetadata: ['streamed' => true],
                );
            }
        }, 200, $this->sseHeaders());
    }

    private function sse(string $event, array $payload): void
    {
        echo "event: {$event}\n";
        echo 'data: '.json_encode($payload, JSON_UNESCAPED_UNICODE)."\n\n";
        if (ob_get_level() > 0) {
            @ob_flush();
        }
        @flush();
    }

    private function sseHeaders(): array
    {
        return [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ];
    }

    private function errorStream(string $code, string $message, int $status): StreamedResponse
    {
        return response()->stream(function () use ($code, $message) {
            $this->sse('error', ['error_code' => $code, 'message' => $message]);
        }, $status, $this->sseHeaders());
    }
}
