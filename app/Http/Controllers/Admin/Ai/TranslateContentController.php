<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Language;
use App\Models\Page;
use App\Services\Ai\AiTranslator;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Translate a Page or Article into a target language in one shot.
 *
 * Same URL + body shape as the legacy AiAssistantController endpoint so
 * the existing create-translation view's JS keeps working, but the
 * implementation now flows through AiModelRouter — gaining BYOK,
 * per-tenant quota, provider fallback, and rich block-content
 * translation that the legacy endpoint never had.
 *
 * Response shape (backward compatible):
 *   { ok: true, result: { title, seo_title?, seo_description?, …,
 *                         sections_json?, content_json? },
 *     source_lang, target_lang }
 */
class TranslateContentController extends Controller
{
    public function __invoke(Request $request, AiTranslator $translator): JsonResponse
    {
        $validated = $request->validate([
            'type'               => ['required', Rule::in(['page', 'article'])],
            'id'                 => 'required|integer',
            'target_language_id' => ['required', 'integer', Rule::exists('central.languages', 'id')],
        ]);

        $target = Language::query()->findOrFail($validated['target_language_id']);
        $tenant = tenancy()->initialized ? tenant() : null;

        try {
            if ($validated['type'] === 'page') {
                $page = Page::query()
                    ->with(['language', 'seo'])
                    ->findOrFail($validated['id']);
                $result = $translator->translatePage($page, $target, $tenant);

                $payload = $result['fields'];
                if ($result['sections_json'] !== null) {
                    $payload['sections_json'] = $result['sections_json'];
                }
            } else {
                $article = Article::query()
                    ->with(['language', 'seo'])
                    ->findOrFail($validated['id']);
                $result = $translator->translateArticle($article, $target, $tenant);

                $payload = $result['fields'];
                if ($result['content_json'] !== null) {
                    $payload['content_json'] = $result['content_json'];
                }
            }
        } catch (AiQuotaExceededException $e) {
            return response()->json([
                'ok'         => false,
                'error_code' => 'quota_exceeded',
                'error'      => $e->getMessage(),     // legacy key (existing view reads this)
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
                'error_code' => 'translation_failed',
                'error'      => $e->getMessage(),    // legacy key
                'message'    => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'ok'          => true,
            'result'      => $payload,
            'source_lang' => $result['source_lang'],
            'target_lang' => $result['target_lang'],
        ]);
    }
}
