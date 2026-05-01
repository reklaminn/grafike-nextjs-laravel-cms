<?php

use App\Models\Admin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $guard = 'admin';

    public function up(): void
    {
        $connection = DB::connection('central');
        $now = now();

        if (! Schema::connection('central')->hasTable('roles') || ! Schema::connection('central')->hasTable('permissions')) {
            return;
        }

        $permissions = $this->permissions();

        foreach ($permissions as $permission) {
            $connection->table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => $this->guard],
                ['updated_at' => $now, 'created_at' => $now],
            );
        }

        foreach ($this->roles() as $role => $rolePermissions) {
            $connection->table('roles')->updateOrInsert(
                ['name' => $role, 'guard_name' => $this->guard],
                ['updated_at' => $now, 'created_at' => $now],
            );

            $roleId = $connection->table('roles')
                ->where('name', $role)
                ->where('guard_name', $this->guard)
                ->value('id');

            $permissionIds = $connection->table('permissions')
                ->where('guard_name', $this->guard)
                ->whereIn('name', $rolePermissions)
                ->pluck('id');

            foreach ($permissionIds as $permissionId) {
                $connection->table('role_has_permissions')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        $this->assignFirstAdminAsSuperAdmin();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep role/permission data intact. These records may be edited in production.
    }

    private function permissionGroups(): array
    {
        return [
            'tenants' => ['view', 'create', 'edit', 'delete', 'switch', 'provision'],
            'pages' => ['view', 'create', 'edit', 'delete'],
            'articles' => ['view', 'create', 'edit', 'delete'],
            'menus' => ['view', 'create', 'edit', 'delete'],
            'forms' => ['view', 'create', 'edit', 'delete'],
            'media' => ['view', 'upload', 'edit', 'delete'],
            'seo' => ['view', 'edit', 'delete'],
            'redirects' => ['view', 'create', 'edit', 'delete'],
            'reviews' => ['view', 'edit', 'delete'],
            'members' => ['view', 'create', 'edit', 'delete'],
            'languages' => ['view', 'create', 'edit', 'delete'],
            'settings' => ['view', 'edit'],
            'admins' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'create', 'edit', 'delete'],
            'design' => ['view', 'edit'],
            'maintenance' => ['view', 'execute'],
        ];
    }

    private function permissions(): array
    {
        $permissions = [];

        foreach ($this->permissionGroups() as $group => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "{$group}.{$action}";
            }
        }

        return $permissions;
    }

    private function roles(): array
    {
        $all = $this->permissions();
        $companyManager = $this->only([
            'pages',
            'articles',
            'menus',
            'forms',
            'media',
            'seo',
            'redirects',
            'reviews',
            'members',
            'languages',
            'settings',
            'design',
        ]);

        $companyEditor = [
            'pages.view',
            'pages.create',
            'pages.edit',
            'articles.view',
            'articles.create',
            'articles.edit',
            'menus.view',
            'forms.view',
            'media.view',
            'media.upload',
            'media.edit',
            'seo.view',
            'seo.edit',
            'redirects.view',
            'reviews.view',
            'reviews.edit',
            'settings.view',
            'design.view',
        ];

        return [
            'super-admin' => $all,
            'agency-admin' => $all,
            'company-owner' => $companyManager,
            'company-manager' => $companyManager,
            'company-editor' => $companyEditor,
        ];
    }

    private function only(array $groups): array
    {
        $permissions = [];

        foreach ($this->permissionGroups() as $group => $actions) {
            if (! in_array($group, $groups, true)) {
                continue;
            }

            foreach ($actions as $action) {
                $permissions[] = "{$group}.{$action}";
            }
        }

        return $permissions;
    }

    private function assignFirstAdminAsSuperAdmin(): void
    {
        $connection = DB::connection('central');
        $adminId = $connection->table('admins')->orderBy('id')->value('id');

        if (! $adminId) {
            return;
        }

        $roleId = $connection->table('roles')
            ->where('name', 'super-admin')
            ->where('guard_name', $this->guard)
            ->value('id');

        if (! $roleId) {
            return;
        }

        $hasAnyAdminRole = $connection->table('model_has_roles')
            ->where('model_type', Admin::class)
            ->exists();

        if ($hasAnyAdminRole) {
            return;
        }

        $connection->table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_type' => Admin::class,
            'model_id' => $adminId,
        ]);
    }
};
