<?php

namespace App\Services\Tenancy;

use App\Models\AdminTenantAccess;
use App\Models\Package;
use App\Models\Tenant;
use App\Services\Ai\AiQuotaService;

/**
 * Bir tenant'ın kaynak kullanımını paket limitleriyle karşılaştırıp
 * "bir üst pakete geçmeli mi?" sorusunu yanıtlar.
 *
 * Paylaşımlı altyapıda CPU/RAM tenant başına izole ölçülemediğinden, proxy
 * metrikler kullanılır (kaynak yükünün vekilleri):
 *   - Disk  : dosya depolama + tenant DB boyutu  → max_storage_mb
 *   - İstek : günlük istek sayısı                → max_requests_per_day
 *   - Kullanıcı: yönetici sayısı                  → max_users
 *   - AI    : aylık istek + maliyet               → ai plan limitleri
 *
 * Eşik: kullanım/limit ≥ %80 → 'warn', ≥ %100 → 'over'.
 */
class TenantUpgradeAdvisor
{
    private const WARN = 0.80;

    public function __construct(
        private readonly TenantUsageMeter $meter,
        private readonly AiQuotaService $aiQuota,
    ) {
    }

    /**
     * @return array{level:string, max_ratio:float, bottleneck:?array, recommended:?string, metrics:array}
     */
    public function evaluate(Tenant $tenant): array
    {
        $id  = (string) $tenant->getTenantKey();
        $pkg = $tenant->packageConfig();

        $diskUsed = $this->meter->storageUsedMb($id) + $this->meter->databaseSizeMb($id);
        $users    = AdminTenantAccess::where('tenant_id', $id)->distinct()->count('admin_id');
        $requests = $this->meter->requestsToday($id);

        $metrics = [
            'disk'     => $this->ratio('Disk (dosya+DB)', round($diskUsed, 1), $pkg['max_storage_mb'] ?? null, 'MB'),
            'requests' => $this->ratio('İstek/gün', $requests, $pkg['max_requests_per_day'] ?? null, ''),
            'users'    => $this->ratio('Kullanıcı', $users, $pkg['max_users'] ?? null, ''),
        ];

        // AI (aylık) — opsiyonel; central DB migrate edilmemişse vb. atla.
        try {
            $plan  = $this->aiQuota->planFor($tenant);
            $usage = $this->aiQuota->currentUsage($tenant);
            $metrics['ai_requests'] = $this->ratio('AI istek/ay', (int) ($usage['requests'] ?? 0), $plan['monthly_requests'] ?? null, '');
            $metrics['ai_cost']     = $this->ratio('AI maliyet/ay', round((float) ($usage['cost_usd'] ?? 0), 2), $plan['monthly_cost_usd'] ?? null, '$');
        } catch (\Throwable) {
            // AI metriklerini atla
        }

        $maxRatio = 0.0;
        $bottleneck = null;
        foreach ($metrics as $m) {
            if ($m['ratio'] !== null && $m['ratio'] > $maxRatio) {
                $maxRatio = $m['ratio'];
                $bottleneck = $m;
            }
        }

        $level = $maxRatio >= 1.0 ? 'over' : ($maxRatio >= self::WARN ? 'warn' : 'ok');
        $recommended = $level === 'ok'
            ? null
            : $this->recommend($level, $diskUsed, $users, $requests, $tenant->package());

        return [
            'level'       => $level,        // ok | warn | over
            'max_ratio'   => $maxRatio,
            'bottleneck'  => $bottleneck,   // ['label','used','limit','ratio','unit'] | null
            'recommended' => $recommended,  // paket anahtarı (mevcuttan büyük) | null
            'metrics'     => $metrics,
        ];
    }

    private function ratio(string $label, int|float $used, int|float|null $limit, string $unit): array
    {
        $r = ($limit === null || $limit <= 0) ? null : round($used / $limit, 2);

        return ['label' => $label, 'used' => $used, 'limit' => $limit, 'ratio' => $r, 'unit' => $unit];
    }

    private function recommend(string $level, float $diskMb, int $users, int $requests, string $current): ?string
    {
        $packages = Package::allKeyed();
        $keys = array_keys($packages);
        $ci = array_search($current, $keys, true);
        if ($ci === false) {
            $ci = 0;
        }

        if ($level === 'over') {
            // Mevcuttan büyük, kullanımı karşılayan en küçük paket.
            for ($i = $ci + 1; $i < count($keys); $i++) {
                if ($this->fits($packages[$keys[$i]], $diskMb, $users, $requests)) {
                    return $keys[$i];
                }
            }
            $last = $keys[count($keys) - 1] ?? null;

            return ($last && $last !== $current) ? $last : null;
        }

        // 'warn' → eşiğe yaklaşıldı; bir üst tier (varsa).
        return $keys[$ci + 1] ?? null;
    }

    private function fits(array $p, float $diskMb, int $users, int $requests): bool
    {
        return (($p['max_storage_mb'] ?? null) === null || $diskMb <= $p['max_storage_mb'])
            && (($p['max_users'] ?? null) === null || $users <= $p['max_users'])
            && (($p['max_requests_per_day'] ?? null) === null || $requests <= $p['max_requests_per_day']);
    }
}
