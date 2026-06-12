<?php

namespace Database\Seeders;

use App\Models\SectionTemplate;
use App\Models\Theme;
use Illuminate\Database\Seeder;

/** EstetikDermalKlasikFieldChromeSeeder: her bölüm için alan-tabanlı (schema+repeater) section template (central). */
class EstetikDermalKlasikFieldChromeSeeder extends Seeder
{
    private const TENANT_ID='estetik_dermal';
    public function run(): void
    {
        $theme=Theme::where('slug','estetikdermal')->first();
        if(! $theme){ $this->command?->warn('estetikdermal teması yok.'); return; }
        $tid=$theme->id;
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-home-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'HERO (split)',
             'html_template'=><<<'EDHTML'
<!-- HERO (split) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(1200px 600px at 80% -10%, rgba(244,161,78,.28), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:48px;align-items:center;padding:84px 0 92px;">
        <div style="flex:1 1 460px;">
          <p style="display:inline-flex;align-items:center;gap:8px;background:var(--primary-soft,#FCE9DC);color:var(--primary-deep,#C85716);border-radius:999px;padding:8px 16px;font-size:13px;font-weight:700;letter-spacing:.4px;margin:0 0 22px;">{{group_1_subtitle}}</p>
          <h1 style="font-size:clamp(34px,5vw,56px);line-height:1.08;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 20px;">{{group_1_title}}<br><span style="background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));-webkit-background-clip:text;background-clip:text;color:transparent;">{{group_1_text}}</span></h1>
          <p style="font-size:clamp(16px,2vw,20px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:560px;margin:0 0 32px;">{{{group_2_body_html}}}</p>
          <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a href="{{group_1_button_url}}" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#E8702A);color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 10px 30px rgba(232,112,42,.32);">{{group_1_button_text}}</a>
            <a href="{{group_2_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);padding:15px 26px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;"><svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_2_button_text}}</a>
          </div>
          <div style="display:flex;gap:28px;flex-wrap:wrap;margin-top:38px;">
            <div><div style="font-size:13px;color:var(--text-soft,#6b6b6b);">{{group_1_text_2}}</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">{{group_2_text}}</div></div>
            <div style="width:1px;background:var(--border-soft,#ece6df);"></div>
            <div><div style="font-size:13px;color:var(--text-soft,#6b6b6b);">{{group_1_text_3}}</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">{{group_2_text_2}}</div></div>
            <div style="width:1px;background:var(--border-soft,#ece6df);"></div>
            <div><div style="font-size:13px;color:var(--text-soft,#6b6b6b);">{{group_1_text_4}}</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">{{group_2_text_3}}</div></div>
          </div>
        </div>
        <!-- HERO SLIDER (saf CSS scroll-snap). Slayt görselleri: /assets/img/hero-*.jpg
             her slaytın görsel kutusu image-ready: dosya gelince otomatik görünür (yoksa krem kalır).
             4. slayt eklemek için: yeni <article class="hero-slide" id="hs4"> kopyala + .hero-dots'a <a href="#hs4"></a> ekle. -->
        <div style="position:relative;min-height:420px;flex:1 1 340px;">
          <div style="position:absolute;inset:0;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));border-radius:28px;transform:rotate(-3deg);opacity:.10;"></div>
          <div class="hero-slider" style="position:relative;">
            <button class="hero-arrow hero-arrow--prev" type="button" aria-label="Önceki ürün"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></button>
            <button class="hero-arrow hero-arrow--next" type="button" aria-label="Sonraki ürün"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></button>
            <div class="hero-slides">
              {{{hero_slide_items_html}}}</div>
            <div class="hero-dots">
              <a href="{{group_1_button_url_2}}" aria-label="1. ürün"></a>
              <a href="{{group_2_button_url_2}}" aria-label="2. ürün"></a>
              <a href="{{group_3_button_url}}" aria-label="3. ürün"></a>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"hero_slide_items": {"type": "repeater", "label": "Hero Slide Items", "repeat_kind": "items", "item_template": "<article class=\"hero-slide\" id=\"hs1\">\n                <div style=\"background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:24px;box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:26px;\">\n                  <!-- 🖼️ GÖRSEL: /assets/img/hero-rrs.jpg (oran 4:3) — RRS HA Long Lasting ürün çekimi (krem zemin, klinik premium) -->\n                  <div style=\"aspect-ratio:4/3;border-radius:16px;background:var(--color-secondary,#FBF4EE) url('assets/img/hero-rrs.jpg') center/cover no-repeat;\"></div>\n                  <div style=\"display:flex;align-items:center;justify-content:space-between;margin-top:18px;\">\n                    <div><div style=\"font-size:12px;color:var(--color-primary,#E8702A);font-weight:700;text-transform:uppercase;letter-spacing:1px;\">{{text}}</div><div style=\"font-weight:800;color:var(--text-main,#2a2a2a);\">{{text_2}}</div></div>\n                    <span style=\"background:var(--primary-soft,#FCE9DC);color:var(--primary-deep,#C85716);font-size:12px;font-weight:700;padding:6px 12px;border-radius:999px;\">{{text_3}}</span>\n                  </div>\n                </div>\n              </article>", "fields": {"text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}}}, "group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_1_button_url_2": {"type": "text", "label": "Group 1 Button Url 2"}, "group_2_button_url_2": {"type": "text", "label": "Group 2 Button Url 2"}, "group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"hero_slide_items": [{"text": "Skin Tech", "text_2": "RRS® HA Long Lasting", "text_3": "CE III"}, {"text": "Skin Tech", "text_2": "Melablock HSP SPF 50+", "text_3": "SPF 50+"}, {"text": "Skin Tech", "text_2": "Benebellum LUMINA VİT-C", "text_3": "VİT-C"}], "group_1_button_url": "/urunler", "group_2_button_url": "https://wa.me/905426205100", "group_1_button_url_2": "#hs1", "group_2_button_url_2": "#hs2", "group_3_button_url": "#hs3", "group_1_subtitle": "● 2004'ten beri Türkiye'nin güveni", "group_1_title": "Medikal estetikte", "group_1_text": "yenilikçi çözümler", "group_2_body_html": "Tek seansta uzun etkili sonuçlarla yüksek memnuniyet. Uluslararası markaların resmi temsilcisi olarak, doktorlara ürün ve uygulamalı eğitim sunuyoruz.", "group_1_button_text": "Ürünleri Keşfet →", "group_2_button_text": "WhatsApp Danışma", "group_1_text_2": "Sertifika", "group_2_text": "CE Class III", "group_1_text_3": "Kapsama", "group_2_text_2": "Türkiye Geneli", "group_1_text_4": "Destek", "group_2_text_3": "Uygulamalı Eğitim"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-home-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'TRUST STATS',
             'html_template'=><<<'EDHTML'
<!-- TRUST STATS -->

    <section style="background:#fff;">
      <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:24px;padding:48px 0;border-bottom:1px solid var(--border-soft,#ece6df);">
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">{{group_1_text}}</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{group_2_text}}</div></div>
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">{{group_1_text_2}}</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{group_2_text_2}}</div></div>
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">{{group_1_text_3}}</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{group_2_text_3}}</div></div>
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">{{group_1_text_4}}</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{group_2_text_4}}</div></div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_text": "20+", "group_2_text": "Yıllık Deneyim", "group_1_text_2": "5+", "group_2_text_2": "Uluslararası Marka", "group_1_text_3": "95+", "group_2_text_3": "Profesyonel Ürün", "group_1_text_4": "81", "group_2_text_4": "İl Dağıtım Ağı"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-home-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'BRAND SHOWCASE',
             'html_template'=><<<'EDHTML'
<!-- BRAND SHOWCASE -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container">
        <div style="text-align:center;max-width:640px;margin:0 auto 52px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(28px,4vw,40px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 14px;line-height:1.15;">{{group_1_title}}</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.7;">{{group_2_description}}</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;">
          <a href="{{group_1_button_url}}" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #0C6E72;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#0C6E72;margin-bottom:10px;">{{group_1_text}}</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;">{{group_1_title_2}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 16px;">{{group_1_subtitle_2}}</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">{{group_1_text_2}}</span>
          </a>
          <a href="{{group_2_button_url}}" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #C98A6D;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#C98A6D;margin-bottom:10px;">{{group_2_text}}</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;">{{group_2_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 16px;">{{group_2_subtitle}}</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">{{group_2_text_2}}</span>
          </a>
          <a href="{{group_3_button_url}}" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #6C5CE0;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#6C5CE0;margin-bottom:10px;">{{group_3_text}}</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;">{{group_3_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 16px;">{{group_3_subtitle}}</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">{{group_3_text_2}}</span>
          </a>
          <a href="{{group_4_button_url}}" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #2DA8FF;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#2480C8;margin-bottom:10px;">{{group_4_text}}</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;">{{group_4_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 16px;">{{group_4_subtitle}}</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">{{group_4_text_2}}</span>
          </a>
          <a href="{{group_5_button_url}}" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #C9A24B;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#B08A38;margin-bottom:10px;">{{group_5_text}}</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;">{{group_5_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 16px;">{{group_5_subtitle}}</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">{{group_5_text_2}}</span>
          </a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_4_button_url": {"type": "text", "label": "Group 4 Button Url"}, "group_5_button_url": {"type": "text", "label": "Group 5 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_title_2": {"type": "text", "label": "Group 1 Title 2"}, "group_1_subtitle_2": {"type": "textarea", "label": "Group 1 Subtitle 2"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_subtitle": {"type": "textarea", "label": "Group 3 Subtitle"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_4_title": {"type": "text", "label": "Group 4 Title"}, "group_4_subtitle": {"type": "textarea", "label": "Group 4 Subtitle"}, "group_4_text_2": {"type": "textarea", "label": "Group 4 Text 2"}, "group_5_text": {"type": "textarea", "label": "Group 5 Text"}, "group_5_title": {"type": "text", "label": "Group 5 Title"}, "group_5_subtitle": {"type": "textarea", "label": "Group 5 Subtitle"}, "group_5_text_2": {"type": "textarea", "label": "Group 5 Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "/marka-skintech", "group_2_button_url": "/marka-seffiline", "group_3_button_url": "/marka-aespio", "group_4_button_url": "/marka-woorhi", "group_5_button_url": "/marka-mi-medical", "group_1_subtitle": "Temsil Ettiğimiz Markalar", "group_1_title": "Dünyanın önde gelen markaları, Türkiye'de tek adreste", "group_2_description": "Her biri kendi alanında uzman; mezoterapiden cihaza, peelingden ip askıya geniş bir portföy.", "group_1_text": "İspanya", "group_1_title_2": "Skin Tech Pharma", "group_1_subtitle_2": "Peeling, mezoterapi ve RRS skinbooster serisi.", "group_1_text_2": "Keşfet →", "group_2_text": "Cilt & Saç", "group_2_title": "Seffiline", "group_2_subtitle": "Cilt, saç, intim bakım ve dolgu çözümleri.", "group_2_text_2": "Keşfet →", "group_3_text": "K-Beauty", "group_3_title": "Grand Aespio", "group_3_subtitle": "Yüz maskeleri ve ip askı (thread) ürünleri.", "group_3_text_2": "Keşfet →", "group_4_text": "Güney Kore", "group_4_title": "Woorhi Mechatronics", "group_4_subtitle": "Kore mühendisliği medikal estetik cihazları.", "group_4_text_2": "Keşfet →", "group_5_text": "Premium", "group_5_title": "Mi Medical Innovation", "group_5_subtitle": "Premium mezoterapi ve enjeksiyon sistemleri.", "group_5_text_2": "Keşfet →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-home-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'FEATURED PRODUCTS',
             'html_template'=><<<'EDHTML'
<!-- FEATURED PRODUCTS -->

    <section style="padding:84px 0;background:var(--color-secondary,#FBF4EE);">
      <div class="container">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:44px;">
          <div>
            <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 10px;">{{group_1_subtitle}}</p>
            <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">{{group_1_title}}</h2>
          </div>
          <a href="{{group_1_button_url}}" style="color:var(--color-primary,#E8702A);font-weight:700;font-size:15px;white-space:nowrap;">{{group_1_button_text}}</a>
        </div>
        <!-- 🖼️ GÖRSEL (öne çıkan ürün kartları – temsili blok, her kart için aynı desen):
             /assets/img/product-{slug}.jpg (oran 4:3) — örn. product-rrs-ha-long-lasting.jpg, product-melablock-spf50.jpg, product-benebellum-lumina-vitc.jpg, product-beta-glukan-mask.jpg
             PROMPT: "Clean studio product photography of a {ürün adı} professional medical aesthetics product, isolated on a soft cream-to-peach gradient background, premium clinical packaging, subtle soft shadow and reflection, warm terracotta and amber brand lighting, dermatology catalog style, no text, no logo, no watermark, high resolution"
             DEĞİŞTİR → her kartın gradyanlı div'i yerine: <img src="assets/img/product-{slug}.jpg" alt="{ÜRÜN ADI} ürün görseli" style="width:100%;height:100%;object-fit:cover;"> -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px;">
          <a href="{{group_1_button_url_2}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-rrs-ha-long-lasting.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_description}}</p>
            </div>
          </a>
          <a href="{{group_2_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-melablock-hsp-spf-50.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_2}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_2}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_description_2}}</p>
            </div>
          </a>
          <a href="{{group_3_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-benebellum-lumina-vit-c-18.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_3}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_3}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle}}</p>
            </div>
          </a>
          <a href="{{group_4_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-beta-glukan-mask.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#6C5CE0;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_4}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_4}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_2}}</p>
            </div>
          </a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_1_button_url_2": {"type": "text", "label": "Group 1 Button Url 2"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_4_button_url": {"type": "text", "label": "Group 4 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_2_title_2": {"type": "text", "label": "Group 2 Title 2"}, "group_2_description_2": {"type": "textarea", "label": "Group 2 Description 2"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_2_title_3": {"type": "text", "label": "Group 2 Title 3"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_2_title_4": {"type": "text", "label": "Group 2 Title 4"}, "group_2_subtitle_2": {"type": "textarea", "label": "Group 2 Subtitle 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "/urunler", "group_1_button_url_2": "/urun-detay", "group_2_button_url": "/urun-detay", "group_3_button_url": "/urun-detay", "group_4_button_url": "/urun-detay", "group_1_subtitle": "Öne Çıkan Ürünler", "group_1_title": "Kliniğinizin en çok tercih ettikleri", "group_1_button_text": "Tüm ürünler →", "group_2_text": "Skin Tech · RRS", "group_2_title": "RRS® HA Long Lasting", "group_2_description": "Çapraz bağlı hyalüronik asit içeren CE Class III dermal implant.", "group_2_text_2": "Skin Tech · Krem", "group_2_title_2": "Melablock HSP SPF 50+", "group_2_description_2": "Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktör.", "group_2_text_3": "Skin Tech · Mezoterapi", "group_2_title_3": "Benebellum LUMINA VİT-C 18%", "group_2_subtitle": "Yüksek konsantrasyonlu C vitamini ile aydınlatıcı bakım.", "group_2_text_4": "Grand Aespio · Maske", "group_2_title_4": "Beta-Glukan Mask", "group_2_subtitle_2": "Yatıştırıcı ve onarıcı beta-glukan içeren yüz maskesi."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-home-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CATEGORY GRID',
             'html_template'=><<<'EDHTML'
<!-- CATEGORY GRID -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container">
        <div style="text-align:center;max-width:600px;margin:0 auto 48px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">{{group_1_title}}</h2>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;max-width:940px;margin:0 auto;">
          {{{cat_pill_items_html}}}</div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"cat_pill_items": {"type": "repeater", "label": "Cat Pill Items", "repeat_kind": "items", "item_template": "<a href=\"{{button_url}}\" class=\"cat-pill\" style=\"background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);\">{{button_text}}</a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "button_text": {"type": "textarea", "label": "Button Text"}}}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"cat_pill_items": [{"button_url": "/urunler", "button_text": "İp"}, {"button_url": "/urunler", "button_text": "Kanül & İğne Ucu"}, {"button_url": "/urunler", "button_text": "Kimyasal Peeling"}, {"button_url": "/urunler", "button_text": "Kozmetik"}, {"button_url": "/urunler", "button_text": "Kremler"}, {"button_url": "/urunler", "button_text": "Mezoterapi"}, {"button_url": "/urunler", "button_text": "Mezoterapi Tabancası"}, {"button_url": "/urunler", "button_text": "Micro İğneleme"}, {"button_url": "/urunler", "button_text": "Otolog Rejeneratif Terapi"}, {"button_url": "/urunler", "button_text": "Peeling"}, {"button_url": "/urunler", "button_text": "Profesyonel Ürünler"}, {"button_url": "/urunler", "button_text": "RRS"}, {"button_url": "/urunler", "button_text": "Terapi"}, {"button_url": "/urunler", "button_text": "Yüz Maskesi"}], "group_1_subtitle": "Ürün Kategorileri", "group_1_title": "İhtiyacınız olan her şey, 14 kategoride"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-home-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'ABOUT TEASER',
             'html_template'=><<<'EDHTML'
<!-- ABOUT TEASER -->

    <section style="padding:84px 0;background:var(--color-secondary,#FBF4EE);">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:52px;align-items:center;">
        <div style="position:relative;min-height:360px;flex:1 1 320px;">
          <div style="position:absolute;inset:0;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:24px;transform:rotate(2.5deg);"></div>
          <!-- 🖼️ GÖRSEL: /assets/img/about-clinic.jpg (oran 3:2)
               PROMPT: "Bright modern medical aesthetics clinic interior with a professional in a white coat consulting, clean minimalist treatment room, warm natural light, cream and soft terracotta accents, trustworthy premium healthcare atmosphere, photorealistic, no text, no logo, no watermark, high resolution"
               DEĞİŞTİR → <img src="assets/img/about-clinic.jpg" alt="Estetik Dermal modern medikal estetik kliniği ortamı" style="width:100%;height:100%;object-fit:cover;border-radius:24px;"> -->
          <div style="position:relative;height:100%;min-height:360px;border-radius:24px;background:var(--color-secondary,#FBF4EE) url('assets/img/about-clinic.jpg') center/cover no-repeat;border:1px solid var(--border-soft,#ece6df);"></div>
        </div>
        <div style="flex:1 1 420px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 18px;line-height:1.18;">{{group_2_title}}</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.75;margin:0 0 18px;">{{{group_2_body_html}}}</p>
          <ul style="list-style:none;margin:0 0 28px;padding:0;display:grid;gap:12px;">
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">{{group_1_text}}</span> {{group_1_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">{{group_2_text}}</span> {{group_2_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">{{group_3_text}}</span> {{group_3_item_text}}</li>
          </ul>
          <a href="{{group_2_button_url}}" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#E8702A);color:#fff;padding:14px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{group_2_button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": "/hakkimizda", "group_1_subtitle": "Biz Kimiz?", "group_2_title": "2004'ten beri medikal estetikte güvenin adresi", "group_2_body_html": "Estetik Dermal, medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline dağıtıyor. Uluslararası markaların resmi temsilcisi olarak, yalnızca ürün değil; doktorlara uygulamalı eğitim ve teknik destek de sunuyoruz.", "group_1_text": "✓", "group_1_item_text": "Uluslararası markaların resmi Türkiye temsilcisi", "group_2_text": "✓", "group_2_item_text": "CE Class III sertifikalı RRS serisi", "group_3_text": "✓", "group_3_item_text": "Doktorlara uygulamalı eğitim ve teknik destek", "group_2_button_text": "Hakkımızda →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-home-6'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'TRAINING / SUPPORT',
             'html_template'=><<<'EDHTML'
<!-- TRAINING / SUPPORT -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container">
        <div style="text-align:center;max-width:620px;margin:0 auto 52px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">{{group_1_title}}</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;">
          <div style="background:var(--color-secondary,#FBF4EE);border-radius:var(--radius-card,18px);padding:34px 30px;">
            <div style="width:54px;height:54px;border-radius:14px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;margin-bottom:20px;color:var(--color-primary,#E8702A);"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div>
            <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">{{group_1_title_2}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">{{group_1_description}}</p>
          </div>
          <div style="background:var(--color-secondary,#FBF4EE);border-radius:var(--radius-card,18px);padding:34px 30px;">
            <div style="width:54px;height:54px;border-radius:14px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;margin-bottom:20px;color:var(--color-primary,#E8702A);"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 14v-1a8 8 0 0 1 16 0v1"/><path d="M4 14a2 2 0 0 1 2-2h1v6H6a2 2 0 0 1-2-2Zm16 0a2 2 0 0 0-2-2h-1v6h1a2 2 0 0 0 2-2Z"/><path d="M18 18v.5a2.5 2.5 0 0 1-2.5 2.5H12"/></svg></div>
            <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">{{group_2_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">{{group_2_description}}</p>
          </div>
          <div style="background:var(--color-secondary,#FBF4EE);border-radius:var(--radius-card,18px);padding:34px 30px;">
            <div style="width:54px;height:54px;border-radius:14px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;margin-bottom:20px;color:var(--color-primary,#E8702A);"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 5 6v5c0 4 3 7 7 8 4-1 7-4 7-8V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg></div>
            <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">{{group_3_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">{{group_3_description}}</p>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_title_2": {"type": "text", "label": "Group 1 Title 2"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_subtitle": "Neden Estetik Dermal?", "group_1_title": "Sadece ürün değil, uçtan uca destek", "group_1_title_2": "Uygulamalı Eğitim", "group_1_description": "Hekimlere ürünlerin doğru ve güvenli kullanımı için birebir, uygulamalı eğitim programları.", "group_2_title": "Teknik Destek", "group_2_description": "Cihaz kurulumundan kullanım sürecine kadar kesintisiz teknik danışmanlık ve servis.", "group_3_title": "Orijinallik Garantisi", "group_3_description": "Resmi temsilci güvencesiyle %100 orijinal, sertifikalı ve takip edilebilir ürünler."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-home-7'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CTA',
             'html_template'=><<<'EDHTML'
<!-- CTA -->

    <section style="padding:20px 0 84px;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:#262220;padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-70px;right:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.30),transparent 70%);"></div>
          <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.16),transparent 70%);"></div>
          <div style="position:relative;">
            <span style="display:inline-block;color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">{{group_3_text}}</span>
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.18;">{{group_3_title}} <span style="color:var(--color-accent,#F4A14E);">{{group_3_text_2}}</span></h2>
            <p style="font-size:18px;color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 34px;line-height:1.65;">{{group_3_description}}</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="{{group_1_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_1_button_text}}</a>
              <a href="{{group_2_button_url}}" target="_blank" rel="noopener" style="background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.32);padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{group_2_button_text}}</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "https://wa.me/905426205100", "group_2_button_url": "https://apps.skintechpharmagroup.com/medinet", "group_3_text": "Bize Ulaşın", "group_3_title": "Profesyonel çözümler için", "group_3_text_2": "buradayız", "group_3_description": "Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.", "group_1_button_text": "WhatsApp ile Yaz", "group_2_button_text": "MEDINET Portalı ↗"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-hakkimizda-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PAGE HERO (compact başlık bandı)',
             'html_template'=><<<'EDHTML'
<!-- PAGE HERO (compact başlık bandı) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(900px 480px at 85% -20%, rgba(244,161,78,.26), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);border-bottom:1px solid var(--border-soft,#ece6df);">
      <div class="container" style="padding:54px 0 60px;">
        <nav aria-label="Breadcrumb" style="margin:0 0 18px;">
          <ol style="list-style:none;display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:0;padding:0;font-size:14px;color:var(--text-soft,#6b6b6b);">
            <li><a href="{{group_1_button_url}}" style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{group_1_button_text}}</a></li>
            <li aria-hidden="true" style="color:var(--border-soft,#ece6df);">{{group_2_item_text}}</li>
            <li aria-current="page" style="color:var(--color-primary,#E8702A);font-weight:700;">{{group_3_item_text}}</li>
          </ol>
        </nav>
        <h1 style="font-size:clamp(32px,5vw,52px);line-height:1.1;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 16px;">{{title}}</h1>
        <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:640px;margin:0;">{{{body_html}}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "/", "group_1_button_text": "Ana Sayfa", "group_2_item_text": "/", "group_3_item_text": "Hakkımızda", "title": "Hakkımızda", "body_html": "2004'ten bu yana medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline ulaştıran resmi distribütör."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-hakkimizda-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'ABOUT STORY (2 kolon split)',
             'html_template'=><<<'EDHTML'
<!-- ABOUT STORY (2 kolon split) -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:52px;align-items:center;">
        <div style="position:relative;min-height:380px;flex:1 1 380px;">
          <div style="position:absolute;inset:0;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));border-radius:24px;opacity:.14;transform:rotate(2deg);"></div>
          <!-- 🖼️ GÖRSEL: /assets/img/about-story.jpg (oran 3:2)
               PROMPT: "Professional medical aesthetics distributor team in a modern showroom presenting skincare and mesotherapy products to doctors, clean bright interior, warm cream and terracotta tones, premium trustworthy corporate healthcare atmosphere, photorealistic, no text, no logo, no watermark, high resolution"
               DEĞİŞTİR → <img src="assets/img/about-story.jpg" alt="Estetik Dermal ekibi modern showroom'da ürün tanıtımı yaparken" style="width:100%;height:100%;object-fit:cover;border-radius:24px;"> -->
          <div style="position:relative;height:100%;min-height:380px;border-radius:24px;background:var(--color-secondary,#FBF4EE) url('assets/img/about-story.jpg') center/cover no-repeat;border:1px solid var(--border-soft,#ece6df);display:grid;place-items:center;color:var(--color-primary,#E8702A);"><svg width="76" height="76" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="opacity:.85;"><path d="M3 21V8l9-5 9 5v13"/><path d="M3 21h18"/><path d="M9 21v-5a3 3 0 0 1 6 0v5"/><path d="M12 7v4"/><path d="M10 9h4"/></svg></div>
        </div>
        <div style="flex:1 1 380px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 18px;line-height:1.18;">{{group_2_title}}</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.75;margin:0 0 18px;">{{{group_2_body_html}}}</p>
          <ul style="list-style:none;margin:0;padding:0;display:grid;gap:12px;">
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">{{group_1_text}}</span> {{group_1_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">{{group_2_text}}</span> {{group_2_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">{{group_3_text}}</span> {{group_3_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">{{group_4_text}}</span> {{group_4_item_text}}</li>
          </ul>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_4_item_text": {"type": "textarea", "label": "Group 4 Item Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_subtitle": "Biz Kimiz?", "group_2_title": "2004'ten beri medikal estetikte güvenin adresi", "group_2_body_html": "2004'ten bu yana medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline dağıtıyoruz. Uluslararası markaların resmi temsilcisi olarak yalnızca ürün değil, doktorlara uygulamalı eğitim ve teknik destek de sunuyoruz.", "group_1_text": "✓", "group_1_item_text": "Uluslararası markaların resmi Türkiye temsilcisi", "group_2_text": "✓", "group_2_item_text": "CE Class III sertifikalı RRS serisi", "group_3_text": "✓", "group_3_item_text": "Doktorlara birebir, uygulamalı eğitim", "group_4_text": "✓", "group_4_item_text": "Türkiye geneli, 81 ile dağıtım ağı"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-hakkimizda-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'VİZYON & MİSYON (2 kart)',
             'html_template'=><<<'EDHTML'
<!-- VİZYON & MİSYON (2 kart) -->

    <section style="padding:84px 0;background:var(--color-secondary,#FBF4EE);">
      <div class="container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;">
          <div style="background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:38px 34px;">
            <div style="width:56px;height:56px;border-radius:16px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;color:var(--color-primary,#E8702A);margin-bottom:20px;"><svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.2" fill="currentColor" stroke="none"/></svg></div>
            <h3 style="font-size:21px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 12px;">{{group_1_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.75;margin:0;">{{group_1_description}}</p>
          </div>
          <div style="background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:38px 34px;">
            <div style="width:56px;height:56px;border-radius:16px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;color:var(--color-primary,#E8702A);margin-bottom:20px;"><svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 15c-1.5 1.3-2 5-2 5s3.7-.5 5-2a2.6 2.6 0 0 0-3-3Z"/><path d="M9 13c2-5 5-8 11-8 0 6-3 9-8 11"/><path d="M9 13l-3-1a14 14 0 0 1 3-3l3 .5"/><path d="M11 15l1 3a14 14 0 0 0 3-3l-.5-3"/></svg></div>
            <h3 style="font-size:21px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 12px;">{{group_2_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.75;margin:0;">{{{group_2_body_html}}}</p>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_title": "Vizyonumuz", "group_1_description": "Medikal estetikte kalite ve güvenin simgesi olarak sektördeki uzmanlık seviyesini yükseltmek.", "group_2_title": "Misyonumuz", "group_2_body_html": "Hastaların cilt sağlığını güçlendirecek profesyonel çözümler sağlamak ve invazif olmayan gençleştirme yöntemlerinde öncü rol oynamak."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-hakkimizda-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'STATS BAR',
             'html_template'=><<<'EDHTML'
<!-- STATS BAR -->

    <section style="background:#fff;">
      <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:24px;padding:48px 0;border-bottom:1px solid var(--border-soft,#ece6df);">
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">{{group_1_text}}</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{group_2_text}}</div></div>
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">{{group_1_text_2}}</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{group_2_text_2}}</div></div>
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">{{group_1_text_3}}</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{group_2_text_3}}</div></div>
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">{{group_1_text_4}}</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{group_2_text_4}}</div></div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_text": "20+", "group_2_text": "Yıllık Deneyim", "group_1_text_2": "5+", "group_2_text_2": "Uluslararası Marka", "group_1_text_3": "95+", "group_2_text_3": "Profesyonel Ürün", "group_1_text_4": "81", "group_2_text_4": "İl Dağıtım Ağı"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-hakkimizda-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PORTFOLIO BRANDS',
             'html_template'=><<<'EDHTML'
<!-- PORTFOLIO BRANDS -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container">
        <div style="text-align:center;max-width:640px;margin:0 auto 52px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(28px,4vw,40px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 14px;line-height:1.15;">{{group_1_title}}</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.7;">{{group_2_description}}</p>
        </div>
        <!-- 🖼️ GÖRSEL (opsiyonel marka logo rozetleri – temsili blok, her kartta harf-tile yerine):
             /assets/img/brand-{slug}.jpg (oran 1:1) — örn. brand-skintech.jpg, brand-mi-medical.jpg, brand-neogenesis.jpg, brand-seffiline.jpg, brand-woorhi.jpg, brand-aespio.jpg
             PROMPT: "Minimalist square brand emblem tile for a medical aesthetics brand, single bold monogram on a flat solid brand-color background, clean modern flat design, soft subtle gradient, professional pharma identity look, no text, no logo, no watermark, high resolution"
             DEĞİŞTİR → her kartın harf-tile div'i yerine: <img src="assets/img/brand-{slug}.jpg" alt="{MARKA ADI} logosu" style="width:52px;height:52px;object-fit:cover;border-radius:14px;"> -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:22px;">
          <a href="{{group_1_button_url}}" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #0C6E72;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#0C6E72;margin-bottom:10px;">{{group_1_text}}</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">{{group_1_title_2}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">{{group_1_subtitle_2}}</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">{{group_1_text_2}}</span>
          </a>
          <a href="{{group_2_button_url}}" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #C9A24B;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#B08A38;margin-bottom:10px;">{{group_2_text}}</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">{{group_2_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">{{group_2_subtitle}}</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">{{group_2_text_2}}</span>
          </a>
          <div style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #2BA39A;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#2BA39A;margin-bottom:10px;">{{group_2_text_3}}</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">{{group_2_title_2}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">{{group_2_subtitle_2}}</p>
            <span style="color:var(--text-soft,#6b6b6b);font-weight:700;font-size:14px;">{{group_2_text_4}}</span>
          </div>
          <a href="{{group_3_button_url}}" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #C98A6D;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#C98A6D;margin-bottom:10px;">{{group_3_text}}</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">{{group_3_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">{{group_3_subtitle}}</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">{{group_3_text_2}}</span>
          </a>
          <a href="{{group_4_button_url}}" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #2DA8FF;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#2480C8;margin-bottom:10px;">{{group_4_text}}</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">{{group_4_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">{{group_4_subtitle}}</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">{{group_4_text_2}}</span>
          </a>
          <a href="{{group_5_button_url}}" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #6C5CE0;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#6C5CE0;margin-bottom:10px;">{{group_5_text}}</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">{{group_5_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">{{group_5_subtitle}}</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">{{group_5_text_2}}</span>
          </a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_4_button_url": {"type": "text", "label": "Group 4 Button Url"}, "group_5_button_url": {"type": "text", "label": "Group 5 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_title_2": {"type": "text", "label": "Group 1 Title 2"}, "group_1_subtitle_2": {"type": "textarea", "label": "Group 1 Subtitle 2"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_2_title_2": {"type": "text", "label": "Group 2 Title 2"}, "group_2_subtitle_2": {"type": "textarea", "label": "Group 2 Subtitle 2"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_subtitle": {"type": "textarea", "label": "Group 3 Subtitle"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_4_title": {"type": "text", "label": "Group 4 Title"}, "group_4_subtitle": {"type": "textarea", "label": "Group 4 Subtitle"}, "group_4_text_2": {"type": "textarea", "label": "Group 4 Text 2"}, "group_5_text": {"type": "textarea", "label": "Group 5 Text"}, "group_5_title": {"type": "text", "label": "Group 5 Title"}, "group_5_subtitle": {"type": "textarea", "label": "Group 5 Subtitle"}, "group_5_text_2": {"type": "textarea", "label": "Group 5 Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "/marka-skintech", "group_2_button_url": "/marka-mi-medical", "group_3_button_url": "/marka-seffiline", "group_4_button_url": "/marka-woorhi", "group_5_button_url": "/marka-aespio", "group_1_subtitle": "Portföyümüz", "group_1_title": "Temsil ettiğimiz uluslararası markalar", "group_2_description": "Her biri kendi alanında uzman; mezoterapiden cihaza, peelingden ip askıya geniş bir ürün yelpazesi.", "group_1_text": "İspanya", "group_1_title_2": "Skin Tech Pharma", "group_1_subtitle_2": "Peeling, mezoterapi ve RRS skinbooster serisi.", "group_1_text_2": "Keşfet →", "group_2_text": "Premium", "group_2_title": "Mi Medical Innovation", "group_2_subtitle": "Premium mezoterapi ve enjeksiyon sistemleri.", "group_2_text_2": "Keşfet →", "group_2_text_3": "Kök Hücre", "group_2_title_2": "Neogenesis", "group_2_subtitle_2": "Kök hücre teknolojili profesyonel cilt bakım serisi.", "group_2_text_4": "Portföyümüzde", "group_3_text": "Cilt & Saç", "group_3_title": "Seffiline", "group_3_subtitle": "Cilt, saç, intim bakım ve dolgu çözümleri.", "group_3_text_2": "Keşfet →", "group_4_text": "Güney Kore", "group_4_title": "Woorhi Mechatronics", "group_4_subtitle": "Kore mühendisliği medikal estetik cihazları.", "group_4_text_2": "Keşfet →", "group_5_text": "K-Beauty", "group_5_title": "Grand Aespio", "group_5_subtitle": "Yüz maskeleri ve ip askı (thread) ürünleri.", "group_5_text_2": "Keşfet →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-hakkimizda-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'TRAINING / SUPPORT',
             'html_template'=><<<'EDHTML'
<!-- TRAINING / SUPPORT -->

    <section style="padding:84px 0;background:var(--color-secondary,#FBF4EE);">
      <div class="container">
        <div style="text-align:center;max-width:620px;margin:0 auto 52px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">{{group_1_title}}</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;">
          <div style="background:#fff;border-radius:var(--radius-card,18px);padding:34px 30px;border:1px solid var(--border-soft,#ece6df);">
            <div style="width:56px;height:56px;border-radius:16px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;color:var(--color-primary,#E8702A);margin-bottom:20px;"><svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div>
            <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">{{group_1_title_2}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">{{group_1_description}}</p>
          </div>
          <div style="background:#fff;border-radius:var(--radius-card,18px);padding:34px 30px;border:1px solid var(--border-soft,#ece6df);">
            <div style="width:56px;height:56px;border-radius:16px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;color:var(--color-primary,#E8702A);margin-bottom:20px;"><svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 14v-1a8 8 0 0 1 16 0v1"/><path d="M4 14a2 2 0 0 1 2-2h1v6H6a2 2 0 0 1-2-2Zm16 0a2 2 0 0 0-2-2h-1v6h1a2 2 0 0 0 2-2Z"/><path d="M18 18v.5a2.5 2.5 0 0 1-2.5 2.5H12"/></svg></div>
            <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">{{group_2_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">{{group_2_description}}</p>
          </div>
          <div style="background:#fff;border-radius:var(--radius-card,18px);padding:34px 30px;border:1px solid var(--border-soft,#ece6df);">
            <div style="width:56px;height:56px;border-radius:16px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;color:var(--color-primary,#E8702A);margin-bottom:20px;"><svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 5 6v5c0 4 3 7 7 8 4-1 7-4 7-8V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg></div>
            <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">{{group_3_title}}</h3>
            <p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">{{group_3_description}}</p>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_title_2": {"type": "text", "label": "Group 1 Title 2"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_subtitle": "Neden Estetik Dermal?", "group_1_title": "Sadece ürün değil, uçtan uca destek", "group_1_title_2": "Uygulamalı Eğitim", "group_1_description": "Hekimlere ürünlerin doğru ve güvenli kullanımı için birebir, uygulamalı eğitim programları.", "group_2_title": "Teknik Destek", "group_2_description": "Cihaz kurulumundan kullanım sürecine kadar kesintisiz teknik danışmanlık ve servis.", "group_3_title": "Orijinallik Garantisi", "group_3_description": "Resmi temsilci güvencesiyle %100 orijinal, sertifikalı ve takip edilebilir ürünler."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-hakkimizda-6'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CTA',
             'html_template'=><<<'EDHTML'
<!-- CTA -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:#262220;padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-70px;right:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.30),transparent 70%);"></div>
          <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.16),transparent 70%);"></div>
          <div style="position:relative;">
            <span style="display:inline-block;color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">{{group_3_text}}</span>
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.18;">{{group_3_title}} <span style="color:var(--color-accent,#F4A14E);">{{group_3_text_2}}</span></h2>
            <p style="font-size:18px;color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 34px;line-height:1.65;">{{group_3_description}}</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="{{group_1_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_1_button_text}}</a>
              <a href="{{group_2_button_url}}" target="_blank" rel="noopener" style="background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.32);padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{group_2_button_text}}</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "https://wa.me/905426205100", "group_2_button_url": "https://apps.skintechpharmagroup.com/medinet", "group_3_text": "Bize Ulaşın", "group_3_title": "Profesyonel çözümler için", "group_3_text_2": "buradayız", "group_3_description": "Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.", "group_1_button_text": "WhatsApp ile Yaz", "group_2_button_text": "MEDINET Portalı ↗"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-urunler-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PAGE HERO (compact)',
             'html_template'=><<<'EDHTML'
<!-- PAGE HERO (compact) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(900px 400px at 85% -20%, rgba(244,161,78,.26), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);">
      <div class="container" style="padding:48px 0 56px;">
        <nav aria-label="Breadcrumb" style="font-size:13.5px;color:var(--text-soft,#6b6b6b);margin:0 0 16px;">
          <a href="{{button_url}}" style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{button_text}}</a>
          <span style="margin:0 8px;opacity:.6;">{{group_1_text}}</span>
          <span style="color:var(--color-primary,#E8702A);font-weight:700;">{{group_2_text}}</span>
        </nav>
        <h1 style="font-size:clamp(30px,4.5vw,46px);line-height:1.1;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 14px;">{{title}}</h1>
        <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:560px;margin:0;">{{subtitle}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "button_text": {"type": "textarea", "label": "Button Text"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "title": {"type": "text", "label": "Title"}, "subtitle": {"type": "textarea", "label": "Subtitle"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "/", "button_text": "Ana Sayfa", "group_1_text": "/", "group_2_text": "Ürünler", "title": "Ürünler", "subtitle": "95+ profesyonel medikal estetik ürünü, 14 kategoride."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-urunler-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'FILTER CHIPS (görsel, statik)',
             'html_template'=><<<'EDHTML'
<!-- FILTER CHIPS (görsel, statik) -->

    <section style="background:#fff;border-bottom:1px solid var(--border-soft,#ece6df);">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:10px;padding:24px 0;">
        <a href="{{group_1_button_url}}" style="display:inline-flex;align-items:center;background:var(--color-primary,#E8702A);color:#fff;border:1.5px solid var(--color-primary,#E8702A);border-radius:999px;padding:9px 20px;font-weight:700;font-size:14px;box-shadow:0 6px 18px rgba(232,112,42,.26);">{{group_1_button_text}}</a>
        <a href="{{group_2_button_url}}" style="display:inline-flex;align-items:center;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:9px 20px;font-weight:700;font-size:14px;">{{group_2_button_text}}</a>
        <a href="{{group_3_button_url}}" style="display:inline-flex;align-items:center;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:9px 20px;font-weight:700;font-size:14px;">{{group_3_button_text}}</a>
        <a href="{{group_4_button_url}}" style="display:inline-flex;align-items:center;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:9px 20px;font-weight:700;font-size:14px;">{{group_4_button_text}}</a>
        <a href="{{group_5_button_url}}" style="display:inline-flex;align-items:center;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:9px 20px;font-weight:700;font-size:14px;">{{group_5_button_text}}</a>
        <a href="{{group_6_button_url}}" style="display:inline-flex;align-items:center;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:9px 20px;font-weight:700;font-size:14px;">{{group_6_button_text}}</a>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_4_button_url": {"type": "text", "label": "Group 4 Button Url"}, "group_5_button_url": {"type": "text", "label": "Group 5 Button Url"}, "group_6_button_url": {"type": "text", "label": "Group 6 Button Url"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "group_3_button_text": {"type": "textarea", "label": "Group 3 Button Text"}, "group_4_button_text": {"type": "textarea", "label": "Group 4 Button Text"}, "group_5_button_text": {"type": "textarea", "label": "Group 5 Button Text"}, "group_6_button_text": {"type": "textarea", "label": "Group 6 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "/urunler", "group_2_button_url": "/urunler", "group_3_button_url": "/urunler", "group_4_button_url": "/urunler", "group_5_button_url": "/urunler", "group_6_button_url": "/urunler", "group_1_button_text": "Tümü", "group_2_button_text": "Skin Tech", "group_3_button_text": "Seffiline", "group_4_button_text": "Grand Aespio", "group_5_button_text": "Woorhi", "group_6_button_text": "Mi Medical"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-urunler-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CATEGORY GRID (14 kategori)',
             'html_template'=><<<'EDHTML'
<!-- CATEGORY GRID (14 kategori) -->

    <section style="padding:64px 0 56px;background:#fff;">
      <div class="container">
        <div style="text-align:center;max-width:600px;margin:0 auto 40px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">{{group_1_title}}</h2>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;max-width:940px;margin:0 auto;">
          {{{cat_pill_items_html}}}</div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"cat_pill_items": {"type": "repeater", "label": "Cat Pill Items", "repeat_kind": "items", "item_template": "<a href=\"{{button_url}}\" class=\"cat-pill\" style=\"background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);\">{{button_text}}</a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "button_text": {"type": "textarea", "label": "Button Text"}}}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"cat_pill_items": [{"button_url": "/urunler", "button_text": "İp"}, {"button_url": "/urunler", "button_text": "Kanül & İğne Ucu"}, {"button_url": "/urunler", "button_text": "Kimyasal Peeling"}, {"button_url": "/urunler", "button_text": "Kozmetik"}, {"button_url": "/urunler", "button_text": "Kremler"}, {"button_url": "/urunler", "button_text": "Mezoterapi"}, {"button_url": "/urunler", "button_text": "Mezoterapi Tabancası"}, {"button_url": "/urunler", "button_text": "Micro İğneleme"}, {"button_url": "/urunler", "button_text": "Otolog Rejeneratif Terapi"}, {"button_url": "/urunler", "button_text": "Peeling"}, {"button_url": "/urunler", "button_text": "Profesyonel Ürünler"}, {"button_url": "/urunler", "button_text": "RRS"}, {"button_url": "/urunler", "button_text": "Terapi"}, {"button_url": "/urunler", "button_text": "Yüz Maskesi"}], "group_1_subtitle": "Ürün Kategorileri", "group_1_title": "İhtiyacınız olan her şey, 14 kategoride"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-urunler-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PRODUCT GRID',
             'html_template'=><<<'EDHTML'
<!-- PRODUCT GRID -->

    <section style="padding:8px 0 72px;background:#fff;">
      <div class="container">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:36px;">
          <div>
            <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 10px;">{{group_1_subtitle}}</p>
            <h2 style="font-size:clamp(26px,4vw,36px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">{{group_1_title}}</h2>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px;">

          <!-- ════════════════════════════════════════════════════════════════════
               🖼️ GÖRSEL DESENİ — ÜRÜN KART GÖRSELLERİ (her kart için tekrarlanır)
               Aşağıdaki her ürün kartının üstündeki gradyan+emoji kutusu
               (aspect-ratio:4/3) bir görsel placeholder'dır. Üretilen görselleri
               şu isimlendirme deseniyle adlandırın: /assets/img/product-{slug}.jpg
               Örn: product-rrs-ha-long-lasting.jpg, product-melablock-hsp-spf50.jpg,
                    product-benebellum-lumina-vitc.jpg, product-aclaranse.jpg,
                    product-actilift.jpg, product-seffihair.jpg, product-raffine.jpg ...

               GENEL ÜRÜN KARTI PROMPTU (oran 4:3):
               PROMPT: "Professional studio product photography of a single medical
               aesthetic / dermatology product package — sleek pharmaceutical box and
               glass vial or syringe — centered on a clean white-to-cream seamless
               background, soft diffused studio lighting, gentle reflection on a glossy
               surface, premium clinical look, warm orange and cream accent tones,
               shallow depth of field, photorealistic, high resolution, no text,
               no logo, no watermark"

               DEĞİŞTİR → her kartta şu satırı:
                 <div style="aspect-ratio:4/3;background:linear-gradient(135deg,...);display:grid;place-items:center;font-size:52px;">EMOJİ</div>
               şununla:
                 <img src="assets/img/product-{slug}.jpg" alt="{ÜRÜN ADI} ürün ambalaj görseli" style="width:100%;height:100%;aspect-ratio:4/3;object-fit:cover;">
          ════════════════════════════════════════════════════════════════════ -->

          <!-- Skin Tech -->
          <!-- 🖼️ GÖRSEL: /assets/img/product-rrs-ha-long-lasting.jpg (oran 4:3) — ÖNE ÇIKAN ÜRÜN
               PROMPT: "Professional studio product photography of a premium cross-linked
               hyaluronic acid dermal implant kit: a sterile pre-filled medical syringe
               beside an elegant pharmaceutical box, on a clean white-to-cream seamless
               background, soft diffused clinical lighting, subtle water-drop dewy
               freshness, blue-teal and warm cream accent tones, glossy reflective
               surface, photorealistic, high resolution, no text, no logo, no watermark"
               DEĞİŞTİR → <img src="assets/img/product-rrs-ha-long-lasting.jpg" alt="RRS HA Long Lasting ürün ambalaj görseli" style="width:100%;height:100%;aspect-ratio:4/3;object-fit:cover;"> -->
          <a href="{{group_1_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-rrs-ha-long-lasting.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle}}</p>
            </div>
          </a>

          <a href="{{group_2_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-melablock-hsp-spf-50.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_2}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_2}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_2}}</p>
            </div>
          </a>

          <a href="{{group_3_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-benebellum-lumina-vit-c-18.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_3}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_3}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_3}}</p>
            </div>
          </a>

          <a href="{{group_4_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-benebellum-lumina-vit-a-e.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_4}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_4}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_4}}</p>
            </div>
          </a>

          <a href="{{group_5_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-benebellum-tx-solution.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_5}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_5}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_5}}</p>
            </div>
          </a>

          <a href="{{group_6_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-aclaranse.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_6}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_6}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_6}}</p>
            </div>
          </a>

          <a href="{{group_7_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-actilift.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_7}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_7}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_7}}</p>
            </div>
          </a>

          <a href="{{group_8_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-atrofillin.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_8}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_8}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_8}}</p>
            </div>
          </a>

          <!-- Grand Aespio -->
          <a href="{{group_9_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-beta-glukan-mask.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#6C5CE0;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_9}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_9}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_9}}</p>
            </div>
          </a>

          <a href="{{group_10_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-hyaluronic-acid-mask.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#6C5CE0;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_10}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_10}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_10}}</p>
            </div>
          </a>

          <a href="{{group_11_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-lfl-anchor.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#6C5CE0;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_11}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_11}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_11}}</p>
            </div>
          </a>

          <!-- Seffiline -->
          <a href="{{group_12_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-seffihair.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#C98A6D;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_12}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_12}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_12}}</p>
            </div>
          </a>

          <a href="{{group_13_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-sefficare.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#C98A6D;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_13}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_13}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_13}}</p>
            </div>
          </a>

          <a href="{{group_14_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-seffiller.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#C98A6D;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_14}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_14}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_14}}</p>
            </div>
          </a>

          <!-- Woorhi -->
          <!-- 🖼️ GÖRSEL: /assets/img/product-raffine.jpg (oran 4:3) — CİHAZ (farklı görünür)
               PROMPT: "Professional studio product photography of a sleek modern Korean
               mesotherapy injection gun device, ergonomic white and silver medical
               handpiece, on a clean light-blue-to-white seamless background, soft
               diffused studio lighting, high-tech premium clinical aesthetic, subtle
               reflection, photorealistic, high resolution, no text, no logo, no watermark"
               DEĞİŞTİR → <img src="assets/img/product-raffine.jpg" alt="Woorhi Raffine mezoterapi tabancası cihaz görseli" style="width:100%;height:100%;aspect-ratio:4/3;object-fit:cover;"> -->
          <a href="{{group_15_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-raffine.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#2DA8FF;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_15}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_15}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_15}}</p>
            </div>
          </a>

          <!-- Mi Medical -->
          <!-- 🖼️ GÖRSEL: /assets/img/product-pistor-eliance.jpg (oran 4:3) — CİHAZ (farklı görünür)
               PROMPT: "Professional studio product photography of a premium professional
               mesotherapy injection device (Pistor-style), elegant white and gold-cream
               medical handpiece, on a clean warm cream-to-white seamless background, soft
               diffused studio lighting, luxurious high-end clinical aesthetic, subtle
               reflection, photorealistic, high resolution, no text, no logo, no watermark"
               DEĞİŞTİR → <img src="assets/img/product-pistor-eliance.jpg" alt="Mi Medical Pistor Eliance enjeksiyon sistemi cihaz görseli" style="width:100%;height:100%;aspect-ratio:4/3;object-fit:cover;"> -->
          <a href="{{group_16_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-pistor-eliance.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#C9A24B;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_16}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_16}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle_16}}</p>
            </div>
          </a>

        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_4_button_url": {"type": "text", "label": "Group 4 Button Url"}, "group_5_button_url": {"type": "text", "label": "Group 5 Button Url"}, "group_6_button_url": {"type": "text", "label": "Group 6 Button Url"}, "group_7_button_url": {"type": "text", "label": "Group 7 Button Url"}, "group_8_button_url": {"type": "text", "label": "Group 8 Button Url"}, "group_9_button_url": {"type": "text", "label": "Group 9 Button Url"}, "group_10_button_url": {"type": "text", "label": "Group 10 Button Url"}, "group_11_button_url": {"type": "text", "label": "Group 11 Button Url"}, "group_12_button_url": {"type": "text", "label": "Group 12 Button Url"}, "group_13_button_url": {"type": "text", "label": "Group 13 Button Url"}, "group_14_button_url": {"type": "text", "label": "Group 14 Button Url"}, "group_15_button_url": {"type": "text", "label": "Group 15 Button Url"}, "group_16_button_url": {"type": "text", "label": "Group 16 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_2_title_2": {"type": "text", "label": "Group 2 Title 2"}, "group_2_subtitle_2": {"type": "textarea", "label": "Group 2 Subtitle 2"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_2_title_3": {"type": "text", "label": "Group 2 Title 3"}, "group_2_subtitle_3": {"type": "textarea", "label": "Group 2 Subtitle 3"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_2_title_4": {"type": "text", "label": "Group 2 Title 4"}, "group_2_subtitle_4": {"type": "textarea", "label": "Group 2 Subtitle 4"}, "group_2_text_5": {"type": "textarea", "label": "Group 2 Text 5"}, "group_2_title_5": {"type": "text", "label": "Group 2 Title 5"}, "group_2_subtitle_5": {"type": "textarea", "label": "Group 2 Subtitle 5"}, "group_2_text_6": {"type": "textarea", "label": "Group 2 Text 6"}, "group_2_title_6": {"type": "text", "label": "Group 2 Title 6"}, "group_2_subtitle_6": {"type": "textarea", "label": "Group 2 Subtitle 6"}, "group_2_text_7": {"type": "textarea", "label": "Group 2 Text 7"}, "group_2_title_7": {"type": "text", "label": "Group 2 Title 7"}, "group_2_subtitle_7": {"type": "textarea", "label": "Group 2 Subtitle 7"}, "group_2_text_8": {"type": "textarea", "label": "Group 2 Text 8"}, "group_2_title_8": {"type": "text", "label": "Group 2 Title 8"}, "group_2_subtitle_8": {"type": "textarea", "label": "Group 2 Subtitle 8"}, "group_2_text_9": {"type": "textarea", "label": "Group 2 Text 9"}, "group_2_title_9": {"type": "text", "label": "Group 2 Title 9"}, "group_2_subtitle_9": {"type": "textarea", "label": "Group 2 Subtitle 9"}, "group_2_text_10": {"type": "textarea", "label": "Group 2 Text 10"}, "group_2_title_10": {"type": "text", "label": "Group 2 Title 10"}, "group_2_subtitle_10": {"type": "textarea", "label": "Group 2 Subtitle 10"}, "group_2_text_11": {"type": "textarea", "label": "Group 2 Text 11"}, "group_2_title_11": {"type": "text", "label": "Group 2 Title 11"}, "group_2_subtitle_11": {"type": "textarea", "label": "Group 2 Subtitle 11"}, "group_2_text_12": {"type": "textarea", "label": "Group 2 Text 12"}, "group_2_title_12": {"type": "text", "label": "Group 2 Title 12"}, "group_2_subtitle_12": {"type": "textarea", "label": "Group 2 Subtitle 12"}, "group_2_text_13": {"type": "textarea", "label": "Group 2 Text 13"}, "group_2_title_13": {"type": "text", "label": "Group 2 Title 13"}, "group_2_subtitle_13": {"type": "textarea", "label": "Group 2 Subtitle 13"}, "group_2_text_14": {"type": "textarea", "label": "Group 2 Text 14"}, "group_2_title_14": {"type": "text", "label": "Group 2 Title 14"}, "group_2_subtitle_14": {"type": "textarea", "label": "Group 2 Subtitle 14"}, "group_2_text_15": {"type": "textarea", "label": "Group 2 Text 15"}, "group_2_title_15": {"type": "text", "label": "Group 2 Title 15"}, "group_2_subtitle_15": {"type": "textarea", "label": "Group 2 Subtitle 15"}, "group_2_text_16": {"type": "textarea", "label": "Group 2 Text 16"}, "group_2_title_16": {"type": "text", "label": "Group 2 Title 16"}, "group_2_subtitle_16": {"type": "textarea", "label": "Group 2 Subtitle 16"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "/urun-detay", "group_2_button_url": "/urun-detay", "group_3_button_url": "/urun-detay", "group_4_button_url": "/urun-detay", "group_5_button_url": "/urun-detay", "group_6_button_url": "/urun-detay", "group_7_button_url": "/urun-detay", "group_8_button_url": "/urun-detay", "group_9_button_url": "/urun-detay", "group_10_button_url": "/urun-detay", "group_11_button_url": "/urun-detay", "group_12_button_url": "/urun-detay", "group_13_button_url": "/urun-detay", "group_14_button_url": "/urun-detay", "group_15_button_url": "/urun-detay", "group_16_button_url": "/urun-detay", "group_1_subtitle": "Tüm Ürünler", "group_1_title": "Profesyonel medikal estetik portföyü", "group_2_text": "Skin Tech · RRS", "group_2_title": "RRS® HA Long Lasting", "group_2_subtitle": "Çapraz bağlı HA içeren CE Class III dermal implant.", "group_2_text_2": "Skin Tech · Krem", "group_2_title_2": "Melablock HSP SPF 50+", "group_2_subtitle_2": "360° güneş koruması.", "group_2_text_3": "Skin Tech · Mezoterapi", "group_2_title_3": "Benebellum LUMINA VİT-C 18%", "group_2_subtitle_3": "Yüksek konsantrasyonlu C vitamini.", "group_2_text_4": "Skin Tech · Mezoterapi", "group_2_title_4": "Benebellum LUMINA VİT. A+E", "group_2_subtitle_4": "A ve E vitamini ile besleyici, antioksidan bakım.", "group_2_text_5": "Skin Tech · Mezoterapi", "group_2_title_5": "Benebellum TX SOLUTION", "group_2_subtitle_5": "Traneksamik asit içeren leke karşıtı aydınlatıcı solüsyon.", "group_2_text_6": "Skin Tech · Peeling", "group_2_title_6": "Aclaranse", "group_2_subtitle_6": "Lekeli ciltler için aydınlatıcı profesyonel peeling çözümü.", "group_2_text_7": "Skin Tech · İp", "group_2_title_7": "Actilift", "group_2_subtitle_7": "Yüz ve boyunda anlık toparlama için ip askı sistemi.", "group_2_text_8": "Skin Tech · Mezoterapi", "group_2_title_8": "Atrofillin", "group_2_subtitle_8": "Atrofik izler ve cilt onarımı için mezoterapi solüsyonu.", "group_2_text_9": "Grand Aespio · Yüz Maskesi", "group_2_title_9": "Beta-Glukan Mask", "group_2_subtitle_9": "Yatıştırıcı ve onarıcı beta-glukan içeren yüz maskesi.", "group_2_text_10": "Grand Aespio · Yüz Maskesi", "group_2_title_10": "Hyaluronic Acid Mask", "group_2_subtitle_10": "Yoğun nem ve dolgunluk veren hyalüronik asit maskesi.", "group_2_text_11": "Grand Aespio · İp", "group_2_title_11": "LFL Anchor", "group_2_subtitle_11": "Güçlü tutuş sağlayan çapalı askı (anchor) ip serisi.", "group_2_text_12": "Seffiline · Mezoterapi", "group_2_title_12": "SeffiHair", "group_2_subtitle_12": "Saç dökülmesine karşı saçlı deri mezoterapi solüsyonu.", "group_2_text_13": "Seffiline · Kozmetik", "group_2_title_13": "SeffiCare", "group_2_subtitle_13": "Günlük cilt bakımı için kozmetik onarım serisi.", "group_2_text_14": "Seffiline · Mezoterapi", "group_2_title_14": "Seffiller", "group_2_subtitle_14": "Hacim ve dolgunluk için hyalüronik asit bazlı dolgu serisi.", "group_2_text_15": "Woorhi · Mezoterapi Tabancası", "group_2_title_15": "Raffine", "group_2_subtitle_15": "Kore mühendisliği cihaz.", "group_2_text_16": "Mi Medical · Mezoterapi Tabancası", "group_2_title_16": "Pistor Eliance", "group_2_subtitle_16": "Premium enjeksiyon sistemi."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-urunler-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'BİLGİ ŞERİDİ',
             'html_template'=><<<'EDHTML'
<!-- BİLGİ ŞERİDİ -->

    <section style="padding:0 0 56px;background:#fff;">
      <div class="container">
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:26px 30px;">
          <div style="flex:1 1 380px;display:flex;align-items:center;gap:16px;">
            <span style="width:46px;height:46px;flex-shrink:0;border-radius:12px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;color:var(--color-primary,#E8702A);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 8-9-5-9 5v8l9 5 9-5V8Z"/><path d="m3 8 9 5 9-5"/><path d="M12 13v8"/></svg></span>
            <p style="margin:0;color:var(--text-main,#2a2a2a);font-size:16px;line-height:1.6;font-weight:600;">{{subtitle}} <strong style="color:var(--color-primary,#E8702A);">{{text}}</strong></p>
          </div>
          <a href="{{button_url}}" target="_blank" rel="noopener" style="flex-shrink:0;display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:14px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "text": {"type": "textarea", "label": "Text"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "subtitle": "Toplam 95+ ürün. Fiyat ve sipariş için WhatsApp:", "text": "+90 542 620 51 00", "button_text": "WhatsApp'tan Yaz"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-urunler-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CTA',
             'html_template'=><<<'EDHTML'
<!-- CTA -->

    <section style="padding:20px 0 84px;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:#262220;padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-70px;right:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.30),transparent 70%);"></div>
          <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.16),transparent 70%);"></div>
          <div style="position:relative;">
            <span style="display:inline-block;color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">{{group_3_text}}</span>
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.18;">{{group_3_title}} <span style="color:var(--color-accent,#F4A14E);">{{group_3_text_2}}</span></h2>
            <p style="font-size:18px;color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 34px;line-height:1.65;">{{group_3_description}}</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="{{group_1_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_1_button_text}}</a>
              <a href="{{group_2_button_url}}" target="_blank" rel="noopener" style="background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.32);padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{group_2_button_text}}</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "https://wa.me/905426205100", "group_2_button_url": "https://apps.skintechpharmagroup.com/medinet", "group_3_text": "Bize Ulaşın", "group_3_title": "Profesyonel çözümler için", "group_3_text_2": "buradayız", "group_3_description": "Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.", "group_1_button_text": "WhatsApp ile Yaz", "group_2_button_text": "MEDINET Portalı ↗"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-urun-detay-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PAGE HERO (compact) + BREADCRUMB',
             'html_template'=><<<'EDHTML'
<!-- PAGE HERO (compact) + BREADCRUMB -->

    <section style="background:radial-gradient(900px 460px at 85% -20%, rgba(244,161,78,.24), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);border-bottom:1px solid var(--border-soft,#ece6df);">
      <div class="container" style="padding:34px 0 30px;">
        <nav aria-label="Breadcrumb" style="font-size:14px;color:var(--text-soft,#6b6b6b);display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
          <a href="{{group_1_button_url}}" style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{group_1_button_text}}</a>
          <span aria-hidden="true" style="opacity:.5;">{{group_1_text}}</span>
          <a href="{{group_2_button_url}}" style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{group_2_button_text}}</a>
          <span aria-hidden="true" style="opacity:.5;">{{group_2_text}}</span>
          <span aria-current="page" style="color:var(--color-primary,#E8702A);font-weight:700;">{{group_3_text}}</span>
        </nav>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "/", "group_2_button_url": "/urunler", "group_1_button_text": "Ana Sayfa", "group_1_text": "/", "group_2_button_text": "Ürünler", "group_2_text": "/", "group_3_text": "RRS® HA Long Lasting"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-urun-detay-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PRODUCT DETAIL (2 kolon)',
             'html_template'=><<<'EDHTML'
<!-- PRODUCT DETAIL (2 kolon) -->

    <section style="padding:48px 0 64px;background:#fff;">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:52px;align-items:flex-start;">

        <!-- SOL: Ürün görsel alanı -->
        <!-- ════════════════════════════════════════════════════════════════════
             🖼️ ÜRÜN DETAY GÖRSELLERİ — ANA GÖRSEL (1:1) + 3 THUMBNAIL (1:1)
             RRS® HA Long Lasting · çapraz bağlı HA dermal implant · klinik premium.

             🖼️ ANA GÖRSEL: /assets/img/rrs-ha-main.jpg (oran 1:1)
             PROMPT: "Hero studio product photograph of a premium cross-linked
             hyaluronic acid dermal implant: a single sterile pre-filled medical syringe
             resting beside its elegant pharmaceutical box, centered on a clean
             white-to-cream seamless background, soft diffused clinical studio lighting,
             dewy hydrating freshness with a faint water-drop highlight, blue-teal and
             warm cream accents, glossy reflective surface, premium medical aesthetic,
             photorealistic, ultra high resolution, no text, no logo, no watermark"
             DEĞİŞTİR → <img src="assets/img/rrs-ha-main.jpg" alt="RRS HA Long Lasting ana ürün görseli — steril enjektör ve ambalaj" style="width:100%;height:100%;aspect-ratio:1/1;object-fit:cover;border-radius:18px;">

             🖼️ THUMBNAIL 1: /assets/img/rrs-ha-thumb-1.jpg (oran 1:1) — paket önden açı
             PROMPT: "Front-angle close-up studio photo of the pharmaceutical box of a
             cross-linked hyaluronic acid dermal implant on white-cream background, soft
             clinical lighting, warm cream tones, photorealistic, no text, no logo, no watermark"
             DEĞİŞTİR → <img src="assets/img/rrs-ha-thumb-1.jpg" alt="RRS HA ürün kutusu önden görünüm" style="width:100%;height:100%;aspect-ratio:1/1;object-fit:cover;border-radius:14px;">

             🖼️ THUMBNAIL 2: /assets/img/rrs-ha-thumb-2.jpg (oran 1:1) — enjektör yakın çekim
             PROMPT: "Macro detail studio photo of a sterile pre-filled medical syringe
             with clear hyaluronic gel, angled view on white-cream background, soft
             diffused clinical lighting, teal accents, photorealistic, no text, no logo, no watermark"
             DEĞİŞTİR → <img src="assets/img/rrs-ha-thumb-2.jpg" alt="RRS HA steril enjektör yakın çekim" style="width:100%;height:100%;aspect-ratio:1/1;object-fit:cover;border-radius:14px;">

             🖼️ THUMBNAIL 3: /assets/img/rrs-ha-thumb-3.jpg (oran 1:1) — kit üstten/açılı
             PROMPT: "Top-down flat-lay studio photo of a dermal implant kit: box, syringe
             and buffer solution vial neatly arranged on a white-cream surface, soft even
             clinical lighting, warm cream and teal accents, photorealistic, no text, no logo, no watermark"
             DEĞİŞTİR → <img src="assets/img/rrs-ha-thumb-3.jpg" alt="RRS HA kit üstten düzen görünümü" style="width:100%;height:100%;aspect-ratio:1/1;object-fit:cover;border-radius:14px;">
        ════════════════════════════════════════════════════════════════════ -->
        <div style="flex:1 1 380px;min-width:0;">
          <div style="position:relative;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:24px;box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:24px;">
            <div style="aspect-ratio:1/1;border-radius:18px;background:var(--color-secondary,#FBF4EE) url('assets/img/rrs-ha-main.jpg') center/cover no-repeat;"></div>
          </div>
          <div style="display:flex;gap:14px;margin-top:16px;flex-wrap:wrap;">
            <div style="flex:1 1 90px;aspect-ratio:1/1;border-radius:14px;border:1px solid var(--border-soft,#ece6df);background:var(--color-secondary,#FBF4EE) url('assets/img/rrs-ha-thumb-1.jpg') center/cover no-repeat;"></div>
            <div style="flex:1 1 90px;aspect-ratio:1/1;border-radius:14px;border:1px solid var(--border-soft,#ece6df);background:var(--color-secondary,#FBF4EE) url('assets/img/rrs-ha-thumb-2.jpg') center/cover no-repeat;"></div>
            <div style="flex:1 1 90px;aspect-ratio:1/1;border-radius:14px;border:1px solid var(--border-soft,#ece6df);background:var(--color-secondary,#FBF4EE) url('assets/img/rrs-ha-thumb-3.jpg') center/cover no-repeat;"></div>
          </div>
        </div>

        <!-- SAĞ: Ürün bilgisi -->
        <div style="flex:1 1 420px;min-width:0;">
          <span style="display:inline-block;font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:1px;background:rgba(12,110,114,.08);padding:6px 12px;border-radius:999px;">{{group_2_text}}</span>
          <h1 style="font-size:clamp(28px,4vw,42px);line-height:1.12;font-weight:800;color:var(--text-main,#2a2a2a);margin:16px 0 14px;">{{group_2_title}}</h1>
          <p style="font-size:17px;color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0 0 26px;max-width:520px;">{{group_1_description}}</p>

          <ul style="list-style:none;margin:0 0 30px;padding:0;display:grid;gap:13px;">
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;font-size:15.5px;"><span style="color:var(--color-primary,#E8702A);font-size:18px;flex-shrink:0;">{{group_1_text}}</span> {{group_1_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;font-size:15.5px;"><span style="color:var(--color-primary,#E8702A);font-size:18px;flex-shrink:0;">{{group_2_text_2}}</span> {{group_2_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;font-size:15.5px;"><span style="color:var(--color-primary,#E8702A);font-size:18px;flex-shrink:0;">{{group_3_text}}</span> {{group_3_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;font-size:15.5px;"><span style="color:var(--color-primary,#E8702A);font-size:18px;flex-shrink:0;">{{group_4_text}}</span> {{group_4_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;font-size:15.5px;"><span style="color:var(--color-primary,#E8702A);font-size:18px;flex-shrink:0;">{{group_5_text}}</span> {{group_5_item_text}}</li>
          </ul>

          <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a href="{{group_1_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_1_button_text}}</a>
            <a href="{{group_2_button_url}}" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);padding:15px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{group_2_button_text}}</a>
          </div>

          <p style="margin:22px 0 0;font-size:13.5px;color:var(--text-soft,#6b6b6b);display:flex;align-items:center;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary,#E8702A)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;"><path d="M12 3 5 6v5c0 4 3 7 7 8 4-1 7-4 7-8V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg> {{group_2_subtitle}}</p>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_4_item_text": {"type": "textarea", "label": "Group 4 Item Text"}, "group_5_text": {"type": "textarea", "label": "Group 5 Text"}, "group_5_item_text": {"type": "textarea", "label": "Group 5 Item Text"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "https://wa.me/905426205100", "group_2_button_url": "/iletisim", "group_2_text": "SKIN TECH · RRS", "group_2_title": "RRS® HA Long Lasting", "group_1_description": "Çapraz bağlı, emilebilir Hyalüronik asit içeren dermal implant.", "group_1_text": "✓", "group_1_item_text": "CE Class III sertifikalı", "group_2_text_2": "✓", "group_2_item_text": "Steril tıbbi enjektör formu", "group_3_text": "✓", "group_3_item_text": "Amino asit içeren koruyucu tampon solüsyonu", "group_4_text": "✓", "group_4_item_text": "Uzun etkili (long lasting)", "group_5_text": "✓", "group_5_item_text": "Cilt gençleştirme & nemlendirme", "group_1_button_text": "WhatsApp ile Sipariş", "group_2_button_text": "Teklif İste", "group_2_subtitle": "Yalnızca hekim/klinik kullanımına yöneliktir."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-urun-detay-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PRODUCT DESCRIPTION (rich-text)',
             'html_template'=><<<'EDHTML'
<!-- PRODUCT DESCRIPTION (rich-text) -->

    <section style="padding:64px 0;background:var(--color-secondary,#FBF4EE);">
      <div class="container" style="max-width:860px;">
        <div style="margin-bottom:42px;">
          <h2 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 16px;line-height:1.2;">{{group_1_title}}</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.8;margin:0 0 14px;">{{{group_1_body_html}}}</p>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.8;margin:0;">{{{group_2_body_html}}}</p>
        </div>

        <div style="margin-bottom:42px;">
          <h2 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 16px;line-height:1.2;">{{group_2_title}}</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.8;margin:0 0 16px;">{{{group_2_body_html_2}}}</p>
          <ul style="list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
            <li style="display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:14px;padding:14px 18px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="width:7px;height:7px;border-radius:50%;background:var(--color-primary,#E8702A);flex-shrink:0;"></span> {{group_1_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:14px;padding:14px 18px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="width:7px;height:7px;border-radius:50%;background:var(--color-primary,#E8702A);flex-shrink:0;"></span> {{group_2_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:14px;padding:14px 18px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="width:7px;height:7px;border-radius:50%;background:var(--color-primary,#E8702A);flex-shrink:0;"></span> {{group_3_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:14px;padding:14px 18px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="width:7px;height:7px;border-radius:50%;background:var(--color-primary,#E8702A);flex-shrink:0;"></span> {{group_4_item_text}}</li>
          </ul>
        </div>

        <div>
          <h2 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 16px;line-height:1.2;">{{group_3_title}}</h2>
          <ul style="list-style:none;margin:0;padding:0;display:grid;gap:12px;">
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;"><span style="color:#0C6E72;font-weight:800;flex-shrink:0;">{{group_1_text}}</span><span><strong style="color:var(--text-main,#2a2a2a);">{{group_2_text}}</strong> {{group_2_text_2}}</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;"><span style="color:#0C6E72;font-weight:800;flex-shrink:0;">{{group_1_text_2}}</span><span><strong style="color:var(--text-main,#2a2a2a);">{{group_2_text_3}}</strong> {{group_2_text_4}}</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;"><span style="color:#0C6E72;font-weight:800;flex-shrink:0;">{{group_1_text_3}}</span><span><strong style="color:var(--text-main,#2a2a2a);">{{group_2_text_5}}</strong> {{group_2_text_6}}</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;"><span style="color:#0C6E72;font-weight:800;flex-shrink:0;">{{group_1_text_4}}</span><span><strong style="color:var(--text-main,#2a2a2a);">{{group_2_text_7}}</strong> {{group_2_text_8}}</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;"><span style="color:#0C6E72;font-weight:800;flex-shrink:0;">{{group_1_text_5}}</span><span><strong style="color:var(--text-main,#2a2a2a);">{{group_2_text_9}}</strong> {{group_2_text_10}}</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;"><span style="color:#0C6E72;font-weight:800;flex-shrink:0;">{{group_1_text_6}}</span><span><strong style="color:var(--text-main,#2a2a2a);">{{group_2_text_11}}</strong> {{group_2_text_12}}</span></li>
          </ul>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_body_html": {"type": "textarea", "label": "Group 1 Body"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_body_html_2": {"type": "textarea", "label": "Group 2 Body Html 2"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "group_4_item_text": {"type": "textarea", "label": "Group 4 Item Text"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_5": {"type": "textarea", "label": "Group 2 Text 5"}, "group_2_text_6": {"type": "textarea", "label": "Group 2 Text 6"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_2_text_7": {"type": "textarea", "label": "Group 2 Text 7"}, "group_2_text_8": {"type": "textarea", "label": "Group 2 Text 8"}, "group_1_text_5": {"type": "textarea", "label": "Group 1 Text 5"}, "group_2_text_9": {"type": "textarea", "label": "Group 2 Text 9"}, "group_2_text_10": {"type": "textarea", "label": "Group 2 Text 10"}, "group_1_text_6": {"type": "textarea", "label": "Group 1 Text 6"}, "group_2_text_11": {"type": "textarea", "label": "Group 2 Text 11"}, "group_2_text_12": {"type": "textarea", "label": "Group 2 Text 12"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_title": "Ürün Açıklaması", "group_1_body_html": "RRS® HA Long Lasting, çapraz bağlı (cross-linked) ve tamamen emilebilir hyalüronik asit içeren steril bir dermal implanttır. Kullanıma hazır tıbbi enjektör formunda sunulan ürün, amino asit içeren koruyucu bir tampon solüsyonunda çözülerek dokuyla yüksek biyouyum sağlar.", "group_2_body_html": "Cildin derin katmanlarına uygulanan formül, dermisi içten nemlendirir, su tutma kapasitesini artırır ve doku kalitesini iyileştirir. Çapraz bağlı yapısı sayesinde etkisini daha uzun süre koruyarak (long lasting), tek seansta belirgin tazelenme ve canlanma sağlar.", "group_2_title": "Kullanım Alanları", "group_2_body_html_2": "Cilt gençleştirme ve skinbooster protokollerinde, dokunun nem dengesini ve elastikiyetini desteklemek amacıyla aşağıdaki bölgelerde uygulanabilir:", "group_1_item_text": "Yüz", "group_2_item_text": "Boyun", "group_3_item_text": "Dekolte", "group_4_item_text": "El sırtı", "group_3_title": "İçerik & Özellikler", "group_1_text": "•", "group_2_text": "Çapraz bağlı hyalüronik asit:", "group_2_text_2": "emilebilir, uzun etkili dermal implant yapısı.", "group_1_text_2": "•", "group_2_text_3": "Koruyucu tampon solüsyonu:", "group_2_text_4": "amino asit içeren, dengeli çözücü ortam.", "group_1_text_3": "•", "group_2_text_5": "Steril enjektör formu:", "group_2_text_6": "kullanıma hazır, tek kullanımlık tıbbi sunum.", "group_1_text_4": "•", "group_2_text_7": "Sertifikasyon:", "group_2_text_8": "CE Class III tıbbi cihaz onayı.", "group_1_text_5": "•", "group_2_text_9": "Endikasyon:", "group_2_text_10": "cilt gençleştirme, nemlendirme ve doku kalitesi iyileştirme.", "group_1_text_6": "•", "group_2_text_11": "Üretici:", "group_2_text_12": "Skin Tech Pharma Group."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-urun-detay-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'RELATED PRODUCTS',
             'html_template'=><<<'EDHTML'
<!-- RELATED PRODUCTS -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:44px;">
          <div>
            <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 10px;">{{group_1_subtitle}}</p>
            <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">{{group_1_title}}</h2>
          </div>
          <a href="{{group_1_button_url}}" style="color:var(--color-primary,#E8702A);font-weight:700;font-size:15px;white-space:nowrap;">{{group_1_button_text}}</a>
        </div>
        <!-- ════════════════════════════════════════════════════════════════════
             🖼️ BENZER ÜRÜN KART GÖRSELLERİ (her kart için tekrarlanır, oran 4:3)
             Aşağıdaki her kartın üstündeki gradyan+emoji kutusu bir görsel
             placeholder'dır. İsimlendirme: /assets/img/product-{slug}.jpg
             (örn. product-melablock-hsp-spf50.jpg, product-benebellum-lumina-vitc.jpg,
              product-atrofillin.jpg) — /urunler ile aynı görseller tekrar kullanılır.
             PROMPT: "Professional studio product photography of a single medical
             aesthetic / dermatology product package — pharmaceutical box and glass vial
             or syringe — centered on a clean white-to-cream seamless background, soft
             diffused studio lighting, gentle reflection, premium clinical look, warm
             orange and cream accents, shallow depth of field, photorealistic, high
             resolution, no text, no logo, no watermark"
             DEĞİŞTİR → her kartta:
               <div style="aspect-ratio:4/3;...">EMOJİ</div>
             şununla:
               <img src="assets/img/product-{slug}.jpg" alt="{ÜRÜN ADI} ürün ambalaj görseli" style="width:100%;height:100%;aspect-ratio:4/3;object-fit:cover;">
        ════════════════════════════════════════════════════════════════════ -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px;">
          <a href="{{group_1_button_url_2}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-melablock-hsp-spf-50.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_description}}</p>
            </div>
          </a>
          <a href="{{group_2_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-benebellum-lumina-vit-c-18.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_2}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_2}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_subtitle}}</p>
            </div>
          </a>
          <a href="{{group_3_button_url}}" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-atrofillin.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">{{group_2_text_3}}</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">{{group_2_title_3}}</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">{{group_2_description_2}}</p>
            </div>
          </a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_1_button_url_2": {"type": "text", "label": "Group 1 Button Url 2"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_2_title_2": {"type": "text", "label": "Group 2 Title 2"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_2_title_3": {"type": "text", "label": "Group 2 Title 3"}, "group_2_description_2": {"type": "textarea", "label": "Group 2 Description 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "/urunler", "group_1_button_url_2": "/urun-detay", "group_2_button_url": "/urun-detay", "group_3_button_url": "/urun-detay", "group_1_subtitle": "Benzer Ürünler", "group_1_title": "İlginizi çekebilecek diğer ürünler", "group_1_button_text": "Tüm ürünler →", "group_2_text": "Skin Tech · Krem", "group_2_title": "Melablock HSP SPF 50+", "group_2_description": "Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktör.", "group_2_text_2": "Skin Tech · Mezoterapi", "group_2_title_2": "Benebellum LUMINA VİT-C 18%", "group_2_subtitle": "Yüksek konsantrasyonlu C vitamini ile aydınlatıcı bakım.", "group_2_text_3": "Skin Tech · RRS", "group_2_title_3": "Atrofillin", "group_2_description_2": "Atrofik ve yıpranmış cilt için yenileyici dermal enjeksiyon çözümü."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-urun-detay-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CTA (full)',
             'html_template'=><<<'EDHTML'
<!-- CTA (full) -->

    <section style="padding:20px 0 84px;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:#262220;padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-70px;right:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.30),transparent 70%);"></div>
          <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.16),transparent 70%);"></div>
          <div style="position:relative;">
            <span style="display:inline-block;color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">{{group_3_text}}</span>
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.18;">{{group_3_title}} <span style="color:var(--color-accent,#F4A14E);">{{group_3_text_2}}</span></h2>
            <p style="font-size:18px;color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 34px;line-height:1.65;">{{group_3_description}}</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="{{group_1_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_1_button_text}}</a>
              <a href="{{group_2_button_url}}" target="_blank" rel="noopener" style="background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.32);padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{group_2_button_text}}</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "https://wa.me/905426205100", "group_2_button_url": "https://apps.skintechpharmagroup.com/medinet", "group_3_text": "Bize Ulaşın", "group_3_title": "Profesyonel çözümler için", "group_3_text_2": "buradayız", "group_3_description": "Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.", "group_1_button_text": "WhatsApp ile Yaz", "group_2_button_text": "MEDINET Portalı ↗"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-markalar-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PAGE HERO (compact)',
             'html_template'=><<<'EDHTML'
<!-- PAGE HERO (compact) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(900px 460px at 85% -20%, rgba(244,161,78,.26), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);">
      <div class="container" style="padding:56px 0 60px;">
        <nav aria-label="Breadcrumb" style="margin:0 0 18px;">
          <ol style="list-style:none;display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:0;padding:0;font-size:14px;color:var(--text-soft,#6b6b6b);">
            <li><a href="{{group_1_button_url}}" style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{group_1_button_text}}</a></li>
            <li aria-hidden="true" style="color:var(--border-soft,#ece6df);">{{group_2_item_text}}</li>
            <li aria-current="page" style="color:var(--color-primary,#E8702A);font-weight:700;">{{group_3_item_text}}</li>
          </ol>
        </nav>
        <h1 style="font-size:clamp(30px,4.6vw,48px);line-height:1.1;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 16px;max-width:760px;">{{title}}</h1>
        <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:640px;margin:0;">{{description}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "/", "group_1_button_text": "Ana Sayfa", "group_2_item_text": "/", "group_3_item_text": "Markalar", "title": "Temsil Ettiğimiz Markalar", "description": "Her biri kendi alanında uzman, uluslararası 5 marka — Türkiye'de resmi temsilcisi Estetik Dermal."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-markalar-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'BRAND CARDS',
             'html_template'=><<<'EDHTML'
<!-- BRAND CARDS -->

    <section style="padding:72px 0;background:#fff;">
      <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(440px,1fr));gap:26px;">

        <!-- 🖼️ GÖRSEL (opsiyonel marka kartı görseli – temsili blok, her kartın üst görsel alanı için aynı desen):
             /assets/img/brand-panel-{slug}.jpg (oran 16:9 yatay) — örn. brand-panel-skintech.jpg, brand-panel-seffiline.jpg, brand-panel-aespio.jpg, brand-panel-woorhi.jpg, brand-panel-mi-medical.jpg
             PROMPT: "Clean horizontal brand product still life for a medical aesthetics company, premium minimal corporate pharma look on a soft cream backdrop, subtle brand-color accent, soft studio lighting, no text, no logo, no watermark, high resolution"
             DEĞİŞTİR → istenirse her <article> başına bir <img src="assets/img/brand-panel-{slug}.jpg" alt="{MARKA ADI}" style="width:100%;aspect-ratio:16/9;object-fit:cover;"> ekle -->
        <!-- Skin Tech Pharma Group -->
        <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #0C6E72;border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
          <div style="aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/brand-panel-skintech.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
          <div style="display:flex;flex-direction:column;flex:1;padding:26px 30px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <span style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#0C6E72;">{{group_1_text}}</span>
            <span style="flex-shrink:0;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);color:var(--text-soft,#6b6b6b);font-size:12px;font-weight:700;padding:5px 12px;border-radius:999px;">{{group_2_text}}</span>
          </div>
          <h2 style="font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">{{group_2_title}}</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:15px;line-height:1.7;margin:0 0 22px;">{{group_2_description}}</p>
          <a href="{{group_2_button_url}}" style="margin-top:auto;align-self:flex-start;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">{{group_2_button_text}}</a>
        
          </div>
        </article>

        <!-- Seffiline -->
        <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #C98A6D;border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
          <div style="aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/brand-panel-seffiline.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
          <div style="display:flex;flex-direction:column;flex:1;padding:26px 30px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <span style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#a5694f;">{{group_1_text_2}}</span>
            <span style="flex-shrink:0;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);color:var(--text-soft,#6b6b6b);font-size:12px;font-weight:700;padding:5px 12px;border-radius:999px;">{{group_2_text_2}}</span>
          </div>
          <h2 style="font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">{{group_2_title_2}}</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:15px;line-height:1.7;margin:0 0 22px;">{{group_2_subtitle}}</p>
          <a href="{{group_2_button_url_2}}" style="margin-top:auto;align-self:flex-start;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">{{group_2_button_text_2}}</a>
        
          </div>
        </article>

        <!-- Grand Aespio -->
        <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #6C5CE0;border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
          <div style="aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/brand-panel-aespio.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
          <div style="display:flex;flex-direction:column;flex:1;padding:26px 30px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <span style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#6C5CE0;">{{group_1_text_3}}</span>
            <span style="flex-shrink:0;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);color:var(--text-soft,#6b6b6b);font-size:12px;font-weight:700;padding:5px 12px;border-radius:999px;">{{group_2_text_3}}</span>
          </div>
          <h2 style="font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">{{group_2_title_3}}</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:15px;line-height:1.7;margin:0 0 22px;">{{group_2_description_2}}</p>
          <a href="{{group_2_button_url_3}}" style="margin-top:auto;align-self:flex-start;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">{{group_2_button_text_3}}</a>
        
          </div>
        </article>

        <!-- Woorhi Mechatronics Co. Ltd. -->
        <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #2DA8FF;border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
          <div style="aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/brand-panel-woorhi.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
          <div style="display:flex;flex-direction:column;flex:1;padding:26px 30px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <span style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#1486d6;">{{group_1_text_4}}</span>
            <span style="flex-shrink:0;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);color:var(--text-soft,#6b6b6b);font-size:12px;font-weight:700;padding:5px 12px;border-radius:999px;">{{group_2_text_4}}</span>
          </div>
          <h2 style="font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">{{group_2_title_4}}</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:15px;line-height:1.7;margin:0 0 22px;">{{group_2_description_3}}</p>
          <a href="{{group_2_button_url_4}}" style="margin-top:auto;align-self:flex-start;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">{{group_2_button_text_4}}</a>
        
          </div>
        </article>

        <!-- Mi Medical Innovation -->
        <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #C9A24B;border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
          <div style="aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/brand-panel-mi-medical.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
          <div style="display:flex;flex-direction:column;flex:1;padding:26px 30px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <span style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#a4812f;">{{group_1_text_5}}</span>
            <span style="flex-shrink:0;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);color:var(--text-soft,#6b6b6b);font-size:12px;font-weight:700;padding:5px 12px;border-radius:999px;">{{group_2_text_5}}</span>
          </div>
          <h2 style="font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">{{group_2_title_5}}</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:15px;line-height:1.7;margin:0 0 22px;">{{group_2_subtitle_2}}</p>
          <a href="{{group_2_button_url_5}}" style="margin-top:auto;align-self:flex-start;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">{{group_2_button_text_5}}</a>
        
          </div>
        </article>

      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_2_button_url_2": {"type": "text", "label": "Group 2 Button Url 2"}, "group_2_button_url_3": {"type": "text", "label": "Group 2 Button Url 3"}, "group_2_button_url_4": {"type": "text", "label": "Group 2 Button Url 4"}, "group_2_button_url_5": {"type": "text", "label": "Group 2 Button Url 5"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_2_title_2": {"type": "text", "label": "Group 2 Title 2"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}, "group_2_button_text_2": {"type": "textarea", "label": "Group 2 Button Text 2"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_2_title_3": {"type": "text", "label": "Group 2 Title 3"}, "group_2_description_2": {"type": "textarea", "label": "Group 2 Description 2"}, "group_2_button_text_3": {"type": "textarea", "label": "Group 2 Button Text 3"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_2_title_4": {"type": "text", "label": "Group 2 Title 4"}, "group_2_description_3": {"type": "textarea", "label": "Group 2 Description 3"}, "group_2_button_text_4": {"type": "textarea", "label": "Group 2 Button Text 4"}, "group_1_text_5": {"type": "textarea", "label": "Group 1 Text 5"}, "group_2_text_5": {"type": "textarea", "label": "Group 2 Text 5"}, "group_2_title_5": {"type": "text", "label": "Group 2 Title 5"}, "group_2_subtitle_2": {"type": "textarea", "label": "Group 2 Subtitle 2"}, "group_2_button_text_5": {"type": "textarea", "label": "Group 2 Button Text 5"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": "/marka-skintech", "group_2_button_url_2": "/marka-seffiline", "group_2_button_url_3": "/marka-aespio", "group_2_button_url_4": "/marka-woorhi", "group_2_button_url_5": "/marka-mi-medical", "group_1_text": "İspanya · Amiral Marka", "group_2_text": "84+ ürün", "group_2_title": "Skin Tech Pharma Group", "group_2_description": "Kimyasal peeling, mezoterapi ve RRS skinbooster serisinde dünya lideri.", "group_2_button_text": "Markayı Keşfet →", "group_1_text_2": "Bakım & Dolgu Serisi", "group_2_text_2": "4 ürün", "group_2_title_2": "Seffiline", "group_2_subtitle": "Cilt, saç, intim bakım ve dolgu çözümleri serisi.", "group_2_button_text_2": "Markayı Keşfet →", "group_1_text_3": "K-Beauty · Thread Lift", "group_2_text_3": "5 ürün", "group_2_title_3": "Grand Aespio", "group_2_description_2": "Yüz maskeleri ve ip askı (thread lift) ürünleri. Modern K-beauty yaklaşımı.", "group_2_button_text_3": "Markayı Keşfet →", "group_1_text_4": "Güney Kore · Mekatronik", "group_2_text_4": "Cihaz", "group_2_title_4": "Woorhi Mechatronics Co. Ltd.", "group_2_description_3": "Güney Kore · Medikal estetik cihaz ve mekatronik mühendisliği.", "group_2_button_text_4": "Markayı Keşfet →", "group_1_text_5": "Enjeksiyon Sistemleri", "group_2_text_5": "Premium", "group_2_title_5": "Mi Medical Innovation", "group_2_subtitle_2": "Premium mezoterapi ve enjeksiyon sistemleri.", "group_2_button_text_5": "Markayı Keşfet →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-markalar-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'KISA BANT',
             'html_template'=><<<'EDHTML'
<!-- KISA BANT -->

    <section style="background:var(--color-secondary,#FBF4EE);">
      <div class="container" style="padding:34px 0;">
        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;justify-content:center;text-align:center;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:24px 30px;">
          <span style="flex-shrink:0;color:var(--color-primary,#E8702A);"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="9" r="6"/><path d="m9 14.5-2 7 5-3 5 3-2-7"/><path d="m12 6 1 2 2 .3-1.5 1.5.4 2L12 11l-1.9 1 .4-2L9 8.3 11 8Z"/></svg></span>
          <p style="color:var(--text-main,#2a2a2a);font-size:16px;font-weight:600;line-height:1.6;margin:0;">{{subtitle}} <strong style="color:var(--color-primary,#E8702A);">{{group_1_text}}</strong> {{subtitle_2}} <strong style="color:var(--color-primary,#E8702A);">{{group_2_text}}</strong> {{subtitle_3}}</p>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"subtitle": {"type": "textarea", "label": "Subtitle"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "subtitle_2": {"type": "textarea", "label": "Subtitle 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "subtitle_3": {"type": "textarea", "label": "Subtitle 3"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"subtitle": "Portföyümüzde ayrıca", "group_1_text": "Neogenesis", "subtitle_2": "ve", "group_2_text": "CE Class III sertifikalı RRS serisi", "subtitle_3": "yer alır."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-markalar-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CTA',
             'html_template'=><<<'EDHTML'
<!-- CTA -->

    <section style="padding:64px 0 84px;background:var(--color-secondary,#FBF4EE);">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:#262220;padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-70px;right:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.30),transparent 70%);"></div>
          <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.16),transparent 70%);"></div>
          <div style="position:relative;">
            <span style="display:inline-block;color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">{{group_3_text}}</span>
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.18;">{{group_3_title}} <span style="color:var(--color-accent,#F4A14E);">{{group_3_text_2}}</span></h2>
            <p style="font-size:18px;color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 34px;line-height:1.65;">{{group_3_description}}</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="{{group_1_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_1_button_text}}</a>
              <a href="{{group_2_button_url}}" target="_blank" rel="noopener" style="background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.32);padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{group_2_button_text}}</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "https://wa.me/905426205100", "group_2_button_url": "https://apps.skintechpharmagroup.com/medinet", "group_3_text": "Bize Ulaşın", "group_3_title": "Profesyonel çözümler için", "group_3_text_2": "buradayız", "group_3_description": "Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.", "group_1_button_text": "WhatsApp ile Yaz", "group_2_button_text": "MEDINET Portalı ↗"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-etkinlikler-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PAGE HERO (compact)',
             'html_template'=><<<'EDHTML'
<!-- PAGE HERO (compact) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(1000px 500px at 85% -20%, rgba(244,161,78,.26), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);">
      <div class="container" style="padding:54px 0 60px;">
        <nav aria-label="Sayfa konumu" style="font-size:13.5px;color:var(--text-soft,#6b6b6b);margin:0 0 20px;">
          <a href="{{button_url}}" style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{button_text}}</a>
          <span style="margin:0 8px;color:var(--border-soft,#ece6df);">{{group_1_text}}</span>
          <span style="color:var(--color-primary,#E8702A);font-weight:700;">{{group_2_text}}</span>
        </nav>
        <p style="display:inline-flex;align-items:center;gap:8px;background:var(--primary-soft,#FCE9DC);color:var(--primary-deep,#C85716);border-radius:999px;padding:7px 15px;font-size:13px;font-weight:700;letter-spacing:.4px;margin:0 0 18px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/></svg>{{group_1_subtitle}}</p>
        <h1 style="font-size:clamp(30px,4.6vw,48px);line-height:1.1;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 16px;max-width:760px;">{{title}}</h1>
        <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:640px;margin:0;">{{group_2_description}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "button_text": {"type": "textarea", "label": "Button Text"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "title": {"type": "text", "label": "Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "/", "button_text": "Ana Sayfa", "group_1_text": "/", "group_2_text": "Etkinlikler", "group_1_subtitle": "Kongre & Etkinlikler", "title": "Kongre & Etkinlikler", "group_2_description": "Estetik Dermal olarak yer aldığımız ulusal ve uluslararası kongreler, fuarlar ve eğitim etkinlikleri."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-etkinlikler-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'EVENT GRID',
             'html_template'=><<<'EDHTML'
<!-- EVENT GRID -->

    <section style="padding:72px 0 40px;background:#fff;">
      <div class="container">
        <p style="display:inline-flex;align-items:center;gap:9px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);color:var(--text-soft,#6b6b6b);border-radius:12px;padding:10px 16px;font-size:13.5px;line-height:1.5;margin:0 0 36px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/></svg>{{group_1_description}}</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px;">

          <!-- Kart 1 -->
          <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
            <!-- 🖼️ GÖRSEL: /assets/img/event-1.jpg (oran 16:10)
                 PROMPT: "Wide shot of a large international aesthetic dermatology congress in Paris, busy modern convention hall with exhibition booths and professional attendees, bright clean atmosphere, warm cream and terracotta accent tones, photorealistic event photography, no text, no logo, no watermark, high resolution"
                 DEĞİŞTİR → <img src="assets/img/event-1.jpg" alt="IMCAS World Congress 2026 kongre salonu" style="width:100%;height:100%;object-fit:cover;"> (tarih rozetini koru) -->
            <div style="position:relative;aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/event-1.jpg') center/cover no-repeat;">
              <span style="position:absolute;top:14px;right:14px;background:#fff;border-radius:14px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:8px 12px;text-align:center;line-height:1;">
                <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#E8702A);">{{group_1_text}}</span>
                <span style="display:block;font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);">{{group_2_text}}</span>
              </span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;flex:1;">
              <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;line-height:1.25;">{{group_2_title}}</h3>
              <p style="display:flex;align-items:center;gap:7px;color:var(--text-soft,#6b6b6b);font-size:14px;font-weight:600;margin:0 0 12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>{{group_1_subtitle}}</p>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14.5px;line-height:1.65;margin:0 0 18px;flex:1;">{{group_2_description}}</p>
              <a href="{{group_2_button_url}}" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">{{group_2_button_text}}</a>
            </div>
          </article>

          <!-- Kart 2 -->
          <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
            <!-- 🖼️ GÖRSEL: /assets/img/event-2.jpg (oran 16:10)
                 PROMPT: "Modern anti-aging and aesthetic medicine conference in Istanbul, professional stage with speaker and audience, sleek exhibition stand showing skincare devices, elegant warm lighting with cream and amber tones, photorealistic event photography, no text, no logo, no watermark, high resolution"
                 DEĞİŞTİR → <img src="assets/img/event-2.jpg" alt="Anti-Aging & Estetik Kongresi sahne ve katılımcılar" style="width:100%;height:100%;object-fit:cover;"> (tarih rozetini koru) -->
            <div style="position:relative;aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/event-2.jpg') center/cover no-repeat;">
              <span style="position:absolute;top:14px;right:14px;background:#fff;border-radius:14px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:8px 12px;text-align:center;line-height:1;">
                <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#E8702A);">{{group_1_text_2}}</span>
                <span style="display:block;font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);">{{group_2_text_2}}</span>
              </span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;flex:1;">
              <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;line-height:1.25;">{{group_2_title_2}}</h3>
              <p style="display:flex;align-items:center;gap:7px;color:var(--text-soft,#6b6b6b);font-size:14px;font-weight:600;margin:0 0 12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>{{group_1_subtitle_2}}</p>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14.5px;line-height:1.65;margin:0 0 18px;flex:1;">{{group_2_description_2}}</p>
              <a href="{{group_2_button_url_2}}" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">{{group_2_button_text_2}}</a>
            </div>
          </article>

          <!-- Kart 3 -->
          <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
            <!-- 🖼️ GÖRSEL: /assets/img/event-3.jpg (oran 16:10)
                 PROMPT: "Dermatology and cosmetology scientific days in Antalya, interactive hands-on workshop with doctors practicing mesotherapy and peeling techniques on training models, bright clinical training room, warm cream and terracotta accents, photorealistic event photography, no text, no logo, no watermark, high resolution"
                 DEĞİŞTİR → <img src="assets/img/event-3.jpg" alt="Dermatoloji & Kozmetoloji Günleri uygulama atölyesi" style="width:100%;height:100%;object-fit:cover;"> (tarih rozetini koru) -->
            <div style="position:relative;aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/event-3.jpg') center/cover no-repeat;">
              <span style="position:absolute;top:14px;right:14px;background:#fff;border-radius:14px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:8px 12px;text-align:center;line-height:1;">
                <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#E8702A);">{{group_1_text_3}}</span>
                <span style="display:block;font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);">{{group_2_text_3}}</span>
              </span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;flex:1;">
              <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;line-height:1.25;">{{group_2_title_3}}</h3>
              <p style="display:flex;align-items:center;gap:7px;color:var(--text-soft,#6b6b6b);font-size:14px;font-weight:600;margin:0 0 12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>{{group_1_subtitle_3}}</p>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14.5px;line-height:1.65;margin:0 0 18px;flex:1;">{{group_2_description_3}}</p>
              <a href="{{group_2_button_url_3}}" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">{{group_2_button_text_3}}</a>
            </div>
          </article>

          <!-- Kart 4 -->
          <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
            <!-- 🖼️ GÖRSEL: /assets/img/event-4.jpg (oran 16:10)
                 PROMPT: "Intimate hands-on medical training workshop in Kusadasi, an instructor demonstrating RRS skinbooster and chemical peeling application to a small group of doctors around a treatment table, warm cream and terracotta clinical interior, photorealistic event photography, no text, no logo, no watermark, high resolution"
                 DEĞİŞTİR → <img src="assets/img/event-4.jpg" alt="Skin Tech uygulamalı eğitim workshop'u" style="width:100%;height:100%;object-fit:cover;"> (tarih rozetini koru) -->
            <div style="position:relative;aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/event-4.jpg') center/cover no-repeat;">
              <span style="position:absolute;top:14px;right:14px;background:#fff;border-radius:14px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:8px 12px;text-align:center;line-height:1;">
                <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#E8702A);">{{group_1_text_4}}</span>
                <span style="display:block;font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);">{{group_2_text_4}}</span>
              </span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;flex:1;">
              <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;line-height:1.25;">{{group_2_title_4}}</h3>
              <p style="display:flex;align-items:center;gap:7px;color:var(--text-soft,#6b6b6b);font-size:14px;font-weight:600;margin:0 0 12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>{{group_1_subtitle_4}}</p>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14.5px;line-height:1.65;margin:0 0 18px;flex:1;">{{group_2_description_4}}</p>
              <a href="{{group_2_button_url_4}}" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">{{group_2_button_text_4}}</a>
            </div>
          </article>

          <!-- Kart 5 -->
          <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
            <!-- 🖼️ GÖRSEL: /assets/img/event-5.jpg (oran 16:10)
                 PROMPT: "Regional face aesthetics conference in Izmir, a speaker presenting thread lift and dermal filler innovations on a large screen to an engaged professional audience, modern auditorium with warm amber and cream lighting, photorealistic event photography, no text, no logo, no watermark, high resolution"
                 DEĞİŞTİR → <img src="assets/img/event-5.jpg" alt="FACE Aesthetic Conference sunumu" style="width:100%;height:100%;object-fit:cover;"> (tarih rozetini koru) -->
            <div style="position:relative;aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/event-5.jpg') center/cover no-repeat;">
              <span style="position:absolute;top:14px;right:14px;background:#fff;border-radius:14px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:8px 12px;text-align:center;line-height:1;">
                <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#E8702A);">{{group_1_text_5}}</span>
                <span style="display:block;font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);">{{group_2_text_5}}</span>
              </span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;flex:1;">
              <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;line-height:1.25;">{{group_2_title_5}}</h3>
              <p style="display:flex;align-items:center;gap:7px;color:var(--text-soft,#6b6b6b);font-size:14px;font-weight:600;margin:0 0 12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>{{group_1_subtitle_5}}</p>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14.5px;line-height:1.65;margin:0 0 18px;flex:1;">{{group_2_description_5}}</p>
              <a href="{{group_2_button_url_5}}" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">{{group_2_button_text_5}}</a>
            </div>
          </article>

          <!-- Kart 6 -->
          <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
            <!-- 🖼️ GÖRSEL: /assets/img/event-6.jpg (oran 16:10)
                 PROMPT: "Medical aesthetics trade fair in Ankara, a well-designed branded exhibition booth displaying skincare products and aesthetic devices, visitors browsing, bright exhibition hall, warm cream and terracotta brand accents, photorealistic event photography, no text, no logo, no watermark, high resolution"
                 DEĞİŞTİR → <img src="assets/img/event-6.jpg" alt="Medikal Estetik Fuarı stant alanı" style="width:100%;height:100%;object-fit:cover;"> (tarih rozetini koru) -->
            <div style="position:relative;aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/event-6.jpg') center/cover no-repeat;">
              <span style="position:absolute;top:14px;right:14px;background:#fff;border-radius:14px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:8px 12px;text-align:center;line-height:1;">
                <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#E8702A);">{{group_1_text_6}}</span>
                <span style="display:block;font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);">{{group_2_text_6}}</span>
              </span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;flex:1;">
              <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;line-height:1.25;">{{group_2_title_6}}</h3>
              <p style="display:flex;align-items:center;gap:7px;color:var(--text-soft,#6b6b6b);font-size:14px;font-weight:600;margin:0 0 12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>{{group_1_subtitle_6}}</p>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14.5px;line-height:1.65;margin:0 0 18px;flex:1;">{{group_2_description_6}}</p>
              <a href="{{group_2_button_url_6}}" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">{{group_2_button_text_6}}</a>
            </div>
          </article>

        </div>

        <p style="margin:30px 0 0;font-size:12.5px;color:var(--text-soft,#6b6b6b);line-height:1.6;font-style:italic;">{{{group_2_body_html}}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_2_button_url_2": {"type": "text", "label": "Group 2 Button Url 2"}, "group_2_button_url_3": {"type": "text", "label": "Group 2 Button Url 3"}, "group_2_button_url_4": {"type": "text", "label": "Group 2 Button Url 4"}, "group_2_button_url_5": {"type": "text", "label": "Group 2 Button Url 5"}, "group_2_button_url_6": {"type": "text", "label": "Group 2 Button Url 6"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_2_title_2": {"type": "text", "label": "Group 2 Title 2"}, "group_1_subtitle_2": {"type": "textarea", "label": "Group 1 Subtitle 2"}, "group_2_description_2": {"type": "textarea", "label": "Group 2 Description 2"}, "group_2_button_text_2": {"type": "textarea", "label": "Group 2 Button Text 2"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_2_title_3": {"type": "text", "label": "Group 2 Title 3"}, "group_1_subtitle_3": {"type": "textarea", "label": "Group 1 Subtitle 3"}, "group_2_description_3": {"type": "textarea", "label": "Group 2 Description 3"}, "group_2_button_text_3": {"type": "textarea", "label": "Group 2 Button Text 3"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_2_title_4": {"type": "text", "label": "Group 2 Title 4"}, "group_1_subtitle_4": {"type": "textarea", "label": "Group 1 Subtitle 4"}, "group_2_description_4": {"type": "textarea", "label": "Group 2 Description 4"}, "group_2_button_text_4": {"type": "textarea", "label": "Group 2 Button Text 4"}, "group_1_text_5": {"type": "textarea", "label": "Group 1 Text 5"}, "group_2_text_5": {"type": "textarea", "label": "Group 2 Text 5"}, "group_2_title_5": {"type": "text", "label": "Group 2 Title 5"}, "group_1_subtitle_5": {"type": "textarea", "label": "Group 1 Subtitle 5"}, "group_2_description_5": {"type": "textarea", "label": "Group 2 Description 5"}, "group_2_button_text_5": {"type": "textarea", "label": "Group 2 Button Text 5"}, "group_1_text_6": {"type": "textarea", "label": "Group 1 Text 6"}, "group_2_text_6": {"type": "textarea", "label": "Group 2 Text 6"}, "group_2_title_6": {"type": "text", "label": "Group 2 Title 6"}, "group_1_subtitle_6": {"type": "textarea", "label": "Group 1 Subtitle 6"}, "group_2_description_6": {"type": "textarea", "label": "Group 2 Description 6"}, "group_2_button_text_6": {"type": "textarea", "label": "Group 2 Button Text 6"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": "#", "group_2_button_url_2": "#", "group_2_button_url_3": "#", "group_2_button_url_4": "#", "group_2_button_url_5": "#", "group_2_button_url_6": "#", "group_1_description": "Aşağıdaki etkinlikler temsili örneklerdir; gerçek tarihler ve katılım bilgileri yakında güncellenecektir.", "group_1_text": "Oca", "group_2_text": "29", "group_2_title": "IMCAS World Congress 2026", "group_1_subtitle": "Paris, Fransa", "group_2_description": "Dünyanın en kapsamlı estetik dermatoloji kongresinde son teknoloji ürün ve uygulamalarla yer alıyoruz.", "group_2_button_text": "Detay →", "group_1_text_2": "Mar", "group_2_text_2": "14", "group_2_title_2": "Anti-Aging & Estetik Kongresi", "group_1_subtitle_2": "İstanbul", "group_2_description_2": "Yaşlanma karşıtı uygulamalarda güncel protokoller; standımızda ürün ve cihaz demoları.", "group_2_button_text_2": "Detay →", "group_1_text_3": "May", "group_2_text_3": "22", "group_2_title_3": "Dermatoloji & Kozmetoloji Günleri", "group_1_subtitle_3": "Antalya", "group_2_description_3": "Mezoterapi ve peeling odaklı bilimsel oturumlar ile interaktif uygulama atölyeleri.", "group_2_button_text_3": "Detay →", "group_1_text_4": "Haz", "group_2_text_4": "18", "group_2_title_4": "Skin Tech Uygulamalı Eğitim Workshop", "group_1_subtitle_4": "Kuşadası, Aydın", "group_2_description_4": "Hekimlere yönelik birebir, uygulamalı RRS ve peeling eğitimi; sınırlı kontenjanlı atölye.", "group_2_button_text_4": "Detay →", "group_1_text_5": "Eyl", "group_2_text_5": "26", "group_2_title_5": "FACE Aesthetic Conference", "group_1_subtitle_5": "İzmir", "group_2_description_5": "Yüz estetiğinde ip askı ve dolgu yeniliklerinin paylaşıldığı bölgesel uzman buluşması.", "group_2_button_text_5": "Detay →", "group_1_text_6": "Kas", "group_2_text_6": "12", "group_2_title_6": "Medikal Estetik Fuarı", "group_1_subtitle_6": "Ankara", "group_2_description_6": "Temsil ettiğimiz tüm markaların ürün ve cihazlarını yakından inceleyebileceğiniz fuar standı.", "group_2_button_text_6": "Detay →", "group_2_body_html": "* Bu sayfadaki etkinlikler temsili örnek amaçlıdır. Kesin tarihler, mekânlar ve katılım koşulları onaylandıkça güncellenecektir."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-etkinlikler-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'INFO BLOCK',
             'html_template'=><<<'EDHTML'
<!-- INFO BLOCK -->

    <section style="padding:40px 0 84px;background:#fff;">
      <div class="container">
        <div style="display:flex;flex-wrap:wrap;gap:32px;align-items:center;justify-content:space-between;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:clamp(28px,4vw,44px);">
          <div style="flex:1 1 380px;">
            <h2 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;line-height:1.2;">{{group_1_title}}</h2>
            <p style="color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;margin:0;">{{{group_1_body_html}}}</p>
          </div>
          <div style="display:flex;gap:14px;flex-wrap:wrap;flex:0 1 auto;">
            <a href="{{group_1_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_1_button_text}}</a>
            <a href="{{group_2_button_url}}" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);padding:15px 26px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{group_2_button_text}}</a>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_body_html": {"type": "textarea", "label": "Group 1 Body"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "https://wa.me/905426205100", "group_2_button_url": "/iletisim", "group_1_title": "Etkinlik takvimi ve katılım için bizimle iletişime geçin", "group_1_body_html": "Yaklaşan kongreler, fuar standlarımız ve uygulamalı eğitim atölyelerimize katılım hakkında güncel bilgi almak için ekibimize ulaşın.", "group_1_button_text": "WhatsApp ile Yaz", "group_2_button_text": "İletişim →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-etkinlikler-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CTA (full)',
             'html_template'=><<<'EDHTML'
<!-- CTA (full) -->

    <section style="padding:20px 0 84px;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:#262220;padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-70px;right:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.30),transparent 70%);"></div>
          <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.16),transparent 70%);"></div>
          <div style="position:relative;">
            <span style="display:inline-block;color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">{{group_3_text}}</span>
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.18;">{{group_3_title}} <span style="color:var(--color-accent,#F4A14E);">{{group_3_text_2}}</span></h2>
            <p style="font-size:18px;color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 34px;line-height:1.65;">{{group_3_description}}</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="{{group_1_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_1_button_text}}</a>
              <a href="{{group_2_button_url}}" target="_blank" rel="noopener" style="background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.32);padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{group_2_button_text}}</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "https://wa.me/905426205100", "group_2_button_url": "https://apps.skintechpharmagroup.com/medinet", "group_3_text": "Bize Ulaşın", "group_3_title": "Profesyonel çözümler için", "group_3_text_2": "buradayız", "group_3_description": "Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.", "group_1_button_text": "WhatsApp ile Yaz", "group_2_button_text": "MEDINET Portalı ↗"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-iletisim-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PAGE HERO (compact)',
             'html_template'=><<<'EDHTML'
<!-- PAGE HERO (compact) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(1000px 460px at 85% -20%, rgba(244,161,78,.26), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);">
      <div class="container" style="padding:54px 0 58px;">
        <nav aria-label="Breadcrumb" style="margin:0 0 18px;font-size:14px;color:var(--text-soft,#6b6b6b);">
          <a href="{{button_url}}" style="color:var(--text-soft,#6b6b6b);font-weight:600;">{{button_text}}</a>
          <span style="margin:0 8px;color:var(--border-soft,#ece6df);">{{group_1_text}}</span>
          <span style="color:var(--color-primary,#E8702A);font-weight:700;">{{group_2_text}}</span>
        </nav>
        <h1 style="font-size:clamp(32px,5vw,52px);line-height:1.1;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 14px;">{{title}}</h1>
        <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:560px;margin:0;">{{subtitle}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "button_text": {"type": "textarea", "label": "Button Text"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "title": {"type": "text", "label": "Title"}, "subtitle": {"type": "textarea", "label": "Subtitle"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "/", "button_text": "Ana Sayfa", "group_1_text": "/", "group_2_text": "İletişim", "title": "İletişim", "subtitle": "Ürün, fiyat ve eğitim talepleriniz için bize ulaşın."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-iletisim-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CONTACT SPLIT',
             'html_template'=><<<'EDHTML'
<!-- CONTACT SPLIT -->

    <section style="padding:72px 0;background:#fff;">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:40px;align-items:stretch;">

        <!-- SOL: iletişim bilgi kartları -->
        <div style="flex:1 1 380px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(24px,3.5vw,32px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 26px;line-height:1.18;">{{group_1_title}}</h2>

          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
            <!-- Telefon -->
            <a href="{{group_1_button_url}}" style="display:flex;align-items:flex-start;gap:14px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 22px;">
              <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;color:#fff;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/></svg></span>
              <span style="display:block;">
                <span style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft,#6b6b6b);margin-bottom:4px;">{{group_1_text}}</span>
                <span style="display:block;font-weight:800;color:var(--text-main,#2a2a2a);font-size:16px;">{{group_2_text}}</span>
              </span>
            </a>
            <!-- WhatsApp -->
            <a href="{{group_2_button_url}}" target="_blank" rel="noopener" style="display:flex;align-items:flex-start;gap:14px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 22px;">
              <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;color:#fff;"><svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true" style="width:21px;height:21px;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg></span>
              <span style="display:block;">
                <span style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft,#6b6b6b);margin-bottom:4px;">{{group_1_text_2}}</span>
                <span style="display:block;font-weight:800;color:var(--text-main,#2a2a2a);font-size:16px;">{{group_2_text_2}}</span>
              </span>
            </a>
            <!-- E-posta -->
            <a href="{{group_3_button_url}}" style="display:flex;align-items:flex-start;gap:14px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 22px;">
              <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;color:#fff;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
              <span style="display:block;">
                <span style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft,#6b6b6b);margin-bottom:4px;">{{group_1_text_3}}</span>
                <span style="display:block;font-weight:800;color:var(--text-main,#2a2a2a);font-size:16px;word-break:break-word;">{{group_2_text_3}}</span>
              </span>
            </a>
            <!-- Adres -->
            <a href="{{group_4_button_url}}" target="_blank" rel="noopener" style="display:flex;align-items:flex-start;gap:14px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 22px;">
              <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;color:#fff;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
              <span style="display:block;">
                <span style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft,#6b6b6b);margin-bottom:4px;">{{group_1_text_4}}</span>
                <span style="display:block;font-weight:700;color:var(--text-main,#2a2a2a);font-size:14.5px;line-height:1.55;">{{group_2_text_4}}</span>
              </span>
            </a>
          </div>

          <!-- Çalışma saatleri -->
          <div style="display:flex;align-items:center;gap:14px;margin-top:18px;background:var(--primary-soft,#FCE9DC);border-radius:var(--radius-card,18px);padding:18px 22px;">
            <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:#fff;display:grid;place-items:center;color:var(--color-primary,#E8702A);"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <div>
              <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--primary-deep,#C85716);margin-bottom:4px;">{{group_1_text_5}}</div>
              <div style="font-weight:800;color:var(--text-main,#2a2a2a);font-size:16px;">{{group_2_text_5}}</div>
            </div>
          </div>

          <!-- Instagram -->
          <a href="{{group_1_button_url_2}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:10px;margin-top:18px;color:var(--color-primary,#E8702A);font-weight:700;font-size:15px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg> {{group_1_button_text}}
          </a>
        </div>

        <!-- SAĞ: Google Maps -->
        <div style="flex:1 1 380px;display:flex;">
          <iframe title="Estetik Dermal — Kuşadası / Aydın konum haritası" src="{{group_2_media_url}}" style="width:100%;min-height:380px;border:0;border-radius:var(--radius-card,18px);box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_4_button_url": {"type": "text", "label": "Group 4 Button Url"}, "group_1_button_url_2": {"type": "text", "label": "Group 1 Button Url 2"}, "group_2_media_url": {"type": "image", "label": "Group 2 Media Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_1_text_5": {"type": "textarea", "label": "Group 1 Text 5"}, "group_2_text_5": {"type": "textarea", "label": "Group 2 Text 5"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "tel:+902566121813", "group_2_button_url": "https://wa.me/905426205100", "group_3_button_url": "mailto:info@estetikdermal.com", "group_4_button_url": "https://maps.google.com/maps?q=Kusadasi%20Aydin", "group_1_button_url_2": "https://www.instagram.com/estetikdermal/", "group_2_media_url": "https://maps.google.com/maps?q=Kusadasi%20Aydin&t=&z=13&ie=UTF8&iwloc=&output=embed", "group_1_subtitle": "Bize Ulaşın", "group_1_title": "İletişim bilgilerimiz", "group_1_text": "Telefon", "group_2_text": "0 256 612 18 13", "group_1_text_2": "WhatsApp", "group_2_text_2": "+90 542 620 51 00", "group_1_text_3": "E-posta", "group_2_text_3": "info@estetikdermal.com", "group_1_text_4": "Adres", "group_2_text_4": "Türkmen Mah. Turgut Özel Bulvarı Ada Modern A Blok No 83/3A Kuşadası/Aydın", "group_1_text_5": "Çalışma Saatleri", "group_2_text_5": "Hafta içi 09:00–18:00", "group_1_button_text": "Instagram'da takip edin →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-iletisim-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CONTACT FORM (KVKK\'lı)',
             'html_template'=><<<'EDHTML'
<!-- CONTACT FORM (KVKK'lı) -->

    <section style="padding:0 0 84px;background:#fff;">
      <div class="container">
        <div style="max-width:760px;margin:0 auto;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:24px;box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:clamp(28px,5vw,48px);">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(24px,3.5vw,32px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;line-height:1.18;">{{title}}</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:15px;line-height:1.65;margin:0 0 8px;">{{group_2_description}}</p>
          <p style="font-size:13px;color:var(--text-soft,#6b6b6b);background:var(--primary-soft,#FCE9DC);border-radius:10px;padding:10px 14px;margin:0 0 28px;line-height:1.55;">{{group_3_subtitle}} <code style="font-size:12.5px;">{{group_3_text}}</code> {{group_3_subtitle_2}}</p>

          <form action="#" method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <!-- Ad Soyad -->
            <div style="display:flex;flex-direction:column;gap:7px;">
              <label for="ad" style="font-weight:700;font-size:14px;color:var(--text-main,#2a2a2a);">{{group_1_text}}</label>
              <input type="text" id="ad" name="ad" autocomplete="name" placeholder="Adınız ve soyadınız" style="width:100%;padding:13px 15px;border:1.5px solid var(--border-soft,#ece6df);border-radius:12px;background:#fff;font-size:15px;color:var(--text-main,#2a2a2a);">
            </div>

            <!-- Telefon -->
            <div style="display:flex;flex-direction:column;gap:7px;">
              <label for="telefon" style="font-weight:700;font-size:14px;color:var(--text-main,#2a2a2a);">{{group_2_text}}</label>
              <input type="tel" id="telefon" name="telefon" autocomplete="tel" placeholder="0 5xx xxx xx xx" style="width:100%;padding:13px 15px;border:1.5px solid var(--border-soft,#ece6df);border-radius:12px;background:#fff;font-size:15px;color:var(--text-main,#2a2a2a);">
            </div>

            <!-- E-posta -->
            <div style="display:flex;flex-direction:column;gap:7px;">
              <label for="eposta" style="font-weight:700;font-size:14px;color:var(--text-main,#2a2a2a);">{{group_3_text_2}}</label>
              <input type="email" id="eposta" name="eposta" autocomplete="email" placeholder="ornek@eposta.com" style="width:100%;padding:13px 15px;border:1.5px solid var(--border-soft,#ece6df);border-radius:12px;background:#fff;font-size:15px;color:var(--text-main,#2a2a2a);">
            </div>

            <!-- Marka / Konu -->
            <div style="display:flex;flex-direction:column;gap:7px;">
              <label for="konu" style="font-weight:700;font-size:14px;color:var(--text-main,#2a2a2a);">{{group_4_text}}</label>
              <select id="konu" name="konu" style="width:100%;padding:13px 15px;border:1.5px solid var(--border-soft,#ece6df);border-radius:12px;background:#fff;font-size:15px;color:var(--text-main,#2a2a2a);">
                <option value="skintech">{{group_1_text_2}}</option>
                <option value="seffiline">{{group_2_text_2}}</option>
                <option value="grand-aespio">{{group_3_text_3}}</option>
                <option value="woorhi">{{group_4_text_2}}</option>
                <option value="mi-medical">{{group_5_text}}</option>
                <option value="genel">{{group_6_text}}</option>
              </select>
            </div>

            <!-- Mesaj -->
            <div style="display:flex;flex-direction:column;gap:7px;grid-column:1 / -1;">
              <label for="mesaj" style="font-weight:700;font-size:14px;color:var(--text-main,#2a2a2a);">{{group_5_text_2}}</label>
              <textarea id="mesaj" name="mesaj" rows="5" placeholder="Talebinizi kısaca yazın..." style="width:100%;padding:13px 15px;border:1.5px solid var(--border-soft,#ece6df);border-radius:12px;background:#fff;font-size:15px;color:var(--text-main,#2a2a2a);line-height:1.6;resize:vertical;font-family:inherit;"></textarea>
            </div>

            <!-- KVKK onay -->
            <div style="grid-column:1 / -1;display:flex;align-items:flex-start;gap:12px;">
              <input type="checkbox" id="kvkk" name="kvkk" style="width:20px;height:20px;flex-shrink:0;margin-top:2px;accent-color:var(--color-primary,#E8702A);">
              <label for="kvkk" style="font-size:14px;color:var(--text-soft,#6b6b6b);line-height:1.6;">{{group_6_text_2}}</label>
            </div>

            <!-- Gönder -->
            <div style="grid-column:1 / -1;">
              <button type="submit" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#E8702A);color:#fff;border:0;padding:15px 34px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 10px 30px rgba(232,112,42,.32);">{{group_7_button_text}}</button>
            </div>

          </form>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "title": {"type": "text", "label": "Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_3_subtitle": {"type": "textarea", "label": "Group 3 Subtitle"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_subtitle_2": {"type": "textarea", "label": "Group 3 Subtitle 2"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text_3": {"type": "textarea", "label": "Group 3 Text 3"}, "group_4_text_2": {"type": "textarea", "label": "Group 4 Text 2"}, "group_5_text": {"type": "textarea", "label": "Group 5 Text"}, "group_6_text": {"type": "textarea", "label": "Group 6 Text"}, "group_5_text_2": {"type": "textarea", "label": "Group 5 Text 2"}, "group_6_text_2": {"type": "textarea", "label": "Group 6 Text 2"}, "group_7_button_text": {"type": "textarea", "label": "Group 7 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_subtitle": "Talep Formu", "title": "Bize yazın, size dönelim", "group_2_description": "Aşağıdaki formu doldurun; ekibimiz en kısa sürede sizinle iletişime geçsin.", "group_3_subtitle": "Not: Form CMS'e bağlandığında", "group_3_text": "/api/v1/forms/.../submit", "group_3_subtitle_2": "ile çalışacaktır.", "group_1_text": "Ad Soyad", "group_2_text": "Telefon", "group_3_text_2": "E-posta", "group_4_text": "İlgilendiğiniz Marka / Konu", "group_1_text_2": "Skin Tech", "group_2_text_2": "Seffiline", "group_3_text_3": "Grand Aespio", "group_4_text_2": "Woorhi", "group_5_text": "Mi Medical", "group_6_text": "Genel", "group_5_text_2": "Mesaj", "group_6_text_2": "Kişisel verilerimin işlenmesine ilişkin aydınlatma metnini okudum, onaylıyorum.", "group_7_button_text": "Gönder →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-skintech-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'HERO (split)',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link rel="stylesheet" href="{{group_3_link_url}}"><style>
    :root{
      --color-primary:#0C6E72;
      --color-accent:#3FBFA8;
      --color-secondary:#EAF6F4;
      --primary-deep:#0B2E34;
      --primary-soft:#D6EFEB;
      --accent-soft:#E3F6F1;
      --grad-brand:linear-gradient(135deg,#0C6E72 0%,#3FBFA8 100%);
      --shadow-card:0 14px 44px rgba(12,110,114,.12);
      --shadow-soft:0 4px 18px rgba(11,46,52,.08);
    }
    body{ font-family:"Manrope","Segoe UI",system-ui,-apple-system,Arial,sans-serif; }
    a.st-card{ transition:transform .2s, box-shadow .2s, border-color .2s; }
    a.st-card:hover{ transform:translateY(-4px); box-shadow:0 18px 50px rgba(12,110,114,.16); border-color:#3FBFA8; }
  </style><!-- HERO (split) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(1100px 560px at 82% -8%, rgba(63,191,168,.20), transparent 62%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#EAF6F4) 100%);">
      <!-- ince grid çizgi dokusu -->
      <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(12,110,114,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(12,110,114,.05) 1px,transparent 1px);background-size:46px 46px;mask-image:linear-gradient(180deg,#000,transparent 80%);"></div>
      <div class="container" style="position:relative;display:flex;flex-wrap:wrap;gap:48px;align-items:center;padding:80px 0 92px;">
        <div style="flex:1 1 480px;">
          <p style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--primary-deep,#0B2E34);border:1px solid var(--color-accent,#3FBFA8);border-radius:999px;padding:8px 16px;font-size:12px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;margin:0 0 24px;">{{group_1_subtitle}}</p>
          <h1 style="font-size:clamp(34px,5.2vw,58px);line-height:1.06;font-weight:800;color:var(--primary-deep,#0B2E34);margin:0 0 22px;letter-spacing:-.5px;">{{group_1_title}}<br><span style="background:var(--grad-brand,linear-gradient(135deg,#0C6E72,#3FBFA8));-webkit-background-clip:text;background-clip:text;color:transparent;">{{group_1_text}}</span></h1>
          <p style="font-size:clamp(16px,2vw,19px);color:#3A5860;line-height:1.75;max-width:580px;margin:0 0 34px;">{{{group_2_body_html}}}</p>
          <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a href="{{group_1_button_url}}" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#0C6E72);color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 12px 32px rgba(12,110,114,.30);">{{group_1_button_text}}</a>
            <a href="{{group_2_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;background:#25D366;color:#fff;border:1.5px solid #25D366;padding:15px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;"><svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_2_button_text}}</a>
          </div>
          <div style="display:flex;gap:26px;flex-wrap:wrap;margin-top:40px;">
            <div><div style="font-size:12px;color:#5A7B82;letter-spacing:.3px;">{{group_1_text_2}}</div><div style="font-weight:800;color:var(--primary-deep,#0B2E34);">{{group_2_text}}</div></div>
            <div style="width:1px;background:var(--border-soft,#cfe6e1);"></div>
            <div><div style="font-size:12px;color:#5A7B82;letter-spacing:.3px;">{{group_1_text_3}}</div><div style="font-weight:800;color:var(--primary-deep,#0B2E34);">{{group_2_text_2}}</div></div>
            <div style="width:1px;background:var(--border-soft,#cfe6e1);"></div>
            <div><div style="font-size:12px;color:#5A7B82;letter-spacing:.3px;">{{group_1_text_4}}</div><div style="font-weight:800;color:var(--primary-deep,#0B2E34);">{{group_2_text_3}}</div></div>
          </div>
        </div>
        <!-- 🖼️ GÖRSEL: /assets/img/skintech-hero.jpg (oran 4:3)
             PROMPT: "Clinical dermocosmetic hero composition, a single premium RRS skinbooster ampoule and glass serum vial standing on a clean white laboratory surface, soft teal and mint reflections, sterile dermatological studio lighting, shallow depth of field, droplet of hyaluronic serum, scientific and pristine aesthetic, white-and-teal color palette; photorealistic, detailed, high resolution; no text, no logo, no watermark"
             DEĞİŞTİR → görsel kartının içindeki .aspect-ratio:4/3 placeholder bloğunu (🧬 emojili) şununla değiştir:
             <img src="/assets/img/skintech-hero.jpg" alt="Skin Tech RRS skinbooster ampul ve serum şişesi klinik laboratuvar çekimi" style="width:100%;height:100%;object-fit:cover;border-radius:16px;"> -->
        <!-- Bilimsel görsel kompozisyon (CSS placeholder) -->
        <div style="position:relative;min-height:430px;flex:1 1 340px;">
          <div style="position:absolute;inset:0;background:var(--grad-brand,linear-gradient(135deg,#0C6E72,#3FBFA8));border-radius:28px;transform:rotate(-3deg);opacity:.14;"></div>
          <div style="position:relative;background:#fff;border:1px solid var(--border-soft,#cfe6e1);border-radius:24px;box-shadow:var(--shadow-card,0 14px 44px rgba(12,110,114,.12));padding:26px;">
            <div style="aspect-ratio:4/3;border-radius:16px;background:linear-gradient(135deg,var(--accent-soft,#E3F6F1),var(--primary-soft,#D6EFEB)) url('/assets/img/skintech-hero.jpg') center/cover no-repeat;display:grid;place-items:center;position:relative;overflow:hidden;">
              <span style="position:absolute;top:14px;left:16px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:var(--color-primary,#0C6E72);text-transform:uppercase;">{{group_1_text_5}}</span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:18px;">
              <div><div style="font-size:11px;color:var(--color-primary,#0C6E72);font-weight:800;text-transform:uppercase;letter-spacing:1.2px;">{{group_1_text_6}}</div><div style="font-weight:800;color:var(--primary-deep,#0B2E34);">{{group_2_text_4}}</div></div>
              <span style="background:var(--primary-soft,#D6EFEB);color:var(--primary-deep,#0B2E34);font-size:11px;font-weight:800;padding:6px 12px;border-radius:999px;letter-spacing:.5px;">{{group_2_text_5}}</span>
            </div>
          </div>
          <div style="position:absolute;bottom:-18px;left:-18px;background:#fff;border:1px solid var(--border-soft,#cfe6e1);border-radius:16px;box-shadow:var(--shadow-soft,0 4px 18px rgba(11,46,52,.08));padding:13px 18px;display:flex;align-items:center;gap:10px;">
            <span style="width:34px;height:34px;flex-shrink:0;border-radius:10px;background:var(--primary-soft,#D6EFEB);"></span>
            <div><div style="font-size:11px;color:#5A7B82;">{{group_1_text_7}}</div><div style="font-weight:800;font-size:14px;color:var(--primary-deep,#0B2E34);">{{group_2_text_6}}</div></div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_1_text_5": {"type": "textarea", "label": "Group 1 Text 5"}, "group_1_text_6": {"type": "textarea", "label": "Group 1 Text 6"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_2_text_5": {"type": "textarea", "label": "Group 2 Text 5"}, "group_1_text_7": {"type": "textarea", "label": "Group 1 Text 7"}, "group_2_text_6": {"type": "textarea", "label": "Group 2 Text 6"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap", "group_1_button_url": "/urunler", "group_2_button_url": "https://wa.me/905426205100", "group_1_subtitle": "İSPANYA · KLİNİK DERMOKOZMETİK", "group_1_title": "Klinik kanıtlı", "group_1_text": "cilt bilimi", "group_2_body_html": "Skin Tech Pharma Group; kimyasal peeling, mezoterapi ve RRS® skinbooster serisinde dünya çapında öncü. Laboratuvar disiplini, dermatolojik kanıt ve CE Class III standartlarıyla geliştirilen profesyonel çözümler.", "group_1_button_text": "Ürünleri Gör →", "group_2_button_text": "WhatsApp Danışma", "group_1_text_2": "Sertifikasyon", "group_2_text": "CE Class III", "group_1_text_3": "Menşei", "group_2_text_2": "İspanya", "group_1_text_4": "Portföy", "group_2_text_3": "84+ Ürün", "group_1_text_5": "RRS® Skinbooster", "group_1_text_6": "Skin Tech · RRS", "group_2_text_4": "RRS® HA Long Lasting", "group_2_text_5": "CE III", "group_1_text_7": "Dermatolojik", "group_2_text_6": "Klinik Test Edildi"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-skintech-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'GÜVEN / KREDİBİLİTE ŞERİDİ',
             'html_template'=><<<'EDHTML'
<!-- GÜVEN / KREDİBİLİTE ŞERİDİ -->

    <section style="background:var(--primary-deep,#0B2E34);">
      <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1px;padding:0;background:rgba(255,255,255,.08);">
        <div style="background:var(--primary-deep,#0B2E34);padding:30px 24px;text-align:center;">
          <div style="width:34px;height:34px;margin:0 auto 10px;border-radius:10px;background:rgba(63,191,168,.18);border:1px solid rgba(63,191,168,.45);"></div>
          <div style="font-weight:800;color:#fff;font-size:16px;">{{group_2_text}}</div>
          <div style="color:rgba(255,255,255,.6);font-size:13px;margin-top:3px;">{{group_3_text}}</div>
        </div>
        <div style="background:var(--primary-deep,#0B2E34);padding:30px 24px;text-align:center;">
          <div style="width:34px;height:34px;margin:0 auto 10px;border-radius:10px;background:rgba(63,191,168,.18);border:1px solid rgba(63,191,168,.45);"></div>
          <div style="font-weight:800;color:#fff;font-size:16px;">{{group_2_text_2}}</div>
          <div style="color:rgba(255,255,255,.6);font-size:13px;margin-top:3px;">{{group_3_text_2}}</div>
        </div>
        <div style="background:var(--primary-deep,#0B2E34);padding:30px 24px;text-align:center;">
          <div style="width:34px;height:34px;margin:0 auto 10px;border-radius:10px;background:rgba(63,191,168,.18);border:1px solid rgba(63,191,168,.45);"></div>
          <div style="font-weight:800;color:#fff;font-size:16px;">{{group_2_text_3}}</div>
          <div style="color:rgba(255,255,255,.6);font-size:13px;margin-top:3px;">{{group_3_text_3}}</div>
        </div>
        <div style="background:var(--primary-deep,#0B2E34);padding:30px 24px;text-align:center;">
          <div style="width:34px;height:34px;margin:0 auto 10px;border-radius:10px;background:rgba(63,191,168,.18);border:1px solid rgba(63,191,168,.45);"></div>
          <div style="font-weight:800;color:#fff;font-size:16px;">{{group_2_text_4}}</div>
          <div style="color:rgba(255,255,255,.6);font-size:13px;margin-top:3px;">{{group_3_text_4}}</div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_3_text_3": {"type": "textarea", "label": "Group 3 Text 3"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_3_text_4": {"type": "textarea", "label": "Group 3 Text 4"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_2_text": "CE Class III", "group_3_text": "Tıbbi cihaz sertifikası", "group_2_text_2": "Klinik Test", "group_3_text_2": "Kanıta dayalı formülasyon", "group_2_text_3": "İspanya Menşeli", "group_3_text_3": "Avrupa üretim standardı", "group_2_text_4": "84+ Ürün", "group_3_text_4": "Geniş profesyonel portföy"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-skintech-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'ÜRÜN AİLELERİ',
             'html_template'=><<<'EDHTML'
<!-- ÜRÜN AİLELERİ -->

    <section style="padding:88px 0;background:#fff;">
      <div class="container">
        <div style="text-align:center;max-width:660px;margin:0 auto 56px;">
          <p style="color:var(--color-primary,#0C6E72);font-weight:800;letter-spacing:1.8px;text-transform:uppercase;font-size:12px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(28px,4vw,40px);font-weight:800;color:var(--primary-deep,#0B2E34);margin:0 0 14px;line-height:1.14;letter-spacing:-.4px;">{{group_1_title}}</h2>
          <p style="color:#3A5860;font-size:17px;line-height:1.7;">{{{group_2_body_html}}}</p>
        </div>

        <!-- 🖼️ GÖRSEL (tekrarlayan ürün ailesi kartı görsel deseni): /assets/img/skintech-product-{slug}.jpg (oran 1:1)
             Örn: skintech-product-rrs-ha-long-lasting.jpg, skintech-product-lumina-vitc.jpg, skintech-product-aclaranse.jpg, skintech-product-melablock-spf50.jpg
             PROMPT: "Clinical packshot of a professional dermocosmetic product (serum vial / mesotherapy ampoule / chemical peel bottle), pristine white background with subtle teal-mint gradient, sterile laboratory lighting, sharp focus, scientific and dermatological aesthetic, soft shadow, teal accent reflection; photorealistic, detailed, high resolution; no text, no logo, no watermark"
             KULLANIM → Her ürün kartının başına (kart başlık bandının üstüne) küçük bir görsel alanı eklenebilir, örn:
             <img src="/assets/img/skintech-product-rrs-ha-long-lasting.jpg" alt="Skin Tech RRS HA Long Lasting ürün çekimi" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:14px;"> -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;">

          <!-- RRS Skinbooster -->
          <div style="background:#fff;border:1px solid var(--border-soft,#cfe6e1);border-radius:var(--radius-card,18px);overflow:hidden;">
            <div style="background:linear-gradient(135deg,var(--primary-deep,#0B2E34),var(--color-primary,#0C6E72));padding:24px 26px;display:flex;align-items:center;justify-content:space-between;">
              <div><div style="font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:var(--color-accent,#3FBFA8);">{{group_1_text}}</div><h3 style="font-size:21px;font-weight:800;color:#fff;margin:4px 0 0;">{{group_1_title_2}}</h3></div>
            </div>
            <div style="padding:8px 16px 18px;">
              <a href="{{group_2_button_url}}" class="st-card" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:16px 14px;border-radius:12px;border:1px solid transparent;background:var(--color-secondary,#EAF6F4);margin-top:10px;">
                <span><span style="display:block;font-weight:800;color:var(--primary-deep,#0B2E34);font-size:15px;">{{group_1_text_2}}</span><span style="display:block;color:#5A7B82;font-size:13px;margin-top:2px;">{{group_2_text}}</span></span>
                <span style="color:var(--color-primary,#0C6E72);font-weight:800;flex-shrink:0;">{{group_2_text_2}}</span>
              </a>
            </div>
          </div>

          <!-- Benebellum Mezoterapi -->
          <div style="background:#fff;border:1px solid var(--border-soft,#cfe6e1);border-radius:var(--radius-card,18px);overflow:hidden;">
            <div style="background:linear-gradient(135deg,var(--primary-deep,#0B2E34),var(--color-primary,#0C6E72));padding:24px 26px;display:flex;align-items:center;justify-content:space-between;">
              <div><div style="font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:var(--color-accent,#3FBFA8);">{{group_1_text_3}}</div><h3 style="font-size:21px;font-weight:800;color:#fff;margin:4px 0 0;">{{group_1_title_3}}</h3></div>
            </div>
            <div style="padding:8px 16px 18px;">
              {{{st_card_items_html}}}</div>
          </div>

          <!-- Kimyasal Peeling -->
          <div style="background:#fff;border:1px solid var(--border-soft,#cfe6e1);border-radius:var(--radius-card,18px);overflow:hidden;">
            <div style="background:linear-gradient(135deg,var(--primary-deep,#0B2E34),var(--color-primary,#0C6E72));padding:24px 26px;display:flex;align-items:center;justify-content:space-between;">
              <div><div style="font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:var(--color-accent,#3FBFA8);">{{group_1_text_4}}</div><h3 style="font-size:21px;font-weight:800;color:#fff;margin:4px 0 0;">{{group_1_title_4}}</h3></div>
            </div>
            <div style="padding:8px 16px 18px;">
              <a href="{{item_1_button_url}}" class="st-card" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 14px;border-radius:12px;border:1px solid transparent;background:var(--color-secondary,#EAF6F4);margin-top:10px;">
                <span><span style="display:block;font-weight:800;color:var(--primary-deep,#0B2E34);font-size:15px;">{{group_1_text_5}}</span><span style="display:block;color:#5A7B82;font-size:13px;margin-top:2px;">{{group_2_text_3}}</span></span>
                <span style="color:var(--color-primary,#0C6E72);font-weight:800;flex-shrink:0;">{{group_2_text_4}}</span>
              </a>
              <a href="{{item_2_button_url}}" class="st-card" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 14px;border-radius:12px;border:1px solid transparent;background:var(--color-secondary,#EAF6F4);margin-top:10px;">
                <span><span style="display:block;font-weight:800;color:var(--primary-deep,#0B2E34);font-size:15px;">{{group_1_text_6}}</span><span style="display:block;color:#5A7B82;font-size:13px;margin-top:2px;">{{group_2_text_5}}</span></span>
                <span style="color:var(--color-primary,#0C6E72);font-weight:800;flex-shrink:0;">{{group_2_text_6}}</span>
              </a>
            </div>
          </div>

          <!-- Güneş Koruma -->
          <div style="background:#fff;border:1px solid var(--border-soft,#cfe6e1);border-radius:var(--radius-card,18px);overflow:hidden;">
            <div style="background:linear-gradient(135deg,var(--primary-deep,#0B2E34),var(--color-primary,#0C6E72));padding:24px 26px;display:flex;align-items:center;justify-content:space-between;">
              <div><div style="font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:var(--color-accent,#3FBFA8);">{{group_1_text_7}}</div><h3 style="font-size:21px;font-weight:800;color:#fff;margin:4px 0 0;">{{group_1_title_5}}</h3></div>
            </div>
            <div style="padding:8px 16px 18px;">
              <a href="{{group_2_button_url_2}}" class="st-card" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:16px 14px;border-radius:12px;border:1px solid transparent;background:var(--color-secondary,#EAF6F4);margin-top:10px;">
                <span><span style="display:block;font-weight:800;color:var(--primary-deep,#0B2E34);font-size:15px;">{{group_1_text_8}}</span><span style="display:block;color:#5A7B82;font-size:13px;margin-top:2px;">{{group_2_text_7}}</span></span>
                <span style="color:var(--color-primary,#0C6E72);font-weight:800;flex-shrink:0;">{{group_2_text_8}}</span>
              </a>
            </div>
          </div>

        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"st_card_items": {"type": "repeater", "label": "St Card Items", "repeat_kind": "items", "item_template": "<a href=\"{{button_url}}\" class=\"st-card\" style=\"display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 14px;border-radius:12px;border:1px solid transparent;background:var(--color-secondary,#EAF6F4);margin-top:10px;\">\n                <span><span style=\"display:block;font-weight:800;color:var(--primary-deep,#0B2E34);font-size:15px;\">{{text}}</span><span style=\"display:block;color:#5A7B82;font-size:13px;margin-top:2px;\">{{text_2}}</span></span>\n                <span style=\"color:var(--color-primary,#0C6E72);font-weight:800;flex-shrink:0;\">{{text_3}}</span>\n              </a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}}}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "item_1_button_url": {"type": "text", "label": "Item 1 Button Url"}, "item_2_button_url": {"type": "text", "label": "Item 2 Button Url"}, "group_2_button_url_2": {"type": "text", "label": "Group 2 Button Url 2"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_title_2": {"type": "text", "label": "Group 1 Title 2"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_1_title_3": {"type": "text", "label": "Group 1 Title 3"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_1_title_4": {"type": "text", "label": "Group 1 Title 4"}, "group_1_text_5": {"type": "textarea", "label": "Group 1 Text 5"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_1_text_6": {"type": "textarea", "label": "Group 1 Text 6"}, "group_2_text_5": {"type": "textarea", "label": "Group 2 Text 5"}, "group_2_text_6": {"type": "textarea", "label": "Group 2 Text 6"}, "group_1_text_7": {"type": "textarea", "label": "Group 1 Text 7"}, "group_1_title_5": {"type": "text", "label": "Group 1 Title 5"}, "group_1_text_8": {"type": "textarea", "label": "Group 1 Text 8"}, "group_2_text_7": {"type": "textarea", "label": "Group 2 Text 7"}, "group_2_text_8": {"type": "textarea", "label": "Group 2 Text 8"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"st_card_items": [{"button_url": "/urun-detay", "text": "LUMINA VİT-C 18%", "text_2": "Aydınlatıcı C vitamini", "text_3": "→"}, {"button_url": "/urun-detay", "text": "VİT A + E", "text_2": "Antioksidan onarım", "text_3": "→"}, {"button_url": "/urun-detay", "text": "TX SOLUTION", "text_2": "Leke karşıtı çözüm", "text_3": "→"}], "group_2_button_url": "/urun-detay", "item_1_button_url": "/urun-detay", "item_2_button_url": "/urun-detay", "group_2_button_url_2": "/urun-detay", "group_1_subtitle": "Ürün Aileleri", "group_1_title": "Bilim temelli dört temel seri", "group_2_body_html": "Skinbooster'dan mezoterapiye, kimyasal peelingden güneş korumaya — her seri dermatolojik kanıt ve klinik standartla geliştirildi.", "group_1_text": "Skinbooster", "group_1_title_2": "RRS® Skinbooster", "group_1_text_2": "RRS® HA Long Lasting", "group_2_text": "Çapraz bağlı HA · CE Class III", "group_2_text_2": "→", "group_1_text_3": "Mezoterapi", "group_1_title_3": "Benebellum", "group_1_text_4": "Peeling", "group_1_title_4": "Kimyasal Peeling", "group_1_text_5": "Aclaranse", "group_2_text_3": "Depigmentasyon peelingi", "group_2_text_4": "→", "group_1_text_6": "Easy Phytic", "group_2_text_5": "Nötralizasyonsuz fitik asit", "group_2_text_6": "→", "group_1_text_7": "SPF", "group_1_title_5": "Güneş Koruma", "group_1_text_8": "Melablock HSP SPF 50+", "group_2_text_7": "Yüksek faktör · leke koruması", "group_2_text_8": "→"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-skintech-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'RRS SPOTLIGHT',
             'html_template'=><<<'EDHTML'
<!-- RRS SPOTLIGHT -->

    <section style="padding:88px 0;background:var(--color-secondary,#EAF6F4);">
      <div class="container">
        <div style="display:flex;flex-wrap:wrap;gap:52px;align-items:center;">
          <!-- 🖼️ GÖRSEL: /assets/img/skintech-rrs.jpg (oran 3:4)
               PROMPT: "Macro photorealistic shot of RRS HA Long Lasting cross-linked hyaluronic acid skinbooster, a sleek medical-grade vial with a glistening droplet of clear viscous gel, deep teal-to-mint gradient backdrop, dermatological CE Class III medical device aesthetic, sterile precise studio lighting, luminous and scientific, water-clarity refraction; photorealistic, detailed, high resolution; no text, no logo, no watermark"
               DEĞİŞTİR → bu görsel kutusundaki 💧 emojili iç placeholder bloğunu şununla değiştir:
               <img src="/assets/img/skintech-rrs.jpg" alt="RRS HA Long Lasting çapraz bağlı hyalüronik asit skinbooster ürün çekimi" style="width:100%;height:100%;object-fit:cover;border-radius:26px;"> -->
          <!-- Görsel -->
          <div style="position:relative;min-height:380px;flex:1 1 340px;">
            <div style="position:absolute;inset:0;background:var(--grad-brand,linear-gradient(135deg,#0C6E72,#3FBFA8));border-radius:26px;opacity:.16;transform:rotate(3deg);"></div>
            <div style="position:relative;height:100%;min-height:380px;border-radius:26px;overflow:hidden;background:linear-gradient(135deg,var(--accent-soft,#E3F6F1),var(--primary-soft,#D6EFEB)) url('/assets/img/skintech-rrs.jpg') center/cover no-repeat;display:grid;place-items:center;">
              <div style="position:relative;text-align:center;">
                <div style="display:inline-block;background:#fff;border:1px solid var(--color-accent,#3FBFA8);color:var(--color-primary,#0C6E72);font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:7px 16px;border-radius:999px;">{{group_2_text}}</div>
              </div>
            </div>
          </div>
          <!-- Açıklama -->
          <div style="flex:1 1 440px;">
            <p style="display:inline-flex;align-items:center;gap:8px;color:var(--color-primary,#0C6E72);font-weight:800;letter-spacing:1.6px;text-transform:uppercase;font-size:12px;margin:0 0 14px;">{{group_1_subtitle}}</p>
            <h2 style="font-size:clamp(27px,4vw,40px);font-weight:800;color:var(--primary-deep,#0B2E34);margin:0 0 18px;line-height:1.15;letter-spacing:-.4px;">{{group_2_title}}</h2>
            <p style="color:#3A5860;font-size:17px;line-height:1.78;margin:0 0 24px;">{{group_2_subtitle}} <strong style="color:var(--primary-deep,#0B2E34);">{{group_2_text_2}}</strong>{{{group_2_body_html}}}</p>
            <ul style="list-style:none;margin:0 0 30px;padding:0;display:grid;gap:13px;">
              <li style="display:flex;align-items:center;gap:12px;color:var(--primary-deep,#0B2E34);font-weight:600;"><span style="width:26px;height:26px;flex-shrink:0;border-radius:8px;background:var(--primary-soft,#D6EFEB);color:var(--color-primary,#0C6E72);display:grid;place-items:center;font-weight:800;">{{group_1_text}}</span> {{group_1_item_text}}</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--primary-deep,#0B2E34);font-weight:600;"><span style="width:26px;height:26px;flex-shrink:0;border-radius:8px;background:var(--primary-soft,#D6EFEB);color:var(--color-primary,#0C6E72);display:grid;place-items:center;font-weight:800;">{{group_2_text_3}}</span> {{group_2_item_text}}</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--primary-deep,#0B2E34);font-weight:600;"><span style="width:26px;height:26px;flex-shrink:0;border-radius:8px;background:var(--primary-soft,#D6EFEB);color:var(--color-primary,#0C6E72);display:grid;place-items:center;font-weight:800;">{{group_3_text}}</span> {{group_3_item_text}}</li>
            </ul>
            <a href="{{group_2_button_url}}" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#0C6E72);color:#fff;padding:14px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 12px 30px rgba(12,110,114,.28);">{{group_2_button_text}}</a>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": "/urun-detay", "group_2_text": "Çapraz Bağlı HA", "group_1_subtitle": "★ Öne Çıkan Ürün", "group_2_title": "RRS® HA Long Lasting", "group_2_subtitle": "Çapraz bağlı hyalüronik asit içeren", "group_2_text_2": "CE Class III dermal implant", "group_2_body_html": ". Cildin derin nem rezervlerini destekleyerek uzun süreli sıkılık, elastikiyet ve canlılık sağlar. Skinbooster protokollerinde profesyonel kullanım için geliştirilmiştir.", "group_1_text": "✓", "group_1_item_text": "CE Class III tıbbi cihaz sınıflandırması", "group_2_text_3": "✓", "group_2_item_text": "Çapraz bağlı HA ile uzun etkili sonuç", "group_3_text": "✓", "group_3_item_text": "Skinbooster protokolleri için optimize", "group_2_button_text": "Ürün Detayı →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-skintech-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'EĞİTİM & DESTEK NOTU',
             'html_template'=><<<'EDHTML'
<!-- EĞİTİM & DESTEK NOTU -->

    <section style="padding:72px 0;background:#fff;">
      <div class="container">
        <div style="border:1px solid var(--border-soft,#cfe6e1);border-radius:var(--radius-card,18px);padding:clamp(28px,4vw,44px);display:flex;flex-wrap:wrap;gap:28px;align-items:center;background:linear-gradient(180deg,#fff,var(--color-secondary,#EAF6F4));">
          <div style="width:64px;height:64px;flex-shrink:0;border-radius:18px;background:var(--grad-brand,linear-gradient(135deg,#0C6E72,#3FBFA8));"></div>
          <div style="flex:1 1 360px;">
            <h3 style="font-size:clamp(20px,3vw,26px);font-weight:800;color:var(--primary-deep,#0B2E34);margin:0 0 8px;">{{group_2_title}}</h3>
            <p style="color:#3A5860;font-size:16px;line-height:1.7;margin:0;">{{{group_2_body_html}}}</p>
          </div>
          <a href="{{button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--primary-deep,#0B2E34);border:1.5px solid var(--color-accent,#3FBFA8);padding:13px 26px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;flex-shrink:0;">{{button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "group_2_title": "Uygulamalı eğitim ve teknik destek", "group_2_body_html": "Skin Tech ürünleri için Estetik Dermal'dan uygulamalı eğitim ve teknik destek. Hekimlere protokol kurulumundan güvenli uygulamaya kadar birebir rehberlik sunuyoruz.", "button_text": "Eğitim Talep Et →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-skintech-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CTA BANDI',
             'html_template'=><<<'EDHTML'
<!-- CTA BANDI -->

    <section style="padding:20px 0 88px;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:var(--grad-brand,linear-gradient(135deg,#0C6E72,#3FBFA8));padding:clamp(40px,6vw,72px);text-align:center;color:#fff;">
          <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.06) 1px,transparent 1px);background-size:40px 40px;"></div>
          <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.12);"></div>
          <div style="position:absolute;bottom:-60px;left:-30px;width:240px;height:240px;border-radius:50%;background:rgba(255,255,255,.08);"></div>
          <div style="position:relative;">
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.15;letter-spacing:-.4px;">{{group_4_title}}</h2>
            <p style="font-size:18px;opacity:.95;max-width:600px;margin:0 auto 32px;line-height:1.6;">{{group_4_description}}</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="{{group_1_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;background:#25D366;color:#fff;padding:15px 32px;border-radius:var(--radius-button,999px);font-weight:800;font-size:15px;"><svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_1_button_text}}</a>
              <a href="{{group_2_button_url}}" target="_blank" rel="noopener" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.6);padding:15px 32px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">{{group_2_button_text}}</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_4_title": {"type": "text", "label": "Group 4 Title"}, "group_4_description": {"type": "textarea", "label": "Group 4 Description"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "https://wa.me/905426205100", "group_2_button_url": "https://apps.skintechpharmagroup.com/medinet", "group_4_title": "Skin Tech ürünleri hakkında bilgi alın", "group_4_description": "Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.", "group_1_button_text": "WhatsApp ile Yaz", "group_2_button_text": "MEDINET Portalı ↗"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-seffiline-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'1. HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><style>
    /* Seffiline — feminen / zarif / editoryal palet override */
    :root {
      --color-primary: #C98A6D;   /* rose-gold */
      --color-accent:  #E8A0A8;   /* blush pembe */
      --color-secondary: #FBF0EF; /* krem */
      --text-main: #4A2E35;       /* koyu erik */
      --text-soft: #8A6A70;       /* yumuşak erik */
      --border-soft: #EBD9D6;
      --serif: "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif;
      --grad-blush: linear-gradient(160deg, #FBE7E6 0%, #FBF0EF 55%, #FFFFFF 100%);
      --grad-rose: linear-gradient(135deg, #C98A6D 0%, #E8A0A8 100%);
      --shadow-petal: 0 18px 50px rgba(201, 138, 109, .14);
      --shadow-soft: 0 6px 22px rgba(74, 46, 53, .07);
    }
    body { background: #FFFCFB; }
    .sf-serif { font-family: var(--serif); }
    ::selection { background: #F3CFC9; color: #4A2E35; }
    a.sf-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-petal); }
    a.sf-btn-fill:hover { transform: translateY(-2px); box-shadow: 0 14px 36px rgba(201,138,109,.34); }
    a.sf-btn-ghost:hover { background: #fff; border-color: #C98A6D; }
  </style><!-- 1. HERO -->

    <section style="position:relative;overflow:hidden;background:var(--grad-blush,linear-gradient(160deg,#FBE7E6,#FBF0EF,#fff));">
      <!-- organik yuvarlak formlar -->
      <div aria-hidden="true" style="position:absolute;top:-160px;right:-120px;width:480px;height:480px;border-radius:50%;background:radial-gradient(circle at 35% 35%, rgba(232,160,168,.32), transparent 70%);"></div>
      <div aria-hidden="true" style="position:absolute;bottom:-180px;left:-140px;width:420px;height:420px;border-radius:50%;background:radial-gradient(circle at 50% 50%, rgba(201,138,109,.20), transparent 70%);"></div>
      <div aria-hidden="true" style="position:absolute;top:48%;left:54%;width:140px;height:140px;border-radius:50%;border:1px solid rgba(201,138,109,.30);"></div>

      <div class="container" style="position:relative;display:flex;flex-wrap:wrap;gap:56px;align-items:center;padding:96px 0 104px;">
        <div style="flex:1 1 460px;">
          <p style="display:inline-flex;align-items:center;gap:9px;color:var(--color-primary,#C98A6D);font-size:12px;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin:0 0 26px;"><span style="width:26px;height:1px;background:var(--color-primary,#C98A6D);"></span>{{group_1_subtitle}}</p>
          <h1 class="sf-serif" style="font-size:clamp(40px,6vw,72px);line-height:1.05;font-weight:500;color:var(--text-main,#4A2E35);margin:0 0 26px;">{{group_1_title}}<br><em style="font-style:italic;color:var(--color-primary,#C98A6D);">{{group_1_text}}</em></h1>
          <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#8A6A70);line-height:1.85;max-width:520px;margin:0 0 38px;">{{{group_2_body_html}}}</p>
          <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;">
            <a href="{{group_1_button_url}}" class="sf-btn-fill" style="display:inline-flex;align-items:center;gap:9px;background:var(--grad-rose,linear-gradient(135deg,#C98A6D,#E8A0A8));color:#fff;padding:16px 34px;border-radius:999px;font-weight:600;font-size:15px;letter-spacing:.3px;box-shadow:0 12px 30px rgba(201,138,109,.30);transition:transform .2s,box-shadow .2s;">{{group_1_button_text}}</a>
            <a href="{{group_1_button_url_2}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;border:1px solid #25D366;padding:15px 30px;border-radius:999px;font-weight:600;font-size:15px;transition:transform .2s,box-shadow .2s;"><svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_1_button_text_2}}</a>
          </div>
        </div>

        <!-- 🖼️ GÖRSEL: /assets/img/seffiline-hero.jpg (oran 3:4)
             PROMPT: "Feminine editorial beauty composition, elegant skincare and dermal filler bottles arranged with fresh blush-pink petals, soft rose-gold and cream tones, luxury magazine aesthetic, diffused soft natural light, delicate silk fabric backdrop, refined and graceful, glowing warm highlights, high-fashion cosmetics editorial; photorealistic, detailed, high resolution; no text, no logo, no watermark"
             DEĞİŞTİR → bu kompozisyondaki 🌸 emojili .aspect-ratio:3/4 iç placeholder bloğunu şununla değiştir:
             <img src="/assets/img/seffiline-hero.jpg" alt="Seffiline feminen cilt bakımı ve dolgu serisi editoryal ürün çekimi" style="width:100%;height:100%;object-fit:cover;border-radius:170px 170px 18px 18px;"> -->
        <!-- editoryal görsel kompozisyon -->
        <div style="position:relative;flex:1 1 340px;min-height:440px;">
          <div style="position:relative;background:#fff;border:1px solid var(--border-soft,#EBD9D6);border-radius:200px 200px 24px 24px;box-shadow:var(--shadow-petal,0 18px 50px rgba(201,138,109,.14));padding:28px;max-width:380px;margin:0 auto;">
            <div style="aspect-ratio:3/4;border-radius:170px 170px 18px 18px;background:linear-gradient(165deg,#FBE7E6,#F6D8D4 60%,#EFC9C2) url('/assets/img/seffiline-hero.jpg') center/cover no-repeat;"></div>
          </div>
          <div style="position:absolute;top:18px;left:-6px;background:#fff;border:1px solid var(--border-soft,#EBD9D6);border-radius:18px;box-shadow:var(--shadow-soft,0 6px 22px rgba(74,46,53,.07));padding:14px 18px;display:flex;align-items:center;gap:11px;">
            <span style="width:34px;height:34px;flex-shrink:0;border-radius:50%;background:linear-gradient(150deg,#FBE7E6,#F4D4CE);"></span>
            <div><div style="font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#C98A6D);font-weight:700;">{{group_2_text}}</div><div class="sf-serif" style="font-size:16px;color:var(--text-main,#4A2E35);">{{group_2_text_2}}</div></div>
          </div>
          <div style="position:absolute;bottom:24px;right:-8px;background:#fff;border:1px solid var(--border-soft,#EBD9D6);border-radius:18px;box-shadow:var(--shadow-soft,0 6px 22px rgba(74,46,53,.07));padding:14px 18px;display:flex;align-items:center;gap:11px;">
            <span style="width:34px;height:34px;flex-shrink:0;border-radius:50%;background:linear-gradient(150deg,#FBE7E6,#F4D4CE);"></span>
            <div><div style="font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#C98A6D);font-weight:700;">{{group_3_text}}</div><div class="sf-serif" style="font-size:16px;color:var(--text-main,#4A2E35);">{{group_3_text_2}}</div></div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_1_button_url_2": {"type": "text", "label": "Group 1 Button Url 2"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_1_button_text_2": {"type": "textarea", "label": "Group 1 Button Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Playfair+Display:wght@500;600&display=swap", "group_1_button_url": "#koleksiyon", "group_1_button_url_2": "https://wa.me/905426205100", "group_1_subtitle": "Güzelliğin İnce Dokunuşu", "group_1_title": "Cildinize özel", "group_1_text": "bütüncül bakım", "group_2_body_html": "Seffiline; cilt, saç, intim bakım ve dolgu çözümlerinde feminen ve profesyonel bir seri. Kadın sağlığı ve güzelliğine, zarafetle ve bilimle yaklaşır.", "group_1_button_text": "Koleksiyonu Keşfet", "group_1_button_text_2": "WhatsApp Danışma", "group_2_text": "Seffiller", "group_2_text_2": "Dolgu Serisi", "group_3_text": "SeffiCare", "group_3_text_2": "Cilt Bakımı"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-seffiline-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'2. MARKA FELSEFESİ',
             'html_template'=><<<'EDHTML'
<!-- 2. MARKA FELSEFESİ -->

    <section style="padding:96px 0;background:#FFFCFB;">
      <div class="container" style="max-width:780px;text-align:center;">
        <p style="color:var(--color-accent,#E8A0A8);font-weight:700;letter-spacing:3px;text-transform:uppercase;font-size:12px;margin:0 0 22px;">{{subtitle}}</p>
        <p class="sf-serif" style="font-size:clamp(24px,3.4vw,34px);line-height:1.55;font-weight:400;color:var(--text-main,#4A2E35);margin:0 0 32px;font-style:italic;">{{{body_html}}}</p>
        <!-- ince zarif ayraç -->
        <div style="display:flex;align-items:center;justify-content:center;gap:14px;margin:0 auto;max-width:240px;">
          <span style="flex:1;height:1px;background:linear-gradient(90deg,transparent,var(--border-soft,#EBD9D6));"></span>
          <span style="color:var(--color-primary,#C98A6D);font-size:18px;">{{group_2_text}}</span>
          <span style="flex:1;height:1px;background:linear-gradient(90deg,var(--border-soft,#EBD9D6),transparent);"></span>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"subtitle": {"type": "textarea", "label": "Subtitle"}, "body_html": {"type": "textarea", "label": "Body"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"subtitle": "Marka Felsefesi", "body_html": "\"Her kadının güzelliği biriciktir. Seffiline, kadın sağlığı ve güzelliğine bütüncül bir yaklaşımla; cildi, saçı ve hassas bölgeleri aynı özen ve zarafetle ele alır.\"", "group_2_text": "❀"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-seffiline-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'3. 4 ÜRÜN AİLESİ',
             'html_template'=><<<'EDHTML'
<!-- 3. 4 ÜRÜN AİLESİ -->

    <section id="koleksiyon" style="padding:32px 0 100px;background:#FFFCFB;">
      <div class="container">
        <div style="text-align:center;max-width:620px;margin:0 auto 56px;">
          <p style="color:var(--color-primary,#C98A6D);font-weight:700;letter-spacing:3px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">{{group_1_subtitle}}</p>
          <h2 class="sf-serif" style="font-size:clamp(30px,4.4vw,46px);font-weight:500;color:var(--text-main,#4A2E35);margin:0 0 16px;line-height:1.12;">{{group_1_title}}</h2>
          <p style="color:var(--text-soft,#8A6A70);font-size:17px;line-height:1.8;">{{group_2_description}}</p>
        </div>

        <!-- 🖼️ GÖRSEL (tekrarlayan ürün ailesi kartı görsel deseni): /assets/img/seffiline-product-{slug}.jpg (oran 1:1)
             Örn: seffiline-product-seffícare.jpg → ASCII güvenli: seffiline-product-sefficare.jpg, seffiline-product-seffigyn.jpg, seffiline-product-seffihair.jpg, seffiline-product-seffiller.jpg
             PROMPT: "Elegant feminine cosmetic product packshot (skincare bottle / intimate care / hair mesotherapy / dermal filler), soft blush-pink and cream backdrop with rose-gold accents, luxury editorial beauty lighting, gentle diffused glow, refined and delicate styling, single fresh petal detail, premium magazine aesthetic; photorealistic, detailed, high resolution; no text, no logo, no watermark"
             KULLANIM → Her ürün kartının başına (yuvarlak ikon dairesinin üstüne) bir görsel alanı eklenebilir, örn:
             <img src="/assets/img/seffiline-product-sefficare.jpg" alt="Seffiline SeffiCare cilt bakımı ürün çekimi" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:22px;margin-bottom:20px;"> -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:26px;">
          <!-- SeffiCare -->
          {{{sf_card_items_html}}}<!-- SeffiGyn -->
          <!-- SeffiHair -->
          <!-- Seffiller -->
          </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"sf_card_items": {"type": "repeater", "label": "Sf Card Items", "repeat_kind": "items", "item_template": "<a href=\"{{button_url}}\" class=\"sf-card\" style=\"display:block;background:#fff;border:1px solid var(--border-soft,#EBD9D6);border-radius:28px;padding:36px 30px;transition:transform .25s,box-shadow .25s;\">\n            <div style=\"width:66px;height:66px;border-radius:50%;background:linear-gradient(150deg,#FBE7E6,#F4D4CE);margin-bottom:24px;\"></div>\n            <h3 class=\"sf-serif\" style=\"font-size:25px;font-weight:600;color:var(--text-main,#4A2E35);margin:0 0 4px;\">{{title}}</h3>\n            <p style=\"font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--color-primary,#C98A6D);font-weight:700;margin:0 0 14px;\">{{subtitle}}</p>\n            <p style=\"color:var(--text-soft,#8A6A70);font-size:14.5px;line-height:1.75;margin:0 0 22px;\">{{description}}</p>\n            <span style=\"color:var(--color-primary,#C98A6D);font-weight:600;font-size:14px;letter-spacing:.3px;\">{{text}}</span>\n          </a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "title": {"type": "text", "label": "Title"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "description": {"type": "textarea", "label": "Description"}, "text": {"type": "textarea", "label": "Text"}}}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"sf_card_items": [{"button_url": "/urun-detay", "title": "SeffiCare", "subtitle": "Cilt Bakımı", "description": "Cildi besleyen, nemlendiren ve canlandıran zarif cilt bakım serisi.", "text": "İncele →"}, {"button_url": "/urun-detay", "title": "SeffiGyn", "subtitle": "İntim Bakım", "description": "Hassas bölgelerin sağlığı için pH dengeli, nazik ve güvenilir intim bakım.", "text": "İncele →"}, {"button_url": "/urun-detay", "title": "SeffiHair", "subtitle": "Saç Bakımı & Mezoterapi", "subtitle_2": "Saç kökünü güçlendiren mezoterapi ve yoğun bakım çözümleri.", "text": "İncele →"}, {"button_url": "/urun-detay", "title": "Seffiller", "subtitle": "Dolgu Serisi", "description": "Hyalüronik asit bazlı, doğal ve zarif sonuçlar veren dolgu çözümleri.", "text": "İncele →"}], "group_1_subtitle": "Koleksiyon", "group_1_title": "Dört ince ürün ailesi", "group_2_description": "Baştan ayağa bütüncül bir bakım ritüeli; her ihtiyaca uygun, zarif ve profesyonel."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-seffiline-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'4. ÖNE ÇIKAN: SeffiHair (editoryal split)',
             'html_template'=><<<'EDHTML'
<!-- 4. ÖNE ÇIKAN: SeffiHair (editoryal split) -->

    <section style="padding:100px 0;background:var(--color-secondary,#FBF0EF);position:relative;overflow:hidden;">
      <div aria-hidden="true" style="position:absolute;top:-120px;right:8%;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,160,168,.22),transparent 70%);"></div>
      <div class="container" style="position:relative;display:flex;flex-wrap:wrap;gap:64px;align-items:center;">
        <!-- 🖼️ GÖRSEL: /assets/img/seffiline-seffihair.jpg (oran 3:4)
             PROMPT: "Editorial feminine hair care beauty shot, a woman's healthy glossy flowing hair with a SeffiHair mesotherapy serum vial, soft rose-gold and blush tones, luxury salon aesthetic, warm diffused light, silky elegant mood, glowing voluminous hair, high-fashion hair editorial; photorealistic, detailed, high resolution; no text, no logo, no watermark"
             DEĞİŞTİR → bu görsel kutusundaki 💇‍♀️ emojili .aspect-ratio:3/4 iç placeholder bloğunu şununla değiştir:
             <img src="/assets/img/seffiline-seffihair.jpg" alt="Seffiline SeffiHair saç bakımı ve mezoterapi editoryal çekimi" style="width:100%;height:100%;object-fit:cover;border-radius:18px 18px 180px 180px;"> -->
        <!-- görsel -->
        <div style="flex:1 1 340px;position:relative;min-height:420px;">
          <div style="position:relative;background:#fff;border:1px solid var(--border-soft,#EBD9D6);border-radius:24px 24px 200px 200px;box-shadow:var(--shadow-petal,0 18px 50px rgba(201,138,109,.14));padding:28px;max-width:400px;margin:0 auto;">
            <div style="aspect-ratio:3/4;border-radius:18px 18px 180px 180px;background:linear-gradient(180deg,#F6D8D4,#EFC9C2 70%,#E6B8B0) url('/assets/img/seffiline-seffihair.jpg') center/cover no-repeat;"></div>
          </div>
        </div>
        <!-- metin -->
        <div style="flex:1 1 420px;">
          <p style="display:inline-flex;align-items:center;gap:9px;color:var(--color-primary,#C98A6D);font-weight:700;letter-spacing:3px;text-transform:uppercase;font-size:12px;margin:0 0 22px;"><span style="width:26px;height:1px;background:var(--color-primary,#C98A6D);"></span>{{group_1_subtitle}}</p>
          <h2 class="sf-serif" style="font-size:clamp(30px,4.4vw,46px);font-weight:500;color:var(--text-main,#4A2E35);margin:0 0 22px;line-height:1.12;">{{group_2_title}}<br><em style="font-style:italic;color:var(--color-primary,#C98A6D);">{{group_2_text}}</em></h2>
          <p style="color:var(--text-soft,#8A6A70);font-size:17px;line-height:1.85;margin:0 0 28px;max-width:520px;">{{{group_2_body_html}}}</p>
          <ul style="list-style:none;margin:0 0 32px;padding:0;display:grid;gap:14px;">
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#4A2E35);font-weight:500;"><span style="color:var(--color-accent,#E8A0A8);font-size:16px;">{{group_1_text}}</span> {{group_1_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#4A2E35);font-weight:500;"><span style="color:var(--color-accent,#E8A0A8);font-size:16px;">{{group_2_text_2}}</span> {{group_2_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#4A2E35);font-weight:500;"><span style="color:var(--color-accent,#E8A0A8);font-size:16px;">{{group_3_text}}</span> {{group_3_item_text}}</li>
          </ul>
          <a href="{{group_2_button_url}}" class="sf-btn-fill" style="display:inline-flex;align-items:center;gap:9px;background:var(--grad-rose,linear-gradient(135deg,#C98A6D,#E8A0A8));color:#fff;padding:15px 32px;border-radius:999px;font-weight:600;font-size:15px;letter-spacing:.3px;box-shadow:0 12px 30px rgba(201,138,109,.30);transition:transform .2s,box-shadow .2s;">{{group_2_button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": "/urun-detay", "group_1_subtitle": "Öne Çıkan", "group_2_title": "SeffiHair ile", "group_2_text": "kökten güçlü saçlar", "group_2_body_html": "Saç dökülmesiyle mücadelede mezoterapi temelli bir yaklaşım. SeffiHair serisi, saç köküne ihtiyaç duyduğu vitamin ve mineralleri ileterek folikülleri besler; daha sağlıklı, dolgun ve canlı bir görünüm için zarif bir bakım ritüeli sunar.", "group_1_text": "❀", "group_1_item_text": "Saç köküne yoğun besin desteği", "group_2_text_2": "❀", "group_2_item_text": "Mezoterapi ile uyumlu profesyonel formül", "group_3_text": "❀", "group_3_item_text": "Dolgun ve canlı bir saç görünümü", "group_2_button_text": "SeffiHair'i İncele →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-seffiline-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'5. GÜVEN / KALİTE NOTU',
             'html_template'=><<<'EDHTML'
<!-- 5. GÜVEN / KALİTE NOTU -->

    <section style="padding:96px 0;background:#FFFCFB;">
      <div class="container" style="max-width:760px;text-align:center;">
        <div style="display:inline-block;width:70px;height:70px;border-radius:50%;background:linear-gradient(150deg,#FBE7E6,#F4D4CE);margin:0 0 26px;"></div>
        <h2 class="sf-serif" style="font-size:clamp(26px,3.8vw,38px);font-weight:500;color:var(--text-main,#4A2E35);margin:0 0 18px;line-height:1.2;">{{title}}<br><em style="font-style:italic;color:var(--color-primary,#C98A6D);">{{text}}</em></h2>
        <p style="color:var(--text-soft,#8A6A70);font-size:17px;line-height:1.85;margin:0;">{{{body_html}}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"title": {"type": "text", "label": "Title"}, "text": {"type": "textarea", "label": "Text"}, "body_html": {"type": "textarea", "label": "Body"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"title": "Estetik Dermal güvencesiyle,", "text": "%100 orijinal", "body_html": "Seffiline ürünleri, resmi distribütör Estetik Dermal güvencesiyle sunulur. Her ürün sertifikalı, takip edilebilir ve tamamen orijinaldir; güzelliğiniz emin ellerde."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-seffiline-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'6. CTA BANDI',
             'html_template'=><<<'EDHTML'
<!-- 6. CTA BANDI -->

    <section style="padding:20px 0 96px;background:#FFFCFB;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:36px;background:var(--grad-rose,linear-gradient(135deg,#C98A6D,#E8A0A8));padding:clamp(48px,7vw,84px);text-align:center;color:#fff;">
          <div aria-hidden="true" style="position:absolute;top:-50px;right:-40px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.14);"></div>
          <div aria-hidden="true" style="position:absolute;bottom:-70px;left:-40px;width:260px;height:260px;border-radius:50%;background:rgba(255,255,255,.10);"></div>
          <div style="position:relative;">
            <p style="letter-spacing:3px;text-transform:uppercase;font-size:12px;font-weight:700;opacity:.9;margin:0 0 18px;">{{group_1_subtitle}}</p>
            <h2 class="sf-serif" style="font-size:clamp(28px,4.4vw,46px);font-weight:500;margin:0 0 18px;line-height:1.14;">{{group_3_title}}</h2>
            <p style="font-size:18px;opacity:.95;max-width:560px;margin:0 auto 36px;line-height:1.7;">{{group_2_description}}</p>
            <a href="{{group_3_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:16px 38px;border-radius:999px;font-weight:700;font-size:15px;letter-spacing:.3px;box-shadow:0 14px 36px rgba(74,46,53,.18);"><svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_3_button_text}}</a>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_3_button_text": {"type": "textarea", "label": "Group 3 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_3_button_url": "https://wa.me/905426205100", "group_1_subtitle": "İletişim", "group_3_title": "Seffiline ürünleri için bize ulaşın", "group_2_description": "Ürün, fiyat ve uygulama bilgileri için ekibimiz hazır. WhatsApp'tan zarifçe yazın, hemen yanıtlayalım.", "group_3_button_text": "WhatsApp ile Yaz"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-aespio-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><style>
    :root {
      --color-primary: #6C5CE0;
      --color-accent: #21D4B4;
      --color-secondary: #F0ECFB;
      --text-main: #221A40;
      --text-soft: #6B6088;
      --aespio-deep: #4A3BC0;
      --aespio-mint-soft: #DFF8F2;
      --aespio-purple-soft: #ECE6FB;
      --grad-aespio: linear-gradient(135deg, #6C5CE0 0%, #21D4B4 100%);
      --grad-aespio-soft: linear-gradient(135deg, #ECE6FB 0%, #DFF8F2 100%);
      --font-display: "Poppins", "Segoe UI", system-ui, sans-serif;
    }
    body { font-family: var(--font-display); color: var(--text-main, #221A40); }
    h1, h2, h3, h4 { font-family: var(--font-display); }
    .aespio-card:hover { transform: translateY(-6px); box-shadow: 0 22px 50px rgba(108, 92, 224, .22); }
    .aespio-card { transition: transform .22s ease, box-shadow .22s ease; }
    .aespio-pill:hover { transform: translateY(-2px); }
    .aespio-pill { transition: transform .18s ease; }
  </style><!-- HERO -->

    <section style="position:relative;overflow:hidden;background:linear-gradient(150deg,#6C5CE0 0%, #5A4BD4 40%, #2FC4C0 100%);">
      <!-- geometrik blob şekiller -->
      <div style="position:absolute;top:-120px;right:-80px;width:420px;height:420px;border-radius:48% 52% 60% 40%/55% 45% 55% 45%;background:radial-gradient(circle at 30% 30%, rgba(33,212,180,.55), transparent 70%);filter:blur(8px);"></div>
      <div style="position:absolute;bottom:-160px;left:-100px;width:480px;height:480px;border-radius:60% 40% 50% 50%/40% 60% 40% 60%;background:radial-gradient(circle at 60% 40%, rgba(255,255,255,.18), transparent 65%);"></div>
      <div style="position:absolute;top:30%;left:42%;width:140px;height:140px;border-radius:50%;border:2px dashed rgba(255,255,255,.25);"></div>
      <div class="container" style="position:relative;display:flex;flex-wrap:wrap;gap:48px;align-items:center;padding:92px 0 100px;">
        <div style="flex:1 1 480px;">
          <p style="display:inline-flex;align-items:center;gap:9px;background:rgba(255,255,255,.16);border:1.5px solid rgba(255,255,255,.32);color:#fff;border-radius:999px;padding:9px 18px;font-size:12.5px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;margin:0 0 26px;backdrop-filter:blur(6px);">{{group_1_subtitle}}</p>
          <h1 style="font-size:clamp(38px,6vw,68px);line-height:1.02;font-weight:900;color:#fff;margin:0 0 22px;letter-spacing:-1px;">{{group_1_title}}<br>{{group_1_title_2}}</h1>
          <p style="font-size:clamp(16px,2vw,21px);color:rgba(255,255,255,.92);line-height:1.65;max-width:560px;margin:0 0 36px;font-weight:500;">{{{group_2_body_html}}}</p>
          <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a href="{{group_1_button_url}}" style="display:inline-flex;align-items:center;gap:9px;background:#fff;color:var(--aespio-deep,#4A3BC0);padding:16px 34px;border-radius:999px;font-weight:800;font-size:16px;box-shadow:0 16px 40px rgba(33,212,180,.35);">{{group_1_button_text}}</a>
            <a href="{{group_2_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;border:1.5px solid rgba(255,255,255,.5);padding:16px 30px;border-radius:999px;font-weight:800;font-size:16px;box-shadow:0 10px 30px rgba(37,211,102,.35);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:20px;height:20px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_2_button_text}}</a>
          </div>
        </div>
        <!-- 🖼️ GÖRSEL: /assets/img/aespio-hero.jpg (oran 4:3)
             PROMPT: "Modern K-beauty hero composition, a sleek sheet face mask sachet and thread-lift product on a clean bright glossy studio surface, vibrant purple and mint color palette, dynamic youthful energy, high-key glossy lighting, fresh and futuristic Korean skincare aesthetic, soft holographic reflections, crisp and vivid; photorealistic, detailed, high resolution; no text, no logo, no watermark"
             DEĞİŞTİR → bu kompozisyondaki 🎭 emojili .aspect-ratio:4/3 iç placeholder bloğunu şununla değiştir:
             <img src="/assets/img/aespio-hero.jpg" alt="Grand Aespio yüz maskesi ve ip askı ürünü modern K-beauty studio çekimi" style="width:100%;height:100%;object-fit:cover;border-radius:20px;"> -->
        <!-- canlı görsel kompozisyon -->
        <div style="position:relative;min-height:400px;flex:1 1 320px;">
          <div style="position:absolute;inset:8px;background:linear-gradient(135deg, rgba(255,255,255,.22), rgba(33,212,180,.32));border-radius:42% 58% 60% 40%/45% 45% 55% 55%;"></div>
          <div style="position:relative;background:rgba(255,255,255,.95);border-radius:28px;box-shadow:0 30px 70px rgba(74,59,192,.4);padding:26px;backdrop-filter:blur(4px);">
            <div style="aspect-ratio:4/3;border-radius:20px;background:var(--grad-aespio-soft,linear-gradient(135deg,#ECE6FB,#DFF8F2)) url('/assets/img/aespio-hero.jpg') center/cover no-repeat;"></div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:18px;">
              <div>
                <div style="font-size:11px;color:var(--color-primary,#6C5CE0);font-weight:800;text-transform:uppercase;letter-spacing:1.2px;">{{group_1_text}}</div>
                <div style="font-weight:900;color:var(--text-main,#221A40);font-size:17px;">{{group_2_text}}</div>
              </div>
              <span style="background:var(--aespio-mint-soft,#DFF8F2);color:#0E9E86;font-size:11.5px;font-weight:800;padding:7px 14px;border-radius:999px;">{{group_2_text_2}}</span>
            </div>
          </div>
          <div style="position:absolute;bottom:-20px;left:-18px;background:#fff;border-radius:18px;box-shadow:0 12px 34px rgba(74,59,192,.22);padding:14px 18px;display:flex;align-items:center;gap:11px;">
            <span style="width:34px;height:34px;border-radius:11px;background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));flex-shrink:0;"></span>
            <div>
              <div style="font-size:11px;color:var(--text-soft,#6B6088);font-weight:600;">{{group_1_text_2}}</div>
              <div style="font-weight:900;font-size:14px;color:var(--text-main,#221A40);">{{group_2_text_3}}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_title_2": {"type": "text", "label": "Group 1 Title 2"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800;900&display=swap", "group_1_button_url": "#urunler", "group_2_button_url": "https://wa.me/905426205100", "group_1_subtitle": "✦ K-Beauty Teknolojisi", "group_1_title": "Cildin için", "group_1_title_2": "yeni nesil bakım", "group_2_body_html": "Grand Aespio, yüz maskeleri ve ip askı (thread lift) ürünlerinde modern Kore yaklaşımını sahaya taşıyor. Yeni nesil formüller, cesur sonuçlar.", "group_1_button_text": "Ürünleri Gör →", "group_2_button_text": "WhatsApp Danışma", "group_1_text": "Grand Aespio", "group_2_text": "Beta-Glukan Mask", "group_2_text_2": "K-BEAUTY", "group_1_text_2": "Thread Lift", "group_2_text_3": "LFL Anchor"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-aespio-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'STAT / ÖZELLİK ŞERİDİ',
             'html_template'=><<<'EDHTML'
<!-- STAT / ÖZELLİK ŞERİDİ -->

    <section style="background:#fff;">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:18px;padding:40px 0;justify-content:center;">
        {{{aespio_pill_items_html}}}</div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"aespio_pill_items": {"type": "repeater", "label": "Aespio Pill Items", "repeat_kind": "items", "item_template": "<div class=\"aespio-pill\" style=\"flex:1 1 200px;display:flex;align-items:center;gap:14px;background:var(--grad-aespio-soft,linear-gradient(135deg,#ECE6FB,#DFF8F2));border-radius:20px;padding:20px 22px;\">\n          <span style=\"width:48px;height:48px;border-radius:14px;background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));flex-shrink:0;\"></span>\n          <div><div style=\"font-weight:900;font-size:16px;color:var(--text-main,#221A40);\">{{text}}</div><div style=\"font-size:13px;color:var(--text-soft,#6B6088);font-weight:600;\">{{text_2}}</div></div>\n        </div>", "fields": {"text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}}}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"aespio_pill_items": [{"text": "Yeni Nesil Formül", "text_2": "İleri Kore bilimi"}, {"text": "K-Beauty", "text_2": "Modern Kore yaklaşımı"}, {"text": "Maske + Thread", "text_2": "Tek portföyde iki güç"}]}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-aespio-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'ÜRÜN SHOWCASE',
             'html_template'=><<<'EDHTML'
<!-- ÜRÜN SHOWCASE -->

    <section id="urunler" style="padding:84px 0;background:var(--color-secondary,#F0ECFB);">
      <div class="container">
        <div style="text-align:center;max-width:640px;margin:0 auto 52px;">
          <p style="color:var(--color-primary,#6C5CE0);font-weight:800;letter-spacing:1.6px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(30px,4.5vw,46px);font-weight:900;color:var(--text-main,#221A40);margin:0 0 14px;line-height:1.08;letter-spacing:-.5px;">{{group_1_title}}</h2>
          <p style="color:var(--text-soft,#6B6088);font-size:17px;line-height:1.7;font-weight:500;">{{group_2_description}}</p>
        </div>
        <!-- 🖼️ GÖRSEL (tekrarlayan ürün showcase kartı görsel deseni): /assets/img/aespio-product-{slug}.jpg (oran 4:3)
             Örn: aespio-product-beta-glukan-mask.jpg, aespio-product-hyaluronic-acid-mask.jpg, aespio-product-feelsoft.jpg, aespio-product-fmc.jpg, aespio-product-lfl-anchor.jpg
             PROMPT: "Modern K-beauty product showcase shot (sheet face mask sachet or PDO thread-lift product), vibrant purple-to-mint gradient backdrop, bright glossy studio lighting, youthful dynamic Korean beauty aesthetic, clean crisp composition, holographic mint and violet accents, fresh and energetic; photorealistic, detailed, high resolution; no text, no logo, no watermark"
             KULLANIM → Her kartın başındaki aspect-ratio:4/3 gradyan+emoji görsel bloğunu ilgili görselle değiştir, örn:
             <img src="/assets/img/aespio-product-beta-glukan-mask.jpg" alt="Grand Aespio Beta-Glukan Mask ürün çekimi" style="width:100%;aspect-ratio:4/3;object-fit:cover;"> -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px;">

          {{{aespio_card_items_html}}}</div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"aespio_card_items": {"type": "repeater", "label": "Aespio Card Items", "repeat_kind": "items", "item_template": "<a href=\"{{button_url}}\" class=\"aespio-card\" style=\"display:block;background:#fff;border-radius:24px;overflow:hidden;\">\n            <div style=\"aspect-ratio:4/3;background:linear-gradient(135deg,#C6BBF7,#8E7DF0) url('/assets/img/aespio-product-beta-glukan-mask.jpg') center/cover no-repeat;\"></div>\n            <div style=\"padding:24px;\">\n              <span style=\"font-size:11.5px;font-weight:800;color:var(--color-primary,#6C5CE0);text-transform:uppercase;letter-spacing:.8px;\">{{text}}</span>\n              <h3 style=\"font-size:19px;font-weight:900;color:var(--text-main,#221A40);margin:7px 0 9px;\">{{title}}</h3>\n              <p style=\"color:var(--text-soft,#6B6088);font-size:14px;line-height:1.6;margin:0 0 14px;font-weight:500;\">{{description}}</p>\n              <span style=\"color:var(--color-accent,#21D4B4);font-weight:800;font-size:14px;\">{{text_2}}</span>\n            </div>\n          </a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}, "text_2": {"type": "textarea", "label": "Text 2"}}}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"aespio_card_items": [{"button_url": "/urun-detay", "text": "Yatıştırıcı Maske", "title": "Beta-Glukan Mask", "description": "Beta-glukan ile hassas cildi yatıştıran, onarıcı yeni nesil yüz maskesi.", "text_2": "İncele →"}, {"button_url": "/urun-detay", "text": "Nemlendirici Maske", "title": "Hyaluronic Acid Mask", "description": "Hyalüronik asit ile yoğun nem desteği; dolgun, ışıltılı bir cilt hissi.", "text_2": "İncele →"}, {"button_url": "/urun-detay", "text": "İp Askı", "title": "FeelSoft", "description": "Yumuşak doku desteği için tasarlanmış konforlu ip askı çözümü.", "text_2": "İncele →"}, {"button_url": "/urun-detay", "text": "İp Askı", "title": "FMC", "description": "Hassas uygulamalar için ince işçilikli, çok yönlü ip askı ürünü.", "text_2": "İncele →"}, {"button_url": "/urun-detay", "text": "Thread Lift", "title": "LFL Anchor", "description": "Güçlü tutuş için çapalı (anchor) tasarımlı ip askı / thread lift sistemi.", "text_2": "İncele →"}], "group_1_subtitle": "Ürün Serisi", "group_1_title": "Maskeden ip askıya, eksiksiz bir seri", "group_2_description": "Yatıştırıcı maskeler, nemlendirici bakım ve thread lift çözümleri — hepsi tek bir cesur marka altında."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-aespio-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'ÖNE ÇIKAN SPLIT',
             'html_template'=><<<'EDHTML'
<!-- ÖNE ÇIKAN SPLIT -->

    <section style="padding:90px 0;background:#fff;">
      <!-- 🖼️ GÖRSEL: /assets/img/aespio-lfl-anchor.jpg (oran 1:1)
           PROMPT: "Modern K-beauty thread-lift product hero shot, an anchor-design PDO lifting thread with cannula, vibrant purple and mint gradient backdrop, glossy bright clinical-studio lighting, dynamic youthful aesthetic, sleek futuristic Korean medical aesthetics styling, crisp precise detail, holographic violet-mint highlights; photorealistic, detailed, high resolution; no text, no logo, no watermark"
           DEĞİŞTİR → bu görsel kutusundaki 🧵 emojili iç placeholder bloğunu şununla değiştir:
           <img src="/assets/img/aespio-lfl-anchor.jpg" alt="Grand Aespio LFL Anchor çapalı ip askı thread lift ürün çekimi" style="width:100%;height:100%;object-fit:cover;border-radius:28px;"> -->
      <div class="container" style="display:flex;flex-wrap:wrap;gap:56px;align-items:center;">
        <div style="position:relative;min-height:380px;flex:1 1 340px;">
          <div style="position:absolute;inset:0;background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));border-radius:46% 54% 56% 44%/52% 48% 52% 48%;opacity:.16;"></div>
          <div style="position:relative;height:100%;min-height:380px;border-radius:28px;background:linear-gradient(150deg,#6C5CE0 0%, #2FC4C0 100%) url('/assets/img/aespio-lfl-anchor.jpg') center/cover no-repeat;display:grid;place-items:center;overflow:hidden;">
            <div style="position:absolute;top:-50px;right:-40px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.14);"></div>
            <div style="position:absolute;bottom:-60px;left:-30px;width:230px;height:230px;border-radius:50%;background:rgba(255,255,255,.1);"></div>
          </div>
        </div>
        <div style="flex:1 1 420px;">
          <p style="color:var(--color-primary,#6C5CE0);font-weight:800;letter-spacing:1.6px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 style="font-size:clamp(28px,4vw,42px);font-weight:900;color:var(--text-main,#221A40);margin:0 0 18px;line-height:1.1;letter-spacing:-.5px;">{{group_2_title}} <span style="background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));-webkit-background-clip:text;background-clip:text;color:transparent;">{{group_2_text}}</span> {{group_2_title_2}}</h2>
          <p style="color:var(--text-soft,#6B6088);font-size:17px;line-height:1.75;margin:0 0 24px;font-weight:500;">{{{group_2_body_html}}}</p>
          <ul style="list-style:none;margin:0 0 30px;padding:0;display:grid;gap:13px;">
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#221A40);font-weight:700;"><span style="width:26px;height:26px;border-radius:8px;background:var(--aespio-mint-soft,#DFF8F2);color:#0E9E86;display:grid;place-items:center;font-size:14px;flex-shrink:0;">{{group_1_text}}</span> {{group_1_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#221A40);font-weight:700;"><span style="width:26px;height:26px;border-radius:8px;background:var(--aespio-purple-soft,#ECE6FB);color:var(--color-primary,#6C5CE0);display:grid;place-items:center;font-size:14px;flex-shrink:0;">{{group_2_text_2}}</span> {{group_2_item_text}}</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#221A40);font-weight:700;"><span style="width:26px;height:26px;border-radius:8px;background:var(--aespio-mint-soft,#DFF8F2);color:#0E9E86;display:grid;place-items:center;font-size:14px;flex-shrink:0;">{{group_3_text}}</span> {{group_3_item_text}}</li>
          </ul>
          <a href="{{group_2_button_url}}" style="display:inline-flex;align-items:center;gap:9px;background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));color:#fff;padding:15px 32px;border-radius:999px;font-weight:800;font-size:15px;box-shadow:0 14px 36px rgba(108,92,224,.32);">{{group_2_button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_title_2": {"type": "text", "label": "Group 2 Title 2"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": "/urun-detay", "group_1_subtitle": "Öne Çıkan · LFL Anchor", "group_2_title": "Thread lift'te", "group_2_text": "çapalı tutuş", "group_2_title_2": "gücü", "group_2_body_html": "LFL Anchor, çapa (anchor) tasarımıyla dokuda güçlü ve dengeli bir tutuş sağlayacak şekilde geliştirildi. Grand Aespio'nun ip askı serisi — FeelSoft, FMC ve LFL Anchor — farklı endikasyonlar için modern, çok yönlü bir araç seti sunar.", "group_1_text": "✓", "group_1_item_text": "Çapalı (anchor) tasarımla güçlü tutuş", "group_2_text_2": "✓", "group_2_item_text": "Maske + thread tamamlayıcı protokoller", "group_3_text": "✓", "group_3_item_text": "Yeni nesil K-beauty üretim kalitesi", "group_2_button_text": "LFL Anchor'ı İncele →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-aespio-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'GÜVEN NOTU',
             'html_template'=><<<'EDHTML'
<!-- GÜVEN NOTU -->

    <section style="padding:0 0 84px;background:#fff;">
      <div class="container">
        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;justify-content:center;background:var(--grad-aespio-soft,linear-gradient(135deg,#ECE6FB,#DFF8F2));border-radius:24px;padding:32px 36px;text-align:center;">
          <span style="width:44px;height:44px;border-radius:13px;background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));flex-shrink:0;"></span>
          <p style="margin:0;font-size:clamp(16px,2.4vw,21px);font-weight:700;color:var(--text-main,#221A40);line-height:1.5;">{{subtitle}} <strong style="color:var(--color-primary,#6C5CE0);">{{text}}</strong> {{subtitle_2}}</p>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"subtitle": {"type": "textarea", "label": "Subtitle"}, "text": {"type": "textarea", "label": "Text"}, "subtitle_2": {"type": "textarea", "label": "Subtitle 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"subtitle": "Grand Aespio,", "text": "Estetik Dermal", "subtitle_2": "resmi distribütörlüğüyle Türkiye'de."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-aespio-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CTA BANDI',
             'html_template'=><<<'EDHTML'
<!-- CTA BANDI -->

    <section style="padding:0 0 90px;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:32px;background:linear-gradient(120deg,#6C5CE0 0%, #5A4BD4 45%, #21D4B4 110%);padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-50px;right:-40px;width:220px;height:220px;border-radius:48% 52% 60% 40%/55% 45% 55% 45%;background:rgba(255,255,255,.12);"></div>
          <div style="position:absolute;bottom:-70px;left:-40px;width:260px;height:260px;border-radius:60% 40% 50% 50%/40% 60% 40% 60%;background:rgba(255,255,255,.08);"></div>
          <div style="position:relative;">
            <p style="display:inline-flex;background:rgba(255,255,255,.16);border:1.5px solid rgba(255,255,255,.32);color:#fff;border-radius:999px;padding:8px 18px;font-size:12px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;margin:0 0 20px;">{{group_1_subtitle}}</p>
            <h2 style="font-size:clamp(28px,4.5vw,46px);font-weight:900;margin:0 0 16px;line-height:1.1;letter-spacing:-.5px;">{{group_3_title}}<br>{{group_3_title_2}}</h2>
            <p style="font-size:18px;opacity:.95;max-width:600px;margin:0 auto 34px;line-height:1.6;font-weight:500;">{{group_2_description}}</p>
            <a href="{{group_3_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:17px 38px;border-radius:999px;font-weight:900;font-size:16px;box-shadow:0 16px 40px rgba(37,211,102,.4);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:20px;height:20px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_3_button_text}}</a>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_title_2": {"type": "text", "label": "Group 3 Title 2"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_3_button_text": {"type": "textarea", "label": "Group 3 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_3_button_url": "https://wa.me/905426205100", "group_1_subtitle": "✦ Grand Aespio", "group_3_title": "Grand Aespio ürünleri için", "group_3_title_2": "bize ulaşın", "group_2_description": "Maske ve ip askı serisi hakkında ürün bilgisi, fiyat ve eğitim talepleriniz için ekibimiz hazır.", "group_3_button_text": "WhatsApp ile Yaz"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-woorhi-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'1 · BESPOKE KOYU HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link rel="stylesheet" href="{{group_3_link_url}}"><style>
    :root {
      --color-primary: #2DA8FF;   /* elektrik mavi */
      --color-accent:  #16E0C8;   /* cyan */
      --color-secondary: #10131A; /* koyu zemin */
      --radius-card: 18px;
      --radius-button: 999px;
    }
    body { background:#0B0E14; color:#E6EDF5; }
    .wh-display { font-family:"Space Grotesk","Rajdhani","Segoe UI",system-ui,sans-serif; letter-spacing:-.01em; }
    .wh-mono { font-family:"JetBrains Mono",ui-monospace,SFMono-Regular,Menlo,monospace; }
    ::selection { background:#2DA8FF; color:#0B0E14; }
    a.wh-link:focus-visible { outline:3px solid #16E0C8; outline-offset:3px; }
  </style><!-- 1 · BESPOKE KOYU HERO -->

    <section style="position:relative;overflow:hidden;background:#0B0E14;background-image:radial-gradient(900px 480px at 82% -8%, rgba(45,168,255,.30), transparent 60%),radial-gradient(700px 520px at 8% 110%, rgba(22,224,200,.20), transparent 60%),linear-gradient(180deg,#0B0E14 0%, #10131A 100%);">
      <!-- teknik grid çizgileri -->
      <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;mask-image:radial-gradient(1000px 600px at 70% 0%,#000,transparent 80%);"></div>
      <div class="container" style="position:relative;display:flex;flex-wrap:wrap;gap:52px;align-items:center;padding:92px 0 100px;">
        <div style="flex:1 1 480px;">
          <p class="wh-mono" style="display:inline-flex;align-items:center;gap:9px;background:rgba(45,168,255,.10);border:1px solid rgba(45,168,255,.35);color:#9AD6FF;border-radius:999px;padding:8px 16px;font-size:12px;letter-spacing:1.2px;text-transform:uppercase;margin:0 0 26px;"><span aria-hidden="true" style="width:7px;height:7px;border-radius:50%;background:#16E0C8;box-shadow:0 0 8px #16E0C8;"></span>{{group_1_subtitle}}</p>
          <h1 class="wh-display" style="font-size:clamp(36px,5.4vw,60px);line-height:1.05;font-weight:700;color:#F4F8FF;margin:0 0 22px;">{{group_1_title}}<br><span style="background:linear-gradient(110deg,#2DA8FF 0%,#16E0C8 100%);-webkit-background-clip:text;background-clip:text;color:transparent;">{{group_1_text}}</span></h1>
          <p style="font-size:clamp(16px,2vw,19px);color:#AEBCCC;line-height:1.75;max-width:560px;margin:0 0 34px;">{{{group_1_body_html}}}</p>
          <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a class="wh-link" href="{{group_1_button_url}}" style="display:inline-flex;align-items:center;gap:9px;background:linear-gradient(135deg,#2DA8FF,#16E0C8);color:#0B0E14;padding:15px 30px;border-radius:999px;font-weight:800;font-size:15px;box-shadow:0 0 30px rgba(45,168,255,.45);">{{group_1_button_text}}</a>
            <a class="wh-link" href="{{group_2_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;border:1px solid rgba(255,255,255,.18);padding:15px 28px;border-radius:999px;font-weight:700;font-size:15px;box-shadow:0 10px 30px rgba(37,211,102,.35);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_2_button_text}}</a>
          </div>
          <div class="wh-mono" style="display:flex;gap:26px;flex-wrap:wrap;margin-top:40px;color:#8FA2B6;font-size:13px;">
            <div><div style="color:#16E0C8;font-size:11px;letter-spacing:1px;">{{group_1_text_2}}</div><div style="color:#E6EDF5;font-weight:500;">{{group_2_text}}</div></div>
            <div style="width:1px;background:rgba(255,255,255,.12);"></div>
            <div><div style="color:#16E0C8;font-size:11px;letter-spacing:1px;">{{group_1_text_3}}</div><div style="color:#E6EDF5;font-weight:500;">{{group_2_text_2}}</div></div>
            <div style="width:1px;background:rgba(255,255,255,.12);"></div>
            <div><div style="color:#16E0C8;font-size:11px;letter-spacing:1px;">{{group_1_text_4}}</div><div style="color:#E6EDF5;font-weight:500;">{{group_2_text_3}}</div></div>
          </div>
        </div>

        <!-- Cihaz silüeti / teknik placeholder -->
        <!-- 🖼️ GÖRSEL: /assets/img/woorhi-device.jpg (oran 4:3)
             PROMPT: "Photorealistic product shot of a premium South Korean medical aesthetic device, futuristic engineering design, sleek metal and glass surfaces, glowing neon blue and cyan light accents along the panel edges, dark studio background with dramatic rim lighting, high-tech control interface with subtle illuminated display, professional clinical equipment, cinematic depth of field, no text, no logo, no watermark, high resolution"
             DEĞİŞTİR → <img src="/assets/img/woorhi-device.jpg" alt="Woorhi medikal estetik cihazı — koyu zeminde neon mavi-cyan ışık vurgulu fütüristik ürün çekimi" style="width:100%;height:100%;object-fit:cover;border-radius:16px;"> -->
        <div style="position:relative;min-height:430px;flex:1 1 360px;">
          <div style="position:absolute;inset:8px;border-radius:28px;background:linear-gradient(135deg,rgba(45,168,255,.30),rgba(22,224,200,.18));filter:blur(28px);"></div>
          <div style="position:relative;background:linear-gradient(160deg,rgba(255,255,255,.06),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.14);border-radius:24px;padding:26px;backdrop-filter:blur(8px);box-shadow:0 20px 60px rgba(0,0,0,.5),inset 0 1px 0 rgba(255,255,255,.08);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
              <span class="wh-mono" style="font-size:11px;letter-spacing:1.5px;color:#16E0C8;">{{group_1_text_5}}</span>
              <span style="display:inline-flex;align-items:center;gap:6px;" class="wh-mono"><span style="width:7px;height:7px;border-radius:50%;background:#16E0C8;box-shadow:0 0 10px #16E0C8;"></span><span style="font-size:11px;color:#9AD6FF;">{{group_2_text_4}}</span></span>
            </div>
            <div style="aspect-ratio:4/3;border-radius:16px;background:radial-gradient(120% 120% at 50% 0%,rgba(45,168,255,.22),rgba(11,14,20,.9)) url('/assets/img/woorhi-device.jpg') center/cover no-repeat;border:1px solid rgba(45,168,255,.28);display:grid;place-items:center;position:relative;overflow:hidden;">
              <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(90deg,rgba(45,168,255,.10) 1px,transparent 1px),linear-gradient(rgba(45,168,255,.10) 1px,transparent 1px);background-size:26px 26px;"></div>
            </div>
            <div class="wh-mono" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:18px;font-size:12px;">
              <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);border-radius:10px;padding:10px 12px;"><div style="color:#7E92A8;">{{group_1_text_6}}</div><div style="color:#E6EDF5;">{{group_2_text_5}}</div></div>
              <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);border-radius:10px;padding:10px 12px;"><div style="color:#7E92A8;">{{group_1_text_7}}</div><div style="color:#E6EDF5;">{{group_2_text_6}}</div></div>
            </div>
          </div>
          <div style="position:absolute;bottom:-16px;left:-14px;background:rgba(16,19,26,.92);border:1px solid rgba(45,168,255,.3);border-radius:14px;box-shadow:0 0 24px rgba(45,168,255,.25);padding:12px 16px;display:flex;align-items:center;gap:10px;">
            <span style="width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,#2DA8FF,#16E0C8);box-shadow:0 0 14px rgba(45,168,255,.5);flex-shrink:0;"></span>
            <div><div class="wh-mono" style="font-size:10px;color:#16E0C8;letter-spacing:1px;">{{group_3_text}}</div><div style="font-weight:700;font-size:13px;color:#E6EDF5;">{{group_3_text_2}}</div></div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_body_html": {"type": "textarea", "label": "Group 1 Body"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_1_text_5": {"type": "textarea", "label": "Group 1 Text 5"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_1_text_6": {"type": "textarea", "label": "Group 1 Text 6"}, "group_2_text_5": {"type": "textarea", "label": "Group 2 Text 5"}, "group_1_text_7": {"type": "textarea", "label": "Group 1 Text 7"}, "group_2_text_6": {"type": "textarea", "label": "Group 2 Text 6"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@400;500&display=swap", "group_1_button_url": "/urun-detay", "group_2_button_url": "https://wa.me/905426205100", "group_1_subtitle": "Güney Kore · Medikal Mekatronik", "group_1_title": "Mühendislik hassasiyetinde", "group_1_text": "estetik teknolojisi", "group_1_body_html": "Woorhi Mechatronics Co. Ltd., Kore mühendisliğiyle geliştirilen medikal estetik cihazları üretir. Hassas kontrol, klinik dayanıklılık ve tekrarlanabilir sonuçlar — kliniğinizin teknolojik altyapısı için tasarlandı.", "group_1_button_text": "Cihazı İncele →", "group_2_button_text": "WhatsApp Danışma", "group_1_text_2": "ORIGIN", "group_2_text": "Seoul · KR", "group_1_text_3": "CLASS", "group_2_text_2": "Klinik Cihaz", "group_1_text_4": "DIST · TR", "group_2_text_3": "Estetik Dermal", "group_1_text_5": "WOORHI · UNIT-01", "group_2_text_4": "ONLINE", "group_1_text_6": "PRECISION", "group_2_text_5": "± hassas kontrol", "group_1_text_7": "BUILD", "group_2_text_6": "KR Engineering", "group_3_text": "RAFFINE", "group_3_text_2": "Ana Cihaz Serisi"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-woorhi-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'2 · TEKNOLOJİ / MÜHENDİSLİK ŞERİDİ',
             'html_template'=><<<'EDHTML'
<!-- 2 · TEKNOLOJİ / MÜHENDİSLİK ŞERİDİ -->

    <section style="background:#10131A;border-top:1px solid rgba(255,255,255,.06);border-bottom:1px solid rgba(255,255,255,.06);">
      <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;padding:40px 0;">
        <div style="display:flex;align-items:center;gap:14px;background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.10);border-radius:16px;padding:18px 20px;backdrop-filter:blur(6px);">
          <span style="width:32px;height:32px;border-radius:9px;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.55),rgba(11,14,20,.4));border:1px solid rgba(45,168,255,.4);box-shadow:0 0 10px rgba(45,168,255,.4);flex-shrink:0;"></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">{{group_1_text}}</div><div class="wh-mono" style="font-size:11px;color:#8FA2B6;">{{group_1_text_2}}</div></div>
        </div>
        <div style="display:flex;align-items:center;gap:14px;background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.10);border-radius:16px;padding:18px 20px;backdrop-filter:blur(6px);">
          <span style="width:32px;height:32px;border-radius:9px;background:radial-gradient(120% 120% at 30% 20%,rgba(22,224,200,.55),rgba(11,14,20,.4));border:1px solid rgba(22,224,200,.4);box-shadow:0 0 10px rgba(22,224,200,.4);flex-shrink:0;"></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">{{group_2_text}}</div><div class="wh-mono" style="font-size:11px;color:#8FA2B6;">{{group_2_text_2}}</div></div>
        </div>
        <div style="display:flex;align-items:center;gap:14px;background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.10);border-radius:16px;padding:18px 20px;backdrop-filter:blur(6px);">
          <span style="width:32px;height:32px;border-radius:9px;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.55),rgba(11,14,20,.4));border:1px solid rgba(45,168,255,.4);box-shadow:0 0 10px rgba(45,168,255,.4);flex-shrink:0;"></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">{{group_3_text}}</div><div class="wh-mono" style="font-size:11px;color:#8FA2B6;">{{group_3_text_2}}</div></div>
        </div>
        <div style="display:flex;align-items:center;gap:14px;background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.10);border-radius:16px;padding:18px 20px;backdrop-filter:blur(6px);">
          <span style="width:32px;height:32px;border-radius:9px;background:radial-gradient(120% 120% at 30% 20%,rgba(22,224,200,.55),rgba(11,14,20,.4));border:1px solid rgba(22,224,200,.4);box-shadow:0 0 10px rgba(22,224,200,.4);flex-shrink:0;"></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">{{group_4_text}}</div><div class="wh-mono" style="font-size:11px;color:#8FA2B6;">{{group_4_text_2}}</div></div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_4_text_2": {"type": "textarea", "label": "Group 4 Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_text": "Hassas Kontrol", "group_1_text_2": "precision control", "group_2_text": "Kore Mühendisliği", "group_2_text_2": "made in korea", "group_3_text": "CE Uyumlu", "group_3_text_2": "ce compliant", "group_4_text": "Klinik Cihaz", "group_4_text_2": "clinical grade"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-woorhi-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'3 · RAFFINE CİHAZ SPOTLIGHT',
             'html_template'=><<<'EDHTML'
<!-- 3 · RAFFINE CİHAZ SPOTLIGHT -->

    <section style="padding:92px 0;background:#0B0E14;background-image:radial-gradient(800px 500px at 100% 50%, rgba(22,224,200,.12), transparent 60%);">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:56px;align-items:center;">
        <!-- sol görsel placeholder -->
        <!-- 🖼️ GÖRSEL: /assets/img/woorhi-raffine.jpg (oran 3:4)
             PROMPT: "Photorealistic spotlight studio shot of the Woorhi Raffine flagship medical aesthetic device, vertical hero composition, precision mechatronic engineering, brushed metal and tempered glass housing, glowing cyan-blue neon edge lighting and reflective highlights, dramatic dark studio background, high-tech digital control panel softly illuminated, professional clinical-grade equipment, sharp focus, cinematic lighting, no text, no logo, no watermark, high resolution"
             DEĞİŞTİR → <img src="/assets/img/woorhi-raffine.jpg" alt="Woorhi Raffine cihazı — koyu stüdyo zemininde neon mavi-cyan ışıklı dikey ürün spotlight çekimi" style="width:100%;height:100%;object-fit:cover;border-radius:24px;"> -->
        <div style="position:relative;min-height:400px;flex:1 1 360px;">
          <div style="position:absolute;inset:12px;border-radius:26px;background:linear-gradient(135deg,rgba(45,168,255,.28),rgba(22,224,200,.16));filter:blur(30px);"></div>
          <div style="position:relative;height:100%;min-height:400px;border-radius:24px;background:linear-gradient(160deg,rgba(255,255,255,.06),rgba(255,255,255,.015)) url('/assets/img/woorhi-raffine.jpg') center/cover no-repeat;border:1px solid rgba(255,255,255,.14);display:grid;place-items:center;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.5);">
            <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(90deg,rgba(45,168,255,.08) 1px,transparent 1px),linear-gradient(rgba(45,168,255,.08) 1px,transparent 1px);background-size:34px 34px;"></div>
            <div style="position:relative;text-align:center;">
              <div class="wh-mono" style="font-size:12px;letter-spacing:2px;color:#16E0C8;">{{group_2_text}}</div>
            </div>
          </div>
        </div>
        <!-- sağ içerik -->
        <div style="flex:1 1 420px;">
          <p class="wh-mono" style="display:inline-block;color:#16E0C8;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 14px;">{{group_2_subtitle}}</p>
          <h2 class="wh-display" style="font-size:clamp(30px,4.4vw,46px);font-weight:700;color:#F4F8FF;margin:0 0 18px;line-height:1.1;">{{group_2_title}}</h2>
          <p style="color:#AEBCCC;font-size:17px;line-height:1.75;margin:0 0 28px;max-width:520px;">{{{group_2_body_html}}}</p>

          <!-- monospace spec listesi -->
          <div class="wh-mono" style="background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid rgba(45,168,255,.22);border-radius:16px;padding:6px 0;margin:0 0 30px;max-width:520px;backdrop-filter:blur(6px);">
            <div style="display:flex;justify-content:space-between;gap:14px;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,.07);font-size:13px;"><span style="color:#7E92A8;">{{group_1_text}}</span><span style="color:#E6EDF5;">{{group_2_text_2}}</span></div>
            <div style="display:flex;justify-content:space-between;gap:14px;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,.07);font-size:13px;"><span style="color:#7E92A8;">{{group_1_text_2}}</span><span style="color:#E6EDF5;">{{group_2_text_3}}</span></div>
            <div style="display:flex;justify-content:space-between;gap:14px;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,.07);font-size:13px;"><span style="color:#7E92A8;">{{group_1_text_3}}</span><span style="color:#E6EDF5;">{{group_2_text_4}}</span></div>
            <div style="display:flex;justify-content:space-between;gap:14px;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,.07);font-size:13px;"><span style="color:#7E92A8;">{{group_1_text_4}}</span><span style="color:#E6EDF5;">{{group_2_text_5}}</span></div>
            <div style="display:flex;justify-content:space-between;gap:14px;padding:12px 20px;font-size:13px;"><span style="color:#7E92A8;">{{group_1_text_5}}</span><span style="color:#16E0C8;">{{group_2_text_6}}</span></div>
          </div>

          <a class="wh-link" href="{{group_2_button_url}}" style="display:inline-flex;align-items:center;gap:9px;background:linear-gradient(135deg,#2DA8FF,#16E0C8);color:#0B0E14;padding:15px 30px;border-radius:999px;font-weight:800;font-size:15px;box-shadow:0 0 30px rgba(45,168,255,.45);">{{group_2_button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_2_text_5": {"type": "textarea", "label": "Group 2 Text 5"}, "group_1_text_5": {"type": "textarea", "label": "Group 1 Text 5"}, "group_2_text_6": {"type": "textarea", "label": "Group 2 Text 6"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": "/urun-detay", "group_2_text": "RAFFINE · DEVICE", "group_2_subtitle": "// Ana Ürün", "group_2_title": "Raffine", "group_2_body_html": "Woorhi'nin amiral gemisi medikal estetik cihazı. Mekatronik kontrol mimarisi, kararlı güç yönetimi ve uygulama tekrarlanabilirliği üzerine kurulu; klinik kullanım için dayanıklı bir gövde ve sezgisel arayüzle tasarlandı.", "group_1_text": "[ TİP ]", "group_2_text_2": "Medikal estetik mekatronik cihaz", "group_1_text_2": "[ UYGULAMA ]", "group_2_text_3": "Yüz & vücut profesyonel bakım", "group_1_text_3": "[ KONTROL ]", "group_2_text_4": "Hassas dijital parametre yönetimi", "group_1_text_4": "[ MENŞE ]", "group_2_text_5": "Güney Kore mühendisliği", "group_1_text_5": "[ KULLANIM ]", "group_2_text_6": "Klinik / profesyonel", "group_2_button_text": "Raffine Detayları →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-woorhi-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'4 · NEDEN WOORHI',
             'html_template'=><<<'EDHTML'
<!-- 4 · NEDEN WOORHI -->

    <section style="padding:92px 0;background:#10131A;border-top:1px solid rgba(255,255,255,.06);">
      <div class="container">
        <div style="text-align:center;max-width:640px;margin:0 auto 52px;">
          <p class="wh-mono" style="color:#16E0C8;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 class="wh-display" style="font-size:clamp(28px,4vw,40px);font-weight:700;color:#F4F8FF;margin:0;line-height:1.15;">{{group_1_title}}</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:22px;">
          <!-- 🖼️ KART GÖRSELLERİ (tekrarlayan grid) — her kart ikonunun yerine opsiyonel görsel.
               Dosya adı deseni: /assets/img/woorhi-feature-1.jpg, woorhi-feature-2.jpg, woorhi-feature-3.jpg (oran 1:1)
               PROMPT (örnek/temsili): "Photorealistic close-up macro detail of high-tech medical aesthetic device component, glowing neon blue and cyan light, dark studio background, brushed metal and glass texture, futuristic engineering, dramatic lighting, no text, no logo, no watermark, high resolution"
               DEĞİŞTİR (kart ikonu <div>...</div> yerine) → <img src="/assets/img/woorhi-feature-1.jpg" alt="Woorhi cihaz detayı — neon mavi-cyan ışıklı yüksek teknoloji bileşeni" style="width:54px;height:54px;border-radius:15px;object-fit:cover;"> -->
          <div style="background:linear-gradient(160deg,rgba(255,255,255,.06),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:34px 30px;backdrop-filter:blur(8px);box-shadow:0 14px 40px rgba(0,0,0,.35);">
            <div style="width:54px;height:54px;border-radius:15px;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.5),rgba(11,14,20,.4)) url('/assets/img/woorhi-feature-1.jpg') center/cover no-repeat;border:1px solid rgba(45,168,255,.4);margin-bottom:20px;box-shadow:0 0 22px rgba(45,168,255,.3);"></div>
            <h3 class="wh-display" style="font-size:20px;font-weight:600;color:#F4F8FF;margin:0 0 10px;">{{group_1_title_2}}</h3>
            <p style="color:#AEBCCC;line-height:1.7;margin:0;font-size:15px;">{{group_1_description}}</p>
          </div>
          <div style="background:linear-gradient(160deg,rgba(255,255,255,.06),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:34px 30px;backdrop-filter:blur(8px);box-shadow:0 14px 40px rgba(0,0,0,.35);">
            <div style="width:54px;height:54px;border-radius:15px;background:radial-gradient(120% 120% at 30% 20%,rgba(22,224,200,.5),rgba(11,14,20,.4)) url('/assets/img/woorhi-feature-2.jpg') center/cover no-repeat;border:1px solid rgba(22,224,200,.4);margin-bottom:20px;box-shadow:0 0 22px rgba(22,224,200,.3);"></div>
            <h3 class="wh-display" style="font-size:20px;font-weight:600;color:#F4F8FF;margin:0 0 10px;">{{group_2_title}}</h3>
            <p style="color:#AEBCCC;line-height:1.7;margin:0;font-size:15px;">{{group_2_description}}</p>
          </div>
          <div style="background:linear-gradient(160deg,rgba(255,255,255,.06),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:34px 30px;backdrop-filter:blur(8px);box-shadow:0 14px 40px rgba(0,0,0,.35);">
            <div style="width:54px;height:54px;border-radius:15px;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.5),rgba(11,14,20,.4)) url('/assets/img/woorhi-feature-3.jpg') center/cover no-repeat;border:1px solid rgba(45,168,255,.4);margin-bottom:20px;box-shadow:0 0 22px rgba(45,168,255,.3);"></div>
            <h3 class="wh-display" style="font-size:20px;font-weight:600;color:#F4F8FF;margin:0 0 10px;">{{group_3_title}}</h3>
            <p style="color:#AEBCCC;line-height:1.7;margin:0;font-size:15px;">{{group_3_description}}</p>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_title_2": {"type": "text", "label": "Group 1 Title 2"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_subtitle": "// Neden Woorhi", "group_1_title": "Mühendisliğin estetikle buluştuğu nokta", "group_1_title_2": "İleri Teknoloji", "group_1_description": "Mekatronik kontrol mimarisi ve hassas parametre yönetimiyle tekrarlanabilir, kontrollü uygulamalar.", "group_2_title": "Klinik Dayanıklılık", "group_2_description": "Yoğun klinik kullanım için tasarlanmış sağlam gövde, kararlı güç yönetimi ve uzun ömürlü bileşenler.", "group_3_title": "Yerel Destek", "group_3_description": "Estetik Dermal güvencesiyle Türkiye'de kurulum, uygulamalı eğitim ve kesintisiz teknik servis desteği."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-woorhi-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'5 · CTA BANDI (neon gradient koyu)',
             'html_template'=><<<'EDHTML'
<!-- 5 · CTA BANDI (neon gradient koyu) -->

    <section style="padding:30px 0 92px;background:#0B0E14;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:linear-gradient(120deg,#0E2A44 0%,#0B1C2C 45%,#0A2A2A 100%);border:1px solid rgba(45,168,255,.3);padding:clamp(40px,6vw,72px);text-align:center;box-shadow:0 0 60px rgba(45,168,255,.18);">
          <div aria-hidden="true" style="position:absolute;inset:0;background-image:radial-gradient(600px 300px at 15% 0%, rgba(45,168,255,.4), transparent 60%),radial-gradient(600px 300px at 90% 100%, rgba(22,224,200,.3), transparent 60%);"></div>
          <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:42px 42px;mask-image:radial-gradient(700px 300px at 50% 50%,#000,transparent 75%);"></div>
          <div style="position:relative;">
            <p class="wh-mono" style="color:#9AD6FF;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 14px;">{{group_3_subtitle}}</p>
            <h2 class="wh-display" style="font-size:clamp(26px,4vw,40px);font-weight:700;margin:0 0 14px;line-height:1.15;color:#F4F8FF;">{{group_3_title}}</h2>
            <p style="font-size:18px;color:#C7D3E2;max-width:600px;margin:0 auto 32px;line-height:1.65;">{{{group_3_body_html}}}</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a class="wh-link" href="{{group_1_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 32px;border-radius:999px;font-weight:800;font-size:15px;box-shadow:0 0 30px rgba(37,211,102,.5);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_1_button_text}}</a>
              <a class="wh-link" href="{{group_2_button_url}}" style="background:rgba(255,255,255,.06);color:#E6EDF5;border:1px solid rgba(255,255,255,.22);padding:15px 32px;border-radius:999px;font-weight:700;font-size:15px;">{{group_2_button_text}}</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_subtitle": {"type": "textarea", "label": "Group 3 Subtitle"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_body_html": {"type": "textarea", "label": "Group 3 Body"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "https://wa.me/905426205100", "group_2_button_url": "/urun-detay", "group_3_subtitle": "// Demo & Teklif", "group_3_title": "Woorhi cihazları için demo / teklif alın", "group_3_body_html": "Raffine ve Woorhi cihaz serisi hakkında detaylı bilgi, demo planlaması ve fiyat teklifi için Estetik Dermal ekibine ulaşın.", "group_1_button_text": "WhatsApp ile Yaz", "group_2_button_text": "Cihazı İncele"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-mi-medical-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'1 · BESPOKE PREMIUM HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link rel="stylesheet" href="{{group_3_link_url}}"><style>
    /* Mi Medical — premium siyah-altın palet override (yalnızca bu sayfa) */
    :root {
      --color-primary: #C9A24B;   /* altın */
      --color-accent:  #E3C97E;   /* açık altın */
      --color-secondary: #F7F3EA; /* fildişi */
      --mi-black: #141414;        /* siyah */
      --mi-black-soft: #1d1d1d;
      --mi-ivory-soft: #FBF8F0;
      --mi-gold-line: rgba(201,162,75,.32);
      --mi-gold-grad: linear-gradient(135deg, #C9A24B 0%, #E3C97E 100%);
      --mi-ink: #2a2622;          /* fildişi zeminde koyu metin */
      --mi-ink-soft: #6c6258;
    }
    .mi-serif { font-family: "Playfair Display", "Cormorant Garamond", Georgia, "Times New Roman", serif; }
    .mi-serif-light { font-family: "Cormorant Garamond", "Playfair Display", Georgia, serif; }
  </style><!-- 1 · BESPOKE PREMIUM HERO -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(900px 520px at 78% -8%, rgba(201,162,75,.16), transparent 62%), linear-gradient(180deg,#141414 0%, #181715 100%);">
      <!-- ince altın çerçeve -->
      <div aria-hidden="true" style="position:absolute;inset:22px;border:1px solid var(--mi-gold-line,rgba(201,162,75,.32));border-radius:6px;pointer-events:none;"></div>
      <div class="container" style="display:flex;flex-wrap:wrap;gap:56px;align-items:center;padding:96px 0 104px;position:relative;">
        <div style="flex:1 1 480px;">
          <p style="display:inline-flex;align-items:center;gap:12px;color:var(--color-accent,#E3C97E);font-size:12px;font-weight:600;letter-spacing:3.5px;text-transform:uppercase;margin:0 0 30px;">
            <span aria-hidden="true" style="display:inline-block;width:34px;height:1px;background:var(--color-primary,#C9A24B);"></span>
            {{group_1_subtitle}}
          </p>
          <h1 class="mi-serif" style="font-size:clamp(38px,5.4vw,66px);line-height:1.06;font-weight:600;color:var(--color-secondary,#F7F3EA);margin:0 0 26px;letter-spacing:.3px;">
            {{group_1_title}}<br><span style="font-style:italic;background:var(--mi-gold-grad,linear-gradient(135deg,#C9A24B,#E3C97E));-webkit-background-clip:text;background-clip:text;color:transparent;">{{group_1_text}}</span> {{group_1_title_2}}
          </h1>
          <p style="font-size:clamp(16px,1.6vw,19px);color:rgba(247,243,234,.72);line-height:1.85;max-width:540px;margin:0 0 40px;">
            {{{group_2_body_html}}}
          </p>
          <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <a href="{{group_1_button_url}}" style="display:inline-flex;align-items:center;gap:9px;background:var(--mi-gold-grad,linear-gradient(135deg,#C9A24B,#E3C97E));color:#141414;padding:16px 32px;border-radius:999px;font-weight:700;font-size:15px;box-shadow:0 14px 34px rgba(201,162,75,.26);">{{group_1_button_text}}</a>
            <a href="{{group_2_button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;border:1px solid rgba(255,255,255,.18);padding:16px 30px;border-radius:999px;font-weight:600;font-size:15px;box-shadow:0 10px 30px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{group_2_button_text}}</a>
          </div>
        </div>

        <!-- Premium cihaz placeholder (siyah + altın) -->
        <!-- 🖼️ GÖRSEL: /assets/img/mi-medical-hero.jpg (oran 1:1)
             PROMPT: "Photorealistic luxury product shot of a premium mesotherapy and injection device (Pistor Eliance), elegant minimal design, sophisticated black background with warm golden rim light and soft golden reflections, precision medical injection system, refined matte and polished surfaces, dramatic studio lighting, high-end clinical aesthetic, cinematic depth of field, no text, no logo, no watermark, high resolution"
             DEĞİŞTİR → <img src="/assets/img/mi-medical-hero.jpg" alt="Pistor Eliance premium enjeksiyon cihazı — siyah zeminde altın ışıklı lüks ürün çekimi" style="width:min(86%,320px);aspect-ratio:1/1;object-fit:cover;border-radius:22px;"> -->
        <div style="position:relative;flex:1 1 320px;min-height:420px;display:grid;place-items:center;">
          <div aria-hidden="true" style="position:absolute;width:300px;height:300px;border:1px solid var(--mi-gold-line,rgba(201,162,75,.32));border-radius:50%;"></div>
          <div aria-hidden="true" style="position:absolute;width:380px;height:380px;border:1px solid rgba(201,162,75,.14);border-radius:50%;"></div>
          <div style="position:relative;width:min(86%,320px);background:linear-gradient(160deg,#1f1d1a 0%, #141414 100%);border:1px solid var(--mi-gold-line,rgba(201,162,75,.32));border-radius:22px;padding:34px 30px;box-shadow:0 30px 70px rgba(0,0,0,.5);">
            <div style="aspect-ratio:1/1;border-radius:16px;background:radial-gradient(circle at 50% 38%, rgba(201,162,75,.18), transparent 60%), #18120a url('/assets/img/mi-medical-hero.jpg') center/cover no-repeat;border:1px solid rgba(201,162,75,.2);"></div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:22px;">
              <div>
                <div class="mi-serif" style="font-size:19px;color:var(--color-secondary,#F7F3EA);">{{group_2_text}}</div>
                <div style="font-size:11.5px;letter-spacing:2px;text-transform:uppercase;color:var(--color-accent,#E3C97E);margin-top:3px;">{{group_2_text_2}}</div>
              </div>
              <span aria-hidden="true" style="width:28px;height:28px;border-radius:50%;background:var(--mi-gold-grad,linear-gradient(135deg,#C9A24B,#E3C97E));flex-shrink:0;"></span>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_title_2": {"type": "text", "label": "Group 1 Title 2"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Playfair+Display:wght@500;600;700&display=swap", "group_1_button_url": "/urun-detay", "group_2_button_url": "https://wa.me/905426205100", "group_1_subtitle": "Premium Enjeksiyon Sistemleri", "group_1_title": "Hassasiyetin ve", "group_1_text": "zarafetin", "group_1_title_2": "buluşması", "group_2_body_html": "Mi Medical Innovation; premium mezoterapi ve enjeksiyon sistemlerinde inovasyonu zarafetle buluşturur. Her ayrıntısı, hekimin elinde kusursuz kontrol ve hastada üst düzey konfor için tasarlanmıştır.", "group_1_button_text": "Pistor Eliance'ı Keşfet →", "group_2_button_text": "Ayrıcalıklı Danışmanlık", "group_2_text": "Pistor Eliance", "group_2_text_2": "Mi Medical Innovation"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-mi-medical-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'2 · İNCE ALTIN AYRAÇLI DEĞER ŞERİDİ',
             'html_template'=><<<'EDHTML'
<!-- 2 · İNCE ALTIN AYRAÇLI DEĞER ŞERİDİ -->

    <section style="background:var(--mi-black-soft,#1d1d1d);border-top:1px solid var(--mi-gold-line,rgba(201,162,75,.32));border-bottom:1px solid var(--mi-gold-line,rgba(201,162,75,.32));">
      <!-- 🖼️ DEĞER KARTI GÖRSELLERİ (tekrarlayan grid) — her kartın ◇/◈ glifinin yerine opsiyonel görsel.
           Dosya adı deseni: /assets/img/mi-medical-value-1.jpg, mi-medical-value-2.jpg, mi-medical-value-3.jpg (oran 1:1)
           PROMPT (örnek/temsili): "Photorealistic elegant macro detail of premium medical injection device feature, sophisticated black background with soft golden light and refined gold reflections, minimal luxury aesthetic, polished premium materials, dramatic studio lighting, no text, no logo, no watermark, high resolution"
           DEĞİŞTİR (◇ glif <div>...</div> yerine) → <img src="/assets/img/mi-medical-value-1.jpg" alt="Mi Medical premium cihaz detayı — siyah zeminde altın ışıklı lüks makro çekim" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin:0 auto 12px;display:block;"> -->
      <div class="container" style="display:flex;flex-wrap:wrap;align-items:stretch;padding:54px 0;gap:0;">
        <div style="flex:1 1 240px;padding:8px 36px;text-align:center;">
          <div style="width:60px;height:60px;border-radius:50%;margin:0 auto 12px;display:grid;place-items:center;font-size:30px;color:var(--color-primary,#C9A24B);background:url('/assets/img/mi-medical-value-1.jpg') center/cover no-repeat;">{{group_1_text}}</div>
          <h3 class="mi-serif" style="font-size:23px;font-weight:600;color:var(--color-secondary,#F7F3EA);margin:0 0 8px;">{{group_1_title}}</h3>
          <p style="color:rgba(247,243,234,.62);font-size:14.5px;line-height:1.7;margin:0;">{{group_1_description}}</p>
        </div>
        <div aria-hidden="true" style="width:1px;background:var(--mi-gold-line,rgba(201,162,75,.32));align-self:stretch;"></div>
        <div style="flex:1 1 240px;padding:8px 36px;text-align:center;">
          <div style="width:60px;height:60px;border-radius:50%;margin:0 auto 12px;display:grid;place-items:center;font-size:30px;color:var(--color-primary,#C9A24B);background:url('/assets/img/mi-medical-value-2.jpg') center/cover no-repeat;">{{group_3_text}}</div>
          <h3 class="mi-serif" style="font-size:23px;font-weight:600;color:var(--color-secondary,#F7F3EA);margin:0 0 8px;">{{group_3_title}}</h3>
          <p style="color:rgba(247,243,234,.62);font-size:14.5px;line-height:1.7;margin:0;">{{group_3_description}}</p>
        </div>
        <div aria-hidden="true" style="width:1px;background:var(--mi-gold-line,rgba(201,162,75,.32));align-self:stretch;"></div>
        <div style="flex:1 1 240px;padding:8px 36px;text-align:center;">
          <div style="width:60px;height:60px;border-radius:50%;margin:0 auto 12px;display:grid;place-items:center;font-size:30px;color:var(--color-primary,#C9A24B);background:url('/assets/img/mi-medical-value-3.jpg') center/cover no-repeat;">{{group_5_text}}</div>
          <h3 class="mi-serif" style="font-size:23px;font-weight:600;color:var(--color-secondary,#F7F3EA);margin:0 0 8px;">{{group_5_title}}</h3>
          <p style="color:rgba(247,243,234,.62);font-size:14.5px;line-height:1.7;margin:0;">{{group_5_description}}</p>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}, "group_5_text": {"type": "textarea", "label": "Group 5 Text"}, "group_5_title": {"type": "text", "label": "Group 5 Title"}, "group_5_description": {"type": "textarea", "label": "Group 5 Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_text": "◇", "group_1_title": "Hassas Dozaj", "group_1_description": "Mikrolitre düzeyinde kontrol ile her uygulamada tutarlı, öngörülebilir sonuç.", "group_3_text": "◈", "group_3_title": "Premium Malzeme", "group_3_description": "Dengeli ergonomi ve uzun ömürlü, üst sınıf bileşenlerle işlenmiş cihaz estetiği.", "group_5_text": "◇", "group_5_title": "Klinik Güven", "group_5_description": "Hekimlerin tercihi; güvenli, konforlu ve tekrarlanabilir profesyonel uygulama."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-mi-medical-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'3 · PISTOR ELIANCE SPOTLIGHT (fildişi)',
             'html_template'=><<<'EDHTML'
<!-- 3 · PISTOR ELIANCE SPOTLIGHT (fildişi) -->

    <section style="background:var(--color-secondary,#F7F3EA);padding:104px 0;">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:64px;align-items:center;">
        <!-- görsel -->
        <!-- 🖼️ GÖRSEL: /assets/img/mi-medical-pistor.jpg (oran 3:4)
             PROMPT: "Photorealistic spotlight studio shot of the Pistor Eliance premium mesotherapy injection gun, vertical hero composition, sophisticated black background with dramatic golden lighting and elegant gold highlights, sleek ergonomic precision medical injection system, refined premium materials, minimal luxury aesthetic, sharp focus, cinematic lighting, no text, no logo, no watermark, high resolution"
             DEĞİŞTİR → <img src="/assets/img/mi-medical-pistor.jpg" alt="Pistor Eliance mezoterapi enjeksiyon sistemi — siyah zeminde altın ışıklı dikey lüks spotlight çekimi" style="width:100%;height:100%;object-fit:cover;border-radius:24px;"> -->
        <div style="flex:1 1 340px;position:relative;min-height:440px;display:grid;place-items:center;">
          <div aria-hidden="true" style="position:absolute;inset:0;background:linear-gradient(150deg,#141414 0%, #211d16 100%) url('/assets/img/mi-medical-pistor.jpg') center/cover no-repeat;border-radius:24px;"></div>
          <div aria-hidden="true" style="position:absolute;inset:16px;border:1px solid var(--mi-gold-line,rgba(201,162,75,.32));border-radius:16px;"></div>
          <div style="position:relative;text-align:center;color:var(--color-secondary,#F7F3EA);padding:40px;">
            <div class="mi-serif" style="font-size:26px;font-weight:600;">{{group_3_text}}</div>
            <div style="font-size:11.5px;letter-spacing:2.5px;text-transform:uppercase;color:var(--color-accent,#E3C97E);margin-top:8px;">{{group_3_text_2}}</div>
          </div>
        </div>
        <!-- içerik -->
        <div style="flex:1 1 420px;">
          <p style="display:inline-flex;align-items:center;gap:12px;color:#9a7d33;font-size:12px;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin:0 0 18px;">
            <span aria-hidden="true" style="display:inline-block;width:30px;height:1px;background:var(--color-primary,#C9A24B);"></span>
            {{group_1_subtitle}}
          </p>
          <h2 class="mi-serif" style="font-size:clamp(30px,4.4vw,48px);font-weight:600;color:var(--mi-ink,#2a2622);margin:0 0 20px;line-height:1.12;">{{group_2_title}}</h2>
          <p style="color:var(--mi-ink-soft,#6c6258);font-size:17px;line-height:1.85;margin:0 0 30px;max-width:520px;">
            {{{group_2_body_html}}}
          </p>
          <ul style="list-style:none;margin:0 0 36px;padding:0;display:grid;gap:15px;">
            <li style="display:flex;align-items:flex-start;gap:14px;color:var(--mi-ink,#2a2622);font-size:16px;font-weight:500;"><span aria-hidden="true" style="color:var(--color-primary,#C9A24B);font-size:18px;line-height:1.4;">{{group_1_text}}</span> {{group_1_item_text}}</li>
            <li style="display:flex;align-items:flex-start;gap:14px;color:var(--mi-ink,#2a2622);font-size:16px;font-weight:500;"><span aria-hidden="true" style="color:var(--color-primary,#C9A24B);font-size:18px;line-height:1.4;">{{group_2_text}}</span> {{group_2_item_text}}</li>
            <li style="display:flex;align-items:flex-start;gap:14px;color:var(--mi-ink,#2a2622);font-size:16px;font-weight:500;"><span aria-hidden="true" style="color:var(--color-primary,#C9A24B);font-size:18px;line-height:1.4;">{{group_3_text_3}}</span> {{group_3_item_text}}</li>
            <li style="display:flex;align-items:flex-start;gap:14px;color:var(--mi-ink,#2a2622);font-size:16px;font-weight:500;"><span aria-hidden="true" style="color:var(--color-primary,#C9A24B);font-size:18px;line-height:1.4;">{{group_4_text}}</span> {{group_4_item_text}}</li>
          </ul>
          <a href="{{group_2_button_url}}" style="display:inline-flex;align-items:center;gap:9px;background:var(--mi-black,#141414);color:var(--color-accent,#E3C97E);border:1px solid var(--color-primary,#C9A24B);padding:15px 30px;border-radius:999px;font-weight:600;font-size:15px;">{{group_2_button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_text_3": {"type": "textarea", "label": "Group 3 Text 3"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_4_item_text": {"type": "textarea", "label": "Group 4 Item Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_2_button_url": "/urun-detay", "group_3_text": "Pistor Eliance", "group_3_text_2": "Premium Enjeksiyon Sistemi", "group_1_subtitle": "Öne Çıkan Sistem", "group_2_title": "Pistor Eliance", "group_2_body_html": "Premium mezoterapi tabancası ve enjeksiyon sistemi; hassas dozaj kontrolü, sessiz mekanizması ve ergonomik dengesiyle hekime kusursuz hâkimiyet, hastaya ise belirgin biçimde daha konforlu bir uygulama deneyimi sunar.", "group_1_text": "✓", "group_1_item_text": "Ayarlanabilir, mikro hassasiyetli dozaj kontrolü", "group_2_text": "✓", "group_2_item_text": "Dengeli, ergonomik tutuş ve düşük titreşim", "group_3_text_3": "✓", "group_3_item_text": "Hasta konforunu artıran konforlu, hızlı uygulama", "group_4_text": "✓", "group_4_item_text": "Premium malzeme ve uzun ömürlü cihaz kalitesi", "group_2_button_text": "Ürün Detayını Gör →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-mi-medical-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'4 · MARKA VAADİ (fildişi, zarif)',
             'html_template'=><<<'EDHTML'
<!-- 4 · MARKA VAADİ (fildişi, zarif) -->

    <section style="background:var(--mi-ivory-soft,#FBF8F0);padding:100px 0;border-top:1px solid rgba(201,162,75,.18);">
      <div class="container" style="max-width:780px;margin:0 auto;text-align:center;">
        <div aria-hidden="true" style="width:46px;height:1px;background:var(--color-primary,#C9A24B);margin:0 auto 30px;"></div>
        <p style="color:#9a7d33;font-size:12px;font-weight:700;letter-spacing:3.5px;text-transform:uppercase;margin:0 0 24px;">{{subtitle}}</p>
        <p class="mi-serif-light" style="font-size:clamp(24px,3.4vw,36px);line-height:1.5;color:var(--mi-ink,#2a2622);margin:0;font-weight:500;">
          {{{body_html}}} <span style="font-style:italic;color:#a8842f;">{{text}}</span> {{subtitle_2}}
        </p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"subtitle": {"type": "textarea", "label": "Subtitle"}, "body_html": {"type": "textarea", "label": "Body"}, "text": {"type": "textarea", "label": "Text"}, "subtitle_2": {"type": "textarea", "label": "Subtitle 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"subtitle": "Marka Vaadi", "body_html": "İnovasyon ile zarafetin aynı çizgide buluştuğuna inanıyoruz. Mi Medical Innovation, en ileri mühendisliği rafine bir tasarım diliyle harmanlayarak, profesyonel uygulamanın her anına", "text": "sessiz bir lüks", "subtitle_2": "kazandırır."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'klasik-s-marka-mi-medical-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'5 · CTA BANDI (siyah + altın)',
             'html_template'=><<<'EDHTML'
<!-- 5 · CTA BANDI (siyah + altın) -->

    <section style="background:linear-gradient(180deg,#181715 0%, #141414 100%);padding:90px 0;border-top:1px solid var(--mi-gold-line,rgba(201,162,75,.32));">
      <div class="container">
        <div style="position:relative;overflow:hidden;border:1px solid var(--mi-gold-line,rgba(201,162,75,.32));border-radius:24px;padding:clamp(44px,6vw,76px);text-align:center;background:radial-gradient(700px 360px at 50% -20%, rgba(201,162,75,.14), transparent 60%);">
          <div aria-hidden="true" style="width:46px;height:1px;background:var(--color-primary,#C9A24B);margin:0 auto 26px;"></div>
          <h2 class="mi-serif" style="font-size:clamp(28px,4.4vw,44px);font-weight:600;color:var(--color-secondary,#F7F3EA);margin:0 0 18px;line-height:1.15;">{{title}}<br>{{title_2}}</h2>
          <p style="font-size:17px;color:rgba(247,243,234,.7);max-width:560px;margin:0 auto 36px;line-height:1.75;">{{{body_html}}}</p>
          <a href="{{button_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:10px;background:#25D366;color:#fff;padding:16px 36px;border-radius:999px;font-weight:700;font-size:15px;box-shadow:0 14px 34px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>{{button_text}}</a>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "title": {"type": "text", "label": "Title"}, "title_2": {"type": "text", "label": "Title 2"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "title": "Mi Medical çözümleri için", "title_2": "ayrıcalıklı danışmanlık", "body_html": "Pistor Eliance ve premium enjeksiyon sistemleri hakkında ürün, fiyat ve uygulama bilgisi için Estetik Dermal ekibiyle iletişime geçin.", "button_text": "WhatsApp ile İletişime Geç"}
EDJSON, true),
             'is_active'=>true]
        );
        $this->command?->info('EstetikDermalKlasikFieldChromeSeeder: <built-in function len> bölüm şablonu kuruldu.');
    }
}
