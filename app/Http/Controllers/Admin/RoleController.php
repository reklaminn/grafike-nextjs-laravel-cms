<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminPermission as Permission;
use App\Models\AdminRole as Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    protected function getPermissionGroups(): array
    {
        return [
            'pages' => ['label' => 'Sayfalar', 'actions' => ['view', 'create', 'edit', 'delete']],
            'articles' => ['label' => 'Yazılar', 'actions' => ['view', 'create', 'edit', 'delete']],
            'menus' => ['label' => 'Menüler', 'actions' => ['view', 'create', 'edit', 'delete']],
            'forms' => ['label' => 'Formlar', 'actions' => ['view', 'create', 'edit', 'delete']],
            'media' => ['label' => 'Medya', 'actions' => ['view', 'upload', 'edit', 'delete']],
            'seo' => ['label' => 'SEO', 'actions' => ['view', 'edit', 'delete']],
            'redirects' => ['label' => 'Yönlendirmeler', 'actions' => ['view', 'create', 'edit', 'delete']],
            'reviews' => ['label' => 'Yorumlar', 'actions' => ['view', 'edit', 'delete']],
            'members' => ['label' => 'Üyeler', 'actions' => ['view', 'create', 'edit', 'delete']],
            'languages' => ['label' => 'Diller', 'actions' => ['view', 'create', 'edit', 'delete']],
            'settings' => ['label' => 'Ayarlar', 'actions' => ['view', 'edit']],
            'admins' => ['label' => 'Yöneticiler', 'actions' => ['view', 'create', 'edit', 'delete']],
            'roles' => ['label' => 'Roller', 'actions' => ['view', 'create', 'edit', 'delete']],
            'design' => ['label' => 'Tasarım (CSS/JS)', 'actions' => ['view', 'edit']],
            'maintenance' => ['label' => 'Bakım', 'actions' => ['view', 'execute']],
        ];
    }

    public function index()
    {
        $roles = Role::where('guard_name', 'admin')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $adminCounts = DB::connection('central')
            ->table(config('permission.table_names.model_has_roles'))
            ->selectRaw('role_id, count(*) as total')
            ->where('model_type', Admin::class)
            ->groupBy('role_id')
            ->pluck('total', 'role_id');

        $roles->each(function (Role $role) use ($adminCounts) {
            $role->users_count = (int) ($adminCounts[$role->id] ?? 0);
        });

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissionGroups = $this->getPermissionGroups();

        // Ensure all permissions exist
        $this->ensurePermissionsExist($permissionGroups);

        $allPermissions = Permission::where('guard_name', 'admin')->orderBy('name')->get();

        return view('admin.roles.create', compact('permissionGroups', 'allPermissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('central.roles', 'name')->where('guard_name', 'admin')],
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::exists('central.permissions', 'name')->where('guard_name', 'admin')],
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'admin',
        ]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Rol başarıyla oluşturuldu.');
    }

    public function edit(Role $role)
    {
        $permissionGroups = $this->getPermissionGroups();
        $this->ensurePermissionsExist($permissionGroups);

        $allPermissions = Permission::where('guard_name', 'admin')->orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'permissionGroups', 'allPermissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('central.roles', 'name')->where('guard_name', 'admin')->ignore($role->id)],
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::exists('central.permissions', 'name')->where('guard_name', 'admin')],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Rol başarıyla güncellendi.');
    }

    public function destroy(Role $role)
    {
        $adminCount = DB::connection('central')
            ->table(config('permission.table_names.model_has_roles'))
            ->where('role_id', $role->id)
            ->where('model_type', Admin::class)
            ->count();

        if ($adminCount > 0) {
            return back()->with('error', 'Bu role atanmış kullanıcılar var. Önce kullanıcıların rollerini değiştirin.');
        }

        if ($role->name === 'super-admin') {
            return back()->with('error', 'Super Admin rolü silinemez.');
        }

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Rol başarıyla silindi.');
    }

    protected function ensurePermissionsExist(array $groups): void
    {
        foreach ($groups as $group => $config) {
            foreach ($config['actions'] as $action) {
                Permission::firstOrCreate([
                    'name' => "{$group}.{$action}",
                    'guard_name' => 'admin',
                ]);
            }
        }
    }
}
