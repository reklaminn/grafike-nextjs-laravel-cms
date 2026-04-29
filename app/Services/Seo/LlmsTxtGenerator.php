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
 */
class LlmsTxtGenerator
{
    public function generate(): string
    {
        $siteName    = SiteSetting::get('site_title', config('app.name'));
        $siteDesc    = SiteSetting::get('crawl.llms_description', '');
        $phone       = SiteSetting::get('contact.phone', '');
        $email       = SiteSetting::get('contact.email', '');
        $address     = SiteSetting::get('contact.address', '');
        $baseUrl     = rtrim(config('app.url', url('/')), '/');

        $lines = [];

        // ── Header ─────────────────────────────────────────────────────────
        $lines[] = "# {$siteName}";
        $lines[] = '';

        if ($siteDesc) {
            $lines[] = "> {$siteDesc}";
            $lines[] = '';
        }

        // ── Contact ────────────────────────────────────────────────────────
        $contactParts = array_filter([$phone, $email, $address]);
        if ($contactParts) {
            $lines[] = 'İletişim: ' . implode(' • ', $contactParts);
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
                $url   = $baseUrl . '/' . ltrim($page->slug, '/');
                $title = $page->seo?->meta_title ?: $page->title;
                $desc  = $page->seo?->meta_description ?: '';
                $entry = "- [{$title}]({$url})";
                if ($desc) {
                    $entry .= ": {$desc}";
                }
                $lines[] = $entry;
            }
            $lines[] = '';
        }

        // ── Articles (latest 20) ───────────────────────────────────────────
        $articles = Article::query()
            ->where('status', 'published')
            ->with(['seo', 'page'])
            ->orderByDesc('published_at')
            ->limit(20)
            ->get();

        if ($articles->isNotEmpty()) {
            $lines[] = '## Blog / Yazılar';
            foreach ($articles as $article) {
                $url   = $baseUrl . '/' . ltrim($article->slug, '/');
                $title = $article->seo?->meta_title ?: $article->title;
                $desc  = $article->seo?->meta_description ?: $article->excerpt ?: '';
                $entry = "- [{$title}]({$url})";
                if ($desc) {
                    $entry .= ': ' . mb_substr($desc, 0, 100) . (mb_strlen($desc) > 100 ? '…' : '');
                }
                $lines[] = $entry;
            }
            $lines[] = '';
        }

        // ── API ────────────────────────────────────────────────────────────
        $lines[] = '## API';
        $lines[] = "- [Site Bilgisi]({$baseUrl}/api/site): Marka ve tema";
        $lines[] = "- [Sayfalar]({$baseUrl}/api/pages/home): Ana sayfa içeriği";
        $lines[] = "- [Yazılar]({$baseUrl}/api/articles): Blog yazısı listesi";
        $lines[] = '';

        // ── Optional ───────────────────────────────────────────────────────
        $lines[] = '## Optional';
        $lines[] = "- [Sitemap]({$baseUrl}/sitemap.xml)";
        $lines[] = "- [Robots]({$baseUrl}/robots.txt)";

        return implode("\n", $lines) . "\n";
    }

    public function generateFull(): string
    {
        $siteName = SiteSetting::get('site_title', config('app.name'));
        $baseUrl  = rtrim(config('app.url', url('/')), '/');

        $lines = [];
        $lines[] = "# {$siteName} — Tam İçerik";
        $lines[] = '';
        $lines[] = '> Bu dosya tüm yayındaki sayfa ve yazı içeriklerini içerir.';
        $lines[] = '';

        // ── Published pages ────────────────────────────────────────────────
        $pages = Page::query()
            ->where('status', 'published')
            ->with(['seo', 'language'])
            ->orderBy('sort_order')
            ->get();

        foreach ($pages as $page) {
            $url  = $baseUrl . '/' . ltrim($page->slug, '/');
            $lines[] = "## [{$page->title}]({$url})";
            if ($desc = $page->seo?->meta_description) {
                $lines[] = $desc;
            }
            $lines[] = '';
        }

        // ── Published articles (with body) ────────────────────────────────
        $articles = Article::query()
            ->where('status', 'published')
            ->with(['seo', 'page'])
            ->orderByDesc('published_at')
            ->get();

        foreach ($articles as $article) {
            $url   = $baseUrl . '/' . ltrim($article->slug, '/');
            $lines[] = "## [{$article->title}]({$url})";
            if ($article->published_at) {
                $lines[] = '_' . $article->published_at->toDateString() . '_';
            }
            if ($article->excerpt) {
                $lines[] = $article->excerpt;
            }
            // Strip HTML from body
            if ($article->body) {
                $text = strip_tags((string) $article->body);
                $text = preg_replace('/\s+/', ' ', $text);
                $lines[] = trim($text);
            }
            $lines[] = '';
        }

        return implode("\n", $lines) . "\n";
    }
}
