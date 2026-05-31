<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Tenancy\TenantUpgradeAdvisor;
use Illuminate\Console\Command;

/**
 * Paket limitlerine yaklaşan/aşan tenant'ları + önerilen paketi listeler.
 *
 *   php artisan tenants:upgrade-report          # sadece warn/over olanlar
 *   php artisan tenants:upgrade-report --all     # tüm tenant'lar
 *
 * Günlük çalıştırmak için routes/console.php'ye Schedule eklenebilir.
 */
class TenantUpgradeReport extends Command
{
    protected $signature = 'tenants:upgrade-report {--all : ok olanları da göster}';

    protected $description = 'Paket limitine yaklaşan/aşan tenant\'ları ve önerilen paketi raporlar';

    public function handle(TenantUpgradeAdvisor $advisor): int
    {
        $rows = [];

        foreach (Tenant::all() as $tenant) {
            $r = $advisor->evaluate($tenant);

            if (! $this->option('all') && $r['level'] === 'ok') {
                continue;
            }

            $b = $r['bottleneck'];
            $bottleneck = $b
                ? sprintf(
                    '%s: %s%s / %s (%d%%)',
                    $b['label'],
                    $b['used'],
                    $b['unit'],
                    $b['limit'] ?? '∞',
                    (int) round(($b['ratio'] ?? 0) * 100),
                )
                : '—';

            $rows[] = [
                $tenant->getTenantKey(),
                $tenant->package(),
                strtoupper($r['level']),
                $bottleneck,
                $r['recommended'] ?? '—',
            ];
        }

        if (empty($rows)) {
            $this->info('Tüm tenant\'lar paket limitleri içinde. 👍');

            return self::SUCCESS;
        }

        $this->table(['Tenant', 'Paket', 'Durum', 'Darboğaz', 'Önerilen'], $rows);
        $this->line(sprintf('%d tenant dikkat gerektiriyor.', count($rows)));

        return self::SUCCESS;
    }
}
