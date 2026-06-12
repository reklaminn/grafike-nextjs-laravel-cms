<?php

namespace Database\Seeders;

use App\Models\SectionTemplate;
use App\Models\Theme;
use Illuminate\Database\Seeder;

class EstetikDermalSectionTemplatesSeeder extends Seeder
{
    // NOT: Görsel alanları boşken gradyan fallback gösterilir; CMS'te image alanı
    // doldurulunca gerçek görsel render edilir (katmanlı background-image deseni).

    /** Bu tema ve bloklar yalnızca bu tenant'ın panelinde görünür (global değil). */
    private const TENANT_ID = 'estetik_dermal';

    public function run(): void
    {
        $theme = Theme::updateOrCreate(
            ['slug' => 'estetikdermal', 'tenant_id' => self::TENANT_ID],
            [
                'tenant_id'            => self::TENANT_ID,
                'module'               => null,
                'name'                 => 'Estetik Dermal',
                'engine'               => 'nextjs-basic-html',
                'tokens_json'          => [
                    'color_primary'   => '#E8702A',
                    'color_secondary' => '#FBF4EE',
                    'color_accent'    => '#F4A14E',
                    'radius_card'     => '18px',
                    'radius_button'   => '999px',
                    'container_width' => '1280px',
                ],
                'assets_json'          => ['css' => [], 'js' => []],
                'settings_schema_json' => [],
                'is_active'            => true,
            ]
        );

        foreach ($this->blocks() as $b) {
            SectionTemplate::updateOrCreate(
                ['theme_id' => $theme->id, 'type' => $b['type'], 'variation' => $b['variation']],
                [
                    'tenant_id'             => self::TENANT_ID,
                    'module'                => null,
                    'name'                  => $b['name'],
                    'render_mode'           => 'html',
                    'html_template'         => $b['html'],
                    'schema_json'           => $b['schema'],
                    'default_content_json'  => $b['content'],
                    'is_active'             => true,
                ]
            );
        }
    }

    private function blocks(): array
    {
        return [
            $this->heroFull(),
            $this->pageHero(),
            $this->trustStats(),
            $this->brandShowcase(),
            $this->categoryGrid(),
            $this->productGrid(),
            $this->productDetail(),
            $this->aboutSplit(),
            $this->trainingSupport(),
            $this->faqAccordion(),
            $this->ctaFull(),
            $this->contactSplit(),
        ];
    }

    /* ===================== 1. HERO FULL ===================== */
    private function heroFull(): array
    {
        $html = <<<'HTML'
<section style="position:relative;overflow:hidden;background:radial-gradient(1200px 600px at 80% -10%, rgba(244,161,78,.28), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);">
  <div class="container" style="display:flex;flex-wrap:wrap;gap:48px;align-items:center;padding:84px 0 92px;">
    <div style="flex:1 1 460px;">
      <p style="display:inline-flex;align-items:center;gap:8px;background:var(--primary-soft,#FCE9DC);color:var(--primary-deep,#C85716);border-radius:999px;padding:8px 16px;font-size:13px;font-weight:700;letter-spacing:.4px;margin:0 0 22px;">{{eyebrow}}</p>
      <h1 style="font-size:clamp(34px,5vw,56px);line-height:1.08;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 20px;">{{title}}<br><span style="background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));-webkit-background-clip:text;background-clip:text;color:transparent;">{{title_accent}}</span></h1>
      <p style="font-size:clamp(16px,2vw,20px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:560px;margin:0 0 32px;">{{subtitle}}</p>
      <div style="display:flex;gap:14px;flex-wrap:wrap;">
        <a href="{{button_url}}" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#E8702A);color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 10px 30px rgba(232,112,42,.32);">{{button_text}}</a>
        <a href="{{button2_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);padding:15px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{button2_text}}</a>
      </div>
      <div style="display:flex;gap:28px;flex-wrap:wrap;margin-top:38px;">
        <div><div style="font-size:13px;color:var(--text-soft,#6b6b6b);">{{badge1_label}}</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">{{badge1_value}}</div></div>
        <div style="width:1px;background:var(--border-soft,#ece6df);"></div>
        <div><div style="font-size:13px;color:var(--text-soft,#6b6b6b);">{{badge2_label}}</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">{{badge2_value}}</div></div>
        <div style="width:1px;background:var(--border-soft,#ece6df);"></div>
        <div><div style="font-size:13px;color:var(--text-soft,#6b6b6b);">{{badge3_label}}</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">{{badge3_value}}</div></div>
      </div>
    </div>
    <div style="position:relative;min-height:420px;flex:1 1 340px;">
      <div style="position:absolute;inset:0;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));border-radius:28px;transform:rotate(-3deg);opacity:.12;"></div>
      <div style="position:relative;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:24px;box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:26px;">
        <div style="aspect-ratio:4/3;border-radius:16px;background-image:url('{{image}}'),linear-gradient(135deg,var(--primary-soft,#FCE9DC),var(--accent-soft,#FBE3CB));background-size:cover;background-position:center;"></div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:18px;">
          <div><div style="font-size:12px;color:var(--color-primary,#E8702A);font-weight:700;text-transform:uppercase;letter-spacing:1px;">{{card_brand}}</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">{{card_title}}</div></div>
          <span style="background:var(--primary-soft,#FCE9DC);color:var(--primary-deep,#C85716);font-size:12px;font-weight:700;padding:6px 12px;border-radius:999px;">{{card_badge}}</span>
        </div>
      </div>
      <div style="position:absolute;bottom:-18px;left:-18px;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:16px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:14px 18px;display:flex;align-items:center;gap:10px;">
        <span style="font-size:26px;">{{float_emoji}}</span>
        <div><div style="font-size:11px;color:var(--text-soft,#6b6b6b);">{{float_label}}</div><div style="font-weight:800;font-size:14px;color:var(--text-main,#2a2a2a);">{{float_value}}</div></div>
      </div>
    </div>
  </div>
</section>
HTML;

        return [
            'type'      => 'hero-full',
            'variation' => 'corporate-center',
            'name'      => 'Hero — Kurumsal (split)',
            'html'      => $html,
            'schema'    => [
                'fields' => [
                    ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Üst etiket'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'title_accent', 'type' => 'text', 'label' => 'Başlık (vurgulu satır)'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Alt metin'],
                    ['key' => 'button_text', 'type' => 'text', 'label' => 'Buton 1 metni'],
                    ['key' => 'button_url', 'type' => 'text', 'label' => 'Buton 1 linki'],
                    ['key' => 'button2_text', 'type' => 'text', 'label' => 'Buton 2 metni'],
                    ['key' => 'button2_url', 'type' => 'text', 'label' => 'Buton 2 linki'],
                    ['key' => 'badge1_label', 'type' => 'text', 'label' => 'Rozet 1 etiket'],
                    ['key' => 'badge1_value', 'type' => 'text', 'label' => 'Rozet 1 değer'],
                    ['key' => 'badge2_label', 'type' => 'text', 'label' => 'Rozet 2 etiket'],
                    ['key' => 'badge2_value', 'type' => 'text', 'label' => 'Rozet 2 değer'],
                    ['key' => 'badge3_label', 'type' => 'text', 'label' => 'Rozet 3 etiket'],
                    ['key' => 'badge3_value', 'type' => 'text', 'label' => 'Rozet 3 değer'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Hero görseli'],
                    ['key' => 'card_brand', 'type' => 'text', 'label' => 'Kart marka'],
                    ['key' => 'card_title', 'type' => 'text', 'label' => 'Kart ürün adı'],
                    ['key' => 'card_badge', 'type' => 'text', 'label' => 'Kart rozet'],
                    ['key' => 'float_emoji', 'type' => 'text', 'label' => 'Floating emoji'],
                    ['key' => 'float_label', 'type' => 'text', 'label' => 'Floating etiket'],
                    ['key' => 'float_value', 'type' => 'text', 'label' => 'Floating değer'],
                ],
            ],
            'content'   => [
                'eyebrow'      => '● 2004\'ten beri Türkiye\'nin güveni',
                'title'        => 'Medikal estetikte',
                'title_accent' => 'yenilikçi çözümler',
                'subtitle'     => 'Tek seansta uzun etkili sonuçlarla yüksek memnuniyet. Uluslararası markaların resmi temsilcisi olarak, doktorlara ürün ve uygulamalı eğitim sunuyoruz.',
                'button_text'  => 'Ürünleri Keşfet →',
                'button_url'   => 'urunler.html',
                'button2_text' => '💬 WhatsApp Danışma',
                'button2_url'  => 'https://wa.me/905426205100',
                'badge1_label' => 'Sertifika',
                'badge1_value' => 'CE Class III',
                'badge2_label' => 'Kapsama',
                'badge2_value' => 'Türkiye Geneli',
                'badge3_label' => 'Destek',
                'badge3_value' => 'Uygulamalı Eğitim',
                'image'        => '',
                'card_brand'   => 'Skin Tech',
                'card_title'   => 'RRS® HA Long Lasting',
                'card_badge'   => 'CE III',
                'float_emoji'  => '☀️',
                'float_label'  => 'Güneş Koruma',
                'float_value'  => 'Melablock SPF 50+',
            ],
        ];
    }

    /* ===================== 2. PAGE HERO (compact) ===================== */
    private function pageHero(): array
    {
        $html = <<<'HTML'
<section style="position:relative;overflow:hidden;background:radial-gradient(900px 400px at 85% -20%, rgba(244,161,78,.26), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);">
  <div class="container" style="padding:48px 0 56px;">
    <nav aria-label="Breadcrumb" style="font-size:13.5px;color:var(--text-soft,#6b6b6b);margin:0 0 16px;">
      <a href="index.html" style="color:var(--text-soft,#6b6b6b);font-weight:600;">Ana Sayfa</a>
      <span style="margin:0 8px;opacity:.6;">/</span>
      <span style="color:var(--color-primary,#E8702A);font-weight:700;">{{breadcrumb}}</span>
    </nav>
    <h1 style="font-size:clamp(30px,4.5vw,46px);line-height:1.1;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 14px;">{{title}}</h1>
    <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:560px;margin:0;">{{subtitle}}</p>
  </div>
</section>
HTML;

        return [
            'type'      => 'page-hero',
            'variation' => 'compact',
            'name'      => 'Sayfa Başlığı — Kompakt (breadcrumb)',
            'html'      => $html,
            'schema'    => [
                'fields' => [
                    ['key' => 'breadcrumb', 'type' => 'text', 'label' => 'Breadcrumb (aktif sayfa)'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Alt metin'],
                ],
            ],
            'content'   => [
                'breadcrumb' => 'Ürünler',
                'title'      => 'Ürünler',
                'subtitle'   => '95+ profesyonel medikal estetik ürünü, 14 kategoride.',
            ],
        ];
    }

    /* ===================== 3. TRUST STATS (repeater) ===================== */
    private function trustStats(): array
    {
        $html = <<<'HTML'
<section style="background:#fff;">
  <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:24px;padding:48px 0;border-bottom:1px solid var(--border-soft,#ece6df);">
    {{{items_html}}}
  </div>
</section>
HTML;

        $itemTemplate = '<div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">{{value}}</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{label}}</div></div>';

        return [
            'type'      => 'trust-stats',
            'variation' => '4-stats',
            'name'      => 'Güven İstatistikleri — 4 sayı',
            'html'      => $html,
            'schema'    => [
                'fields' => [
                    [
                        'key'           => 'items',
                        'type'          => 'repeater',
                        'label'         => 'İstatistikler',
                        'item_template' => $itemTemplate,
                        'fields'        => [
                            ['key' => 'value', 'type' => 'text', 'label' => 'Değer'],
                            ['key' => 'label', 'type' => 'text', 'label' => 'Etiket'],
                        ],
                    ],
                ],
            ],
            'content'   => [
                'items' => [
                    ['value' => '20+', 'label' => 'Yıllık Deneyim'],
                    ['value' => '5+', 'label' => 'Uluslararası Marka'],
                    ['value' => '95+', 'label' => 'Profesyonel Ürün'],
                    ['value' => '81', 'label' => 'İl Dağıtım Ağı'],
                ],
            ],
        ];
    }

    /* ===================== 4. BRAND SHOWCASE (repeater) ===================== */
    private function brandShowcase(): array
    {
        $html = <<<'HTML'
<section style="padding:84px 0;background:#fff;">
  <div class="container">
    <div style="text-align:center;max-width:640px;margin:0 auto 52px;">
      <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{eyebrow}}</p>
      <h2 style="font-size:clamp(28px,4vw,40px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 14px;line-height:1.15;">{{title}}</h2>
      <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.7;">{{subtitle}}</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:22px;">
      {{{items_html}}}
    </div>
  </div>
</section>
HTML;

        $itemTemplate = '<a href="{{url}}" style="display:block;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:30px 26px;transition:transform .2s,box-shadow .2s;"><div style="width:52px;height:52px;border-radius:14px;background:{{brand_color}};display:grid;place-items:center;color:#fff;font-size:22px;font-weight:800;margin-bottom:18px;">{{logo_letter}}</div><h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">{{name}}</h3><p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">{{desc}}</p><span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">Keşfet →</span></a>';

        return [
            'type'      => 'brand-showcase',
            'variation' => 'cards',
            'name'      => 'Marka Vitrini — Kartlar',
            'html'      => $html,
            'schema'    => [
                'fields' => [
                    ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Üst etiket'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Alt metin'],
                    [
                        'key'           => 'items',
                        'type'          => 'repeater',
                        'label'         => 'Markalar',
                        'item_template' => $itemTemplate,
                        'fields'        => [
                            ['key' => 'logo_letter', 'type' => 'text', 'label' => 'Logo harfi'],
                            ['key' => 'brand_color', 'type' => 'text', 'label' => 'Marka rengi (hex)'],
                            ['key' => 'name', 'type' => 'text', 'label' => 'Marka adı'],
                            ['key' => 'desc', 'type' => 'textarea', 'label' => 'Açıklama'],
                            ['key' => 'url', 'type' => 'text', 'label' => 'Link'],
                        ],
                    ],
                ],
            ],
            'content'   => [
                'eyebrow'  => 'Temsil Ettiğimiz Markalar',
                'title'    => 'Dünyanın önde gelen markaları, Türkiye\'de tek adreste',
                'subtitle' => 'Her biri kendi alanında uzman; mezoterapiden cihaza, peelingden ip askıya geniş bir portföy.',
                'items'    => [
                    ['logo_letter' => 'S', 'brand_color' => '#0C6E72', 'name' => 'Skin Tech Pharma', 'desc' => 'Peeling, mezoterapi ve RRS skinbooster serisi.', 'url' => 'marka/skintech.html'],
                    ['logo_letter' => 'Sf', 'brand_color' => '#C98A6D', 'name' => 'Seffiline', 'desc' => 'Cilt, saç, intim bakım ve dolgu çözümleri.', 'url' => 'marka/seffiline.html'],
                    ['logo_letter' => 'A', 'brand_color' => '#6C5CE0', 'name' => 'Grand Aespio', 'desc' => 'Yüz maskeleri ve ip askı (thread) ürünleri.', 'url' => 'marka/aespio.html'],
                    ['logo_letter' => 'W', 'brand_color' => '#10131A', 'name' => 'Woorhi Mechatronics', 'desc' => 'Kore mühendisliği medikal estetik cihazları.', 'url' => 'marka/woorhi.html'],
                    ['logo_letter' => 'Mi', 'brand_color' => '#1a1a1a', 'name' => 'Mi Medical Innovation', 'desc' => 'Premium mezoterapi ve enjeksiyon sistemleri.', 'url' => 'marka/mi-medical.html'],
                ],
            ],
        ];
    }

    /* ===================== 5. CATEGORY GRID (repeater chips) ===================== */
    private function categoryGrid(): array
    {
        $html = <<<'HTML'
<section style="padding:84px 0;background:#fff;">
  <div class="container">
    <div style="text-align:center;max-width:600px;margin:0 auto 48px;">
      <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{eyebrow}}</p>
      <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">{{title}}</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
      {{{items_html}}}
    </div>
  </div>
</section>
HTML;

        $itemTemplate = '<a href="{{url}}" style="display:flex;align-items:center;gap:12px;background:var(--color-secondary,#FBF4EE);border-radius:14px;padding:18px 20px;font-weight:700;color:var(--text-main,#2a2a2a);transition:background .18s;"><span style="font-size:22px;">{{icon}}</span> {{name}}</a>';

        return [
            'type'      => 'category-grid',
            'variation' => 'chips',
            'name'      => 'Kategori Grid — Çipler',
            'html'      => $html,
            'schema'    => [
                'fields' => [
                    ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Üst etiket'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    [
                        'key'           => 'items',
                        'type'          => 'repeater',
                        'label'         => 'Kategoriler',
                        'item_template' => $itemTemplate,
                        'fields'        => [
                            ['key' => 'icon', 'type' => 'text', 'label' => 'İkon (emoji)'],
                            ['key' => 'name', 'type' => 'text', 'label' => 'Kategori adı'],
                            ['key' => 'url', 'type' => 'text', 'label' => 'Link'],
                        ],
                    ],
                ],
            ],
            'content'   => [
                'eyebrow' => 'Ürün Kategorileri',
                'title'   => 'İhtiyacınız olan her şey, 14 kategoride',
                'items'   => [
                    ['icon' => '🧵', 'name' => 'İp', 'url' => 'urunler.html'],
                    ['icon' => '💉', 'name' => 'Kanül & İğne Ucu', 'url' => 'urunler.html'],
                    ['icon' => '🧴', 'name' => 'Kimyasal Peeling', 'url' => 'urunler.html'],
                    ['icon' => '💄', 'name' => 'Kozmetik', 'url' => 'urunler.html'],
                    ['icon' => '🫙', 'name' => 'Kremler', 'url' => 'urunler.html'],
                    ['icon' => '🧪', 'name' => 'Mezoterapi', 'url' => 'urunler.html'],
                    ['icon' => '🔫', 'name' => 'Mezoterapi Tabancası', 'url' => 'urunler.html'],
                    ['icon' => '🪡', 'name' => 'Micro İğneleme', 'url' => 'urunler.html'],
                    ['icon' => '🩸', 'name' => 'Otolog Rejeneratif Terapi', 'url' => 'urunler.html'],
                    ['icon' => '✨', 'name' => 'Peeling', 'url' => 'urunler.html'],
                    ['icon' => '⚕️', 'name' => 'Profesyonel Ürünler', 'url' => 'urunler.html'],
                    ['icon' => '💧', 'name' => 'RRS', 'url' => 'urunler.html'],
                    ['icon' => '🌿', 'name' => 'Terapi', 'url' => 'urunler.html'],
                    ['icon' => '🎭', 'name' => 'Yüz Maskesi', 'url' => 'urunler.html'],
                ],
            ],
        ];
    }

    /* ===================== 6. PRODUCT GRID (repeater) ===================== */
    private function productGrid(): array
    {
        $html = <<<'HTML'
<section style="padding:8px 0 72px;background:#fff;">
  <div class="container">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:36px;">
      <div>
        <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 10px;">{{eyebrow}}</p>
        <h2 style="font-size:clamp(26px,4vw,36px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">{{title}}</h2>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px;">
      {{{items_html}}}
    </div>
  </div>
</section>
HTML;

        $itemTemplate = '<a href="{{url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;"><div style="aspect-ratio:4/3;background-image:url(\'{{image}}\'),linear-gradient(135deg,#FCE9DC,#FBE3CB);background-size:cover;background-position:center;"></div><div style="padding:22px;"><span style="font-size:12px;font-weight:700;color:{{brand_color}};text-transform:uppercase;letter-spacing:.6px;">{{brand_label}}</span><h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{name}}</h3><p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{desc}}</p></div></a>';

        return [
            'type'      => 'product-grid',
            'variation' => 'cards',
            'name'      => 'Ürün Grid — Kartlar',
            'html'      => $html,
            'schema'    => [
                'fields' => [
                    ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Üst etiket'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    [
                        'key'           => 'items',
                        'type'          => 'repeater',
                        'label'         => 'Ürünler',
                        'item_template' => $itemTemplate,
                        'fields'        => [
                            ['key' => 'image', 'type' => 'image', 'label' => 'Ürün görseli'],
                            ['key' => 'brand_label', 'type' => 'text', 'label' => 'Marka · Kategori etiketi'],
                            ['key' => 'brand_color', 'type' => 'text', 'label' => 'Marka rengi (hex)'],
                            ['key' => 'name', 'type' => 'text', 'label' => 'Ürün adı'],
                            ['key' => 'desc', 'type' => 'textarea', 'label' => 'Açıklama'],
                            ['key' => 'url', 'type' => 'text', 'label' => 'Link'],
                        ],
                    ],
                ],
            ],
            'content'   => [
                'eyebrow' => 'Tüm Ürünler',
                'title'   => 'Profesyonel medikal estetik portföyü',
                'items'   => [
                    ['image' => '', 'brand_label' => 'Skin Tech · RRS', 'brand_color' => '#0C6E72', 'name' => 'RRS® HA Long Lasting', 'desc' => 'Çapraz bağlı HA içeren CE Class III dermal implant.', 'url' => 'urun-detay.html'],
                    ['image' => '', 'brand_label' => 'Skin Tech · Krem', 'brand_color' => '#0C6E72', 'name' => 'Melablock HSP SPF 50+', 'desc' => 'Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktör.', 'url' => 'urun-detay.html'],
                    ['image' => '', 'brand_label' => 'Skin Tech · Mezoterapi', 'brand_color' => '#0C6E72', 'name' => 'Benebellum LUMINA VİT-C 18%', 'desc' => 'Yüksek konsantrasyonlu C vitamini ile aydınlatıcı bakım.', 'url' => 'urun-detay.html'],
                    ['image' => '', 'brand_label' => 'Skin Tech · Peeling', 'brand_color' => '#0C6E72', 'name' => 'Aclaranse', 'desc' => 'Lekeli ciltler için aydınlatıcı profesyonel peeling çözümü.', 'url' => 'urun-detay.html'],
                    ['image' => '', 'brand_label' => 'Grand Aespio · Yüz Maskesi', 'brand_color' => '#6C5CE0', 'name' => 'Beta-Glukan Mask', 'desc' => 'Yatıştırıcı ve onarıcı beta-glukan içeren yüz maskesi.', 'url' => 'urun-detay.html'],
                    ['image' => '', 'brand_label' => 'Seffiline · Mezoterapi', 'brand_color' => '#C98A6D', 'name' => 'SeffiHair', 'desc' => 'Saç dökülmesine karşı saçlı deri mezoterapi solüsyonu.', 'url' => 'urun-detay.html'],
                    ['image' => '', 'brand_label' => 'Woorhi · Mezoterapi Tabancası', 'brand_color' => '#2DA8FF', 'name' => 'Raffine', 'desc' => 'Kore mühendisliği cihaz.', 'url' => 'urun-detay.html'],
                    ['image' => '', 'brand_label' => 'Mi Medical · Mezoterapi Tabancası', 'brand_color' => '#C9A24B', 'name' => 'Pistor Eliance', 'desc' => 'Premium enjeksiyon sistemi.', 'url' => 'urun-detay.html'],
                ],
            ],
        ];
    }

    /* ===================== 7. PRODUCT DETAIL (split) ===================== */
    private function productDetail(): array
    {
        $html = <<<'HTML'
<section style="padding:48px 0 64px;background:#fff;">
  <div class="container" style="display:flex;flex-wrap:wrap;gap:52px;align-items:flex-start;">
    <div style="flex:1 1 380px;min-width:0;">
      <div style="position:relative;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:24px;box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:24px;">
        <div style="aspect-ratio:1/1;border-radius:18px;background-image:url('{{image}}'),linear-gradient(135deg,var(--primary-soft,#FCE9DC),var(--accent-soft,#FBE3CB));background-size:cover;background-position:center;"></div>
      </div>
    </div>
    <div style="flex:1 1 420px;min-width:0;">
      <span style="display:inline-block;font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:1px;background:rgba(12,110,114,.08);padding:6px 12px;border-radius:999px;">{{brand_label}} · {{category}}</span>
      <h1 style="font-size:clamp(28px,4vw,42px);line-height:1.12;font-weight:800;color:var(--text-main,#2a2a2a);margin:16px 0 14px;">{{title}}</h1>
      <p style="font-size:17px;color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0 0 26px;max-width:520px;">{{description}}</p>
      <ul style="list-style:none;margin:0 0 30px;padding:0;display:grid;gap:13px;">
        {{{features_html}}}
      </ul>
      <div style="display:flex;gap:14px;flex-wrap:wrap;">
        <a href="{{wa_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#E8702A);color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 10px 30px rgba(232,112,42,.32);">💬 WhatsApp ile Sipariş</a>
        <a href="{{quote_url}}" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);padding:15px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">Teklif İste</a>
      </div>
      <p style="margin:22px 0 0;font-size:13.5px;color:var(--text-soft,#6b6b6b);display:flex;align-items:center;gap:8px;">⚕️ Yalnızca hekim/klinik kullanımına yöneliktir.</p>
    </div>
  </div>
</section>
HTML;

        $itemTemplate = '<li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;font-size:15.5px;"><span style="color:var(--color-primary,#E8702A);font-size:18px;flex-shrink:0;">✓</span> {{text}}</li>';

        return [
            'type'      => 'product-detail',
            'variation' => 'split',
            'name'      => 'Ürün Detay — Split',
            'html'      => $html,
            'schema'    => [
                'fields' => [
                    ['key' => 'image', 'type' => 'image', 'label' => 'Ana ürün görseli'],
                    ['key' => 'brand_label', 'type' => 'text', 'label' => 'Marka'],
                    ['key' => 'category', 'type' => 'text', 'label' => 'Kategori'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Ürün adı'],
                    ['key' => 'description', 'type' => 'textarea', 'label' => 'Kısa açıklama'],
                    [
                        'key'           => 'features',
                        'type'          => 'repeater',
                        'label'         => 'Özellikler',
                        'item_template' => $itemTemplate,
                        'fields'        => [
                            ['key' => 'text', 'type' => 'text', 'label' => 'Özellik'],
                        ],
                    ],
                    ['key' => 'wa_url', 'type' => 'text', 'label' => 'WhatsApp linki'],
                    ['key' => 'quote_url', 'type' => 'text', 'label' => 'Teklif linki'],
                ],
            ],
            'content'   => [
                'image'       => '',
                'brand_label' => 'SKIN TECH',
                'category'    => 'RRS',
                'title'       => 'RRS® HA Long Lasting',
                'description' => 'Çapraz bağlı, emilebilir Hyalüronik asit içeren dermal implant.',
                'features'    => [
                    ['text' => 'CE Class III sertifikalı'],
                    ['text' => 'Steril tıbbi enjektör formu'],
                    ['text' => 'Amino asit içeren koruyucu tampon solüsyonu'],
                    ['text' => 'Uzun etkili (long lasting)'],
                    ['text' => 'Cilt gençleştirme & nemlendirme'],
                ],
                'wa_url'    => 'https://wa.me/905426205100',
                'quote_url' => 'iletisim.html',
            ],
        ];
    }

    /* ===================== 8. ABOUT SPLIT ===================== */
    private function aboutSplit(): array
    {
        $html = <<<'HTML'
<section style="padding:84px 0;background:var(--color-secondary,#FBF4EE);">
  <div class="container" style="display:flex;flex-wrap:wrap;gap:52px;align-items:center;">
    <div style="position:relative;min-height:360px;flex:1 1 320px;">
      <div style="position:absolute;inset:0;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));border-radius:24px;opacity:.14;transform:rotate(2deg);"></div>
      <div style="position:relative;height:100%;min-height:360px;border-radius:24px;background-image:url('{{image}}'),linear-gradient(135deg,#FCE9DC,#fff);background-size:cover;background-position:center;border:1px solid var(--border-soft,#ece6df);"></div>
    </div>
    <div style="flex:1 1 420px;">
      <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{eyebrow}}</p>
      <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 18px;line-height:1.18;">{{title}}</h2>
      <div style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.75;margin:0 0 18px;">{{{body_html}}}</div>
      <ul style="list-style:none;margin:0 0 28px;padding:0;display:grid;gap:12px;">
        {{{items_html}}}
      </ul>
      <a href="{{button_url}}" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#E8702A);color:#fff;padding:14px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{button_text}}</a>
    </div>
  </div>
</section>
HTML;

        $itemTemplate = '<li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">✓</span> {{text}}</li>';

        return [
            'type'      => 'about-split',
            'variation' => 'text-left',
            'name'      => 'Hakkımızda — Metin + Görsel',
            'html'      => $html,
            'schema'    => [
                'fields' => [
                    ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Üst etiket'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'body_html', 'type' => 'richtext', 'label' => 'Metin (HTML)'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Görsel'],
                    ['key' => 'button_text', 'type' => 'text', 'label' => 'Buton metni'],
                    ['key' => 'button_url', 'type' => 'text', 'label' => 'Buton linki'],
                    [
                        'key'           => 'bullets',
                        'type'          => 'repeater',
                        'label'         => 'Maddeler',
                        'item_template' => $itemTemplate,
                        'fields'        => [
                            ['key' => 'text', 'type' => 'text', 'label' => 'Madde'],
                        ],
                    ],
                ],
            ],
            'content'   => [
                'eyebrow'     => 'Biz Kimiz?',
                'title'       => '2004\'ten beri medikal estetikte güvenin adresi',
                'body_html'   => '<p style="margin:0;">Estetik Dermal, medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline dağıtıyor. Uluslararası markaların resmi temsilcisi olarak, yalnızca ürün değil; doktorlara uygulamalı eğitim ve teknik destek de sunuyoruz.</p>',
                'image'       => '',
                'button_text' => 'Hakkımızda →',
                'button_url'  => 'hakkimizda.html',
                'bullets'     => [
                    ['text' => 'Uluslararası markaların resmi Türkiye temsilcisi'],
                    ['text' => 'CE Class III sertifikalı RRS serisi'],
                    ['text' => 'Doktorlara uygulamalı eğitim ve teknik destek'],
                ],
            ],
        ];
    }

    /* ===================== 9. TRAINING / SUPPORT (repeater) ===================== */
    private function trainingSupport(): array
    {
        $html = <<<'HTML'
<section style="padding:84px 0;background:#fff;">
  <div class="container">
    <div style="text-align:center;max-width:620px;margin:0 auto 52px;">
      <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{eyebrow}}</p>
      <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">{{title}}</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;">
      {{{items_html}}}
    </div>
  </div>
</section>
HTML;

        $itemTemplate = '<div style="background:var(--color-secondary,#FBF4EE);border-radius:var(--radius-card,18px);padding:34px 30px;"><div style="width:56px;height:56px;border-radius:16px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;font-size:26px;margin-bottom:20px;">{{icon}}</div><h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">{{title}}</h3><p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">{{text}}</p></div>';

        return [
            'type'      => 'training-support',
            'variation' => '3cards',
            'name'      => 'Eğitim & Destek — 3 Kart',
            'html'      => $html,
            'schema'    => [
                'fields' => [
                    ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Üst etiket'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    [
                        'key'           => 'items',
                        'type'          => 'repeater',
                        'label'         => 'Kartlar',
                        'item_template' => $itemTemplate,
                        'fields'        => [
                            ['key' => 'icon', 'type' => 'text', 'label' => 'İkon (emoji)'],
                            ['key' => 'title', 'type' => 'text', 'label' => 'Kart başlığı'],
                            ['key' => 'text', 'type' => 'textarea', 'label' => 'Kart metni'],
                        ],
                    ],
                ],
            ],
            'content'   => [
                'eyebrow' => 'Neden Estetik Dermal?',
                'title'   => 'Sadece ürün değil, uçtan uca destek',
                'items'   => [
                    ['icon' => '🎓', 'title' => 'Uygulamalı Eğitim', 'text' => 'Hekimlere ürünlerin doğru ve güvenli kullanımı için birebir, uygulamalı eğitim programları.'],
                    ['icon' => '🛠️', 'title' => 'Teknik Destek', 'text' => 'Cihaz kurulumundan kullanım sürecine kadar kesintisiz teknik danışmanlık ve servis.'],
                    ['icon' => '🛡️', 'title' => 'Orijinallik Garantisi', 'text' => 'Resmi temsilci güvencesiyle %100 orijinal, sertifikalı ve takip edilebilir ürünler.'],
                ],
            ],
        ];
    }

    /* ===================== 10. FAQ ACCORDION (details) ===================== */
    private function faqAccordion(): array
    {
        $html = <<<'HTML'
<section style="padding:84px 0;background:var(--color-secondary,#FBF4EE);">
  <div class="container" style="max-width:820px;">
    <div style="text-align:center;margin:0 auto 44px;">
      <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 12px;line-height:1.15;">{{title}}</h2>
      <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.7;margin:0;">{{subtitle}}</p>
    </div>
    <div style="display:grid;gap:14px;">
      {{{items_html}}}
    </div>
  </div>
</section>
HTML;

        $itemTemplate = '<details style="background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 24px;"><summary style="cursor:pointer;font-weight:800;color:var(--text-main,#2a2a2a);font-size:16.5px;list-style:none;display:flex;align-items:center;justify-content:space-between;gap:14px;">{{question}}<span style="color:var(--color-primary,#E8702A);font-size:22px;font-weight:700;flex-shrink:0;">+</span></summary><p style="color:var(--text-soft,#6b6b6b);font-size:15.5px;line-height:1.75;margin:14px 0 0;">{{answer}}</p></details>';

        return [
            'type'      => 'faq-accordion',
            'variation' => 'details',
            'name'      => 'SSS — Akordiyon (details)',
            'html'      => $html,
            'schema'    => [
                'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Alt metin'],
                    [
                        'key'           => 'items',
                        'type'          => 'repeater',
                        'label'         => 'Sorular',
                        'item_template' => $itemTemplate,
                        'fields'        => [
                            ['key' => 'question', 'type' => 'text', 'label' => 'Soru'],
                            ['key' => 'answer', 'type' => 'textarea', 'label' => 'Cevap'],
                        ],
                    ],
                ],
            ],
            'content'   => [
                'title'    => 'Sıkça Sorulan Sorular',
                'subtitle' => 'Sipariş, eğitim ve ürünlerle ilgili en çok merak edilenler.',
                'items'    => [
                    ['question' => 'Sipariş nasıl verilir?', 'answer' => 'Ürün ve fiyat talepleriniz için WhatsApp hattımızdan (+90 542 620 51 00) ya da iletişim formundan bize ulaşabilirsiniz. Ekibimiz en kısa sürede dönüş yaparak siparişinizi oluşturur. Ürünlerimiz yalnızca hekim ve kliniklere yöneliktir.'],
                    ['question' => 'Ürünler orijinal ve sertifikalı mı?', 'answer' => 'Evet. Resmi distribütör güvencesiyle tüm ürünlerimiz %100 orijinal, sertifikalı ve takip edilebilirdir. RRS serisi CE Class III tıbbi cihaz onayına sahiptir.'],
                    ['question' => 'Ürün kullanımı için eğitim veriyor musunuz?', 'answer' => 'Evet. Hekimlere ürünlerin doğru ve güvenli kullanımı için birebir, uygulamalı eğitim programları sunuyoruz. Cihaz kurulumundan uygulama protokollerine kadar teknik destek sağlıyoruz.'],
                    ['question' => 'Türkiye genelinde kargo / teslimat var mı?', 'answer' => 'Evet, 81 ili kapsayan dağıtım ağımızla Türkiye geneline kargo gönderiyoruz. Sipariş ve teslimat süreleriyle ilgili güncel bilgiyi WhatsApp üzerinden alabilirsiniz.'],
                ],
            ],
        ];
    }

    /* ===================== 11. CTA FULL (gradient) ===================== */
    private function ctaFull(): array
    {
        $html = <<<'HTML'
<section style="padding:20px 0 84px;background:#fff;">
  <div class="container">
    <div style="position:relative;overflow:hidden;border-radius:28px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));padding:clamp(40px,6vw,72px);text-align:center;color:#fff;">
      <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.12);"></div>
      <div style="position:absolute;bottom:-60px;left:-30px;width:240px;height:240px;border-radius:50%;background:rgba(255,255,255,.08);"></div>
      <div style="position:relative;">
        <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.15;">{{title}}</h2>
        <p style="font-size:18px;opacity:.95;max-width:600px;margin:0 auto 32px;line-height:1.6;">{{subtitle}}</p>
        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
          <a href="{{button_url}}" target="_blank" rel="noopener" style="background:#fff;color:var(--color-primary,#E8702A);padding:15px 32px;border-radius:var(--radius-button,999px);font-weight:800;font-size:15px;">{{button_text}}</a>
          <a href="{{button2_url}}" target="_blank" rel="noopener" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.6);padding:15px 32px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{button2_text}}</a>
        </div>
      </div>
    </div>
  </div>
</section>
HTML;

        return [
            'type'      => 'cta-full',
            'variation' => 'gradient',
            'name'      => 'CTA — Gradyan',
            'html'      => $html,
            'schema'    => [
                'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Başlık'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Alt metin'],
                    ['key' => 'button_text', 'type' => 'text', 'label' => 'Buton 1 metni'],
                    ['key' => 'button_url', 'type' => 'text', 'label' => 'Buton 1 linki'],
                    ['key' => 'button2_text', 'type' => 'text', 'label' => 'Buton 2 metni'],
                    ['key' => 'button2_url', 'type' => 'text', 'label' => 'Buton 2 linki'],
                ],
            ],
            'content'   => [
                'title'        => 'Profesyonel çözümler için bize ulaşın',
                'subtitle'     => 'Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp\'tan hızlıca yazın ya da MEDINET portalına giriş yapın.',
                'button_text'  => '💬 WhatsApp ile Yaz',
                'button_url'   => 'https://wa.me/905426205100',
                'button2_text' => 'MEDINET Portalı ↗',
                'button2_url'  => 'https://apps.skintechpharmagroup.com/medinet',
            ],
        ];
    }

    /* ===================== 12. CONTACT SPLIT (map right) ===================== */
    private function contactSplit(): array
    {
        $html = <<<'HTML'
<section style="padding:72px 0;background:#fff;">
  <div class="container" style="display:flex;flex-wrap:wrap;gap:40px;align-items:stretch;">
    <div style="flex:1 1 380px;">
      <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Bize Ulaşın</p>
      <h2 style="font-size:clamp(24px,3.5vw,32px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 26px;line-height:1.18;">İletişim bilgilerimiz</h2>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
        <a href="tel:{{phone_href}}" style="display:flex;align-items:flex-start;gap:14px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 22px;">
          <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;font-size:21px;">📞</span>
          <span style="display:block;">
            <span style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft,#6b6b6b);margin-bottom:4px;">Telefon</span>
            <span style="display:block;font-weight:800;color:var(--text-main,#2a2a2a);font-size:16px;">{{phone}}</span>
          </span>
        </a>
        <a href="{{whatsapp}}" target="_blank" rel="noopener" style="display:flex;align-items:flex-start;gap:14px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 22px;">
          <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;font-size:21px;">💬</span>
          <span style="display:block;">
            <span style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft,#6b6b6b);margin-bottom:4px;">WhatsApp</span>
            <span style="display:block;font-weight:800;color:var(--text-main,#2a2a2a);font-size:16px;">{{whatsapp_label}}</span>
          </span>
        </a>
        <a href="mailto:{{email}}" style="display:flex;align-items:flex-start;gap:14px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 22px;">
          <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;font-size:21px;">✉️</span>
          <span style="display:block;">
            <span style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft,#6b6b6b);margin-bottom:4px;">E-posta</span>
            <span style="display:block;font-weight:800;color:var(--text-main,#2a2a2a);font-size:16px;word-break:break-word;">{{email}}</span>
          </span>
        </a>
        <a href="https://maps.google.com/maps?q=Kusadasi%20Aydin" target="_blank" rel="noopener" style="display:flex;align-items:flex-start;gap:14px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 22px;">
          <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;font-size:21px;">📍</span>
          <span style="display:block;">
            <span style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft,#6b6b6b);margin-bottom:4px;">Adres</span>
            <span style="display:block;font-weight:700;color:var(--text-main,#2a2a2a);font-size:14.5px;line-height:1.55;">{{address}}</span>
          </span>
        </a>
      </div>
      <div style="display:flex;align-items:center;gap:14px;margin-top:18px;background:var(--primary-soft,#FCE9DC);border-radius:var(--radius-card,18px);padding:18px 22px;">
        <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:#fff;display:grid;place-items:center;font-size:21px;">🕘</span>
        <div>
          <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--primary-deep,#C85716);margin-bottom:4px;">Çalışma Saatleri</div>
          <div style="font-weight:800;color:var(--text-main,#2a2a2a);font-size:16px;">{{hours}}</div>
        </div>
      </div>
      <a href="https://www.instagram.com/estetikdermal/" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:10px;margin-top:18px;color:var(--color-primary,#E8702A);font-weight:700;font-size:15px;">
        <span style="font-size:18px;">📷</span> Instagram'da takip edin →
      </a>
    </div>
    <div style="flex:1 1 380px;display:flex;">
      <iframe
        title="Estetik Dermal — Kuşadası / Aydın konum haritası"
        src="{{map_embed}}"
        style="width:100%;min-height:380px;border:0;border-radius:var(--radius-card,18px);box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>
</section>
HTML;

        return [
            'type'      => 'contact-split',
            'variation' => 'map-right',
            'name'      => 'İletişim — Harita Sağda',
            'html'      => $html,
            'schema'    => [
                'fields' => [
                    ['key' => 'phone', 'type' => 'text', 'label' => 'Telefon (görünen)'],
                    ['key' => 'phone_href', 'type' => 'text', 'label' => 'Telefon (tel: değeri)'],
                    ['key' => 'whatsapp', 'type' => 'text', 'label' => 'WhatsApp linki'],
                    ['key' => 'whatsapp_label', 'type' => 'text', 'label' => 'WhatsApp (görünen numara)'],
                    ['key' => 'email', 'type' => 'text', 'label' => 'E-posta'],
                    ['key' => 'address', 'type' => 'textarea', 'label' => 'Adres'],
                    ['key' => 'hours', 'type' => 'text', 'label' => 'Çalışma saatleri'],
                    ['key' => 'map_embed', 'type' => 'text', 'label' => 'Harita embed URL'],
                ],
            ],
            'content'   => [
                'phone'          => '0 256 612 18 13',
                'phone_href'     => '+902566121813',
                'whatsapp'       => 'https://wa.me/905426205100',
                'whatsapp_label' => '+90 542 620 51 00',
                'email'          => 'info@estetikdermal.com',
                'address'        => 'Türkmen Mah. Turgut Özel Bulvarı Ada Modern A Blok No 83/3A Kuşadası/Aydın',
                'hours'          => 'Hafta içi 09:00–18:00',
                'map_embed'      => 'https://maps.google.com/maps?q=Kusadasi%20Aydin&t=&z=13&ie=UTF8&iwloc=&output=embed',
            ],
        ];
    }
}
