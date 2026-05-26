<?php

declare(strict_types=1);

namespace App\Services\Modules;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Orchestrates installing / uninstalling a vertical module on a single tenant.
 *
 * Responsibilities:
 *   - Validate against ModuleRegistry (unknown slug → exception)
 *   - Resolve dependencies (install all `requires` before the target)
 *   - Persist the module flag on Tenant.data['modules']
 *   - Run the module's tenant migrations against the tenant's DB
 *   - On uninstall, optionally roll back migrations (drop tables)
 *
 * The actual DB-creation is *not* this class's job — tenants are created
 * by the standard stancl pipeline; ModuleManager only adds the
 * module-specific tables to an already-provisioned tenant DB.
 */
class ModuleManager
{
    public function __construct(private readonly ModuleRegistry $registry)
    {
    }

    /**
     * Install `$module` (and its dependencies, transitively) on `$tenant`.
     *
     * Idempotent: re-running on an already-enabled module is a no-op for
     * the flag, and Laravel's migrations table prevents duplicate table
     * creation, so the operation is safe to retry.
     *
     * Returns the list of modules that were actually newly enabled
     * during this call (useful for UI feedback).
     *
     * @return array<int, string>
     */
    public function install(Tenant $tenant, string $module): array
    {
        if (! $this->registry->exists($module)) {
            throw new InvalidArgumentException("Unknown tenant module: {$module}");
        }

        $chain      = $this->registry->resolveInstallOrder($module);
        $newlyAdded = [];

        foreach ($chain as $slug) {
            if ($tenant->hasModule($slug)) {
                // Still re-run migrations to catch newly-added files
                // for previously-installed modules.  No-op if up-to-date.
                $this->runMigrations($tenant, $slug, rollback: false);
                continue;
            }

            $tenant->enableModule($slug);
            $tenant->save();
            $newlyAdded[] = $slug;

            $this->runMigrations($tenant, $slug, rollback: false);

            Log::info('ModuleManager: module installed', [
                'tenant' => $tenant->getTenantKey(),
                'module' => $slug,
            ]);
        }

        return $newlyAdded;
    }

    /**
     * Disable `$module` on `$tenant`.
     *
     * When `$dropTables` is false (the default), only the flag is removed —
     * data and tables stay intact (re-enabling later is non-destructive).
     *
     * When `$dropTables` is true, the module's migrations are rolled back
     * on the tenant DB.  Destructive — caller must confirm with the user.
     *
     * Refuses to uninstall a module that other enabled modules depend on,
     * unless `$force` is true (in which case dependents are silently
     * orphaned — only use during testing / recovery).
     */
    public function uninstall(
        Tenant $tenant,
        string $module,
        bool $dropTables = false,
        bool $force = false,
    ): void {
        if (! $this->registry->exists($module)) {
            throw new InvalidArgumentException("Unknown tenant module: {$module}");
        }

        $slug = strtolower(trim($module));

        if (! $tenant->hasModule($slug)) {
            // Already disabled — nothing to do.
            return;
        }

        if (! $force) {
            $dependents = $this->findEnabledDependents($tenant, $slug);
            if ($dependents !== []) {
                throw new RuntimeException(sprintf(
                    'Cannot disable [%s] while these enabled modules depend on it: %s. ' .
                    'Disable them first, or pass --force.',
                    $slug,
                    implode(', ', $dependents),
                ));
            }
        }

        if ($dropTables) {
            $this->runMigrations($tenant, $slug, rollback: true);
        }

        $tenant->disableModule($slug);
        $tenant->save();

        Log::info('ModuleManager: module uninstalled', [
            'tenant'      => $tenant->getTenantKey(),
            'module'      => $slug,
            'drop_tables' => $dropTables,
            'force'       => $force,
        ]);
    }

    /**
     * Run the module's tenant migrations against the given tenant's DB.
     *
     * Uses stancl's `tenants:migrate` command with `--path` override so
     * each module owns its migration directory under
     * `app/Modules/<Module>/Database/migrations/`.
     *
     * No-op when the module has no migrations_path configured.
     */
    public function runMigrations(Tenant $tenant, string $module, bool $rollback = false): void
    {
        $path = $this->registry->migrationsPath($module);

        if ($path === null) {
            return;
        }

        if (! is_dir($path)) {
            // First-class warning, not a fatal — module may be in early
            // development with no migration files yet.
            Log::warning('ModuleManager: migrations_path missing on disk', [
                'module' => $module,
                'path'   => $path,
            ]);
            return;
        }

        $command   = $rollback ? 'tenants:rollback' : 'tenants:migrate';
        $arguments = [
            '--tenants'  => [$tenant->getTenantKey()],
            '--path'     => [$path],
            '--realpath' => true,
            '--force'    => true,
        ];

        try {
            Artisan::call($command, $arguments);
        } catch (Throwable $e) {
            Log::error('ModuleManager: migration command failed', [
                'tenant'   => $tenant->getTenantKey(),
                'module'   => $module,
                'rollback' => $rollback,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Modules currently enabled on this tenant that depend on `$module`.
     *
     * @return array<int, string>
     */
    private function findEnabledDependents(Tenant $tenant, string $module): array
    {
        $module    = strtolower(trim($module));
        $dependents = [];

        foreach ($tenant->enabledModules() as $other) {
            if ($other === $module) {
                continue;
            }
            if (in_array($module, $this->registry->requires($other), true)) {
                $dependents[] = $other;
            }
        }

        return $dependents;
    }
}
