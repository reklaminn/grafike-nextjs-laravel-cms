<?php

namespace App\Jobs;

use App\Models\SiteTemplate;
use App\Models\Tenant;
use App\Services\Tenants\IndustryTemplateApplier;
use App\Services\Tenants\TenantStarterContentSeeder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Throwable;

/**
 * Provisions a tenant database asynchronously.
 *
 * Runs on the 'default' queue worker. Creates the tenant DB,
 * runs all tenant migrations, seeds starter content, and optionally
 * applies an industry site template.
 *
 * Status lifecycle:
 *   provisioning → active   (success)
 *   provisioning → failed   (exception)
 *
 * The admin show page polls or refreshes when status = 'provisioning'.
 */
class ProvisionTenantDatabaseJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 300;   // 5 minutes max
    public int $tries   = 1;     // don't retry — partial state is dangerous

    public function __construct(
        public readonly string $tenantId,
        public readonly ?int   $siteTemplateId = null,
    ) {}

    public function handle(DatabaseManager $dbManager): void
    {
        $tenant = Tenant::findOrFail($this->tenantId);

        tenancy()->central(function () use ($tenant, $dbManager) {
            // 1. Create DB
            $tenant->database()->makeCredentials();
            $dbName = $tenant->database()->getName();

            if (! $tenant->database()->manager()->databaseExists($dbName)) {
                (new CreateDatabase($tenant))->handle($dbManager);
            }

            // 2. Run migrations
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->getTenantKey()],
                '--force'   => true,
            ]);

            // 3. Seed starter content + storage dirs
            tenancy()->initialize($tenant);
            try {
                app(TenantStarterContentSeeder::class)->seed($tenant);
            } finally {
                tenancy()->end();
            }

            // 4. Apply industry site template (optional)
            if ($this->siteTemplateId) {
                $template = SiteTemplate::find($this->siteTemplateId);
                if ($template) {
                    app(IndustryTemplateApplier::class)->apply($tenant, $template);
                }
            }
        });

        // Mark as active
        $this->updateStatus($tenant, 'active');
    }

    public function failed(Throwable $e): void
    {
        report($e);

        if ($tenant = Tenant::find($this->tenantId)) {
            $this->updateStatus($tenant, 'failed', $e->getMessage());
        }
    }

    private function updateStatus(Tenant $tenant, string $status, ?string $error = null): void
    {
        tenancy()->central(function () use ($tenant, $status, $error) {
            $data              = $tenant->toArray();
            $data['status']    = $status;
            if ($error) {
                $data['provision_error'] = substr($error, 0, 500);
            } else {
                unset($data['provision_error']);
            }

            // Persist via raw update to avoid VirtualColumn encoding issues
            \Illuminate\Support\Facades\DB::connection('central')
                ->table('tenants')
                ->where('id', $tenant->getTenantKey())
                ->update([
                    'data'       => json_encode(array_diff_key($data, array_flip(['id', 'tenancy_db_name'])), JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
        });
    }
}
