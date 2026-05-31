<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Silinmiş sayfa / yazılara ait sahipsiz seo_entries kayıtlarını temizler.
 *
 * Soft-delete edilen Page / Article'ların SEO kaydı otomatik silinmiyordu;
 * PageObserver / ArticleObserver düzeltildi ama geçmiş kayıtlar kaldı.
 *
 * Kullanım (tenant bağlamında çalışır):
 *   php artisan seo:prune-orphans --tenant=estetik_dermal
 *   php artisan seo:prune-orphans  # aktif tenant (tenancy başlatıldıysa)
 */
class PruneOrphanedSeoEntries extends Command
{
    protected $signature   = 'seo:prune-orphans {--tenant= : Tenant ID}';
    protected $description = 'Silinmiş sayfa/yazılara ait sahipsiz seo_entries kayıtlarını siler';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            $tenant = \App\Models\Tenant::find($tenantId);
            if (! $tenant) {
                $this->error("Tenant bulunamadı: {$tenantId}");
                return 1;
            }
            tenancy()->initialize($tenant);
        }

        try {
            $deleted = 0;

            // Seoable_type = Page → pages tablosunda deleted_at dolu veya hiç yok
            $orphanPageSeo = DB::table('seo_entries')
                ->where('seoable_type', 'App\\Models\\Page')
                ->whereNotExists(function ($q) {
                    $q->from('pages')
                      ->whereColumn('pages.id', 'seo_entries.seoable_id')
                      ->whereNull('pages.deleted_at');
                })
                ->get(['id', 'slug', 'seoable_id']);

            foreach ($orphanPageSeo as $row) {
                $this->line("  [Sayfa] seo_entries#{$row->id} slug={$row->slug} page_id={$row->seoable_id} → silindi");
                DB::table('seo_entries')->where('id', $row->id)->delete();
                $deleted++;
            }

            // Seoable_type = Article → articles tablosunda yok
            $orphanArticleSeo = DB::table('seo_entries')
                ->where('seoable_type', 'App\\Models\\Article')
                ->whereNotExists(function ($q) {
                    $q->from('articles')
                      ->whereColumn('articles.id', 'seo_entries.seoable_id');
                })
                ->get(['id', 'slug', 'seoable_id']);

            foreach ($orphanArticleSeo as $row) {
                $this->line("  [Yazı] seo_entries#{$row->id} slug={$row->slug} article_id={$row->seoable_id} → silindi");
                DB::table('seo_entries')->where('id', $row->id)->delete();
                $deleted++;
            }

            $this->info("Tamamlandı. Silinen: {$deleted} sahipsiz SEO kaydı.");
        } finally {
            if ($tenantId) tenancy()->end();
        }

        return 0;
    }
}
