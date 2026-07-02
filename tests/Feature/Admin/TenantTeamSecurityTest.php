<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\AdminTenantAccess;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Tenant "Ekip / Yöneticiler" güvenlik değişmezleri.
 * TenantTeamController bu modeller üzerine kurulu; en kritik kural:
 * bir admin'in SON tenant erişimi silinirse isAgencyAdmin()=true olur
 * (tüm platforma erişim) — controller bu yüzden böyle durumda admin'i siler.
 */
class TenantTeamSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenant(string $id): Tenant
    {
        DB::connection('central')->table('tenants')->where('id', $id)->delete();
        DB::connection('central')->table('admin_tenant_access')->where('tenant_id', $id)->delete();
        DB::connection('central')->table('tenants')->insert([
            'id' => $id, 'data' => json_encode([]), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return Tenant::query()->findOrFail($id);
    }

    public function test_tenant_role_helpers(): void
    {
        $tenant = $this->makeTenant('team-helpers');

        $owner = Admin::factory()->create();
        AdminTenantAccess::create(['admin_id' => $owner->id, 'tenant_id' => $tenant->id, 'role' => 'owner', 'is_default' => true]);

        $member = Admin::factory()->create();
        AdminTenantAccess::create(['admin_id' => $member->id, 'tenant_id' => $tenant->id, 'role' => 'manager', 'is_default' => true]);

        $this->assertTrue($owner->ownsTenant($tenant->id));
        $this->assertTrue($owner->ownsTenant($tenant)); // model kabul eder
        $this->assertFalse($member->ownsTenant($tenant->id));
        $this->assertSame('manager', $member->tenantRole($tenant->id));
        $this->assertNull($member->tenantRole('baska-tenant'));
        $this->assertNull($member->tenantRole(null));
    }

    public function test_member_with_access_is_not_agency_admin_but_orphan_is(): void
    {
        $tenant = $this->makeTenant('team-orphan');

        $member = Admin::factory()->create();
        AdminTenantAccess::create(['admin_id' => $member->id, 'tenant_id' => $tenant->id, 'role' => 'editor', 'is_default' => true]);

        // Erişimi varken: agency admin DEĞİL (kapsamı tenant'ıyla sınırlı).
        $this->assertFalse($member->isAgencyAdmin());

        // KRİTİK: son tenant erişimi silinince → agency admin (tüm platform!).
        // Controller::destroy bu yüzden son erişimde admin'i siler.
        $member->tenantAccesses()->where('tenant_id', $tenant->id)->delete();

        $this->assertTrue(
            $member->fresh()->isAgencyAdmin(),
            'Son tenant erişimi silinince admin agency-admin olur — controller bu durumda admin\'i silmeli'
        );
    }
}
