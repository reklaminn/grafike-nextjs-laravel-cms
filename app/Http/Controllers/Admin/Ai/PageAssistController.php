<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiPageAssistant;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Page assistant (Madde 3b) — proposes a multi-block change plan
 * (edit / reorder / remove) from a natural-language instruction.
 *
 * Returns the plan ONLY; the editor renders it as a diff and applies it
 * client-side after the admin confirms. Nothing is persisted here, so the
 * regular page save flow remains the single source of truth.
 */
class PageAssistController extends Controller
{
    public function __invoke(
        Request $request,
        AiPageAssistant $assistant,
        AiQuotaService $quota,
    ): JsonResponse {
        $validated = $request->validate([
            'instruction'          => 'required|string|min:4|max:1500',
            'blocks'               => 'required|array|min:1|max:60',
            'blocks.*.ref'         => 'required|integer|min:1',
            'blocks.*.type'        => 'nullable|string|max:80',
            'blocks.*.name'        => 'nullable|string|max:160',
            'blocks.*.content'     => 'nullable|array',
        ]);

        $tenant = tenancy()->initialized ? tenant() : null;

        // Pre-check quota before opening the (non-streamed) generation.
        try {
            $quota->assertWithinQuota($tenant, estimatedTokens: 4000);
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
        }

        try {
            $plan = $assistant->plan(
                instruction: $validated['instruction'],
                blocks:      $validated['blocks'],
                tenant:      $tenant,
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
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok'         => false,
                'error_code' => 'assist_failed',
                'message'    => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'ok'   => true,
            'plan' => $plan,
        ]);
    }
}
