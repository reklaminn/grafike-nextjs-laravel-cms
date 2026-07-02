<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\MediaAsset;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;
use Tests\TestCase;

/**
 * Medya "dosya adını yeniden adlandır" (slug): dosyayı diskte taşır + file_name'i
 * slug'lar + bu sitenin içeriklerinde eski URL'i yenisiyle değiştirir (link kırılmaz).
 */
class MediaRenameTest extends TestCase
{
    use RefreshDatabase;

    public function test_rename_slugs_filename_moves_file_and_updates_references(): void
    {
        $disk = config('media-library.disk_name', 'public');
        Storage::fake($disk);
        // Tenant URL generator yerine default (test tenant'sız) — getUrl() düz URL döner.
        config(['media-library.url_generator' => DefaultUrlGenerator::class]);

        $admin = Admin::factory()->create();

        $asset = MediaAsset::create(['name' => 'Ücretsiz Bina Etüdü']);
        $media = $asset->addMedia(UploadedFile::fake()->image('x.jpg', 80, 80))
            ->usingName('Ücretsiz Bina Etüdü')
            ->usingFileName('Ücretsiz-Bina-Etüdü.jpg')
            ->toMediaCollection('library');

        $oldUrl      = $media->getUrl();
        $oldRelative = $media->getPathRelativeToRoot();
        $this->assertTrue(Storage::disk($disk)->exists($oldRelative));

        // Bu görseli kullanan bir sayfa (blok içeriğinde URL)
        $page = Page::create([
            'title'         => 'Test Sayfa',
            'slug'          => 'rename-test-' . uniqid(),
            'status'        => 'draft',
            'sections_json' => ['regions' => ['main' => [['columns' => [['blocks' => [['content' => ['image' => $oldUrl]]]]]]]]],
        ]);

        $res = $this->actingAs($admin, 'admin')
            ->putJson(route('admin.media.rename', $media), ['name' => 'Ücretsiz Bina Etüdü']);

        $res->assertOk()->assertJson(['success' => true, 'renamed' => true]);
        $this->assertSame('ucretsiz-bina-etudu.jpg', $res->json('file_name'));
        $this->assertSame(1, $res->json('refs_updated'));

        // Dosya taşındı: yeni var, eski yok
        $media->refresh();
        $this->assertSame('ucretsiz-bina-etudu.jpg', $media->file_name);
        $this->assertTrue(Storage::disk($disk)->exists($media->getPathRelativeToRoot()));
        $this->assertFalse(Storage::disk($disk)->exists($oldRelative));

        // Sayfa referansı güncellendi
        $page->refresh();
        $newUrl = $res->json('url');
        $this->assertSame($newUrl, $page->sections_json['regions']['main'][0]['columns'][0]['blocks'][0]['content']['image']);
        $this->assertStringNotContainsString($oldUrl, json_encode($page->sections_json));
    }
}
