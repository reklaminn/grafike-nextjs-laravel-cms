<?php

namespace App\Services\Ai;

use App\Models\Tenant;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Generates a brand-new SectionTemplate (name + type + html_template +
 * schema_json + default_content_json) from a natural-language description.
 *
 * Audience: firma (agency) admin, not tenants — every tenant shares the
 * SectionTemplate library. The AI cost lands on the agency quota; tenant
 * is null when called from the section-templates admin screen.
 *
 * Quality bar: the model is asked to emit production-ready HTML with
 * Tailwind CSS, semantic markup, mobile-first responsive breakpoints,
 * and {{placeholder}} syntax that exactly matches schema_json keys.
 * The service post-validates this consistency and reports any mismatch
 * back to the UI instead of silently letting bad templates land.
 */
class AiSectionTemplateGenerator
{
    public function __construct(private readonly AiModelRouter $router)
    {
    }

    /**
     * @return array{
     *     name:string, type:string, variation:string,
     *     html_template:string,
     *     schema_json:array,
     *     default_content_json:array,
     *     warnings:array<int,string>
     * }
     */
    public function generate(
        string $prompt,
        ?Tenant $tenant = null,
        ?array $hints = null,
        ?string $imageBase64 = null,
        string $imageMimeType = 'image/jpeg',
        ?string $currentHtml = null,
        ?array $currentSchema = null,
    ): array {
        $system = $this->systemPrompt();
        $user   = $this->userPrompt(
            $prompt,
            $hints ?? [],
            hasImage: $imageBase64 !== null,
            currentHtml: $currentHtml,
            currentSchema: $currentSchema,
        );

        try {
            $response = $this->router->generate(
                feature:       'block.template',
                prompt:        $user,
                system:        $system,
                tenant:        $tenant,
                metadata:      ['source' => 'section-template.generator'],
                imageBase64:   $imageBase64,
                imageMimeType: $imageMimeType,
            );
        } catch (AiProviderException $e) {
            throw new RuntimeException('AI sağlayıcısı yanıt veremedi: '.$e->getMessage(), 0, $e);
        }

        $parsed = $this->parseJson($response->content);

        return $this->normalize($parsed);
    }

    // ────────────────────────────────────────────────────────────────────

    private function systemPrompt(): string
    {
        return <<<PROMPT
Sen kıdemli bir frontend web tasarımcısı ve geliştiricisin. Bir doğal dil tarif alırsın ve
yeniden kullanılabilir bir "block şablonu" üretirsin. Çıktın bu CMS'te bir SectionTemplate
kaydı olur ve tüm sitelerde tek tıkla kullanılabilir.

GEREKSİNİMLER:

1) HTML
   - Tailwind CSS utility class'ları (v3) kullan. Custom CSS yazma; tüm stiller class'larda olsun.
   - Mobile-first: temel layout mobile için, md: ve lg: prefix'leriyle responsive.
   - Semantic HTML5: section, header, h2/h3, p, ul/li, figure, button — doğru etiketler.
   - Accessibility: aria-label, alt (gerekiyorsa), buton/link metni anlamlı.
   - Renkler: koyu zemin üzerinde text-white, açık zemin üzerinde text-gray-900 gibi
     erişilebilir kontrast. Spesifik markaya yapışma — neutral palet (slate, indigo, emerald).
   - Padding/spacing: py-12 lg:py-20, container mx-auto px-4, gap-6 gibi standart ölçüler.

2) Placeholder syntax
   - Metin alanları için {{key}} (HTML-escaped olarak render edilir).
   - Zaten HTML içeren alanlar için {{{key}}} (raw render — opsiyonel).
   - Her placeholder'ın schema_json'da KARŞILIĞI OLMAK ZORUNDA.
   - Şablonda olmayan key'i schema'ya KOYMA, schema'da olmayan key'i HTML'e KOYMA.

3) Schema_json
   - Her key için: { "type": <string>, "label": <Türkçe etiket> }
   - Tipler: "text" (tek satır), "textarea" (çok satır), "url" (link), "media_url"
     (görsel URL'i), "rich-text" (zengin metin — {{{key}}} ile birlikte kullan).
   - Mantıklı label'lar yaz ("Başlık", "Alt başlık", "Buton metni", "1. Kart başlığı").

4) Default content
   - default_content_json'a her key için anlamlı, Türkçe örnek değer koy.
   - Lorem ipsum yazma. Gerçekçi, profesyonel örnekler.
   - URL alanları için "#" veya "https://example.com/sayfa" gibi placeholder.

5) Tasarım kararları
   - name: kısa, açıklayıcı, Türkçe ("Hizmet Kartları 3'lü")
   - type: ingilizce kebab-case ("services", "hero", "cta", "team", "testimonials")
   - variation: ingilizce kebab-case, name'in kısa hali ("3-column-cards", "centered-light")

ÇIKTI BİÇİMİ: Sadece geçerli JSON. Kod bloğu YOK, ön söz YOK, açıklama YOK.

Beklenen şema:
{
  "name": "Hizmet Kartları 3'lü",
  "type": "services",
  "variation": "3-column-cards",
  "html_template": "<section class=...>{{title}}{{subtitle}}...</section>",
  "schema_json": {
    "title": {"type": "text", "label": "Başlık"},
    "subtitle": {"type": "textarea", "label": "Alt başlık"},
    "card_1_icon_url": {"type": "media_url", "label": "1. Kart ikon URL"},
    ...
  },
  "default_content_json": {
    "title": "Hizmetlerimiz",
    "subtitle": "Müşterilerimize sunduğumuz çözümler",
    ...
  }
}
PROMPT;
    }

    private function userPrompt(string $prompt, array $hints, bool $hasImage = false, ?string $currentHtml = null, ?array $currentSchema = null): string
    {
        $hintLines = [];
        if (! empty($hints['color_scheme'])) {
            $hintLines[] = 'Renk paleti tercihi: '.$hints['color_scheme'];
        }
        if (! empty($hints['style'])) {
            $hintLines[] = 'Stil tercihi: '.$hints['style'];
        }
        if (! empty($hints['language'])) {
            $hintLines[] = 'Default content dili: '.$hints['language'];
        }
        $hintBlock = $hintLines !== ''
            ? "\nKısıtlar:\n- ".implode("\n- ", $hintLines)."\n"
            : '';

        $imageNote = $hasImage
            ? "\nReferans görsel eklendi — tasarımı bu görseldeki layout, renk ve bileşen yapısına benzetmeye çalış.\n"
            : '';

        // DÜZENLE modu: mevcut html + şema verilirse, sıfırdan değil ÜZERİNDE çalış.
        if ($currentHtml !== null && trim($currentHtml) !== '') {
            $schemaJson = json_encode($currentSchema ?? new \stdClass(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

            return "GÖREV: Aşağıdaki MEVCUT block şablonunu, kullanıcının isteğine göre DÜZENLE. "
                . "Genel yapıyı, Tailwind class'larını ve çalışan kısımları KORU; yalnızca istenen değişikliği uygula. "
                . "Placeholder ({{key}}) ↔ schema_json tutarlılığını koru (yeni alan eklersen schema_json'a da ekle).{$imageNote}{$hintBlock}"
                . "\n\nMEVCUT html_template:\n{$currentHtml}"
                . "\n\nMEVCUT schema_json:\n{$schemaJson}"
                . "\n\nKULLANICI İSTEĞİ:\n{$prompt}"
                . "\n\nÇIKTI: Güncellenmiş TAM JSON (name/type/variation/html_template/schema_json/default_content_json), başka hiçbir şey.";
        }

        return "Block şablonu tarifi:\n{$prompt}{$imageNote}{$hintBlock}\n\nÇIKTI: Yukarıdaki şemada tam JSON, başka hiçbir şey.";
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
            // `{` ile başlayıp `}` ile bitmiyorsa yanıt büyük olasılıkla token
            // limitinde YARIDA KESİLMİŞ (kesik JSON parse edilemez).
            $looksTruncated = Str::startsWith($json, '{') && ! Str::endsWith(rtrim($json), '}');
            $hint = $looksTruncated
                ? ' (yanıt yarıda kesilmiş görünüyor — şablon çok büyük; daha küçük/parçalı bir düzenleme deneyin)'
                : '';

            throw new RuntimeException('AI yanıtı geçerli JSON değil'.$hint.': '.mb_substr($raw, 0, 200));
        }

        return $decoded;
    }

    /**
     * Validate + normalize the AI reply. Returns the final shape with
     * any consistency warnings appended (the UI surfaces them so the
     * admin can review before saving).
     *
     * @return array{
     *     name:string, type:string, variation:string,
     *     html_template:string,
     *     schema_json:array,
     *     default_content_json:array,
     *     warnings:array<int,string>
     * }
     */
    private function normalize(array $parsed): array
    {
        $name         = trim((string) ($parsed['name'] ?? ''));
        $type         = $this->slugify((string) ($parsed['type'] ?? ''));
        $variation    = $this->slugify((string) ($parsed['variation'] ?? ''));
        $htmlTemplate = trim((string) ($parsed['html_template'] ?? ''));
        $schemaJson   = is_array($parsed['schema_json'] ?? null) ? $parsed['schema_json'] : [];
        $defaultJson  = is_array($parsed['default_content_json'] ?? null) ? $parsed['default_content_json'] : [];
        $warnings     = [];

        if ($name === '') {
            throw new RuntimeException('AI yanıtında name eksik.');
        }
        if ($type === '') {
            throw new RuntimeException('AI yanıtında type eksik.');
        }
        if ($htmlTemplate === '') {
            throw new RuntimeException('AI yanıtında html_template eksik.');
        }
        if (empty($schemaJson)) {
            throw new RuntimeException('AI yanıtında schema_json eksik veya boş.');
        }

        // ── Cross-check placeholders ↔ schema keys ──────────────────────
        $placeholders = $this->extractPlaceholders($htmlTemplate);
        $schemaKeys   = array_keys($schemaJson);

        // Repeater alanları TÜREVİYLE kullanılır: schema'daki repeater 'X', HTML'de
        // {{{X_html}}} olarak basılır (renderer X_html'i X repeater'ından üretir).
        // Bu türev placeholder'ı ne "schema'da yok" say, ne de repeater'ı "kullanılmıyor" say.
        $repeaterKeys    = array_keys(array_filter(
            $schemaJson,
            fn ($f) => is_array($f) && ($f['type'] ?? null) === 'repeater',
        ));
        $derivedHtmlKeys = array_map(static fn ($k) => $k.'_html', $repeaterKeys);
        $usedRepeaters   = array_filter($repeaterKeys, fn ($k) => in_array($k.'_html', $placeholders, true));

        $missingInSchema = array_values(array_diff($placeholders, $schemaKeys, $derivedHtmlKeys));
        $missingInHtml   = array_values(array_diff($schemaKeys, $placeholders, $usedRepeaters));

        foreach ($missingInSchema as $key) {
            $warnings[] = "HTML'de {{ {$key} }} kullanılıyor ama schema_json'da yok.";
            // Auto-heal: add a sensible default to schema
            $schemaJson[$key] = ['type' => 'text', 'label' => Str::headline(str_replace('_', ' ', $key))];
        }
        foreach ($missingInHtml as $key) {
            $warnings[] = "schema_json'da '{$key}' tanımlı ama HTML şablonunda kullanılmıyor.";
        }

        // ── Cross-check default_content keys ↔ schema keys ─────────────
        foreach ($schemaKeys as $key) {
            if (! array_key_exists($key, $defaultJson)) {
                $defaultJson[$key] = ''; // empty string so the field is creatable
            }
        }
        foreach (array_keys($defaultJson) as $key) {
            if (! isset($schemaJson[$key])) {
                $warnings[] = "default_content_json'da '{$key}' var ama schema'da tanımlı değil.";
                unset($defaultJson[$key]);
            }
        }

        return [
            'name'                 => $name,
            'type'                 => $type,
            'variation'            => $variation,
            'html_template'        => $htmlTemplate,
            'schema_json'          => $schemaJson,
            'default_content_json' => $defaultJson,
            'warnings'             => $warnings,
        ];
    }

    /**
     * Pull out {{key}} and {{{key}}} placeholder names from html_template.
     * Returns deduped + ordered-first-seen.
     *
     * @return array<int, string>
     */
    private function extractPlaceholders(string $html): array
    {
        $matches = [];
        preg_match_all('/\{{2,3}\s*([a-zA-Z][a-zA-Z0-9_]*)\s*\}{2,3}/', $html, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    private function slugify(string $value): string
    {
        $slug = Str::slug(trim($value));

        return $slug !== '' ? $slug : '';
    }
}
