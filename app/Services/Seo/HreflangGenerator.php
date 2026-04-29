<?php

namespace App\Services\Seo;

use App\Models\Article;
use App\Models\Language;
use App\Models\Page;

/**
 * Generates hreflang alternate tag sets from the translation chain.
 *
 * Pages:    root_page_id links all language versions of the same page.
 * Articles: parent_article_id links translations to the original.
 *
 * Output format:
 *   [ 'tr' => 'https://example.com/tr/hakkimizda', 'en' => 'https://example.com/en/about-us', ... ]
 *   plus 'x-default' pointing to the default locale version.
 */
class HreflangGenerator
{
    protected string $baseUrl;
    protected string $defaultLocale;

    public function __construct()
    {
        $this->baseUrl       = rtrim(config('app.url', url('/')), '/');
        $this->defaultLocale = config('app.locale', 'tr');
    }

    // ─── Pages ────────────────────────────────────────────────────────────────

    public function forPage(Page $page): array
    {
        $rootId = $page->root_page_id ?: $page->id;

        // Fetch all versions that share the same root
        $versions = Page::query()
            ->where(function ($q) use ($rootId, $page) {
                $q->where('root_page_id', $rootId)
                  ->orWhere('id', $rootId)
                  ->orWhere('root_page_id', $page->id);
            })
            ->where('status', 'published')
            ->with('language')
            ->get();

        if ($versions->isEmpty()) {
            return [];
        }

        $tags = [];
        $defaultUrl = null;

        foreach ($versions as $version) {
            $locale = $version->language?->code ?? $this->defaultLocale;
            $url    = $this->pageUrl($locale, $version->slug);

            $tags[$locale] = $url;

            if ($locale === $this->defaultLocale) {
                $defaultUrl = $url;
            }
        }

        // x-default points to the default locale version
        if ($defaultUrl) {
            $tags['x-default'] = $defaultUrl;
        } elseif (! empty($tags)) {
            $tags['x-default'] = reset($tags);
        }

        return $tags;
    }

    // ─── Articles ─────────────────────────────────────────────────────────────

    public function forArticle(Article $article): array
    {
        // Find the root (original) article
        $rootId = $article->parent_article_id ?? $article->id;

        // Collect: root + all children
        $versions = Article::query()
            ->where(function ($q) use ($rootId, $article) {
                $q->where('id', $rootId)
                  ->orWhere('parent_article_id', $rootId)
                  ->orWhere('parent_article_id', $article->id);
            })
            ->where('status', 'published')
            ->with('language')
            ->get();

        if ($versions->isEmpty()) {
            return [];
        }

        $tags = [];
        $defaultUrl = null;

        foreach ($versions as $version) {
            $locale = $version->language?->code ?? $this->defaultLocale;
            $url    = $this->pageUrl($locale, $version->slug);

            $tags[$locale] = $url;

            if ($locale === $this->defaultLocale) {
                $defaultUrl = $url;
            }
        }

        if ($defaultUrl) {
            $tags['x-default'] = $defaultUrl;
        } elseif (! empty($tags)) {
            $tags['x-default'] = reset($tags);
        }

        return $tags;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function pageUrl(string $locale, string $slug): string
    {
        // Next.js convention: /{locale}/{slug}  (or /{locale} for home)
        $slug = ltrim($slug, '/');

        return $this->baseUrl . '/' . $locale . ($slug ? '/' . $slug : '');
    }

    /**
     * Add the x-default entry to an already-built map if it's missing.
     */
    public function addXDefault(array $tags, ?string $defaultLocale = null): array
    {
        $locale = $defaultLocale ?? $this->defaultLocale;

        if (! isset($tags['x-default']) && isset($tags[$locale])) {
            $tags['x-default'] = $tags[$locale];
        }

        return $tags;
    }
}
