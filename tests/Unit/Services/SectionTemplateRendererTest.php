<?php

namespace Tests\Unit\Services;

use App\Models\SectionTemplate;
use App\Services\SectionTemplate\SectionTemplateRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Renderer repeater alanlarını ({{{key_html}}}) item_template × items olarak
 * genişletmeli. Eskiden genişletmiyordu → {{{slides_html}}} boş kalıp slider
 * boş render oluyordu (kullanıcı raporu: koyu boş preview).
 */
class SectionTemplateRendererTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeater_field_is_expanded_into_html(): void
    {
        $tpl = new SectionTemplate([
            'html_template' => '<section class="x">{{{slides_html}}}</section>',
            'schema_json' => [
                'slides' => [
                    'type' => 'repeater',
                    'label' => 'Slaytlar',
                    'item_template' => '<div class="hs-slide"><div class="hs-bg" style="background-image:url({{{image}}})"></div><h2>{{title}}</h2><a href="{{button_url}}">{{button_text}}</a></div>',
                    'fields' => [],
                ],
            ],
            'default_content_json' => [
                'slides' => [
                    ['image' => '/img/a.jpg', 'title' => 'Slayt A', 'button_url' => '/a', 'button_text' => 'Git A'],
                    ['image' => '', 'title' => 'Slayt B', 'button_url' => '/b', 'button_text' => 'Git B'],
                ],
            ],
        ]);

        $out = app(SectionTemplateRenderer::class)->render($tpl);

        // İki slaytın metni de basılmalı
        $this->assertStringContainsString('Slayt A', $out);
        $this->assertStringContainsString('Slayt B', $out);
        // Linkler item bazında doğru
        $this->assertStringContainsString('href="/a"', $out);
        $this->assertStringContainsString('href="/b"', $out);
        // {{{image}}} raw olarak url() içine girer
        $this->assertStringContainsString('url(/img/a.jpg)', $out);
        // {{{slides_html}}} tüketildi — ham placeholder kalmadı
        $this->assertStringNotContainsString('slides_html', $out);
        $this->assertStringNotContainsString('{{title}}', $out);
    }
}
