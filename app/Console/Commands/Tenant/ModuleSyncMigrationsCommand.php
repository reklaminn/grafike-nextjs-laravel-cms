<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use App\Services\Modules\ModuleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Recovery tool: tenant DB'de bad state'i temizler (table var ama
 * `migrations` tablosunda kayıt yok).
 *
 * Tipik senaryo:
 *   - Dev tenant'ı snapshot'tan klonlanmış, schema gelmiş ama migrations
 *     rows gelmemiş
 *   - Yarıda kalmış migration run (bir kısım table create oldu, sonra
 *     fail oldu, ama transaction iz bırakmadan kapandı)
 *   - Stancl v3 tenancy init context kayması (rare)
 *
 * Davranış:
 *   - Modülün migration path'indeki `*.php` dosyalarını listele
 *   - Tenant DB'sinin `migrations` tablosundan o dosya basename'lerini
 *     sorgula
 *   - Diff'i raporla (files-not-tracked)
 *   - --apply ile eksik row'ları INSERT et (next batch number)
 *
 * Güvenlik:
 *   - Default dry-run (sadece rapor)
 *   - --apply için --tenant zorunlu (tek tenant)
 *   - Hangi dosyaların ROW EKLENECEK olduğu açıkça listelenir
 *
 * Kullanım:
 *   php artisan tenant:module:sync-migrations tours --tenant=gemiseyahati
 *   php artisan tenant:module:sync-migrations tours --tenant=gemiseyahati --apply
 */
class ModuleSyncMigrationsCommand extends Command
{
    protected $signature = 'tenant:module:sync-migrations
                            {module : Module slug (e.g. tours)}
                            {--tenant= : Tenant id (required for --apply)}
                            {--apply : Insert missing migration rows (default = dry-run)}';

    protected $description = 'Reconcile a tenant\'s migrations table with files on disk (recovery tool)';

    public function handle(ModuleRegistry $registry): int
    {
        $module   = (string) $this->argument('module');
        $tenantId = (string) ($this->option('tenant') ?? '');
        $apply    = (bool) $this->option('apply');

        if (! $registry->exists($module)) {
            $this->error("Unknown module: {$module}");

            return self::FAILURE;
        }

        $path = $registry->migrationsPath($module);

        if ($path === null || ! is_dir($path)) {
            $this->error("Module [{$module}] has no migrations path on disk: " . ($path ?? '(null)'));

            return self::FAILURE;
        }

        if ($tenantId === '') {
            $this->error('--tenant is required.');

            return self::FAILURE;
        }

        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            $this->error("Tenant not found: {$tenantId}");

            return self::FAILURE;
        }

        // Dosyaları listele (basename, .php uzantısı çıkarılır)
        $files = $this->discoverMigrationFiles($path);

        $this->line("Module:  [{$module}]");
        $this->line("Tenant:  [{$tenantId}]");
        $this->line("Path:    {$path}");
        $this->line('Files:   ' . count($files));
        $this->newLine();

        // Tenant context'inde migrations tablosunu sorgula
        $wasInitialized   = tenancy()->initialized ?? false;
        $previousTenantId = tenancy()->tenant?->getTenantKey();
        $needSwitch       = ! $wasInitialized || $previousTenantId !== $tenant->getTenantKey();

        if ($needSwitch) {
            if ($wasInitialized) {
                tenancy()->end();
            }
            tenancy()->initialize($tenant);
        }

        try {
            // migrations tablosu yoksa ilk migrate çalıştırılmamış demektir
            if (! \Illuminate\Support\Facades\Schema::hasTable('migrations')) {
                $this->error('Tenant DB has no `migrations` table — run a normal migrate first.');

                return self::FAILURE;
            }

            $tracked = DB::table('migrations')
                ->whereIn('migration', $files)
                ->pluck('migration')
                ->all();

            $trackedSet = array_flip($tracked);
            $missing    = array_values(array_filter($files, fn ($f) => ! isset($trackedSet[$f])));

            $this->line('Tracked: ' . count($tracked) . ' / ' . count($files));

            if ($missing === []) {
                $this->info('✓ No drift detected — migrations table is in sync.');

                return self::SUCCESS;
            }

            $this->warn('⚠ Drift detected — files on disk but no row in migrations table:');
            foreach ($missing as $m) {
                $this->line("    • {$m}");
            }
            $this->newLine();

            if (! $apply) {
                $this->comment('Dry-run mode (no changes).  Re-run with --apply to insert missing rows.');
                $this->comment('NOTE: Only apply when tables for these migrations actually exist on disk.');

                return self::SUCCESS;
            }

            // Apply: bir sonraki batch numarasını al, eksik row'ları INSERT
            $nextBatch = ((int) DB::table('migrations')->max('batch')) + 1;
            $now       = now();

            $inserted = 0;
            foreach ($missing as $m) {
                DB::table('migrations')->insert([
                    'migration' => $m,
                    'batch'     => $nextBatch,
                ]);
                $inserted++;
                $this->line("    + {$m}");
            }

            $this->info("✓ Inserted {$inserted} migration row(s) at batch {$nextBatch}.");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Sync failed: ' . $e->getMessage());

            return self::FAILURE;
        } finally {
            if ($needSwitch) {
                tenancy()->end();
                if ($wasInitialized && $previousTenantId !== null) {
                    $prev = Tenant::query()->find($previousTenantId);
                    if ($prev) {
                        tenancy()->initialize($prev);
                    }
                }
            }
        }
    }

    /**
     * Dosya basename'lerini topla (.php uzantısı çıkarılmış).
     * Sıralama deterministic — Laravel migrator dosyaları aynı sırayla işler.
     *
     * @return array<int, string>
     */
    private function discoverMigrationFiles(string $path): array
    {
        $entries = scandir($path) ?: [];
        $files   = [];

        foreach ($entries as $entry) {
            if (! str_ends_with($entry, '.php')) {
                continue;
            }
            $files[] = substr($entry, 0, -4);
        }

        sort($files);

        return $files;
    }
}
