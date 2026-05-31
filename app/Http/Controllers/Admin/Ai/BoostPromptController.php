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
 * Kısa / ham bir tarifi, AI ile ayrıntılı bir prompt'a dönüştürür.
 *
 * Kullanım:
 *   POST /admin/ai/boost-prompt
 *   Body: {
 *     "prompt": "diş kliniği hizmetler sayfası",
 *     "locale": "tr",
 *     "context": "page"|"block",       // üretim bağlamı (varsayılan: page)
 *     "image_base64": "<base64>",       // opsiyonel referans görsel
 *     "image_mime":   "image/jpeg"
 *   }
 *   Response: { "ok": true, "boosted": "..." }
 */
class BoostPromptController extends Controller
{
    /** Sayfa oluşturma bağlamı için sistem promptu */
    private const SYSTEM_PAGE = <<<PROMPT
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

    /** Block şablon oluşturma bağlamı için sistem promptu */
    private const SYSTEM_BLOCK = <<<PROMPT
Sen bir UI/UX ve front-end uzmanısın.
Kullanıcıdan kısa bir HTML blok şablon tarifi (ve opsiyonel bir referans görsel) alırsın;
bunu bir blok şablon üretici AI'a verilecek somut, teknik, detaylı bir prompt'a dönüştürürsün.

Kurallar:
- Tarifin dilini koru (Türkçe geldiyse Türkçe, İngilizce geldiyse İngilizce yaz).
- Sadece geliştirilmiş prompt'u döndür; başlık, açıklama veya ön söz YAZMA.
- Referans görsel varsa: renk paleti, tipografi boyutu, grid düzeni, boşluk ritmi, ikonlar,
  hover efektleri, responsive breakpoint'leri gibi görsel öğeleri prompt'a yansıt.
- Layout, grid kolonları, mobil uyumluluk, Tailwind class önerisi, hover/animasyon
  gibi teknik ayrıntıları ekle.
- Schema alanları (başlık, alt başlık, CTA metni, CTA URL, görsel URL vb.) ne olmalı, belirt.
- Maksimum 350 kelime; yapılandırılmış, sade metin — JSON veya kod bloğu YAZMA.
PROMPT;

    public function __invoke(
        Request $request,
        AiModelRouter $router,
        AiQuotaService $quota,
    ): JsonResponse {
        $validated = $request->validate([
            'prompt'       => 'required|string|min:3|max:1000',
            'locale'       => 'nullable|string|max:5',
            'context'      => 'nullable|string|in:page,block',
            'image_base64' => 'nullable|string|max:6000000',
            'image_mime'   => 'nullable|string|in:image/jpeg,image/png,image/webp,image/gif',
        ]);

        $tenant = tenancy()->initialized ? tenant() : null;

        try {
            $quota->assertWithinQuota($tenant, estimatedTokens: 800);
        } catch (AiQuotaExceededException $e) {
            return response()->json([
                'ok'         => false,
                'error_code' => 'quota_exceeded',
                'message'    => $e->getMessage(),
            ], 402);
        }

        $context = $validated['context'] ?? 'page';
        $system  = $context === 'block' ? self::SYSTEM_BLOCK : self::SYSTEM_PAGE;

        $locale = $validated['locale'] ?? 'tr';
        $langNote = match ($locale) {
            'en'    => 'Write the enhanced prompt in English.',
            'de'    => 'Schreibe den verbesserten Prompt auf Deutsch.',
            'ar'    => 'اكتب الموجه المحسّن باللغة العربية.',
            'ru'    => 'Напишите улучшенный промпт на русском языке.',
            default => 'Geliştirilmiş promptu Türkçe yaz.',
        };

        $hasImage = !empty($validated['image_base64']);

        $user = $context === 'block'
            ? ($hasImage
                ? "Referans görseli analiz et ve şu tarifi hem görseldeki tasarım öğelerini hem de metni göz önünde bulundurarak geliştir:\n\nTarif: {$validated['prompt']}\n\n{$langNote}\n\nGeliştirilmiş prompt:"
                : "Şu blok şablon tarifini geliştir:\n\nTarif: {$validated['prompt']}\n\n{$langNote}\n\nGeliştirilmiş prompt:")
            : "Sayfa tarifi: {$validated['prompt']}\n\n{$langNote}\n\nGeliştirilmiş prompt:";

        try {
            $response = $router->generate(
                feature:       'page.create',
                prompt:        $user,
                system:        $system,
                tenant:        $tenant,
                metadata:      ['source' => 'boost.prompt', 'context' => $context],
                imageBase64:   $hasImage ? $validated['image_base64'] : null,
                imageMimeType: $validated['image_mime'] ?? 'image/jpeg',
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
