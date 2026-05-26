<?php

declare(strict_types=1);

namespace App\Jobs\Tenancy;

use App\Services\Modules\ModuleManager;
use App\Services\Modules\ModuleRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Throwable;

/**
 * Pipeline job — runs module migrations on a freshly-provisioned tenant DB.
 *
 * Chained AFTER stancl's MigrateDatabase in TenancyServiceProvider, so by
 * the time we run, the tenant DB exists and has all core migrations
 * applied.  This job adds module-specific migrations for any vertical
 * modules that were enabled at creation time (e.g. via a bulk-import
 * script or backup restore).
 *
 * In the typical UX flow, a tenant is created with NO modules enabled —
 * `enabledModules()` returns `[]`, this job is a no-op, and the admin
 * later enables Tours/Commerce via the panel (which triggers
 * ModuleManager::install directly).
 *
 * Idempotent: re-running on an already-migrated tenant is safe because
 * Laravel's migrations table prevents duplicate execution.
 */
class MigrateEnabledModulesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected TenantWithDatabase $tenant)
    {
    }

    public function handle(ModuleManager $manager, ModuleRegistry $registry): void
    {
        // The tenant model passed in from the JobPipeline is the central
        // representation; re-pull fresh from the DB so we see whatever
        // modules ended up in `data.modules` (other pipeline jobs may
        // have modified it earlier).
        $tenant = $this->tenant->fresh() ?? $this->tenant;

        if (! method_exists($tenant, 'enabledModules')) {
            return;
        }

        $modules = $tenant->enabledModules();

        if ($modules === []) {
            return;
        }

        foreach ($modules as $slug) {
            if (! $registry->exists($slug)) {
                Log::warning('MigrateEnabledModulesJob: skipping unknown module', [
                    'tenant' => $tenant->getTenantKey(),
                    'module' => $slug,
                ]);
                continue;
            }

            try {
                $manager->runMigrations($tenant, $slug, rollback: false);
            } catch (Throwable $e) {
                // Surface the failure but keep going — one broken module
                // shouldn't block the others.  Ops can re-run via
                // `tenant:module:install` once the underlying issue
                // (missing migration file, schema error, …) is fixed.
                Log::error('MigrateEnabledModulesJob: module migration failed', [
                    'tenant' => $tenant->getTenantKey(),
                    'module' => $slug,
                    'error'  => $e->getMessage(),
                ]);
            }
        }
    }
}
