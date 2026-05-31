<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiModelRouter;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Kısa / ham bir sayfa tarifini, AI ile ayrıntılı bir prompt'a dönüştürür.
 *
 * Kullanım:
 *   POST /admin/ai/boost-prompt
 *   Body: { "prompt": "diş kliniği hizmetler sayfası", "locale": "tr" }
 *   Response: { "ok": true, "boosted": "Hero bölümü: ..." }
 */
class BoostPromptController extends Controller
{
    private const SYSTEM = <<<PROMPT
Sen bir web sayfası içerik stratejisti ve UX uzmanısın.
Kullanıcıdan kısa bir sayfa tarifi alırsın ve bunu, bir sayfa üreteci AI'a
verilecek detaylı, yapılandırılmış bir prompt'a dönüştürürsün.

Kurallar:
- Tarifin dilini koru (Türkçe geldiyse Türkçe, İngilizce geldiyse İngilizce yaz).
- Sadece geliştirilmiş prompt'u döndür; başlık, açıklama veya ön söz YAZMA.
- Önerilen bölümleri, içerik tiplerini ve CTA'ları somutlaştır.
- Maksimum 300 kelime, yapılandırılmış paragraf halinde yaz.
- Teknik terim veya JSON kullanma — düz metin yeterli.
PROMPT;

    public function __invoke(
        Request $request,
        AiModelRouter $router,
        AiQuotaService $quota,
    ): JsonResponse {
        $validated = $request->validate([
            'prompt' => 'required|string|min:3|max:500',
            'locale' => 'nullable|string|max:5',
        ]);

        $tenant = tenancy()->initialized ? tenant() : null;

        try {
            $quota->assertWithinQuota($tenant, estimatedTokens: 600);
        } catch (AiQuotaExceededException $e) {
            return response()->json([
                'ok'         => false,
                'error_code' => 'quota_exceeded',
                'message'    => $e->getMessage(),
            ], 402);
        }

        $locale = $validated['locale'] ?? 'tr';
        $langNote = match ($locale) {
            'en' => 'Write the enhanced prompt in English.',
            'de' => 'Schreibe den verbesserten Prompt auf Deutsch.',
            'ar' => 'اكتب الموجه المحسّن باللغة العربية.',
            'ru' => 'Напишите улучшенный промпт на русском языке.',
            default => 'Geliştirilmiş promptu Türkçe yaz.',
        };

        $user = "Sayfa tarifi: {$validated['prompt']}\n\n{$langNote}\n\nGeliştirilmiş prompt:";

        try {
            $response = $router->generate(
                feature:  'page.create',
                prompt:   $user,
                system:   self::SYSTEM,
                tenant:   $tenant,
                metadata: ['source' => 'boost.prompt'],
            );
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok'      => false,
                'message' => 'AI sağlayıcısı yanıt veremedi: '.$e->getMessage(),
            ], 500);
        }

        $boosted = trim($response->content);

        // Strip any accidental fences/prefixes the model might add
        $boosted = preg_replace('/^```[a-z]*\n?/i', '', $boosted);
        $boosted = rtrim($boosted, '`');
        $boosted = trim($boosted);

        return response()->json(['ok' => true, 'boosted' => $boosted]);
    }
}
