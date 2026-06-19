<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Tenant;
use Illuminate\Console\Command;

/**
 * Her tenant'taki her sayfanın revizyon geçmişini son N (varsayılan 30) ile
 * sınırlar. PageObserver artık her kayıtta otomatik buduyor; bu komut ondan
 * ÖNCE birikmiş eski 'pre-update' revizyonlarını tek seferde temizlemek için.
 *
 * Kullanım (sunucuda):  php artisan cms:prune-page-revisions
 *                       php artisan cms:prune-page-revisions --keep=20
 *                       php artisan cms:prune-page-revisions --dry-run
 */
class PrunePageRevisions extends Command
{
    protected $signature = 'cms:prune-page-revisions
                            {--keep=30 : Her sayfada tutulacak en yeni revizyon sayısı}
                            {--dry-run : Sadece kaç revizyon silineceğini raporla, silme}';

    protected $description = 'Tüm tenant sayfalarında revizyon geçmişini son N ile sınırlar (eski revizyonları siler)';

    public function handle(): int
    {
        $keep   = max(1, (int) $this->option('keep'));
        $dryRun = (bool) $this->option('dry-run');
        $grandTotal = 0;

        tenancy()->runForMultiple(Tenant::all(), function (Tenant $tenant) use ($keep, $dryRun, &$grandTotal) {
            $deleted = 0;

            Page::query()->cursor()->each(function (Page $page) use ($keep, $dryRun, &$deleted) {
                // En yeni $keep revizyonun id'leri (revisions() created_at DESC sıralı)
                $keepIds = $page->revisions()->limit($keep)->pluck('id');
                if ($keepIds->count() < $keep) {
                    return; // zaten <= keep, dokunma
                }

                $stale = $page->revisions()->whereNotIn('id', $keepIds);
                $deleted += $dryRun ? $stale->count() : $stale->delete();
            });

            $grandTotal += $deleted;
            $verb = $dryRun ? 'silinecek' : 'silindi';
            $this->info("✓ [{$tenant->id}] {$deleted} eski revizyon {$verb}");
        });

        $this->newLine();
        $this->info(($dryRun ? '[DRY-RUN] ' : '')."Toplam {$grandTotal} revizyon (keep={$keep}).");

        return self::SUCCESS;
    }
}
