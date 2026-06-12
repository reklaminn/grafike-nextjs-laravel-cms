<?php

namespace App\Services\Ai;

use App\Models\Article;
use App\Models\Language;
use App\Models\Page;
use App\Models\Tenant;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Batch translator for full page/article payloads.
 *
 * Single API call per content item: walks the source object, flattens
 * every text-typed value into a {t0,t1,t2,…} dictionary keyed by stable
 * IDs, sends one prompt, and re-injects the translations back into the
 * original structure via dot-path replacement.
 *
 * Why batch: a typical page has 8-15 blocks, each with 3-6 text fields.
 * Per-field calls would cost ~50× more tokens (each call's system prompt
 * is re-paid) and burn the quota fast. One call = one bill.
 *
 * Schema-aware: technical keys (image_id, url, color, render_mode, …)
 * are skipped by the collector and never reach the model.
 */
class AiTranslator
{
    /** Maximum aggregate text size sent in a single batch (~3-4K tokens). */
    private const MAX_BATCH_CHARS = 15000;

    /**
     * Keys that look like technical identifiers; their values are never
     * sent for translation even if the value is a string.
     */
    private const SKIP_KEYS = [
        'id', '_uid', 'uid', 'type', 'template', 'template_id',
        'section_template_id', 'image_id', 'media_id', 'url', 'href',
        'color', 'icon', 'class', 'css', 'align', 'size', 'background',
        'video_url', 'embed', 'tag', 'render_mode', 'variation',
        'sort_order', 'width', 'slug', 'page_template', 'page_template_id',
    ];

    public function __construct(private readonly AiModelRouter $router)
    {
    }

    /**
     * Translate a Page's translatable fields and its sections_json layout.
     *
     * @return array{
     *     fields: array<string,string>,
     *     sections_json: array|null,
     *     source_lang: string,
     *     target_lang: string,
     * }
     */
    public function translatePage(Page $page, Language $target, ?Tenant $tenant = null): array
    {
        $sourceLang = $page->language;
        $fields     = $this->collectPageFields($page);
        $translatedFields = $this->translateBatch($fields, $sourceLang, $target, $tenant, $page);
        $newSections      = null;

        if (is_array($page->sections_json) && ! empty($page->sections_json)) {
            $newSections = $this->translateSectionsJson(
                $page->sections_json, $sourceLang, $target, $tenant, $page
            );
        }

        return [
            'fields'        => $translatedFields,
            'sections_json' => $newSections,
            'source_lang'   => $sourceLang?->name ?? 'auto',
            'target_lang'   => $target->name,
        ];
    }

    /**
     * Translate an Article's translatable fields. Articles use plain
     * content_json or body text, no region structure.
     *
     * @return array{
     *     fields: array<string,string>,
     *     content_json: array|null,
     *     source_lang: string,
     *     target_lang: string,
     * }
     */
    public function translateArticle(Article $article, Language $target, ?Tenant $tenant = null): array
    {
        $sourceLang = $article->language;
        $fields     = $this->collectArticleFields($article);
        $translatedFields = $this->translateBatch($fields, $sourceLang, $target, $tenant, $article);
        $newContentJson   = null;

        if (is_array($article->content_json) && ! empty($article->content_json)) {
            $newContentJson = $this->translateGenericArray(
                $article->content_json, $sourceLang, $target, $tenant, $article
            );
        }

        return [
            'fields'       => $translatedFields,
            'content_json' => $newContentJson,
            'source_lang'  => $sourceLang?->name ?? 'auto',
            'target_lang'  => $target->name,
        ];
    }

    // ────────────────────────────────────────────────────────────────────

    private function collectPageFields(Page $page): array
    {
        $fields = ['title' => (string) $page->title];

        $seo = $page->seo;
        if ($seo) {
            if ($seo->meta_title)       $fields['seo_title']       = (string) $seo->meta_title;
            if ($seo->meta_description) $fields['seo_description'] = (string) $seo->meta_description;
            if ($seo->meta_keywords)    $fields['seo_keywords']    = (string) $seo->meta_keywords;
            if ($seo->h1_override)      $fields['seo_h1']          = (string) $seo->h1_override;
        }

        return array_filter($fields, fn ($v) => $v !== '');
    }

    private function collectArticleFields(Article $article): array
    {
        $fields = ['title' => (string) $article->title];

        if ($article->excerpt) {
            $fields['excerpt'] = (string) $article->excerpt;
        }
        if ($article->body) {
            // Truncate body to keep prompt size sane; admin can paste rest
            // manually if it gets cut.
            $fields['body'] = mb_substr((string) $article->body, 0, 10000);
        }

        $seo = $article->seo ?? null;
        if ($seo) {
            if ($seo->meta_title)       $fields['seo_title']       = (string) $seo->meta_title;
            if ($seo->meta_description) $fields['seo_description'] = (string) $seo->meta_description;
            if ($seo->meta_keywords)    $fields['seo_keywords']    = (string) $seo->meta_keywords;
        }

        return array_filter($fields, fn ($v) => $v !== '');
    }

    /**
     * Translate sections_json (region-based v2). Collects every text leaf
     * from block content (and only block content — block ids, types,
     * template references, sort orders stay intact), translates in one
     * batch, and re-injects.
     */
    private function translateSectionsJson(
        array $sections,
        ?Language $source,
        Language $target,
        ?Tenant $tenant,
        Page|Article $context,
    ): array {
        return $this->translateGenericArray($sections, $source, $target, $tenant, $context);
    }

    /**
     * Walk any array (sections_json, content_json) and translate every
     * eligible string leaf in one call.
     */
    private function translateGenericArray(
        array $node,
        ?Language $source,
        Language $target,
        ?Tenant $tenant,
        Page|Article $context,
    ): array {
        $texts = [];
        $paths = [];
        $this->collectTexts($node, '', $texts, $paths);

        if (empty($texts)) {
            return $node;
        }

        $translatedDict = $this->translateBatch($texts, $source, $target, $tenant, $context);

        // Put each translated value back at its original dot-path.
        $copy = $node;
        foreach ($paths as $key => $path) {
            if (isset($translatedDict[$key]) && is_string($translatedDict[$key])) {
                Arr::set($copy, $path, $translatedDict[$key]);
            }
        }

        return $copy;
    }

    /**
     * Recursive text collector. Appends to $texts as {t0, t1, …} keyed
     * by stable index, and tracks the dot-path of each value in $paths.
     */
    private function collectTexts(array $node, string $parentPath, array &$texts, array &$paths): void
    {
        foreach ($node as $key => $value) {
            $path = $parentPath === '' ? (string) $key : "{$parentPath}.{$key}";

            if (is_array($value)) {
                $this->collectTexts($value, $path, $texts, $paths);
                continue;
            }
            if (! is_string($value)) {
                continue;
            }
            if (mb_strlen(trim($value)) < 2) {
                continue;
            }
            if (is_string($key) && in_array(strtolower($key), self::SKIP_KEYS, true)) {
                continue;
            }

            $id         = 't'.count($texts);
            $texts[$id] = $value;
            $paths[$id] = $path;
        }
    }

    /**
     * One API call to translate a dictionary of strings. Keys preserved.
     * Splits into multiple batches if the aggregate size exceeds
     * MAX_BATCH_CHARS — keeps a single page well under the model's
     * context window even with very wordy content.
     *
     * @param  array<string, string>  $items
     * @return array<string, string>
     */
    private function translateBatch(
        array $items,
        ?Language $source,
        Language $target,
        ?Tenant $tenant,
        Page|Article $context,
    ): array {
        if (empty($items)) {
            return [];
        }

        $batches = $this->splitIntoBatches($items, self::MAX_BATCH_CHARS);
        $result  = [];

        foreach ($batches as $batch) {
            $result += $this->translateOneBatch($batch, $source, $target, $tenant, $context);
        }

        return $result;
    }

    /**
     * @param  array<string, string>  $batch
     * @return array<string, string>
     */
    private function translateOneBatch(
        array $batch,
        ?Language $source,
        Language $target,
        ?Tenant $tenant,
        Page|Article $context,
    ): array {
        $sourceName = $source?->name ?: 'kaynak dil';
        $targetName = $target->name;

        $system = $this->systemPrompt($sourceName, $targetName);
        $user   = $this->userPrompt($batch);

        try {
            $response = $this->router->generate(
                feature:  'page.translate',
                prompt:   $user,
                system:   $system,
                tenant:   $tenant,
                metadata: [
                    'source'      => 'ai.translator',
                    'source_lang' => $source?->code,
                    'target_lang' => $target->code,
                    'context_id'  => $context->getKey(),
                    'context_type' => $context::class,
                ],
            );
        } catch (AiProviderException $e) {
            throw new RuntimeException('Çeviri sağlayıcısı yanıt veremedi: '.$e->getMessage(), 0, $e);
        }

        $decoded = $this->parseJson($response->content);

        // Keep only declared keys with string values; missing keys fall
        // back to the original text so the editor still gets a complete
        // result even on a partial AI failure.
        $out = [];
        foreach ($batch as $key => $original) {
            $out[$key] = (isset($decoded[$key]) && is_string($decoded[$key]) && $decoded[$key] !== '')
                ? $decoded[$key]
                : $original;
        }

        return $out;
    }

    /**
     * @param  array<string, string>  $items
     * @return array<int, array<string, string>>
     */
    private function splitIntoBatches(array $items, int $maxChars): array
    {
        $batches = [];
        $current = [];
        $size    = 0;

        foreach ($items as $key => $value) {
            $valLen = mb_strlen($value);
            if ($size > 0 && ($size + $valLen) > $maxChars) {
                $batches[] = $current;
                $current   = [];
                $size      = 0;
            }
            $current[$key] = $value;
            $size += $valLen;
        }
        if (! empty($current)) {
            $batches[] = $current;
        }

        return $batches;
    }

    private function systemPrompt(string $sourceName, string $targetName): string
    {
        return <<<PROMPT
Sen profesyonel bir tercümansın. Verilen JSON nesnesindeki tüm değerleri
{$sourceName} dilinden {$targetName} diline çevirirsin.

KATI KURALLAR:
- Aynı anahtarları (keys) kullan; yeni anahtar ekleme, anahtar silme.
- Sadece STRING değerleri çevir. Sayı veya null değerlere dokunma.
- Değer içinde HTML etiketi varsa (örn. <strong>, <br>, <a>) etiket
  yapısını AYNEN koru, sadece etiketler arasındaki metni çevir.
- URL'leri, e-posta adreslerini, telefon numaralarını, sayıları,
  marka adlarını, kod parçalarını ÇEVİRME — aynen koru.
- Doğal ve akıcı bir çeviri yap. Cümle başına bir anlam ver, kelime
  kelime çeviri yapma.

ÇIKTI BİÇİMİ: Sadece geçerli JSON. Kod bloğu YOK, ön söz YOK, ek
açıklama YOK.

Örnek:
Giriş: {"t0":"Hizmetler","t1":"İletişime geçin"}
Çıkış: {"t0":"Services","t1":"Get in touch"}
PROMPT;
    }

    private function userPrompt(array $batch): string
    {
        $payload = json_encode($batch, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return "Çevrilecek alanlar:\n{$payload}\n\nÇIKTI: Aynı anahtarlarla, değerleri çevrilmiş JSON.";
    }

    private function parseJson(string $raw): array
    {
        $json = trim($raw);

        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/s', $json, $m)) {
            $json = $m[1];
        }
        if (! Str::startsWith($json, '{') && preg_match('/(\{.*\})/s', $json, $m)) {
            $json = $m[1];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('AI yanıtı geçerli JSON değil: '.mb_substr($raw, 0, 200));
        }

        return $decoded;
    }
}
