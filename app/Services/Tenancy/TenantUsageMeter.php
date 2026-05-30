<?php

namespace App\Services\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;

/**
 * Per-tenant resource metering (application level — shared infra).
 *
 *  - Storage: computed from the tenant's on-disk folders
 *    (storage/app/public/tenant_X + storage/app/tenant_X/private). Works from
 *    ANY context (admin reading another tenant) because it's keyed by tenant
 *    id, not by the active tenancy.
 *  - Daily request / login counters: stored in Redis with EXPLICIT tenant_id
 *    keys (not the stancl tenant-tagged Cache), so the same key is written in
 *    the public request context and read in the agency admin panel.
 *
 * All counter ops are fail-open (wrapped in try/catch) — metering must never
 * break a live request.
 */
class TenantUsageMeter
{
    // ─── Storage ────────────────────────────────────────────────────────────

    public function storageUsedBytes(string $tenantId): int
    {
        $dirs = [
            storage_path("app/public/tenant_{$tenantId}"),
            storage_path("app/tenant_{$tenantId}/private"),
        ];

        $total = 0;
        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            foreach (File::allFiles($dir) as $f) {
                $total += $f->getSize();
            }
        }

        return $total;
    }

    public function storageUsedMb(string $tenantId): float
    {
        return round($this->storageUsedBytes($tenantId) / 1048576, 1);
    }

    /** True if adding $additionalBytes would push the tenant over its quota. */
    public function wouldExceedStorage(Tenant $tenant, int $additionalBytes): bool
    {
        $quotaMb = $tenant->packageConfig()['max_storage_mb'] ?? null;
        if ($quotaMb === null) {
            return false; // unlimited
        }

        $used = $this->storageUsedBytes((string) $tenant->getTenantKey());

        return ($used + $additionalBytes) > ((int) $quotaMb * 1048576);
    }

    // ─── Daily counters (Redis, explicit tenant key) ─────────────────────────

    public function hitRequest(string $tenantId): void
    {
        $this->bump("req:{$tenantId}");
    }

    public function hitLogin(string $tenantId): void
    {
        $this->bump("login:{$tenantId}");
    }

    public function requestsToday(string $tenantId): int
    {
        return $this->read("req:{$tenantId}");
    }

    public function loginsToday(string $tenantId): int
    {
        return $this->read("login:{$tenantId}");
    }

    private function bump(string $suffix): void
    {
        try {
            $key = $this->key($suffix);
            $count = (int) Redis::incr($key);
            if ($count === 1) {
                Redis::expire($key, 172800); // 2 gün — gün dönümü payıyla
            }
        } catch (\Throwable) {
            // fail-open: metering canlı isteği asla bozmaz
        }
    }

    private function read(string $suffix): int
    {
        try {
            return (int) (Redis::get($this->key($suffix)) ?? 0);
        } catch (\Throwable) {
            return 0;
        }
    }

    private function key(string $suffix): string
    {
        return 'meter:' . $suffix . ':' . now()->format('Ymd');
    }
}
