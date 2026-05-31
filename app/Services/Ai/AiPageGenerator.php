<?php

namespace App\Services\Ai;

use App\Models\SectionTemplate;
use App\Models\Tenant;
use App\Services\Ai\Exceptions\AiProviderException;
use App\Support\FrontendSections;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Generates a full page (title + slug + sections_json) from a single
 * natural-language prompt using the AI router.
 *
 * Workflow:
 *   1. Fetch active SectionTemplate's from central DB → build a catalog
 *      string the model can pick from (id + type + name + schema fields).
 *   2. Send a strict-JSON prompt with the catalog + user's request.
 *   3. Parse the model's reply, drop any hallucinated template_ids that
 *      don't exist, filter each block's content to schema-declared keys,
 *      and wrap the flat block list into the region-based sections_json
 *      shape via FrontendSections::normalize().
 *
 * Output is returned, NOT persisted — caller decides whether to preview
 * or auto-save into a Page record.
 */
class AiPageGenerator
{
    /** Cap on templates fed to the model — keeps prompt size bounded. */
    private const MAX_TEMPLATES = 40;

    public function __construct(
        private readonly AiModelRouter $router,
        private readonly SiteContextBuilder $contextBuilder,
    ) {
    }

    /**
     * @param  array  $siteContext  Optional site-context overrides (from wizard or caller).
     *                              When empty, auto-fetched from SiteContextBuilder.
     *
     * @return array{title:string, slug:string, sections_json:array, picked_template_ids:array<int,int>}
     */
    public function generate(
        string $prompt,
        ?Tenant $tenant = null,
        string $locale = 'tr',
        ?Collection $availableTemplates = null,
        array $siteContext = [],
    ): array {
        $templates = $availableTemplates ?? $this->fetchTemplates();
        if ($templates->isEmpty()) {
            throw new RuntimeException(
                'Hiç aktif blok şablonu bulunamadı. AI sayfa üretmek için en az 1 SectionTemplate gerekir.'
            );
        }

        // Auto-build site context from DB when caller doesn't supply one
        if (empty($siteContext)) {
            try {
                $siteContext = $this->contextBuilder->build();
            } catch (Throwable) {
                $siteContext = [];
            }
        }

        $contextSnippet = !empty($siteContext)
            ? $this->contextBuilder->toPromptSnippet($siteContext)
            : '';

        $system = $this->systemPrompt($locale, $contextSnippet);
        $user   = $this->userPrompt($prompt, $this->buildCatalog($templates));

        try {
            $response = $this->router->generate(
                feature:  'page.create',
                prompt:   $user,
                system:   $system,
                tenant:   $tenant,
                metadata: ['source' => 'page.generator'],
            );
        } catch (AiProviderException $e) {
            throw new RuntimeException('AI sağlayıcısı yanıt veremedi: '.$e->getMessage(), 0, $e);
        }

        $parsed = $this->parseJson($response->content);

        return $this->buildPageData($parsed, $templates);
    }

    // ────────────────────────────────────────────────────────────────────

    private function fetchTemplates(): Collection
    {
        return SectionTemplate::query()
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('id')
            ->limit(self::MAX_TEMPLATES)
            ->get(['id', 'type', 'variation', 'name', 'render_mode', 'schema_json']);
    }

    /**
     * Tablo benzeri tek satırlık şablon kataloğu — model token ucuz olsun
     * diye kompakt format. Örnek:
     *   id=12 type=hero name="Porto Hero Split" alanlar=[title(text), subtitle(textarea), button_text(text), button_url(text)]
     */
    private function buildCatalog(Collection $templates): string
    {
        return $templates->map(function (SectionTemplate $tpl) {
            $schema = is_array($tpl->schema_json) ? $tpl->schema_json : [];
            $fields = collect($schema)
                ->map(function ($def, $key) {
                    $type = is_array($def) ? ($def['type'] ?? 'text') : (string) $def;

                    return "{$key}({$type})";
                })
                ->implode(', ');

            $name = $tpl->name ?: ($tpl->type.($tpl->variation ? '/'.$tpl->variation : ''));

            return sprintf(
                'id=%d type=%s name="%s" alanlar=[%s]',
                (int) $tpl->id,
                (string) $tpl->type,
                str_replace('"', "'", $name),
                $fields,
            );
        })->implode("\n");
    }

    private function systemPrompt(string $locale, string $contextSnippet = ''): string
    {
        $langName = $this->humanLanguage($locale);

        // Site context block is injected between the intro and the strict rules
        $ctxBlock = $contextSnippet !== ''
            ? "\n\n" . $contextSnippet . "\n"
            : '';

        $intro = "Sen profesyonel bir web içerik tasarımcısısın. "
               . "Bir prompt + mevcut blok şablonu listesi alırsın; "
               . "sayfanın blok dizisini ve {$langName} içeriklerini üretirsin.";

        $rules = "\n\nKATI KURALLAR:\n"
               . "- SADECE listedeki \"id\" değerlerini kullan; uydurma id KESINLIKLE kullanma.\n"
               . "- Her blokun content'inde, blokun \"alanlar\" listesindeki anahtarları kullan; eksik veya fazla key olmasın.\n"
               . "- İçerikler {$langName} dilinde, akıcı, doğal, somut, firmaya özgü olsun. Lorem ipsum yazma.\n"
               . "- Site bağlamı verilmişse firma adını, sektörü ve iletişim bilgilerini içeriklere doğal biçimde yansıt.\n"
               . "- Sayfa için kısa bir title (max 70 karakter) ve URL slug öner. Slug: küçük harf, tire ayraçlı, Türkçe karakterleri ascii'ye çevir, max 60 karakter.\n"
               . "- Blok sırası mantıklı olsun (hero → kısa anlatım → özellikler → detay → CTA gibi).\n"
               . "- 3-7 blok yeterli. Aşırıya kaçma.\n\n"
               . "ÇIKTI: Sadece geçerli JSON. Kod bloğu YOK, ön söz YOK.\n\n"
               . "Beklenen şema:\n"
               . "{\n"
               . "  \"title\": \"Sayfa başlığı\",\n"
               . "  \"slug\": \"url-slug\",\n"
               . "  \"sections\": [\n"
               . "    {\"template_id\": <int>, \"content\": {<şablon alan adı>: <değer>, ...}},\n"
               . "    ...\n"
               . "  ]\n"
               . "}";

        return $intro . $ctxBlock . $rules;
    }

    private function userPrompt(string $prompt, string $catalog): string
    {
        return "Mevcut blok şablonları:\n{$catalog}\n\nKullanıcı isteği:\n{$prompt}\n\nUygun blokları seç ve içerikleri üret.";
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

    /**
     * Materialize the AI reply into a Page-ready payload. Hallucinated
     * template_ids are silently dropped. Empty result throws so caller
     * doesn't get an empty page.
     *
     * @return array{title:string, slug:string, sections_json:array, picked_template_ids:array<int,int>}
     */
    private function buildPageData(array $parsed, Collection $templates): array
    {
        $title       = trim((string) ($parsed['title'] ?? ''));
        $slug        = $this->normalizeSlug((string) ($parsed['slug'] ?? ''), $title);
        $rawSections = $parsed['sections'] ?? [];

        if (! is_array($rawSections)) {
            throw new RuntimeException('AI yanıtında sections array eksik.');
        }

        /** @var Collection<int, SectionTemplate> $byId */
        $byId      = $templates->keyBy('id');
        $blocks    = [];
        $pickedIds = [];

        foreach (array_values($rawSections) as $i => $section) {
            if (! is_array($section)) {
                continue;
            }
            $tid = (int) ($section['template_id'] ?? 0);
            /** @var SectionTemplate|null $tpl */
            $tpl = $byId->get($tid);
            if (! $tpl) {
                continue; // skip hallucinations
            }

            $rawContent = $section['content'] ?? [];
            if (! is_array($rawContent)) {
                $rawContent = [];
            }

            // Filter to schema-declared keys only
            $schema   = is_array($tpl->schema_json) ? $tpl->schema_json : [];
            $declared = array_keys($schema);
            $content  = $declared
                ? array_intersect_key($rawContent, array_flip($declared))
                : $rawContent;

            $blocks[] = [
                'id'                  => 'block_'.($i + 1).'_'.Str::random(4),
                'type'                => (string) $tpl->type,
                'variation'           => (string) ($tpl->variation ?? ''),
                'render_mode'         => (string) ($tpl->render_mode ?? 'html'),
                'section_template_id' => (int) $tpl->id,
                'is_active'           => true,
                'sort_order'          => $i + 1,
                'content'             => $content,
            ];
            $pickedIds[] = (int) $tpl->id;
        }

        if (empty($blocks)) {
            throw new RuntimeException(
                'AI hiç geçerli blok seçemedi. Promptu daha açıklayıcı yazıp tekrar deneyin.'
            );
        }

        return [
            'title'               => $title !== '' ? $title : 'Yeni Sayfa',
            'slug'                => $slug,
            'sections_json'       => FrontendSections::normalize($blocks),
            'picked_template_ids' => $pickedIds,
        ];
    }

    /**
     * AI'in önerdiği slug'i normalize et; boşsa title'dan üret.
     */
    private function normalizeSlug(string $raw, string $title): string
    {
        $candidate = trim($raw);
        if ($candidate === '' && $title !== '') {
            $candidate = $title;
        }
        if ($candidate === '') {
            return 'yeni-sayfa';
        }
        $slug = Str::slug($candidate, '-', 'tr');
        // Cap length, strip trailing dashes from truncation
        if (mb_strlen($slug) > 60) {
            $slug = rtrim(mb_substr($slug, 0, 60), '-');
        }

        return $slug !== '' ? $slug : 'yeni-sayfa';
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
