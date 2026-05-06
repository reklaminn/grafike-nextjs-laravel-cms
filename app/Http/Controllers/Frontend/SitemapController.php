<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;

/**
 * Generates per-tenant sitemap.xml with:
 *  - Locale-prefixed URLs  : /{locale}/{slug}  (Next.js route format)
 *  - Correct article paths : /{locale}/{page_slug}/{article_slug}
 *  - Homepage              : /{locale}  (not /{locale}/home)
 *  - <lastmod>             : from entity.updated_at
 *  - <changefreq>/<priority>: from seo_entry or smart defaults
 *  - <xhtml:link hreflang> : from seo_entry OR auto-built from root_page_id siblings
 *  - <image:image>         : cover media URL
 *
 * Cache key "sitemap_xml" is automatically scoped per tenant by
 * stancl/tenancy's CacheTenancyBootstrapper (Redis prefix = tenant id).
 */
class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap_xml', config('cms.cache.ttl', 600), fn () => $this->generateSitemap());

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    // ─────────────────────────────────────────────────────────────────────────

    protected function generateSitemap(): string
    {
        $defaultLocale = config('cms.default_language', 'tr');

        // request()->getSchemeAndHttpHost() returns the real tenant domain
        // because bootstrap/app.php trusts X-Forwarded-Host from Traefik.
        $base = rtrim(url('/'), '/');

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:xhtml="http://www.w3.org/1999/xhtml"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        // ── Pages ──────────────────────────────────────────────────────────
        $pages = Page::query()
            ->where('status', 'published')
            ->with(['seo', 'language'])
            ->get();

        // Group by root_page_id so we can auto-build hreflang from siblings.
        // Pages without root_page_id are their own root (keyed by their own id).
        $pagesByRoot = $pages->groupBy(fn ($p) => $p->root_page_id ?? $p->id);

        $emittedPageIds = [];

        foreach ($pages as $page) {
            if (in_array($page->id, $emittedPageIds, true)) {
                continue; // already emitted as part of a sibling group
            }

            $seo = $page->seo;

            if ($seo && ($seo->sitemap_exclude || $seo->is_noindex)) {
                continue;
            }

            $locale = $page->language?->code ?? $defaultLocale;
            $loc    = $this->pageUrl($base, $locale, $page->slug);

            // Admin-set canonical overrides the computed URL.
            if ($seo?->canonical_url) {
                $loc = $seo->canonical_url;
            }

            $lastmod    = $page->updated_at?->toW3cString();
            $priority   = (string) ($seo?->sitemap_priority  ?? 0.8);
            $changefreq = $seo?->sitemap_changefreq ?? 'weekly';

            // Hreflang: prefer SEO entry values; fall back to root_page_id siblings.
            $hreflang = [];
            if ($seo && is_array($seo->hreflang_tags) && ! empty($seo->hreflang_tags)) {
                $hreflang = $seo->hreflang_tags;
            } else {
                $rootId   = $page->root_page_id ?? $page->id;
                $siblings = $pagesByRoot[$rootId] ?? collect();

                if ($siblings->count() > 1) {
                    foreach ($siblings as $sibling) {
                        $sibLocale = $sibling->language?->code ?? $defaultLocale;
                        $hreflang[$sibLocale] = $this->pageUrl($base, $sibLocale, $sibling->slug);
                        $emittedPageIds[] = $sibling->id;
                    }
                }
            }

            $imageUrl = $page->getFirstMediaUrl('cover');

            $xml .= $this->urlEntry($loc, $lastmod, $priority, $changefreq, $hreflang, $imageUrl ?: null);
            $emittedPageIds[] = $page->id;
        }

        // ── Articles ───────────────────────────────────────────────────────
        $articles = Article::query()
            ->where('status', 'published')
            ->with(['seo', 'language', 'page'])
            ->get();

        foreach ($articles as $article) {
            $seo = $article->seo;

            if ($seo && ($seo->sitemap_exclude || $seo->is_noindex)) {
                continue;
            }

            $locale    = $article->language?->code ?? $defaultLocale;
            $pageSlug  = $article->page?->slug;
            $loc       = $this->articleUrl($base, $locale, $pageSlug, $article->slug);

            if ($seo?->canonical_url) {
                $loc = $seo->canonical_url;
            }

            $lastmod    = $article->updated_at?->toW3cString();
            $priority   = (string) ($seo?->sitemap_priority  ?? 0.6);
            $changefreq = $seo?->sitemap_changefreq ?? 'monthly';
            $hreflang   = is_array($seo?->hreflang_tags) ? $seo->hreflang_tags : [];
            $imageUrl   = $article->getFirstMediaUrl('cover');

            $xml .= $this->urlEntry($loc, $lastmod, $priority, $changefreq, $hreflang, $imageUrl ?: null);
        }

        $xml .= '</urlset>';

        return $xml;
    }

    // ─── URL helpers ──────────────────────────────────────────────────────────

    /**
     * Build a locale-prefixed page URL.
     * "home" slug → /{locale}  (root of the locale)
     * Other slugs → /{locale}/{slug}
     */
    protected function pageUrl(string $base, string $locale, string $slug): string
    {
        return $slug === 'home'
            ? "{$base}/{$locale}"
            : "{$base}/{$locale}/{$slug}";
    }

    /**
     * Build a locale-prefixed article URL.
     * Next.js route: /{locale}/{page_slug}/{article_slug}
     * If the parent page is "home" or missing, fall back to /{locale}/{article_slug}.
     */
    protected function articleUrl(string $base, string $locale, ?string $pageSlug, string $articleSlug): string
    {
        if ($pageSlug && $pageSlug !== 'home') {
            return "{$base}/{$locale}/{$pageSlug}/{$articleSlug}";
        }

        return "{$base}/{$locale}/{$articleSlug}";
    }

    // ─── XML entry builder ────────────────────────────────────────────────────

    /**
     * Build a single <url>…</url> entry.
     * <xhtml:link> alternates and <image:image> must be inside <url>.
     */
    protected function urlEntry(
        string $loc,
        ?string $lastmod,
        string $priority,
        string $changefreq,
        array $hreflang = [],
        ?string $imageUrl = null,
    ): string {
        $xml  = "  <url>\n";
        $xml .= '    <loc>' . e($loc) . '</loc>' . "\n";

        if ($lastmod) {
            $xml .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
        }

        $xml .= '    <changefreq>' . $changefreq . '</changefreq>' . "\n";
        $xml .= '    <priority>' . $priority . '</priority>' . "\n";

        foreach ($hreflang as $lang => $href) {
            $xml .= '    <xhtml:link rel="alternate"'
                  . ' hreflang="' . e($lang) . '"'
                  . ' href="' . e($href) . '"'
                  . '/>' . "\n";
        }

        if ($imageUrl) {
            $xml .= "    <image:image>\n";
            $xml .= '      <image:loc>' . e($imageUrl) . '</image:loc>' . "\n";
            $xml .= "    </image:image>\n";
        }

        $xml .= "  </url>\n";

        return $xml;
    }
}
