<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Tenant;
use App\Services\Tenancy\TenantDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * /admin/tenants operasyon panosu — servis yapısı + ajans admin sayfası render.
 */
class TenantDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush(); // overview() 60 sn cache'li — testler arası sızmasın
    }

    private function makeTenant(string $id): Tenant
    {
        DB::connection('central')->table('tenants')->where('id', $id)->delete();
        DB::connection('central')->table('tenants')->insert([
            'id'         => $id,
            'data'       => json_encode(['name' => 'Dash ' . $id, 'status' => 'active'], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Tenant::query()->findOrFail($id);
    }

    public function test_dashboard_service_returns_expected_structure(): void
    {
        $this->makeTenant('svc-demo');

        $data = app(TenantDashboardService::class)->overview();

        foreach (['kpis', 'near_limit', 'top_requests', 'package_dist', 'status_dist', 'trend', 'ai_trend', 'quota_ext', 'recent', 'backup_alerts'] as $key) {
            $this->assertArrayHasKey($key, $data, "pano '{$key}' anahtarını içermeli");
        }
        $this->assertArrayHasKey('total', $data['kpis']);
        $this->assertGreaterThanOrEqual(1, $data['kpis']['total']);
        $this->assertCount(30, $data['trend']['labels'], 'trend 30 günlük seri olmalı');
        $this->assertCount(30, $data['trend']['requests']);

        DB::connection('central')->table('tenants')->where('id', 'svc-demo')->delete();
    }

    public function test_agency_admin_sees_operations_dashboard(): void
    {
        $this->makeTenant('dash-demo');
        $agency = Admin::factory()->create(); // tenant ataması yok = ajans admin

        $response = $this->actingAs($agency, 'admin')->get(route('admin.tenants.index'));

        $response->assertOk();
        $response->assertSee('Limite Yakın');
        $response->assertSee('İstek Trendi (30 gün)');
        $response->assertSee('Tüm Siteler');
        $response->assertSee('chart-req-trend', false); // grafik canvas'ı bağlandı

        DB::connection('central')->table('tenants')->where('id', 'dash-demo')->delete();
    }
}
