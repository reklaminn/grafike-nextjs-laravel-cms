<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\SectionTemplate;
use App\Services\Ai\AiBlockEditor;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Throwable;

/**
 * AJAX endpoint that runs an AI-driven transformation on a single block's
 * content payload (shorten, professional tone, SEO-optimize, translate, …)
 * and returns the rewritten content for the editor to preview.
 *
 * No DB writes — the admin previews + applies via the page form's normal
 * save flow.
 */
class BlockEditController extends Controller
{
    public function __invoke(Request $request, AiBlockEditor $editor): JsonResponse
    {
        $actions = array_keys($editor->availableActions());

        $validated = $request->validate([
            'content'       => 'required|array',
            'action'        => ['required', 'string', Rule::in([...$actions, 'custom'])],
            'custom_prompt' => 'nullable|string|max:500',
            // Either pass schema_json inline …
            'schema'              => 'nullable|array',
            // … or just the SectionTemplate id and we'll fetch it.
            'section_template_id' => 'nullable|integer',
        ]);

        $schema = $validated['schema'] ?? null;
        if ($schema === null && ! empty($validated['section_template_id'])) {
            $tpl = SectionTemplate::query()
                ->whereKey($validated['section_template_id'])
                ->first();
            if ($tpl && is_array($tpl->schema_json)) {
                $schema = $tpl->schema_json;
            }
        }

        $tenant = tenancy()->initialized ? tenant() : null;

        try {
            $newContent = $editor->edit(
                content:      $validated['content'],
                schema:       $schema,
                action:       $validated['action'],
                customPrompt: $validated['custom_prompt'] ?? null,
                tenant:       $tenant,
            );
        } catch (AiQuotaExceededException $e) {
            return response()->json([
                'ok'         => false,
                'error_code' => 'quota_exceeded',
                'message'    => $e->getMessage(),
                'limit_type' => $e->limitType,
                'plan'       => $e->plan,
                'used'       => $e->used,
                'limit'      => $e->limit,
            ], 402);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'ok'         => false,
                'error_code' => 'invalid_request',
                'message'    => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok'         => false,
                'error_code' => 'generation_failed',
                'message'    => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'ok'      => true,
            'content' => $newContent,
            'action'  => $validated['action'],
        ]);
    }
}
