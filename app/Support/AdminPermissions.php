<?php

namespace App\Support;

use App\Models\AdminRole;
use Spatie\Permission\Models\Permission;

/**
 * Admin paneli izinleri için TEK kaynak. Hem rol tanım UI'si (RoleController)
 * hem de enforcement (EnforceAdminPermission middleware + Gate) buradan okur.
 *
 * İzin adı formatı: "{group}.{action}" (ör. pages.edit).
 */
class AdminPermissions
{
    /**
     * Enforcement YALNIZCA tenant-içerik gruplarına uygulanır. tenants/admins/
     * roles/maintenance ajans-seviyesidir (zaten agency.admin middleware'i
     * korur; teammate'ler erişemez) → can-check yapılmaz.
     */
    public const ENFORCED_GROUPS = [
        'pages', 'articles', 'menus', 'forms', 'media', 'seo',
        'redirects', 'reviews', 'members', 'languages', 'settings', 'design',
    ];

    /** Ajans-seviyesi gruplar — bir rol bunlardan izin içeriyorsa müşteri (owner) ATAYAMAZ. */
    public const AGENCY_GROUPS = ['tenants', 'admins', 'roles', 'maintenance'];

    /** @return array<string, array{label:string, actions:array<int,string>}> */
    public static function groups(): array
    {
        return [
            'tenants'     => ['label' => 'Siteler',          'actions' => ['view', 'create', 'edit', 'delete', 'switch', 'provision']],
            'pages'       => ['label' => 'Sayfalar',         'actions' => ['view', 'create', 'edit', 'delete']],
            'articles'    => ['label' => 'Yazılar',          'actions' => ['view', 'create', 'edit', 'delete']],
            'menus'       => ['label' => 'Menüler',          'actions' => ['view', 'create', 'edit', 'delete']],
            'forms'       => ['label' => 'Formlar',          'actions' => ['view', 'create', 'edit', 'delete']],
            'media'       => ['label' => 'Medya',            'actions' => ['view', 'upload', 'edit', 'delete']],
            'seo'         => ['label' => 'SEO',              'actions' => ['view', 'edit', 'delete']],
            'redirects'   => ['label' => 'Yönlendirmeler',   'actions' => ['view', 'create', 'edit', 'delete']],
            'reviews'     => ['label' => 'Yorumlar',         'actions' => ['view', 'edit', 'delete']],
            'members'     => ['label' => 'Üyeler',           'actions' => ['view', 'create', 'edit', 'delete']],
            'languages'   => ['label' => 'Diller',           'actions' => ['view', 'create', 'edit', 'delete']],
            'settings'    => ['label' => 'Ayarlar',          'actions' => ['view', 'edit']],
            'admins'      => ['label' => 'Yöneticiler',      'actions' => ['view', 'create', 'edit', 'delete']],
            'roles'       => ['label' => 'Roller',           'actions' => ['view', 'create', 'edit', 'delete']],
            'design'      => ['label' => 'Tasarım (CSS/JS)', 'actions' => ['view', 'edit']],
            'maintenance' => ['label' => 'Bakım',            'actions' => ['view', 'execute']],
        ];
    }

    /** Tüm izin adları (düz liste). */
    public static function all(): array
    {
        $names = [];
        foreach (self::groups() as $group => $config) {
            foreach ($config['actions'] as $action) {
                $names[] = "{$group}.{$action}";
            }
        }

        return $names;
    }

    /** Eksik Permission kayıtlarını oluşturur (guard: admin). can() çalışsın diye. */
    public static function ensure(): void
    {
        foreach (self::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }
    }

    /**
     * Bir rol müşteri (site owner) tarafından ATANABİLİR mi? super-admin değil
     * VE ajans-seviyesi (tenants/admins/roles/maintenance) izin içermiyor.
     */
    public static function isTenantAssignable(AdminRole $role): bool
    {
        if ($role->name === 'super-admin') {
            return false;
        }

        foreach ($role->permissions as $perm) {
            $group = strtok((string) $perm->name, '.');
            if (in_array($group, self::AGENCY_GROUPS, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Route adından gerekli izin yeteneğini türetir (yalnızca enforced gruplar).
     * Ör: admin.pages.index → pages.view, admin.pages.update → pages.edit.
     * Eşleşmeyen/agency/utility route → null (kontrol yok).
     */
    public static function abilityForRoute(?string $routeName, string $method = 'GET'): ?string
    {
        if (! $routeName || ! str_starts_with($routeName, 'admin.')) {
            return null;
        }

        $parts = explode('.', substr($routeName, 6)); // "pages.index" → [pages, index]
        $group = $parts[0];

        if (! in_array($group, self::ENFORCED_GROUPS, true)) {
            return null;
        }

        $action  = end($parts);
        $allowed = self::groups()[$group]['actions'] ?? [];

        $perm = match ($action) {
            'index', 'show', 'list'              => 'view',
            'create', 'store'                    => 'create',
            'edit', 'update'                     => 'edit',
            'destroy'                            => 'delete',
            default => in_array(strtoupper($method), ['GET', 'HEAD'], true) ? 'view' : 'edit',
        };

        // Grup bu eylemi tanımlamıyorsa en yakına düş (edit varsa edit, yoksa view).
        if (! in_array($perm, $allowed, true)) {
            $perm = in_array('edit', $allowed, true) ? 'edit' : 'view';
        }

        return "{$group}.{$perm}";
    }
}
