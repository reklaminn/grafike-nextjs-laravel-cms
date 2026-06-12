<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Tenant yedeğini geri yükler.
 *
 * Yedek formatı (BackupTenant ile aynı):
 *   database.sql        — tenant DB dump'ı
 *   storage/private/…   — storage/app/tenant_{id}
 *   storage/public/…    — storage/app/public/tenant_{id}
 *
 * Güvenlik: geri yüklemeden ÖNCE mevcut durumun otomatik yedeği alınır
 * (pre-restore snapshot) — yanlış yedek seçildiyse geri dönüş mümkün.
 */
class RestoreTenant extends Command
{
    protected $signature = 'cms:restore-tenant
                            {tenant   : Tenant ID}
                            {backup   : Yedek dosya adı (backup_*.zip)}
                            {--no-snapshot : Geri yükleme öncesi otomatik yedeği atla}';

    protected $description = 'Restore a tenant from a backup zip (DB + storage)';

    public function handle(): int
    {
        $tenant = Tenant::find($this->argument('tenant'));

        if (! $tenant) {
            $this->error('Tenant bulunamadı.');
            return self::FAILURE;
        }

        try {
            $this->restoreTenant($tenant, $this->argument('backup'), ! $this->option('no-snapshot'));
            $this->info("✓ [{$tenant->id}] Geri yükleme tamamlandı.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("✗ Geri yükleme başarısız: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * Controller'dan da çağrılır (TenantBackupController::restore).
     */
    public function restoreTenant(Tenant $tenant, string $filename, bool $takeSnapshot = true): void
    {
        $tenantId = (string) $tenant->getTenantKey();

        if (! preg_match('/^backup_[\w\-]+\.zip$/', $filename)) {
            throw new \InvalidArgumentException('Geçersiz yedek dosya adı.');
        }

        $storagePath = "backups/{$tenantId}/{$filename}";

        if (! Storage::disk('local')->exists($storagePath)) {
            throw new \RuntimeException('Yedek dosyası bulunamadı.');
        }

        // ── 0. Pre-restore snapshot — geri dönüş garantisi ────────────────
        if ($takeSnapshot) {
            app(BackupTenant::class)->backupTenant($tenant);
        }

        $tmpDir = sys_get_temp_dir().'/cms_restore_'.$tenantId.'_'.now()->format('His');

        try {
            // ── 1. Zip'i aç ───────────────────────────────────────────────
            @mkdir($tmpDir, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open(Storage::disk('local')->path($storagePath)) !== true) {
                throw new \RuntimeException('Yedek zip dosyası açılamadı.');
            }
            $zip->extractTo($tmpDir);
            $zip->close();

            $sqlFile = "{$tmpDir}/database.sql";
            if (! is_file($sqlFile)) {
                throw new \RuntimeException('Yedekte database.sql yok — bozuk veya eski format.');
            }

            // ── 2. DB import (mariadb/mysql CLI) ─────────────────────────
            $dbName = config('tenancy.database.prefix').$tenantId.config('tenancy.database.suffix', '');

            $this->importSql(
                $sqlFile,
                $dbName,
                (string) config('database.connections.central.host', '127.0.0.1'),
                (int) config('database.connections.central.port', 3306),
                (string) config('database.connections.central.username'),
                (string) config('database.connections.central.password', ''),
            );

            // ── 3. Storage geri yükleme ───────────────────────────────────
            $targets = [
                "{$tmpDir}/storage/private" => storage_path("app/tenant_{$tenantId}"),
                "{$tmpDir}/storage/public"  => storage_path("app/public/tenant_{$tenantId}"),
            ];

            foreach ($targets as $src => $dst) {
                if (! is_dir($src)) {
                    continue; // yedekte bu dizin yoksa dokunma
                }
                $this->deleteDir($dst);
                @mkdir($dst, 0755, true);
                $this->copyDir($src, $dst);
            }
        } finally {
            $this->deleteDir($tmpDir);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function importSql(string $sqlFile, string $dbName, string $host, int $port, string $user, string $password): void
    {
        // mariadb istemcisi (mariadb-dump backup'ta kullanılıyor → client mevcut)
        $binary = $this->resolveClientBinary();

        $command = [
            $binary,
            "--host={$host}",
            "--port={$port}",
            "--user={$user}",
            '--ssl=FALSE',
            $dbName,
        ];

        $process = new Process(
            $command,
            null,
            ['MYSQL_PWD' => $password], // şifre argümanda görünmesin (ps çıktısı)
            file_get_contents($sqlFile),
            600
        );

        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('DB import hatası: '.trim($process->getErrorOutput() ?: $process->getOutput()));
        }
    }

    private function resolveClientBinary(): string
    {
        foreach (['mariadb', 'mysql'] as $bin) {
            $check = Process::fromShellCommandline("command -v {$bin}");
            $check->run();
            if ($check->isSuccessful() && trim($check->getOutput()) !== '') {
                return trim($check->getOutput());
            }
        }

        throw new \RuntimeException('mariadb/mysql istemcisi bulunamadı.');
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
}
