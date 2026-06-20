<?php

namespace App\Services\Tenancy;

use App\Models\Package;
use App\Models\QuotaExtension;
use App\Models\Tenant;
use App\Models\TenantUsageDaily;
use App\Services\Ai\AiUsageReporter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * /admin/tenants operasyon panosu için toplu metrikler.
 *
 * Tasarım: AĞIR ölçümler (depolama taraması, DB boyutu) `cms:rollup-usage`
 * cron'unda alınıp tenant_usage_daily'ye yazılır. Bu servis web isteğinde
 * yalnızca UCUZ işler yapar: canlı Redis istek sayacı + tablo okumaları +
 * birkaç toplama sorgusu. Tüm yük 60 sn cache'lenir.
 *
 * Yalnızca ajans/superadmin için anlamlıdır (tüm tenant'lar üzerinden).
 */
class TenantDashboardService
{
    /** Limit oranı bu eşiğin üstündeyse "limite yakın" (warn); ≥1.0 ise "aşıldı" (over). */
    private const WARN = 0.80;

    public function __construct(
        private readonly TenantUsageMeter $meter,
        private readonly AiUsageReporter $aiReporter,
    ) {
    }

    /** Pano verisini döndürür (60 sn cache). */
    public function overview(): array
    {
        return Cache::remember('admin.tenants.dashboard', 60, fn () => $this->build());
    }

    private function build(): array
    {
        $tenants = Tenant::query()->get();
        $ids     = $tenants->pluck('id')->map(fn ($i) => (string) $i)->all();
        $byId    = $tenants->keyBy(fn ($t) => (string) $t->id);

        $snapshots = empty($ids)
            ? collect()
            : TenantUsageDaily::whereIn('tenant_id', $ids)
                ->where('date', now()->toDateString())
                ->get()->keyBy('tenant_id');

        // ── Tenant başına satır (ucuz) ──────────────────────────────────────
        $rows = [];
        foreach ($tenants as $t) {
            $id   = (string) $t->id;
            $snap = $snapshots->get($id);
            $pkg  = $t->packageConfig();

            $requests  = $this->meter->requestsToday($id);               // canlı Redis
            $reqLimit  = $t->effectiveDailyRequestLimit();
            $storageMb = $snap ? round((float) $snap->storage_mb + (float) $snap->db_mb, 1) : null;
            $users     = $snap ? (int) $snap->users : null;

            $candidates = [
                'requests' => ['label' => 'İstek/gün', 'used' => $requests, 'limit' => $reqLimit, 'unit' => ''],
                'disk'     => ['label' => 'Disk',      'used' => $storageMb, 'limit' => $pkg['max_storage_mb'] ?? null, 'unit' => 'MB'],
                'users'    => ['label' => 'Kullanıcı', 'used' => $users,    'limit' => $pkg['max_users'] ?? null,        'unit' => ''],
            ];

            $maxRatio = 0.0;
            $bottleneck = null;
            foreach ($candidates as $c) {
                $r = $this->ratio($c['used'], $c['limit']);
                if ($r !== null && $r > $maxRatio) {
                    $maxRatio   = $r;
                    $bottleneck = $c + ['ratio' => $r];
                }
            }
            $level = $maxRatio >= 1.0 ? 'over' : ($maxRatio >= self::WARN ? 'warn' : 'ok');

            $rows[] = [
                'id'            => $id,
                'name'          => $t->name ?: $id,
                'status'        => $t->status ?: 'active',
                'package_label' => $pkg['label'] ?? $t->package(),
                'requests'      => $requests,
                'req_limit'     => $reqLimit,
                'storage_mb'    => $storageMb,
                'level'         => $level,
                'max_ratio'     => $maxRatio,
                'bottleneck'    => $bottleneck,
            ];
        }
        $rows = collect($rows);

        // ── KPI'lar ─────────────────────────────────────────────────────────
        $requestsTotal = (int) $rows->sum('requests');
        $aiTotals      = $this->safe(fn () => $this->aiReporter->totalsForCurrentMonth(null));

        $kpis = [
            'total'          => $tenants->count(),
            'active'         => $rows->where('status', 'active')->count(),
            'suspended'      => $rows->where('status', 'suspended')->count(),
            'near_limit'     => $rows->whereIn('level', ['warn', 'over'])->count(),
            'over_limit'     => $rows->where('level', 'over')->count(),
            'requests_today' => $requestsTotal,
            'ai_cost_month'  => (float) ($aiTotals['cost'] ?? 0),
        ];

        // ── Listeler & dağılımlar ───────────────────────────────────────────
        $nearLimit = $rows->whereIn('level', ['warn', 'over'])
            ->sortByDesc('max_ratio')->take(12)->values()->all();

        $topRequests = $rows->where('requests', '>', 0)
            ->sortByDesc('requests')->take(8)->values()->all();

        $packageDist = $rows->groupBy('package_label')->map->count()->sortDesc()->all();
        $statusDist  = $rows->groupBy('status')->map->count()->all();

        // ── Zaman serileri ──────────────────────────────────────────────────
        $trend = $this->requestTrend($ids, 30, $requestsTotal);
        $aiTrend = $this->safe(fn () => $this->aiReporter->dailyTrend(null, 30)) ?? [];

        // ── Operasyon kartları ──────────────────────────────────────────────
        $quotaExt = QuotaExtension::query()->active()->orderBy('ends_at')->get()
            ->map(fn ($e) => [
                'tenant_id'   => $e->tenant_id,
                'tenant_name' => optional($byId->get((string) $e->tenant_id))->name ?: $e->tenant_id,
                'extra'       => (int) $e->extra_requests_per_day,
                'ends_at'     => $e->ends_at,
                'days_left'   => (int) floor(now()->diffInDays($e->ends_at, false)),
                'reason'      => $e->reason,
            ])->values()->all();

        $recent = $tenants->sortByDesc('created_at')->take(5)
            ->map(fn ($t) => [
                'id'         => (string) $t->id,
                'name'       => $t->name ?: $t->id,
                'created_at' => $t->created_at,
            ])->values()->all();

        $backupAlerts = $this->backupAlerts($tenants);

        return [
            'kpis'          => $kpis,
            'near_limit'    => $nearLimit,
            'top_requests'  => $topRequests,
            'package_dist'  => $packageDist,
            'status_dist'   => $statusDist,
            'trend'         => $trend,
            'ai_totals'     => $aiTotals,
            'ai_trend'      => $aiTrend,
            'quota_ext'     => $quotaExt,
            'recent'        => $recent,
            'backup_alerts' => $backupAlerts,
            'generated_at'  => now(),
        ];
    }

    /** Kullanım/limit oranı; limit yoksa (sınırsız) null. */
    private function ratio(int|float|null $used, int|float|null $limit): ?float
    {
        if ($used === null || $limit === null || $limit <= 0) {
            return null;
        }

        return round($used / $limit, 2);
    }

    /** Son N günün toplam istek/giriş serisi (sıfır-doldurmalı). */
    private function requestTrend(array $ids, int $days, int $liveTodayTotal): array
    {
        $labels = [];
        $requests = [];
        $logins = [];

        $rows = collect();
        if (! empty($ids)) {
            $start = now()->subDays($days - 1)->toDateString();
            $rows = TenantUsageDaily::whereIn('tenant_id', $ids)
                ->where('date', '>=', $start)
                ->selectRaw('date, SUM(requests) as req, SUM(logins) as logn')
                ->groupBy('date')->get()
                ->keyBy(fn ($r) => Carbon::parse($r->date)->toDateString());
        }

        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $row = $rows->get($d);
            $labels[]   = $d;
            $requests[] = $row ? (int) $row->req : 0;
            $logins[]   = $row ? (int) $row->logn : 0;
        }

        // Bugünün barı: cron saatlik yazdığı için canlı toplamla güncelle.
        if (! empty($requests)) {
            $last = count($requests) - 1;
            $requests[$last] = max($requests[$last], $liveTodayTotal);
        }

        return ['labels' => $labels, 'requests' => $requests, 'logins' => $logins];
    }

    /**
     * Yedeği eski/olmayan siteler. Her tenant için backups/{id}/*.zip en yeni
     * mtime'ına bakar (ucuz dizin taraması). Yalnızca uyarı verenleri döndürür.
     */
    private function backupAlerts($tenants): array
    {
        $alerts = [];
        foreach ($tenants as $t) {
            $id = (string) $t->id;
            $dir = storage_path("backups/{$id}");
            $newest = null;
            try {
                foreach (glob($dir . '/*.zip') ?: [] as $file) {
                    $m = @filemtime($file);
                    if ($m && (! $newest || $m > $newest)) {
                        $newest = $m;
                    }
                }
            } catch (\Throwable) {
                // yoksay
            }

            $last = $newest ? Carbon::createFromTimestamp($newest) : null;
            $daysAgo = $last ? (int) floor($last->diffInDays(now())) : null;

            if ($last === null || $daysAgo >= 3) {
                $alerts[] = [
                    'id'        => $id,
                    'name'      => $t->name ?: $id,
                    'last'      => $last,
                    'days_ago'  => $daysAgo,
                ];
            }
        }

        // En kritik (yedeği yok / en eski) önce
        usort($alerts, fn ($a, $b) => ($b['days_ago'] ?? 99999) <=> ($a['days_ago'] ?? 99999));

        return array_slice($alerts, 0, 8);
    }

    private function safe(callable $fn)
    {
        try {
            return $fn();
        } catch (\Throwable) {
            return null;
        }
    }
}
