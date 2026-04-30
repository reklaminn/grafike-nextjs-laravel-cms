<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
     * Create a tenant + domain records (does NOT run migrations yet — use provision).
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

        $tenant = Tenant::create([
            'id'   => $validated['slug'],
            'data' => [
                'name'     => $validated['name'],
                'status'   => 'active',
                'theme_id' => $validated['theme_id'] ?? null,
            ],
        ]);

        // Register primary domain
        $tenant->domains()->create(['domain' => $domain]);

        // Auto-add www. variant only for root custom domains (e.g. nuhcicek.com.tr).
        // Skip for subdomain tenants (e.g. firma1.grafike.site) — the wildcard DNS
        // record (*.grafike.site) only covers one level, so www.firma1.grafike.site
        // would not resolve and Let's Encrypt would not be able to issue a cert for it.
        $centralDomain = env('APP_DOMAIN', '');
        $isSubdomainTenant = $centralDomain && str_ends_with($domain, '.' . $centralDomain);

        if (! str_starts_with($domain, 'www.') && ! $isSubdomainTenant) {
            $tenant->domains()->create(['domain' => 'www.' . $domain]);
        }

        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('success', "Tenant «{$tenant->name}» oluşturuldu. Şimdi veritabanını hazırlamak için Provision butonuna tıklayın.");
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
     * Run tenant migrations (provision the tenant DB).
     * Calls `php artisan tenants:migrate --tenants={id}`.
     */
    public function provision(Tenant $tenant)
    {
        try {
            \Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--force'   => true,
            ]);
            $output = \Artisan::output();
        } catch (\Throwable $e) {
            return back()->with('error', 'Migration hatası: ' . $e->getMessage());
        }

        return back()->with('success', "Tenant «{$tenant->name}» veritabanı hazır.\n" . $output);
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
        // End active session if this tenant was selected
        if (session('active_tenant') === $tenant->id) {
            session()->forget('active_tenant');
        }

        $tenant->delete(); // stancl cascades domain deletion

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', "Tenant «{$tenant->id}» silindi. Veritabanı manuel silinmesi gerekebilir.");
    }
}
