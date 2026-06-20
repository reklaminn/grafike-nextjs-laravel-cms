<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\AdminTenantAccess;
use App\Models\Tenant;
use App\Support\AdminPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Granüler izin enforcement modeli (Gate::before + AdminPermissions).
 * Çekirdek kural: yalnızca ROL ATANMIŞ + owner olmayan teammate kısıtlanır;
 * rolsüz/ajans/owner her zaman tam erişir (geriye uyumlu).
 */
class AdminPermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    // Tüm yazımlar 'central' bağlantısında (Admin/AdminRole/Tenant…). central'ı
    // transaction'a alırsak: (1) shared in-memory DB'de default+central çift
    // transaction kilidi olmaz, (2) yazımlar test sonrası roll-back ile temizlenir.
    protected $connectionsToTransact = ['central'];

    protected function setUp(): void
    {
        parent::setUp();
        AdminPermissions::ensure();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeTenant(string $id): Tenant
    {
        DB::connection('central')->table('tenants')->where('id', $id)->delete();
        DB::connection('central')->table('admin_tenant_access')->where('tenant_id', $id)->delete();
        DB::connection('central')->table('tenants')->insert([
            'id' => $id, 'data' => json_encode([]), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return Tenant::query()->findOrFail($id);
    }

    public function test_ability_for_route_mapping(): void
    {
        $this->assertSame('pages.view',     AdminPermissions::abilityForRoute('admin.pages.index', 'GET'));
        $this->assertSame('pages.create',   AdminPermissions::abilityForRoute('admin.pages.store', 'POST'));
        $this->assertSame('pages.edit',     AdminPermissions::abilityForRoute('admin.pages.update', 'PUT'));
        $this->assertSame('pages.delete',   AdminPermissions::abilityForRoute('admin.pages.destroy', 'DELETE'));
        $this->assertSame('settings.edit',  AdminPermissions::abilityForRoute('admin.settings.update', 'PUT'));
        // enforced OLMAYAN / agency / utility → null
        $this->assertNull(AdminPermissions::abilityForRoute('admin.tenants.index', 'GET'));
        $this->assertNull(AdminPermissions::abilityForRoute('admin.section-templates.index', 'GET'));
        $this->assertNull(AdminPermissions::abilityForRoute('admin.dashboard', 'GET'));
        $this->assertNull(AdminPermissions::abilityForRoute(null, 'GET'));
    }

    public function test_role_less_teammate_has_full_access(): void
    {
        $tenant = $this->makeTenant('perm-roleless');
        $member = Admin::factory()->create();
        AdminTenantAccess::create(['admin_id' => $member->id, 'tenant_id' => $tenant->id, 'role' => 'manager', 'is_default' => true]);

        // Rol yok → Gate::before bypass → tam erişim (eski davranış korunur).
        $this->assertTrue($member->can('pages.edit'));
        $this->assertTrue($member->can('settings.edit'));
        $this->assertTrue($member->can('members.delete'));
    }

    public function test_role_assigned_teammate_is_restricted(): void
    {
        $tenant = $this->makeTenant('perm-restricted');

        $role = AdminRole::create(['name' => 'icerik-goruntuleyici', 'guard_name' => 'admin']);
        $role->givePermissionTo('pages.view');

        $member = Admin::factory()->create();
        AdminTenantAccess::create(['admin_id' => $member->id, 'tenant_id' => $tenant->id, 'role' => 'manager', 'is_default' => true]);
        $member->assignRole('icerik-goruntuleyici');

        $this->assertFalse($member->isAgencyAdmin(), 'tenant erişimi var + super-admin değil → agency DEĞİL');
        $this->assertTrue($member->fresh()->can('pages.view'),  'rolünde var');
        $this->assertFalse($member->fresh()->can('pages.edit'), 'rolünde yok → reddedilmeli');
        $this->assertFalse($member->fresh()->can('articles.view'));
    }

    public function test_tenant_assignable_role_filter(): void
    {
        // super-admin → atanamaz
        $super = AdminRole::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'admin']);
        $this->assertFalse(AdminPermissions::isTenantAssignable($super->load('permissions')));

        // ajans-seviyesi izinli rol → atanamaz
        $agency = AdminRole::create(['name' => 'site-yoneticisi', 'guard_name' => 'admin']);
        $agency->givePermissionTo('tenants.create');
        $this->assertFalse(AdminPermissions::isTenantAssignable($agency->fresh()->load('permissions')));

        // yalnızca tenant-içerik izinli rol → atanabilir
        $editor = AdminRole::create(['name' => 'editor-rolu', 'guard_name' => 'admin']);
        $editor->givePermissionTo(['pages.edit', 'media.upload']);
        $this->assertTrue(AdminPermissions::isTenantAssignable($editor->fresh()->load('permissions')));
    }
}
