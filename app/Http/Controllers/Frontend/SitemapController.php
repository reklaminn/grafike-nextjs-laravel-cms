<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Page;
use App\Models\SeoEntry;
use Illuminate\Support\Facades\Cache;

/**
 * Generates sitemap.xml with:
 *  - <lastmod>   — from entity.updated_at
 *  - <changefreq> / <priority> — from seo_entry or smart defaults
 *  - <xhtml:link rel="alternate" hreflang="..."> — from seo_entry.hreflang_tags
 *  - <image:image> — cover media URLs
 */
class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap_xml', config('cms.cache.ttl', 600), function () {
            return $this->generateSitemap();
        });

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    protected function generateSitemap(): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:xhtml="http://www.w3.org/1999/xhtml"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
        $xml .= "\n";

        // Homepage
        $xml .= $this->urlEntry(url('/'), now()->toW3cString(), '1.0', 'daily');

        // Indexable published pages
        $pages = Page::query()
            ->where('status', 'published')
            ->with(['seo', 'language'])
            ->get();

        foreach ($pages as $page) {
            $seo = $page->seo;

            if ($seo && $seo->sitemap_exclude) {
                continue;
            }
            if ($seo && $seo->is_noindex) {
                continue;
            }

            $url        = $seo?->canonical_url ?: url($page->slug);
            $lastmod    = $page->updated_at?->toW3cString();
            $priority   = (string) ($seo?->sitemap_priority  ?? 0.8);
            $changefreq = $seo?->sitemap_changefreq ?? 'weekly';
            $hreflang   = is_array($seo?->hreflang_tags) ? $seo->hreflang_tags : [];

            // Cover image
            $imageUrl = $page->getFirstMediaUrl('cover');

            $xml .= $this->urlEntry($url, $lastmod, $priority, $changefreq, $hreflang, $imageUrl ?: null);
        }

        // Indexable published articles
        $articles = Article::query()
            ->where('status', 'published')
            ->with(['seo', 'language'])
            ->get();

        foreach ($articles as $article) {
            $seo = $article->seo;

            if ($seo && $seo->sitemap_exclude) {
                continue;
            }
            if ($seo && $seo->is_noindex) {
                continue;
            }

            $url        = $seo?->canonical_url ?: url($article->slug);
            $lastmod    = $article->updated_at?->toW3cString();
            $priority   = (string) ($seo?->sitemap_priority  ?? 0.6);
            $changefreq = $seo?->sitemap_changefreq ?? 'monthly';
            $hreflang   = is_array($seo?->hreflang_tags) ? $seo->hreflang_tags : [];

            $imageUrl = $article->getFirstMediaUrl('cover');

            $xml .= $this->urlEntry($url, $lastmod, $priority, $changefreq, $hreflang, $imageUrl ?: null);
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Build a single <url>...</url> entry.
     *
     * NOTE: <xhtml:link> alternates and <image:image> must be INSIDE <url>, before </url>.
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

        // Hreflang alternates (inside <url>)
        foreach ($hreflang as $lang => $href) {
            $xml .= '    <xhtml:link rel="alternate"'
                  . ' hreflang="' . e($lang) . '"'
                  . ' href="' . e($href) . '"'
                  . '/>' . "\n";
        }

        // Cover image
        if ($imageUrl) {
            $xml .= "    <image:image>\n";
            $xml .= '      <image:loc>' . e($imageUrl) . '</image:loc>' . "\n";
            $xml .= "    </image:image>\n";
        }

        $xml .= "  </url>\n";

        return $xml;
    }
}
