<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\SectionTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Block şablonu versiyon davranışı: "Geri Yükle" içerik aynıysa yeni geri dönüş
 * noktası OLUŞTURMAMALI (kullanıcı raporu). Farklıysa restore + pre-restore.
 */
class SectionTemplateVersionTest extends TestCase
{
    use RefreshDatabase;

    protected $connectionsToTransact = ['central'];

    public function test_edit_page_renders_without_error(): void
    {
        $admin = Admin::factory()->create(); // ajans admin
        $tpl = SectionTemplate::factory()->create();

        $this->actingAs($admin, 'admin')
            ->withoutExceptionHandling()
            ->get(route('admin.section-templates.edit', $tpl))
            ->assertOk();
    }

    public function test_restore_to_identical_content_creates_no_new_version(): void
    {
        $admin = Admin::factory()->create(); // tenant ataması yok = ajans admin → küresel katalog yazabilir
        $tpl = SectionTemplate::factory()->create([
            'html_template'        => '<div>{{title}}</div>',
            'schema_json'          => ['title' => ['type' => 'text', 'label' => 'Title']],
            'default_content_json' => ['title' => 'Merhaba'],
        ]);
        $version = $tpl->recordVersion('manual'); // mevcut (birebir aynı) içeriğin snapshot'ı
        $countBefore = $tpl->versions()->count();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.section-templates.restore-version', [$tpl, $version]))
            ->assertRedirect();

        $this->assertSame($countBefore, $tpl->fresh()->versions()->count(),
            'Aynı içeriğe geri yüklemede yeni versiyon oluşmamalı.');
    }

    public function test_restore_to_different_content_restores_and_snapshots(): void
    {
        $admin = Admin::factory()->create();
        $tpl = SectionTemplate::factory()->create([
            'html_template'        => '<div>ESKI</div>',
            'schema_json'          => [],
            'default_content_json' => [],
        ]);
        $old = $tpl->recordVersion('manual');          // '<div>ESKI</div>' snapshot
        $tpl->update(['html_template' => '<div>YENI</div>']); // canlı içerik değişti
        $countBefore = $tpl->versions()->count();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.section-templates.restore-version', [$tpl, $old]))
            ->assertRedirect();

        $tpl->refresh();
        $this->assertSame('<div>ESKI</div>', $tpl->html_template, 'İçerik geri yüklenmeli.');
        $this->assertSame($countBefore + 1, $tpl->versions()->count(),
            'Farklı içerikte pre-restore snapshot oluşmalı.');
    }
}
