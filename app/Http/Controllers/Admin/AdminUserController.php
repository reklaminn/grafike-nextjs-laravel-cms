<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminRole as Role;
use App\Models\AdminTenantAccess;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::with('roles')->withTrashed(false);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admins = $query->orderBy('name')->paginate(25);

        return view('admin.admin-users.index', compact('admins'));
    }

    public function create()
    {
        $roles = Role::where('guard_name', 'admin')->orderBy('name')->get();
        $tenants = Tenant::orderBy('id')->get();

        return view('admin.admin-users.create', compact('roles', 'tenants'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('central.admins', 'username')],
            'email' => ['required', 'email', 'max:255', Rule::unique('central.admins', 'email')],
            'password' => 'required|string|min:6|confirmed',
            'role' => ['nullable', 'string', Rule::exists('central.roles', 'name')->where('guard_name', 'admin')],
            'tenant_ids' => 'nullable|array',
            'tenant_ids.*' => ['string', Rule::exists('central.tenants', 'id')],
            'tenant_access_role' => 'nullable|in:owner,manager,editor',
            'default_tenant_id' => ['nullable', 'string', Rule::exists('central.tenants', 'id')],
        ]);

        $admin = Admin::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        if (!empty($data['role'])) {
            $admin->assignRole($data['role']);
        }

        $this->syncTenantAccess($admin, $data);

        return redirect()
            ->route('admin.admin-users.index')
            ->with('success', 'Yönetici başarıyla oluşturuldu.');
    }

    public function edit(Admin $admin_user)
    {
        $roles = Role::where('guard_name', 'admin')->orderBy('name')->get();
        $tenants = Tenant::orderBy('id')->get();
        $admin_user->load(['roles', 'tenantAccesses']);

        return view('admin.admin-users.edit', compact('admin_user', 'roles', 'tenants'));
    }

    public function update(Request $request, Admin $admin_user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('central.admins', 'username')->ignore($admin_user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('central.admins', 'email')->ignore($admin_user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => ['nullable', 'string', Rule::exists('central.roles', 'name')->where('guard_name', 'admin')],
            'tenant_ids' => 'nullable|array',
            'tenant_ids.*' => ['string', Rule::exists('central.tenants', 'id')],
            'tenant_access_role' => 'nullable|in:owner,manager,editor',
            'default_tenant_id' => ['nullable', 'string', Rule::exists('central.tenants', 'id')],
        ]);

        $admin_user->name = $data['name'];
        $admin_user->username = $data['username'];
        $admin_user->email = $data['email'];

        if (!empty($data['password'])) {
            $admin_user->password = $data['password'];
        }

        $admin_user->save();

        // Sync role
        $admin_user->syncRoles($data['role'] ? [$data['role']] : []);
        $this->syncTenantAccess($admin_user, $data);

        return redirect()
            ->route('admin.admin-users.index')
            ->with('success', 'Yönetici başarıyla güncellendi.');
    }

    public function destroy(Admin $admin_user)
    {
        // Prevent self-deletion
        if ($admin_user->id === auth('admin')->id()) {
            return back()->with('error', 'Kendi hesabınızı silemezsiniz.');
        }

        $admin_user->delete();

        return redirect()
            ->route('admin.admin-users.index')
            ->with('success', 'Yönetici başarıyla silindi.');
    }

    public function toggleStatus(Admin $admin_user)
    {
        if ($admin_user->id === auth('admin')->id()) {
            return back()->with('error', 'Kendi hesabınızı devre dışı bırakamazsınız.');
        }

        if ($admin_user->trashed()) {
            $admin_user->restore();
            $message = 'Yönetici aktif edildi.';
        } else {
            $admin_user->delete();
            $message = 'Yönetici devre dışı bırakıldı.';
        }

        return back()->with('success', $message);
    }

    private function syncTenantAccess(Admin $admin, array $data): void
    {
        $tenantIds = collect($data['tenant_ids'] ?? [])
            ->filter()
            ->unique()
            ->values();

        AdminTenantAccess::where('admin_id', $admin->id)->delete();

        if ($tenantIds->isEmpty()) {
            return;
        }

        $defaultTenantId = $data['default_tenant_id'] ?? $tenantIds->first();
        if (! $tenantIds->contains($defaultTenantId)) {
            $defaultTenantId = $tenantIds->first();
        }

        foreach ($tenantIds as $tenantId) {
            AdminTenantAccess::create([
                'admin_id' => $admin->id,
                'tenant_id' => $tenantId,
                'role' => $data['tenant_access_role'] ?? 'manager',
                'is_default' => $tenantId === $defaultTenantId,
            ]);
        }
    }
}
