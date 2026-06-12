<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Page;
use App\Services\Ai\AiPageGenerator;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Generate a brand-new page from a natural-language prompt.
 *
 * Two flows in one endpoint:
 *  - Preview mode (default): generate and return the structure for the
 *    UI to inspect; nothing is persisted.
 *  - Auto-save mode (`auto_save=true`): also create the Page record in
 *    the tenant DB with status=draft and return a redirect URL to the
 *    edit screen.
 *
 * Either way the admin still polishes the result in the regular editor;
 * this controller just bootstraps the page so the blank-canvas problem
 * disappears.
 */
class PageGenerateController extends Controller
{
    public function __invoke(Request $request, AiPageGenerator $generator): JsonResponse
    {
        $validated = $request->validate([
            'prompt'      => 'required|string|min:5|max:1000',
            'language_id' => 'nullable|integer',
            'parent_id'   => 'nullable|integer',
            'auto_save'   => 'nullable|boolean',
            'locale'      => 'nullable|string|max:5',
            'async'       => 'nullable|boolean',
        ]);

        $tenant = tenancy()->initialized ? tenant() : null;
        $locale = $validated['locale'] ?? $this->resolveLocale($validated['language_id'] ?? null);

        // ── Async mod: job'u kuyruğa at, UI status endpoint'ini poll'lar ──
        // (queue=sync ortamlarda dispatch inline çalışır; davranış aynı kalır)
        if (filter_var($validated['async'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $jobId = (string) \Illuminate\Support\Str::uuid();

            \Illuminate\Support\Facades\Cache::put(
                \App\Jobs\Ai\GenerateAiPageJob::cacheKey($jobId),
                ['status' => 'queued'],
                3600
            );

            \App\Jobs\Ai\GenerateAiPageJob::dispatch(
                jobId:      $jobId,
                prompt:     $validated['prompt'],
                locale:     $locale,
                languageId: $validated['language_id'] ?? null,
                parentId:   $validated['parent_id'] ?? null,
                autoSave:   filter_var($validated['auto_save'] ?? false, FILTER_VALIDATE_BOOLEAN),
            );

            return response()->json([
                'ok'         => true,
                'mode'       => 'queued',
                'job_id'     => $jobId,
                'status_url' => route('admin.ai.generate-page.status', $jobId, false),
            ]);
        }

        try {
            $result = $generator->generate(
                prompt: $validated['prompt'],
                tenant: $tenant,
                locale: $locale,
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
                'error_code' => 'generation_failed',
                'message'    => $e->getMessage(),
            ], 500);
        }

        if (filter_var($validated['auto_save'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $page = Page::create([
                'title'         => $result['title'],
                'slug'          => $this->uniqueSlug($result['slug']),
                'language_id'   => $validated['language_id'] ?? null,
                'parent_id'     => $validated['parent_id'] ?? null,
                'status'        => 'draft',
                'sections_json' => $result['sections_json'],
                'show_in_menu'  => false,
            ]);

            return response()->json([
                'ok'           => true,
                'mode'         => 'saved',
                'page_id'      => $page->id,
                'title'        => $page->title,
                'slug'         => $page->slug,
                'block_count'  => count($result['picked_template_ids']),
                'redirect_url' => route('admin.pages.edit', $page, false),
            ]);
        }

        return response()->json([
            'ok'      => true,
            'mode'    => 'preview',
            'preview' => $result,
        ]);
    }

    /**
     * GET /admin/ai/generate-page/status/{jobId}
     * Async üretimin durumunu döner: queued | running | done | failed.
     */
    public function status(string $jobId): JsonResponse
    {
        if (! preg_match('/^[0-9a-f\-]{36}$/', $jobId)) {
            abort(400, 'Geçersiz job id.');
        }

        $state = \Illuminate\Support\Facades\Cache::get(
            \App\Jobs\Ai\GenerateAiPageJob::cacheKey($jobId)
        );

        if (! $state) {
            return response()->json([
                'ok'     => false,
                'status' => 'not_found',
                'message'=> 'Job bulunamadı veya süresi doldu.',
            ], 404);
        }

        return response()->json(['ok' => true] + $state);
    }

    /**
     * Pick a 2-letter locale from the language model if available, fall
     * back to Turkish. Languages live on the central connection.
     */
    private function resolveLocale(?int $languageId): string
    {
        if ($languageId) {
            $lang = Language::query()->find($languageId);
            if ($lang && ! empty($lang->code)) {
                return strtolower((string) $lang->code);
            }
        }

        return 'tr';
    }

    /**
     * Append "-2", "-3", … until the slug is free in the current tenant DB.
     */
    private function uniqueSlug(string $base): string
    {
        if (! Page::query()->where('slug', $base)->exists()) {
            return $base;
        }
        for ($i = 2; $i < 200; $i++) {
            $candidate = $base.'-'.$i;
            if (! Page::query()->where('slug', $candidate)->exists()) {
                return $candidate;
            }
        }

        // Fallback — astronomically unlikely
        return $base.'-'.substr(uniqid(), -4);
    }
}
