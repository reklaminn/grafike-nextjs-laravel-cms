<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\DbDumper\Databases\MariaDb;
use ZipArchive;

class BackupTenant extends Command
{
    protected $signature = 'cms:backup-tenant
                            {tenant? : Tenant ID — omit together with --all to back up all}
                            {--all   : Back up every active tenant}';

    protected $description = 'Create a DB + storage backup for one or all tenants';

    public function handle(): int
    {
        if ($this->option('all')) {
            $tenants = Tenant::all();
        } elseif ($id = $this->argument('tenant')) {
            $tenants = Tenant::where('id', $id)->get();
        } else {
            $tenants = Tenant::all();
        }

        if ($tenants->isEmpty()) {
            $this->error('No tenants found.');
            return self::FAILURE;
        }

        $failed = 0;
        foreach ($tenants as $tenant) {
            try {
                $path = $this->backupTenant($tenant);
                $this->info("✓ [{$tenant->id}] Saved → {$path}");
            } catch (\Throwable $e) {
                $this->error("✗ [{$tenant->id}] Failed: {$e->getMessage()}");
                ++$failed;
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Run the full backup for a single tenant and return the stored path.
     */
    public function backupTenant(Tenant $tenant): string
    {
        $tenantId  = (string) $tenant->getTenantKey();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $zipName   = "backup_{$tenantId}_{$timestamp}.zip";
        $tmpDir    = sys_get_temp_dir()."/cms_backup_{$tenantId}_{$timestamp}";

        try {
            @mkdir($tmpDir, 0755, true);

            // ── 1. Database dump ──────────────────────────────────────────────
            $sqlFile = "{$tmpDir}/database.sql";
            $dbName  = config('tenancy.database.prefix').$tenantId.config('tenancy.database.suffix', '');

            MariaDb::create()
                ->setHost(config('database.connections.central.host', '127.0.0.1'))
                ->setPort((int) config('database.connections.central.port', 3306))
                ->setDbName($dbName)
                ->setUserName(config('database.connections.central.username'))
                ->setPassword(config('database.connections.central.password', ''))
                ->setSkipSsl(true)
                ->setSslFlag('skip-ssl')
                ->addExtraOption('--ssl=FALSE')
                ->dumpToFile($sqlFile);

            // ── 2. Storage files (tenant private + public) ───────────────────
            $sources = [
                storage_path("app/tenant_{$tenantId}")         => "storage/private",
                storage_path("app/public/tenant_{$tenantId}")  => "storage/public",
            ];

            foreach ($sources as $src => $rel) {
                if (is_dir($src)) {
                    $dst = "{$tmpDir}/{$rel}";
                    @mkdir($dst, 0755, true);
                    $this->copyDir($src, $dst);
                }
            }

            // ── 3. Create zip ─────────────────────────────────────────────────
            $zipTmp = sys_get_temp_dir()."/{$zipName}";
            $zip    = new ZipArchive();

            if ($zip->open($zipTmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException("Cannot create zip at {$zipTmp}");
            }

            $this->addDirToZip($zip, $tmpDir, '');
            $zip->close();

            // ── 4. Persist to storage disk ───────────────────────────────────
            $storagePath = "backups/{$tenantId}/{$zipName}";
            Storage::disk('local')->put($storagePath, file_get_contents($zipTmp));
            @unlink($zipTmp);

            // ── 5. Eski yedekleri buda — GFS katmanlı saklama ─────────────────
            $this->pruneOld("backups/{$tenantId}");

            return $storagePath;

        } finally {
            $this->deleteDir($tmpDir);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function addDirToZip(ZipArchive $zip, string $baseDir, string $prefix): void
    {
        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iter as $file) {
            if ($file->isDir()) {
                continue;
            }
            $relative = ltrim(substr($file->getPathname(), strlen($baseDir)), DIRECTORY_SEPARATOR);
            $zip->addFile($file->getPathname(), ($prefix ? $prefix.'/' : '').$relative);
        }
    }

    private function copyDir(string $src, string $dst): void
    {
        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iter as $item) {
            $target = $dst.DIRECTORY_SEPARATOR.$iter->getSubPathname();
            if ($item->isDir()) {
                @mkdir($target, 0755, true);
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    private function deleteDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($dir);
    }

    /**
     * GFS (Grandfather-Father-Son) katmanlı saklama:
     *  - Son 7 gün: TÜM yedekler (yakın güvenlik + aynı-gün manuel checkpoint'ler)
     *  - 8 gün – ~5 hafta: her ISO hafta için en yeni 1 yedek
     *  - ~5 hafta – ~6 ay: her takvim ayı için en yeni 1 yedek
     *  - Daha eski: silinir
     * Sonuç ≈ 17 yedek, ~6 ay geriye dönük kapsama; disk şişmez.
     * Dosya adı: backup_{tenant}_{Y-m-d_H-i-s}.zip — tenant id'si alt çizgi
     * içerebildiği için zaman damgası SONDAN regex'le ayrıştırılır.
     */
    private function pruneOld(string $dir): void
    {
        $items = [];
        foreach (Storage::disk('local')->files($dir) as $f) {
            if (preg_match('/(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})\.zip$/', $f, $m)) {
                $ts = \DateTimeImmutable::createFromFormat('Y-m-d_H-i-s', $m[1]);
                if ($ts !== false) {
                    $items[] = ['path' => $f, 'ts' => $ts];
                }
            }
        }
        if ($items === []) {
            return;
        }

        usort($items, fn ($a, $b) => $b['ts'] <=> $a['ts']); // en yeni → en eski

        $now = new \DateTimeImmutable('now');
        $keep = [];
        $seenWeek = [];
        $seenMonth = [];

        foreach ($items as $it) {
            $ageDays = (int) $now->diff($it['ts'])->days;

            if ($ageDays <= 7) {
                $keep[$it['path']] = true;                       // günlük: son 7 gün — hepsi
            } elseif ($ageDays <= 35) {
                $wk = $it['ts']->format('o-W');                  // haftalık: hafta başına 1
                if (! isset($seenWeek[$wk])) {
                    $seenWeek[$wk] = $keep[$it['path']] = true;
                }
            } elseif ($ageDays <= 215) {
                $mo = $it['ts']->format('Y-m');                  // aylık: ay başına 1
                if (! isset($seenMonth[$mo])) {
                    $seenMonth[$mo] = $keep[$it['path']] = true;
                }
            }
            // daha eski → tutulmaz (silinecek)
        }

        foreach ($items as $it) {
            if (! isset($keep[$it['path']])) {
                Storage::disk('local')->delete($it['path']);
            }
        }
    }
}
