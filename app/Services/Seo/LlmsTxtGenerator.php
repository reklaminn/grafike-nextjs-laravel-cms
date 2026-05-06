<?php

namespace App\Services\Seo;

use App\Models\Article;
use App\Models\Page;
use App\Models\SiteSetting;

/**
 * Generates llms.txt and llms-full.txt per the llmstxt.org specification.
 *
 * llms.txt      — A concise site summary for AI context windows (< ~10k tokens).
 * llms-full.txt — All published content concatenated; for AI training / offline ingestion.
 *
 * Format spec: https://llmstxt.org/
 *
 * Geo section exposes business location, coordinates and opening hours so
 * LLMs can answer local-intent queries ("dentist near Kuşadası") correctly.
 */
class LlmsTxtGenerator
{
    public function generate(): string
    {
        $siteName   = SiteSetting::get('site_title', config('app.name'));
        $siteDesc   = SiteSetting::get('crawl.llms_description', '');
        $locale     = config('cms.default_language', 'tr');

        // Use url('/') so Traefik's X-Forwarded-Host returns the real tenant domain.
        $base = rtrim(url('/'), '/');

        $lines = [];

        // ── Header ─────────────────────────────────────────────────────────
        $lines[] = "# {$siteName}";
        $lines[] = '';

        if ($siteDesc) {
            $lines[] = "> {$siteDesc}";
            $lines[] = '';
        }

        // ── Business / Geo ─────────────────────────────────────────────────
        $geoBlock = $this->buildGeoBlock();
        if ($geoBlock) {
            $lines[] = '## İşletme';
            foreach ($geoBlock as $line) {
                $lines[] = $line;
            }
            $lines[] = '';
        }

        // ── Pages ──────────────────────────────────────────────────────────
        $pages = Page::query()
            ->where('status', 'published')
            ->where('show_in_menu', true)
            ->with(['seo', 'language'])
            ->orderBy('sort_order')
            ->get();

        if ($pages->isNotEmpty()) {
            $lines[] = '## Sayfalar';
            foreach ($pages as $page) {
                $pageLocale = $page->language?->code ?? $locale;
                $url        = $this->pageUrl($base, $pageLocale, $page->slug);
                $title      = $page->seo?->meta_title ?: $page->title;
                $desc       = $page->seo?->meta_description ?: '';
                $entry      = "- [{$title}]({$url})";
                if ($desc) {
                    $entry .= ': ' . mb_substr($desc, 0, 120) . (mb_strlen($desc) > 120 ? '…' : '');
                }
                $lines[] = $entry;
            }
            $lines[] = '';
        }

        // ── Articles (latest 20) ───────────────────────────────────────────
        $articles = Article::query()
            ->where('status', 'published')
            ->with(['seo', 'page', 'language'])
            ->orderByDesc('published_at')
            ->limit(20)
            ->get();

        if ($articles->isNotEmpty()) {
            $lines[] = '## Blog / Yazılar';
            foreach ($articles as $article) {
                $artLocale = $article->language?->code ?? $locale;
                $url       = $this->articleUrl($base, $artLocale, $article->page?->slug, $article->slug);
                $title     = $article->seo?->meta_title ?: $article->title;
                $desc      = $article->seo?->meta_description ?: $article->excerpt ?: '';
                $entry     = "- [{$title}]({$url})";
                if ($desc) {
                    $entry .= ': ' . mb_substr($desc, 0, 100) . (mb_strlen($desc) > 100 ? '…' : '');
                }
                $lines[] = $entry;
            }
            $lines[] = '';
        }

        // ── API ────────────────────────────────────────────────────────────
        $lines[] = '## API';
        $lines[] = "- [Site Bilgisi]({$base}/api/site): Marka ve tema";
        $lines[] = "- [Sayfalar]({$base}/api/pages/home): Ana sayfa içeriği";
        $lines[] = "- [Yazılar]({$base}/api/articles): Blog yazısı listesi";
        $lines[] = '';

        // ── Optional ───────────────────────────────────────────────────────
        $lines[] = '## Optional';
        $lines[] = "- [Sitemap]({$base}/sitemap.xml)";
        $lines[] = "- [Robots]({$base}/robots.txt)";

        return implode("\n", $lines) . "\n";
    }

    public function generateFull(): string
    {
        $siteName = SiteSetting::get('site_title', config('app.name'));
        $locale   = config('cms.default_language', 'tr');
        $base     = rtrim(url('/'), '/');

        $lines   = [];
        $lines[] = "# {$siteName} — Tam İçerik";
        $lines[] = '';
        $lines[] = '> Bu dosya tüm yayındaki sayfa ve yazı içeriklerini içerir.';
        $lines[] = '';

        // ── Business / Geo ─────────────────────────────────────────────────
        $geoBlock = $this->buildGeoBlock();
        if ($geoBlock) {
            $lines[] = '## İşletme Bilgileri';
            foreach ($geoBlock as $line) {
                $lines[] = $line;
            }
            $lines[] = '';
        }

        // ── Published pages ────────────────────────────────────────────────
        $pages = Page::query()
            ->where('status', 'published')
            ->with(['seo', 'language'])
            ->orderBy('sort_order')
            ->get();

        foreach ($pages as $page) {
            $pageLocale = $page->language?->code ?? $locale;
            $url        = $this->pageUrl($base, $pageLocale, $page->slug);
            $lines[]    = "## [{$page->title}]({$url})";
            if ($desc = $page->seo?->meta_description) {
                $lines[] = $desc;
            }
            $lines[] = '';
        }

        // ── Published articles (with body) ────────────────────────────────
        $articles = Article::query()
            ->where('status', 'published')
            ->with(['seo', 'page', 'language'])
            ->orderByDesc('published_at')
            ->get();

        foreach ($articles as $article) {
            $artLocale = $article->language?->code ?? $locale;
            $url       = $this->articleUrl($base, $artLocale, $article->page?->slug, $article->slug);
            $lines[]   = "## [{$article->title}]({$url})";
            if ($article->published_at) {
                $lines[] = '_' . $article->published_at->toDateString() . '_';
            }
            if ($article->excerpt) {
                $lines[] = $article->excerpt;
            }
            if ($article->body) {
                $text    = strip_tags((string) $article->body);
                $text    = preg_replace('/\s+/', ' ', $text);
                $lines[] = trim($text);
            }
            $lines[] = '';
        }

        return implode("\n", $lines) . "\n";
    }

    // ─── Geo / Business block ─────────────────────────────────────────────────

    /**
     * Build the business/geo section lines.
     * Returns an empty array when no business settings are configured.
     */
    protected function buildGeoBlock(): array
    {
        $name         = SiteSetting::get('business.name', '');
        $type         = SiteSetting::get('business.type', '');
        $street       = SiteSetting::get('business.address_street', '');
        $city         = SiteSetting::get('business.address_city', '');
        $postalCode   = SiteSetting::get('business.address_postal_code', '');
        $country      = SiteSetting::get('business.address_country', '');
        $phone        = SiteSetting::get('business.telephone', '')
                     ?: SiteSetting::get('contact.phone', '');
        $email        = SiteSetting::get('business.email', '')
                     ?: SiteSetting::get('contact.email', '');
        $lat          = SiteSetting::get('business.geo_lat', '');
        $lng          = SiteSetting::get('business.geo_lng', '');
        $hoursRaw     = SiteSetting::get('business.opening_hours', '');

        // Skip block entirely if no meaningful business data exists.
        if (! ($name || $city || $lat)) {
            return [];
        }

        $lines = [];

        if ($name) {
            $lines[] = "- **İşletme adı:** {$name}";
        }

        if ($type) {
            $lines[] = "- **Kategori:** {$type}";
        }

        // Full address
        $addressParts = array_filter([$street, $city, $postalCode, $country]);
        if ($addressParts) {
            $lines[] = '- **Adres:** ' . implode(', ', $addressParts);
        }

        // Coordinates — machine-readable geo: URI + human decimal degrees
        if ($lat && $lng) {
            $latF    = (float) $lat;
            $lngF    = (float) $lng;
            $latDir  = $latF >= 0 ? 'K' : 'G';
            $lngDir  = $lngF >= 0 ? 'D' : 'B';
            $lines[] = "- **Koordinatlar:** {$latF}° {$latDir}, {$lngF}° {$lngDir} (geo:{$latF},{$lngF})";
        }

        if ($phone) {
            $lines[] = "- **Telefon:** {$phone}";
        }

        if ($email) {
            $lines[] = "- **E-posta:** {$email}";
        }

        // Opening hours — parse the JSON array from admin settings
        if ($hoursRaw) {
            $parsed = $this->parseOpeningHours($hoursRaw);
            if ($parsed) {
                $lines[] = '- **Çalışma saatleri:** ' . implode('; ', $parsed);
            }
        }

        return $lines;
    }

    /**
     * Parse the opening_hours JSON setting into human-readable strings.
     * Input:  [{"days":"Mo-Fr","hours":"09:00-18:00"},{"days":"Sa","hours":"09:00-14:00"}]
     * Output: ["Pzt-Cum 09:00-18:00", "Cmt 09:00-14:00"]
     */
    protected function parseOpeningHours(string $raw): array
    {
        try {
            $data = json_decode($raw, true, 4, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        if (! is_array($data)) {
            return [];
        }

        $dayMap = [
            'Mo' => 'Pzt', 'Tu' => 'Sal', 'We' => 'Çar',
            'Th' => 'Per', 'Fr' => 'Cum', 'Sa' => 'Cmt', 'Su' => 'Paz',
        ];

        $results = [];
        foreach ($data as $entry) {
            if (empty($entry['days']) || empty($entry['hours'])) {
                continue;
            }

            // Translate day abbreviations: "Mo-Fr" → "Pzt-Cum"
            $days = preg_replace_callback(
                '/\b(Mo|Tu|We|Th|Fr|Sa|Su)\b/',
                fn ($m) => $dayMap[$m[1]] ?? $m[1],
                $entry['days'],
            );

            $results[] = "{$days} {$entry['hours']}";
        }

        return $results;
    }

    // ─── URL helpers (mirrors SitemapController) ──────────────────────────────

    protected function pageUrl(string $base, string $locale, string $slug): string
    {
        return $slug === 'home'
            ? "{$base}/{$locale}"
            : "{$base}/{$locale}/{$slug}";
    }

    protected function articleUrl(string $base, string $locale, ?string $pageSlug, string $articleSlug): string
    {
        if ($pageSlug && $pageSlug !== 'home') {
            return "{$base}/{$locale}/{$pageSlug}/{$articleSlug}";
        }

        return "{$base}/{$locale}/{$articleSlug}";
    }
}
