<?php

namespace App\Observers;

use App\Models\Language;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\SeoEntry;
use App\Services\FrontendRevalidator;
use App\Services\Seo\IndexNowNotifier;
use Illuminate\Support\Facades\Cache;

class PageObserver
{
    /**
     * Sayfa oluşturulduğunda otomatik SEO kaydı aç.
     * slug dolu olacak, meta alanlar boş — editörden doldurulur.
     */
    public function created(Page $page): void
    {
        if (! $page->seo()->exists()) {
            SeoEntry::create([
                'seoable_id'   => $page->id,
                'seoable_type' => Page::class,
                'slug'         => $page->slug,
                'language_id'  => $page->language_id,
            ]);
        }
    }

    /**
     * Slug değişince SEO kaydını da güncelle.
     * saveSeo() yalnızca meta alanlar doluysa çalışır; bu observer
     * her durumda seo_entries.slug'ı pages.slug ile senkronize tutar.
     */
    public function updated(Page $page): void
    {
        if ($page->wasChanged('slug')) {
            $page->seo()->update(['slug' => $page->slug]);
        }
    }

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
        // SEO kaydını da sil — soft-delete cascade etmiyor
        $page->seo()->delete();

        $this->clearPageCache($page);
        $this->revalidateFrontend($page);
    }

    public function forceDeleted(Page $page): void
    {
        $page->seo()->forceDelete();
        $this->clearPageCache($page);
    }

    // ─── Cache invalidation ───────────────────────────────────────────────────

    protected function clearPageCache(Page $page): void
    {
        if ($page->seo) {
            Cache::forget("seo_resolve_{$page->seo->slug}_");
        }

        Cache::forget('sitemap_xml');
        Cache::forget('llms_txt');
        Cache::forget('llms_full_txt');
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
