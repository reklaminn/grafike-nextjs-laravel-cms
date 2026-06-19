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
        // Revizyona giren alanlardan herhangi biri değiştiyse, update
        // yazılmadan ÖNCEKİ durumun tam snapshot'ını al. changed_fields
        // hangi alanların değiştiğini saklar — UI'da diff özeti gösterilir.
        $changed = array_values(array_filter(
            Page::REVISION_FIELDS,
            fn (string $field) => $page->isDirty($field)
        ));

        if ($changed !== []) {
            $snapshot = [];
            foreach (Page::REVISION_FIELDS as $field) {
                $snapshot[$field] = $page->getOriginal($field);
            }
            $snapshot['changed_fields'] = $changed;

            PageRevision::create([
                'page_id'    => $page->id,
                'admin_id'   => auth()->id(),
                'snapshot'   => $snapshot,
                'reason'     => 'pre-update',
                'created_at' => now(),
            ]);

            // 30 en yeni dışındakileri buda — her kayıt yeni 'pre-update'
            // revizyonu üretiyor; budama olmazsa sınırsız birikir (DB şişer).
            // SectionTemplateController ile aynı politika (30).
            if ($page->revisions()->count() > 30) {
                $keepIds = $page->revisions()->limit(30)->pluck('id');
                $page->revisions()->whereNotIn('id', $keepIds)->delete();
            }
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

        // Otomatik SEO meta (opt-in): sayfa yayına geçtiyse, tenant
        // ai_settings.auto_seo_meta açıksa ve meta alanları boşsa kuyrukta
        // AI ile doldur. Hata/kota durumunda job sessizce vazgeçer.
        $this->maybeQueueSeoMeta($page);
    }

    private function maybeQueueSeoMeta(Page $page): void
    {
        if ($page->status !== 'published' || ! $page->wasChanged('status')) {
            return;
        }

        try {
            $tenant = (function_exists('tenancy') && tenancy()->initialized) ? tenancy()->tenant : null;
            if (! $tenant || ! ($tenant->aiSettings()['auto_seo_meta'] ?? false)) {
                return;
            }

            $seo = $page->seo()->first();
            if ($seo && (filled($seo->meta_title) || filled($seo->meta_description))) {
                return;
            }

            \App\Jobs\Ai\GenerateSeoMetaJob::dispatch($page->id);
        } catch (\Throwable) {
            // otomatik özellik — kayıt akışını asla bozma
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
