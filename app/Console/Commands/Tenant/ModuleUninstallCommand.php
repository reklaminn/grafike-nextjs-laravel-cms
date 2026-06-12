<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use App\Services\Modules\ModuleManager;
use App\Services\Modules\ModuleRegistry;
use Illuminate\Console\Command;
use Throwable;

/**
 * Disable a vertical module on a single tenant.
 *
 *   php artisan tenant:module:uninstall tours --tenant=acentem
 *   php artisan tenant:module:uninstall tours --tenant=acentem --drop-tables
 *
 * Without --drop-tables, the data and tables stay intact — re-enabling
 * later is non-destructive.  With --drop-tables, module migrations are
 * rolled back (irreversible without backup).
 *
 * Refuses to uninstall a dependency that's still needed by other enabled
 * modules, unless --force is set.
 */
class ModuleUninstallCommand extends Command
{
    protected $signature = 'tenant:module:uninstall
                            {module : Module slug (e.g. tours, commerce)}
                            {--tenant= : Tenant id (slug)}
                            {--drop-tables : Roll back the module migrations on the tenant DB (DESTRUCTIVE)}
                            {--force : Uninstall even if other enabled modules depend on it}';

    protected $description = 'Disable a vertical module on a tenant (optionally drops its tables)';

    public function handle(ModuleManager $manager, ModuleRegistry $registry): int
    {
        $module     = (string) $this->argument('module');
        $tenantId   = (string) ($this->option('tenant') ?? '');
        $dropTables = (bool) $this->option('drop-tables');
        $force      = (bool) $this->option('force');

        if ($tenantId === '') {
            $this->error('--tenant is required.');
            return self::FAILURE;
        }

        if (! $registry->exists($module)) {
            $this->error("Unknown module: {$module}");
            return self::FAILURE;
        }

        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            $this->error("Tenant not found: {$tenantId}");
            return self::FAILURE;
        }

        if ($dropTables && ! $force) {
            if (! $this->confirm("⚠️  --drop-tables will roll back ALL tenant migrations for [{$module}] on «{$tenantId}». Data will be lost. Continue?", false)) {
                $this->line('Cancelled.');
                return self::SUCCESS;
            }
        }

        try {
            $manager->uninstall($tenant, $module, dropTables: $dropTables, force: $force);
        } catch (Throwable $e) {
            $this->error('Uninstall failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Tenant «%s» — disabled: [%s]%s',
            $tenantId,
            $module,
            $dropTables ? ' (tables dropped)' : ' (tables preserved)',
        ));

        $remaining = $tenant->fresh()->enabledModules();
        $this->line('Remaining modules: ' . ($remaining === [] ? '(core only)' : implode(', ', $remaining)));

        return self::SUCCESS;
    }
}
