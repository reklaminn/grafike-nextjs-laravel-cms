<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\AdminTenantAccess;
use App\Models\Tenant;
use App\Support\AdminPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Tenant'a özel "Ekip / Yöneticiler" — site SAHİBİ (owner) kendi sitesine üye
 * ekler ve onlara Roller/Yetkiler'de tanımlı bir rol atar (izinler enforce
 * edilir). Ajans/superadmin'in global "Yöneticiler" sayfasından (agency.admin)
 * FARKLI: yalnızca AKTİF tenant kapsamında çalışır.
 *
 * Güvenlik:
 *  - Sadece aktif tenant'ın owner'ı (veya agency admin) erişir.
 *  - Atanabilir roller YALNIZCA tenant-güvenli olanlar — super-admin ve
 *    ajans-seviyesi izin (tenants/admins/roles/maintenance) içerenler DIŞARIDA.
 *  - Rol opsiyonel: seçilmezse üye tam tenant yetkisine sahip (eski davranış,
 *    Gate::before rolsüz bypass'ı); seçilirse rolün izinleriyle kısıtlanır.
 *  - Çıkarma: üyenin başka tenant erişimi kalmıyor ve super-admin değilse hesap
 *    tamamen silinir (yoksa isAgencyAdmin()=true olup tüm platforma erişir).
 */
class TenantTeamController extends Controller
{
    public function index()
    {
        $tenant = $this->activeOwnedTenant();

        $members = AdminTenantAccess::with('admin.roles')
            ->where('tenant_id', $tenant->id)
            ->get()
            ->filter(fn (AdminTenantAccess $a) => $a->admin !== null)
            ->sortBy(fn (AdminTenantAccess $a) => [$a->role === 'owner' ? 0 : 1, $a->admin->name])
            ->values();

        $roles   = $this->assignableRoles();
        $max     = $tenant->maxAdminUsers();
        $current = $members->count();

        return view('admin.team.index', compact('tenant', 'members', 'roles', 'max', 'current'));
    }

    public function store(Request $request)
    {
        $tenant = $this->activeOwnedTenant();

        $request->validate([
            'email' => 'required|email|max:255',
            'role'  => ['nullable', Rule::in($this->assignableRoles()->pluck('name')->all())],
        ]);

        $existing = Admin::where('email', $request->input('email'))->first();

        if ($existing) {
            if (AdminTenantAccess::where('admin_id', $existing->id)->where('tenant_id', $tenant->id)->exists()) {
                return back()->with('error', 'Bu e-posta zaten bu sitenin ekibinde.');
            }

            $this->assertQuota($tenant);
            $this->grantAccess($existing, $tenant, isDefault: false);
            $this->applyRole($existing, $request->input('role'));

            return back()->with('success', "«{$existing->name}» ekibe eklendi (mevcut hesaba site erişimi verildi).");
        }

        // Yeni hesap — ad + şifre zorunlu
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $this->assertQuota($tenant);

        $admin = Admin::create([
            'name'     => $data['name'],
            'email'    => $request->input('email'),
            'username' => $this->uniqueUsername($request->input('email')),
            'password' => $data['password'],
        ]);

        $this->grantAccess($admin, $tenant, isDefault: true);
        $this->applyRole($admin, $request->input('role'));

        return back()->with('success', "«{$admin->name}» oluşturuldu ve ekibe eklendi.");
    }

    public function update(Request $request, Admin $admin)
    {
        $tenant = $this->activeOwnedTenant();

        $request->validate([
            'role' => ['nullable', Rule::in($this->assignableRoles()->pluck('name')->all())],
        ]);

        $access = AdminTenantAccess::where('admin_id', $admin->id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        if ($access->role === 'owner') {
            return back()->with('error', 'Site sahibinin yetkisi buradan değiştirilemez.');
        }

        $this->applyRole($admin, $request->input('role'));

        return back()->with('success', "«{$admin->name}» yetkisi güncellendi.");
    }

    public function destroy(Admin $admin)
    {
        $tenant = $this->activeOwnedTenant();

        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'Kendinizi ekipten çıkaramazsınız.');
        }

        $access = AdminTenantAccess::where('admin_id', $admin->id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        if ($access->role === 'owner') {
            return back()->with('error', 'Site sahibi ekipten çıkarılamaz.');
        }

        $access->delete();

        // Kritik: başka tenant erişimi YOKSA ve super-admin DEĞİLSE hesabı sil —
        // aksi halde tenantAccesses boşalır → isAgencyAdmin()=true → tüm platform.
        if (! $admin->hasRole('super-admin') && ! $admin->tenantAccesses()->exists()) {
            $admin->delete();

            return back()->with('success', "«{$admin->name}» ekipten çıkarıldı ve hesabı kapatıldı (başka site erişimi yoktu).");
        }

        return back()->with('success', "«{$admin->name}» bu siteden çıkarıldı.");
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** Müşteri (owner) tarafından atanabilir tenant-güvenli roller. */
    private function assignableRoles(): Collection
    {
        return AdminRole::where('guard_name', 'admin')
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->filter(fn (AdminRole $role) => AdminPermissions::isTenantAssignable($role))
            ->values();
    }

    /** Üyeye global Spatie rolünü uygular (boşsa rol yok = tam tenant yetkisi). */
    private function applyRole(Admin $admin, ?string $roleName): void
    {
        $admin->syncRoles($roleName ? [$roleName] : []);
    }

    private function grantAccess(Admin $admin, Tenant $tenant, bool $isDefault): void
    {
        AdminTenantAccess::create([
            'admin_id'   => $admin->id,
            'tenant_id'  => $tenant->id,
            'role'       => 'manager', // pivot: owner-dışı erişim seviyesi (gerçek yetki Spatie rolünde)
            'is_default' => $isDefault,
        ]);
    }

    /**
     * Aktif tenant'ı döndürür; yalnızca o tenant'ın owner'ı (veya agency admin)
     * geçebilir. Aksi halde 403/404.
     */
    private function activeOwnedTenant(): Tenant
    {
        $admin    = auth('admin')->user();
        $tenantId = session('active_tenant');

        abort_unless($tenantId, 403, 'Önce bir site seçin.');

        $tenant = Tenant::find($tenantId);
        abort_unless($tenant, 404);

        abort_unless(
            $admin->isAgencyAdmin() || $admin->ownsTenant($tenantId),
            403,
            'Ekip yönetimi için site sahibi olmalısınız.'
        );

        return $tenant;
    }

    private function assertQuota(Tenant $tenant): void
    {
        $max = $tenant->maxAdminUsers();
        if ($max === null) {
            return;
        }

        $current = AdminTenantAccess::where('tenant_id', $tenant->id)->distinct()->count('admin_id');
        if ($current + 1 > $max) {
            throw ValidationException::withMessages([
                'email' => "Paket en fazla {$max} yönetici kullanıcıya izin veriyor (şu an {$current}). Paketi yükseltmek için ajansınıza başvurun.",
            ]);
        }
    }

    private function uniqueUsername(string $email): string
    {
        $base = Str::slug(Str::before($email, '@'), '_') ?: 'user';
        $username = $base;
        $i = 2;
        while (Admin::withTrashed()->where('username', $username)->exists()) {
            $username = $base.'_'.$i++;
        }

        return $username;
    }
}
