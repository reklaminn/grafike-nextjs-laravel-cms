<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminTenantAccess;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Tenant'a özel "Ekip / Yöneticiler" — site SAHİBİ (owner) kendi sitesine
 * manager/editor ekler. Ajans/superadmin'in global "Yöneticiler" sayfasından
 * (agency.admin) FARKLI: yalnızca AKTİF tenant kapsamında çalışır.
 *
 * Güvenlik:
 *  - Sadece aktif tenant'ın owner'ı (veya agency admin) erişir.
 *  - Atanabilir roller yalnızca manager/editor — owner ASLA (yetki yükseltme).
 *  - Hiçbir global rol (super-admin) atanmaz.
 *  - Çıkarma: admin'in BAŞKA tenant erişimi kalmıyor ve super-admin değilse
 *    admin tamamen silinir — yoksa tenantAccesses boşalıp isAgencyAdmin()=true
 *    olur ve TÜM platforma erişim kazanır (kritik!).
 */
class TenantTeamController extends Controller
{
    /** Atanabilir roller — owner kasıtlı olarak DIŞARIDA. */
    private const ASSIGNABLE_ROLES = ['manager', 'editor'];

    public function index()
    {
        $tenant = $this->activeOwnedTenant();

        $members = AdminTenantAccess::with('admin')
            ->where('tenant_id', $tenant->id)
            ->get()
            ->filter(fn (AdminTenantAccess $a) => $a->admin !== null)
            ->sortBy(fn (AdminTenantAccess $a) => [$a->role === 'owner' ? 0 : 1, $a->admin->name])
            ->values();

        $max     = $tenant->maxAdminUsers();
        $current = $members->count();

        return view('admin.team.index', compact('tenant', 'members', 'max', 'current'));
    }

    public function store(Request $request)
    {
        $tenant = $this->activeOwnedTenant();

        $request->validate([
            'email' => 'required|email|max:255',
            'role'  => ['required', Rule::in(self::ASSIGNABLE_ROLES)],
        ]);

        $existing = Admin::where('email', $request->input('email'))->first();

        if ($existing) {
            if (AdminTenantAccess::where('admin_id', $existing->id)->where('tenant_id', $tenant->id)->exists()) {
                return back()->with('error', 'Bu e-posta zaten bu sitenin ekibinde.');
            }

            $this->assertQuota($tenant);

            AdminTenantAccess::create([
                'admin_id'   => $existing->id,
                'tenant_id'  => $tenant->id,
                'role'       => $request->input('role'),
                'is_default' => false, // mevcut kullanıcının varsayılanını bozma
            ]);

            return back()->with('success', "«{$existing->name}» ekibe eklendi (mevcut hesaba site erişimi verildi).");
        }

        // Yeni yönetici — ad + şifre zorunlu
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
            // GLOBAL ROL ATANMAZ — yalnızca tenant erişimi
        ]);

        AdminTenantAccess::create([
            'admin_id'   => $admin->id,
            'tenant_id'  => $tenant->id,
            'role'       => $request->input('role'),
            'is_default' => true, // tek erişimi bu site
        ]);

        return back()->with('success', "«{$admin->name}» oluşturuldu ve ekibe eklendi.");
    }

    public function update(Request $request, Admin $admin)
    {
        $tenant = $this->activeOwnedTenant();

        $request->validate(['role' => ['required', Rule::in(self::ASSIGNABLE_ROLES)]]);

        $access = AdminTenantAccess::where('admin_id', $admin->id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        if ($access->role === 'owner') {
            return back()->with('error', 'Site sahibinin rolü buradan değiştirilemez.');
        }

        $access->update(['role' => $request->input('role')]);

        return back()->with('success', "«{$admin->name}» rolü güncellendi.");
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

        // Kritik: başka tenant erişimi YOKSA ve super-admin DEĞİLSE admin'i sil.
        // Aksi halde tenantAccesses boşalır → isAgencyAdmin()=true → tüm platforma
        // erişim kazanır.
        if (! $admin->hasRole('super-admin') && ! $admin->tenantAccesses()->exists()) {
            $admin->delete(); // soft delete
            return back()->with('success', "«{$admin->name}» ekipten çıkarıldı ve hesabı kapatıldı (başka site erişimi yoktu).");
        }

        return back()->with('success', "«{$admin->name}» bu siteden çıkarıldı.");
    }

    // ─────────────────────────────────────────────────────────────────────────

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
            return; // sınırsız paket
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
