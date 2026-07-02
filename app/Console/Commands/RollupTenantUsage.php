<?php

namespace App\Console\Commands;

use App\Models\AdminTenantAccess;
use App\Models\Tenant;
use App\Models\TenantUsageDaily;
use App\Services\Tenancy\TenantUsageMeter;
use Illuminate\Console\Command;

/**
 * Tenant günlük kullanımını kalıcılaştırır (saatlik cron).
 *
 *  - Bugün: istek/giriş sayaçları (Redis) + kaynak anlık görüntüsü
 *    (depolama, DB boyutu, kullanıcı sayısı) → tenant_usage_daily upsert.
 *  - Dün: Redis 2 gün tuttuğu için sayaçlar hâlâ okunur → kesinleştirilir.
 *
 * Ağır ölçümleri (depolama taraması) buraya alır ki tenant panosu web
 * isteğinde tarama yapmasın — tablodan okur.
 */
class RollupTenantUsage extends Command
{
    protected $signature = 'cms:rollup-usage';

    protected $description = 'Tenant günlük kullanımını (istek/giriş + depolama/DB/kullanıcı) tenant_usage_daily tablosuna yazar.';

    public function handle(TenantUsageMeter $meter): int
    {
        $todayYmd     = now()->format('Ymd');
        $yesterdayYmd = now()->subDay()->format('Ymd');
        $todayDate    = now()->toDateString();
        $yesterdayDate = now()->subDay()->toDateString();
        $done = 0;

        foreach (Tenant::query()->get(['id']) as $tenant) {
            $id = (string) $tenant->id;

            try {
                // Bugün — tam anlık görüntü
                TenantUsageDaily::updateOrCreate(
                    ['tenant_id' => $id, 'date' => $todayDate],
                    [
                        'requests'   => $meter->requestsOn($id, $todayYmd),
                        'logins'     => $meter->loginsOn($id, $todayYmd),
                        'storage_mb' => $meter->storageUsedMb($id),
                        'db_mb'      => $meter->databaseSizeMb($id),
                        'users'      => AdminTenantAccess::where('tenant_id', $id)->distinct()->count('admin_id'),
                    ],
                );

                // Dün — sayaçları kesinleştir (kaynak ölçümüne dokunma)
                $yReq = $meter->requestsOn($id, $yesterdayYmd);
                $yLog = $meter->loginsOn($id, $yesterdayYmd);
                $yRow = TenantUsageDaily::firstOrNew(['tenant_id' => $id, 'date' => $yesterdayDate]);
                if ($yRow->exists || $yReq > 0 || $yLog > 0) {
                    $yRow->requests = max((int) $yRow->requests, $yReq);
                    $yRow->logins   = max((int) $yRow->logins, $yLog);
                    $yRow->save();
                }

                $done++;
            } catch (\Throwable $e) {
                $this->warn("rollup atlandı: {$id} — {$e->getMessage()}");
            }
        }

        $this->info("Kullanım rollup tamam: {$done} site.");

        return self::SUCCESS;
    }
}
