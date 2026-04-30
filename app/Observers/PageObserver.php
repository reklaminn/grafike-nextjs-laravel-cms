<?php

namespace App\Observers;

use App\Models\Language;
use App\Models\Page;
use App\Models\PageRevision;
use App\Services\FrontendRevalidator;
use App\Services\Seo\IndexNowNotifier;
use Illuminate\Support\Facades\Cache;

class PageObserver
{
    public function updating(Page $page): void
    {
        if ($page->isDirty('sections_json') || $page->isDirty('layout_json')) {
            // Capture the state BEFORE the update is written.
            PageRevision::create([
                'page_id'    => $page->id,
                'admin_id'   => auth()->id(),
                'snapshot'   => [
                    'sections_json' => $page->getOriginal('sections_json'),
                    'layout_json'   => $page->getOriginal('layout_json'),
                ],
                'reason'     => 'pre-update',
                'created_at' => now(),
            ]);
        }
    }

    public function saved(Page $page): void
    {
        $this->clearPageCache($page);
        $this->revalidateFrontend($page);

        // IndexNow — only notify when the page is published
        if ($page->status === 'published') {
            $this->notifyIndexNow($page);
        }
    }

    public function deleted(Page $page): void
    {
        $this->clearPageCache($page);
        $this->revalidateFrontend($page);
    }

    // ─── Cache invalidation ───────────────────────────────────────────────────

    protected function clearPageCache(Page $page): void
    {
        if ($page->seo) {
            Cache::forget("seo_resolve_{$page->seo->slug}_");
        }

        Cache::forget('sitemap_xml');
        Cache::forget("layout_{$page->id}_0");
        Cache::forget("page_{$page->id}");
        Cache::forget('dashboard.stats');

        if ($page->parent_id) {
            Cache::forget("page_{$page->parent_id}");
            Cache::forget("page_children_{$page->parent_id}");
        }
    }

    // ─── Next.js ISR revalidation ─────────────────────────────────────────────

    protected function revalidateFrontend(Page $page): void
    {
        $tags  = ['pages', "page-{$page->slug}"];
        $paths = $this->buildLocalePaths($page->slug);

        app(FrontendRevalidator::class)->flush($paths, $tags);
    }

    // ─── IndexNow ─────────────────────────────────────────────────────────────

    protected function notifyIndexNow(Page $page): void
    {
        try {
            $siteUrl = rtrim(config('app.url', ''), '/');

            // Build one URL per active locale
            $codes = Language::active()->pluck('code')->toArray() ?: [config('cms.default_language', 'tr')];
            $urls  = array_map(fn (string $c) => "{$siteUrl}/{$c}/{$page->slug}", $codes);

            app(IndexNowNotifier::class)->pingBatch($urls);
        } catch (\Throwable) {
            // Non-fatal
        }
    }

    private function buildLocalePaths(string $slug): array
    {
        try {
            $codes = Language::active()->pluck('code')->toArray();
        } catch (\Throwable) {
            $codes = [config('cms.default_language', 'tr')];
        }

        return array_map(fn (string $code) => "/{$code}/{$slug}", $codes);
    }
}
