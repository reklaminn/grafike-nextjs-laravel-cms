<?php

namespace Database\Seeders;

use App\Models\SectionTemplate;
use App\Models\Theme;
use Illuminate\Database\Seeder;

/**
 * Global (shared) tourism block library — feeds the "Turizm" industry
 * SiteTemplate (IndustryTemplatesSeeder::tourism()).
 *
 * All rows are created with tenant_id = NULL, i.e. GLOBAL: every tenant can
 * USE these blocks on their pages; only agency admins can edit them.  The
 * industry-template applier resolves its sections to these global blocks, so
 * applying the tourism template to any site produces a working layout without
 * leaking another tenant's blocks.
 *
 * Blocks are self-contained (inline styles + CSS-var fallbacks) so they render
 * styled even before a theme stylesheet loads; colours follow the site's
 * design tokens (--color-primary / --color-accent / --color-secondary) which
 * the tourism template sets to sea-tones.
 *
 * Idempotent — keyed by (theme_id, type, variation).  Run:
 *   php artisan db:seed --class=GlobalTourismCatalogSeeder
 */
class GlobalTourismCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $theme = Theme::updateOrCreate(
            ['slug' => 'turizm-modern'],
            [
                'tenant_id'   => null, // GLOBAL / shared
                'name'        => 'Turizm — Modern',
                'engine'      => 'nextjs-basic-html',
                'description' => 'Tur acenteleri için global blok kütüphanesi (deniz tonları).',
                'assets_json' => ['css' => [], 'js' => []],
                'tokens_json' => [
                    'color_primary'   => '#0ea5e9',
                    'color_secondary' => '#f0f9ff',
                    'color_accent'    => '#0f766e',
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
                    'tenant_id'            => null, // GLOBAL / shared
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
                'type' => 'hero', 'variation' => 'turizm-hero', 'name' => 'Turizm / Hero Banner',
                'html' => <<<'HTML'
<section style="position:relative;padding:104px 0;background:linear-gradient(135deg,var(--color-primary,#0ea5e9),var(--color-accent,#0f766e));color:#fff;text-align:center;">
  <div style="max-width:var(--container-width,1200px);margin:0 auto;padding:0 24px;">
    <p style="text-transform:uppercase;letter-spacing:3px;font-weight:600;opacity:.9;margin:0 0 14px;">{{eyebrow}}</p>
    <h1 style="font-size:clamp(34px,5vw,58px);line-height:1.08;margin:0 0 18px;">{{title}}</h1>
    <p style="font-size:19px;max-width:680px;margin:0 auto 30px;opacity:.95;line-height:1.6;">{{subtitle}}</p>
    <a href="{{button_url}}" style="display:inline-block;background:#fff;color:var(--color-primary,#0ea5e9);padding:15px 36px;border-radius:var(--radius-button,999px);font-weight:700;text-decoration:none;box-shadow:0 8px 24px rgba(0,0,0,.18);">{{button_text}}</a>
  </div>
</section>
HTML,
                'schema' => [
                    'eyebrow'     => ['type' => 'text'],
                    'title'       => ['type' => 'text'],
                    'subtitle'    => ['type' => 'textarea'],
                    'button_text' => ['type' => 'text'],
                    'button_url'  => ['type' => 'text'],
                ],
                'content' => [
                    'eyebrow'     => 'Keşfetmeye Hazır mısın?',
                    'title'       => 'Hayalindeki tatil bir tık uzağında',
                    'subtitle'    => 'Yurt içi ve yurt dışı tur paketleri, günübirlik geziler ve özel rotalarla unutulmaz anılar.',
                    'button_text' => 'Turları İncele',
                    'button_url'  => '/turlar',
                ],
            ],

            // ── FEATURES / NEDEN BİZ ───────────────────────────────────────
            [
                'type' => 'features', 'variation' => 'turizm-services', 'name' => 'Turizm / Hizmet Kartları (3)',
                'html' => <<<'HTML'
<section style="padding:76px 0;background:var(--color-secondary,#f0f9ff);">
  <div style="max-width:var(--container-width,1200px);margin:0 auto;padding:0 24px;text-align:center;">
    <h2 style="font-size:34px;margin:0 0 10px;color:#0f172a;">{{title}}</h2>
    <p style="color:#64748b;max-width:640px;margin:0 auto 44px;line-height:1.6;">{{subtitle}}</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px;text-align:left;">
      <div style="background:#fff;border-radius:var(--radius-card,16px);padding:30px;box-shadow:0 6px 24px rgba(2,132,199,.08);">
        <div style="font-size:40px;line-height:1;margin-bottom:14px;">{{item1_icon}}</div>
        <h3 style="margin:0 0 8px;font-size:20px;color:#0f172a;">{{item1_title}}</h3>
        <p style="margin:0;color:#64748b;line-height:1.65;">{{item1_text}}</p>
      </div>
      <div style="background:#fff;border-radius:var(--radius-card,16px);padding:30px;box-shadow:0 6px 24px rgba(2,132,199,.08);">
        <div style="font-size:40px;line-height:1;margin-bottom:14px;">{{item2_icon}}</div>
        <h3 style="margin:0 0 8px;font-size:20px;color:#0f172a;">{{item2_title}}</h3>
        <p style="margin:0;color:#64748b;line-height:1.65;">{{item2_text}}</p>
      </div>
      <div style="background:#fff;border-radius:var(--radius-card,16px);padding:30px;box-shadow:0 6px 24px rgba(2,132,199,.08);">
        <div style="font-size:40px;line-height:1;margin-bottom:14px;">{{item3_icon}}</div>
        <h3 style="margin:0 0 8px;font-size:20px;color:#0f172a;">{{item3_title}}</h3>
        <p style="margin:0;color:#64748b;line-height:1.65;">{{item3_text}}</p>
      </div>
    </div>
  </div>
</section>
HTML,
                'schema' => [
                    'title'      => ['type' => 'text'],
                    'subtitle'   => ['type' => 'textarea'],
                    'item1_icon' => ['type' => 'text'], 'item1_title' => ['type' => 'text'], 'item1_text' => ['type' => 'textarea'],
                    'item2_icon' => ['type' => 'text'], 'item2_title' => ['type' => 'text'], 'item2_text' => ['type' => 'textarea'],
                    'item3_icon' => ['type' => 'text'], 'item3_title' => ['type' => 'text'], 'item3_text' => ['type' => 'textarea'],
                ],
                'content' => [
                    'title'    => 'Neden Bizi Tercih Etmelisiniz?',
                    'subtitle' => 'Profesyonel rehberlik, güvenli ulaşım ve özenle seçilmiş rotalarla yanınızdayız.',
                    'item1_icon' => '🌍', 'item1_title' => 'Uzman Rehberler', 'item1_text' => 'Deneyimli, yerel kültürü tanıyan profesyonel rehber kadromuz.',
                    'item2_icon' => '🚌', 'item2_title' => 'Konforlu Ulaşım', 'item2_text' => 'Klimalı, güvenli araç filomuzla kapıdan kapıya hizmet.',
                    'item3_icon' => '💳', 'item3_title' => 'Esnek Ödeme', 'item3_text' => 'Taksit imkânı ve şeffaf fiyat politikası ile bütçenize uygun.',
                ],
            ],

            // ── RICH TEXT ──────────────────────────────────────────────────
            [
                'type' => 'rich-text', 'variation' => 'turizm-content', 'name' => 'Turizm / Metin Bloğu',
                'html' => <<<'HTML'
<section style="padding:66px 0;">
  <div style="max-width:860px;margin:0 auto;padding:0 24px;">
    <h2 style="font-size:30px;margin:0 0 20px;color:#0f172a;">{{title}}</h2>
    <div style="color:#334155;line-height:1.85;font-size:17px;">{{{body_html}}}</div>
  </div>
</section>
HTML,
                'schema' => [
                    'title'     => ['type' => 'text'],
                    'body_html' => ['type' => 'textarea'],
                ],
                'content' => [
                    'title'     => 'Hakkımızda',
                    'body_html' => '<p>20 yılı aşkın tecrübemizle binlerce misafirimizi hayalindeki destinasyonlara ulaştırdık. Acentemiz, TÜRSAB üyesi olup tüm turlarımız sigorta güvencesi altındadır.</p><p>Amacımız; her bütçeye uygun, güvenli ve keyifli seyahat deneyimleri sunmak.</p>',
                ],
            ],

            // ── ARTICLE LIST / POPÜLER TURLAR ──────────────────────────────
            [
                'type' => 'article-list', 'variation' => 'turizm-tours', 'name' => 'Turizm / Popüler Tur Kartları',
                'html' => <<<'HTML'
<section style="padding:76px 0;background:var(--color-secondary,#f0f9ff);">
  <div style="max-width:var(--container-width,1200px);margin:0 auto;padding:0 24px;">
    <h2 style="font-size:34px;margin:0 0 10px;text-align:center;color:#0f172a;">{{title}}</h2>
    <p style="text-align:center;color:#64748b;max-width:640px;margin:0 auto 44px;line-height:1.6;">{{description}}</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;">{{{items_html}}}</div>
  </div>
</section>
HTML,
                'schema' => [
                    'title'       => ['type' => 'text'],
                    'description' => ['type' => 'textarea'],
                    'items_html'  => ['type' => 'textarea'],
                ],
                'content' => [
                    'title'       => 'Popüler Turlar',
                    'description' => 'En çok tercih edilen rotalarımızdan bir seçki.',
                    'items_html'  => '<article style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 6px 24px rgba(2,132,199,.08);">'
                        .'<img src="https://picsum.photos/seed/kapadokya/600/360" alt="Kapadokya" style="width:100%;height:200px;object-fit:cover;display:block;">'
                        .'<div style="padding:20px;"><h3 style="margin:0 0 8px;font-size:19px;">Kapadokya Turu</h3><p style="margin:0 0 12px;color:#64748b;line-height:1.6;">2 gece 3 gün · Balon turu opsiyonlu</p><span style="font-weight:700;color:var(--color-primary,#0ea5e9);">₺4.900\'den başlayan</span></div></article>'
                        .'<article style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 6px 24px rgba(2,132,199,.08);">'
                        .'<img src="https://picsum.photos/seed/karadeniz/600/360" alt="Karadeniz" style="width:100%;height:200px;object-fit:cover;display:block;">'
                        .'<div style="padding:20px;"><h3 style="margin:0 0 8px;font-size:19px;">Karadeniz Yaylaları</h3><p style="margin:0 0 12px;color:#64748b;line-height:1.6;">4 gece 5 gün · Tam pansiyon</p><span style="font-weight:700;color:var(--color-primary,#0ea5e9);">₺7.500\'den başlayan</span></div></article>'
                        .'<article style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 6px 24px rgba(2,132,199,.08);">'
                        .'<img src="https://picsum.photos/seed/efes/600/360" alt="Ege" style="width:100%;height:200px;object-fit:cover;display:block;">'
                        .'<div style="padding:20px;"><h3 style="margin:0 0 8px;font-size:19px;">Efes & Şirince</h3><p style="margin:0 0 12px;color:#64748b;line-height:1.6;">Günübirlik · Rehberli</p><span style="font-weight:700;color:var(--color-primary,#0ea5e9);">₺1.200\'den başlayan</span></div></article>',
                ],
            ],

            // ── CTA / REZERVASYON ──────────────────────────────────────────
            [
                'type' => 'cta', 'variation' => 'turizm-cta', 'name' => 'Turizm / Rezervasyon CTA',
                'html' => <<<'HTML'
<section style="padding:84px 0;background:var(--color-accent,#0f766e);color:#fff;text-align:center;">
  <div style="max-width:760px;margin:0 auto;padding:0 24px;">
    <h2 style="font-size:36px;margin:0 0 14px;">{{title}}</h2>
    <p style="font-size:18px;opacity:.92;margin:0 0 30px;line-height:1.6;">{{subtitle}}</p>
    <a href="{{button_url}}" style="display:inline-block;background:#fff;color:var(--color-accent,#0f766e);padding:15px 38px;border-radius:var(--radius-button,999px);font-weight:700;text-decoration:none;">{{button_text}}</a>
  </div>
</section>
HTML,
                'schema' => [
                    'title'       => ['type' => 'text'],
                    'subtitle'    => ['type' => 'textarea'],
                    'button_text' => ['type' => 'text'],
                    'button_url'  => ['type' => 'text'],
                ],
                'content' => [
                    'title'       => 'Bir sonraki maceran için hazır mısın?',
                    'subtitle'    => 'Uzman ekibimiz sana en uygun tur paketini bulmak için bekliyor.',
                    'button_text' => 'Hemen Rezervasyon Yap',
                    'button_url'  => '/iletisim',
                ],
            ],

            // ── GALLERY ────────────────────────────────────────────────────
            [
                'type' => 'gallery', 'variation' => 'turizm-gallery', 'name' => 'Turizm / Galeri',
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
                    'title'   => ['type' => 'text'],
                    'image_1' => ['type' => 'text'], 'image_2' => ['type' => 'text'], 'image_3' => ['type' => 'text'],
                    'image_4' => ['type' => 'text'], 'image_5' => ['type' => 'text'], 'image_6' => ['type' => 'text'],
                ],
                'content' => [
                    'title'   => 'Gezi Albümü',
                    'image_1' => 'https://picsum.photos/seed/tur1/600/400',
                    'image_2' => 'https://picsum.photos/seed/tur2/600/400',
                    'image_3' => 'https://picsum.photos/seed/tur3/600/400',
                    'image_4' => 'https://picsum.photos/seed/tur4/600/400',
                    'image_5' => 'https://picsum.photos/seed/tur5/600/400',
                    'image_6' => 'https://picsum.photos/seed/tur6/600/400',
                ],
            ],
        ];
    }
}
