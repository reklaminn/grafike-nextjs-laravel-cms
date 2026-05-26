<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use App\Services\Modules\ModuleManager;
use App\Services\Modules\ModuleRegistry;
use Illuminate\Console\Command;
use Throwable;

/**
 * Enable a vertical module on a single tenant and run its migrations.
 *
 *   php artisan tenant:module:install tours --tenant=acentem
 *
 * Dependencies are resolved automatically (e.g. installing `tours` also
 * pulls in `payments` if not yet enabled).
 */
class ModuleInstallCommand extends Command
{
    protected $signature = 'tenant:module:install
                            {module : Module slug (e.g. tours, commerce)}
                            {--tenant= : Tenant id (slug)}';

    protected $description = 'Enable a vertical module on a tenant and run its migrations';

    public function handle(ModuleManager $manager, ModuleRegistry $registry): int
    {
        $module   = (string) $this->argument('module');
        $tenantId = (string) ($this->option('tenant') ?? '');

        if ($tenantId === '') {
            $this->error('--tenant is required.');
            return self::FAILURE;
        }

        if (! $registry->exists($module)) {
            $this->error("Unknown module: {$module}");
            $this->line('Available modules: ' . implode(', ', $registry->all()));
            return self::FAILURE;
        }

        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            $this->error("Tenant not found: {$tenantId}");
            return self::FAILURE;
        }

        $chain = $registry->resolveInstallOrder($module);
        $this->line('Install order: ' . implode(' → ', $chain));

        try {
            $newly = $manager->install($tenant, $module);
        } catch (Throwable $e) {
            $this->error('Install failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        if ($newly === []) {
            $this->info("Tenant «{$tenantId}» already had [{$module}] enabled. Migrations re-checked (idempotent).");
        } else {
            $this->info(sprintf(
                'Tenant «%s» — newly enabled: [%s]. Migrations applied.',
                $tenantId,
                implode(', ', $newly),
            ));
        }

        $this->line('Current modules: ' . (implode(', ', $tenant->fresh()->enabledModules()) ?: '(core only)'));

        return self::SUCCESS;
    }
}
