<?php

declare(strict_types=1);

namespace App\Services\Modules;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
                // Re-run seeders too (idempotent — updateOrCreate-based)
                $this->runSeeders($tenant, $slug);
                continue;
            }

            $tenant->enableModule($slug);
            $tenant->save();
            $newlyAdded[] = $slug;

            $this->runMigrations($tenant, $slug, rollback: false);
            $this->runSeeders($tenant, $slug);

            Log::info('ModuleManager: module installed', [
                'tenant' => $tenant->getTenantKey(),
                'module' => $slug,
            ]);
        }

        return $newlyAdded;
    }

    /**
     * Run the module's seeders inside the tenant context.  Idempotent —
     * seeders must use updateOrCreate (or similar) so re-running on an
     * existing tenant doesn't duplicate.
     *
     * Called automatically by install() after migrations succeed.  Can
     * also be invoked manually via the tenant:module:seed artisan command
     * (Phase 1.5.b follow-up).
     */
    public function runSeeders(Tenant $tenant, string $module): void
    {
        $seeders = $this->registry->seeders($module);

        if ($seeders === []) {
            return;
        }

        // Stancl/tenancy v3 API: no tenancy()->run() helper exists
        // (added in v4).  Use manual initialize()/end() with try/finally
        // to guarantee the connection is restored even on exception.
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
            foreach ($seeders as $seederClass) {
                try {
                    /** @var \Illuminate\Database\Seeder $seeder */
                    $seeder = app($seederClass);
                    $seeder->run();

                    Log::info('ModuleManager: seeder ran', [
                        'tenant' => $tenant->getTenantKey(),
                        'module' => $module,
                        'seeder' => $seederClass,
                    ]);
                } catch (Throwable $e) {
                    Log::error('ModuleManager: seeder failed', [
                        'tenant' => $tenant->getTenantKey(),
                        'module' => $module,
                        'seeder' => $seederClass,
                        'error'  => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }
        } finally {
            if ($needSwitch) {
                tenancy()->end();
                // Restore previous context if there was one
                if ($wasInitialized && $previousTenantId !== null) {
                    $previousTenant = \App\Models\Tenant::query()->find($previousTenantId);
                    if ($previousTenant) {
                        tenancy()->initialize($previousTenant);
                    }
                }
            }
        }
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

        // Drift detection — sadece forward migrate sonrası.  Tenant DB'de
        // bad state (table var ama migrations row yok) varsa konsola/log'a
        // uyarı bas.  Otomatik düzeltmiyoruz çünkü row eklemek dosyaya
        // güvenmek demek; user explicit `tenant:module:sync-migrations`
        // ile onaylasın.
        if (! $rollback) {
            $this->reportDrift($tenant, $module, $path);
        }
    }

    /**
     * Post-migrate drift kontrolü — files vs migrations table diff'i.
     *
     * - Sessizce çalışır; sadece drift varsa Log::warning emit eder
     * - Otomatik INSERT yapmaz; operator `tenant:module:sync-migrations`
     *   komutuyla manuel olarak düzeltir (Phase 1.5.c follow-up)
     *
     * Tenant context'i çağrıldığında zaten `tenants:migrate` tarafından
     * initialize edilmişti — biz sonrasında çağırıyoruz, context restore
     * gerekiyor.  Defansif olarak yine initialize/end pattern kullanıyoruz.
     */
    private function reportDrift(Tenant $tenant, string $module, string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $files = [];
        foreach (scandir($path) ?: [] as $entry) {
            if (str_ends_with($entry, '.php')) {
                $files[] = substr($entry, 0, -4);
            }
        }
        if ($files === []) {
            return;
        }

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
            if (! Schema::hasTable('migrations')) {
                return; // genç tenant, migrate hiç koşmadı — drift değil
            }

            $tracked    = DB::table('migrations')->whereIn('migration', $files)->pluck('migration')->all();
            $trackedSet = array_flip($tracked);
            $missing    = array_values(array_filter($files, fn ($f) => ! isset($trackedSet[$f])));

            if ($missing !== []) {
                Log::warning('ModuleManager: migration drift detected after migrate', [
                    'tenant'           => $tenant->getTenantKey(),
                    'module'           => $module,
                    'missing_rows'     => $missing,
                    'recovery_command' => sprintf(
                        'php artisan tenant:module:sync-migrations %s --tenant=%s --apply',
                        $module,
                        $tenant->getTenantKey(),
                    ),
                ]);
            }
        } catch (Throwable $e) {
            // Drift detection is best-effort; never fail install over it
            Log::warning('ModuleManager: drift check failed', [
                'tenant' => $tenant->getTenantKey(),
                'module' => $module,
                'error'  => $e->getMessage(),
            ]);
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
