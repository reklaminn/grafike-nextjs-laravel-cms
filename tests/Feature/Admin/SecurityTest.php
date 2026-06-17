<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\AdminTenantAccess;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Faz A güvenlik regresyon testleri.
 *
 * - Admin login brute-force throttle
 * - Tenant izolasyonu: tenant admin başka tenant'a geçemez (IDOR)
 * - SVG upload sanitization (stored XSS)
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // throttle middleware'i RateLimiter cache'ini kullanır; testler
        // arasında sayaç sızmasın
        RateLimiter::clear('login');
    }

    /**
     * stancl'ın CreateDatabase pipeline'ını tetiklemeden tenants satırı ekle.
     *
     * Not: central bağlantısı RefreshDatabase transaction'ının dışında
     * kaldığından satırlar testler arası kalıcı olur — önce temizle.
     */
    private function makeTenant(string $id): Tenant
    {
        DB::connection('central')->table('tenants')->where('id', $id)->delete();
        DB::connection('central')->table('admin_tenant_access')->where('tenant_id', $id)->delete();

        DB::connection('central')->table('tenants')->insert([
            'id'         => $id,
            'data'       => json_encode([], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Tenant::query()->findOrFail($id);
    }

    // ── Rate limiting ────────────────────────────────────────────────────

    public function test_admin_login_is_rate_limited_after_five_attempts(): void
    {
        Admin::factory()->create(['username' => 'victim', 'password' => 'correct-password']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.submit'), [
                'username' => 'victim',
                'password' => 'wrong-password-' . $i,
            ]);
        }

        $response = $this->post(route('admin.login.submit'), [
            'username' => 'victim',
            'password' => 'wrong-password-6',
        ]);

        $response->assertStatus(429);
    }

    // ── Tenant izolasyonu (IDOR) ─────────────────────────────────────────

    public function test_tenant_admin_cannot_switch_to_unassigned_tenant(): void
    {
        $tenantA = $this->makeTenant('tenant-a');
        $tenantB = $this->makeTenant('tenant-b');

        $admin = Admin::factory()->create();
        AdminTenantAccess::create([
            'admin_id'   => $admin->id,
            'tenant_id'  => $tenantA->id,
            'is_default' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.tenants.switch', $tenantB));

        $response->assertStatus(403);
        $this->assertNotSame($tenantB->id, session('active_tenant'));
    }

    public function test_tenant_admin_can_switch_to_assigned_tenant(): void
    {
        $tenantA = $this->makeTenant('tenant-a');

        $admin = Admin::factory()->create();
        AdminTenantAccess::create([
            'admin_id'   => $admin->id,
            'tenant_id'  => $tenantA->id,
            'is_default' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.tenants.switch', $tenantA));

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertSame($tenantA->id, session('active_tenant'));
    }

    public function test_agency_admin_can_switch_to_any_tenant(): void
    {
        $tenantA = $this->makeTenant('tenant-a');

        // Hiç tenant ataması olmayan admin = agency admin (isAgencyAdmin)
        $agencyAdmin = Admin::factory()->create();

        $response = $this->actingAs($agencyAdmin, 'admin')
            ->post(route('admin.tenants.switch', $tenantA));

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertSame($tenantA->id, session('active_tenant'));
    }

    public function test_guest_cannot_access_admin_pages(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.media.index'))->assertRedirect(route('admin.login'));
    }

    // ── SVG sanitization (stored XSS) ────────────────────────────────────

    public function test_svg_upload_strips_embedded_script(): void
    {
        $admin = Admin::factory()->create();

        $maliciousSvg = <<<'SVG'
        <?xml version="1.0"?>
        <svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)">
            <script>alert('xss')</script>
            <circle cx="50" cy="50" r="40"/>
        </svg>
        SVG;

        $file = UploadedFile::fake()->createWithContent('logo.svg', $maliciousSvg);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.media.upload'), ['file' => $file]);

        $response->assertOk();

        // Upload artık Spatie Media kaydı yapıyor; sanitize edilmiş içerik
        // medyanın kendi diskinde saklanır. Diske bağımlı kalmadan oku.
        $path = $response->json('path');
        $disk = $response->json('disk');
        $this->assertNotNull($path);
        $this->assertNotNull($disk);

        $stored = \Illuminate\Support\Facades\Storage::disk($disk)->get($path);
        $this->assertStringNotContainsString('<script', $stored);
        $this->assertStringNotContainsString('onload', $stored);
        $this->assertStringContainsString('<circle', $stored);
    }
}
