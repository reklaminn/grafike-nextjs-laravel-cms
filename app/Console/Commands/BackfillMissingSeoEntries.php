<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Page;
use App\Models\SeoEntry;
use Illuminate\Console\Command;

/**
 * SEO kaydı olmayan mevcut sayfa ve yazılar için boş SeoEntry oluşturur.
 *
 * Wizard ile oluşturulan draft sayfalar SEO kaydı olmadan eklendi;
 * bu komut onları düzeltir. Bir kez çalıştır, tekrar çalıştırsan zarar vermez.
 *
 * Kullanım:
 *   php artisan seo:backfill-missing --tenant=estetik_dermal
 */
class BackfillMissingSeoEntries extends Command
{
    protected $signature   = 'seo:backfill-missing {--tenant= : Tenant ID}';
    protected $description = 'SEO kaydı olmayan sayfa/yazılara boş SeoEntry oluşturur';

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
            $pageCount    = 0;
            $articleCount = 0;

            // Sayfalar
            Page::whereDoesntHave('seo')->get()->each(function (Page $page) use (&$pageCount) {
                SeoEntry::create([
                    'seoable_id'   => $page->id,
                    'seoable_type' => Page::class,
                    'slug'         => $page->slug,
                    'language_id'  => $page->language_id,
                ]);
                $this->line("  [Sayfa] #{$page->id} \"{$page->title}\" → slug={$page->slug}");
                $pageCount++;
            });

            // Yazılar
            Article::whereDoesntHave('seo')->get()->each(function (Article $article) use (&$articleCount) {
                SeoEntry::create([
                    'seoable_id'   => $article->id,
                    'seoable_type' => Article::class,
                    'slug'         => $article->slug,
                    'language_id'  => $article->language_id,
                ]);
                $this->line("  [Yazı]  #{$article->id} \"{$article->title}\" → slug={$article->slug}");
                $articleCount++;
            });

            $this->info("Tamamlandı. Sayfa: {$pageCount}, Yazı: {$articleCount} SEO kaydı oluşturuldu.");
        } finally {
            if ($tenantId) tenancy()->end();
        }

        return 0;
    }
}
