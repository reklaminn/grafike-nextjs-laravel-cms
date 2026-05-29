<?php

namespace Database\Seeders;

use App\Models\SectionTemplate;
use App\Models\Theme;
use Illuminate\Database\Seeder;

/**
 * Neutral GLOBAL block library ("Genel — Temel") shared by every industry
 * SiteTemplate except tourism (which keeps its own turizm-* family referenced
 * by already-applied tenants).
 *
 * All rows: tenant_id = NULL (global). Self-contained HTML (inline styles +
 * CSS-var fallbacks) so blocks render styled before any theme CSS loads, and
 * adopt each tenant's design tokens (--color-primary / --color-accent /
 * --color-secondary).
 *
 * Idempotent — keyed by (theme_id, type, variation). Run:
 *   php artisan db:seed --class=GlobalBaseCatalogSeeder
 */
class GlobalBaseCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $theme = Theme::updateOrCreate(
            ['slug' => 'genel-temel'],
            [
                'tenant_id'   => null,
                'name'        => 'Genel — Temel',
                'engine'      => 'nextjs-basic-html',
                'description' => 'Tüm sektörler için nötr, yeniden kullanılabilir global blok kütüphanesi.',
                'assets_json' => ['css' => [], 'js' => []],
                'tokens_json' => [
                    'color_primary'   => '#4f46e5',
                    'color_secondary' => '#f8fafc',
                    'color_accent'    => '#0f172a',
                    'radius_card'     => '16px',
                    'radius_button'   => '999px',
                    'container_width' => '1200px',
                ],
                'settings_schema_json' => [],
                'is_active'   => true,
            ]
        );

        foreach ($this->blocks() as $block) {
            SectionTemplate::updateOrCreate(
                ['theme_id' => $theme->id, 'type' => $block['type'], 'variation' => $block['variation']],
                [
                    'tenant_id'            => null,
                    'name'                 => $block['name'],
                    'render_mode'          => 'html',
                    'html_template'        => $block['html'],
                    'schema_json'          => $block['schema'],
                    'default_content_json' => $block['content'],
                    'is_active'            => true,
                ]
            );
        }
    }

    /**
     * @return array<int, array{type:string, variation:string, name:string, html:string, schema:array, content:array}>
     */
    private function blocks(): array
    {
        return [
            // ── HERO ───────────────────────────────────────────────────────
            [
                'type' => 'hero', 'variation' => 'base-hero', 'name' => 'Genel / Hero',
                'html' => <<<'HTML'
<section style="padding:100px 0;background:linear-gradient(135deg,var(--color-primary,#4f46e5),var(--color-accent,#0f172a));color:#fff;text-align:center;">
  <div style="max-width:var(--container-width,1200px);margin:0 auto;padding:0 24px;">
    <p style="text-transform:uppercase;letter-spacing:3px;font-weight:600;opacity:.9;margin:0 0 14px;">{{eyebrow}}</p>
    <h1 style="font-size:clamp(32px,5vw,56px);line-height:1.1;margin:0 0 18px;">{{title}}</h1>
    <p style="font-size:19px;max-width:680px;margin:0 auto 30px;opacity:.95;line-height:1.6;">{{subtitle}}</p>
    <a href="{{button_url}}" style="display:inline-block;background:#fff;color:var(--color-primary,#4f46e5);padding:15px 36px;border-radius:var(--radius-button,999px);font-weight:700;text-decoration:none;box-shadow:0 8px 24px rgba(0,0,0,.18);">{{button_text}}</a>
  </div>
</section>
HTML,
                'schema' => [
                    'eyebrow' => ['type' => 'text'], 'title' => ['type' => 'text'], 'subtitle' => ['type' => 'textarea'],
                    'button_text' => ['type' => 'text'], 'button_url' => ['type' => 'text'],
                ],
                'content' => [
                    'eyebrow' => 'Hoş geldiniz', 'title' => 'Başlık buraya gelecek',
                    'subtitle' => 'Kısa ve etkileyici bir alt başlık.', 'button_text' => 'Daha Fazla', 'button_url' => '/hakkimizda',
                ],
            ],

            // ── FEATURES (3 kart) ──────────────────────────────────────────
            [
                'type' => 'features', 'variation' => 'base-features', 'name' => 'Genel / Özellik Kartları (3)',
                'html' => <<<'HTML'
<section style="padding:76px 0;background:var(--color-secondary,#f8fafc);">
  <div style="max-width:var(--container-width,1200px);margin:0 auto;padding:0 24px;text-align:center;">
    <h2 style="font-size:34px;margin:0 0 10px;color:#0f172a;">{{title}}</h2>
    <p style="color:#64748b;max-width:640px;margin:0 auto 44px;line-height:1.6;">{{description}}</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px;text-align:left;">
      <div style="background:#fff;border-radius:var(--radius-card,16px);padding:30px;box-shadow:0 6px 24px rgba(15,23,42,.06);">
        <div style="font-size:40px;line-height:1;margin-bottom:14px;">{{item1_icon}}</div>
        <h3 style="margin:0 0 8px;font-size:20px;color:#0f172a;">{{item1_title}}</h3>
        <p style="margin:0;color:#64748b;line-height:1.65;">{{item1_text}}</p>
      </div>
      <div style="background:#fff;border-radius:var(--radius-card,16px);padding:30px;box-shadow:0 6px 24px rgba(15,23,42,.06);">
        <div style="font-size:40px;line-height:1;margin-bottom:14px;">{{item2_icon}}</div>
        <h3 style="margin:0 0 8px;font-size:20px;color:#0f172a;">{{item2_title}}</h3>
        <p style="margin:0;color:#64748b;line-height:1.65;">{{item2_text}}</p>
      </div>
      <div style="background:#fff;border-radius:var(--radius-card,16px);padding:30px;box-shadow:0 6px 24px rgba(15,23,42,.06);">
        <div style="font-size:40px;line-height:1;margin-bottom:14px;">{{item3_icon}}</div>
        <h3 style="margin:0 0 8px;font-size:20px;color:#0f172a;">{{item3_title}}</h3>
        <p style="margin:0;color:#64748b;line-height:1.65;">{{item3_text}}</p>
      </div>
    </div>
  </div>
</section>
HTML,
                'schema' => [
                    'title' => ['type' => 'text'], 'description' => ['type' => 'textarea'],
                    'item1_icon' => ['type' => 'text'], 'item1_title' => ['type' => 'text'], 'item1_text' => ['type' => 'textarea'],
                    'item2_icon' => ['type' => 'text'], 'item2_title' => ['type' => 'text'], 'item2_text' => ['type' => 'textarea'],
                    'item3_icon' => ['type' => 'text'], 'item3_title' => ['type' => 'text'], 'item3_text' => ['type' => 'textarea'],
                ],
                'content' => [
                    'title' => 'Hizmetlerimiz', 'description' => 'Sunduğumuz başlıca hizmetler.',
                    'item1_icon' => '⭐', 'item1_title' => 'Özellik 1', 'item1_text' => 'Kısa açıklama.',
                    'item2_icon' => '⚡', 'item2_title' => 'Özellik 2', 'item2_text' => 'Kısa açıklama.',
                    'item3_icon' => '🤝', 'item3_title' => 'Özellik 3', 'item3_text' => 'Kısa açıklama.',
                ],
            ],

            // ── RICH TEXT ──────────────────────────────────────────────────
            [
                'type' => 'rich-text', 'variation' => 'base-content', 'name' => 'Genel / Metin Bloğu',
                'html' => <<<'HTML'
<section style="padding:66px 0;">
  <div style="max-width:860px;margin:0 auto;padding:0 24px;">
    <h2 style="font-size:30px;margin:0 0 20px;color:#0f172a;">{{title}}</h2>
    <div style="color:#334155;line-height:1.85;font-size:17px;">{{{body_html}}}</div>
  </div>
</section>
HTML,
                'schema' => ['title' => ['type' => 'text'], 'body_html' => ['type' => 'textarea']],
                'content' => ['title' => 'Hakkımızda', 'body_html' => '<p>Buraya açıklayıcı metin gelecek.</p>'],
            ],

            // ── ARTICLE LIST / KARTLAR ─────────────────────────────────────
            [
                'type' => 'article-list', 'variation' => 'base-cards', 'name' => 'Genel / Kart Listesi',
                'html' => <<<'HTML'
<section style="padding:76px 0;background:var(--color-secondary,#f8fafc);">
  <div style="max-width:var(--container-width,1200px);margin:0 auto;padding:0 24px;">
    <h2 style="font-size:34px;margin:0 0 10px;text-align:center;color:#0f172a;">{{title}}</h2>
    <p style="text-align:center;color:#64748b;max-width:640px;margin:0 auto 44px;line-height:1.6;">{{description}}</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;">{{{items_html}}}</div>
  </div>
</section>
HTML,
                'schema' => ['title' => ['type' => 'text'], 'description' => ['type' => 'textarea'], 'items_html' => ['type' => 'textarea']],
                'content' => ['title' => 'Öne Çıkanlar', 'description' => 'Seçili içerikler.', 'items_html' => ''],
            ],

            // ── CTA ────────────────────────────────────────────────────────
            [
                'type' => 'cta', 'variation' => 'base-cta', 'name' => 'Genel / CTA',
                'html' => <<<'HTML'
<section style="padding:84px 0;background:var(--color-accent,#0f172a);color:#fff;text-align:center;">
  <div style="max-width:760px;margin:0 auto;padding:0 24px;">
    <h2 style="font-size:36px;margin:0 0 14px;">{{title}}</h2>
    <p style="font-size:18px;opacity:.92;margin:0 0 30px;line-height:1.6;">{{subtitle}}</p>
    <a href="{{button_url}}" style="display:inline-block;background:#fff;color:var(--color-accent,#0f172a);padding:15px 38px;border-radius:var(--radius-button,999px);font-weight:700;text-decoration:none;">{{button_text}}</a>
  </div>
</section>
HTML,
                'schema' => [
                    'title' => ['type' => 'text'], 'subtitle' => ['type' => 'textarea'],
                    'button_text' => ['type' => 'text'], 'button_url' => ['type' => 'text'],
                ],
                'content' => [
                    'title' => 'Bizimle çalışmaya hazır mısınız?', 'subtitle' => 'Hemen iletişime geçin.',
                    'button_text' => 'İletişime Geç', 'button_url' => '/iletisim',
                ],
            ],

            // ── GALLERY ────────────────────────────────────────────────────
            [
                'type' => 'gallery', 'variation' => 'base-gallery', 'name' => 'Genel / Galeri',
                'html' => <<<'HTML'
<section style="padding:76px 0;">
  <div style="max-width:var(--container-width,1200px);margin:0 auto;padding:0 24px;">
    <h2 style="font-size:34px;text-align:center;margin:0 0 44px;color:#0f172a;">{{title}}</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
      <img src="{{image_1}}" alt="" style="width:100%;height:220px;object-fit:cover;border-radius:14px;">
      <img src="{{image_2}}" alt="" style="width:100%;height:220px;object-fit:cover;border-radius:14px;">
      <img src="{{image_3}}" alt="" style="width:100%;height:220px;object-fit:cover;border-radius:14px;">
      <img src="{{image_4}}" alt="" style="width:100%;height:220px;object-fit:cover;border-radius:14px;">
      <img src="{{image_5}}" alt="" style="width:100%;height:220px;object-fit:cover;border-radius:14px;">
      <img src="{{image_6}}" alt="" style="width:100%;height:220px;object-fit:cover;border-radius:14px;">
    </div>
  </div>
</section>
HTML,
                'schema' => [
                    'title' => ['type' => 'text'],
                    'image_1' => ['type' => 'text'], 'image_2' => ['type' => 'text'], 'image_3' => ['type' => 'text'],
                    'image_4' => ['type' => 'text'], 'image_5' => ['type' => 'text'], 'image_6' => ['type' => 'text'],
                ],
                'content' => [
                    'title' => 'Galeri',
                    'image_1' => 'https://picsum.photos/seed/g1/600/400', 'image_2' => 'https://picsum.photos/seed/g2/600/400',
                    'image_3' => 'https://picsum.photos/seed/g3/600/400', 'image_4' => 'https://picsum.photos/seed/g4/600/400',
                    'image_5' => 'https://picsum.photos/seed/g5/600/400', 'image_6' => 'https://picsum.photos/seed/g6/600/400',
                ],
            ],

            // ── TESTIMONIALS (3) ───────────────────────────────────────────
            [
                'type' => 'testimonials', 'variation' => 'base-testimonials', 'name' => 'Genel / Yorumlar (3)',
                'html' => <<<'HTML'
<section style="padding:76px 0;background:var(--color-secondary,#f8fafc);">
  <div style="max-width:var(--container-width,1200px);margin:0 auto;padding:0 24px;text-align:center;">
    <h2 style="font-size:34px;margin:0 0 44px;color:#0f172a;">{{title}}</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;text-align:left;">
      <figure style="background:#fff;border-radius:var(--radius-card,16px);padding:28px;margin:0;box-shadow:0 6px 24px rgba(15,23,42,.06);">
        <blockquote style="margin:0 0 16px;color:#334155;line-height:1.7;font-style:italic;">“{{quote1}}”</blockquote>
        <figcaption style="font-weight:700;color:var(--color-primary,#4f46e5);">{{author1}}</figcaption>
      </figure>
      <figure style="background:#fff;border-radius:var(--radius-card,16px);padding:28px;margin:0;box-shadow:0 6px 24px rgba(15,23,42,.06);">
        <blockquote style="margin:0 0 16px;color:#334155;line-height:1.7;font-style:italic;">“{{quote2}}”</blockquote>
        <figcaption style="font-weight:700;color:var(--color-primary,#4f46e5);">{{author2}}</figcaption>
      </figure>
      <figure style="background:#fff;border-radius:var(--radius-card,16px);padding:28px;margin:0;box-shadow:0 6px 24px rgba(15,23,42,.06);">
        <blockquote style="margin:0 0 16px;color:#334155;line-height:1.7;font-style:italic;">“{{quote3}}”</blockquote>
        <figcaption style="font-weight:700;color:var(--color-primary,#4f46e5);">{{author3}}</figcaption>
      </figure>
    </div>
  </div>
</section>
HTML,
                'schema' => [
                    'title' => ['type' => 'text'],
                    'quote1' => ['type' => 'textarea'], 'author1' => ['type' => 'text'],
                    'quote2' => ['type' => 'textarea'], 'author2' => ['type' => 'text'],
                    'quote3' => ['type' => 'textarea'], 'author3' => ['type' => 'text'],
                ],
                'content' => [
                    'title' => 'Müşterilerimiz Ne Diyor?',
                    'quote1' => 'Harika bir deneyimdi, kesinlikle tavsiye ederim.', 'author1' => 'Ayşe K.',
                    'quote2' => 'Profesyonel ve ilgili bir ekip.', 'author2' => 'Mehmet T.',
                    'quote3' => 'Beklentilerimin ötesinde bir hizmet aldım.', 'author3' => 'Zeynep A.',
                ],
            ],
        ];
    }
}
