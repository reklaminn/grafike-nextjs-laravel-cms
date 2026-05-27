<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminTenantAccess;
use App\Models\SiteTemplate;
use App\Models\Tenant;
use App\Models\Theme;
use App\Modules\Payments\Services\IyzicoBYOKResolver;
use App\Services\Ai\AiManager;
use App\Services\Ai\Dtos\AiMessage;
use App\Services\Ai\Dtos\AiRequest;
use App\Services\Ai\Exceptions\AiProviderException;
use App\Jobs\ProvisionTenantDatabaseJob;
use App\Services\Modules\ModuleManager;
use App\Services\Modules\ModuleRegistry;
use App\Services\Tenants\IndustryTemplateApplier;
use App\Services\Tenants\TenantStarterContentSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\DeleteDatabase;
use Throwable;

class TenantController extends Controller
{
    /**
     * List all tenants (central DB).
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        $tenants = Tenant::with('domains')
            ->when($admin && ! $admin->isAgencyAdmin(), function ($query) use ($admin) {
                $query->whereIn('id', $admin->tenantAccesses()->select('tenant_id'));
            })
            ->latest()
            ->get();

        $canManageTenants = $admin?->isAgencyAdmin() ?? false;

        return view('admin.tenants.index', compact('tenants', 'canManageTenants'));
    }

    /**
     * Show the new-tenant form.
     */
    public function create()
    {
        $this->authorizeAgencyAdmin();

        $themes    = Theme::active()->orderBy('name')->get();
        $plans     = array_keys(config('ai.plans', []));
        $industries = SiteTemplate::INDUSTRIES;

        // Group active templates by industry for the JS-driven selector
        $templatesByIndustry = SiteTemplate::active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'industry'])
            ->groupBy('industry')
            ->map(fn ($group) => $group->values())
            ->toArray();

        // Map industry → suggested vertical modules.  JS uses this to
        // show a hint banner so the admin knows that picking "Turizm"
        // typically pairs with the Tours module (which still needs to
        // be enabled via the Modüller panel after creation).
        $industryModuleHints = SiteTemplate::INDUSTRY_MODULE_HINTS;

        return view('admin.tenants.create', compact(
            'themes', 'plans', 'industries', 'templatesByIndustry', 'industryModuleHints'
        ));
    }

    /**
     * Create a tenant + domain records, then provision its database.
     */
    public function store(Request $request)
    {
        $aiPlans = array_keys(config('ai.plans', []));

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'slug'     => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('central.tenants', 'id')],
            'domain'   => 'required|string|max:253',
            'theme_id' => ['nullable', Rule::exists('central.themes', 'id')],
            'plan'     => ['nullable', Rule::in($aiPlans)],
            'site_template_id' => ['nullable', Rule::exists('central.site_templates', 'id')],
            'create_company_admin' => 'nullable|boolean',
            'company_admin_name' => 'required_if:create_company_admin,1|nullable|string|max:255',
            'company_admin_username' => ['required_if:create_company_admin,1', 'nullable', 'string', 'max:255', Rule::unique('central.admins', 'username')],
            'company_admin_email' => ['required_if:create_company_admin,1', 'nullable', 'email', 'max:255', Rule::unique('central.admins', 'email')],
            'company_admin_password' => 'required_if:create_company_admin,1|nullable|string|min:6|confirmed',
        ]);

        $this->authorizeAgencyAdmin();

        // Normalise domain (strip protocol / trailing slash)
        $domain = strtolower(preg_replace('#^https?://#', '', rtrim($validated['domain'], '/')));

        $tenant = tenancy()->central(function () use ($validated, $domain) {
            return DB::connection('central')->transaction(function () use ($validated, $domain) {
                // Insert explicitly into the central table. This avoids both
                // stancl creation events and Eloquent key casting edge cases.
                $tenantData = [
                    'name'     => $validated['name'],
                    'status'   => 'provisioning',   // job will flip to 'active'
                    'theme_id' => $validated['theme_id'] ?? null,
                ];

                // Store initial AI plan in the data blob so quota checks
                // work immediately after provisioning.
                if (! empty($validated['plan'])) {
                    $tenantData['ai_settings'] = ['plan' => $validated['plan']];
                }

                DB::connection('central')->table('tenants')->insert([
                    'id'         => $validated['slug'],
                    'data'       => json_encode($tenantData, JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $tenant = Tenant::query()->findOrFail($validated['slug']);

                if ((string) $tenant->getTenantKey() !== (string) $validated['slug']) {
                    throw new \RuntimeException("Tenant ID kaydedilemedi. Beklenen: {$validated['slug']}, gelen: {$tenant->getTenantKey()}");
                }

                // Register primary domain. Keep tenant_id explicit so Eloquent
                // relation key casting can never turn string slugs into 0.
                $this->createTenantDomain($validated['slug'], $domain);

                // Auto-add www. variant only for root custom domains (e.g. nuhcicek.com.tr).
                // Skip for subdomain tenants (e.g. firma1.grafike.site) — the wildcard DNS
                // record (*.grafike.site) only covers one level, so www.firma1.grafike.site
                // would not resolve and Let's Encrypt would not be able to issue a cert for it.
                $centralDomain = env('APP_DOMAIN', '');
                $isSubdomainTenant = $centralDomain && str_ends_with($domain, '.' . $centralDomain);

                if (! str_starts_with($domain, 'www.') && ! $isSubdomainTenant) {
                    $this->createTenantDomain($validated['slug'], 'www.' . $domain);
                }

                if (! empty($validated['create_company_admin'])) {
                    $this->createCompanyAdminForTenant($validated);
                }

                return $tenant;
            });
        });

        // Dispatch async — DB creation + migrations run in the queue worker.
        // This prevents 504 timeouts on slow provisioning (~30-60 sec).
        ProvisionTenantDatabaseJob::dispatch(
            $tenant->getTenantKey(),
            $validated['site_template_id'] ?? null,
        );

        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('info', "Tenant «{$tenant->name}» oluşturuldu. Veritabanı arka planda hazırlanıyor… Sayfa otomatik güncellenecek.");
    }

    /**
     * Show tenant details + actions.
     */
    public function show(Tenant $tenant, \App\Services\Ai\AiQuotaService $quotaService, ModuleRegistry $registry, IyzicoBYOKResolver $iyzicoResolver)
    {
        $this->authorizeTenantAccess($tenant);

        $tenant->load('domains');
        $themes = Theme::active()->orderBy('name')->get();
        $canManageTenants = Auth::guard('admin')->user()?->isAgencyAdmin() ?? false;

        // Iyzico BYOK status for the panel.  Resolver checks for presence
        // without decrypting (cheap), sandbox flag is unencrypted in data.
        $iyzicoStatus = [
            'configured' => $iyzicoResolver->isConfigured($tenant),
            'sandbox'    => (bool) ($tenant->getAttribute('iyzico_keys')['sandbox'] ?? config('payments.gateways.iyzico.default_sandbox', true)),
        ];

        // AI usage snapshot for the panel — survives gracefully if the
        // central DB hasn't been migrated yet (e.g. fresh installs).
        try {
            $aiPlan  = $quotaService->planFor($tenant);
            $aiUsage = $quotaService->currentUsage($tenant);
        } catch (\Throwable $e) {
            report($e);
            $aiPlan  = ['name' => $tenant->aiPlan(), 'label' => '?', 'monthly_requests' => null, 'monthly_tokens' => null, 'monthly_cost_usd' => null];
            $aiUsage = ['requests' => 0, 'tokens' => 0, 'cost_usd' => 0.0, 'period' => now()->format('Y-m')];
        }

        // Vertical module catalog for the "Modüller" panel.  Filtered to
        // user-installable entries so internal modules (e.g. `payments`)
        // and not-yet-shipped modules (e.g. `commerce` in Phase 0) stay
        // hidden from the agency admin UI but are still toggleable via
        // artisan for testing.
        $availableModules = [];
        foreach ($registry->userInstallable() as $slug) {
            $availableModules[$slug] = [
                'slug'        => $slug,
                'label'       => $registry->label($slug),
                'description' => $registry->description($slug),
                'requires'    => $registry->requires($slug),
                'enabled'     => $tenant->hasModule($slug),
            ];
        }

        return view('admin.tenants.show', compact(
            'tenant', 'themes', 'canManageTenants', 'aiPlan', 'aiUsage', 'availableModules', 'iyzicoStatus'
        ));
    }

    /**
     * Persist Iyzico BYOK credentials on Tenant.data.iyzico_keys.
     *
     * Following the AI-settings pattern: an empty submitted key field
     * means "leave the existing value untouched"; pass `clear_<field>=1`
     * to explicitly drop the stored value.
     */
    public function updateIyzicoSettings(Request $request, Tenant $tenant)
    {
        $this->authorizeAgencyAdmin();

        $validated = $request->validate([
            'sandbox'          => 'nullable|boolean',
            'api_key'          => 'nullable|string|max:256',
            'secret_key'       => 'nullable|string|max:256',
            'clear_api_key'    => 'nullable|boolean',
            'clear_secret_key' => 'nullable|boolean',
        ]);

        $bag = $tenant->getAttribute('iyzico_keys');
        $bag = is_array($bag) ? $bag : [];

        // Sandbox flag — always overwritten (it's a checkbox).
        $bag['sandbox'] = (bool) ($validated['sandbox'] ?? false);

        // API key
        if (! empty($validated['clear_api_key'])) {
            unset($bag['api_key']);
        } elseif (! empty($validated['api_key'])) {
            $bag['api_key'] = Crypt::encryptString(trim($validated['api_key']));
        }

        // Secret key
        if (! empty($validated['clear_secret_key'])) {
            unset($bag['secret_key']);
        } elseif (! empty($validated['secret_key'])) {
            $bag['secret_key'] = Crypt::encryptString(trim($validated['secret_key']));
        }

        $tenant->setAttribute('iyzico_keys', $bag);
        $tenant->save();

        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('success', 'Iyzico ayarları güncellendi.');
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
        $this->authorizeAgencyAdmin();

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
        $this->authorizeTenantAccess($tenant);

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
        $this->authorizeAgencyAdmin();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'theme_id' => ['nullable', Rule::exists('central.themes', 'id')],
            'status'   => 'required|in:active,suspended',
        ]);

        $tenantId = (string) $tenant->getTenantKey();
        $currentData = DB::connection('central')
            ->table('tenants')
            ->where('id', $tenantId)
            ->value('data');

        $currentData = is_string($currentData)
            ? (json_decode($currentData, true) ?: [])
            : ((array) $currentData);

        DB::connection('central')
            ->table('tenants')
            ->where('id', $tenantId)
            ->update([
                'data' => json_encode(array_merge($currentData, [
                    'name'     => $validated['name'],
                    'theme_id' => $validated['theme_id'] ?? null,
                    'status'   => $validated['status'],
                ]), JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Tenant güncellendi.');
    }

    /**
     * Delete a tenant and its DB (irreversible).
     */
    public function destroy(Tenant $tenant)
    {
        $this->authorizeAgencyAdmin();

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

    /**
     * Update a tenant's AI / BYOK settings.
     *
     * Empty key fields are interpreted as "leave existing key untouched";
     * pass `clear_<provider>=1` to explicitly remove the stored key.
     */
    public function updateAiSettings(Request $request, Tenant $tenant)
    {
        $this->authorizeTenantAccess($tenant);

        $providers = array_keys(config('ai.providers', []));
        $plans     = array_keys(config('ai.plans', []));

        $rules = [
            'use_byok'           => 'nullable|boolean',
            'preferred_provider' => ['nullable', Rule::in($providers)],
            'plan'               => ['nullable', Rule::in($plans)],
        ];
        foreach ($providers as $p) {
            $rules["api_keys.{$p}"]       = 'nullable|string|max:512';
            $rules["clear.{$p}"]          = 'nullable|boolean';
            $rules["models.{$p}.simple"]  = 'nullable|string|max:128';
            $rules["models.{$p}.complex"] = 'nullable|string|max:128';
        }

        $validated = $request->validate($rules);

        $settings = $tenant->aiSettings();
        $settings['use_byok']           = (bool) ($validated['use_byok'] ?? false);
        $settings['preferred_provider'] = $validated['preferred_provider'] ?? null;
        if (! empty($validated['plan'])) {
            // Only agency admins should change plans; non-agency requests
            // would have been rejected by authorizeTenantAccess() anyway,
            // but we double-gate here in case roles widen later.
            if (Auth::guard('admin')->user()?->isAgencyAdmin()) {
                $settings['plan'] = $validated['plan'];
            }
        }
        $settings['models'] = $settings['models'] ?? [];

        foreach ($providers as $p) {
            // Models (per provider, per tier)
            $simple  = $validated['models'][$p]['simple']  ?? null;
            $complex = $validated['models'][$p]['complex'] ?? null;
            if ($simple || $complex) {
                $settings['models'][$p] = array_filter([
                    'simple'  => $simple,
                    'complex' => $complex,
                ]);
            } else {
                unset($settings['models'][$p]);
            }

            // API keys
            if (! empty($validated['clear'][$p])) {
                $tenant->setAiSettings($settings);
                $tenant->setAiApiKey($p, null);
                $settings = $tenant->aiSettings();
                continue;
            }
            $newKey = trim((string) ($validated['api_keys'][$p] ?? ''));
            if ($newKey !== '') {
                $tenant->setAiSettings($settings);
                $tenant->setAiApiKey($p, $newKey);
                $settings = $tenant->aiSettings();
            }
        }

        $tenant->setAiSettings($settings);
        $tenant->save();

        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('success', 'AI ayarları güncellendi.');
    }

    /**
     * Toggle which vertical modules (Tours, Commerce, …) are enabled
     * on a tenant.  Installing a module runs its tenant migrations;
     * uninstalling only flips the flag (tables are preserved unless
     * `php artisan tenant:module:uninstall ... --drop-tables` is used).
     */
    public function updateModules(Request $request, Tenant $tenant, ModuleManager $manager, ModuleRegistry $registry)
    {
        $this->authorizeAgencyAdmin();

        // Only modules listed as user-installable in the registry can be
        // toggled via the UI.  Internal modules (payments) are pulled
        // in transitively by the manager; not-yet-shipped modules
        // (commerce) require artisan access.
        $available = $registry->userInstallable();

        $validated = $request->validate([
            'modules'   => 'array',
            'modules.*' => ['string', Rule::in($available)],
        ]);

        $desired = array_values(array_unique($validated['modules'] ?? []));
        $current = array_values(array_intersect($tenant->enabledModules(), $available));

        $toEnable  = array_diff($desired, $current);
        $toDisable = array_diff($current, $desired);

        $errors = [];

        foreach ($toEnable as $slug) {
            try {
                $manager->install($tenant, $slug);
            } catch (\Throwable $e) {
                report($e);
                $errors[] = "[{$slug}] etkinleştirilemedi: " . $e->getMessage();
            }
        }

        foreach ($toDisable as $slug) {
            try {
                // UI-side disable never drops tables — data preservation
                // is the safe default.  Use the artisan command with
                // `--drop-tables` for irreversible cleanup.
                $manager->uninstall($tenant, $slug, dropTables: false);
            } catch (\Throwable $e) {
                report($e);
                $errors[] = "[{$slug}] devre dışı bırakılamadı: " . $e->getMessage();
            }
        }

        if ($errors !== []) {
            return back()->with('error', "Modül güncellemesinde hata:\n" . implode("\n", $errors));
        }

        $changed = count($toEnable) + count($toDisable);
        $message = $changed === 0
            ? 'Modül seçiminde değişiklik yok.'
            : sprintf(
                'Modüller güncellendi (%d etkinleştirildi, %d devre dışı bırakıldı).',
                count($toEnable),
                count($toDisable),
            );

        return back()->with('success', $message);
    }

    /**
     * Smoke-test a tenant's AI key by sending a tiny prompt. Does not
     * mutate state. Returns JSON with status + token usage.
     */
    public function testAiKey(Request $request, Tenant $tenant, AiManager $manager)
    {
        $this->authorizeTenantAccess($tenant);

        $providerName = $request->input('provider')
            ?: $tenant->preferredAiProvider()
            ?: $manager->defaultProvider();

        $apiKey = $tenant->aiApiKey($providerName);
        if (! $apiKey) {
            return response()->json([
                'ok'      => false,
                'message' => "Bu provider için kayıtlı bir API anahtarı yok: {$providerName}.",
            ], 422);
        }

        try {
            $response = $manager->provider($providerName)->generate(new AiRequest(
                model:          $manager->resolveModel('simple', $providerName),
                messages:       [AiMessage::user('OK')],
                system:         'Reply with the single word "ok" and nothing else.',
                maxTokens:      10,
                temperature:    0.0,
                metadata:       ['source' => 'tenant.ai-test', 'tenant_id' => $tenant->getKey()],
                apiKeyOverride: $apiKey,
            ));

            return response()->json([
                'ok'       => true,
                'provider' => $response->provider,
                'model'    => $response->model,
                'content'  => $response->content,
                'usage'    => $response->usage->toArray(),
            ]);
        } catch (AiProviderException $e) {
            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage(),
                'status'  => $e->statusCode,
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Beklenmedik hata: '.$e->getMessage(),
            ], 500);
        }
    }

    private function createTenantDomain(string $tenantId, string $domain): void
    {
        DB::connection('central')->table('domains')->insert([
            'domain'    => $domain,
            'tenant_id' => $tenantId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCompanyAdminForTenant(array $validated): void
    {
        $admin = Admin::create([
            'name' => $validated['company_admin_name'],
            'username' => $validated['company_admin_username'],
            'email' => $validated['company_admin_email'],
            'password' => $validated['company_admin_password'],
        ]);

        AdminTenantAccess::create([
            'admin_id' => $admin->id,
            'tenant_id' => $validated['slug'],
            'role' => 'owner',
            'is_default' => true,
        ]);
    }

    private function authorizeAgencyAdmin(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isAgencyAdmin(), 403);
    }

    private function authorizeTenantAccess(Tenant $tenant): void
    {
        abort_unless(Auth::guard('admin')->user()?->canAccessTenant($tenant), 403);
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

            tenancy()->initialize($tenant);
            try {
                $this->ensureTenantStorageDirectories();
                app(TenantStarterContentSeeder::class)->seed($tenant);
            } finally {
                tenancy()->end();
            }
        });
    }

    private function ensureTenantStorageDirectories(): void
    {
        foreach ([
            storage_path('app'),
            storage_path('app/public'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ] as $path) {
            File::ensureDirectoryExists($path, 0775, true);
        }
    }
}
