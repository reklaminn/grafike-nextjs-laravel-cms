<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Language;
use App\Services\FrontendRevalidator;
use App\Services\Seo\IndexNowNotifier;
use Illuminate\Support\Facades\Cache;

class ArticleObserver
{
    public function saved(Article $article): void
    {
        $this->clearArticleCache($article);
        $this->revalidateFrontend($article);

        // IndexNow — only notify when the article is published
        if ($article->status === 'published') {
            $this->notifyIndexNow($article);
        }
    }

    public function deleted(Article $article): void
    {
        $this->clearArticleCache($article);
        $this->revalidateFrontend($article);
    }

    // ─── Cache invalidation ───────────────────────────────────────────────────

    protected function clearArticleCache(Article $article): void
    {
        if ($article->seo) {
            Cache::forget("seo_resolve_{$article->seo->slug}_");
        }

        Cache::forget('sitemap_xml');
        Cache::forget('llms_txt');
        Cache::forget('llms_full_txt');
        Cache::forget('dashboard.stats');

        if ($article->page_id) {
            Cache::forget("page_{$article->page_id}");
            Cache::forget("layout_{$article->page_id}_0");
            Cache::forget("layout_{$article->page_id}_{$article->id}");
        }
    }

    // ─── IndexNow ─────────────────────────────────────────────────────────────

    protected function notifyIndexNow(Article $article): void
    {
        try {
            $siteUrl = rtrim(config('app.url', ''), '/');

            $locale = $article->language?->code ?? config('cms.default_language', 'tr');

            // Article URL: /{locale}/{parentPageSlug}/{articleSlug}
            $parentSlug = $article->page?->slug ?? null;
            $urlPath    = $parentSlug
                ? "/{$locale}/{$parentSlug}/{$article->slug}"
                : "/{$locale}/{$article->slug}";

            app(IndexNowNotifier::class)->ping($siteUrl . $urlPath);
        } catch (\Throwable) {
            // Non-fatal
        }
    }

    // ─── Next.js ISR revalidation ─────────────────────────────────────────────

    protected function revalidateFrontend(Article $article): void
    {
        $tags = [
            'articles',
            "article-{$article->slug}",
        ];

        // Also bust the article-list cache on the parent page
        if ($article->page_id) {
            $tags[] = "articles-page-{$article->page_id}";
        }

        app(FrontendRevalidator::class)->tags($tags);
    }
}
