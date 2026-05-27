<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use App\Services\Modules\ModuleManager;
use App\Services\Modules\ModuleRegistry;
use Illuminate\Console\Command;
use Throwable;

/**
 * Run a module's seeders on a tenant (or all tours-enabled tenants).
 *
 * Use cases:
 *   - Mevcut tenant'larda (Phase 1.5.b deploy öncesinden tours install'lı)
 *     yeni seeder'ları çalıştırmak
 *   - Master data değişikliği sonrası re-seed (örn. yeni CabinCategory eklendi)
 *
 * Idempotent — seeders updateOrCreate kullanır, re-run güvenli.
 *
 *   php artisan tenant:module:seed tours --tenant=gemiseyahati
 *   php artisan tenant:module:seed tours              # tüm tours-enabled tenant'lar
 */
class ModuleSeedCommand extends Command
{
    protected $signature = 'tenant:module:seed
                            {module : Module slug (e.g. tours)}
                            {--tenant= : Tenant id (omit = all enabled tenants)}';

    protected $description = 'Run a module\'s seeders on a tenant (or all enabled tenants)';

    public function handle(ModuleManager $manager, ModuleRegistry $registry): int
    {
        $module   = (string) $this->argument('module');
        $tenantId = (string) ($this->option('tenant') ?? '');

        if (! $registry->exists($module)) {
            $this->error("Unknown module: {$module}");
            return self::FAILURE;
        }

        $seeders = $registry->seeders($module);
        if ($seeders === []) {
            $this->warn("Module [{$module}] has no seeders configured.");
            return self::SUCCESS;
        }

        $this->info('Will run seeders:');
        foreach ($seeders as $s) {
            $this->line("  - {$s}");
        }

        $tenants = $tenantId !== ''
            ? Tenant::query()->where('id', $tenantId)->get()
            : Tenant::query()->orderBy('id')->get()
                ->filter(fn ($t) => method_exists($t, 'hasModule') && $t->hasModule($module));

        if ($tenants->isEmpty()) {
            $this->error('No matching tenants.');
            return self::FAILURE;
        }

        $processed = 0;
        $failed    = [];

        foreach ($tenants as $tenant) {
            try {
                $manager->runSeeders($tenant, $module);
                $this->line("  ✓ {$tenant->getTenantKey()}: seeders ran");
                $processed++;
            } catch (Throwable $e) {
                $failed[] = "{$tenant->getTenantKey()}: " . $e->getMessage();
                $this->error("  ✗ {$tenant->getTenantKey()}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Completed: {$processed} tenant(s).");

        if ($failed !== []) {
            $this->error('Failures:');
            foreach ($failed as $f) {
                $this->line("  • {$f}");
            }
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
