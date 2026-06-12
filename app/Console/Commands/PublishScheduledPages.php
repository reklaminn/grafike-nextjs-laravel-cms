<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Tenant;
use Illuminate\Console\Command;

/**
 * Vakti gelen zamanlanmış sayfaları yayınlar.
 *
 * Scheduler dakikada bir çalıştırır (routes/console.php). Her tenant'ın
 * DB'sinde status='scheduled' AND scheduled_at <= now olan sayfalar
 * 'published' yapılır. update() PageObserver'ı tetikler → cache temizliği
 * ve Next.js revalidation otomatik gerçekleşir.
 */
class PublishScheduledPages extends Command
{
    protected $signature = 'cms:publish-scheduled';

    protected $description = 'Publish scheduled pages whose time has come (all tenants)';

    public function handle(): int
    {
        $published = 0;

        tenancy()->runForMultiple(Tenant::all(), function (Tenant $tenant) use (&$published) {
            $due = Page::query()
                ->where('status', 'scheduled')
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '<=', now())
                ->get();

            foreach ($due as $page) {
                $page->update([
                    'status'       => 'published',
                    'scheduled_at' => null,
                ]);

                ++$published;
                $this->info("✓ [{$tenant->id}] {$page->title} (/{$page->slug}) yayınlandı");
            }
        });

        if ($published === 0) {
            $this->line('Yayınlanacak zamanlanmış sayfa yok.');
        }

        return self::SUCCESS;
    }
}
