<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Theme;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\DeleteDatabase;

class TenantController extends Controller
{
    /**
     * List all tenants (central DB).
     */
    public function index()
    {
        $tenants = Tenant::with('domains')->latest()->get();

        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Show the new-tenant form.
     */
    public function create()
    {
        $themes = Theme::active()->orderBy('name')->get();

        return view('admin.tenants.create', compact('themes'));
    }

    /**
     * Create a tenant + domain records, then provision its database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'slug'     => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('tenants', 'id')],
            'domain'   => 'required|string|max:253',
            'theme_id' => 'nullable|exists:themes,id',
        ]);

        // Normalise domain (strip protocol / trailing slash)
        $domain = strtolower(preg_replace('#^https?://#', '', rtrim($validated['domain'], '/')));

        $tenant = tenancy()->central(function () use ($validated, $domain) {
            return DB::connection('central')->transaction(function () use ($validated, $domain) {
                // Provisioning is handled explicitly below. Bypassing the
                // TenantCreated event avoids the admin request crashing inside
                // stancl's synchronous CreateDatabase/MigrateDatabase pipeline.
                $tenant = Tenant::withoutEvents(function () use ($validated) {
                    $tenant = new Tenant();
                    $tenant->forceFill([
                        'id'   => $validated['slug'],
                        'data' => [
                            'name'     => $validated['name'],
                            'status'   => 'active',
                            'theme_id' => $validated['theme_id'] ?? null,
                        ],
                    ]);
                    $tenant->save();

                    return $tenant;
                });

                if ((string) $tenant->getTenantKey() !== (string) $validated['slug']) {
                    throw new \RuntimeException("Tenant ID kaydedilemedi. Beklenen: {$validated['slug']}, gelen: {$tenant->getTenantKey()}");
                }

                // Register primary domain. Keep tenant_id explicit so Eloquent
                // relation key casting can never turn string slugs into 0.
                $this->createTenantDomain($tenant, $domain);

                // Auto-add www. variant only for root custom domains (e.g. nuhcicek.com.tr).
                // Skip for subdomain tenants (e.g. firma1.grafike.site) — the wildcard DNS
                // record (*.grafike.site) only covers one level, so www.firma1.grafike.site
                // would not resolve and Let's Encrypt would not be able to issue a cert for it.
                $centralDomain = env('APP_DOMAIN', '');
                $isSubdomainTenant = $centralDomain && str_ends_with($domain, '.' . $centralDomain);

                if (! str_starts_with($domain, 'www.') && ! $isSubdomainTenant) {
                    $this->createTenantDomain($tenant, 'www.' . $domain);
                }

                return $tenant;
            });
        });

        try {
            $this->provisionTenantDatabase($tenant);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.tenants.show', $tenant)
                ->with('warning', "Tenant «{$tenant->name}» oluşturuldu ancak veritabanı/migration adımı başarısız oldu: {$e->getMessage()}");
        }

        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('success', "Tenant «{$tenant->name}» oluşturuldu. Veritabanı ve migrationlar otomatik çalıştırıldı.");
    }

    /**
     * Show tenant details + actions.
     */
    public function show(Tenant $tenant)
    {
        $tenant->load('domains');
        $themes = Theme::active()->orderBy('name')->get();

        return view('admin.tenants.show', compact('tenant', 'themes'));
    }

    /**
     * (Re-)run tenant migrations on an existing tenant DB.
     *
     * This action is useful when:
     *  - New tenant migrations were added and need to be applied to existing tenants
     *  - The initial provisioning failed (e.g. DB permissions not yet granted)
     */
    public function provision(Tenant $tenant)
    {
        try {
            $this->provisionTenantDatabase($tenant);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            return back()->with('error', 'Migration hatası: ' . $e->getMessage());
        }

        return back()->with('success', "Tenant «{$tenant->name}» migrationları çalıştırıldı.\n" . $output);
    }

    /**
     * Set the active tenant for the admin session.
     * After this, Page / Article / Menu queries run against this tenant's DB.
     */
    public function switchTo(Tenant $tenant)
    {
        session(['active_tenant' => $tenant->id]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Aktif site: {$tenant->name}");
    }

    /**
     * Clear the active tenant session (back to central/no-tenant context).
     */
    public function clearActive()
    {
        session()->forget('active_tenant');

        return back()->with('success', 'Aktif site temizlendi.');
    }

    /**
     * Update tenant metadata (name, theme, status).
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'theme_id' => 'nullable|exists:themes,id',
            'status'   => 'required|in:active,suspended',
        ]);

        $tenant->update([
            'data' => array_merge($tenant->data ?? [], [
                'name'     => $validated['name'],
                'theme_id' => $validated['theme_id'] ?? null,
                'status'   => $validated['status'],
            ]),
        ]);

        return back()->with('success', 'Tenant güncellendi.');
    }

    /**
     * Delete a tenant and its DB (irreversible).
     */
    public function destroy(Tenant $tenant)
    {
        $tenantId = (string) $tenant->getTenantKey();
        $databaseWarning = null;

        // End active session if this tenant was selected
        if ((string) session('active_tenant') === $tenantId) {
            session()->forget('active_tenant');
        }

        try {
            $tenant->database()->makeCredentials();
            $databaseName = $tenant->database()->getName();

            if ($tenant->database()->manager()->databaseExists($databaseName)) {
                (new DeleteDatabase($tenant))->handle();
            } else {
                $databaseWarning = "Tenant veritabanı zaten yoktu: {$databaseName}";
            }
        } catch (\Throwable $e) {
            report($e);
            $databaseWarning = 'Tenant veritabanı silinemedi: ' . $e->getMessage();
        }

        // Delete the central tenant/domain records without firing stancl's
        // TenantDeleted pipeline again; database cleanup was handled above.
        Tenant::withoutEvents(function () use ($tenant) {
            $tenant->delete(); // domains cascade by FK
        });

        $message = "Tenant «{$tenantId}» silindi.";
        if ($databaseWarning) {
            $message .= ' ' . $databaseWarning;
        }

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', $message);
    }

    private function createTenantDomain(Tenant $tenant, string $domain): void
    {
        $domainModel = config('tenancy.domain_model');

        $domainModel::query()->create([
            'domain'    => $domain,
            'tenant_id' => (string) $tenant->getTenantKey(),
        ]);
    }

    private function provisionTenantDatabase(Tenant $tenant): void
    {
        tenancy()->central(function () use ($tenant) {
            if (! $tenant->getTenantKey()) {
                throw new \RuntimeException('Tenant ID boş görünüyor; veritabanı oluşturulamadı.');
            }

            $tenant->database()->makeCredentials();
            $databaseName = $tenant->database()->getName();

            if (! $tenant->database()->manager()->databaseExists($databaseName)) {
                (new CreateDatabase($tenant))->handle(app(DatabaseManager::class));
            }

            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->getTenantKey()],
                '--force'   => true,
            ]);
        });
    }
}
