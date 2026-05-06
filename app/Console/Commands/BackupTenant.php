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

            // ── 5. Prune old backups (keep last 30) ───────────────────────────
            $this->pruneOld("backups/{$tenantId}", 30);

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

    private function pruneOld(string $dir, int $keep): void
    {
        $files = Storage::disk('local')->files($dir);
        rsort($files); // newest first (timestamp in filename)

        foreach (array_slice($files, $keep) as $old) {
            Storage::disk('local')->delete($old);
        }
    }
}
