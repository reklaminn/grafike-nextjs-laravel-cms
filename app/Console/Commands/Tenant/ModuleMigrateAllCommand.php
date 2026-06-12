<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use App\Services\Modules\ModuleManager;
use App\Services\Modules\ModuleRegistry;
use Illuminate\Console\Command;
use Throwable;

/**
 * Deploy-time helper: run a module's migrations on EVERY tenant that has
 * it enabled.  Use this after merging new migrations under
 * `app/Modules/<Module>/Database/migrations/` so that all existing
 * customer DBs pick up the new tables.
 *
 *   php artisan tenants:modules:migrate --module=tours
 *   php artisan tenants:modules:migrate --module=tours --tenant=acme
 *
 * Without --module, runs migrations for every enabled module on every
 * tenant — useful as a "catch-all" idempotent step in deploy scripts.
 */
class ModuleMigrateAllCommand extends Command
{
    protected $signature = 'tenants:modules:migrate
                            {--module= : Restrict to a single module slug}
                            {--tenant= : Restrict to a single tenant id}';

    protected $description = 'Run module migrations across all tenants that have them enabled';

    public function handle(ModuleManager $manager, ModuleRegistry $registry): int
    {
        $moduleFilter = (string) ($this->option('module') ?? '');
        $tenantFilter = (string) ($this->option('tenant') ?? '');

        if ($moduleFilter !== '' && ! $registry->exists($moduleFilter)) {
            $this->error("Unknown module: {$moduleFilter}");
            return self::FAILURE;
        }

        $tenants = Tenant::query()
            ->when($tenantFilter !== '', fn ($q) => $q->where('id', $tenantFilter))
            ->orderBy('id')
            ->get();

        if ($tenants->isEmpty()) {
            $this->line('No tenants match.');
            return self::SUCCESS;
        }

        $processed = 0;
        $failed    = [];

        foreach ($tenants as $tenant) {
            $modules = $tenant->enabledModules();

            if ($moduleFilter !== '') {
                $modules = array_values(array_filter($modules, fn ($m) => $m === $moduleFilter));
            }

            if ($modules === []) {
                continue;
            }

            foreach ($modules as $slug) {
                if (! $registry->exists($slug)) {
                    $this->warn("  ⚠ {$tenant->getTenantKey()}: skipping unknown module [{$slug}]");
                    continue;
                }

                try {
                    $manager->runMigrations($tenant, $slug, rollback: false);
                    $this->line("  ✓ {$tenant->getTenantKey()}: [{$slug}] migrated");
                    $processed++;
                } catch (Throwable $e) {
                    $failed[] = "{$tenant->getTenantKey()}/{$slug}: " . $e->getMessage();
                    $this->error("  ✗ {$tenant->getTenantKey()}: [{$slug}] failed — " . $e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info("Migration runs completed: {$processed}");

        if ($failed !== []) {
            $this->error('Failures:');
            foreach ($failed as $line) {
                $this->line("  • {$line}");
            }
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
