<?php

namespace App\Services\Ai;

use App\Models\Page;
use App\Models\Tenant;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Generates SEO meta (title / description / keywords) for a Page using the
 * AI router. Encapsulates:
 *
 *   1. Text extraction from sections_json / layout_json (provider-neutral
 *      blob → plain text, capped at ~6KB so we don't burn tokens).
 *   2. Prompt construction in the page's language.
 *   3. Strict JSON parsing of the model's reply, with two retry shapes
 *      (raw JSON; or first JSON object inside a fenced block).
 *
 * Caller responsibilities:
 *   - The HTTP layer translates AiQuotaExceededException → 402 response.
 *   - The HTTP layer persists the returned values to seo_entries.
 *   - The user always sees the suggestion before it lands, so we don't
 *     auto-save here; the controller just hands the JSON to the form.
 */
class AiSeoGenerator
{
    /** Maximum characters of page content fed to the model. ~6 KB ≈ 1.5K tok. */
    private const MAX_CONTENT_CHARS = 6000;

    public function __construct(
        private readonly AiModelRouter $router,
    ) {
    }

    /**
     * @return array{title:string, description:string, keywords:string}
     */
    public function generate(Page $page, ?Tenant $tenant = null, ?string $locale = null): array
    {
        $locale  ??= $this->resolveLocale($page);
        $content   = $this->extractText($page);
        $system    = $this->systemPrompt($locale);
        $userPrompt = $this->userPrompt($page, $content, $locale);

        try {
            $response = $this->router->generate(
                feature:  'seo.meta',
                prompt:   $userPrompt,
                system:   $system,
                tenant:   $tenant,
                metadata: ['source' => 'page.seo-generator', 'page_id' => $page->id],
            );
        } catch (AiProviderException $e) {
            // Re-throw with friendlier message; quota exceptions bubble untouched.
            throw new RuntimeException('AI sağlayıcısı yanıt veremedi: '.$e->getMessage(), 0, $e);
        }

        return $this->parseResponse($response->content, $page);
    }

    // ────────────────────────────────────────────────────────────────────

    private function resolveLocale(Page $page): string
    {
        // Page.language_id → 2-letter code; falls back to 'tr'.
        $lang = $page->language;
        if ($lang && ! empty($lang->code)) {
            return strtolower($lang->code);
        }

        return 'tr';
    }

    /**
     * Walk sections_json / layout_json and collect plain-text content
     * from blocks. Output is capped at MAX_CONTENT_CHARS to keep token
     * usage predictable.
     */
    private function extractText(Page $page): string
    {
        $parts = [];

        // Modern Next.js builder
        $sections = $page->sections_json;
        if (is_array($sections)) {
            $this->walkArray($sections, $parts);
        }

        // Legacy region-based builder
        if (empty($parts)) {
            $layout = $page->layout_json;
            if (is_array($layout)) {
                $this->walkArray($layout, $parts);
            }
        }

        $text = implode("\n\n", array_filter(array_map('trim', $parts)));
        $text = preg_replace('/\s+/u', ' ', $text);

        if (mb_strlen($text) > self::MAX_CONTENT_CHARS) {
            $text = mb_substr($text, 0, self::MAX_CONTENT_CHARS).' …';
        }

        return $text;
    }

    /**
     * Recursive collector. We're intentionally permissive — we don't know
     * every block shape ahead of time, so any string-valued leaf that
     * looks textual gets harvested.
     */
    private function walkArray(array $node, array &$out): void
    {
        foreach ($node as $key => $value) {
            if (is_array($value)) {
                $this->walkArray($value, $out);
                continue;
            }
            if (! is_string($value)) {
                continue;
            }
            // Skip obvious non-content fields
            if (is_string($key) && in_array(strtolower($key), [
                'id', '_uid', 'uid', 'type', 'template', 'image_id', 'media_id',
                'url', 'href', 'color', 'icon', 'class', 'css', 'align', 'size',
                'background', 'video_url', 'embed', 'tag',
            ], true)) {
                continue;
            }
            $clean = strip_tags($value);
            if (mb_strlen($clean) >= 4) { // skip "ok", short labels
                $out[] = $clean;
            }
        }
    }

    private function systemPrompt(string $locale): string
    {
        $langName = $this->humanLanguage($locale);

        return <<<PROMPT
Sen profesyonel bir SEO uzmanısın. Verilen sayfa içeriğinden $langName dilinde SEO meta etiketleri üretirsin.

Kurallar:
- title: Maksimum 60 karakter. Sayfanın özünü vurgulayan, kullanıcıyı tıklamaya yönlendiren bir başlık. Marka adı veya site adı EKLEME.
- description: 140-160 karakter arasında. Doğal, akıcı bir cümle. Anahtar kelimeleri içermeli ama kelime yığını yapma. Aksiyon davetiyle bitebilir.
- keywords: 5 ila 8 arası virgülle ayrılmış anahtar kelime / kelime öbeği. Sayfanın gerçekten ele aldığı konularla sınırlı kal — uydurma.

ÇIKTI BİÇİMİ: Sadece geçerli JSON. Açıklama yazma, ön söz yazma, kod bloğu kullanma.

Örnek format:
{"title": "...", "description": "...", "keywords": "..."}
PROMPT;
    }

    private function userPrompt(Page $page, string $content, string $locale): string
    {
        $title    = trim((string) $page->title);
        $slug     = trim((string) $page->slug);
        $template = $page->template ?: $page->page_template ?: null;

        $contentBlock = $content !== ''
            ? "Sayfa içeriği:\n---\n{$content}\n---"
            : '(Sayfa henüz içerik bloğu içermiyor — başlık ve slug üzerinden çıkarım yap.)';

        $hints = [];
        if ($title !== '')    $hints[] = "Sayfa başlığı: {$title}";
        if ($slug !== '')     $hints[] = "URL slug: {$slug}";
        if ($template)        $hints[] = "Şablon türü: {$template}";
        $hints[] = "Hedef dil: {$locale}";

        return implode("\n", $hints)."\n\n".$contentBlock;
    }

    /**
     * Parse the model's reply. We accept two shapes:
     *   1. Raw JSON object as the entire reply.
     *   2. JSON object embedded inside a ```json fenced block (rare; we
     *      told it not to, but providers sometimes ignore that).
     *
     * Defensive defaults: missing fields fall back to the page's existing
     * data so the caller always gets a usable payload.
     */
    private function parseResponse(string $raw, Page $page): array
    {
        $json = trim($raw);

        // Strip ```json fences if present.
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $json, $m)) {
            $json = $m[1];
        }
        // Or grab the first {...} block.
        if (! Str::startsWith($json, '{') && preg_match('/(\{.*\})/s', $json, $m)) {
            $json = $m[1];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('AI yanıtı geçerli JSON değil: '.mb_substr($raw, 0, 200));
        }

        return [
            'title'       => $this->trimToLimit((string) ($decoded['title']       ?? $page->title ?? ''), 70),
            'description' => $this->trimToLimit((string) ($decoded['description'] ?? ''), 160),
            'keywords'    => $this->normalizeKeywords((string) ($decoded['keywords'] ?? '')),
        ];
    }

    private function trimToLimit(string $value, int $maxLen): string
    {
        $value = trim($value);
        if (mb_strlen($value) <= $maxLen) {
            return $value;
        }
        // Trim at last word boundary under the limit
        $trimmed = mb_substr($value, 0, $maxLen);
        $lastSpace = mb_strrpos($trimmed, ' ');
        if ($lastSpace !== false && $lastSpace > $maxLen * 0.6) {
            $trimmed = mb_substr($trimmed, 0, $lastSpace);
        }

        return rtrim($trimmed, " ,.;:");
    }

    private function normalizeKeywords(string $raw): string
    {
        $parts = preg_split('/[,;]+/', $raw) ?: [];
        $parts = array_map('trim', $parts);
        $parts = array_filter($parts, fn ($k) => $k !== '' && mb_strlen($k) <= 50);

        // Dedup case-insensitive while preserving first-seen casing.
        $seen = [];
        $out  = [];
        foreach ($parts as $part) {
            $key = mb_strtolower($part);
            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $out[] = $part;
            }
        }
        $out = array_slice($out, 0, 10);

        return implode(', ', $out);
    }

    private function humanLanguage(string $code): string
    {
        return match (strtolower($code)) {
            'tr' => 'Türkçe',
            'en' => 'İngilizce',
            'de' => 'Almanca',
            'fr' => 'Fransızca',
            'es' => 'İspanyolca',
            'ar' => 'Arapça',
            'ru' => 'Rusça',
            default => strtoupper($code),
        };
    }
}
