<?php

namespace App\Services\Ai;

use App\Models\Tenant;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Block content editor — applies a named transformation (shorten,
 * professional tone, SEO-optimize, translate, …) to a single block's
 * content payload, preserving the schema shape.
 *
 * The service is schema-aware: when a SectionTemplate.schema_json is
 * supplied we only let the AI rewrite text-typed fields (`string`,
 * `text`, `longtext`, `html`). Identifier fields (`media_id`, urls,
 * colors, etc.) are kept verbatim from the original input.
 *
 * No persistence — the controller returns the new payload to the front-
 * end editor, which lets the admin preview/apply before saving the page.
 */
class AiBlockEditor
{
    /**
     * @var array<string, string>  action key → Turkish instruction shown to the LLM
     */
    private const ACTIONS = [
        'shorten'       => 'Cümleleri kısalt, gereksiz sıfatları çıkar, anlamı koru. Yaklaşık %30 daha kısa olsun.',
        'lengthen'      => 'İçeriği zenginleştir, somut detay ve örnek ekle. Yaklaşık %30 daha uzun olsun.',
        'professional'  => 'Daha profesyonel ve kurumsal bir ton uygula. Resmi dil kullan.',
        'casual'        => 'Daha samimi ve gündelik bir ton uygula; "siz" yerine "sen" kullanma esnekliği olsun.',
        'seo_optimize'  => 'Anahtar kelimeleri doğal şekilde içeriğe yerleştir. Başlık ve alt başlıkları SEO odaklı yaz; aksiyon davetli, somut faydaları vurgula.',
        'rephrase'      => 'Aynı anlamı koruyarak yeniden yaz; akıcı, sıkıcı olmayan bir versiyon oluştur.',
        'fix_typos'     => 'Sadece yazım, dilbilgisi ve noktalama hatalarını düzelt. Hiçbir cümleyi yeniden yazma, içeriği değiştirme.',
        'translate_en'  => 'Tüm metinleri akıcı İngilizceye çevir.',
        'translate_tr'  => 'Tüm metinleri akıcı Türkçeye çevir.',
        'translate_de'  => 'Tüm metinleri akıcı Almancaya çevir.',
        'translate_ru'  => 'Tüm metinleri akıcı Rusçaya çevir.',
        'translate_ar'  => 'Tüm metinleri akıcı Arapçaya çevir.',
    ];

    /** Schema field types that the AI is allowed to rewrite. */
    private const TEXT_TYPES = ['string', 'text', 'longtext', 'html', 'richtext'];

    public function __construct(private readonly AiModelRouter $router)
    {
    }

    /** Public API: actions the UI may offer as preset buttons/dropdown. */
    public function availableActions(): array
    {
        return self::ACTIONS;
    }

    /**
     * The text-editable subset of a block's content — what an AI is allowed
     * to rewrite. Identifier/url/color/media fields are excluded. Exposed so
     * page-level tooling (AiPageAssistant) can build prompts without
     * duplicating the field-safety logic.
     *
     * @param  array<string, mixed>  $content
     * @param  array<int, array<string,mixed>>|null  $schema
     * @return array<string, mixed>
     */
    public function editableSubset(array $content, ?array $schema): array
    {
        $allowed = $this->allowedKeys($schema);

        return $allowed !== null
            ? array_intersect_key($content, array_flip($allowed))
            : $this->filterNonTextFields($content);
    }

    /**
     * Merge AI-produced field values back onto the original content, applying
     * the same allowlist + type-preservation guards as edit(). Used by
     * AiPageAssistant which gets already-parsed fields (not raw output).
     *
     * @param  array<string, mixed>  $original
     * @param  array<string, mixed>  $aiFields
     * @param  array<int, array<string,mixed>>|null  $schema
     * @return array<string, mixed>
     */
    public function mergeFields(array $original, array $aiFields, ?array $schema): array
    {
        return $this->mergeOutput($original, $aiFields, $this->allowedKeys($schema));
    }

    /**
     * Build the system + user prompt parts for streaming — same logic as
     * edit(), but without making the AI call itself.
     *
     * @return array{system:string, user:string|null, allowed:array<int,string>|null}
     */
    public function buildPromptParts(
        array $content,
        ?array $schema,
        string $action,
        ?string $customPrompt = null,
    ): array {
        $instruction = $this->resolveInstruction($action, $customPrompt);
        $allowed     = $this->allowedKeys($schema);
        $editable    = $allowed !== null
            ? array_intersect_key($content, array_flip($allowed))
            : $this->filterNonTextFields($content);

        return [
            'system'  => $this->systemPrompt(),
            'user'    => empty($editable) ? null : $this->userPrompt($editable, $instruction),
            'allowed' => $allowed,
        ];
    }

    /**
     * Parse accumulated raw AI output + merge onto original content.
     * Used by the streaming controller after all deltas are received.
     *
     * @return array<string, mixed>
     */
    public function applyRawOutput(array $content, string $rawOutput, ?array $schema): array
    {
        $allowed   = $this->allowedKeys($schema);
        $aiOutput  = $this->parseJson($rawOutput);

        return $this->mergeOutput($content, $aiOutput, $allowed);
    }

    /**
     * Apply a transformation to the block content.
     *
     * @param  array<string, mixed>            $content        Current block content keyed by field
     * @param  array<int, array<string,mixed>>|null  $schema    SectionTemplate.schema_json (list of field defs)
     * @param  string                          $action         Preset key or 'custom'
     * @param  string|null                     $customPrompt   Required when action='custom'
     * @return array<string, mixed>                            Merged content (same shape as $content)
     */
    public function edit(
        array $content,
        ?array $schema,
        string $action,
        ?string $customPrompt = null,
        ?Tenant $tenant = null,
    ): array {
        $instruction = $this->resolveInstruction($action, $customPrompt);

        // Snapshot the editable subset so the AI only sees text fields.
        $allowed = $this->allowedKeys($schema);
        $editable = $allowed !== null
            ? array_intersect_key($content, array_flip($allowed))
            : $this->filterNonTextFields($content);

        if (empty($editable)) {
            // Nothing for AI to chew on; return original untouched.
            return $content;
        }

        $system = $this->systemPrompt();
        $prompt = $this->userPrompt($editable, $instruction);

        try {
            $response = $this->router->generate(
                feature:  'block.edit',
                prompt:   $prompt,
                system:   $system,
                tenant:   $tenant,
                metadata: ['source' => 'block.editor', 'action' => $action],
            );
        } catch (AiProviderException $e) {
            throw new RuntimeException('AI sağlayıcısı yanıt veremedi: '.$e->getMessage(), 0, $e);
        }

        $aiOutput = $this->parseJson($response->content);

        return $this->mergeOutput($content, $aiOutput, $allowed);
    }

    // ────────────────────────────────────────────────────────────────────

    private function resolveInstruction(string $action, ?string $customPrompt): string
    {
        if ($action === 'custom') {
            $trimmed = trim((string) $customPrompt);
            if ($trimmed === '') {
                throw new InvalidArgumentException('custom eylem için custom_prompt zorunlu');
            }
            if (mb_strlen($trimmed) > 500) {
                throw new InvalidArgumentException('custom_prompt çok uzun (max 500 karakter)');
            }

            return $trimmed;
        }
        if (! isset(self::ACTIONS[$action])) {
            throw new InvalidArgumentException("Bilinmeyen eylem: {$action}");
        }

        return self::ACTIONS[$action];
    }

    /**
     * Schema-derived allowlist. Returns null when no schema is supplied
     * (caller falls back to a permissive content-shape filter).
     *
     * @return array<int, string>|null
     */
    private function allowedKeys(?array $schema): ?array
    {
        if ($schema === null || $schema === []) {
            return null;
        }
        $keys = [];
        foreach ($schema as $field) {
            if (! is_array($field) || ! isset($field['key'])) {
                continue;
            }
            $type = strtolower((string) ($field['type'] ?? 'string'));
            if (in_array($type, self::TEXT_TYPES, true)) {
                $keys[] = (string) $field['key'];
            }
        }

        return $keys;
    }

    /**
     * Permissive fallback when no schema is available: keep only string-
     * valued, content-looking keys; drop ids/urls/colors/etc.
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function filterNonTextFields(array $content): array
    {
        $skip = ['id', '_uid', 'uid', 'type', 'template', 'image_id', 'media_id',
                 'url', 'href', 'color', 'icon', 'class', 'css', 'align', 'size',
                 'background', 'video_url', 'embed', 'tag'];

        // Suffix patterns catch derived identifier/style keys (button_url,
        // icon_class, hero_image_id, accent_color, …) that the exact-match
        // blacklist misses — the AI must never rewrite these.
        $skipSuffixes = ['_url', '_href', '_link', '_id', '_color', '_icon', '_class'];

        return array_filter(
            $content,
            function ($value, $key) use ($skip, $skipSuffixes) {
                if (! is_string($value) || ! is_string($key)) {
                    return false;
                }
                $lower = strtolower($key);
                if (in_array($lower, $skip, true)) {
                    return false;
                }
                foreach ($skipSuffixes as $suffix) {
                    if (str_ends_with($lower, $suffix)) {
                        return false;
                    }
                }

                return true;
            },
            ARRAY_FILTER_USE_BOTH,
        );
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
Sen profesyonel bir Türkçe içerik editörüsün. Sana bir blok içeriği (JSON) ve bir düzenleme talimatı verilir.

Görevin: aynı JSON yapısını koruyarak değerleri talimata göre güncellemek.

Katı kurallar:
- Aynı anahtarları (keys) kullan; yeni anahtar EKLEME, anahtar SİLME.
- Değer tiplerini değiştirme: string→string, array→array.
- Yalnızca verilen metin alanlarını yeniden yaz; orijinal yapıyı bozma.
- HTML etiketi varsa (örn. <strong>, <br>) anlamlı olduğunda koru.

ÇIKTI BİÇİMİ: Sadece geçerli JSON. Kod bloğu YOK, ön söz YOK, ek açıklama YOK.

Örnek:
Giriş: {"heading":"Eski başlık","body":"Eski metin"}
Çıkış: {"heading":"Yeni başlık","body":"Yeni metin"}
PROMPT;
    }

    private function userPrompt(array $editable, string $instruction): string
    {
        $payload = json_encode($editable, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return "Eylem: {$instruction}\n\nMevcut içerik:\n{$payload}\n\nÇIKTI: Aynı yapıdaki yeni JSON.";
    }

    /**
     * Parse the model's reply. Tolerates fences and chatty prefixes.
     *
     * @return array<string, mixed>
     */
    private function parseJson(string $raw): array
    {
        $json = trim($raw);

        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $json, $m)) {
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

    /**
     * Merge AI output onto the original content. Keys NOT in the allowlist
     * (or matching the non-text blacklist) keep their original values, so
     * media_id / urls / colors / etc. never get mutated by the model.
     *
     * @param  array<string, mixed>  $original
     * @param  array<string, mixed>  $aiOutput
     * @param  array<int, string>|null  $allowed
     * @return array<string, mixed>
     */
    private function mergeOutput(array $original, array $aiOutput, ?array $allowed): array
    {
        $merged = $original;
        $whitelist = $allowed ?? array_keys($this->filterNonTextFields($original));

        foreach ($whitelist as $key) {
            if (! array_key_exists($key, $aiOutput)) {
                continue;
            }
            $newValue = $aiOutput[$key];

            // Preserve type: string stays string, array stays array.
            if (array_key_exists($key, $original)) {
                $origType = gettype($original[$key]);
                $newType  = gettype($newValue);
                if ($origType !== $newType && $origType !== 'NULL') {
                    continue; // model changed the type — reject
                }
            }
            $merged[$key] = $newValue;
        }

        return $merged;
    }
}
