<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use App\Services\Modules\ModuleRegistry;
use Illuminate\Console\Command;

/**
 * Inspect tenant module state.
 *
 *   php artisan tenant:module:list                  → table of every tenant + enabled modules
 *   php artisan tenant:module:list --tenant=acentem → details for a single tenant
 *   php artisan tenant:module:list --available      → just the registry of known modules
 */
class ModuleListCommand extends Command
{
    protected $signature = 'tenant:module:list
                            {--tenant= : Restrict to one tenant id}
                            {--available : Show the module registry (config/tenant_modules.php) instead of tenant state}';

    protected $description = 'List enabled modules per tenant (or the module registry)';

    public function handle(ModuleRegistry $registry): int
    {
        if ($this->option('available')) {
            return $this->showAvailable($registry);
        }

        $tenantId = (string) ($this->option('tenant') ?? '');

        if ($tenantId !== '') {
            return $this->showOne($registry, $tenantId);
        }

        return $this->showAll($registry);
    }

    private function showAvailable(ModuleRegistry $registry): int
    {
        $rows = [];
        foreach ($registry->all() as $slug) {
            $def = $registry->definition($slug);
            $rows[] = [
                $slug,
                $registry->label($slug),
                ($def['user_installable'] ?? false) ? 'yes' : 'no',
                implode(', ', $registry->requires($slug)) ?: '—',
            ];
        }

        $this->table(['Slug', 'Label', 'User-installable', 'Requires'], $rows);

        return self::SUCCESS;
    }

    private function showOne(ModuleRegistry $registry, string $tenantId): int
    {
        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            $this->error("Tenant not found: {$tenantId}");
            return self::FAILURE;
        }

        $enabled = $tenant->enabledModules();

        $this->info("Tenant «{$tenantId}» — {$tenant->name}");
        $this->line('Modules: ' . ($enabled === [] ? '(core only)' : implode(', ', $enabled)));

        if ($enabled !== []) {
            $rows = [];
            foreach ($enabled as $slug) {
                $rows[] = [$slug, $registry->exists($slug) ? $registry->label($slug) : '⚠️ unknown'];
            }
            $this->table(['Slug', 'Label'], $rows);
        }

        return self::SUCCESS;
    }

    private function showAll(ModuleRegistry $registry): int
    {
        $tenants = Tenant::query()->orderBy('id')->get();

        if ($tenants->isEmpty()) {
            $this->line('No tenants.');
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($tenants as $tenant) {
            $enabled = $tenant->enabledModules();
            $rows[] = [
                $tenant->getTenantKey(),
                $tenant->name ?? '—',
                $enabled === [] ? '(core only)' : implode(', ', $enabled),
            ];
        }

        $this->table(['Tenant ID', 'Name', 'Enabled modules'], $rows);

        return self::SUCCESS;
    }
}
