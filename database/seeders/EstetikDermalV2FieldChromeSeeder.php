<?php

namespace Database\Seeders;

use App\Models\SectionTemplate;
use App\Models\Theme;
use Illuminate\Database\Seeder;

/** EstetikDermalV2FieldChromeSeeder: her bölüm için alan-tabanlı (schema+repeater) section template (central). */
class EstetikDermalV2FieldChromeSeeder extends Seeder
{
    private const TENANT_ID='estetik_dermal';
    public function run(): void
    {
        $theme=Theme::where('slug','estetikdermal-v2')->first();
        if(! $theme){ $this->command?->warn('estetikdermal-v2 teması yok.'); return; }
        $tid=$theme->id;
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-home-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><!-- HERO -->

    <section class="hero">
      <div class="hero__grid" aria-hidden="true"></div>
      <div class="wrap hero__in">
        <div class="hero__copy">
          <span class="eyebrow reveal">{{text}}</span>
          <h1 class="reveal d1">{{title}} <em>{{text_2}}</em> {{title_2}}</h1>
          <p class="hero__sub reveal d2">{{{body_html}}}</p>
          <div class="hero__cta reveal d3">
            <a class="btn btn--primary" href="{{button_url}}">{{button_text}}</a>
            <a class="btn btn--ghost" href="{{button_url_2}}" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 4 1.7 4 1.2 4.7 1.1.7-.1 2.4-1 2.7-1.9.3-1 .3-1.8.2-1.9z"/></svg>
              {{button_text_2}}</a>
          </div>
          <div class="hero__trust reveal d4">
            <div><b>{{group_1_text}}</b> {{group_1_text_2}}</div>
            <div><b>{{group_2_text}}</b> {{group_2_text_2}}</div>
            <div><b>{{group_3_text}}</b> {{group_3_text_2}}</div>
          </div>
        </div>
        <div class="hero__visual reveal d2">
          <div class="hero__card">
            <!-- 🖼️ GÖRSEL: assets/img/v2-hero.jpg (oran 4:5) — macro ürün/lab çekimi, temiz açık zemin, klinik lüks -->
            <div class="hero__img" style="background-image:url('{{background_image_url}}')"></div>
            <div class="hero__badge">
              <span class="ce">{{text_3}}</span>
              <div><small>{{text_4}}</small><b>{{text_5}}</b></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "background_image_url": {"type": "image", "label": "Background Image Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "text_2": {"type": "textarea", "label": "Text 2"}, "title_2": {"type": "text", "label": "Title 2"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "text_4": {"type": "textarea", "label": "Text 4"}, "text_5": {"type": "textarea", "label": "Text 5"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap", "button_url": "#urunler", "button_url_2": "https://wa.me/905426205100", "background_image_url": "assets/img/v2-hero.jpg", "text": "2004'ten bu yana · Resmi Distribütör", "title": "Medikal estetikte", "text_2": "güvenin", "title_2": "adresi.", "body_html": "CE Class III sertifikalı ürünler, uluslararası markalar ve doktorlara özel uygulamalı eğitim — kliniğinizin profesyonel tedarik ortağı.", "button_text": "Ürünleri Keşfet", "button_text_2": "Bilgi Al — WhatsApp", "group_1_text": "CE Class III", "group_1_text_2": "Sertifikalı portföy", "group_2_text": "20+ yıl", "group_2_text_2": "Sektör deneyimi", "group_3_text": "6 marka", "group_3_text_2": "Resmi temsilcilik", "text_3": "CE", "text_4": "Öne çıkan ürün", "text_5": "RRS® HA Long Lasting"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-home-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'TRUST BAND + COUNTERS + MARQUEE',
             'html_template'=><<<'EDHTML'
<!-- TRUST BAND + COUNTERS + MARQUEE -->

    <section class="band">
      <div class="wrap stats">
        <div class="reveal"><div class="stat__n"><span data-count="20">{{text}}</span><span class="accent">{{text_2}}</span></div><div class="stat__l">{{text_3}}</div></div>
        <div class="reveal d1"><div class="stat__n"><span data-count="6">{{text_4}}</span></div><div class="stat__l">{{text_5}}</div></div>
        <div class="reveal d2"><div class="stat__n"><span data-count="14">{{text_6}}</span></div><div class="stat__l">{{text_7}}</div></div>
        <div class="reveal d3"><div class="stat__n">{{text_8}} <span class="accent">{{text_9}}</span></div><div class="stat__l">{{text_10}}</div></div>
      </div>
      <div class="marquee" aria-hidden="true">
        <div class="marquee__track">
          <span>{{group_1_text}}</span><span>{{group_2_text}}</span><span>{{group_3_text}}</span><span>{{group_4_text}}</span><span>{{group_5_text}}</span><span>{{group_6_text}}</span>
          <span>{{group_7_text}}</span><span>{{group_8_text}}</span><span>{{group_9_text}}</span><span>{{group_10_text}}</span><span>{{group_11_text}}</span><span>{{group_12_text}}</span>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "text_4": {"type": "textarea", "label": "Text 4"}, "text_5": {"type": "textarea", "label": "Text 5"}, "text_6": {"type": "textarea", "label": "Text 6"}, "text_7": {"type": "textarea", "label": "Text 7"}, "text_8": {"type": "textarea", "label": "Text 8"}, "text_9": {"type": "textarea", "label": "Text 9"}, "text_10": {"type": "textarea", "label": "Text 10"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_5_text": {"type": "textarea", "label": "Group 5 Text"}, "group_6_text": {"type": "textarea", "label": "Group 6 Text"}, "group_7_text": {"type": "textarea", "label": "Group 7 Text"}, "group_8_text": {"type": "textarea", "label": "Group 8 Text"}, "group_9_text": {"type": "textarea", "label": "Group 9 Text"}, "group_10_text": {"type": "textarea", "label": "Group 10 Text"}, "group_11_text": {"type": "textarea", "label": "Group 11 Text"}, "group_12_text": {"type": "textarea", "label": "Group 12 Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"text": "0", "text_2": "+", "text_3": "Yıllık deneyim", "text_4": "0", "text_5": "Uluslararası marka temsilciliği", "text_6": "0", "text_7": "Ürün kategorisi", "text_8": "CE", "text_9": "III", "text_10": "Sertifikalı portföy", "group_1_text": "Skin Tech Pharma Group", "group_2_text": "MI-Medical Innovation", "group_3_text": "Neogenesis", "group_4_text": "Seffiline", "group_5_text": "Woorhi Mechatronics", "group_6_text": "Grand Aespio", "group_7_text": "Skin Tech Pharma Group", "group_8_text": "MI-Medical Innovation", "group_9_text": "Neogenesis", "group_10_text": "Seffiline", "group_11_text": "Woorhi Mechatronics", "group_12_text": "Grand Aespio"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-home-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'FEATURED PRODUCTS',
             'html_template'=><<<'EDHTML'
<!-- FEATURED PRODUCTS -->

    <section class="section" id="urunler">
      <div class="wrap">
        <div class="feature">
          <div class="feature__media reveal">
            <!-- 🖼️ GÖRSEL: assets/img/v2-rrs.jpg (oran 5:4) — RRS HA Long Lasting macro ürün çekimi -->
            <div class="feature__img" style="background-image:url('{{background_image_url}}')"></div>
          </div>
          <div class="reveal d1">
            <span class="eyebrow">{{text}}</span>
            <h2>{{title}}</h2>
            <p class="lead">{{{body_html}}}</p>
            <p style="margin:18px 0 24px;"><span class="badge-ce">{{text_2}}</span></p>
            <a class="link-arrow" href="{{button_url}}">{{button_text}} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </div>

        <div class="feature feature--rev">
          <div class="feature__media reveal">
            <!-- 🖼️ GÖRSEL: assets/img/v2-melablock.jpg (oran 5:4) — Melablock HSP SPF 50+ ürün çekimi -->
            <div class="feature__img" style="background-image:url('{{background_image_url_2}}')"></div>
          </div>
          <div class="reveal d1">
            <span class="eyebrow">{{text_3}}</span>
            <h2>{{title_2}}</h2>
            <p class="lead">{{{body_html_2}}}</p>
            <a class="link-arrow" href="{{button_url_2}}" style="margin-top:22px;">{{button_text_2}} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"background_image_url": {"type": "image", "label": "Background Image Url"}, "button_url": {"type": "text", "label": "Button Url"}, "background_image_url_2": {"type": "image", "label": "Background Image Url 2"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "text_2": {"type": "textarea", "label": "Text 2"}, "button_text": {"type": "textarea", "label": "Button Text"}, "text_3": {"type": "textarea", "label": "Text 3"}, "title_2": {"type": "text", "label": "Title 2"}, "body_html_2": {"type": "textarea", "label": "Body Html 2"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"background_image_url": "assets/img/v2-rrs.jpg", "button_url": "urun-detay.html", "background_image_url_2": "assets/img/v2-melablock.jpg", "button_url_2": "urunler.html", "text": "Öne Çıkan Ürün · Skin Tech", "title": "RRS® HA Long Lasting", "body_html": "Çapraz bağlı, emilebilir hyalüronik asit içeren CE Class III dermal implant. Amino asit içeren koruyucu tampon solüsyonunda; cilt kalitesini içten iyileştiren uzun etkili skinbooster.", "text_2": "● CE Class III · Tıbbi cihaz sınıfı", "button_text": "Detaylı bilgi al", "text_3": "Güneş Koruma · Skin Tech", "title_2": "Melablock HSP SPF 50+", "body_html_2": "Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktörlü formül; lazer ve peeling sonrası termal hasara karşı savunma sağlar.", "button_text_2": "Tüm ürünleri gör"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-home-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CATEGORIES',
             'html_template'=><<<'EDHTML'
<!-- CATEGORIES -->

    <section class="section" style="background:var(--surface);border-block:1px solid var(--line);">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:46px;">
          <span class="eyebrow">{{text}}</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">{{title}}</h2>
        </div>
        <div class="catgroup reveal">
          <div class="catgroup__h"><h3>{{title_2}}</h3><span>{{text_2}}</span></div>
          <div class="catgrid">
            <a class="cat" href="{{group_1_button_url}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m18 2 4 4M17 7 7 17l-4 1 1-4L14 4z"/><path d="m13 5 6 6"/></svg></span><b>{{group_1_text}}</b></a>
            <a class="cat" href="{{group_2_button_url}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="9" y="2" width="6" height="6" rx="1"/><path d="M12 8v8M9 12h6M10 16h4l-2 6z"/></svg></span><b>{{group_2_text}}</b></a>
            <a class="cat" href="{{group_3_button_url}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 21a5 5 0 0 1-5-5c0-2 1-3 3-5l7-7 4 4-7 7c-2 2-3 3-2 6z"/></svg></span><b>{{group_3_text}}</b></a>
            <a class="cat" href="{{group_4_button_url}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 21 21 3M14 4l6 6M16 8l-9 9"/></svg></span><b>{{group_4_text}}</b></a>
            <a class="cat" href="{{group_5_button_url}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2"/></svg></span><b>{{group_5_text}}</b></a>
          </div>
        </div>
        <div class="catgroup reveal d1">
          <div class="catgroup__h"><h3>{{title_3}}</h3><span>{{text_3}}</span></div>
          <div class="catgrid">
            <a class="cat" href="{{group_1_button_url_2}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 2h10l-1 6a4 4 0 0 1-8 0z"/><path d="M9 14h6v6a3 3 0 0 1-6 0z"/></svg></span><b>{{group_1_text_2}}</b></a>
            <a class="cat" href="{{group_2_button_url_2}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v4M5 8l3 2M19 8l-3 2"/><circle cx="12" cy="15" r="6"/></svg></span><b>{{group_2_text_2}}</b></a>
            <a class="cat" href="{{group_3_button_url_2}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="7" y="9" width="10" height="12" rx="2"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg></span><b>{{group_3_text_2}}</b></a>
            <a class="cat" href="{{group_4_button_url_2}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/></svg></span><b>{{group_4_text_2}}</b></a>
            <a class="cat" href="{{group_5_button_url_2}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><circle cx="9" cy="10" r="1"/><circle cx="15" cy="10" r="1"/><path d="M9 15c1 1 5 1 6 0"/></svg></span><b>{{group_5_text_2}}</b></a>
            <a class="cat" href="{{group_6_button_url}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20 20 4M8 4H4v4M16 20h4v-4"/><circle cx="7" cy="7" r="1"/><circle cx="17" cy="17" r="1"/></svg></span><b>{{group_6_text}}</b></a>
          </div>
        </div>
        <div class="catgroup reveal d2">
          <div class="catgroup__h"><h3>{{title_4}}</h3><span>{{text_4}}</span></div>
          <div class="catgrid">
            <a class="cat" href="{{group_1_button_url_3}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6c6 2 10 4 16 12M6 4c5 6 9 10 14 16"/></svg></span><b>{{group_1_text_3}}</b></a>
            <a class="cat" href="{{group_2_button_url_3}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3a9 9 0 1 0 9 9"/><path d="M12 7v5l3 2"/></svg></span><b>{{group_2_text_3}}</b></a>
            <a class="cat" href="{{group_3_button_url_3}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 3h6v4l4 14H5l4-14z"/><path d="M8 13h8"/></svg></span><b>{{group_3_text_3}}</b></a>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_4_button_url": {"type": "text", "label": "Group 4 Button Url"}, "group_5_button_url": {"type": "text", "label": "Group 5 Button Url"}, "group_1_button_url_2": {"type": "text", "label": "Group 1 Button Url 2"}, "group_2_button_url_2": {"type": "text", "label": "Group 2 Button Url 2"}, "group_3_button_url_2": {"type": "text", "label": "Group 3 Button Url 2"}, "group_4_button_url_2": {"type": "text", "label": "Group 4 Button Url 2"}, "group_5_button_url_2": {"type": "text", "label": "Group 5 Button Url 2"}, "group_6_button_url": {"type": "text", "label": "Group 6 Button Url"}, "group_1_button_url_3": {"type": "text", "label": "Group 1 Button Url 3"}, "group_2_button_url_3": {"type": "text", "label": "Group 2 Button Url 3"}, "group_3_button_url_3": {"type": "text", "label": "Group 3 Button Url 3"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "title_2": {"type": "text", "label": "Title 2"}, "text_2": {"type": "textarea", "label": "Text 2"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_5_text": {"type": "textarea", "label": "Group 5 Text"}, "title_3": {"type": "text", "label": "Title 3"}, "text_3": {"type": "textarea", "label": "Text 3"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_4_text_2": {"type": "textarea", "label": "Group 4 Text 2"}, "group_5_text_2": {"type": "textarea", "label": "Group 5 Text 2"}, "group_6_text": {"type": "textarea", "label": "Group 6 Text"}, "title_4": {"type": "text", "label": "Title 4"}, "text_4": {"type": "textarea", "label": "Text 4"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_3_text_3": {"type": "textarea", "label": "Group 3 Text 3"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "urunler.html", "group_2_button_url": "urunler.html", "group_3_button_url": "urunler.html", "group_4_button_url": "urunler.html", "group_5_button_url": "urunler.html", "group_1_button_url_2": "urunler.html", "group_2_button_url_2": "urunler.html", "group_3_button_url_2": "urunler.html", "group_4_button_url_2": "urunler.html", "group_5_button_url_2": "urunler.html", "group_6_button_url": "urunler.html", "group_1_button_url_3": "urunler.html", "group_2_button_url_3": "urunler.html", "group_3_button_url_3": "urunler.html", "text": "Ürün Kategorileri", "title": "14 kategoride, kliniğinizin ihtiyaç duyduğu her şey", "title_2": "İnjeksiyon & Mezoterapi", "text_2": "5 kategori", "group_1_text": "Mezoterapi", "group_2_text": "Mezoterapi Tabancası", "group_3_text": "RRS", "group_4_text": "Kanül & İğne Ucu", "group_5_text": "Otolog Rejeneratif Terapi", "title_3": "Cilt Bakımı & Peeling", "text_3": "6 kategori", "group_1_text_2": "Kimyasal Peeling", "group_2_text_2": "Peeling", "group_3_text_2": "Kremler", "group_4_text_2": "Kozmetik", "group_5_text_2": "Yüz Maskesi", "group_6_text": "Micro İğneleme", "title_4": "Cihaz & Profesyonel Sarf", "text_4": "3 kategori", "group_1_text_3": "İp", "group_2_text_3": "Terapi", "group_3_text_3": "Profesyonel Ürünler"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-home-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'REPRESENTED BRANDS',
             'html_template'=><<<'EDHTML'
<!-- REPRESENTED BRANDS -->

    <section class="section" id="markalar">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:44px;">
          <span class="eyebrow">{{text}}</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">{{title}}</h2>
        </div>
        <div class="brandgrid">
          {{{brandc_items_html}}}<div class="brandc reveal d2"><div class="brandc__origin">{{text_2}}</div><div class="brandc__logo">{{text_3}}</div><p>{{subtitle}}</p></div>
          </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"brandc_items": {"type": "repeater", "label": "Brandc Items", "repeat_kind": "items", "item_template": "<a class=\"brandc reveal\" href=\"{{button_url}}\"><div class=\"brandc__origin\">{{text}}</div><div class=\"brandc__logo\">{{text_2}}</div><p>{{description}}</p></a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "description": {"type": "textarea", "label": "Description"}}}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "subtitle": {"type": "textarea", "label": "Subtitle"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"brandc_items": [{"button_url": "marka/skintech.html", "text": "İspanya", "text_2": "Skin Tech Pharma Group", "description": "Kimyasal peeling, mezoterapi ve RRS skinbooster serisinde dünya lideri."}, {"button_url": "marka/mi-medical.html", "text": "Premium", "text_2": "MI-Medical Innovation", "subtitle": "Premium mezoterapi ve enjeksiyon sistemleri."}, {"button_url": "marka/seffiline.html", "text": "Bakım & Dolgu", "text_2": "Seffiline", "subtitle": "Cilt, saç, intim bakım ve dolgu çözümleri serisi."}, {"button_url": "marka/woorhi.html", "text": "Güney Kore", "text_2": "Woorhi Mechatronics", "subtitle": "Kore mühendisliğiyle geliştirilen medikal estetik cihazları."}, {"button_url": "marka/aespio.html", "text": "K-Beauty", "text_2": "Grand Aespio", "subtitle": "Yüz maskeleri ve ip askı (thread lift) ürünleri."}], "text": "Temsil Ettiğimiz Markalar", "title": "Dünyanın önde gelen markalarıyla çalışıyoruz", "text_2": "Portföy", "text_3": "Neogenesis", "subtitle": "Rejeneratif cilt bakımı çözümleri."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-home-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'FOR DOCTORS',
             'html_template'=><<<'EDHTML'
<!-- FOR DOCTORS -->

    <section class="section" id="doktorlar">
      <div class="wrap">
        <div class="docs reveal">
          <div class="docs__grid" aria-hidden="true"></div>
          <span class="eyebrow" style="color:#F4A14E;">{{text}}</span>
          <h2 style="margin-top:18px;max-width:660px;">{{title}}</h2>
          <p class="lead" style="max-width:600px;margin-top:14px;">{{{body_html}}}</p>
          <div class="docs__cards">
            <div class="docc"><div class="docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div><h4>{{group_1_eyebrow}}</h4><p>{{group_1_description}}</p></div>
            <div class="docc"><div class="docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M8 21h8M12 17v4M3 4h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg></div><h4>{{group_2_eyebrow}}</h4><p>{{group_2_subtitle}}</p></div>
            <div class="docc"><div class="docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 14h2"/></svg></div><h4>{{group_3_eyebrow}}</h4><p>{{group_3_description}}</p></div>
          </div>
          <a class="btn btn--wa" href="{{button_url}}">{{button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "group_1_eyebrow": {"type": "text", "label": "Group 1 Eyebrow"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_eyebrow": {"type": "text", "label": "Group 2 Eyebrow"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}, "group_3_eyebrow": {"type": "text", "label": "Group 3 Eyebrow"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "etkinlikler.html", "text": "Doktorlar İçin", "title": "Sadece tedarik değil, uçtan uca profesyonel destek", "body_html": "Ürünlerin doğru ve güvenli kullanımı için uygulamalı eğitim, ulusal/uluslararası kongreler ve MEDINET dijital platformuyla yanınızdayız.", "group_1_eyebrow": "Uygulamalı Eğitim", "group_1_description": "Hekimlere birebir, uygulamalı ürün ve teknik kullanım eğitimleri.", "group_2_eyebrow": "Kongre & Etkinlik", "group_2_subtitle": "Sektörün önde gelen kongre ve fuarlarında aktif katılım.", "group_3_eyebrow": "MEDINET Platformu", "group_3_description": "Dijital sipariş, takip ve bilgi erişimi için MEDINET portalı.", "button_text": "Eğitim ve etkinliklerimizi inceleyin"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-home-6'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'ABOUT TEASER',
             'html_template'=><<<'EDHTML'
<!-- ABOUT TEASER -->

    <section class="section" id="hakkimizda">
      <div class="wrap about">
        <div class="about__media reveal">
          <!-- 🖼️ GÖRSEL: assets/img/v2-about.jpg (oran 4:3) — kurumsal/lab ortam, profesyonel -->
          <div class="about__media" style="background-image:url('{{background_image_url}}');background-size:cover;background-position:center;border:0;"></div>
        </div>
        <div class="reveal d1">
          <span class="eyebrow">{{text}}</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin:18px 0 16px;">{{title}}</h2>
          <p class="lead">{{{body_html}}}</p>
          <a class="link-arrow" href="{{button_url}}" style="margin-top:22px;">{{button_text}} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"background_image_url": {"type": "image", "label": "Background Image Url"}, "button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"background_image_url": "assets/img/v2-about.jpg", "button_url": "hakkimizda.html", "text": "Biz Kimiz", "title": "2004'ten bugüne, medikal estetikte güvenin temsilcisi", "body_html": "Estetik Dermal, medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline dağıtıyor. Uluslararası markaların resmi temsilcisi olarak, yalnızca ürün değil; doktorlara uygulamalı eğitim ve teknik destek de sunuyoruz.", "button_text": "Hikayemizi okuyun"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-home-7'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CONTACT',
             'html_template'=><<<'EDHTML'
<!-- CONTACT -->

    <section class="section" id="iletisim" style="background:var(--surface);border-top:1px solid var(--line);">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:40px;">
          <span class="eyebrow">{{text}}</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">{{title}}</h2>
        </div>
        <div class="contact">
          <form class="reveal" action="#" method="post" novalidate>
            <p style="font-size:12.5px;color:var(--muted);margin:0 0 16px;">{{description}}</p>
            <div class="field"><label for="ad">{{group_1_text}}</label><input id="ad" name="ad" type="text" autocomplete="name"></div>
            <div class="field"><label for="kurum">{{group_2_text}}</label><input id="kurum" name="kurum" type="text" autocomplete="organization"></div>
            <div class="field"><label for="tel">{{group_3_text}}</label><input id="tel" name="tel" type="tel" autocomplete="tel"></div>
            <div class="field"><label for="mesaj">{{group_4_text}}</label><textarea id="mesaj" name="mesaj" rows="4"></textarea></div>
            <button class="btn btn--primary" type="submit" style="width:100%;justify-content:center;">{{button_text}}</button>
          </form>
          <div class="contact__info reveal d1">
            <a class="info-row" href="{{group_1_button_url}}" target="_blank" rel="noopener"><span class="ic"><svg viewBox="0 0 32 32" fill="currentColor"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4z"/></svg></span><div><small>{{group_1_text_2}}</small><b>{{group_1_text_3}}</b></div></a>
            <a class="info-row" href="{{group_2_button_url}}"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg></span><div><small>{{group_2_text_2}}</small><b>{{group_2_text_3}}</b></div></a>
            <a class="info-row" href="{{group_3_button_url}}"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg></span><div><small>{{group_3_text_2}}</small><b>{{group_3_text_3}}</b></div></a>
            <div class="info-row"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg></span><div><small>{{text_2}}</small><b style="font-weight:500;font-size:14px;">{{text_3}}</b></div></div>
            <div class="contact__map"><iframe title="Estetik Dermal — Kuşadası konumu" src="{{media_url}}" loading="lazy"></iframe></div>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "media_url": {"type": "image", "label": "Media Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "button_text": {"type": "textarea", "label": "Button Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_3_text_3": {"type": "textarea", "label": "Group 3 Text 3"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "https://wa.me/905426205100", "group_2_button_url": "tel:+902566121813", "group_3_button_url": "mailto:info@estetikdermal.com", "media_url": "https://maps.google.com/maps?q=Kusadasi%20Aydin&t=&z=12&ie=UTF8&iwloc=&output=embed", "text": "İletişim", "title": "Ürün, fiyat ve eğitim için bize ulaşın", "description": "Form yalnızca yapı amaçlıdır; CMS'te /api/v1/forms/.../submit ile bağlanır.", "group_1_text": "Ad Soyad", "group_2_text": "Klinik / Kurum", "group_3_text": "Telefon", "group_4_text": "Mesaj", "button_text": "Gönder", "group_1_text_2": "WhatsApp", "group_1_text_3": "+90 542 620 51 00", "group_2_text_2": "Telefon", "group_2_text_3": "0 256 612 18 13", "group_3_text_2": "E-posta", "group_3_text_3": "info@estetikdermal.com", "text_2": "Adres", "text_3": "Türkmen Mah. Turgut Özel Bulvarı, Ada Modern A Blok No 83/3A · Kuşadası / Aydın"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-hakkimizda-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PAGE HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><!-- PAGE HERO -->

    <section class="phero">
      <div class="wrap">
        <nav class="crumb" aria-label="Breadcrumb">
          <a href="{{button_url}}">{{button_text}}</a> <span aria-hidden="true">{{text}}</span> {{text_2}}
        </nav>
        <h1 class="reveal">{{title}}</h1>
        <p class="lead reveal d1">{{{body_html}}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_text": {"type": "textarea", "label": "Button Text"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap", "button_url": "/", "button_text": "Ana Sayfa", "text": "/", "text_2": "Hakkımızda", "title": "Hakkımızda", "body_html": "2004'ten bu yana medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline ulaştıran resmi distribütör."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-hakkimizda-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'ABOUT STORY (split)',
             'html_template'=><<<'EDHTML'
<!-- ABOUT STORY (split) -->

    <section class="section">
      <div class="wrap">
        <div class="feature">
          <div class="feature__media reveal">
            <!-- 🖼️ GÖRSEL: assets/img/v2-about.jpg (oran 5:4) — kurumsal/showroom, ürün tanıtımı, klinik lüks -->
            <div class="feature__img" style="background-image:url('{{background_image_url}}')"></div>
          </div>
          <div class="reveal d1">
            <span class="eyebrow">{{text}}</span>
            <h2>{{title}}</h2>
            <p class="lead">{{{body_html}}}</p>
            <ul style="list-style:none;margin:24px 0 0;padding:0;display:grid;gap:12px;">
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;"><span style="color:var(--accent);font-size:18px;">{{group_1_text}}</span> {{group_1_item_text}}</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;"><span style="color:var(--accent);font-size:18px;">{{group_2_text}}</span> {{group_2_item_text}}</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;"><span style="color:var(--accent);font-size:18px;">{{group_3_text}}</span> {{group_3_item_text}}</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;"><span style="color:var(--accent);font-size:18px;">{{group_4_text}}</span> {{group_4_item_text}}</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"background_image_url": {"type": "image", "label": "Background Image Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_4_item_text": {"type": "textarea", "label": "Group 4 Item Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"background_image_url": "assets/img/v2-about.jpg", "text": "Biz Kimiz?", "title": "2004'ten beri medikal estetikte güvenin adresi", "body_html": "2004'ten bu yana medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline dağıtıyoruz. Uluslararası markaların resmi temsilcisi olarak yalnızca ürün değil, doktorlara uygulamalı eğitim ve teknik destek de sunuyoruz.", "group_1_text": "✓", "group_1_item_text": "Uluslararası markaların resmi Türkiye temsilcisi", "group_2_text": "✓", "group_2_item_text": "CE Class III sertifikalı RRS serisi", "group_3_text": "✓", "group_3_item_text": "Doktorlara birebir, uygulamalı eğitim", "group_4_text": "✓", "group_4_item_text": "Türkiye geneli, 81 ile dağıtım ağı"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-hakkimizda-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'VİZYON & MİSYON',
             'html_template'=><<<'EDHTML'
<!-- VİZYON & MİSYON -->

    <section class="section" style="background:var(--surface);border-block:1px solid var(--line);">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:46px;">
          <span class="eyebrow">{{text}}</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">{{title}}</h2>
        </div>
        <div class="brandgrid">
          <div class="brandc reveal">
            <div class="cat__ic" style="margin-bottom:18px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.2" fill="currentColor" stroke="none"/></svg></div>
            <h3 style="font-size:21px;font-weight:520;margin:0 0 12px;">{{title_2}}</h3>
            <p style="font-size:16px;color:var(--body);line-height:1.7;margin:0;">{{description}}</p>
          </div>
          <div class="brandc reveal d1">
            <div class="cat__ic" style="margin-bottom:18px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 15c-1.5 1.3-2 5-2 5s3.7-.5 5-2a2.6 2.6 0 0 0-3-3Z"/><path d="M9 13c2-5 5-8 11-8 0 6-3 9-8 11"/><path d="M9 13l-3-1a14 14 0 0 1 3-3l3 .5"/><path d="M11 15l1 3a14 14 0 0 0 3-3l-.5-3"/></svg></div>
            <h3 style="font-size:21px;font-weight:520;margin:0 0 12px;">{{title_3}}</h3>
            <p style="font-size:16px;color:var(--body);line-height:1.7;margin:0;">{{{body_html}}}</p>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "title_2": {"type": "text", "label": "Title 2"}, "description": {"type": "textarea", "label": "Description"}, "title_3": {"type": "text", "label": "Title 3"}, "body_html": {"type": "textarea", "label": "Body"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"text": "Vizyon & Misyon", "title": "Sektörün uzmanlık seviyesini yükseltmek için varız", "title_2": "Vizyonumuz", "description": "Medikal estetikte kalite ve güvenin simgesi olarak sektördeki uzmanlık seviyesini sürekli yükseltmek.", "title_3": "Misyonumuz", "body_html": "Hastaların cilt sağlığını güçlendirecek profesyonel çözümler sağlamak ve invazif olmayan gençleştirme yöntemlerinde öncü rol oynamak."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-hakkimizda-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'STATS BAND',
             'html_template'=><<<'EDHTML'
<!-- STATS BAND -->

    <section class="band">
      <div class="wrap stats">
        <div class="reveal"><div class="stat__n"><span>{{text}}</span><span class="accent">{{text_2}}</span></div><div class="stat__l">{{text_3}}</div></div>
        <div class="reveal d1"><div class="stat__n"><span>{{text_4}}</span></div><div class="stat__l">{{text_5}}</div></div>
        <div class="reveal d2"><div class="stat__n"><span>{{text_6}}</span></div><div class="stat__l">{{text_7}}</div></div>
        <div class="reveal d3"><div class="stat__n">{{text_8}} <span class="accent">{{text_9}}</span></div><div class="stat__l">{{text_10}}</div></div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "text_4": {"type": "textarea", "label": "Text 4"}, "text_5": {"type": "textarea", "label": "Text 5"}, "text_6": {"type": "textarea", "label": "Text 6"}, "text_7": {"type": "textarea", "label": "Text 7"}, "text_8": {"type": "textarea", "label": "Text 8"}, "text_9": {"type": "textarea", "label": "Text 9"}, "text_10": {"type": "textarea", "label": "Text 10"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"text": "20", "text_2": "+", "text_3": "Yıllık deneyim", "text_4": "6", "text_5": "Uluslararası marka temsilciliği", "text_6": "14", "text_7": "Ürün kategorisi", "text_8": "CE", "text_9": "III", "text_10": "Sertifikalı portföy"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-hakkimizda-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PORTFOLIO BRANDS',
             'html_template'=><<<'EDHTML'
<!-- PORTFOLIO BRANDS -->

    <section class="section">
      <div class="wrap">
        <div class="reveal" style="max-width:640px;margin-bottom:44px;">
          <span class="eyebrow">{{text}}</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">{{title}}</h2>
          <p class="lead" style="margin-top:14px;">{{description}}</p>
        </div>
        <div class="brandgrid">
          {{{brandc_items_html}}}<div class="brandc reveal d2"><div class="brandc__origin">{{text_2}}</div><div class="brandc__logo">{{text_3}}</div><p>{{subtitle}}</p></div>
          </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"brandc_items": {"type": "repeater", "label": "Brandc Items", "repeat_kind": "items", "item_template": "<a class=\"brandc reveal\" href=\"{{button_url}}\"><div class=\"brandc__origin\">{{text}}</div><div class=\"brandc__logo\">{{text_2}}</div><p>{{description}}</p></a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "description": {"type": "textarea", "label": "Description"}}}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "subtitle": {"type": "textarea", "label": "Subtitle"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"brandc_items": [{"button_url": "/marka-skintech", "text": "İspanya", "text_2": "Skin Tech Pharma Group", "description": "Kimyasal peeling, mezoterapi ve RRS skinbooster serisinde dünya lideri."}, {"button_url": "/marka-mi-medical", "text": "Premium", "text_2": "MI-Medical Innovation", "subtitle": "Premium mezoterapi ve enjeksiyon sistemleri."}, {"button_url": "/marka-seffiline", "text": "Cilt & Saç", "text_2": "Seffiline", "subtitle": "Cilt, saç, intim bakım ve dolgu çözümleri serisi."}, {"button_url": "/marka-woorhi", "text": "Güney Kore", "text_2": "Woorhi Mechatronics", "subtitle": "Kore mühendisliğiyle geliştirilen medikal estetik cihazları."}, {"button_url": "/marka-aespio", "text": "K-Beauty", "text_2": "Grand Aespio", "subtitle": "Yüz maskeleri ve ip askı (thread lift) ürünleri."}], "text": "Portföyümüz", "title": "Temsil ettiğimiz uluslararası markalar", "description": "Her biri kendi alanında uzman; mezoterapiden cihaza, peelingden ip askıya geniş bir ürün yelpazesi.", "text_2": "Kök Hücre", "text_3": "Neogenesis", "subtitle": "Kök hücre teknolojili profesyonel cilt bakım serisi."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-hakkimizda-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'FOR DOCTORS / TRAINING',
             'html_template'=><<<'EDHTML'
<!-- FOR DOCTORS / TRAINING -->

    <section class="section" style="background:var(--surface);border-top:1px solid var(--line);">
      <div class="wrap">
        <div class="docs reveal">
          <div class="docs__grid" aria-hidden="true"></div>
          <span class="eyebrow" style="color:#F4A14E;">{{text}}</span>
          <h2 style="margin-top:18px;max-width:660px;">{{title}}</h2>
          <p class="lead" style="max-width:600px;margin-top:14px;">{{{body_html}}}</p>
          <div class="docs__cards">
            <div class="docc"><div class="docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div><h4>{{group_1_eyebrow}}</h4><p>{{group_1_description}}</p></div>
            <div class="docc"><div class="docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 14v-1a8 8 0 0 1 16 0v1"/><path d="M4 14a2 2 0 0 1 2-2h1v6H6a2 2 0 0 1-2-2Zm16 0a2 2 0 0 0-2-2h-1v6h1a2 2 0 0 0 2-2Z"/><path d="M18 18v.5a2.5 2.5 0 0 1-2.5 2.5H12"/></svg></div><h4>{{group_2_eyebrow}}</h4><p>{{group_2_description}}</p></div>
            <div class="docc"><div class="docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3 5 6v5c0 4 3 7 7 8 4-1 7-4 7-8V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg></div><h4>{{group_3_eyebrow}}</h4><p>{{group_3_description}}</p></div>
          </div>
          <a class="btn btn--wa" href="{{button_url}}">{{button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "group_1_eyebrow": {"type": "text", "label": "Group 1 Eyebrow"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_eyebrow": {"type": "text", "label": "Group 2 Eyebrow"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_3_eyebrow": {"type": "text", "label": "Group 3 Eyebrow"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "/etkinlikler", "text": "Neden Estetik Dermal?", "title": "Sadece ürün değil, uçtan uca profesyonel destek", "body_html": "Ürünlerin doğru ve güvenli kullanımı için uygulamalı eğitim, kesintisiz teknik danışmanlık ve orijinallik güvencesiyle hekimlerin yanındayız.", "group_1_eyebrow": "Uygulamalı Eğitim", "group_1_description": "Hekimlere ürünlerin doğru ve güvenli kullanımı için birebir, uygulamalı eğitim programları.", "group_2_eyebrow": "Teknik Destek", "group_2_description": "Cihaz kurulumundan kullanım sürecine kadar kesintisiz teknik danışmanlık ve servis.", "group_3_eyebrow": "Orijinallik Garantisi", "group_3_description": "Resmi temsilci güvencesiyle %100 orijinal, sertifikalı ve takip edilebilir ürünler.", "button_text": "Eğitim ve etkinliklerimizi inceleyin"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-hakkimizda-6'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CTA / CONTACT',
             'html_template'=><<<'EDHTML'
<!-- CTA / CONTACT -->

    <section class="section">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:40px;">
          <span class="eyebrow">{{text}}</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">{{title}}</h2>
          <p class="lead" style="margin-top:14px;">{{description}}</p>
        </div>
        <div class="hero__cta reveal d1">
          <a class="btn btn--ghost" href="{{group_1_button_url}}" target="_blank" rel="noopener">
            <svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 4 1.7 4 1.2 4.7 1.1.7-.1 2.4-1 2.7-1.9.3-1 .3-1.8.2-1.9z"/></svg>
            {{group_1_button_text}}</a>
          <a class="btn btn--ghost" href="{{group_2_button_url}}" target="_blank" rel="noopener">{{group_2_button_text}}</a>
          <a class="btn btn--primary" href="{{button_url}}">{{button_text}}</a>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "https://wa.me/905426205100", "group_2_button_url": "https://apps.skintechpharmagroup.com/medinet", "button_url": "/iletisim", "text": "Bize Ulaşın", "title": "Profesyonel çözümler için buradayız", "description": "Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.", "group_1_button_text": "WhatsApp ile Yaz", "group_2_button_text": "MEDINET Portalı", "button_text": "İletişime Geç"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-urunler-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PAGE HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><!-- PAGE HERO -->

    <section class="phero">
      <div class="wrap">
        <nav class="crumb" aria-label="Breadcrumb">
          <a href="{{button_url}}">{{button_text}}</a> <span aria-hidden="true">{{text}}</span> {{text_2}}
        </nav>
        <h1 class="reveal">{{title}}</h1>
        <p class="lead reveal d1">{{description}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_text": {"type": "textarea", "label": "Button Text"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap", "button_url": "/", "button_text": "Ana Sayfa", "text": "/", "text_2": "Ürünler", "title": "Ürünler", "description": "95+ profesyonel medikal estetik ürünü, 14 kategoride — uluslararası markaların resmi distribütör kataloğu."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-urunler-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'FILTER CHIPS (statik)',
             'html_template'=><<<'EDHTML'
<!-- FILTER CHIPS (statik) -->

    <section style="background:var(--surface);border-bottom:1px solid var(--line);">
      <div class="wrap" style="display:flex;flex-wrap:wrap;gap:10px;padding:24px 0;" aria-label="Marka filtresi">
        <a class="btn btn--primary" href="{{button_url}}" style="padding:10px 22px;font-size:14px;">{{button_text}}</a>
        {{{btn_items_html}}}</div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"btn_items": {"type": "repeater", "label": "Btn Items", "repeat_kind": "items", "item_template": "<a class=\"btn btn--ghost\" href=\"{{button_url}}\" style=\"padding:10px 22px;font-size:14px;\">{{button_text}}</a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "button_text": {"type": "textarea", "label": "Button Text"}}}, "button_url": {"type": "text", "label": "Button Url"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"btn_items": [{"button_url": "/urunler", "button_text": "Skin Tech"}, {"button_url": "/urunler", "button_text": "Seffiline"}, {"button_url": "/urunler", "button_text": "Grand Aespio"}, {"button_url": "/urunler", "button_text": "Woorhi"}, {"button_url": "/urunler", "button_text": "Mi Medical"}], "button_url": "/urunler", "button_text": "Tümü"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-urunler-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CATEGORY GROUPS',
             'html_template'=><<<'EDHTML'
<!-- CATEGORY GROUPS -->

    <section class="section" style="background:var(--surface);border-bottom:1px solid var(--line);">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:46px;">
          <span class="eyebrow">{{text}}</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">{{title}}</h2>
        </div>
        <div class="catgroup reveal">
          <div class="catgroup__h"><h3>{{title_2}}</h3><span>{{text_2}}</span></div>
          <div class="catgrid">
            <a class="cat" href="{{group_1_button_url}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m18 2 4 4M17 7 7 17l-4 1 1-4L14 4z"/><path d="m13 5 6 6"/></svg></span><b>{{group_1_text}}</b></a>
            <a class="cat" href="{{group_2_button_url}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="9" y="2" width="6" height="6" rx="1"/><path d="M12 8v8M9 12h6M10 16h4l-2 6z"/></svg></span><b>{{group_2_text}}</b></a>
            <a class="cat" href="{{group_3_button_url}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 21a5 5 0 0 1-5-5c0-2 1-3 3-5l7-7 4 4-7 7c-2 2-3 3-2 6z"/></svg></span><b>{{group_3_text}}</b></a>
            <a class="cat" href="{{group_4_button_url}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 21 21 3M14 4l6 6M16 8l-9 9"/></svg></span><b>{{group_4_text}}</b></a>
            <a class="cat" href="{{group_5_button_url}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2"/></svg></span><b>{{group_5_text}}</b></a>
          </div>
        </div>
        <div class="catgroup reveal d1">
          <div class="catgroup__h"><h3>{{title_3}}</h3><span>{{text_3}}</span></div>
          <div class="catgrid">
            <a class="cat" href="{{group_1_button_url_2}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 2h10l-1 6a4 4 0 0 1-8 0z"/><path d="M9 14h6v6a3 3 0 0 1-6 0z"/></svg></span><b>{{group_1_text_2}}</b></a>
            <a class="cat" href="{{group_2_button_url_2}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v4M5 8l3 2M19 8l-3 2"/><circle cx="12" cy="15" r="6"/></svg></span><b>{{group_2_text_2}}</b></a>
            <a class="cat" href="{{group_3_button_url_2}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="7" y="9" width="10" height="12" rx="2"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg></span><b>{{group_3_text_2}}</b></a>
            <a class="cat" href="{{group_4_button_url_2}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/></svg></span><b>{{group_4_text_2}}</b></a>
            <a class="cat" href="{{group_5_button_url_2}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><circle cx="9" cy="10" r="1"/><circle cx="15" cy="10" r="1"/><path d="M9 15c1 1 5 1 6 0"/></svg></span><b>{{group_5_text_2}}</b></a>
            <a class="cat" href="{{group_6_button_url}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20 20 4M8 4H4v4M16 20h4v-4"/><circle cx="7" cy="7" r="1"/><circle cx="17" cy="17" r="1"/></svg></span><b>{{group_6_text}}</b></a>
          </div>
        </div>
        <div class="catgroup reveal d2">
          <div class="catgroup__h"><h3>{{title_4}}</h3><span>{{text_4}}</span></div>
          <div class="catgrid">
            <a class="cat" href="{{group_1_button_url_3}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6c6 2 10 4 16 12M6 4c5 6 9 10 14 16"/></svg></span><b>{{group_1_text_3}}</b></a>
            <a class="cat" href="{{group_2_button_url_3}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3a9 9 0 1 0 9 9"/><path d="M12 7v5l3 2"/></svg></span><b>{{group_2_text_3}}</b></a>
            <a class="cat" href="{{group_3_button_url_3}}"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 3h6v4l4 14H5l4-14z"/><path d="M8 13h8"/></svg></span><b>{{group_3_text_3}}</b></a>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_4_button_url": {"type": "text", "label": "Group 4 Button Url"}, "group_5_button_url": {"type": "text", "label": "Group 5 Button Url"}, "group_1_button_url_2": {"type": "text", "label": "Group 1 Button Url 2"}, "group_2_button_url_2": {"type": "text", "label": "Group 2 Button Url 2"}, "group_3_button_url_2": {"type": "text", "label": "Group 3 Button Url 2"}, "group_4_button_url_2": {"type": "text", "label": "Group 4 Button Url 2"}, "group_5_button_url_2": {"type": "text", "label": "Group 5 Button Url 2"}, "group_6_button_url": {"type": "text", "label": "Group 6 Button Url"}, "group_1_button_url_3": {"type": "text", "label": "Group 1 Button Url 3"}, "group_2_button_url_3": {"type": "text", "label": "Group 2 Button Url 3"}, "group_3_button_url_3": {"type": "text", "label": "Group 3 Button Url 3"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "title_2": {"type": "text", "label": "Title 2"}, "text_2": {"type": "textarea", "label": "Text 2"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_5_text": {"type": "textarea", "label": "Group 5 Text"}, "title_3": {"type": "text", "label": "Title 3"}, "text_3": {"type": "textarea", "label": "Text 3"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_4_text_2": {"type": "textarea", "label": "Group 4 Text 2"}, "group_5_text_2": {"type": "textarea", "label": "Group 5 Text 2"}, "group_6_text": {"type": "textarea", "label": "Group 6 Text"}, "title_4": {"type": "text", "label": "Title 4"}, "text_4": {"type": "textarea", "label": "Text 4"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_3_text_3": {"type": "textarea", "label": "Group 3 Text 3"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "/urunler", "group_2_button_url": "/urunler", "group_3_button_url": "/urunler", "group_4_button_url": "/urunler", "group_5_button_url": "/urunler", "group_1_button_url_2": "/urunler", "group_2_button_url_2": "/urunler", "group_3_button_url_2": "/urunler", "group_4_button_url_2": "/urunler", "group_5_button_url_2": "/urunler", "group_6_button_url": "/urunler", "group_1_button_url_3": "/urunler", "group_2_button_url_3": "/urunler", "group_3_button_url_3": "/urunler", "text": "Ürün Kategorileri", "title": "İhtiyacınız olan her şey, 14 kategoride", "title_2": "İnjeksiyon & Mezoterapi", "text_2": "5 kategori", "group_1_text": "Mezoterapi", "group_2_text": "Mezoterapi Tabancası", "group_3_text": "RRS", "group_4_text": "Kanül & İğne Ucu", "group_5_text": "Otolog Rejeneratif Terapi", "title_3": "Cilt Bakımı & Peeling", "text_3": "6 kategori", "group_1_text_2": "Kimyasal Peeling", "group_2_text_2": "Peeling", "group_3_text_2": "Kremler", "group_4_text_2": "Kozmetik", "group_5_text_2": "Yüz Maskesi", "group_6_text": "Micro İğneleme", "title_4": "Cihaz & Profesyonel Sarf", "text_4": "3 kategori", "group_1_text_3": "İp", "group_2_text_3": "Terapi", "group_3_text_3": "Profesyonel Ürünler"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-urunler-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PRODUCT GRID',
             'html_template'=><<<'EDHTML'
<!-- PRODUCT GRID -->

    <section class="section">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:44px;">
          <span class="eyebrow">{{text}}</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">{{title}}</h2>
        </div>
        <div class="brandgrid">

          <!-- ════════════════════════════════════════════════════════════════════
               🖼️ ÜRÜN KART GÖRSELLERİ — image-ready (oran 5:4)
               İsimlendirme: assets/img/product-{slug}.jpg
               v2.css .feature__img gradyan fallback'i kullanılır; görsel yoksa nötr kalır.
          ════════════════════════════════════════════════════════════════════ -->

          <!-- Skin Tech -->
          {{{brandc_items_html}}}<!-- Grand Aespio -->
          <!-- Seffiline -->
          <!-- Woorhi -->
          <!-- Mi Medical -->
          </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"brandc_items": {"type": "repeater", "label": "Brandc Items", "repeat_kind": "items", "item_template": "<a class=\"brandc reveal\" href=\"{{button_url}}\" style=\"padding:0;overflow:hidden;\">\n            <div class=\"feature__img\" style=\"aspect-ratio:5/4;background-image:url('{{background_image_url}}');\"></div>\n            <div style=\"padding:24px 26px;\">\n              <div class=\"brandc__origin\">{{text}}</div>\n              <div class=\"brandc__logo\" style=\"font-size:18px;\">{{text_2}}</div>\n              <p>{{subtitle}}</p>\n            </div>\n          </a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "background_image_url": {"type": "image", "label": "Background Image Url"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "subtitle": {"type": "textarea", "label": "Subtitle"}}}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"brandc_items": [{"button_url": "/urun-detay", "background_image_url": "assets/img/product-rrs-ha-long-lasting.jpg", "text": "Skin Tech · RRS", "text_2": "RRS® HA Long Lasting", "subtitle": "Çapraz bağlı HA içeren CE Class III dermal implant."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-melablock-hsp-spf-50.jpg", "text": "Skin Tech · Krem", "text_2": "Melablock HSP SPF 50+", "description": "Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktör."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-benebellum-lumina-vit-c-18.jpg", "text": "Skin Tech · Mezoterapi", "text_2": "Benebellum LUMINA VİT-C 18%", "subtitle": "Yüksek konsantrasyonlu C vitamini ile aydınlatıcı bakım."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-benebellum-lumina-vit-a-e.jpg", "text": "Skin Tech · Mezoterapi", "text_2": "Benebellum LUMINA VİT. A+E", "subtitle": "A ve E vitamini ile besleyici, antioksidan bakım."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-benebellum-tx-solution.jpg", "text": "Skin Tech · Mezoterapi", "text_2": "Benebellum TX SOLUTION", "subtitle": "Traneksamik asit içeren leke karşıtı aydınlatıcı solüsyon."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-aclaranse.jpg", "text": "Skin Tech · Peeling", "text_2": "Aclaranse", "subtitle": "Lekeli ciltler için aydınlatıcı profesyonel peeling çözümü."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-actilift.jpg", "text": "Skin Tech · İp", "text_2": "Actilift", "subtitle": "Yüz ve boyunda anlık toparlama için ip askı sistemi."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-atrofillin.jpg", "text": "Skin Tech · Mezoterapi", "text_2": "Atrofillin", "subtitle": "Atrofik izler ve cilt onarımı için mezoterapi solüsyonu."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-beta-glukan-mask.jpg", "text": "Grand Aespio · Yüz Maskesi", "text_2": "Beta-Glukan Mask", "subtitle": "Yatıştırıcı ve onarıcı beta-glukan içeren yüz maskesi."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-hyaluronic-acid-mask.jpg", "text": "Grand Aespio · Yüz Maskesi", "text_2": "Hyaluronic Acid Mask", "subtitle": "Yoğun nem ve dolgunluk veren hyalüronik asit maskesi."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-lfl-anchor.jpg", "text": "Grand Aespio · İp", "text_2": "LFL Anchor", "subtitle": "Güçlü tutuş sağlayan çapalı askı (anchor) ip serisi."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-seffihair.jpg", "text": "Seffiline · Mezoterapi", "text_2": "SeffiHair", "subtitle": "Saç dökülmesine karşı saçlı deri mezoterapi solüsyonu."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-sefficare.jpg", "text": "Seffiline · Kozmetik", "text_2": "SeffiCare", "subtitle": "Günlük cilt bakımı için kozmetik onarım serisi."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-raffine.jpg", "text": "Woorhi · Mezoterapi Tabancası", "text_2": "Raffine", "description": "Kore mühendisliğiyle geliştirilen profesyonel mezoterapi cihazı."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-pistor-eliance.jpg", "text": "Mi Medical · Mezoterapi Tabancası", "text_2": "Pistor Eliance", "subtitle": "Premium enjeksiyon sistemi."}], "text": "Tüm Ürünler", "title": "Profesyonel medikal estetik portföyü"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-urunler-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'INFO BAND + CTA',
             'html_template'=><<<'EDHTML'
<!-- INFO BAND + CTA -->

    <section class="section" style="background:var(--surface);border-top:1px solid var(--line);">
      <div class="wrap">
        <div class="info-row reveal" style="padding:26px 30px;flex-wrap:wrap;gap:18px;">
          <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m21 8-9-5-9 5v8l9 5 9-5V8Z"/><path d="m3 8 9 5 9-5"/><path d="M12 13v8"/></svg></span>
          <div style="flex:1 1 320px;"><b style="font-weight:600;">{{text}}</b><small>{{text_2}}</small></div>
          <a class="btn btn--ghost" href="{{button_url}}" target="_blank" rel="noopener" style="flex-shrink:0;">
            <svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9z"/></svg>
            {{button_text}}</a>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "text": "Toplam 95+ ürün", "text_2": "Fiyat ve sipariş için WhatsApp: +90 542 620 51 00", "button_text": "WhatsApp'tan Yaz"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-urun-detay-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PAGE HERO / BREADCRUMB',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><!-- PAGE HERO / BREADCRUMB -->

    <section class="phero">
      <div class="wrap">
        <nav class="crumb" aria-label="Breadcrumb">
          <a href="{{group_1_button_url}}">{{group_1_button_text}}</a> <span aria-hidden="true">{{group_1_text}}</span>
          <a href="{{group_2_button_url}}">{{group_2_button_text}}</a> <span aria-hidden="true">{{group_2_text}}</span>
          {{text}}
        </nav>
        <h1 class="reveal">{{title}}</h1>
        <p class="lead reveal d1">{{description}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap", "group_1_button_url": "/", "group_2_button_url": "/urunler", "group_1_button_text": "Ana Sayfa", "group_1_text": "/", "group_2_button_text": "Ürünler", "group_2_text": "/", "text": "RRS® HA Long Lasting", "title": "RRS® HA Long Lasting", "description": "Çapraz bağlı, emilebilir hyalüronik asit içeren CE Class III dermal implant — uzun etkili skinbooster."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-urun-detay-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PRODUCT DETAIL (split)',
             'html_template'=><<<'EDHTML'
<!-- PRODUCT DETAIL (split) -->

    <section class="section">
      <div class="wrap">
        <div class="feature">
          <div class="feature__media reveal">
            <!-- 🖼️ GÖRSEL: assets/img/product-rrs-ha-long-lasting.jpg (oran 5:4) — RRS HA macro ürün çekimi -->
            <div class="feature__img" style="background-image:url('{{background_image_url}}')"></div>
          </div>
          <div class="reveal d1">
            <span class="eyebrow">{{text}}</span>
            <h2>{{title}}</h2>
            <p class="lead">{{{body_html}}}</p>
            <p style="margin:18px 0 22px;"><span class="badge-ce">{{group_1_text}}</span></p>
            <ul style="list-style:none;margin:0 0 28px;padding:0;display:grid;gap:12px;">
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;font-size:15.5px;"><span style="color:var(--accent);font-size:18px;flex-shrink:0;">{{group_1_text_2}}</span> {{group_1_item_text}}</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;font-size:15.5px;"><span style="color:var(--accent);font-size:18px;flex-shrink:0;">{{group_2_text}}</span> {{group_2_item_text}}</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;font-size:15.5px;"><span style="color:var(--accent);font-size:18px;flex-shrink:0;">{{group_3_text}}</span> {{group_3_item_text}}</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;font-size:15.5px;"><span style="color:var(--accent);font-size:18px;flex-shrink:0;">{{group_4_text}}</span> {{group_4_item_text}}</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;font-size:15.5px;"><span style="color:var(--accent);font-size:18px;flex-shrink:0;">{{group_5_text}}</span> {{group_5_item_text}}</li>
            </ul>
            <div class="hero__cta">
              <a class="btn btn--ghost" href="{{button_url}}" target="_blank" rel="noopener">
                <svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9z"/></svg>
                {{button_text}}</a>
              <a class="btn btn--primary" href="{{button_url_2}}">{{button_text_2}}</a>
            </div>
            <p style="margin:22px 0 0;font-size:13.5px;color:var(--muted);display:flex;align-items:center;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.8" aria-hidden="true" style="flex-shrink:0;"><path d="M12 3 5 6v5c0 4 3 7 7 8 4-1 7-4 7-8V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg> {{group_2_subtitle}}</p>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"background_image_url": {"type": "image", "label": "Background Image Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_4_item_text": {"type": "textarea", "label": "Group 4 Item Text"}, "group_5_text": {"type": "textarea", "label": "Group 5 Text"}, "group_5_item_text": {"type": "textarea", "label": "Group 5 Item Text"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"background_image_url": "assets/img/product-rrs-ha-long-lasting.jpg", "button_url": "https://wa.me/905426205100", "button_url_2": "/iletisim", "text": "Skin Tech · RRS", "title": "RRS® HA Long Lasting", "body_html": "Çapraz bağlı, emilebilir hyalüronik asit içeren dermal implant. Amino asit içeren koruyucu tampon solüsyonunda; cilt kalitesini içten iyileştiren uzun etkili skinbooster.", "group_1_text": "● CE Class III · Tıbbi cihaz sınıfı", "group_1_text_2": "✓", "group_1_item_text": "CE Class III sertifikalı", "group_2_text": "✓", "group_2_item_text": "Steril tıbbi enjektör formu", "group_3_text": "✓", "group_3_item_text": "Amino asit içeren koruyucu tampon solüsyonu", "group_4_text": "✓", "group_4_item_text": "Uzun etkili (long lasting)", "group_5_text": "✓", "group_5_item_text": "Cilt gençleştirme & nemlendirme", "button_text": "WhatsApp ile Sipariş", "button_text_2": "Teklif İste", "group_2_subtitle": "Yalnızca hekim/klinik kullanımına yöneliktir."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-urun-detay-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PRODUCT DESCRIPTION (rich text)',
             'html_template'=><<<'EDHTML'
<!-- PRODUCT DESCRIPTION (rich text) -->

    <section class="section" style="background:var(--surface);border-block:1px solid var(--line);">
      <div class="wrap" style="max-width:860px;">
        <div class="reveal" style="margin-bottom:46px;">
          <span class="eyebrow">{{group_1_text}}</span>
          <h2 style="font-size:clamp(24px,3vw,32px);margin:18px 0 16px;">{{group_1_title}}</h2>
          <p style="color:var(--body);font-size:17px;line-height:1.8;margin:0 0 14px;">{{{group_1_body_html}}}</p>
          <p style="color:var(--body);font-size:17px;line-height:1.8;margin:0;">{{{group_2_body_html}}}</p>
        </div>

        <div class="reveal" style="margin-bottom:46px;">
          <span class="eyebrow">{{group_2_text}}</span>
          <h2 style="font-size:clamp(24px,3vw,32px);margin:18px 0 16px;">{{group_2_title}}</h2>
          <p style="color:var(--body);font-size:17px;line-height:1.8;margin:0 0 18px;">{{{group_2_body_html_2}}}</p>
          <div class="catgrid">
            <div class="cat"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M9 15c1 1 5 1 6 0"/><circle cx="9" cy="10" r="1"/><circle cx="15" cy="10" r="1"/></svg></span><b>{{group_1_text_2}}</b></div>
            <div class="cat"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M8 3v4a4 4 0 0 0 8 0V3M6 21a6 6 0 0 1 12 0"/></svg></span><b>{{group_2_text_2}}</b></div>
            <div class="cat"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 8h16M6 8V5h12v3M5 8l1 12h12l1-12"/></svg></span><b>{{group_3_text}}</b></div>
            <div class="cat"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 11V5a2 2 0 0 1 4 0v6M10 11V4a2 2 0 0 1 4 0v7M14 11V6a2 2 0 0 1 4 0v9a6 6 0 0 1-6 6H9a5 5 0 0 1-5-5l-1-3"/></svg></span><b>{{group_4_text}}</b></div>
          </div>
        </div>

        <div class="reveal">
          <span class="eyebrow">{{group_3_text_2}}</span>
          <h2 style="font-size:clamp(24px,3vw,32px);margin:18px 0 16px;">{{group_3_title}}</h2>
          <ul style="list-style:none;margin:0;padding:0;display:grid;gap:12px;">
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--body);font-size:16px;line-height:1.7;"><span style="color:var(--accent-ink);font-weight:800;flex-shrink:0;">{{group_1_text_3}}</span><span><strong style="color:var(--ink);">{{group_2_text_3}}</strong> {{group_2_text_4}}</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--body);font-size:16px;line-height:1.7;"><span style="color:var(--accent-ink);font-weight:800;flex-shrink:0;">{{group_1_text_4}}</span><span><strong style="color:var(--ink);">{{group_2_text_5}}</strong> {{group_2_text_6}}</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--body);font-size:16px;line-height:1.7;"><span style="color:var(--accent-ink);font-weight:800;flex-shrink:0;">{{group_1_text_5}}</span><span><strong style="color:var(--ink);">{{group_2_text_7}}</strong> {{group_2_text_8}}</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--body);font-size:16px;line-height:1.7;"><span style="color:var(--accent-ink);font-weight:800;flex-shrink:0;">{{group_1_text_6}}</span><span><strong style="color:var(--ink);">{{group_2_text_9}}</strong> {{group_2_text_10}}</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--body);font-size:16px;line-height:1.7;"><span style="color:var(--accent-ink);font-weight:800;flex-shrink:0;">{{group_1_text_7}}</span><span><strong style="color:var(--ink);">{{group_2_text_11}}</strong> {{group_2_text_12}}</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--body);font-size:16px;line-height:1.7;"><span style="color:var(--accent-ink);font-weight:800;flex-shrink:0;">{{group_1_text_8}}</span><span><strong style="color:var(--ink);">{{group_2_text_13}}</strong> {{group_2_text_14}}</span></li>
          </ul>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_body_html": {"type": "textarea", "label": "Group 1 Body"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_body_html_2": {"type": "textarea", "label": "Group 2 Body Html 2"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_2_text_5": {"type": "textarea", "label": "Group 2 Text 5"}, "group_2_text_6": {"type": "textarea", "label": "Group 2 Text 6"}, "group_1_text_5": {"type": "textarea", "label": "Group 1 Text 5"}, "group_2_text_7": {"type": "textarea", "label": "Group 2 Text 7"}, "group_2_text_8": {"type": "textarea", "label": "Group 2 Text 8"}, "group_1_text_6": {"type": "textarea", "label": "Group 1 Text 6"}, "group_2_text_9": {"type": "textarea", "label": "Group 2 Text 9"}, "group_2_text_10": {"type": "textarea", "label": "Group 2 Text 10"}, "group_1_text_7": {"type": "textarea", "label": "Group 1 Text 7"}, "group_2_text_11": {"type": "textarea", "label": "Group 2 Text 11"}, "group_2_text_12": {"type": "textarea", "label": "Group 2 Text 12"}, "group_1_text_8": {"type": "textarea", "label": "Group 1 Text 8"}, "group_2_text_13": {"type": "textarea", "label": "Group 2 Text 13"}, "group_2_text_14": {"type": "textarea", "label": "Group 2 Text 14"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_text": "Ürün Açıklaması", "group_1_title": "Çapraz bağlı HA ile uzun etkili tazelenme", "group_1_body_html": "RRS® HA Long Lasting, çapraz bağlı (cross-linked) ve tamamen emilebilir hyalüronik asit içeren steril bir dermal implanttır. Kullanıma hazır tıbbi enjektör formunda sunulan ürün, amino asit içeren koruyucu bir tampon solüsyonunda çözülerek dokuyla yüksek biyouyum sağlar.", "group_2_body_html": "Cildin derin katmanlarına uygulanan formül, dermisi içten nemlendirir, su tutma kapasitesini artırır ve doku kalitesini iyileştirir. Çapraz bağlı yapısı sayesinde etkisini daha uzun süre koruyarak (long lasting), tek seansta belirgin tazelenme ve canlanma sağlar.", "group_2_text": "Kullanım Alanları", "group_2_title": "Skinbooster protokolleri için", "group_2_body_html_2": "Cilt gençleştirme ve skinbooster protokollerinde, dokunun nem dengesini ve elastikiyetini desteklemek amacıyla aşağıdaki bölgelerde uygulanabilir:", "group_1_text_2": "Yüz", "group_2_text_2": "Boyun", "group_3_text": "Dekolte", "group_4_text": "El sırtı", "group_3_text_2": "İçerik & Özellikler", "group_3_title": "Teknik özet", "group_1_text_3": "•", "group_2_text_3": "Çapraz bağlı hyalüronik asit:", "group_2_text_4": "emilebilir, uzun etkili dermal implant yapısı.", "group_1_text_4": "•", "group_2_text_5": "Koruyucu tampon solüsyonu:", "group_2_text_6": "amino asit içeren, dengeli çözücü ortam.", "group_1_text_5": "•", "group_2_text_7": "Steril enjektör formu:", "group_2_text_8": "kullanıma hazır, tek kullanımlık tıbbi sunum.", "group_1_text_6": "•", "group_2_text_9": "Sertifikasyon:", "group_2_text_10": "CE Class III tıbbi cihaz onayı.", "group_1_text_7": "•", "group_2_text_11": "Endikasyon:", "group_2_text_12": "cilt gençleştirme, nemlendirme ve doku kalitesi iyileştirme.", "group_1_text_8": "•", "group_2_text_13": "Üretici:", "group_2_text_14": "Skin Tech Pharma Group."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-urun-detay-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'RELATED PRODUCTS',
             'html_template'=><<<'EDHTML'
<!-- RELATED PRODUCTS -->

    <section class="section">
      <div class="wrap">
        <div class="reveal" style="display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:44px;">
          <div style="max-width:560px;">
            <span class="eyebrow">{{text}}</span>
            <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">{{title}}</h2>
          </div>
          <a class="link-arrow" href="{{button_url}}">{{button_text}} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="brandgrid">
          {{{brandc_items_html}}}</div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"brandc_items": {"type": "repeater", "label": "Brandc Items", "repeat_kind": "items", "item_template": "<a class=\"brandc reveal\" href=\"{{button_url}}\" style=\"padding:0;overflow:hidden;\">\n            <div class=\"feature__img\" style=\"aspect-ratio:5/4;background-image:url('{{background_image_url}}');\"></div>\n            <div style=\"padding:24px 26px;\">\n              <div class=\"brandc__origin\">{{text}}</div>\n              <div class=\"brandc__logo\" style=\"font-size:18px;\">{{text_2}}</div>\n              <p>{{description}}</p>\n            </div>\n          </a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "background_image_url": {"type": "image", "label": "Background Image Url"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "description": {"type": "textarea", "label": "Description"}}}, "button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"brandc_items": [{"button_url": "/urun-detay", "background_image_url": "assets/img/product-melablock-hsp-spf-50.jpg", "text": "Skin Tech · Krem", "text_2": "Melablock HSP SPF 50+", "description": "Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktör."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-benebellum-lumina-vit-c-18.jpg", "text": "Skin Tech · Mezoterapi", "text_2": "Benebellum LUMINA VİT-C 18%", "subtitle": "Yüksek konsantrasyonlu C vitamini ile aydınlatıcı bakım."}, {"button_url": "/urun-detay", "background_image_url": "assets/img/product-atrofillin.jpg", "text": "Skin Tech · RRS", "text_2": "Atrofillin", "description": "Atrofik ve yıpranmış cilt için yenileyici dermal enjeksiyon çözümü."}], "button_url": "/urunler", "text": "Benzer Ürünler", "title": "İlginizi çekebilecek diğer ürünler", "button_text": "Tüm ürünler"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-urun-detay-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CTA / FOR DOCTORS',
             'html_template'=><<<'EDHTML'
<!-- CTA / FOR DOCTORS -->

    <section class="section" style="background:var(--surface);border-top:1px solid var(--line);">
      <div class="wrap">
        <div class="docs reveal">
          <div class="docs__grid" aria-hidden="true"></div>
          <span class="eyebrow" style="color:#F4A14E;">{{text}}</span>
          <h2 style="margin-top:18px;max-width:660px;">{{title}}</h2>
          <p class="lead" style="max-width:600px;margin-top:14px;">{{description}}</p>
          <div class="hero__cta" style="margin-top:30px;">
            <a class="btn btn--wa" href="{{button_url}}" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4z"/></svg>
              {{button_text}}</a>
            <a class="btn btn--ghost" href="{{button_url_2}}" target="_blank" rel="noopener" style="color:#fff;border-color:rgba(255,255,255,.3);">{{button_text_2}}</a>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "button_url_2": "https://apps.skintechpharmagroup.com/medinet", "text": "Bize Ulaşın", "title": "Ürün, fiyat ve eğitim için ekibimiz hazır", "description": "WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın. Resmi distribütör güvencesiyle %100 orijinal ürünler.", "button_text": "WhatsApp ile Yaz", "button_text_2": "MEDINET Portalı"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-markalar-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PAGE HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><!-- PAGE HERO -->

    <section class="phero">
      <div class="wrap">
        <nav class="crumb" aria-label="Sayfa konumu">
          <a href="{{button_url}}">{{button_text}}</a> {{text}} <span>{{text_2}}</span>
        </nav>
        <span class="eyebrow reveal">{{text_3}}</span>
        <h1 class="reveal d1">{{title}}</h1>
        <p class="lead reveal d2">{{{body_html}}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_text": {"type": "textarea", "label": "Button Text"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap", "button_url": "/", "button_text": "Ana Sayfa", "text": "/", "text_2": "Markalar", "text_3": "Temsil Ettiğimiz Markalar", "title": "Temsil Ettiğimiz Markalar", "body_html": "Her biri kendi alanında uzman, uluslararası 6 marka — Türkiye'deki resmi temsilcisi Estetik Dermal. Kimyasal peeling ve mezoterapiden cihaz teknolojisi ve K-beauty'ye uzanan kapsamlı bir portföy."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-markalar-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'BRAND CARDS',
             'html_template'=><<<'EDHTML'
<!-- BRAND CARDS -->

    <section class="section">
      <div class="wrap">
        <div class="brandgrid">

          <!-- Skin Tech Pharma Group -->
          {{{brandc_items_html}}}<!-- MI-Medical Innovation -->
          <!-- Neogenesis -->
          <!-- Seffiline -->
          <!-- Woorhi Mechatronics -->
          <!-- Grand Aespio -->
          </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"brandc_items": {"type": "repeater", "label": "Brandc Items", "repeat_kind": "items", "item_template": "<a class=\"brandc reveal\" href=\"{{button_url}}\">\n            <!-- 🖼️ GÖRSEL: assets/img/brand-panel-skintech.jpg (oran 16:9) — premium minimal ürün still life -->\n            <div class=\"brandc__img\" style=\"background-image:url('{{background_image_url}}')\"></div>\n            <div class=\"brandc__origin\">{{text}}</div>\n            <div class=\"brandc__logo\">{{text_2}}</div>\n            <p>{{description}}</p>\n            <span class=\"link-arrow\" style=\"margin-top:18px;\">{{text_3}} <svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M5 12h14M13 6l6 6-6 6\"/></svg></span>\n          </a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "background_image_url": {"type": "image", "label": "Background Image Url"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "description": {"type": "textarea", "label": "Description"}, "text_3": {"type": "textarea", "label": "Text 3"}}}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"brandc_items": [{"button_url": "/marka-skintech", "background_image_url": "assets/img/brand-panel-skintech.jpg", "text": "İspanya · Amiral Marka", "text_2": "Skin Tech Pharma Group", "description": "Kimyasal peeling, mezoterapi ve RRS skinbooster serisinde dünya lideri. CE Class III sertifikalı portföyün omurgası.", "text_3": "Markayı Keşfet"}, {"button_url": "/marka-mi-medical", "background_image_url": "assets/img/brand-panel-mi-medical.jpg", "text": "Premium · Enjeksiyon Sistemleri", "text_2": "MI-Medical Innovation", "description": "Premium mezoterapi ve enjeksiyon sistemleri; hassas uygulama için geliştirilmiş profesyonel çözümler.", "text_3": "Markayı Keşfet"}, {"button_url": "/marka-neogenesis", "background_image_url": "assets/img/brand-panel-neogenesis.jpg", "text": "Portföy · Rejeneratif Bakım", "text_2": "Neogenesis", "description": "Rejeneratif cilt bakımı çözümleri; cilt yenilenmesini destekleyen ileri formüllerle portföyümüzü tamamlar.", "text_3": "Markayı Keşfet"}, {"button_url": "/marka-seffiline", "background_image_url": "assets/img/brand-panel-seffiline.jpg", "text": "Bakım & Dolgu Serisi", "text_2": "Seffiline", "description": "Cilt, saç, intim bakım ve dolgu çözümleri serisi. Geniş kullanım alanına yayılan bütünsel bir bakım yelpazesi.", "text_3": "Markayı Keşfet"}, {"button_url": "/marka-woorhi", "background_image_url": "assets/img/brand-panel-woorhi.jpg", "text": "Güney Kore · Mekatronik", "text_2": "Woorhi Mechatronics", "description": "Kore mühendisliğiyle geliştirilen medikal estetik cihazları. Mekatronik hassasiyetle klinik performans.", "text_3": "Markayı Keşfet"}, {"button_url": "/marka-aespio", "background_image_url": "assets/img/brand-panel-aespio.jpg", "text": "K-Beauty · Thread Lift", "text_2": "Grand Aespio", "description": "Yüz maskeleri ve ip askı (thread lift) ürünleri. Modern K-beauty yaklaşımıyla estetik bakım çözümleri.", "text_3": "Markayı Keşfet"}]}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-markalar-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'KISA BANT',
             'html_template'=><<<'EDHTML'
<!-- KISA BANT -->

    <section class="section" style="padding-block:0;">
      <div class="wrap">
        <div class="band-note reveal">
          <span class="band-note__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="9" r="6"/><path d="m9 14.5-2 7 5-3 5 3-2-7"/><path d="m12 6 1 2 2 .3-1.5 1.5.4 2L12 11l-1.9 1 .4-2L9 8.3 11 8Z"/></svg></span>
          <p>{{subtitle}} <strong>{{group_1_text}}</strong> {{subtitle_2}} <strong>{{group_2_text}}</strong> {{subtitle_3}}</p>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"subtitle": {"type": "textarea", "label": "Subtitle"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "subtitle_2": {"type": "textarea", "label": "Subtitle 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "subtitle_3": {"type": "textarea", "label": "Subtitle 3"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"subtitle": "Portföyümüzde ayrıca", "group_1_text": "Neogenesis", "subtitle_2": "rejeneratif bakım serisi ve", "group_2_text": "CE Class III sertifikalı RRS", "subtitle_3": "skinbooster serisi yer alır."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-markalar-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CTA',
             'html_template'=><<<'EDHTML'
<!-- CTA -->

    <section class="section">
      <div class="wrap">
        <div class="docs reveal" style="text-align:center;">
          <div class="docs__grid" aria-hidden="true"></div>
          <span class="eyebrow" style="color:#F4A14E;justify-content:center;">{{text}}</span>
          <h2 style="margin:18px auto 14px;max-width:660px;">{{title}}</h2>
          <p class="lead" style="max-width:600px;margin:0 auto 30px;">{{description}}</p>
          <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
            <a class="btn" href="{{button_url}}" target="_blank" rel="noopener" style="background:#25D366;color:#fff;">
              <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9z"/></svg>
              {{button_text}}</a>
            <a class="btn btn--ghost" href="{{button_url_2}}" target="_blank" rel="noopener" style="color:#fff;border-color:rgba(255,255,255,.32);">{{button_text_2}}</a>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "button_url_2": "https://apps.skintechpharmagroup.com/medinet", "text": "Bize Ulaşın", "title": "Profesyonel çözümler için buradayız", "description": "Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.", "button_text": "WhatsApp ile Yaz", "button_text_2": "MEDINET Portalı"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-etkinlikler-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PAGE HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><!-- PAGE HERO -->

    <section class="phero">
      <div class="wrap">
        <nav class="crumb" aria-label="Sayfa konumu">
          <a href="{{button_url}}">{{button_text}}</a> {{text}} <span>{{text_2}}</span>
        </nav>
        <span class="eyebrow reveal">{{text_3}}</span>
        <h1 class="reveal d1">{{title}}</h1>
        <p class="lead reveal d2">{{{body_html}}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_text": {"type": "textarea", "label": "Button Text"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap", "button_url": "/", "button_text": "Ana Sayfa", "text": "/", "text_2": "Eğitim & Kongre", "text_3": "Kongre & Etkinlikler", "title": "Kongre & Etkinlikler", "body_html": "Estetik Dermal olarak yer aldığımız ulusal ve uluslararası kongreler, fuarlar ve hekimlere yönelik uygulamalı eğitim etkinlikleri."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-etkinlikler-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'EVENT GRID',
             'html_template'=><<<'EDHTML'
<!-- EVENT GRID -->

    <section class="section">
      <div class="wrap">
        <div class="band-note reveal" style="margin-bottom:40px;">
          <span class="band-note__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/></svg></span>
          <p>{{description}}</p>
        </div>

        <div class="eventgrid">

          <!-- Etkinlik 1 -->
          {{{eventc_items_html}}}<!-- Etkinlik 2 -->
          <!-- Etkinlik 3 -->
          <!-- Etkinlik 4 -->
          <!-- Etkinlik 5 -->
          <!-- Etkinlik 6 -->
          </div>

        <p style="margin:32px 0 0;font-size:12.5px;color:var(--muted);line-height:1.6;font-style:italic;">{{{body_html}}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"eventc_items": {"type": "repeater", "label": "Eventc Items", "repeat_kind": "items", "item_template": "<article class=\"eventc reveal\">\n            <!-- 🖼️ GÖRSEL: assets/img/event-1.jpg (oran 16:9) — uluslararası estetik kongresi -->\n            <div class=\"eventc__media\" style=\"background-image:url('{{background_image_url}}')\">\n              <span class=\"eventc__date\"><small>{{text}}</small><b>{{text_2}}</b></span>\n            </div>\n            <div class=\"eventc__body\">\n              <h3>{{title}}</h3>\n              <p class=\"eventc__place\"><svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\"><path d=\"M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z\"/><circle cx=\"12\" cy=\"10\" r=\"2.5\"/></svg>{{subtitle}}</p>\n              <p class=\"eventc__desc\">{{description}}</p>\n            </div>\n          </article>", "fields": {"background_image_url": {"type": "image", "label": "Background Image Url"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "title": {"type": "text", "label": "Title"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "description": {"type": "textarea", "label": "Description"}}}, "description": {"type": "textarea", "label": "Description"}, "body_html": {"type": "textarea", "label": "Body"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"eventc_items": [{"background_image_url": "assets/img/event-1.jpg", "text": "Oca", "text_2": "29", "title": "IMCAS World Congress 2026", "subtitle": "Paris, Fransa", "description": "Dünyanın en kapsamlı estetik dermatoloji kongresinde son teknoloji ürün ve uygulamalarla yer alıyoruz."}, {"background_image_url": "assets/img/event-2.jpg", "text": "Mar", "text_2": "14", "title": "Anti-Aging & Estetik Kongresi", "subtitle": "İstanbul", "description": "Yaşlanma karşıtı uygulamalarda güncel protokoller; standımızda ürün ve cihaz demoları."}, {"background_image_url": "assets/img/event-3.jpg", "text": "May", "text_2": "22", "title": "Dermatoloji & Kozmetoloji Günleri", "subtitle": "Antalya", "description": "Mezoterapi ve peeling odaklı bilimsel oturumlar ile interaktif uygulama atölyeleri."}, {"background_image_url": "assets/img/event-4.jpg", "text": "Haz", "text_2": "18", "title": "Skin Tech Uygulamalı Eğitim Workshop", "subtitle": "Kuşadası, Aydın", "description": "Hekimlere yönelik birebir, uygulamalı RRS ve peeling eğitimi; sınırlı kontenjanlı atölye."}, {"background_image_url": "assets/img/event-5.jpg", "text": "Eyl", "text_2": "26", "title": "FACE Aesthetic Conference", "subtitle": "İzmir", "description": "Yüz estetiğinde ip askı ve dolgu yeniliklerinin paylaşıldığı bölgesel uzman buluşması."}, {"background_image_url": "assets/img/event-6.jpg", "text": "Kas", "text_2": "12", "title": "Medikal Estetik Fuarı", "subtitle": "Ankara", "description": "Temsil ettiğimiz tüm markaların ürün ve cihazlarını yakından inceleyebileceğiniz fuar standı."}], "description": "Aşağıdaki etkinlikler temsili örneklerdir; gerçek tarihler ve katılım bilgileri onaylandıkça güncellenecektir.", "body_html": "* Bu sayfadaki etkinlikler temsili örnek amaçlıdır. Kesin tarihler, mekânlar ve katılım koşulları onaylandıkça güncellenecektir."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-etkinlikler-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'INFO BLOCK',
             'html_template'=><<<'EDHTML'
<!-- INFO BLOCK -->

    <section class="section" style="padding-top:0;">
      <div class="wrap">
        <div class="infoband reveal">
          <div class="infoband__copy">
            <h2>{{title}}</h2>
            <p class="lead">{{{body_html}}}</p>
          </div>
          <div class="infoband__cta">
            <a class="btn" href="{{button_url}}" target="_blank" rel="noopener" style="background:#25D366;color:#fff;">
              <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9z"/></svg>
              {{button_text}}</a>
            <a class="btn btn--ghost" href="{{button_url_2}}">{{button_text_2}}</a>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "button_url_2": "/iletisim", "title": "Etkinlik takvimi ve katılım için bizimle iletişime geçin", "body_html": "Yaklaşan kongreler, fuar standlarımız ve uygulamalı eğitim atölyelerimize katılım hakkında güncel bilgi almak için ekibimize ulaşın.", "button_text": "WhatsApp ile Yaz", "button_text_2": "İletişim"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-etkinlikler-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CTA',
             'html_template'=><<<'EDHTML'
<!-- CTA -->

    <section class="section" style="padding-top:0;">
      <div class="wrap">
        <div class="docs reveal" style="text-align:center;">
          <div class="docs__grid" aria-hidden="true"></div>
          <span class="eyebrow" style="color:#F4A14E;justify-content:center;">{{text}}</span>
          <h2 style="margin:18px auto 14px;max-width:660px;">{{title}}</h2>
          <p class="lead" style="max-width:600px;margin:0 auto 30px;">{{description}}</p>
          <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
            <a class="btn" href="{{button_url}}" target="_blank" rel="noopener" style="background:#25D366;color:#fff;">
              <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9z"/></svg>
              {{button_text}}</a>
            <a class="btn btn--ghost" href="{{button_url_2}}" target="_blank" rel="noopener" style="color:#fff;border-color:rgba(255,255,255,.32);">{{button_text_2}}</a>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "button_url_2": "https://apps.skintechpharmagroup.com/medinet", "text": "Bize Ulaşın", "title": "Profesyonel çözümler için buradayız", "description": "Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.", "button_text": "WhatsApp ile Yaz", "button_text_2": "MEDINET Portalı"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-iletisim-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'PAGE HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><!-- PAGE HERO -->

    <section class="phero">
      <div class="wrap">
        <nav class="crumb" aria-label="Sayfa konumu">
          <a href="{{button_url}}">{{button_text}}</a> {{text}} <span>{{text_2}}</span>
        </nav>
        <span class="eyebrow reveal">{{text_3}}</span>
        <h1 class="reveal d1">{{title}}</h1>
        <p class="lead reveal d2">{{description}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_text": {"type": "textarea", "label": "Button Text"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap", "button_url": "/", "button_text": "Ana Sayfa", "text": "/", "text_2": "İletişim", "text_3": "İletişim", "title": "İletişim", "description": "Ürün, fiyat ve eğitim talepleriniz için bize ulaşın. Ekibimiz en kısa sürede sizinle iletişime geçsin."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-iletisim-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'CONTACT',
             'html_template'=><<<'EDHTML'
<!-- CONTACT -->

    <section class="section">
      <div class="wrap">
        <div class="contact">

          <!-- SOL: form -->
          <form class="reveal" action="#" method="post" novalidate>
            <span class="eyebrow">{{text}}</span>
            <h2 style="font-size:clamp(24px,3vw,34px);margin:16px 0 10px;">{{title}}</h2>
            <p style="font-size:14px;color:var(--muted);margin:0 0 24px;line-height:1.6;">{{subtitle}} <code>{{text_2}}</code> {{subtitle_2}}</p>
            <div class="field"><label for="ad">{{group_1_text}}</label><input id="ad" name="ad" type="text" autocomplete="name" placeholder="Adınız ve soyadınız"></div>
            <div class="field"><label for="kurum">{{group_2_text}}</label><input id="kurum" name="kurum" type="text" autocomplete="organization" placeholder="Klinik veya kurum adınız"></div>
            <div class="field"><label for="tel">{{group_3_text}}</label><input id="tel" name="tel" type="tel" autocomplete="tel" placeholder="0 5xx xxx xx xx"></div>
            <div class="field"><label for="eposta">{{group_4_text}}</label><input id="eposta" name="eposta" type="email" autocomplete="email" placeholder="ornek@eposta.com"></div>
            <div class="field"><label for="mesaj">{{group_5_text}}</label><textarea id="mesaj" name="mesaj" rows="4" placeholder="Talebinizi kısaca yazın..."></textarea></div>
            <button class="btn btn--primary" type="submit" style="width:100%;justify-content:center;">{{button_text}}</button>
          </form>

          <!-- SAĞ: bilgi kartları + harita -->
          <div class="contact__info reveal d1">
            <a class="info-row" href="{{group_1_button_url}}">
              <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg></span>
              <div><small>{{group_1_text_2}}</small><b>{{group_1_text_3}}</b></div>
            </a>
            <a class="info-row" href="{{group_2_button_url}}" target="_blank" rel="noopener">
              <span class="ic" style="background:rgba(37,211,102,.14);color:#25D366;"><svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 4 1.7 4 1.2 4.7 1.1.7-.1 2.4-1 2.7-1.9.3-1 .3-1.8.2-1.9z"/></svg></span>
              <div><small>{{group_2_text_2}}</small><b>{{group_2_text_3}}</b></div>
            </a>
            <a class="info-row" href="{{group_3_button_url}}">
              <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg></span>
              <div><small>{{group_3_text_2}}</small><b>{{group_3_text_3}}</b></div>
            </a>
            <a class="info-row" href="{{group_4_button_url}}" target="_blank" rel="noopener">
              <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
              <div><small>{{group_4_text_2}}</small><b style="font-weight:500;font-size:14px;">{{group_4_text_3}}</b></div>
            </a>
            <div class="contact__map">
              <iframe title="Estetik Dermal — Kuşadası / Aydın konum haritası" src="{{media_url}}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <a class="link-arrow" href="{{button_url}}" target="_blank" rel="noopener" style="margin-top:4px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg>
              {{button_text_2}}
            </a>
          </div>

        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_4_button_url": {"type": "text", "label": "Group 4 Button Url"}, "media_url": {"type": "image", "label": "Media Url"}, "button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "text_2": {"type": "textarea", "label": "Text 2"}, "subtitle_2": {"type": "textarea", "label": "Subtitle 2"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_5_text": {"type": "textarea", "label": "Group 5 Text"}, "button_text": {"type": "textarea", "label": "Button Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_3_text_3": {"type": "textarea", "label": "Group 3 Text 3"}, "group_4_text_2": {"type": "textarea", "label": "Group 4 Text 2"}, "group_4_text_3": {"type": "textarea", "label": "Group 4 Text 3"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "tel:+902566121813", "group_2_button_url": "https://wa.me/905426205100", "group_3_button_url": "mailto:info@estetikdermal.com", "group_4_button_url": "https://maps.google.com/maps?q=Kusadasi%20Aydin", "media_url": "https://maps.google.com/maps?q=Kusadasi%20Aydin&t=&z=12&ie=UTF8&iwloc=&output=embed", "button_url": "https://www.instagram.com/estetikdermal/", "text": "Talep Formu", "title": "Bize yazın, size dönelim", "subtitle": "Form yalnızca yapı amaçlıdır; CMS'e bağlandığında", "text_2": "/api/v1/forms/.../submit", "subtitle_2": "ile çalışacaktır.", "group_1_text": "Ad Soyad", "group_2_text": "Klinik / Kurum", "group_3_text": "Telefon", "group_4_text": "E-posta", "group_5_text": "Mesaj", "button_text": "Gönder", "group_1_text_2": "Telefon", "group_1_text_3": "0 256 612 18 13", "group_2_text_2": "WhatsApp", "group_2_text_3": "+90 542 620 51 00", "group_3_text_2": "E-posta", "group_3_text_3": "info@estetikdermal.com", "group_4_text_2": "Adres", "group_4_text_3": "Türkmen Mah. Turgut Özel Bulvarı, Ada Modern A Blok No 83/3A · Kuşadası / Aydın", "button_text_2": "Instagram'da takip edin"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-skintech-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'1 · HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><style>
    .st{
      --navy:#0B2A4A;
      --navy-900:#071D34;
      --navy-700:#143A60;
      --blue:#7FB3E8;          /* ince mavi aksan */
      --blue-deep:#2C6BB0;
      --ice:#EEF4FB;
      --ice-2:#F6F9FD;
      --line:#D7E2F0;
      --paper:#FBFCFE;
      --ink:#0B2A4A;
      --muted:#46627F;
      --muted-2:#6E87A2;
      --wa:#25D366;
      --serif:"Newsreader",Georgia,"Times New Roman",serif;
      --sans:"Inter",system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
      --ease:cubic-bezier(.22,1,.36,1);
      font-family:var(--sans);
      color:var(--ink);
      -webkit-font-smoothing:antialiased;
    }
    .st *{ box-sizing:border-box; }
    .st-wrap{ width:min(1200px,92vw); margin-inline:auto; }
    .st h1,.st h2,.st h3,.st .st-serif{ font-family:var(--serif); font-weight:500; letter-spacing:-.015em; }

    /* --- Eyebrow / kicker --- */
    .st-kick{
      display:inline-flex; align-items:center; gap:9px;
      font-family:var(--sans); font-size:11.5px; font-weight:600;
      letter-spacing:.18em; text-transform:uppercase; color:var(--blue-deep);
    }
    .st-kick::before{ content:""; width:26px; height:1px; background:var(--blue-deep); opacity:.6; }
    .st-kick--light{ color:var(--blue); } .st-kick--light::before{ background:var(--blue); opacity:.7; }

    /* --- Buttons --- */
    .st-btn{ display:inline-flex; align-items:center; gap:10px; font-family:var(--sans);
      font-weight:600; font-size:15px; padding:14px 26px; border-radius:6px; line-height:1;
      transition:transform .25s var(--ease), box-shadow .25s var(--ease), background .25s, color .25s; cursor:pointer; }
    .st-btn:focus-visible{ outline:3px solid var(--blue-deep); outline-offset:3px; }
    .st-btn--solid{ background:var(--navy); color:#fff; box-shadow:0 12px 30px rgba(11,42,74,.22); }
    .st-btn--solid:hover{ transform:translateY(-2px); box-shadow:0 18px 40px rgba(11,42,74,.30); }
    .st-btn--ghost{ background:transparent; color:var(--navy); border:1px solid var(--line); }
    .st-btn--ghost:hover{ border-color:var(--navy); transform:translateY(-2px); }
    .st-btn--wa{ background:var(--wa); color:#062b14; }
    .st-btn--wa:hover{ transform:translateY(-2px); box-shadow:0 14px 32px rgba(37,211,102,.35); }
    .st-btn--lightline{ background:transparent; color:#fff; border:1px solid rgba(255,255,255,.42); }
    .st-btn--lightline:hover{ border-color:#fff; background:rgba(255,255,255,.08); transform:translateY(-2px); }
    .st-btn--lightline:focus-visible{ outline:3px solid var(--blue); outline-offset:3px; }
    .st-btn svg{ width:18px; height:18px; flex-shrink:0; }

    .st-link{ display:inline-flex; align-items:center; gap:8px; font-weight:600; font-size:15px;
      color:var(--blue-deep); }
    .st-link svg{ width:18px; height:18px; transition:transform .25s var(--ease); }
    .st-link:hover svg{ transform:translateX(5px); }
    .st-link:focus-visible{ outline:2px solid var(--blue-deep); outline-offset:3px; border-radius:3px; }

    .st-chip{ display:inline-flex; align-items:center; gap:7px; font-size:12px; font-weight:600;
      letter-spacing:.02em; padding:6px 13px; border-radius:999px;
      background:var(--ice); color:var(--navy-700); border:1px solid var(--line); }
    .st-chip .dot{ width:6px; height:6px; border-radius:50%; background:var(--blue-deep); flex-shrink:0; }

    /* ============ HERO ============ */
    .st-hero{ position:relative; overflow:hidden; min-height:100vh; display:flex; align-items:center;
      background:var(--navy);
      background-image:
        radial-gradient(1200px 620px at 88% -10%, rgba(127,179,232,.20), transparent 60%),
        radial-gradient(900px 500px at -10% 110%, rgba(44,107,176,.22), transparent 60%),
        linear-gradient(180deg,#0B2A4A 0%, #071D34 100%);
      padding:120px 0 80px; }
    .st-hero__lines{ position:absolute; inset:0; pointer-events:none;
      background-image:linear-gradient(rgba(127,179,232,.07) 1px,transparent 1px),
                       linear-gradient(90deg,rgba(127,179,232,.07) 1px,transparent 1px);
      background-size:64px 64px;
      -webkit-mask-image:radial-gradient(900px 600px at 75% 30%,#000,transparent 78%);
      mask-image:radial-gradient(900px 600px at 75% 30%,#000,transparent 78%); }
    .st-hero__in{ position:relative; display:grid; grid-template-columns:1.05fr .95fr; gap:64px; align-items:center; }
    .st-hero__loc{ display:inline-flex; align-items:center; gap:10px; color:var(--blue);
      font-size:12.5px; font-weight:600; letter-spacing:.16em; text-transform:uppercase; margin:0 0 26px; }
    .st-hero__loc .pin{ width:7px; height:7px; border-radius:50%; background:var(--blue);
      box-shadow:0 0 0 4px rgba(127,179,232,.18); }
    .st-hero h1{ color:#fff; font-size:clamp(40px,6vw,78px); line-height:1.02; margin:0 0 26px; }
    .st-hero h1 em{ font-style:italic; color:var(--blue); font-weight:500; }
    .st-hero__sub{ color:rgba(255,255,255,.80); font-size:clamp(16px,1.6vw,19px); line-height:1.72;
      max-width:560px; margin:0 0 38px; }
    .st-hero__cta{ display:flex; gap:14px; flex-wrap:wrap; }
    .st-hero__meta{ display:flex; gap:0; flex-wrap:wrap; margin-top:46px;
      border-top:1px solid rgba(127,179,232,.22); padding-top:26px; }
    .st-hero__meta > div{ padding-right:34px; margin-right:34px; border-right:1px solid rgba(127,179,232,.18); }
    .st-hero__meta > div:last-child{ border-right:0; margin-right:0; padding-right:0; }
    .st-hero__meta small{ display:block; font-size:11.5px; letter-spacing:.12em; text-transform:uppercase;
      color:var(--blue); margin-bottom:6px; }
    .st-hero__meta b{ display:block; color:#fff; font-size:19px; font-family:var(--serif); font-weight:500; }

    /* Hero visual */
    .st-hv{ position:relative; }
    .st-hv__glow{ position:absolute; inset:-6% -4% -6% -4%;
      background:linear-gradient(140deg,rgba(127,179,232,.45),rgba(44,107,176,.15));
      filter:blur(8px); border-radius:30px; transform:rotate(-2.4deg); opacity:.5; }
    .st-hv__card{ position:relative; background:rgba(255,255,255,.04); border:1px solid rgba(127,179,232,.28);
      border-radius:22px; padding:22px; backdrop-filter:blur(6px); box-shadow:0 30px 80px rgba(2,12,28,.5); }
    .st-hv__img{ aspect-ratio:4/5; border-radius:14px; overflow:hidden;
      background:linear-gradient(155deg,#13365A,#0A2240) center/cover no-repeat;
      background-image:linear-gradient(155deg,rgba(11,42,74,.1),rgba(7,29,52,.45)),url('/assets/img/skintech-hero.jpg');
      position:relative; }
    .st-hv__tag{ position:absolute; top:14px; left:14px; z-index:2; font-size:10.5px; font-weight:600;
      letter-spacing:.16em; text-transform:uppercase; color:#fff; background:rgba(11,42,74,.55);
      border:1px solid rgba(127,179,232,.4); padding:6px 12px; border-radius:999px; backdrop-filter:blur(4px); }
    .st-hv__cap{ display:flex; align-items:center; justify-content:space-between; gap:14px; margin-top:18px; }
    .st-hv__cap small{ display:block; font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:var(--blue); }
    .st-hv__cap b{ display:block; color:#fff; font-family:var(--serif); font-size:18px; font-weight:500; margin-top:3px; }
    .st-ce{ flex-shrink:0; display:grid; place-items:center; text-align:center; width:54px; height:54px; border-radius:12px;
      background:rgba(127,179,232,.14); border:1px solid rgba(127,179,232,.42); }
    .st-ce b{ font-family:var(--sans); font-size:13px; font-weight:700; color:#fff; line-height:1; }
    .st-ce small{ display:block; font-size:8px; letter-spacing:.1em; color:var(--blue); margin-top:2px; }
    .st-hv__float{ position:absolute; bottom:-22px; left:-22px; display:flex; align-items:center; gap:12px;
      background:#fff; border:1px solid var(--line); border-radius:14px; padding:13px 18px;
      box-shadow:0 18px 44px rgba(7,29,52,.28); }
    .st-hv__float .ic{ width:38px; height:38px; border-radius:10px; flex-shrink:0; display:grid; place-items:center;
      background:var(--ice); color:var(--blue-deep); }
    .st-hv__float .ic svg{ width:20px; height:20px; }
    .st-hv__float small{ display:block; font-size:11px; color:var(--muted-2); }
    .st-hv__float b{ display:block; font-size:14px; color:var(--navy); font-weight:600; }

    /* ============ SECTION SHELL ============ */
    .st-sec{ padding:clamp(72px,9vw,118px) 0; }
    .st-sec--paper{ background:var(--paper); }
    .st-sec--ice{ background:var(--ice-2); border-block:1px solid var(--line); }
    .st-head{ max-width:680px; margin:0 0 56px; }
    .st-head--center{ margin-inline:auto; text-align:center; }
    .st-head h2{ font-size:clamp(30px,4.4vw,50px); line-height:1.08; margin:18px 0 0; color:var(--navy); }
    .st-head p{ color:var(--muted); font-size:17px; line-height:1.72; margin:18px 0 0; }

    /* ============ STORY ============ */
    .st-story{ display:grid; grid-template-columns:1fr 1fr; gap:64px; align-items:center; }
    .st-story__media{ position:relative; }
    .st-story__img{ aspect-ratio:4/5; border-radius:18px; overflow:hidden;
      background:linear-gradient(155deg,var(--ice),#DCE8F6) center/cover no-repeat;
      background-image:url('/assets/img/skintech-lab.jpg');
      border:1px solid var(--line); box-shadow:0 26px 60px rgba(7,29,52,.12); }
    .st-story__quote{ position:absolute; right:-18px; bottom:26px; max-width:240px;
      background:var(--navy); color:#fff; border-radius:14px; padding:18px 22px 20px;
      box-shadow:0 22px 50px rgba(7,29,52,.34); }
    .st-story__quote span{ font-family:var(--serif); font-size:40px; line-height:0; color:var(--blue); display:block; height:22px; }
    .st-story__quote p{ font-size:14px; line-height:1.6; margin:6px 0 0; color:rgba(255,255,255,.88); }
    .st-story p.lead{ color:var(--muted); font-size:17.5px; line-height:1.78; margin:0 0 20px; }
    .st-story strong{ color:var(--navy); font-weight:600; }

    /* ============ CREDIBILITY STRIP ============ */
    .st-cred{ background:var(--navy); }
    .st-cred__grid{ display:grid; grid-template-columns:repeat(4,1fr); }
    .st-cred__item{ padding:44px 28px; border-right:1px solid rgba(127,179,232,.16);
      border-top:1px solid rgba(127,179,232,.16); }
    .st-cred__grid > .st-cred__item:nth-child(4n){ border-right:0; }
    .st-cred__item .ic{ width:40px; height:40px; border-radius:11px; margin-bottom:18px; display:grid; place-items:center;
      background:rgba(127,179,232,.14); border:1px solid rgba(127,179,232,.4); color:var(--blue); }
    .st-cred__item .ic svg{ width:21px; height:21px; }
    .st-cred__item b{ display:block; color:#fff; font-family:var(--serif); font-size:21px; font-weight:500; }
    .st-cred__item small{ display:block; color:rgba(255,255,255,.66); font-size:13.5px; margin-top:6px; line-height:1.5; }
    .st-cred__note{ text-align:center; padding:22px 24px; color:rgba(255,255,255,.74); font-size:13.5px;
      border-top:1px solid rgba(127,179,232,.16); letter-spacing:.02em; }
    .st-cred__note b{ color:var(--blue); font-weight:600; }

    /* ============ FEATURED SPLIT (RRS + Melablock) ============ */
    .st-split{ display:grid; grid-template-columns:1fr 1fr; gap:30px; }
    .st-feat{ position:relative; border-radius:20px; overflow:hidden; border:1px solid var(--line);
      display:flex; flex-direction:column; min-height:480px;
      transition:transform .35s var(--ease), box-shadow .35s var(--ease); }
    .st-feat:hover{ transform:translateY(-6px); box-shadow:0 30px 70px rgba(7,29,52,.18); }
    .st-feat__img{ flex:1 1 auto; min-height:240px;
      background:linear-gradient(155deg,#13365A,#0A2240) center/cover no-repeat; position:relative; }
    .st-feat--a .st-feat__img{ background-image:linear-gradient(180deg,transparent 45%,rgba(7,29,52,.2)),url('/assets/img/skintech-rrs.jpg'); }
    .st-feat--b .st-feat__img{ background-image:linear-gradient(180deg,transparent 45%,rgba(7,29,52,.2)),url('/assets/img/skintech-melablock.jpg'); }
    .st-feat__badge{ position:absolute; top:18px; left:18px; }
    .st-feat__badge .st-chip{ background:rgba(255,255,255,.92); }
    .st-feat__body{ background:#fff; padding:30px 32px 34px; }
    .st-feat__body .st-kick{ margin-bottom:12px; }
    .st-feat__body h3{ font-size:clamp(24px,2.6vw,32px); margin:0 0 12px; color:var(--navy); }
    .st-feat__body p{ color:var(--muted); font-size:15.5px; line-height:1.7; margin:0 0 20px; }
    .st-feat__body .st-chip{ margin-bottom:20px; }

    /* ============ PRODUCT RANGE GRID ============ */
    .st-fam{ display:grid; grid-template-columns:repeat(4,1fr); gap:22px; }
    .st-fam__col{ background:#fff; border:1px solid var(--line); border-radius:16px; overflow:hidden;
      display:flex; flex-direction:column; }
    .st-fam__h{ background:linear-gradient(150deg,var(--navy),var(--navy-700)); padding:22px 22px 20px; }
    .st-fam__h .tag{ font-size:10.5px; font-weight:600; letter-spacing:.16em; text-transform:uppercase; color:var(--blue); }
    .st-fam__h h3{ font-size:20px; margin:6px 0 0; color:#fff; }
    .st-fam__list{ padding:12px; display:flex; flex-direction:column; gap:9px; }
    .st-prod{ display:flex; align-items:center; justify-content:space-between; gap:12px;
      padding:14px 15px; border-radius:11px; background:var(--ice-2); border:1px solid transparent;
      transition:background .2s, border-color .2s, transform .2s; }
    .st-prod:hover{ background:var(--ice); border-color:var(--blue); transform:translateX(3px); }
    .st-prod:focus-visible{ outline:2px solid var(--blue-deep); outline-offset:2px; }
    .st-prod b{ display:block; font-size:14.5px; font-weight:600; color:var(--navy); }
    .st-prod small{ display:block; font-size:12.5px; color:var(--muted-2); margin-top:2px; }
    .st-prod .arr{ color:var(--blue-deep); flex-shrink:0; font-weight:700; }

    /* ============ FOR DOCTORS ============ */
    .st-doc{ position:relative; overflow:hidden; border-radius:24px; padding:clamp(40px,5vw,68px);
      background:var(--navy);
      background-image:radial-gradient(700px 400px at 90% 0%,rgba(127,179,232,.18),transparent 60%); }
    .st-doc__lines{ position:absolute; inset:0; pointer-events:none;
      background-image:linear-gradient(rgba(127,179,232,.06) 1px,transparent 1px),
                       linear-gradient(90deg,rgba(127,179,232,.06) 1px,transparent 1px);
      background-size:48px 48px; -webkit-mask-image:linear-gradient(180deg,#000,transparent 85%);
      mask-image:linear-gradient(180deg,#000,transparent 85%); }
    .st-doc__in{ position:relative; }
    .st-doc h2{ color:#fff; font-size:clamp(28px,3.8vw,42px); max-width:680px; margin:18px 0 14px; }
    .st-doc__lead{ color:rgba(255,255,255,.80); font-size:17px; line-height:1.72; max-width:620px; margin:0 0 42px; }
    .st-doc__cards{ display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-bottom:42px; }
    .st-doc__card{ background:rgba(255,255,255,.05); border:1px solid rgba(127,179,232,.24);
      border-radius:14px; padding:24px; }
    .st-doc__card .ic{ width:44px; height:44px; border-radius:11px; display:grid; place-items:center;
      background:rgba(127,179,232,.14); border:1px solid rgba(127,179,232,.4); color:var(--blue); margin-bottom:16px; }
    .st-doc__card .ic svg{ width:23px; height:23px; }
    .st-doc__card h4{ font-family:var(--serif); font-weight:500; font-size:19px; color:#fff; margin:0 0 8px; }
    .st-doc__card p{ color:rgba(255,255,255,.74); font-size:14.5px; line-height:1.65; margin:0; }

    /* ============ CTA BAND ============ */
    .st-cta{ position:relative; overflow:hidden; border-radius:24px; text-align:center;
      padding:clamp(48px,7vw,84px) clamp(24px,5vw,64px); color:#fff;
      background:linear-gradient(135deg,var(--navy-900),var(--navy-700)); }
    .st-cta__orb{ position:absolute; border-radius:50%; background:rgba(127,179,232,.14); pointer-events:none; }
    .st-cta h2{ font-size:clamp(28px,4.4vw,48px); margin:0 auto 16px; max-width:14ch; line-height:1.08; color:#fff; }
    .st-cta p{ color:rgba(255,255,255,.84); font-size:18px; line-height:1.6; max-width:580px; margin:0 auto 36px; }
    .st-cta__row{ display:flex; gap:14px; justify-content:center; flex-wrap:wrap; }

    /* ============ RESPONSIVE ============ */
    @media (max-width:980px){
      .st-hero__in,.st-story,.st-split,.st-doc__cards{ grid-template-columns:1fr; }
      .st-hero{ min-height:auto; }
      .st-hv{ order:-1; max-width:440px; margin-inline:auto; }
      .st-fam{ grid-template-columns:1fr 1fr; }
      .st-cred__grid{ grid-template-columns:1fr 1fr; }
      .st-cred__grid > .st-cred__item:nth-child(4n){ border-right:1px solid rgba(127,179,232,.16); }
      .st-cred__grid > .st-cred__item:nth-child(2n){ border-right:0; }
      .st-story__quote{ position:static; max-width:none; margin-top:18px; }
    }
    @media (max-width:560px){
      .st-fam{ grid-template-columns:1fr; }
      .st-cred__grid{ grid-template-columns:1fr; }
      .st-cred__grid > .st-cred__item{ border-right:0 !important; }
      .st-doc__cards{ grid-template-columns:1fr; }
      .st-hero__meta > div{ border-right:0; padding-right:0; margin-right:0; margin-bottom:18px; }
      .st-hv__float{ left:0; }
    }
  </style><!-- 1 · HERO -->

    <section class="st-hero" aria-labelledby="st-h1">
      <div class="st-hero__lines" aria-hidden="true"></div>
      <div class="st-wrap st-hero__in">
        <div class="st-hero__copy">
          <p class="st-hero__loc reveal"><span class="pin" aria-hidden="true"></span>{{subtitle}}</p>
          <h1 id="st-h1" class="reveal d1">{{title}}<br><em>{{text}}</em> {{title_2}}</h1>
          <p class="st-hero__sub reveal d2">{{{body_html}}}</p>
          <div class="st-hero__cta reveal d3">
            <a class="st-btn st-btn--solid" href="{{button_url}}">{{button_text}}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            <a class="st-btn st-btn--wa" href="{{button_url_2}}" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#062b14" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 4 1.7 4 1.2 4.7 1.1.7-.1 2.4-1 2.7-1.9.3-1 .3-1.8.2-1.9z"/></svg>
              {{button_text_2}}</a>
          </div>
          <div class="st-hero__meta reveal d4">
            <div><small>{{group_1_text}}</small><b>{{group_1_text_2}}</b></div>
            <div><small>{{group_2_text}}</small><b>{{group_2_text_2}}</b></div>
            <div><small>{{group_3_text}}</small><b>{{group_3_text_2}}</b></div>
          </div>
        </div>

        <div class="st-hv reveal d2">
          <div class="st-hv__glow" aria-hidden="true"></div>
          <div class="st-hv__card">
            <div class="st-hv__img" role="img" aria-label="RRS skinbooster ampul ve serum şişesinin klinik laboratuvar çekimi">
              <span class="st-hv__tag">{{text_2}}</span>
            </div>
            <div class="st-hv__cap">
              <div>
                <small>{{text_3}}</small>
                <b>{{text_4}}</b>
              </div>
              <span class="st-ce" aria-label="CE Class III"><span><b>{{text_5}}</b><small>{{text_6}}</small></span></span>
            </div>
          </div>
          <div class="st-hv__float" aria-hidden="true">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2a7 7 0 0 1 7 7c0 1.6-.4 3-1 4l-6 9-6-9c-.6-1-1-2.4-1-4a7 7 0 0 1 7-7z"/><path d="M9 9l2 2 4-4"/></svg></span>
            <div><small>{{text_7}}</small><b>{{text_8}}</b></div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "text": {"type": "textarea", "label": "Text"}, "title_2": {"type": "text", "label": "Title 2"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "text_4": {"type": "textarea", "label": "Text 4"}, "text_5": {"type": "textarea", "label": "Text 5"}, "text_6": {"type": "textarea", "label": "Text 6"}, "text_7": {"type": "textarea", "label": "Text 7"}, "text_8": {"type": "textarea", "label": "Text 8"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400..600;1,6..72,400..500&family=Inter:wght@400;500;600;700&display=swap", "button_url": "#urunler", "button_url_2": "https://wa.me/905426205100", "subtitle": "İspanya · Klinik Dermokozmetik", "title": "Skin Tech Pharma Group.", "text": "Klinik kanıtın", "title_2": "otoritesi.", "body_html": "Kimyasal peeling, mezoterapi ve RRS® skinbooster serisinde dünya çapında öncü. Laboratuvar disiplini, dermatolojik kanıt ve CE Class III standartlarıyla geliştirilen profesyonel çözümler.", "button_text": "Ürünleri İncele", "button_text_2": "Bilgi Al — WhatsApp", "group_1_text": "Sertifikasyon", "group_1_text_2": "CE Class III", "group_2_text": "Menşei", "group_2_text_2": "İspanya", "group_3_text": "Portföy", "group_3_text_2": "84+ Ürün", "text_2": "RRS® Skinbooster", "text_3": "Skin Tech · RRS", "text_4": "RRS® HA Long Lasting", "text_5": "CE", "text_6": "CLASS III", "text_7": "Dermatolojik", "text_8": "Klinik Test Edildi"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-skintech-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'2 · MARKA HİKAYESİ',
             'html_template'=><<<'EDHTML'
<!-- 2 · MARKA HİKAYESİ -->

    <section class="st-sec st-sec--paper" aria-labelledby="st-story-h">
      <div class="st-wrap st-story">
        <div class="st-story__media reveal">
          <div class="st-story__img" role="img" aria-label="Skin Tech Pharma Group dermatolojik laboratuvar ve üretim ortamı"></div>
          <div class="st-story__quote">
            <span aria-hidden="true">{{text}}</span>
            <p>{{subtitle}}</p>
          </div>
        </div>
        <div class="reveal d1">
          <span class="st-kick">{{text_2}}</span>
          <h2 id="st-story-h" style="font-size:clamp(28px,3.8vw,44px);line-height:1.1;color:var(--navy);margin:18px 0 22px;">{{title}}</h2>
          <p class="lead">{{group_1_subtitle}} <strong>{{group_1_text}}</strong> {{{group_1_body_html}}}</p>
          <p class="lead">{{group_2_subtitle}} <strong>{{group_2_text}}</strong>{{{group_2_body_html}}}</p>
          <p style="margin:26px 0 0;"><a class="st-link" href="{{button_url}}">{{button_text}}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></p>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "text_2": {"type": "textarea", "label": "Text 2"}, "title": {"type": "text", "label": "Title"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_body_html": {"type": "textarea", "label": "Group 1 Body"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "/markalar", "text": "“", "subtitle": "Bilim, formülasyonun her aşamasında kanıtla doğrulanır.", "text_2": "Marka Hikayesi", "title": "İspanya'dan, dermatolojinin diliyle yazılan bir bilim", "group_1_subtitle": "Skin Tech Pharma Group, İspanya merkezli laboratuvarlarında", "group_1_text": "kimyasal peeling, mezoterapi ve skinbooster", "group_1_body_html": "alanlarında dermatolojik kanıta dayalı profesyonel çözümler geliştirir. Easy Phytic ve RRS® gibi referans formülleriyle dünya genelinde hekimlerin güvendiği bir klinik otoritedir.", "group_2_subtitle": "Türkiye'de", "group_2_text": "Estetik Dermal", "group_2_body_html": "; 2004'ten bu yana taşıdığı medikal estetik birikimiyle bu portföyün resmi temsilcisidir. Yalnızca tedarik değil; hekimlere uygulamalı eğitim, protokol desteği ve sürekli teknik danışmanlık sunar.", "button_text": "Tüm temsil ettiğimiz markalar"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-skintech-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'3 · KREDİBİLİTE ŞERİDİ',
             'html_template'=><<<'EDHTML'
<!-- 3 · KREDİBİLİTE ŞERİDİ -->

    <section class="st-cred" aria-label="Kredibilite ve sertifikasyon">
      <div class="st-wrap" style="padding-inline:0;">
        <div class="st-cred__grid">
          <div class="st-cred__item reveal">
            <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2 4 5v6c0 5 3.5 8.5 8 11 4.5-2.5 8-6 8-11V5z"/><path d="M9 12l2 2 4-4"/></svg></span>
            <b>{{text}}</b><small>{{text_2}}</small>
          </div>
          <div class="st-cred__item reveal d1">
            <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 2h6M10 2v6L5 18a2 2 0 0 0 1.8 3h10.4A2 2 0 0 0 19 18L14 8V2"/><path d="M7.5 14h9"/></svg></span>
            <b>{{text_3}}</b><small>{{text_4}}</small>
          </div>
          <div class="st-cred__item reveal d2">
            <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M2 12h20M12 3c2.5 2.4 4 5.6 4 9s-1.5 6.6-4 9c-2.5-2.4-4-5.6-4-9s1.5-6.6 4-9z"/></svg></span>
            <b>{{text_5}}</b><small>{{text_6}}</small>
          </div>
          <div class="st-cred__item reveal d3">
            <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/></svg></span>
            <b>{{text_7}}</b><small>{{text_8}}</small>
          </div>
        </div>
        <p class="st-cred__note reveal">{{subtitle}} <b>{{text_9}}</b> {{subtitle_2}}</p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "text_4": {"type": "textarea", "label": "Text 4"}, "text_5": {"type": "textarea", "label": "Text 5"}, "text_6": {"type": "textarea", "label": "Text 6"}, "text_7": {"type": "textarea", "label": "Text 7"}, "text_8": {"type": "textarea", "label": "Text 8"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "text_9": {"type": "textarea", "label": "Text 9"}, "subtitle_2": {"type": "textarea", "label": "Subtitle 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"text": "CE Class III", "text_2": "Tıbbi cihaz sınıfı sertifikası", "text_3": "İspanya Menşeli", "text_4": "Avrupa üretim ve kalite standardı", "text_5": "Dünya Lideri", "text_6": "Peeling ve skinbooster alanında öncü", "text_7": "84+ Ürün", "text_8": "Geniş profesyonel klinik portföy", "subtitle": "Türkiye distribütörü:", "text_9": "Estetik Dermal", "subtitle_2": "· Resmi temsilci · 2004'ten bu yana"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-skintech-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'4 · ÖNE ÇIKAN ÜRÜNLER (split)',
             'html_template'=><<<'EDHTML'
<!-- 4 · ÖNE ÇIKAN ÜRÜNLER (split) -->

    <section class="st-sec st-sec--paper" id="urunler" aria-labelledby="st-feat-h">
      <div class="st-wrap">
        <div class="st-head reveal">
          <span class="st-kick">{{text}}</span>
          <h2 id="st-feat-h">{{title}}</h2>
          <p>{{description}}</p>
        </div>
        <div class="st-split">
          <article class="st-feat st-feat--a reveal">
            <div class="st-feat__img" role="img" aria-label="RRS HA Long Lasting çapraz bağlı hyalüronik asit skinbooster ürün çekimi">
              <span class="st-feat__badge"><span class="st-chip"><span class="dot" aria-hidden="true"></span>{{text_2}}</span></span>
            </div>
            <div class="st-feat__body">
              <span class="st-kick">{{text_3}}</span>
              <h3>{{title_2}}</h3>
              <span class="st-chip"><span class="dot" aria-hidden="true"></span>{{text_4}}</span>
              <p>{{{body_html}}}</p>
              <a class="st-link" href="{{button_url}}">{{button_text}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </div>
          </article>
          <article class="st-feat st-feat--b reveal d1">
            <div class="st-feat__img" role="img" aria-label="Melablock HSP SPF 50+ yüksek faktörlü güneş koruma ürünü çekimi">
              <span class="st-feat__badge"><span class="st-chip"><span class="dot" aria-hidden="true"></span>{{text_5}}</span></span>
            </div>
            <div class="st-feat__body">
              <span class="st-kick">{{text_6}}</span>
              <h3>{{title_3}}</h3>
              <span class="st-chip"><span class="dot" aria-hidden="true"></span>{{text_7}}</span>
              <p>{{{body_html_2}}}</p>
              <a class="st-link" href="{{button_url_2}}">{{button_text_2}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </div>
          </article>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "title_2": {"type": "text", "label": "Title 2"}, "text_4": {"type": "textarea", "label": "Text 4"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}, "text_5": {"type": "textarea", "label": "Text 5"}, "text_6": {"type": "textarea", "label": "Text 6"}, "title_3": {"type": "text", "label": "Title 3"}, "text_7": {"type": "textarea", "label": "Text 7"}, "body_html_2": {"type": "textarea", "label": "Body Html 2"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "/urun-detay", "button_url_2": "/urun-detay", "text": "Öne Çıkan Ürünler", "title": "Portföyün referans iki ürünü", "description": "Birinde dermal implant disiplini, diğerinde 360° fotokoruma — ikisi de klinik protokollerin temel taşı.", "text_2": "CE Class III", "text_3": "Skinbooster · RRS®", "title_2": "RRS® HA Long Lasting", "text_4": "Çapraz bağlı HA · Dermal implant", "body_html": "Çapraz bağlı, emilebilir hyalüronik asit içeren CE Class III dermal implant. Amino asit içeren koruyucu tampon solüsyonunda; cilt kalitesini içten destekleyen uzun etkili skinbooster.", "button_text": "Ürün detayı", "text_5": "SPF 50+", "text_6": "Fotokoruma · Premium", "title_3": "Melablock HSP SPF 50+", "text_7": "360° fotokoruma · Leke savunması", "body_html_2": "Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktörlü formül; lazer ve peeling sonrası termal hasara karşı dermatolojik savunma sağlar.", "button_text_2": "Ürün detayı"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-skintech-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'5 · ÜRÜN YELPAZESİ (grid)',
             'html_template'=><<<'EDHTML'
<!-- 5 · ÜRÜN YELPAZESİ (grid) -->

    <section class="st-sec st-sec--ice" aria-labelledby="st-range-h">
      <div class="st-wrap">
        <div class="st-head st-head--center reveal">
          <span class="st-kick" style="margin-inline:auto;">{{text}}</span>
          <h2 id="st-range-h">{{title}}</h2>
          <p>{{{body_html}}}</p>
        </div>
        <div class="st-fam">

          {{{st_fam__col_items_html}}}</div>
        <p style="text-align:center;margin:44px 0 0;" class="reveal">
          <a class="st-btn st-btn--ghost" href="{{button_url}}">{{button_text}}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </p>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"st_fam__col_items": {"type": "repeater", "label": "St Fam  Col Items", "repeat_kind": "items", "item_template": "<div class=\"st-fam__col reveal\">\n            <div class=\"st-fam__h\"><span class=\"tag\">{{text}}</span><h3>{{title}}</h3></div>\n            <div class=\"st-fam__list\">\n              <a class=\"st-prod\" href=\"{{button_url}}\"><span><b>{{text_2}}</b><small>{{text_3}}</small></span><span class=\"arr\" aria-hidden=\"true\">{{text_4}}</span></a>\n              <a class=\"st-prod\" href=\"{{button_url_2}}\"><span><b>{{text_5}}</b><small>{{text_6}}</small></span><span class=\"arr\" aria-hidden=\"true\">{{text_7}}</span></a>\n            </div>\n          </div>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "text_4": {"type": "textarea", "label": "Text 4"}, "text_5": {"type": "textarea", "label": "Text 5"}, "text_6": {"type": "textarea", "label": "Text 6"}, "text_7": {"type": "textarea", "label": "Text 7"}}}, "button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"st_fam__col_items": [{"button_url": "/urun-detay", "button_url_2": "/urun-detay", "text": "Skinbooster", "title": "RRS®", "text_2": "RRS® HA Long Lasting", "text_3": "Çapraz bağlı HA · CE III", "text_4": "→", "text_5": "RRS® HA Cellular", "text_6": "Hücresel revitalizasyon", "text_7": "→"}, {"button_url": "/urun-detay", "button_url_2": "/urun-detay", "button_url_3": "/urun-detay", "text": "Mezoterapi", "title": "Benebellum", "text_2": "Lumina Vit-C 18%", "text_3": "Aydınlatıcı C vitamini", "text_4": "→", "text_5": "Vit A + E", "text_6": "Antioksidan onarım", "text_7": "→", "text_8": "TX Solution", "text_9": "Leke karşıtı çözüm", "text_10": "→"}, {"button_url": "/urun-detay", "button_url_2": "/urun-detay", "text": "Peeling", "title": "Kimyasal Peeling", "text_2": "Aclaranse", "text_3": "Depigmentasyon peelingi", "text_4": "→", "text_5": "Easy Phytic", "text_6": "Nötralizasyonsuz fitik asit", "text_7": "→"}, {"button_url": "/urun-detay", "button_url_2": "/urun-detay", "text": "Bakım & SPF", "title": "Kremler", "text_2": "Melablock HSP SPF 50+", "text_3": "Yüksek faktör · leke koruması", "text_4": "→", "text_5": "Actilift Krem", "text_6": "Sıkılaştırıcı bakım", "text_7": "→"}], "button_url": "/urunler", "text": "Ürün Yelpazesi", "title": "Bilim temelli dört temel seri", "body_html": "Skinbooster'dan mezoterapiye, kimyasal peelingden güneş korumaya — her seri dermatolojik kanıt ve klinik standartla geliştirildi.", "button_text": "Tüm Skin Tech ürünlerini gör"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-skintech-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'6 · DOKTORLAR İÇİN / UYGULAMA',
             'html_template'=><<<'EDHTML'
<!-- 6 · DOKTORLAR İÇİN / UYGULAMA -->

    <section class="st-sec st-sec--paper" aria-labelledby="st-doc-h">
      <div class="st-wrap">
        <div class="st-doc reveal">
          <div class="st-doc__lines" aria-hidden="true"></div>
          <div class="st-doc__in">
            <span class="st-kick st-kick--light">{{text}}</span>
            <h2 id="st-doc-h">{{title}}</h2>
            <p class="st-doc__lead">{{{body_html}}}</p>
            <div class="st-doc__cards">
              <div class="st-doc__card">
                <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></span>
                <h4>{{item_1_eyebrow}}</h4>
                <p>{{item_1_description}}</p>
              </div>
              <div class="st-doc__card">
                <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V5a2 2 0 0 1 2-2h9l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><path d="M14 3v5h5M8 13h8M8 17h5"/></svg></span>
                <h4>{{item_2_eyebrow}}</h4>
                <p>{{item_2_description}}</p>
              </div>
              <div class="st-doc__card">
                <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 14h2"/></svg></span>
                <h4>{{item_3_eyebrow}}</h4>
                <p>{{item_3_description}}</p>
              </div>
            </div>
            <div style="display:flex;gap:14px;flex-wrap:wrap;">
              <a class="st-btn st-btn--lightline" href="{{group_1_button_url}}">{{group_1_button_text}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
              <a class="st-btn st-btn--lightline" href="{{group_2_button_url}}" target="_blank" rel="noopener">{{group_2_button_text}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8"/></svg></a>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": {"type": "text", "label": "Group 1 Button Url"}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "item_1_eyebrow": {"type": "text", "label": "Item 1 Eyebrow"}, "item_1_description": {"type": "textarea", "label": "Item 1 Description"}, "item_2_eyebrow": {"type": "text", "label": "Item 2 Eyebrow"}, "item_2_description": {"type": "textarea", "label": "Item 2 Description"}, "item_3_eyebrow": {"type": "text", "label": "Item 3 Eyebrow"}, "item_3_description": {"type": "textarea", "label": "Item 3 Description"}, "group_1_button_text": {"type": "textarea", "label": "Group 1 Button Text"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_button_url": "/etkinlikler", "group_2_button_url": "https://apps.skintechpharmagroup.com/medinet", "text": "Doktorlar İçin · Klinik Kullanım", "title": "Sadece tedarik değil; protokolün her adımında yanınızda", "body_html": "Skin Tech ürünlerinin klinikte doğru ve güvenli kullanımı için Estetik Dermal; uygulamalı eğitim, protokol kurulumu ve sürekli teknik destek sunar. Tüm içerik yalnızca profesyonel hekim kullanımına yöneliktir.", "item_1_eyebrow": "Uygulamalı Eğitim", "item_1_description": "Hekimlere birebir, uygulamalı ürün ve teknik kullanım eğitimleri.", "item_2_eyebrow": "Protokol Desteği", "item_2_description": "Doğru endikasyon, dozaj ve uygulama protokolü kurulumu için rehberlik.", "item_3_eyebrow": "MEDINET Platformu", "item_3_description": "Dijital sipariş, takip ve ürün bilgisine erişim için MEDINET portalı.", "group_1_button_text": "Eğitim ve etkinlikler", "group_2_button_text": "MEDINET Portalı"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-skintech-6'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'7 · CTA BANDI',
             'html_template'=><<<'EDHTML'
<!-- 7 · CTA BANDI -->

    <section class="st-sec st-sec--paper" style="padding-top:0;" id="iletisim" aria-labelledby="st-cta-h">
      <div class="st-wrap">
        <div class="st-cta reveal">
          <span class="st-cta__orb" aria-hidden="true" style="top:-50px;right:-50px;width:220px;height:220px;"></span>
          <span class="st-cta__orb" aria-hidden="true" style="bottom:-70px;left:-40px;width:260px;height:260px;background:rgba(127,179,232,.09);"></span>
          <span class="st-kick st-kick--light" style="justify-content:center;">{{text}}</span>
          <h2 id="st-cta-h" style="margin-top:16px;">{{title}}</h2>
          <p>{{{body_html}}}</p>
          <div class="st-cta__row">
            <a class="st-btn st-btn--wa" href="{{button_url}}" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#062b14" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 4 1.7 4 1.2 4.7 1.1.7-.1 2.4-1 2.7-1.9.3-1 .3-1.8.2-1.9z"/></svg>
              {{button_text}}</a>
            <a class="st-btn st-btn--lightline" href="{{button_url_2}}">{{button_text_2}}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "button_url_2": "/iletisim", "text": "İletişim", "title": "Skin Tech ürünleri hakkında bilgi alın", "body_html": "Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da iletişim sayfamızdan bize ulaşın.", "button_text": "WhatsApp ile Yaz", "button_text_2": "İletişim Sayfası"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-seffiline-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'1. HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><style>
    /* ============================================================
       SEFFILINE — özgün, zarif, sıcak minimal kozmesötik palet.
       NOT: global v2 :root EZİLMEZ; tüm marka token'ları .sf
       scope'unda tutulur ki nav/footer turuncu kimliği korunsun.
       ============================================================ */
    .sf {
      --rose:    #C98A6D;   /* rose-gold */
      --blush:   #E8A0A8;   /* blush pembe */
      --cream:   #FBF0EF;   /* krem */
      --plum:    #4A2E35;   /* koyu erik metin */
      --plum-soft:#8A6A70;  /* yumuşak erik */
      --line:    #EBD9D6;   /* yumuşak çizgi */
      --paper:   #FFFCFB;   /* sıcak beyaz zemin */
      --serif: "Cormorant Garamond", "Fraunces", Georgia, "Times New Roman", serif;
      --grad-blush: linear-gradient(160deg, #FBE7E6 0%, #FBF0EF 55%, #FFFFFF 100%);
      --grad-rose:  linear-gradient(135deg, #C98A6D 0%, #E8A0A8 100%);
      --shadow-petal: 0 22px 56px rgba(201, 138, 109, .15);
      --shadow-soft:  0 8px 26px rgba(74, 46, 53, .08);
      --wa: #25D366;

      font-family: "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
      color: var(--plum);
      background: var(--paper);
    }
    .sf ::selection { background: #F3CFC9; color: #4A2E35; }

    /* yapı */
    .sf-wrap { width: min(1180px, 92%); margin-inline: auto; }
    .sf-serif { font-family: var(--serif); }
    .sf-eyebrow {
      display: inline-flex; align-items: center; gap: 10px;
      color: var(--rose); font-size: 12px; font-weight: 700;
      letter-spacing: 3px; text-transform: uppercase; margin: 0 0 22px;
    }
    .sf-eyebrow::before { content: ""; width: 26px; height: 1px; background: currentColor; }
    .sf-eyebrow--c { justify-content: center; }
    .sf-eyebrow--blush { color: var(--blush); }

    /* butonlar */
    .sf-btn {
      display: inline-flex; align-items: center; gap: 9px;
      padding: 16px 34px; border-radius: 999px;
      font-weight: 600; font-size: 15px; letter-spacing: .3px;
      transition: transform .22s ease, box-shadow .22s ease, background .22s ease, color .22s ease;
      will-change: transform;
    }
    .sf-btn-fill { background: var(--grad-rose); color: #fff; box-shadow: 0 14px 32px rgba(201,138,109,.30); }
    .sf-btn-fill:hover { transform: translateY(-2px); box-shadow: 0 18px 40px rgba(201,138,109,.38); color: #fff; }
    .sf-btn-wa { background: var(--wa); color: #fff; box-shadow: 0 12px 28px rgba(37,211,102,.26); }
    .sf-btn-wa:hover { transform: translateY(-2px); box-shadow: 0 16px 36px rgba(37,211,102,.34); color: #fff; }
    .sf-btn-ghost { background: var(--cream); color: var(--plum); border: 1px solid var(--line); }
    .sf-btn-ghost:hover { transform: translateY(-2px); background: #fff; border-color: var(--rose); }

    .sf-link { color: var(--rose); font-weight: 600; font-size: 14px; letter-spacing: .3px; transition: color .2s ease; }
    .sf-link:hover { color: var(--blush); }

    /* kart hover */
    .sf-card { transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease; }
    .sf-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-petal); border-color: #E3C5C0; }

    /* zarif yumuşak fade reveal (kendi motion) */
    .sf [data-sf] { opacity: 0; transform: translateY(22px); transition: opacity .9s cubic-bezier(.22,.61,.36,1), transform .9s cubic-bezier(.22,.61,.36,1); }
    .sf [data-sf].in { opacity: 1; transform: none; }
    .sf [data-sf].d1 { transition-delay: .09s; }
    .sf [data-sf].d2 { transition-delay: .18s; }
    .sf [data-sf].d3 { transition-delay: .27s; }
    @media (prefers-reduced-motion: reduce) {
      .sf [data-sf] { opacity: 1 !important; transform: none !important; transition: none !important; }
    }

    /* hero görsel kompozisyon */
    .sf-hero { position: relative; overflow: hidden; background: var(--grad-blush); }
    .sf-blob { position: absolute; border-radius: 50%; pointer-events: none; }
    .sf-photo {
      aspect-ratio: 3 / 4; background-size: cover; background-position: center;
      background-repeat: no-repeat;
    }

    /* ürün ailesi grid */
    .sf-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(238px, 1fr)); gap: 26px; }
    .sf-prodcard {
      display: block; background: #fff; border: 1px solid var(--line);
      border-radius: 28px; padding: 34px 30px; height: 100%;
    }
    .sf-ico {
      width: 64px; height: 64px; border-radius: 50%; margin-bottom: 22px;
      display: grid; place-items: center; color: #fff;
      background: var(--grad-rose); box-shadow: 0 10px 24px rgba(201,138,109,.26);
    }
    .sf-ico svg { width: 30px; height: 30px; }

    /* split (öne çıkan) */
    .sf-split { display: flex; flex-wrap: wrap; gap: 64px; align-items: center; position: relative; }
    .sf-split .sf-col { flex: 1 1 380px; }
    .sf-feat-list { list-style: none; margin: 0 0 32px; padding: 0; display: grid; gap: 14px; }
    .sf-feat-list li { display: flex; align-items: center; gap: 12px; color: var(--plum); font-weight: 500; }
    .sf-feat-list .pet { color: var(--blush); font-size: 16px; line-height: 1; }

    /* kredibilite şeridi */
    .sf-cred { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 1px; background: var(--line); border: 1px solid var(--line); border-radius: 28px; overflow: hidden; }
    .sf-cred > div { background: #fff; padding: 34px 28px; }
    .sf-cred h4 { font-size: 16px; color: var(--plum); margin: 0 0 8px; font-weight: 600; }
    .sf-cred p { font-size: 13.5px; color: var(--plum-soft); line-height: 1.7; margin: 0; }

    /* doktorlar için kart */
    .sf-docgrid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px,1fr)); gap: 24px; }
    .sf-doc { background: var(--cream); border: 1px solid var(--line); border-radius: 24px; padding: 30px 28px; }
    .sf-doc h4 { font-size: 17px; color: var(--plum); margin: 16px 0 8px; font-weight: 600; }
    .sf-doc p { font-size: 14px; color: var(--plum-soft); line-height: 1.7; margin: 0; }
  </style><!-- 1. HERO -->

    <section class="sf-hero">
      {{{sf_blob_items_html}}}<div class="sf-wrap" style="position:relative;display:flex;flex-wrap:wrap;gap:56px;align-items:center;padding:clamp(72px,8vw,100px) 0 clamp(80px,9vw,108px);">
        <div style="flex:1 1 460px;">
          <p class="sf-eyebrow" data-sf>{{subtitle}}</p>
          <h1 class="sf-serif" data-sf style="font-size:clamp(42px,6vw,74px);line-height:1.05;font-weight:500;color:var(--plum);margin:0 0 26px;">{{title}}<br><em style="font-style:italic;color:var(--rose);">{{text}}</em></h1>
          <p data-sf class="d1" style="font-size:clamp(16px,2vw,19px);color:var(--plum-soft);line-height:1.85;max-width:520px;margin:0 0 38px;">{{{body_html}}}</p>
          <div data-sf class="d2" style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;">
            <a href="{{button_url}}" class="sf-btn sf-btn-fill">{{button_text}}</a>
            <a href="{{button_url_2}}" target="_blank" rel="noopener" class="sf-btn sf-btn-wa">
              <svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
              {{button_text_2}}</a>
          </div>
        </div>

        <!-- 🖼️ GÖRSEL: /assets/img/seffiline-hero.jpg (oran 3:4)
             PROMPT: "Feminine editorial beauty composition, elegant skincare and dermal filler bottles arranged with fresh blush-pink petals, soft rose-gold and cream tones, luxury magazine aesthetic, diffused soft natural light, delicate silk fabric backdrop, refined and graceful, glowing warm highlights, high-fashion cosmetics editorial; photorealistic, detailed, high resolution; no text, no logo, no watermark"
             image-ready: .sf-photo bloğunun background-image'i hazır; gerçek görsel dosyası geldiğinde otomatik gösterilir. -->
        <div data-sf class="d1" style="position:relative;flex:1 1 340px;min-height:460px;">
          <div style="position:relative;background:#fff;border:1px solid var(--line);border-radius:200px 200px 24px 24px;box-shadow:var(--shadow-petal);padding:28px;max-width:380px;margin:0 auto;">
            <div class="sf-photo" style="border-radius:170px 170px 18px 18px;background-color:#F4D4CE;background-image:linear-gradient(165deg,rgba(251,231,230,.35),rgba(239,201,194,.35)),url('/assets/img/seffiline-hero.jpg');"></div>
          </div>
          <div style="position:absolute;top:18px;left:-6px;background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow-soft);padding:14px 18px;display:flex;align-items:center;gap:11px;">
            <span style="width:34px;height:34px;flex-shrink:0;border-radius:50%;background:var(--grad-rose);"></span>
            <div><div style="font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--rose);font-weight:700;">{{group_2_text}}</div><div class="sf-serif" style="font-size:16px;color:var(--plum);">{{group_2_text_2}}</div></div>
          </div>
          <div style="position:absolute;bottom:24px;right:-8px;background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow-soft);padding:14px 18px;display:flex;align-items:center;gap:11px;">
            <span style="width:34px;height:34px;flex-shrink:0;border-radius:50%;background:var(--grad-rose);"></span>
            <div><div style="font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--rose);font-weight:700;">{{group_3_text}}</div><div class="sf-serif" style="font-size:16px;color:var(--plum);">{{group_3_text_2}}</div></div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"sf_blob_items": {"type": "repeater", "label": "Sf Blob Items", "repeat_kind": "items", "item_template": "<div class=\"sf-blob\" aria-hidden=\"true\" style=\"top:-160px;right:-120px;width:480px;height:480px;background:radial-gradient(circle at 35% 35%, rgba(232,160,168,.32), transparent 70%);\"></div>", "fields": {}}, "group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "text": {"type": "textarea", "label": "Text"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"sf_blob_items": [{}, {}, {}], "group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap", "button_url": "#koleksiyon", "button_url_2": "https://wa.me/905426205100", "subtitle": "Güzelliğin İnce Dokunuşu", "title": "Cildinize özel", "text": "bütüncül bakım", "body_html": "Seffiline; cilt, saç, intim bakım ve dolgu çözümlerinde feminen ve profesyonel bir kozmesötik seri. Kadın sağlığı ve güzelliğine, zarafetle ve bilimle yaklaşır.", "button_text": "Koleksiyonu İncele", "button_text_2": "Bilgi Al — WhatsApp", "group_2_text": "Seffiller", "group_2_text_2": "Dolgu Serisi", "group_3_text": "SeffiCare", "group_3_text_2": "Cilt Bakımı"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-seffiline-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'2. MARKA HİKAYESİ',
             'html_template'=><<<'EDHTML'
<!-- 2. MARKA HİKAYESİ -->

    <section style="padding:clamp(72px,9vw,104px) 0;background:var(--paper);">
      <div class="sf-wrap" style="max-width:820px;text-align:center;">
        <p class="sf-eyebrow sf-eyebrow--c sf-eyebrow--blush" data-sf>{{subtitle}}</p>
        <p class="sf-serif" data-sf style="font-size:clamp(24px,3.4vw,36px);line-height:1.55;font-weight:400;color:var(--plum);margin:0 0 14px;font-style:italic;">{{{body_html}}}</p>
        <p data-sf class="d1" style="color:var(--plum-soft);font-size:17px;line-height:1.85;max-width:640px;margin:24px auto 36px;">{{{body_html_2}}}</p>
        <div data-sf class="d1" style="display:flex;align-items:center;justify-content:center;gap:14px;margin:0 auto;max-width:240px;">
          <span style="flex:1;height:1px;background:linear-gradient(90deg,transparent,var(--line));"></span>
          <span style="color:var(--rose);font-size:18px;">{{group_2_text}}</span>
          <span style="flex:1;height:1px;background:linear-gradient(90deg,var(--line),transparent);"></span>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"subtitle": {"type": "textarea", "label": "Subtitle"}, "body_html": {"type": "textarea", "label": "Body"}, "body_html_2": {"type": "textarea", "label": "Body Html 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"subtitle": "Marka Hikayesi", "body_html": "\"Her kadının güzelliği biriciktir. Seffiline, kadın sağlığı ve güzelliğine bütüncül bir yaklaşımla; cildi, saçı ve hassas bölgeleri aynı özen ve zarafetle ele alır.\"", "body_html_2": "Bilim ile zarafeti aynı şişede buluşturan Seffiline; gündelik bakımdan profesyonel uygulamalara uzanan, baştan ayağa bütüncül bir güzellik ritüeli sunar. Yumuşak, sıcak ve incelikli — tıpkı kendisine değer veren bir kadının dokunuşu gibi.", "group_2_text": "❀"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-seffiline-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'3. KREDİBİLİTE ŞERİDİ',
             'html_template'=><<<'EDHTML'
<!-- 3. KREDİBİLİTE ŞERİDİ -->

    <section style="padding:0 0 clamp(56px,7vw,88px);background:var(--paper);">
      <div class="sf-wrap">
        <div class="sf-cred" data-sf>
          <div>
            <span class="sf-ico" style="width:46px;height:46px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2 4 6v6c0 5 3.4 8.6 8 10 4.6-1.4 8-5 8-10V6z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <h4>{{group_1_eyebrow}}</h4>
            <p>{{group_1_description}}</p>
          </div>
          <div>
            <span class="sf-ico" style="width:46px;height:46px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg></span>
            <h4>{{group_2_eyebrow}}</h4>
            <p>{{group_2_subtitle}}</p>
          </div>
          <div>
            <span class="sf-ico" style="width:46px;height:46px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="5"/><path d="M12 13v8M9 18h6"/></svg></span>
            <h4>{{group_3_eyebrow}}</h4>
            <p>{{group_3_subtitle}}</p>
          </div>
          <div>
            <span class="sf-ico" style="width:46px;height:46px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg></span>
            <h4>{{group_4_eyebrow}}</h4>
            <p>{{group_4_subtitle}}</p>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_eyebrow": {"type": "text", "label": "Group 1 Eyebrow"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_eyebrow": {"type": "text", "label": "Group 2 Eyebrow"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}, "group_3_eyebrow": {"type": "text", "label": "Group 3 Eyebrow"}, "group_3_subtitle": {"type": "textarea", "label": "Group 3 Subtitle"}, "group_4_eyebrow": {"type": "text", "label": "Group 4 Eyebrow"}, "group_4_subtitle": {"type": "textarea", "label": "Group 4 Subtitle"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_eyebrow": "%100 Orijinal", "group_1_description": "Her ürün sertifikalı, takip edilebilir ve tamamen orijinaldir.", "group_2_eyebrow": "Estetik Dermal Güvencesi", "group_2_subtitle": "Resmi distribütör güvencesiyle, doğru kaynaktan tedarik.", "group_3_eyebrow": "20+ Yıl Deneyim", "group_3_subtitle": "2004'ten bu yana medikal estetikte birikmiş uzmanlık.", "group_4_eyebrow": "Bütüncül Bakım", "group_4_subtitle": "Cilt, saç, intim ve dolgu — tek bir feminen seri çatısında."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-seffiline-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'4a. ÖNE ÇIKAN SPLIT: SeffiHair',
             'html_template'=><<<'EDHTML'
<!-- 4a. ÖNE ÇIKAN SPLIT: SeffiHair -->

    <section style="padding:clamp(72px,9vw,100px) 0;background:var(--cream);position:relative;overflow:hidden;">
      <div class="sf-blob" aria-hidden="true" style="top:-120px;right:8%;width:300px;height:300px;background:radial-gradient(circle,rgba(232,160,168,.22),transparent 70%);"></div>
      <div class="sf-wrap sf-split" style="position:relative;">
        <!-- 🖼️ GÖRSEL: /assets/img/seffiline-seffihair.jpg (oran 3:4)
             PROMPT: "Editorial feminine hair care beauty shot, a woman's healthy glossy flowing hair with a hair mesotherapy serum vial, soft rose-gold and blush tones, luxury salon aesthetic, warm diffused light, silky elegant mood, glowing voluminous hair, high-fashion hair editorial; photorealistic, detailed, high resolution; no text, no logo, no watermark" -->
        <div class="sf-col" data-sf style="position:relative;min-height:420px;">
          <div style="position:relative;background:#fff;border:1px solid var(--line);border-radius:24px 24px 200px 200px;box-shadow:var(--shadow-petal);padding:28px;max-width:400px;margin:0 auto;">
            <div class="sf-photo" style="border-radius:18px 18px 180px 180px;background-color:#EFC9C2;background-image:linear-gradient(180deg,rgba(246,216,212,.4),rgba(230,184,176,.4)),url('/assets/img/seffiline-seffihair.jpg');"></div>
          </div>
        </div>
        <div class="sf-col d1" data-sf>
          <p class="sf-eyebrow">{{subtitle}}</p>
          <h2 class="sf-serif" style="font-size:clamp(30px,4.4vw,46px);font-weight:500;color:var(--plum);margin:0 0 22px;line-height:1.12;">{{title}}<br><em style="font-style:italic;color:var(--rose);">{{text}}</em></h2>
          <p style="color:var(--plum-soft);font-size:17px;line-height:1.85;margin:0 0 28px;max-width:520px;">{{{body_html}}}</p>
          <ul class="sf-feat-list">
            <li><span class="pet">{{group_1_text}}</span> {{group_1_item_text}}</li>
            <li><span class="pet">{{group_2_text}}</span> {{group_2_item_text}}</li>
            <li><span class="pet">{{group_3_text}}</span> {{group_3_item_text}}</li>
          </ul>
          <a href="{{button_url}}" class="sf-btn sf-btn-fill">{{button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "text": {"type": "textarea", "label": "Text"}, "body_html": {"type": "textarea", "label": "Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "/urun-detay", "subtitle": "Öne Çıkan · Saç Mezoterapi", "title": "SeffiHair ile", "text": "kökten güçlü saçlar", "body_html": "Saç dökülmesiyle mücadelede mezoterapi temelli bir yaklaşım. SeffiHair serisi, saç köküne ihtiyaç duyduğu vitamin ve mineralleri ileterek folikülleri besler; daha sağlıklı, dolgun ve canlı bir görünüm için zarif bir bakım ritüeli sunar.", "group_1_text": "❀", "group_1_item_text": "Saç köküne yoğun besin desteği", "group_2_text": "❀", "group_2_item_text": "Mezoterapi ile uyumlu profesyonel formül", "group_3_text": "❀", "group_3_item_text": "Dolgun ve canlı bir saç görünümü", "button_text": "SeffiHair'i İncele →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-seffiline-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'4b. ÖNE ÇIKAN SPLIT: Seffiller (ters)',
             'html_template'=><<<'EDHTML'
<!-- 4b. ÖNE ÇIKAN SPLIT: Seffiller (ters) -->

    <section style="padding:clamp(72px,9vw,100px) 0;background:var(--paper);position:relative;overflow:hidden;">
      <div class="sf-blob" aria-hidden="true" style="bottom:-120px;left:6%;width:300px;height:300px;background:radial-gradient(circle,rgba(201,138,109,.18),transparent 70%);"></div>
      <div class="sf-wrap sf-split" style="position:relative;flex-direction:row-reverse;">
        <!-- 🖼️ GÖRSEL: /assets/img/seffiline-seffiller.jpg (oran 3:4)
             PROMPT: "Elegant dermal filler beauty editorial, refined hyaluronic acid filler syringe and glass vials on soft blush-pink silk, rose-gold accents, luxury aesthetic clinic mood, gentle diffused glow, graceful minimal composition, premium magazine styling; photorealistic, detailed, high resolution; no text, no logo, no watermark" -->
        <div class="sf-col" data-sf style="position:relative;min-height:420px;">
          <div style="position:relative;background:#fff;border:1px solid var(--line);border-radius:200px 200px 24px 24px;box-shadow:var(--shadow-petal);padding:28px;max-width:400px;margin:0 auto;">
            <div class="sf-photo" style="border-radius:180px 180px 18px 18px;background-color:#F4D4CE;background-image:linear-gradient(165deg,rgba(251,231,230,.4),rgba(244,212,206,.4)),url('/assets/img/seffiline-seffiller.jpg');"></div>
          </div>
        </div>
        <div class="sf-col d1" data-sf>
          <p class="sf-eyebrow">{{subtitle}}</p>
          <h2 class="sf-serif" style="font-size:clamp(30px,4.4vw,46px);font-weight:500;color:var(--plum);margin:0 0 22px;line-height:1.12;">{{title}}<br><em style="font-style:italic;color:var(--rose);">{{text}}</em></h2>
          <p style="color:var(--plum-soft);font-size:17px;line-height:1.85;margin:0 0 28px;max-width:520px;">{{{body_html}}}</p>
          <ul class="sf-feat-list">
            <li><span class="pet">{{group_1_text}}</span> {{group_1_item_text}}</li>
            <li><span class="pet">{{group_2_text}}</span> {{group_2_item_text}}</li>
            <li><span class="pet">{{group_3_text}}</span> {{group_3_item_text}}</li>
          </ul>
          <a href="{{button_url}}" class="sf-btn sf-btn-fill">{{button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "text": {"type": "textarea", "label": "Text"}, "body_html": {"type": "textarea", "label": "Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "/urun-detay", "subtitle": "Öne Çıkan · Dolgu Serisi", "title": "Seffiller ile", "text": "doğal ve zarif hatlar", "body_html": "Hyalüronik asit bazlı Seffiller dolgu serisi; yüz hatlarına doğal hacim ve zarif bir tazelik kazandırmak için tasarlanmıştır. İnce dokulu, akışkan ve işlenebilir formülüyle, abartısız ve incelikli sonuçlara zemin hazırlar.", "group_1_text": "❀", "group_1_item_text": "Çapraz bağlı hyalüronik asit yapısı", "group_2_text": "❀", "group_2_item_text": "Doğal hacim ve zarif tazelik", "group_3_text": "❀", "group_3_item_text": "Farklı yoğunluklarda esnek seçenekler", "button_text": "Seffiller'i İncele →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-seffiline-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'5. ÜRÜN AİLESİ GRID',
             'html_template'=><<<'EDHTML'
<!-- 5. ÜRÜN AİLESİ GRID -->

    <section id="koleksiyon" style="padding:clamp(72px,9vw,100px) 0;background:var(--cream);">
      <div class="sf-wrap">
        <div style="text-align:center;max-width:620px;margin:0 auto 56px;">
          <p class="sf-eyebrow sf-eyebrow--c" data-sf>{{subtitle}}</p>
          <h2 class="sf-serif" data-sf style="font-size:clamp(30px,4.4vw,46px);font-weight:500;color:var(--plum);margin:0 0 16px;line-height:1.12;">{{title}}</h2>
          <p data-sf class="d1" style="color:var(--plum-soft);font-size:17px;line-height:1.8;">{{description}}</p>
        </div>

        <!-- 🖼️ GÖRSEL (opsiyonel kart görseli deseni): /assets/img/seffiline-product-{slug}.jpg (oran 1:1)
             slug'lar (ASCII güvenli): sefficare, seffigyn, seffihair, seffiller
             PROMPT: "Elegant feminine cosmetic product packshot, soft blush-pink and cream backdrop with rose-gold accents, luxury editorial beauty lighting, gentle diffused glow, refined delicate styling, single fresh petal detail, premium magazine aesthetic; photorealistic, detailed, high resolution; no text, no logo, no watermark"
             Kart ikon dairesinin yerine eklenebilir:
             <img src="/assets/img/seffiline-product-sefficare.jpg" alt="Seffiline SeffiCare ürün çekimi" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:22px;margin-bottom:20px;"> -->
        <div class="sf-grid" data-sf>
          <!-- SeffiCare -->
          <a href="{{item_1_button_url}}" class="sf-prodcard sf-card">
            <span class="sf-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2C7 7 7 11 12 22 17 11 17 7 12 2z"/><path d="M9 13c2 1.5 4 1.5 6 0"/></svg></span>
            <h3 class="sf-serif" style="font-size:25px;font-weight:600;color:var(--plum);margin:0 0 4px;">{{item_1_title}}</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--rose);font-weight:700;margin:0 0 14px;">{{group_1_subtitle}}</p>
            <p style="color:var(--plum-soft);font-size:14.5px;line-height:1.75;margin:0 0 22px;">{{group_2_description}}</p>
            <span class="sf-link">{{item_1_text}}</span>
          </a>
          <!-- SeffiGyn -->
          <a href="{{item_2_button_url}}" class="sf-prodcard sf-card">
            <span class="sf-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="5"/><path d="M12 13v8M9 18h6"/></svg></span>
            <h3 class="sf-serif" style="font-size:25px;font-weight:600;color:var(--plum);margin:0 0 4px;">{{item_2_title}}</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--rose);font-weight:700;margin:0 0 14px;">{{group_1_subtitle_2}}</p>
            <p style="color:var(--plum-soft);font-size:14.5px;line-height:1.75;margin:0 0 22px;">{{group_2_description_2}}</p>
            <span class="sf-link">{{item_2_text}}</span>
          </a>
          <!-- SeffiHair -->
          <a href="{{item_3_button_url}}" class="sf-prodcard sf-card">
            <span class="sf-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 4c0 8-2 10-2 14M12 4c0 9-1 11-1 14M18 4c0 8 2 10 2 14"/></svg></span>
            <h3 class="sf-serif" style="font-size:25px;font-weight:600;color:var(--plum);margin:0 0 4px;">{{item_3_title}}</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--rose);font-weight:700;margin:0 0 14px;">{{group_1_subtitle_3}}</p>
            <p style="color:var(--plum-soft);font-size:14.5px;line-height:1.75;margin:0 0 22px;">{{group_2_subtitle}}</p>
            <span class="sf-link">{{item_3_text}}</span>
          </a>
          <!-- Seffiller -->
          <a href="{{item_4_button_url}}" class="sf-prodcard sf-card">
            <span class="sf-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 2h6v3l-1 1v4l3 9a2 2 0 0 1-1.9 2.6H8.9A2 2 0 0 1 7 19l3-9V6L9 5z"/><path d="M9 13h6"/></svg></span>
            <h3 class="sf-serif" style="font-size:25px;font-weight:600;color:var(--plum);margin:0 0 4px;">{{item_4_title}}</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--rose);font-weight:700;margin:0 0 14px;">{{group_1_subtitle_4}}</p>
            <p style="color:var(--plum-soft);font-size:14.5px;line-height:1.75;margin:0 0 22px;">{{group_2_description_3}}</p>
            <span class="sf-link">{{item_4_text}}</span>
          </a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"item_1_button_url": {"type": "text", "label": "Item 1 Button Url"}, "item_2_button_url": {"type": "text", "label": "Item 2 Button Url"}, "item_3_button_url": {"type": "text", "label": "Item 3 Button Url"}, "item_4_button_url": {"type": "text", "label": "Item 4 Button Url"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}, "item_1_title": {"type": "text", "label": "Item 1 Title"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "item_1_text": {"type": "textarea", "label": "Item 1 Text"}, "item_2_title": {"type": "text", "label": "Item 2 Title"}, "group_1_subtitle_2": {"type": "textarea", "label": "Group 1 Subtitle 2"}, "group_2_description_2": {"type": "textarea", "label": "Group 2 Description 2"}, "item_2_text": {"type": "textarea", "label": "Item 2 Text"}, "item_3_title": {"type": "text", "label": "Item 3 Title"}, "group_1_subtitle_3": {"type": "textarea", "label": "Group 1 Subtitle 3"}, "group_2_subtitle": {"type": "textarea", "label": "Group 2 Subtitle"}, "item_3_text": {"type": "textarea", "label": "Item 3 Text"}, "item_4_title": {"type": "text", "label": "Item 4 Title"}, "group_1_subtitle_4": {"type": "textarea", "label": "Group 1 Subtitle 4"}, "group_2_description_3": {"type": "textarea", "label": "Group 2 Description 3"}, "item_4_text": {"type": "textarea", "label": "Item 4 Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"item_1_button_url": "/urun-detay", "item_2_button_url": "/urun-detay", "item_3_button_url": "/urun-detay", "item_4_button_url": "/urun-detay", "subtitle": "Koleksiyon", "title": "Dört ince ürün ailesi", "description": "Baştan ayağa bütüncül bir bakım ritüeli; her ihtiyaca uygun, zarif ve profesyonel.", "item_1_title": "SeffiCare", "group_1_subtitle": "Cilt Bakımı", "group_2_description": "Cildi besleyen, nemlendiren ve canlandıran zarif cilt bakım serisi.", "item_1_text": "İncele →", "item_2_title": "SeffiGyn", "group_1_subtitle_2": "İntim Bakım", "group_2_description_2": "Hassas bölgelerin sağlığı için pH dengeli, nazik ve güvenilir intim bakım.", "item_2_text": "İncele →", "item_3_title": "SeffiHair", "group_1_subtitle_3": "Saç Bakımı & Mezoterapi", "group_2_subtitle": "Saç kökünü güçlendiren mezoterapi ve yoğun bakım çözümleri.", "item_3_text": "İncele →", "item_4_title": "Seffiller", "group_1_subtitle_4": "Dolgu Serisi", "group_2_description_3": "Hyalüronik asit bazlı, doğal ve zarif sonuçlar veren dolgu çözümleri.", "item_4_text": "İncele →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-seffiline-6'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'6. DOKTORLAR İÇİN / UYGULAMA',
             'html_template'=><<<'EDHTML'
<!-- 6. DOKTORLAR İÇİN / UYGULAMA -->

    <section style="padding:clamp(72px,9vw,100px) 0;background:var(--paper);">
      <div class="sf-wrap">
        <div style="max-width:660px;margin:0 0 48px;">
          <p class="sf-eyebrow" data-sf>{{subtitle}}</p>
          <h2 class="sf-serif" data-sf style="font-size:clamp(28px,4vw,42px);font-weight:500;color:var(--plum);margin:0 0 16px;line-height:1.15;">{{title}}</h2>
          <p data-sf class="d1" style="color:var(--plum-soft);font-size:17px;line-height:1.85;">{{{body_html}}}</p>
        </div>
        <div class="sf-docgrid" data-sf>
          <div class="sf-doc">
            <span class="sf-ico" style="width:50px;height:50px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></span>
            <h4>{{group_1_eyebrow}}</h4>
            <p>{{group_1_description}}</p>
          </div>
          <div class="sf-doc">
            <span class="sf-ico" style="width:50px;height:50px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l8.8 8.6 8.8-8.6a5.5 5.5 0 0 0 0-7.8z"/></svg></span>
            <h4>{{group_2_eyebrow}}</h4>
            <p>{{group_2_description}}</p>
          </div>
          <div class="sf-doc">
            <span class="sf-ico" style="width:50px;height:50px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 14h2"/></svg></span>
            <h4>{{group_3_eyebrow}}</h4>
            <p>{{group_3_description}}</p>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "group_1_eyebrow": {"type": "text", "label": "Group 1 Eyebrow"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_eyebrow": {"type": "text", "label": "Group 2 Eyebrow"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_3_eyebrow": {"type": "text", "label": "Group 3 Eyebrow"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"subtitle": "Doktorlar & Uygulayıcılar İçin", "title": "Profesyonel kullanım için zarif ve güvenilir bir seri", "body_html": "Seffiline; klinik ve uygulayıcılar için sertifikalı, takip edilebilir ve düzenli tedarik edilen bir kozmesötik portföydür. Ürün, içerik ve uygulama detayları için ekibimiz yanınızda.", "group_1_eyebrow": "Ürün & İçerik Bilgisi", "group_1_description": "Her seri için içerik, kullanım alanı ve sunum formatı bilgileri.", "group_2_eyebrow": "Bütüncül Portföy", "group_2_description": "Cilt, saç, intim ve dolgu serileri tek bir tedarik ortağında.", "group_3_eyebrow": "Düzenli Tedarik", "group_3_description": "Resmi distribütör güvencesiyle istikrarlı ve takip edilebilir tedarik."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-seffiline-7'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'7. CTA BANDI',
             'html_template'=><<<'EDHTML'
<!-- 7. CTA BANDI -->

    <section style="padding:0 0 clamp(72px,9vw,104px);background:var(--paper);">
      <div class="sf-wrap">
        <div data-sf style="position:relative;overflow:hidden;border-radius:36px;background:var(--grad-rose);padding:clamp(48px,7vw,84px);text-align:center;color:#fff;">
          <div class="sf-blob" aria-hidden="true" style="top:-50px;right:-40px;width:220px;height:220px;background:rgba(255,255,255,.14);"></div>
          <div class="sf-blob" aria-hidden="true" style="bottom:-70px;left:-40px;width:260px;height:260px;background:rgba(255,255,255,.10);"></div>
          <div style="position:relative;">
            <p style="letter-spacing:3px;text-transform:uppercase;font-size:12px;font-weight:700;opacity:.9;margin:0 0 18px;">{{group_1_subtitle}}</p>
            <h2 class="sf-serif" style="font-size:clamp(28px,4.4vw,46px);font-weight:500;margin:0 0 18px;line-height:1.14;">{{title}}</h2>
            <p style="font-size:18px;opacity:.95;max-width:560px;margin:0 auto 36px;line-height:1.7;">{{group_2_description}}</p>
            <a href="{{button_url}}" target="_blank" rel="noopener" class="sf-btn" style="background:#25D366;color:#fff;font-weight:700;box-shadow:0 14px 36px rgba(74,46,53,.18);">
              <svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
              {{button_text}}</a>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "title": {"type": "text", "label": "Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "group_1_subtitle": "İletişim", "title": "Seffiline ürünleri için bize ulaşın", "group_2_description": "Ürün, içerik ve uygulama bilgileri için ekibimiz hazır. WhatsApp'tan zarifçe yazın, hemen yanıtlayalım.", "button_text": "WhatsApp ile Yaz"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-aespio-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'1) HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><style>
    .aespio {
      --ae-bg:        #15171C;   /* koyu antrasit */
      --ae-bg-2:      #191C22;   /* hafif açık panel */
      --ae-bg-3:      #1F232B;   /* kart yüzeyi */
      --ae-line:      #2C313A;   /* ince çizgi */
      --ae-line-2:    #3A414C;
      --ae-chrome:    #C7CCD1;   /* metalik gümüş/krom */
      --ae-chrome-2:  #E8ECEF;   /* parlak krom */
      --ae-cool:      #6FB6C9;   /* soğuk aksan (buz mavisi) */
      --ae-cool-2:    #9AD7E6;
      --ae-ink:       #F3F5F7;   /* açık metin (başlık) */
      --ae-body:      #B6BDC6;   /* gövde metni */
      --ae-muted:     #828A95;   /* ikincil */
      --ae-display:   "Space Grotesk", "Inter", system-ui, sans-serif;
      --ae-sans:      "Inter", system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
      font-family: var(--ae-sans);
      color: var(--ae-body);
      background: var(--ae-bg);
    }
    .aespio h1, .aespio h2, .aespio h3, .aespio h4 {
      font-family: var(--ae-display);
      color: var(--ae-ink);
      font-weight: 600;
      letter-spacing: -.02em;
      line-height: 1.05;
      margin: 0;
    }
    .aespio p { margin: 0; }
    .aespio ::selection { background: var(--ae-cool); color: #0B0D11; }

    /* metalik krom metin */
    .ae-chrome-text {
      background: linear-gradient(180deg, #FFFFFF 0%, #C7CCD1 42%, #6E747D 100%);
      -webkit-background-clip: text; background-clip: text; color: transparent;
    }
    .ae-eyebrow {
      display: inline-flex; align-items: center; gap: 10px;
      font-family: var(--ae-display); font-size: 12px; font-weight: 600;
      letter-spacing: .26em; text-transform: uppercase; color: var(--ae-cool-2);
    }
    .ae-eyebrow::before { content: ""; width: 26px; height: 1px;
      background: linear-gradient(90deg, var(--ae-cool), transparent); }

    /* container */
    .ae-wrap { width: min(100% - 48px, 1240px); margin-inline: auto; }
    .ae-sec { padding: clamp(74px, 11vh, 138px) 0; position: relative; }

    /* buttons */
    .ae-btn { display: inline-flex; align-items: center; gap: 10px; padding: 15px 30px;
      border-radius: 999px; font-family: var(--ae-display); font-size: 15px; font-weight: 600;
      letter-spacing: .01em; transition: .28s cubic-bezier(.22,.61,.36,1); border: 1px solid transparent; }
    .ae-btn--chrome { color: #0C0E12;
      background: linear-gradient(180deg, #F2F4F6 0%, #C7CCD1 100%);
      box-shadow: 0 0 0 1px rgba(255,255,255,.18) inset, 0 14px 34px -14px rgba(199,204,209,.55); }
    .ae-btn--chrome:hover { transform: translateY(-2px);
      box-shadow: 0 0 0 1px rgba(255,255,255,.3) inset, 0 20px 44px -14px rgba(199,204,209,.7); }
    .ae-btn--ghost { color: var(--ae-ink); border-color: var(--ae-line-2); background: rgba(255,255,255,.02); }
    .ae-btn--ghost:hover { border-color: var(--ae-cool); color: #fff;
      box-shadow: 0 0 24px -6px rgba(111,182,201,.5); transform: translateY(-2px); }
    .ae-btn--wa { color: #fff; background: #25D366; border-color: rgba(255,255,255,.14);
      box-shadow: 0 12px 30px -10px rgba(37,211,102,.55); }
    .ae-btn--wa:hover { transform: translateY(-2px); box-shadow: 0 18px 40px -10px rgba(37,211,102,.7); }
    .ae-btn svg { width: 18px; height: 18px; flex-shrink: 0; }
    .ae-link { display: inline-flex; align-items: center; gap: 9px; font-family: var(--ae-display);
      font-weight: 600; font-size: 15px; color: var(--ae-cool-2); transition: .25s; }
    .ae-link svg { width: 17px; height: 17px; transition: transform .25s; }
    .ae-link:hover { color: #fff; } .ae-link:hover svg { transform: translateX(4px); }

    /* HERO */
    .ae-hero { position: relative; overflow: hidden; background:
      radial-gradient(1000px 560px at 76% 8%, rgba(111,182,201,.16), transparent 60%),
      radial-gradient(760px 600px at 8% 100%, rgba(199,204,209,.07), transparent 62%),
      var(--ae-bg); }
    .ae-hero__grid { position: absolute; inset: 0; pointer-events: none;
      background-image: linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
      background-size: 58px 58px;
      -webkit-mask-image: radial-gradient(900px 600px at 72% 20%, #000, transparent 72%);
      mask-image: radial-gradient(900px 600px at 72% 20%, #000, transparent 72%); }
    .ae-hero__scan { position: absolute; inset: 0; pointer-events: none; opacity: .5;
      background: repeating-linear-gradient(180deg, transparent 0 3px, rgba(255,255,255,.018) 3px 4px); }
    .ae-hero__in { position: relative; display: grid; grid-template-columns: 1.06fr .94fr;
      gap: 56px; align-items: center; padding: clamp(78px,11vh,118px) 0 clamp(82px,12vh,122px); }
    .ae-hero h1 { font-size: clamp(40px, 6vw, 76px); margin: 24px 0 22px; font-weight: 600; }
    .ae-hero__sub { font-size: clamp(16px, 1.5vw, 21px); color: var(--ae-body);
      max-width: 540px; line-height: 1.65; margin: 0 0 36px; }
    .ae-hero__cta { display: flex; gap: 14px; flex-wrap: wrap; }
    .ae-hero__spec { display: flex; gap: 30px; flex-wrap: wrap; margin-top: 48px;
      padding-top: 28px; border-top: 1px solid var(--ae-line); }
    .ae-hero__spec div { font-size: 13px; color: var(--ae-muted); }
    .ae-hero__spec b { display: block; font-family: var(--ae-display); font-size: 19px;
      color: var(--ae-ink); font-weight: 600; letter-spacing: -.01em; margin-bottom: 2px; }

    /* hero device visual */
    .ae-device { position: relative; }
    .ae-device__frame { position: relative; aspect-ratio: 4/5; border-radius: 26px; overflow: hidden;
      border: 1px solid var(--ae-line-2); background: linear-gradient(160deg, #20242C, #14161B);
      box-shadow: 0 40px 90px -40px rgba(0,0,0,.8), 0 0 0 1px rgba(255,255,255,.04) inset; }
    .ae-device__img { position: absolute; inset: 0; background-size: cover; background-position: center;
      background-image: linear-gradient(150deg, #262B34 0%, #171A20 70%); }
    .ae-device__glow { position: absolute; inset: 0; pointer-events: none;
      background: radial-gradient(420px 280px at 70% 12%, rgba(111,182,201,.34), transparent 62%); }
    .ae-device__sheen { position: absolute; top: 0; left: -40%; width: 50%; height: 100%;
      background: linear-gradient(100deg, transparent, rgba(255,255,255,.12), transparent);
      transform: skewX(-18deg); animation: ae-sheen 5.5s ease-in-out infinite; }
    @keyframes ae-sheen { 0%,72% { left: -45%; } 100% { left: 130%; } }
    .ae-device__badge { position: absolute; left: 16px; bottom: 16px; right: 16px;
      display: flex; align-items: center; gap: 12px;
      background: rgba(20,23,28,.72); backdrop-filter: blur(10px);
      border: 1px solid var(--ae-line-2); border-radius: 15px; padding: 13px 16px; }
    .ae-device__badge .tag { width: 44px; height: 44px; border-radius: 11px; flex-shrink: 0;
      display: grid; place-items: center; font-family: var(--ae-display); font-weight: 700; font-size: 12px;
      color: #0C0E12; background: linear-gradient(180deg, #E8ECEF, #9FA6AF); }
    .ae-device__badge small { color: var(--ae-muted); font-size: 12px; }
    .ae-device__badge b { display: block; color: var(--ae-ink); font-weight: 600; font-size: 15px; font-family: var(--ae-display); }
    .ae-device__chip { position: absolute; top: -16px; right: -14px;
      display: flex; align-items: center; gap: 9px;
      background: rgba(25,28,34,.9); border: 1px solid var(--ae-line-2); border-radius: 13px;
      padding: 10px 15px; box-shadow: 0 16px 36px -16px rgba(0,0,0,.7); }
    .ae-device__chip span { width: 8px; height: 8px; border-radius: 50%;
      background: var(--ae-cool-2); box-shadow: 0 0 12px var(--ae-cool); }
    .ae-device__chip b { font-family: var(--ae-display); font-size: 13px; color: var(--ae-ink); font-weight: 600; }

    /* CREDIBILITY STRIP */
    .ae-cred { background: var(--ae-bg-2); border-block: 1px solid var(--ae-line); }
    .ae-cred__in { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; padding: 48px 0; }
    .ae-cred__item { position: relative; padding-left: 18px; }
    .ae-cred__item::before { content: ""; position: absolute; left: 0; top: 4px; bottom: 4px;
      width: 2px; border-radius: 2px; background: linear-gradient(180deg, var(--ae-cool), transparent); }
    .ae-cred__item b { display: block; font-family: var(--ae-display); font-size: clamp(20px,2.3vw,28px);
      color: var(--ae-ink); font-weight: 600; letter-spacing: -.01em; }
    .ae-cred__item span { display: block; color: var(--ae-muted); font-size: 13.5px; margin-top: 5px; line-height: 1.5; }

    /* STORY */
    .ae-story { display: grid; grid-template-columns: 1fr 1.05fr; gap: clamp(40px,6vw,80px); align-items: center; }
    .ae-story__visual { position: relative; aspect-ratio: 5/6; border-radius: 24px; overflow: hidden;
      border: 1px solid var(--ae-line-2); background: linear-gradient(160deg, #20242C, #14161B);
      box-shadow: 0 36px 80px -42px rgba(0,0,0,.8); }
    .ae-story__img { position: absolute; inset: 0; background-size: cover; background-position: center;
      background-image: linear-gradient(150deg, #242931 0%, #15181D 72%); }
    .ae-story__visual .ae-device__glow { background: radial-gradient(420px 300px at 30% 18%, rgba(111,182,201,.3), transparent 64%); }
    .ae-story h2 { font-size: clamp(28px,3.8vw,46px); margin: 20px 0 18px; }
    .ae-story p + p { margin-top: 16px; }
    .ae-story__list { list-style: none; margin: 28px 0 0; padding: 0; display: grid; gap: 14px; }
    .ae-story__list li { display: flex; gap: 13px; align-items: flex-start; color: var(--ae-body); font-size: 15px; }
    .ae-story__list .ic { width: 30px; height: 30px; border-radius: 9px; flex-shrink: 0; display: grid; place-items: center;
      background: rgba(111,182,201,.12); border: 1px solid rgba(111,182,201,.3); color: var(--ae-cool-2); }
    .ae-story__list .ic svg { width: 16px; height: 16px; }
    .ae-story__list b { color: var(--ae-ink); font-weight: 600; display: block; margin-bottom: 2px; font-family: var(--ae-display); }

    /* FEATURED SPLIT (LFL Anchor + Beta-Glukan) */
    .ae-split { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .ae-feat { position: relative; overflow: hidden; border-radius: 24px; border: 1px solid var(--ae-line-2);
      background: linear-gradient(165deg, var(--ae-bg-3) 0%, #14161B 100%);
      display: flex; flex-direction: column; min-height: 460px; transition: .3s cubic-bezier(.22,.61,.36,1); }
    .ae-feat:hover { transform: translateY(-5px); border-color: var(--ae-cool);
      box-shadow: 0 32px 70px -34px rgba(0,0,0,.85), 0 0 36px -14px rgba(111,182,201,.5); }
    .ae-feat__media { position: relative; aspect-ratio: 16/10; overflow: hidden; border-bottom: 1px solid var(--ae-line); }
    .ae-feat__img { position: absolute; inset: 0; background-size: cover; background-position: center;
      transition: transform .5s cubic-bezier(.22,.61,.36,1); }
    .ae-feat:hover .ae-feat__img { transform: scale(1.05); }
    .ae-feat__media::after { content: ""; position: absolute; inset: 0;
      background: linear-gradient(180deg, transparent 40%, rgba(20,22,27,.55)); }
    .ae-feat__tag { position: absolute; top: 14px; left: 14px; z-index: 2;
      font-family: var(--ae-display); font-size: 11px; font-weight: 600; letter-spacing: .16em; text-transform: uppercase;
      color: var(--ae-cool-2); background: rgba(15,17,21,.7); backdrop-filter: blur(6px);
      border: 1px solid rgba(111,182,201,.32); border-radius: 999px; padding: 7px 14px; }
    .ae-feat__body { padding: 28px 28px 30px; display: flex; flex-direction: column; flex: 1; }
    .ae-feat__body h3 { font-size: clamp(22px,2.6vw,30px); margin: 0 0 12px; }
    .ae-feat__body p { color: var(--ae-body); font-size: 15px; line-height: 1.65; margin: 0 0 22px; }
    .ae-feat__body .ae-link { margin-top: auto; }

    /* PRODUCT GRID */
    .ae-pgrid { display: grid; grid-template-columns: repeat(auto-fit, minmax(248px,1fr)); gap: 22px; }
    .ae-pcard { display: flex; flex-direction: column; overflow: hidden; border-radius: 20px;
      border: 1px solid var(--ae-line); background: linear-gradient(165deg, var(--ae-bg-3), #14161B);
      transition: .28s cubic-bezier(.22,.61,.36,1); }
    .ae-pcard:hover { transform: translateY(-5px); border-color: var(--ae-cool);
      box-shadow: 0 28px 60px -32px rgba(0,0,0,.85), 0 0 30px -14px rgba(111,182,201,.45); }
    .ae-pcard__media { position: relative; aspect-ratio: 4/3; overflow: hidden; border-bottom: 1px solid var(--ae-line); }
    .ae-pcard__img { position: absolute; inset: 0; background-size: cover; background-position: center;
      transition: transform .5s cubic-bezier(.22,.61,.36,1); }
    .ae-pcard:hover .ae-pcard__img { transform: scale(1.06); }
    .ae-pcard__body { padding: 22px; display: flex; flex-direction: column; flex: 1; }
    .ae-pcard__cat { font-family: var(--ae-display); font-size: 11px; font-weight: 600;
      letter-spacing: .14em; text-transform: uppercase; color: var(--ae-cool-2); }
    .ae-pcard__body h3 { font-size: 19px; margin: 8px 0 9px; }
    .ae-pcard__body p { color: var(--ae-muted); font-size: 13.5px; line-height: 1.6; margin: 0 0 16px; flex: 1; }
    .ae-pcard__go { font-family: var(--ae-display); font-weight: 600; font-size: 13.5px;
      color: var(--ae-chrome); display: inline-flex; align-items: center; gap: 7px; }
    .ae-pcard:hover .ae-pcard__go { color: #fff; }

    /* FOR DOCTORS */
    .ae-docs { position: relative; overflow: hidden; border-radius: 28px; padding: clamp(40px,6vw,72px);
      border: 1px solid var(--ae-line-2);
      background: radial-gradient(700px 400px at 85% -10%, rgba(111,182,201,.16), transparent 60%),
        linear-gradient(160deg, #1B1F26, #111317); }
    .ae-docs__grid { position: absolute; inset: 0; pointer-events: none;
      background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 52px 52px; }
    .ae-docs > * { position: relative; }
    .ae-docs h2 { font-size: clamp(28px,3.6vw,44px); margin: 18px 0 14px; max-width: 660px; }
    .ae-docs__lead { color: var(--ae-body); font-size: clamp(16px,1.4vw,19px); max-width: 600px; line-height: 1.65; }
    .ae-docs__cards { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; margin: 38px 0 32px; }
    .ae-docc { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.09);
      border-radius: 16px; padding: 26px; }
    .ae-docc__ic { width: 46px; height: 46px; border-radius: 12px; display: grid; place-items: center; margin-bottom: 16px;
      background: rgba(111,182,201,.14); border: 1px solid rgba(111,182,201,.3); color: var(--ae-cool-2); }
    .ae-docc__ic svg { width: 22px; height: 22px; }
    .ae-docc h4 { font-size: 17px; margin: 0 0 8px; }
    .ae-docc p { color: var(--ae-muted); font-size: 14px; line-height: 1.6; margin: 0; }

    /* CTA BAND */
    .ae-cta { position: relative; overflow: hidden; border-radius: 30px; text-align: center;
      padding: clamp(48px,7vw,84px) clamp(28px,5vw,64px); border: 1px solid var(--ae-line-2);
      background: radial-gradient(640px 360px at 50% -20%, rgba(111,182,201,.22), transparent 60%),
        linear-gradient(160deg, #1C2027, #101216); }
    .ae-cta__grid { position: absolute; inset: 0; pointer-events: none; opacity: .6;
      background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 50px 50px;
      -webkit-mask-image: radial-gradient(500px 320px at 50% 0%, #000, transparent 75%);
      mask-image: radial-gradient(500px 320px at 50% 0%, #000, transparent 75%); }
    .ae-cta > * { position: relative; }
    .ae-cta h2 { font-size: clamp(28px,4.4vw,48px); margin: 20px 0 16px; }
    .ae-cta p { color: var(--ae-body); font-size: 17px; max-width: 580px; margin: 0 auto 34px; line-height: 1.6; }

    /* scroll-reveal (kendi gözlemci — v2.js .reveal ile çakışmaz) */
    .ae-rev { opacity: 0; transform: translateY(24px); transition: opacity .7s cubic-bezier(.22,.61,.36,1), transform .7s cubic-bezier(.22,.61,.36,1); }
    .ae-rev.in { opacity: 1; transform: none; }
    .ae-d1.in { transition-delay: .08s; } .ae-d2.in { transition-delay: .16s; } .ae-d3.in { transition-delay: .24s; }
    @media (prefers-reduced-motion: reduce) { .ae-rev { opacity: 1; transform: none; transition: none; } .ae-device__sheen { animation: none; } }

    @media (max-width: 960px) {
      .ae-hero__in, .ae-story, .ae-split { grid-template-columns: 1fr; }
      .ae-hero__visual { order: -1; max-width: 420px; }
      .ae-cred__in { grid-template-columns: repeat(2,1fr); gap: 30px 28px; }
      .ae-docs__cards { grid-template-columns: 1fr; }
    }
    @media (max-width: 520px) {
      .ae-cred__in { grid-template-columns: 1fr; }
    }
  </style><!-- 1) HERO -->

    <section class="ae-hero">
      <div class="ae-hero__grid" aria-hidden="true"></div>
      <div class="ae-hero__scan" aria-hidden="true"></div>
      <div class="ae-wrap ae-hero__in">
        <div class="ae-hero__copy">
          <span class="ae-eyebrow ae-rev">{{text}}</span>
          <h1 class="ae-rev ae-d1">{{title}}<br><span class="ae-chrome-text">{{text_2}}</span></h1>
          <p class="ae-hero__sub ae-rev ae-d2">{{{body_html}}}</p>
          <div class="ae-hero__cta ae-rev ae-d3">
            <a class="ae-btn ae-btn--chrome" href="{{button_url}}">{{button_text}}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            <a class="ae-btn ae-btn--wa" href="{{button_url_2}}" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
              {{button_text_2}}</a>
          </div>
          <div class="ae-hero__spec ae-rev ae-d3">
            <div><b>{{group_1_text}}</b>{{group_1_text_2}}</div>
            <div><b>{{group_2_text}}</b>{{group_2_text_2}}</div>
            <div><b>{{group_3_text}}</b>{{group_3_text_2}}</div>
          </div>
        </div>

        <!-- 🖼️ GÖRSEL: /assets/img/aespio-hero.jpg (oran 4:5)
             PROMPT: "Sleek dark futuristic macro shot of a high-tech medical aesthetics thread-lift device / cannula on a brushed metallic anthracite surface, chrome and silver reflections, cool cyan glow accent, moody studio lighting, advanced technology product photography, premium and precise; photorealistic, high resolution; no text, no logo, no watermark"
             KULLANIM → .ae-device__img elementine background-image olarak ekle. -->
        <div class="ae-hero__visual ae-rev ae-d2">
          <div class="ae-device">
            <div class="ae-device__frame">
              <div class="ae-device__img" style="background-image:url('{{background_image_url}}');"></div>
              <div class="ae-device__glow" aria-hidden="true"></div>
              <div class="ae-device__sheen" aria-hidden="true"></div>
              <div class="ae-device__badge">
                <span class="tag">{{text_3}}</span>
                <div><small>{{text_4}}</small><b>{{text_5}}</b></div>
              </div>
            </div>
            <div class="ae-device__chip" aria-hidden="true"><span></span><b>{{text_6}}</b></div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "background_image_url": {"type": "image", "label": "Background Image Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "text_2": {"type": "textarea", "label": "Text 2"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "text_4": {"type": "textarea", "label": "Text 4"}, "text_5": {"type": "textarea", "label": "Text 5"}, "text_6": {"type": "textarea", "label": "Text 6"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap", "button_url": "#urunler", "button_url_2": "https://wa.me/905426205100", "background_image_url": "/assets/img/aespio-hero.jpg", "text": "Grand Aespio · İleri Formül & Cihaz Teknolojisi", "title": "Yeni Nesil", "text_2": "Estetik Teknolojisi", "body_html": "Grand Aespio, ip askı (thread lift) sistemleri ve ileri formül yüz maskelerini tek bir mühendislik diliyle birleştirir. Hassas tutuş, kontrollü etki, ölçülebilir kalite.", "button_text": "Ürünleri İncele", "button_text_2": "Bilgi Al — WhatsApp", "group_1_text": "3", "group_1_text_2": "İp askı sistemi", "group_2_text": "2", "group_2_text_2": "İleri formül maske", "group_3_text": "TR", "group_3_text_2": "Estetik Dermal temsilciliği", "text_3": "LFL", "text_4": "Öne çıkan sistem", "text_5": "LFL Anchor — Thread Lift", "text_6": "Precision Hold"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-aespio-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'2) MARKA HİKAYESİ',
             'html_template'=><<<'EDHTML'
<!-- 2) MARKA HİKAYESİ -->

    <section class="ae-sec">
      <div class="ae-wrap">
        <div class="ae-story">
          <!-- 🖼️ GÖRSEL: /assets/img/aespio-tech.jpg (oran 5:6)
               PROMPT: "Dark futuristic close-up of advanced skincare formulation — a sheet mask serum and biotech ingredient texture on a glossy black tech surface, cool silver-cyan rim light, laboratory precision aesthetic, sleek and high-tech; photorealistic, high resolution; no text, no logo, no watermark"
               KULLANIM → .ae-story__img elementine background-image olarak ekle. -->
          <div class="ae-story__visual ae-rev">
            <div class="ae-story__img" style="background-image:url('{{background_image_url}}');"></div>
            <div class="ae-device__glow" aria-hidden="true"></div>
            <div class="ae-device__sheen" aria-hidden="true"></div>
          </div>
          <div class="ae-rev ae-d1">
            <span class="ae-eyebrow">{{text}}</span>
            <h2>{{title}}</h2>
            <p style="color:var(--ae-body);font-size:clamp(16px,1.3vw,18px);line-height:1.72;">{{{group_1_body_html}}}</p>
            <p style="color:var(--ae-body);font-size:clamp(16px,1.3vw,18px);line-height:1.72;">{{{group_2_body_html}}}</p>
            <ul class="ae-story__list">
              <li><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2.4"/></svg></span><div><b>{{group_1_text}}</b>{{group_1_text_2}}</div></li>
              <li><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3h6M10 3v5L5 19a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1L14 8V3"/></svg></span><div><b>{{group_2_text}}</b>{{group_2_text_2}}</div></li>
              <li><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg></span><div><b>{{group_3_text}}</b>{{group_3_text_2}}</div></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"background_image_url": {"type": "image", "label": "Background Image Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "group_1_body_html": {"type": "textarea", "label": "Group 1 Body"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"background_image_url": "/assets/img/aespio-tech.jpg", "text": "Marka Yaklaşımı", "title": "Teknolojiyi formüle, formülü sonuca çeviren mühendislik.", "group_1_body_html": "Grand Aespio, estetik uygulamalara cihaz hassasiyetiyle yaklaşır. İp askı tarafında çapalı (anchor) tutuş geometrisi ve doku uyumlu malzeme; maske tarafında beta-glukan ve hyalüronik asit gibi ileri aktiflerin kontrollü salımı. Her ürün, tekrarlanabilir ve ölçülebilir bir sonuç hedefiyle tasarlanır.", "group_2_body_html": "Yaklaşım nettir: gösterişten çok performans, moda yerine mühendislik. Yeni nesil formüller, hekimin elinde öngörülebilir bir araç setine dönüşür.", "group_1_text": "Hassas geometri", "group_1_text_2": "Çapalı tutuş ve doku uyumlu ip askı tasarımı.", "group_2_text": "İleri aktif formül", "group_2_text_2": "Beta-glukan ve hyalüronik asit ile kontrollü salım.", "group_3_text": "Ölçülebilir kalite", "group_3_text_2": "Tekrarlanabilir, öngörülebilir üretim standardı."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-aespio-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'3) KREDİBİLİTE ŞERİDİ',
             'html_template'=><<<'EDHTML'
<!-- 3) KREDİBİLİTE ŞERİDİ -->

    <section class="ae-cred">
      <div class="ae-wrap ae-cred__in">
        <div class="ae-cred__item ae-rev"><b>{{text}}</b><span>{{text_2}}</span></div>
        <div class="ae-cred__item ae-rev ae-d1"><b>{{text_3}}</b><span>{{text_4}}</span></div>
        <div class="ae-cred__item ae-rev ae-d2"><b>{{text_5}}</b><span>{{text_6}}</span></div>
        <div class="ae-cred__item ae-rev ae-d3"><b>{{text_7}}</b><span>{{text_8}}</span></div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "text_4": {"type": "textarea", "label": "Text 4"}, "text_5": {"type": "textarea", "label": "Text 5"}, "text_6": {"type": "textarea", "label": "Text 6"}, "text_7": {"type": "textarea", "label": "Text 7"}, "text_8": {"type": "textarea", "label": "Text 8"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"text": "Yenilikçi", "text_2": "İleri formül & cihaz odaklı yeni nesil yaklaşım", "text_3": "Estetik Dermal", "text_4": "Grand Aespio'nun Türkiye temsilcisi", "text_5": "5 ürün", "text_6": "İp askı serisi + ileri formül maske serisi", "text_7": "Hekime özel", "text_8": "Profesyonel uygulama ve teknik destek"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-aespio-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'4) ÖNE ÇIKAN SPLIT: LFL Anchor + Beta-Glukan',
             'html_template'=><<<'EDHTML'
<!-- 4) ÖNE ÇIKAN SPLIT: LFL Anchor + Beta-Glukan -->

    <section class="ae-sec">
      <div class="ae-wrap">
        <div class="ae-rev" style="max-width:640px;margin-bottom:46px;">
          <span class="ae-eyebrow">{{text}}</span>
          <h2 style="font-size:clamp(28px,3.8vw,46px);margin-top:18px;">{{title}}</h2>
        </div>
        <div class="ae-split">

          <!-- LFL Anchor -->
          <article class="ae-feat ae-rev">
            <div class="ae-feat__media">
              <!-- 🖼️ GÖRSEL: /assets/img/aespio-lfl-anchor.jpg (oran 16:10)
                   PROMPT: "Dark high-tech macro of an anchor-design PDO lifting thread with cannula on brushed metallic surface, chrome reflections, cool cyan glow, futuristic medical aesthetics product shot, sleek and precise; photorealistic, high resolution; no text, no logo, no watermark"
                   KULLANIM → .ae-feat__img elementine background-image olarak ekle. -->
              <div class="ae-feat__img" style="background-image:url('{{background_image_url}}');"></div>
              <span class="ae-feat__tag">{{text_2}}</span>
            </div>
            <div class="ae-feat__body">
              <h3>{{title_2}}</h3>
              <p>{{{body_html}}}</p>
              <a class="ae-link" href="{{button_url}}">{{button_text}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </div>
          </article>

          <!-- Beta-Glukan Mask -->
          <article class="ae-feat ae-rev ae-d1">
            <div class="ae-feat__media">
              <!-- 🖼️ GÖRSEL: /assets/img/aespio-beta-glukan.jpg (oran 16:10)
                   PROMPT: "Dark futuristic product shot of a soothing beta-glucan sheet face mask sachet on a glossy black surface, cool silver-cyan rim light, biotech skincare aesthetic, sleek high-tech composition; photorealistic, high resolution; no text, no logo, no watermark"
                   KULLANIM → .ae-feat__img elementine background-image olarak ekle. -->
              <div class="ae-feat__img" style="background-image:url('{{background_image_url_2}}');"></div>
              <span class="ae-feat__tag">{{text_3}}</span>
            </div>
            <div class="ae-feat__body">
              <h3>{{title_3}}</h3>
              <p>{{{body_html_2}}}</p>
              <a class="ae-link" href="{{button_url_2}}">{{button_text_2}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </div>
          </article>

        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"background_image_url": {"type": "image", "label": "Background Image Url"}, "button_url": {"type": "text", "label": "Button Url"}, "background_image_url_2": {"type": "image", "label": "Background Image Url 2"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "text_2": {"type": "textarea", "label": "Text 2"}, "title_2": {"type": "text", "label": "Title 2"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}, "text_3": {"type": "textarea", "label": "Text 3"}, "title_3": {"type": "text", "label": "Title 3"}, "body_html_2": {"type": "textarea", "label": "Body Html 2"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"background_image_url": "/assets/img/aespio-lfl-anchor.jpg", "button_url": "/urun-detay", "background_image_url_2": "/assets/img/aespio-beta-glukan.jpg", "button_url_2": "/urun-detay", "text": "Öne Çıkan İki Güç", "title": "Tek portföyde thread lift ve ileri formül maske", "text_2": "İp Askı · Thread Lift", "title_2": "LFL Anchor", "body_html": "Çapa (anchor) tasarımıyla dokuda güçlü ve dengeli bir tutuş için geliştirilen ip askı sistemi. FeelSoft ve FMC ile birlikte, farklı endikasyonlara cevap veren çok yönlü bir thread lift araç seti sunar.", "button_text": "LFL Anchor'ı incele", "text_3": "Yüz Maskesi · İleri Formül", "title_3": "Beta-Glukan Mask", "body_html_2": "Beta-glukan aktifinin kontrollü salımıyla hassas cildi yatıştırmaya ve onarım sürecini desteklemeye yönelik ileri formül yüz maskesi. Thread protokollerini tamamlayan bakım adımı olarak da konumlanır.", "button_text_2": "Beta-Glukan Mask'ı incele"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-aespio-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'5) ÜRÜN YELPAZESİ GRID',
             'html_template'=><<<'EDHTML'
<!-- 5) ÜRÜN YELPAZESİ GRID -->

    <section class="ae-sec" id="urunler" style="background:var(--ae-bg-2);border-block:1px solid var(--ae-line);">
      <div class="ae-wrap">
        <div class="ae-rev" style="max-width:640px;margin-bottom:48px;">
          <span class="ae-eyebrow">{{text}}</span>
          <h2 style="font-size:clamp(28px,3.8vw,46px);margin-top:18px;">{{title}}</h2>
          <p style="color:var(--ae-body);font-size:17px;line-height:1.7;margin-top:14px;">{{description}}</p>
        </div>

        <!-- 🖼️ GÖRSEL deseni (tekrarlayan ürün kartı): /assets/img/aespio-product-{slug}.jpg (oran 4:3)
             Örn: aespio-product-beta-glukan-mask.jpg, aespio-product-hyaluronic-acid-mask.jpg,
                  aespio-product-feelsoft.jpg, aespio-product-fmc.jpg, aespio-product-lfl-anchor.jpg
             PROMPT: "Dark sleek futuristic product showcase (sheet face mask sachet OR PDO thread-lift product), brushed metallic anthracite backdrop, chrome and cool-cyan glow accents, advanced medical aesthetics studio lighting, crisp precise composition; photorealistic, high resolution; no text, no logo, no watermark" -->
        <div class="ae-pgrid">

          <a class="ae-pcard ae-rev" href="{{item_1_button_url}}">
            <div class="ae-pcard__media"><div class="ae-pcard__img" style="background-image:url('{{item_1_background_image_url}}');"></div></div>
            <div class="ae-pcard__body">
              <span class="ae-pcard__cat">{{item_1_text}}</span>
              <h3>{{item_1_title}}</h3>
              <p>{{item_1_description}}</p>
              <span class="ae-pcard__go">{{item_1_text_2}} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ae-pcard ae-rev ae-d1" href="{{item_1_button_url_2}}">
            <div class="ae-pcard__media"><div class="ae-pcard__img" style="background-image:url('{{item_1_background_image_url_2}}');"></div></div>
            <div class="ae-pcard__body">
              <span class="ae-pcard__cat">{{item_1_text_3}}</span>
              <h3>{{item_1_title_2}}</h3>
              <p>{{item_1_description_2}}</p>
              <span class="ae-pcard__go">{{item_1_text_4}} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ae-pcard ae-rev ae-d2" href="{{button_url}}">
            <div class="ae-pcard__media"><div class="ae-pcard__img" style="background-image:url('{{background_image_url}}');"></div></div>
            <div class="ae-pcard__body">
              <span class="ae-pcard__cat">{{text_2}}</span>
              <h3>{{title_2}}</h3>
              <p>{{description_2}}</p>
              <span class="ae-pcard__go">{{text_3}} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ae-pcard ae-rev" href="{{item_2_button_url}}">
            <div class="ae-pcard__media"><div class="ae-pcard__img" style="background-image:url('{{item_2_background_image_url}}');"></div></div>
            <div class="ae-pcard__body">
              <span class="ae-pcard__cat">{{item_2_text}}</span>
              <h3>{{item_2_title}}</h3>
              <p>{{item_2_description}}</p>
              <span class="ae-pcard__go">{{item_2_text_2}} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ae-pcard ae-rev ae-d1" href="{{item_2_button_url_2}}">
            <div class="ae-pcard__media"><div class="ae-pcard__img" style="background-image:url('{{item_2_background_image_url_2}}');"></div></div>
            <div class="ae-pcard__body">
              <span class="ae-pcard__cat">{{item_2_text_3}}</span>
              <h3>{{item_2_title_2}}</h3>
              <p>{{item_2_description_2}}</p>
              <span class="ae-pcard__go">{{item_2_text_4}} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"item_1_button_url": {"type": "text", "label": "Item 1 Button Url"}, "item_1_background_image_url": {"type": "image", "label": "Item 1 Background Image Url"}, "item_1_button_url_2": {"type": "text", "label": "Item 1 Button Url 2"}, "item_1_background_image_url_2": {"type": "image", "label": "Item 1 Background Image Url 2"}, "button_url": {"type": "text", "label": "Button Url"}, "background_image_url": {"type": "image", "label": "Background Image Url"}, "item_2_button_url": {"type": "text", "label": "Item 2 Button Url"}, "item_2_background_image_url": {"type": "image", "label": "Item 2 Background Image Url"}, "item_2_button_url_2": {"type": "text", "label": "Item 2 Button Url 2"}, "item_2_background_image_url_2": {"type": "image", "label": "Item 2 Background Image Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}, "item_1_text": {"type": "textarea", "label": "Item 1 Text"}, "item_1_title": {"type": "text", "label": "Item 1 Title"}, "item_1_description": {"type": "textarea", "label": "Item 1 Description"}, "item_1_text_2": {"type": "textarea", "label": "Item 1 Text 2"}, "item_1_text_3": {"type": "textarea", "label": "Item 1 Text 3"}, "item_1_title_2": {"type": "text", "label": "Item 1 Title 2"}, "item_1_description_2": {"type": "textarea", "label": "Item 1 Description 2"}, "item_1_text_4": {"type": "textarea", "label": "Item 1 Text 4"}, "text_2": {"type": "textarea", "label": "Text 2"}, "title_2": {"type": "text", "label": "Title 2"}, "description_2": {"type": "textarea", "label": "Description 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "item_2_text": {"type": "textarea", "label": "Item 2 Text"}, "item_2_title": {"type": "text", "label": "Item 2 Title"}, "item_2_description": {"type": "textarea", "label": "Item 2 Description"}, "item_2_text_2": {"type": "textarea", "label": "Item 2 Text 2"}, "item_2_text_3": {"type": "textarea", "label": "Item 2 Text 3"}, "item_2_title_2": {"type": "text", "label": "Item 2 Title 2"}, "item_2_description_2": {"type": "textarea", "label": "Item 2 Description 2"}, "item_2_text_4": {"type": "textarea", "label": "Item 2 Text 4"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"item_1_button_url": "/urun-detay", "item_1_background_image_url": "/assets/img/aespio-product-beta-glukan-mask.jpg", "item_1_button_url_2": "/urun-detay", "item_1_background_image_url_2": "/assets/img/aespio-product-hyaluronic-acid-mask.jpg", "button_url": "/urun-detay", "background_image_url": "/assets/img/aespio-product-feelsoft.jpg", "item_2_button_url": "/urun-detay", "item_2_background_image_url": "/assets/img/aespio-product-fmc.jpg", "item_2_button_url_2": "/urun-detay", "item_2_background_image_url_2": "/assets/img/aespio-product-lfl-anchor.jpg", "text": "Ürün Yelpazesi", "title": "İleri formül maskelerden ip askı sistemlerine", "description": "İki güçlü hat tek bir mühendislik dilinde: ileri formül maske serisi ve çok yönlü ip askı serisi.", "item_1_text": "Yatıştırıcı Maske", "item_1_title": "Beta-Glukan Mask", "item_1_description": "Beta-glukan ile hassas cildi yatıştıran, onarıcı ileri formül yüz maskesi.", "item_1_text_2": "İncele", "item_1_text_3": "Nemlendirici Maske", "item_1_title_2": "Hyaluronic Acid Mask", "item_1_description_2": "Hyalüronik asit ile yoğun nem desteği; dolgun ve ışıltılı bir cilt hissi.", "item_1_text_4": "İncele", "text_2": "İp Askı", "title_2": "FeelSoft", "description_2": "Yumuşak doku desteği için tasarlanmış, konforlu ip askı çözümü.", "text_3": "İncele", "item_2_text": "İp Askı", "item_2_title": "FMC", "item_2_description": "Hassas uygulamalar için ince işçilikli, çok yönlü ip askı ürünü.", "item_2_text_2": "İncele", "item_2_text_3": "Thread Lift", "item_2_title_2": "LFL Anchor", "item_2_description_2": "Güçlü tutuş için çapalı (anchor) tasarımlı ip askı / thread lift sistemi.", "item_2_text_4": "İncele"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-aespio-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'6) DOKTORLAR İÇİN / UYGULAMA',
             'html_template'=><<<'EDHTML'
<!-- 6) DOKTORLAR İÇİN / UYGULAMA -->

    <section class="ae-sec">
      <div class="ae-wrap">
        <div class="ae-docs ae-rev">
          <div class="ae-docs__grid" aria-hidden="true"></div>
          <span class="ae-eyebrow">{{text}}</span>
          <h2>{{title}}</h2>
          <p class="ae-docs__lead">{{{body_html}}}</p>
          <div class="ae-docs__cards">
            <div class="ae-docc">
              <div class="ae-docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div>
              <h4>{{group_1_eyebrow}}</h4>
              <p>{{group_1_description}}</p>
            </div>
            <div class="ae-docc">
              <div class="ae-docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 3h6M10 3v5L5 19a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1L14 8V3"/></svg></div>
              <h4>{{group_2_eyebrow}}</h4>
              <p>{{group_2_description}}</p>
            </div>
            <div class="ae-docc">
              <div class="ae-docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 14h2"/></svg></div>
              <h4>{{group_3_eyebrow}}</h4>
              <p>{{group_3_description}}</p>
            </div>
          </div>
          <a class="ae-btn ae-btn--ghost" href="{{button_url}}">{{button_text}}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "group_1_eyebrow": {"type": "text", "label": "Group 1 Eyebrow"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_eyebrow": {"type": "text", "label": "Group 2 Eyebrow"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_3_eyebrow": {"type": "text", "label": "Group 3 Eyebrow"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "/iletisim", "text": "Hekimler İçin", "title": "Cihaz hassasiyetiyle, profesyonel uygulamaya hazır", "body_html": "Grand Aespio ürünleri yalnızca profesyonel kullanıma yöneliktir. Estetik Dermal, doğru ve uyumlu uygulama için ürün bilgisi, teknik içerik ve eğitim desteğiyle hekimin yanındadır.", "group_1_eyebrow": "Uygulama Eğitimi", "group_1_description": "İp askı ve maske protokolleri için ürün ve teknik kullanım bilgisi.", "group_2_eyebrow": "İleri Formül Bilgisi", "group_2_description": "Beta-glukan ve hyalüronik asit aktiflerine dair teknik içerik.", "group_3_eyebrow": "Tedarik & Destek", "group_3_description": "Estetik Dermal temsilciliğiyle güvenilir tedarik ve teknik destek.", "button_text": "Hekimler için bilgi alın"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-aespio-6'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'7) CTA BANDI (WhatsApp)',
             'html_template'=><<<'EDHTML'
<!-- 7) CTA BANDI (WhatsApp) -->

    <section class="ae-sec" style="padding-top:0;">
      <div class="ae-wrap">
        <div class="ae-cta ae-rev">
          <div class="ae-cta__grid" aria-hidden="true"></div>
          <span class="ae-eyebrow" style="justify-content:center;">{{text}}</span>
          <h2>{{title}}<br>{{title_2}}</h2>
          <p>{{description}}</p>
          <a class="ae-btn ae-btn--wa" href="{{button_url}}" target="_blank" rel="noopener" style="font-size:16px;padding:17px 36px;">
            <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:20px;height:20px;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
            {{button_text}}</a>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "title_2": {"type": "text", "label": "Title 2"}, "description": {"type": "textarea", "label": "Description"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "text": "Grand Aespio · Estetik Dermal", "title": "Yeni nesil sistemler hakkında", "title_2": "bize ulaşın", "description": "İp askı serisi ve ileri formül maskeler için ürün bilgisi, fiyat ve eğitim talepleriniz için ekibimiz hazır.", "button_text": "WhatsApp ile Yaz"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-woorhi-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'1 · HERO — gri mühendislik gridi + cihaz silüeti + elektrik glow',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><style>
    /* Sayfa-özel marka değişkenleri (wh- ad alanı) — global :root'a dokunmaz */
    .wh {
      --wh-ink:    #0E1116;   /* en koyu zemin */
      --wh-ink-2:  #161A22;   /* panel koyu */
      --wh-grey:   #2B2F36;   /* soğuk gri */
      --wh-line:   rgba(255,255,255,.10);
      --wh-line-2: rgba(45,168,255,.24);
      --wh-blue:   #2DA8FF;   /* elektrik mavi aksan */
      --wh-cyan:   #5BE6D4;   /* cyan */
      --wh-txt:    #E6EDF5;   /* açık metin (AA) */
      --wh-txt-2:  #AEBCCC;   /* ikincil metin */
      --wh-txt-3:  #8395A8;   /* üçüncül / mono */
      --wh-ease:   cubic-bezier(.22,.61,.36,1);
    }
    .wh-disp { font-family:"Space Grotesk","Rajdhani","Segoe UI",system-ui,sans-serif; letter-spacing:-.012em; }
    .wh-mono { font-family:"JetBrains Mono",ui-monospace,SFMono-Regular,Menlo,monospace; }

    /* Eyebrow — teknik mono çip */
    .wh-eyebrow {
      display:inline-flex; align-items:center; gap:9px;
      background:rgba(45,168,255,.10); border:1px solid var(--wh-line-2);
      color:#9AD6FF; border-radius:999px; padding:7px 15px;
      font-size:11.5px; letter-spacing:1.4px; text-transform:uppercase; font-weight:500;
    }
    .wh-eyebrow .dot { width:7px; height:7px; border-radius:50%; background:var(--wh-cyan); box-shadow:0 0 9px var(--wh-cyan); }
    .wh-tag { color:var(--wh-cyan); font-size:12px; letter-spacing:1.5px; text-transform:uppercase; }

    /* Butonlar (marka) */
    .wh-btn { display:inline-flex; align-items:center; gap:9px; border-radius:999px; font-weight:700; font-size:15px; line-height:1; transition:transform .25s var(--wh-ease), box-shadow .25s; border:1px solid transparent; }
    .wh-btn:focus-visible { outline:3px solid var(--wh-cyan); outline-offset:3px; }
    .wh-btn--primary { background:linear-gradient(135deg,var(--wh-blue),var(--wh-cyan)); color:var(--wh-ink); padding:15px 30px; box-shadow:0 0 30px rgba(45,168,255,.40); font-weight:800; }
    .wh-btn--primary:hover { transform:translateY(-2px); box-shadow:0 0 42px rgba(45,168,255,.6); }
    .wh-btn--ghost { background:rgba(255,255,255,.05); color:var(--wh-txt); border-color:rgba(255,255,255,.20); padding:15px 28px; }
    .wh-btn--ghost:hover { transform:translateY(-2px); border-color:var(--wh-blue); }
    .wh-btn--wa { background:#25D366; color:#fff; padding:15px 28px; box-shadow:0 0 26px rgba(37,211,102,.40); }
    .wh-btn--wa:hover { transform:translateY(-2px); box-shadow:0 0 38px rgba(37,211,102,.55); }
    .wh-btn svg { width:18px; height:18px; flex-shrink:0; }

    /* Bölüm sarmalayıcı */
    .wh-sec { padding:clamp(70px,10vw,120px) 0; }
    .wh-h2 { font-size:clamp(28px,4vw,46px); line-height:1.08; color:#F4F8FF; font-weight:700; margin:0; }
    .wh-lead { color:var(--wh-txt-2); font-size:clamp(16px,1.7vw,19px); line-height:1.75; }

    /* Glass panel */
    .wh-glass { background:linear-gradient(160deg,rgba(255,255,255,.055),rgba(255,255,255,.018)); border:1px solid var(--wh-line); border-radius:18px; backdrop-filter:blur(8px); }

    /* Spec tablosu */
    .wh-spec { border:1px solid var(--wh-line-2); border-radius:16px; overflow:hidden; background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.018)); }
    .wh-spec__row { display:flex; justify-content:space-between; gap:16px; padding:13px 20px; font-size:13.5px; border-top:1px solid rgba(255,255,255,.07); }
    .wh-spec__row:first-child { border-top:0; }
    .wh-spec__k { color:var(--wh-txt-3); white-space:nowrap; }
    .wh-spec__v { color:var(--wh-txt); text-align:right; }

    /* Kart hover */
    .wh-card { transition:transform .28s var(--wh-ease), border-color .28s, box-shadow .28s; }
    .wh-card:hover { transform:translateY(-5px); border-color:var(--wh-line-2); box-shadow:0 22px 50px -24px rgba(45,168,255,.4); }

    /* Teknik grid arka planı (dekor) */
    .wh-gridbg { position:absolute; inset:0; background-image:linear-gradient(rgba(255,255,255,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.045) 1px,transparent 1px); background-size:48px 48px; }

    /* "scanline" hassas-teknik motion (ölçülü) */
    @keyframes wh-scan { 0%{transform:translateY(-100%);opacity:0} 12%{opacity:.9} 88%{opacity:.9} 100%{transform:translateY(900%);opacity:0} }
    .wh-scanline { position:absolute; left:0; right:0; height:2px; background:linear-gradient(90deg,transparent,var(--wh-blue),transparent); filter:drop-shadow(0 0 6px var(--wh-blue)); animation:wh-scan 4.6s cubic-bezier(.7,0,.3,1) infinite; }
    @keyframes wh-pulse { 0%,100%{opacity:.55} 50%{opacity:1} }
    .wh-live { animation:wh-pulse 2.4s ease-in-out infinite; }

    .wh ::selection { background:var(--wh-blue); color:var(--wh-ink); }

    /* Layout: tek-kolon kırılımı (scoped) */
    .wh-2col { display:grid; gap:clamp(40px,6vw,76px); align-items:center; }
    @media (max-width:960px){ .wh-2col { grid-template-columns:1fr !important; } }
    @media (prefers-reduced-motion:reduce){ .wh-scanline,.wh-live{ animation:none; } .wh-scanline{display:none;} }
  </style><!-- 1 · HERO — gri mühendislik gridi + cihaz silüeti + elektrik glow -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(1000px 540px at 84% -10%, rgba(45,168,255,.30), transparent 60%),radial-gradient(760px 540px at 6% 112%, rgba(91,230,212,.16), transparent 62%),linear-gradient(180deg,#0E1116 0%,#161A22 100%);">
      <div aria-hidden="true" class="wh-gridbg" style="-webkit-mask-image:radial-gradient(1000px 620px at 72% 4%,#000,transparent 78%);mask-image:radial-gradient(1000px 620px at 72% 4%,#000,transparent 78%);"></div>
      <div class="wrap wh-2col" style="position:relative;grid-template-columns:1.06fr .94fr;padding:clamp(72px,9vw,104px) 0 clamp(80px,9vw,108px);">
        <div class="reveal">
          <p class="wh-mono wh-eyebrow" style="margin:0 0 26px;"><span class="dot" aria-hidden="true"></span>{{subtitle}}</p>
          <h1 class="wh-disp" style="font-size:clamp(38px,5.6vw,66px);line-height:1.04;font-weight:700;color:#F4F8FF;margin:0 0 22px;">{{title}}<br><span style="background:linear-gradient(110deg,#2DA8FF 0%,#5BE6D4 100%);-webkit-background-clip:text;background-clip:text;color:transparent;">{{text}}</span></h1>
          <p class="wh-lead" style="max-width:560px;margin:0 0 34px;">{{{body_html}}}</p>
          <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a class="wh-btn wh-btn--primary" href="{{button_url}}">{{button_text}}</a>
            <a class="wh-btn wh-btn--wa" href="{{button_url_2}}" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
              {{button_text_2}}</a>
          </div>
          <div class="wh-mono reveal d2" style="display:flex;gap:26px;flex-wrap:wrap;margin-top:42px;padding-top:26px;border-top:1px solid var(--wh-line);font-size:13px;color:var(--wh-txt-3);">
            <div><div style="color:var(--wh-cyan);font-size:11px;letter-spacing:1px;">{{group_1_text}}</div><div style="color:var(--wh-txt);font-weight:500;margin-top:3px;">{{group_2_text}}</div></div>
            <div aria-hidden="true" style="width:1px;background:var(--wh-line);"></div>
            <div><div style="color:var(--wh-cyan);font-size:11px;letter-spacing:1px;">{{group_1_text_2}}</div><div style="color:var(--wh-txt);font-weight:500;margin-top:3px;">{{group_2_text_2}}</div></div>
            <div aria-hidden="true" style="width:1px;background:var(--wh-line);"></div>
            <div><div style="color:var(--wh-cyan);font-size:11px;letter-spacing:1px;">{{group_1_text_3}}</div><div style="color:var(--wh-txt);font-weight:500;margin-top:3px;">{{group_2_text_3}}</div></div>
          </div>
        </div>

        <!-- Cihaz (Raffine) silüeti + elektrik aksan glow / teknik HUD kart -->
        <!-- 🖼️ GÖRSEL: /assets/img/woorhi-device.jpg (oran 4:3)
             PROMPT: "Photorealistic product shot of a premium South Korean medical aesthetic device, futuristic precision engineering, brushed metal and tempered glass housing, glowing electric blue and cyan neon edge lighting, dark grey engineering studio background, illuminated control display, professional clinical-grade equipment, dramatic rim light, cinematic, no text, no logo, no watermark"
             DEĞİŞTİR → <img src="/assets/img/woorhi-device.jpg" alt="Woorhi Raffine medikal estetik cihazı — koyu gri zeminde elektrik mavi-cyan ışık vurgulu fütüristik ürün çekimi" style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:16px;"> -->
        <div class="reveal d1" style="position:relative;min-height:430px;">
          <div aria-hidden="true" style="position:absolute;inset:6px;border-radius:28px;background:linear-gradient(135deg,rgba(45,168,255,.32),rgba(91,230,212,.18));filter:blur(30px);"></div>
          <div class="wh-glass" style="position:relative;padding:24px;box-shadow:0 24px 70px rgba(0,0,0,.55),inset 0 1px 0 rgba(255,255,255,.07);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
              <span class="wh-mono" style="font-size:11px;letter-spacing:1.5px;color:var(--wh-cyan);">{{group_1_text_4}}</span>
              <span class="wh-mono" style="display:inline-flex;align-items:center;gap:6px;font-size:11px;color:#9AD6FF;"><span class="wh-live" aria-hidden="true" style="width:7px;height:7px;border-radius:50%;background:var(--wh-cyan);box-shadow:0 0 10px var(--wh-cyan);"></span>{{group_2_text_4}}</span>
            </div>
            <div style="position:relative;aspect-ratio:4/3;border-radius:16px;overflow:hidden;border:1px solid var(--wh-line-2);background:radial-gradient(130% 130% at 50% 0%,rgba(45,168,255,.24),rgba(14,17,22,.92)),url('/assets/img/woorhi-device.jpg');background-size:cover;background-position:center;display:grid;place-items:center;">
              <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(90deg,rgba(45,168,255,.10) 1px,transparent 1px),linear-gradient(rgba(45,168,255,.10) 1px,transparent 1px);background-size:26px 26px;"></div>
              <div class="wh-scanline" aria-hidden="true"></div>
              <!-- cihaz silüeti (görsel yokken görünür wireframe) -->
              <svg viewBox="0 0 120 90" width="58%" aria-hidden="true" style="position:relative;opacity:.5;filter:drop-shadow(0 0 10px rgba(45,168,255,.6));"><g fill="none" stroke="#2DA8FF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="30" y="14" width="60" height="40" rx="6"/><rect x="40" y="24" width="40" height="20" rx="3" stroke="#5BE6D4"/><path d="M60 54v14M44 68h32"/><circle cx="60" cy="76" r="4" stroke="#5BE6D4"/><path d="M36 20h6M78 20h6"/></g></svg>
            </div>
            <div class="wh-mono" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px;font-size:12px;">
              <div style="background:rgba(255,255,255,.04);border:1px solid var(--wh-line);border-radius:10px;padding:10px 12px;"><div style="color:var(--wh-txt-3);">{{group_1_text_5}}</div><div style="color:var(--wh-txt);margin-top:2px;">{{group_2_text_5}}</div></div>
              <div style="background:rgba(255,255,255,.04);border:1px solid var(--wh-line);border-radius:10px;padding:10px 12px;"><div style="color:var(--wh-txt-3);">{{group_1_text_6}}</div><div style="color:var(--wh-txt);margin-top:2px;">{{group_2_text_6}}</div></div>
            </div>
          </div>
          <!-- floating rozeti -->
          <div style="position:absolute;bottom:-16px;left:-14px;background:rgba(22,26,34,.94);border:1px solid var(--wh-line-2);border-radius:14px;box-shadow:0 0 26px rgba(45,168,255,.28);padding:12px 16px;display:flex;align-items:center;gap:11px;">
            <span aria-hidden="true" style="width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,#2DA8FF,#5BE6D4);box-shadow:0 0 14px rgba(45,168,255,.5);flex-shrink:0;"></span>
            <div><div class="wh-mono" style="font-size:10px;color:var(--wh-cyan);letter-spacing:1px;">{{group_2_text_7}}</div><div style="font-weight:700;font-size:13px;color:var(--wh-txt);">{{group_2_text_8}}</div></div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "text": {"type": "textarea", "label": "Text"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_1_text_3": {"type": "textarea", "label": "Group 1 Text 3"}, "group_2_text_3": {"type": "textarea", "label": "Group 2 Text 3"}, "group_1_text_4": {"type": "textarea", "label": "Group 1 Text 4"}, "group_2_text_4": {"type": "textarea", "label": "Group 2 Text 4"}, "group_1_text_5": {"type": "textarea", "label": "Group 1 Text 5"}, "group_2_text_5": {"type": "textarea", "label": "Group 2 Text 5"}, "group_1_text_6": {"type": "textarea", "label": "Group 1 Text 6"}, "group_2_text_6": {"type": "textarea", "label": "Group 2 Text 6"}, "group_2_text_7": {"type": "textarea", "label": "Group 2 Text 7"}, "group_2_text_8": {"type": "textarea", "label": "Group 2 Text 8"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap", "button_url": "#raffine", "button_url_2": "https://wa.me/905426205100", "subtitle": "Güney Kore · Medikal Mekatronik", "title": "Mühendislik hassasiyetinde", "text": "estetik teknolojisi", "body_html": "Woorhi Mechatronics Co. Ltd., Kore mühendisliğiyle geliştirilen medikal estetik cihazları üretir. Hassas kontrol, klinik dayanıklılık ve tekrarlanabilir sonuçlar — kliniğinizin teknolojik altyapısı için tasarlandı.", "button_text": "Cihazı İncele →", "button_text_2": "Bilgi Al — WhatsApp", "group_1_text": "ORIGIN", "group_2_text": "Seoul · KR", "group_1_text_2": "CLASS", "group_2_text_2": "Klinik Cihaz", "group_1_text_3": "DIST · TR", "group_2_text_3": "Estetik Dermal", "group_1_text_4": "WOORHI · UNIT-01", "group_2_text_4": "ONLINE", "group_1_text_5": "PRECISION", "group_2_text_5": "± hassas kontrol", "group_1_text_6": "BUILD", "group_2_text_6": "KR Engineering", "group_2_text_7": "RAFFINE", "group_2_text_8": "Ana Cihaz Serisi"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-woorhi-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'2 · MARKA HİKAYESİ — Kore mühendisliği, hassas kontrol, tekrarlanabilirlik',
             'html_template'=><<<'EDHTML'
<!-- 2 · MARKA HİKAYESİ — Kore mühendisliği, hassas kontrol, tekrarlanabilirlik -->

    <section class="wh-sec" style="background:var(--wh-ink);background-image:radial-gradient(720px 460px at 100% 0%, rgba(45,168,255,.10), transparent 60%);">
      <div class="wrap wh-2col" style="grid-template-columns:.95fr 1.05fr;">
        <div class="reveal" style="position:relative;min-height:380px;">
          <!-- 🖼️ GÖRSEL: /assets/img/woorhi-engineering.jpg (oran 4:5)
               PROMPT: "Photorealistic macro detail of South Korean precision mechatronic engineering, brushed metal device internals, glowing electric blue circuit traces and cyan light, cold grey studio background, high-tech clinical equipment, dramatic lighting, no text, no logo, no watermark"
               DEĞİŞTİR → <img src="/assets/img/woorhi-engineering.jpg" alt="Woorhi hassas mekatronik mühendisliği — koyu gri zeminde elektrik mavi devre vurgulu makro detay" style="width:100%;height:100%;object-fit:cover;border-radius:20px;"> -->
          <div aria-hidden="true" style="position:absolute;inset:10px;border-radius:24px;background:linear-gradient(135deg,rgba(45,168,255,.22),rgba(91,230,212,.14));filter:blur(28px);"></div>
          <div style="position:relative;height:100%;min-height:380px;border-radius:20px;overflow:hidden;border:1px solid var(--wh-line);background:linear-gradient(160deg,#22272F,#10141B),url('/assets/img/woorhi-engineering.jpg');background-size:cover;background-position:center;box-shadow:0 24px 60px rgba(0,0,0,.5);">
            <div aria-hidden="true" class="wh-gridbg" style="opacity:.6;"></div>
            <div class="wh-scanline" aria-hidden="true"></div>
            <div class="wh-mono" style="position:absolute;left:18px;bottom:16px;font-size:11px;letter-spacing:1.5px;color:var(--wh-cyan);background:rgba(14,17,22,.7);border:1px solid var(--wh-line-2);border-radius:8px;padding:6px 11px;">{{group_2_text}}</div>
          </div>
        </div>
        <div class="reveal d1">
          <p class="wh-mono wh-eyebrow" style="margin:0 0 18px;">{{subtitle}}</p>
          <h2 class="wh-disp wh-h2" style="margin-bottom:18px;">{{title}}</h2>
          <p class="wh-lead" style="margin:0 0 18px;">{{{group_1_body_html}}}</p>
          <p class="wh-lead" style="margin:0 0 30px;">{{{group_2_body_html}}}</p>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
            {{{wh_glass_items_html}}}</div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"wh_glass_items": {"type": "repeater", "label": "Wh Glass Items", "repeat_kind": "items", "item_template": "<div class=\"wh-glass\" style=\"padding:18px 16px;\">\n              <div class=\"wh-disp\" style=\"font-size:26px;font-weight:700;background:linear-gradient(120deg,#2DA8FF,#5BE6D4);-webkit-background-clip:text;background-clip:text;color:transparent;\">{{text}}</div>\n              <div class=\"wh-mono\" style=\"font-size:11px;color:var(--wh-txt-3);margin-top:6px;\">{{text_2}}</div>\n            </div>", "fields": {"text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}}}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "group_1_body_html": {"type": "textarea", "label": "Group 1 Body"}, "group_2_body_html": {"type": "textarea", "label": "Group 2 Body"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"wh_glass_items": [{"text": "±", "text_2": "Hassas kontrol"}, {"text": "KR", "text_2": "Made in Korea"}, {"text": "∞", "text_2": "Tekrarlanabilirlik"}], "group_2_text": "// PRECISION ARCHITECTURE", "subtitle": "Marka Hikayesi", "title": "Kore mühendisliği, ölçülebilir hassasiyet", "group_1_body_html": "Woorhi Mechatronics, mekatronik kontrol mimarisini medikal estetik dünyasına taşıyan bir mühendislik markasıdır. Her cihaz; kararlı güç yönetimi, hassas parametre kontrolü ve uygulama tekrarlanabilirliği ilkeleri üzerine kurulur.", "group_2_body_html": "Amaç basit ama disiplinli: hekimin belirlediği parametreyi her seferinde aynı doğrulukla sahaya yansıtmak. Bu yüzden Woorhi cihazları yoğun klinik kullanım için dayanıklılık ve sezgisel kontrol etrafında tasarlanır."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-woorhi-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'3 · KREDİBİLİTE ŞERİDİ',
             'html_template'=><<<'EDHTML'
<!-- 3 · KREDİBİLİTE ŞERİDİ -->

    <section style="background:var(--wh-ink-2);border-top:1px solid var(--wh-line);border-bottom:1px solid var(--wh-line);">
      <div class="wrap" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;padding:40px 0;">
        <!-- Güney Kore -->
        <div class="reveal wh-glass" style="display:flex;align-items:center;gap:14px;padding:18px 20px;">
          <span aria-hidden="true" style="width:40px;height:40px;border-radius:11px;flex-shrink:0;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);box-shadow:0 0 12px rgba(45,168,255,.35);"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#9AD6FF" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">{{text}}</div><div class="wh-mono" style="font-size:11px;color:var(--wh-txt-3);margin-top:2px;">{{text_2}}</div></div>
        </div>
        <!-- CE uyumlu -->
        <div class="reveal d1 wh-glass" style="display:flex;align-items:center;gap:14px;padding:18px 20px;">
          <span aria-hidden="true" style="width:40px;height:40px;border-radius:11px;flex-shrink:0;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(91,230,212,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);box-shadow:0 0 12px rgba(91,230,212,.35);"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#7CF0E1" stroke-width="1.7"><path d="M12 2 4 5v6c0 5 3.4 8.5 8 11 4.6-2.5 8-6 8-11V5z"/><path d="m9 12 2 2 4-4"/></svg></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">{{text_3}}</div><div class="wh-mono" style="font-size:11px;color:var(--wh-txt-3);margin-top:2px;">{{text_4}}</div></div>
        </div>
        <!-- Klinik cihaz -->
        <div class="reveal d2 wh-glass" style="display:flex;align-items:center;gap:14px;padding:18px 20px;">
          <span aria-hidden="true" style="width:40px;height:40px;border-radius:11px;flex-shrink:0;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);box-shadow:0 0 12px rgba(45,168,255,.35);"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#9AD6FF" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M12 9v6M9 12h6"/></svg></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">{{text_5}}</div><div class="wh-mono" style="font-size:11px;color:var(--wh-txt-3);margin-top:2px;">{{text_6}}</div></div>
        </div>
        <!-- Estetik Dermal Türkiye -->
        <div class="reveal d3 wh-glass" style="display:flex;align-items:center;gap:14px;padding:18px 20px;">
          <span aria-hidden="true" style="width:40px;height:40px;border-radius:11px;flex-shrink:0;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(91,230,212,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);box-shadow:0 0 12px rgba(91,230,212,.35);"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#7CF0E1" stroke-width="1.7"><path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.4"/></svg></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">{{text_7}}</div><div class="wh-mono" style="font-size:11px;color:var(--wh-txt-3);margin-top:2px;">{{text_8}}</div></div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "text_4": {"type": "textarea", "label": "Text 4"}, "text_5": {"type": "textarea", "label": "Text 5"}, "text_6": {"type": "textarea", "label": "Text 6"}, "text_7": {"type": "textarea", "label": "Text 7"}, "text_8": {"type": "textarea", "label": "Text 8"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"text": "Güney Kore", "text_2": "made in korea", "text_3": "CE Uyumlu", "text_4": "ce compliant", "text_5": "Klinik Cihaz", "text_6": "clinical grade", "text_7": "Estetik Dermal · TR", "text_8": "resmi distribütör"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-woorhi-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'4 · RAFFINE SPOTLIGHT + monospace teknik spec tablosu',
             'html_template'=><<<'EDHTML'
<!-- 4 · RAFFINE SPOTLIGHT + monospace teknik spec tablosu -->

    <section id="raffine" class="wh-sec" style="background:var(--wh-ink);background-image:radial-gradient(820px 520px at 100% 50%, rgba(91,230,212,.12), transparent 60%);scroll-margin-top:80px;">
      <div class="wrap wh-2col" style="grid-template-columns:.9fr 1.1fr;">
        <!-- Dikey ürün spotlight -->
        <!-- 🖼️ GÖRSEL: /assets/img/woorhi-raffine.jpg (oran 3:4)
             PROMPT: "Photorealistic vertical hero spotlight of the Woorhi Raffine flagship medical aesthetic device, precision mechatronic engineering, brushed metal and tempered glass housing, glowing electric blue and cyan neon edge lighting, dramatic cold grey studio background, illuminated digital control panel, professional clinical-grade equipment, sharp focus, cinematic, no text, no logo, no watermark"
             DEĞİŞTİR → <img src="/assets/img/woorhi-raffine.jpg" alt="Woorhi Raffine cihazı — koyu gri stüdyoda elektrik mavi-cyan ışıklı dikey ürün spotlight çekimi" style="width:100%;height:100%;object-fit:cover;border-radius:24px;"> -->
        <div class="reveal" style="position:relative;min-height:440px;">
          <div aria-hidden="true" style="position:absolute;inset:12px;border-radius:26px;background:linear-gradient(135deg,rgba(45,168,255,.30),rgba(91,230,212,.18));filter:blur(32px);"></div>
          <div style="position:relative;height:100%;min-height:440px;border-radius:24px;overflow:hidden;border:1px solid var(--wh-line);background:linear-gradient(160deg,#22272F,#0E1116),url('/assets/img/woorhi-raffine.jpg');background-size:cover;background-position:center;box-shadow:0 24px 64px rgba(0,0,0,.55);display:grid;place-items:center;">
            <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(90deg,rgba(45,168,255,.08) 1px,transparent 1px),linear-gradient(rgba(45,168,255,.08) 1px,transparent 1px);background-size:34px 34px;"></div>
            <div class="wh-scanline" aria-hidden="true"></div>
            <svg viewBox="0 0 120 150" width="46%" aria-hidden="true" style="position:relative;opacity:.5;filter:drop-shadow(0 0 12px rgba(45,168,255,.6));"><g fill="none" stroke="#2DA8FF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="34" y="18" width="52" height="34" rx="6"/><rect x="44" y="26" width="32" height="18" rx="3" stroke="#5BE6D4"/><path d="M60 52v22M40 74h40v54H40z"/><path d="M48 86h24M48 98h24M48 110h16" stroke="#5BE6D4"/></g></svg>
            <div class="wh-mono" style="position:absolute;left:16px;top:14px;font-size:11px;letter-spacing:2px;color:var(--wh-cyan);background:rgba(14,17,22,.7);border:1px solid var(--wh-line-2);border-radius:8px;padding:6px 11px;">{{group_2_text}}</div>
          </div>
        </div>
        <!-- içerik + spec -->
        <div class="reveal d1">
          <p class="wh-mono wh-tag" style="display:inline-block;margin:0 0 14px;">{{subtitle}}</p>
          <h2 class="wh-disp wh-h2" style="margin-bottom:16px;">{{title}}</h2>
          <p class="wh-lead" style="max-width:540px;margin:0 0 28px;">{{{body_html}}}</p>

          <div class="wh-mono wh-spec" style="max-width:540px;margin:0 0 30px;">
            {{{wh_spec__row_items_html}}}</div>

          <a class="wh-btn wh-btn--primary" href="{{button_url}}">{{button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"wh_spec__row_items": {"type": "repeater", "label": "Wh Spec  Row Items", "repeat_kind": "items", "item_template": "<div class=\"wh-spec__row\"><span class=\"wh-spec__k\">{{text}}</span><span class=\"wh-spec__v\">{{text_2}}</span></div>", "fields": {"text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}}}, "button_url": {"type": "text", "label": "Button Url"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"wh_spec__row_items": [{"text": "[ TİP ]", "text_2": "Medikal estetik mekatronik cihaz"}, {"text": "[ UYGULAMA ]", "text_2": "Yüz & vücut profesyonel bakım"}, {"text": "[ KONTROL ]", "text_2": "Hassas dijital parametre yönetimi"}, {"text": "[ GÖVDE ]", "text_2": "Klinik dayanımlı, sezgisel arayüz"}, {"text": "[ MENŞE ]", "text_2": "Güney Kore mühendisliği"}, {"text": "[ KULLANIM ]", "text_2": "Klinik / profesyonel"}], "button_url": "/urun-detay", "group_2_text": "RAFFINE · DEVICE", "subtitle": "// Öne Çıkan Cihaz", "title": "Raffine", "body_html": "Woorhi'nin amiral gemisi medikal estetik cihazı. Mekatronik kontrol mimarisi, kararlı güç yönetimi ve uygulama tekrarlanabilirliği üzerine kurulu; klinik kullanım için dayanıklı bir gövde ve sezgisel arayüzle tasarlandı.", "button_text": "Raffine Detayları →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-woorhi-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'5 · ÜRÜN / CİHAZ GRID (Raffine + ilgili sarf)',
             'html_template'=><<<'EDHTML'
<!-- 5 · ÜRÜN / CİHAZ GRID (Raffine + ilgili sarf) -->

    <section class="wh-sec" style="background:var(--wh-ink-2);border-top:1px solid var(--wh-line);">
      <div class="wrap">
        <div class="reveal" style="max-width:640px;margin-bottom:46px;">
          <p class="wh-mono wh-tag" style="margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 class="wh-disp wh-h2">{{group_1_title}}</h2>
          <p class="wh-lead" style="margin-top:14px;">{{{group_1_body_html}}}</p>
        </div>
        <!-- 🖼️ GÖRSEL deseni: /assets/img/woorhi-{slug}.jpg (oran 4:3) — koyu zemin, elektrik mavi-cyan ışık vurgulu teknik packshot -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;">

          <!-- Raffine ana cihaz -->
          {{{wh_card_items_html}}}<!-- Aplikatör başlıkları -->
          <!-- Sarf & tüketim -->
          </div>
        <div class="reveal" style="margin-top:36px;">
          <a class="wh-mono" href="{{group_2_button_url}}" style="display:inline-flex;align-items:center;gap:8px;color:var(--wh-cyan);font-weight:500;font-size:14px;">{{group_2_button_text}} <span aria-hidden="true">{{group_2_text}}</span></a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"wh_card_items": {"type": "repeater", "label": "Wh Card Items", "repeat_kind": "items", "item_template": "<a class=\"wh-card wh-glass\" href=\"{{button_url}}\" style=\"display:block;overflow:hidden;text-decoration:none;\">\n            <div style=\"position:relative;aspect-ratio:4/3;background:linear-gradient(160deg,#222730,#0E1116),url('/assets/img/woorhi-raffine.jpg');background-size:cover;background-position:center;border-bottom:1px solid var(--wh-line);\">\n              <div aria-hidden=\"true\" style=\"position:absolute;inset:0;background-image:linear-gradient(90deg,rgba(45,168,255,.07) 1px,transparent 1px),linear-gradient(rgba(45,168,255,.07) 1px,transparent 1px);background-size:24px 24px;\"></div>\n              <span class=\"wh-mono\" style=\"position:absolute;top:12px;left:12px;font-size:10px;letter-spacing:1.5px;color:var(--wh-ink);background:linear-gradient(135deg,#2DA8FF,#5BE6D4);border-radius:7px;padding:5px 9px;font-weight:600;\">{{text}}</span>\n            </div>\n            <div style=\"padding:22px 24px;\">\n              <div class=\"wh-mono\" style=\"font-size:11px;color:var(--wh-cyan);letter-spacing:1px;\">{{text_2}}</div>\n              <h3 class=\"wh-disp\" style=\"font-size:21px;font-weight:600;color:#F4F8FF;margin:6px 0 8px;\">{{title}}</h3>\n              <p style=\"color:var(--wh-txt-2);font-size:14.5px;line-height:1.65;margin:0;\">{{description}}</p>\n            </div>\n          </a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "text_2": {"type": "textarea", "label": "Text 2"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}}}, "group_2_button_url": {"type": "text", "label": "Group 2 Button Url"}, "group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_body_html": {"type": "textarea", "label": "Group 1 Body"}, "group_2_button_text": {"type": "textarea", "label": "Group 2 Button Text"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"wh_card_items": [{"button_url": "/urun-detay", "text": "ANA CİHAZ", "text_2": "RAFFINE", "title": "Raffine Cihazı", "description": "Amiral gemisi mekatronik medikal estetik cihazı; hassas kontrol ve klinik dayanıklılık."}, {"button_url": "/urun-detay", "text": "APLİKATÖR", "text_2": "HANDPIECE", "title": "Aplikatör Başlıkları", "description": "Farklı endikasyonlara yönelik, değiştirilebilir hassas uygulama başlıkları."}, {"button_url": "/urun-detay", "text": "SARF", "text_2": "CONSUMABLE", "title": "İlgili Sarf Bileşenleri", "description": "Cihazın kesintisiz çalışması için orijinal tüketim ve sarf malzemeleri."}], "group_2_button_url": "/urunler", "group_1_subtitle": "// Cihaz & Sarf", "group_1_title": "Raffine ve ilgili profesyonel sarf", "group_1_body_html": "Cihaz, aplikatörleri ve sarf bileşenleriyle eksiksiz bir klinik kurulum. Tüm hat, Estetik Dermal güvencesiyle Türkiye'de.", "group_2_button_text": "Tüm Woorhi ürünlerini gör", "group_2_text": "→"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-woorhi-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'6 · DOKTORLAR İÇİN / UYGULAMA — klinik cihaz kullanımı, demo/eğitim',
             'html_template'=><<<'EDHTML'
<!-- 6 · DOKTORLAR İÇİN / UYGULAMA — klinik cihaz kullanımı, demo/eğitim -->

    <section class="wh-sec" style="background:var(--wh-ink);">
      <div class="wrap">
        <div style="text-align:center;max-width:660px;margin:0 auto 52px;">
          <p class="wh-mono wh-tag reveal" style="margin:0 0 12px;">{{group_1_subtitle}}</p>
          <h2 class="wh-disp wh-h2 reveal d1">{{group_1_title}}</h2>
          <p class="wh-lead reveal d2" style="margin-top:14px;">{{group_1_description}}</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:22px;">
          <!-- İleri teknoloji -->
          <div class="reveal wh-glass" style="padding:34px 30px;box-shadow:0 14px 40px rgba(0,0,0,.35);">
            <div aria-hidden="true" style="width:54px;height:54px;border-radius:15px;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);margin-bottom:20px;box-shadow:0 0 22px rgba(45,168,255,.3);"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#BFE4FF" stroke-width="1.6"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M9 9h6v6H9z"/><path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2"/></svg></div>
            <h3 class="wh-disp" style="font-size:20px;font-weight:600;color:#F4F8FF;margin:0 0 10px;">{{group_2_title}}</h3>
            <p style="color:var(--wh-txt-2);line-height:1.7;margin:0;font-size:15px;">{{group_2_description}}</p>
          </div>
          <!-- Klinik dayanıklılık -->
          <div class="reveal d1 wh-glass" style="padding:34px 30px;box-shadow:0 14px 40px rgba(0,0,0,.35);">
            <div aria-hidden="true" style="width:54px;height:54px;border-radius:15px;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(91,230,212,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);margin-bottom:20px;box-shadow:0 0 22px rgba(91,230,212,.3);"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#9CF0E2" stroke-width="1.6"><path d="M12 2 4 5v6c0 5 3.4 8.5 8 11 4.6-2.5 8-6 8-11V5z"/></svg></div>
            <h3 class="wh-disp" style="font-size:20px;font-weight:600;color:#F4F8FF;margin:0 0 10px;">{{group_2_title_2}}</h3>
            <p style="color:var(--wh-txt-2);line-height:1.7;margin:0;font-size:15px;">{{group_2_description_2}}</p>
          </div>
          <!-- Yerel destek & eğitim -->
          <div class="reveal d2 wh-glass" style="padding:34px 30px;box-shadow:0 14px 40px rgba(0,0,0,.35);">
            <div aria-hidden="true" style="width:54px;height:54px;border-radius:15px;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);margin-bottom:20px;box-shadow:0 0 22px rgba(45,168,255,.3);"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#BFE4FF" stroke-width="1.6"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div>
            <h3 class="wh-disp" style="font-size:20px;font-weight:600;color:#F4F8FF;margin:0 0 10px;">{{group_2_title_3}}</h3>
            <p style="color:var(--wh-txt-2);line-height:1.7;margin:0;font-size:15px;">{{group_2_description_3}}</p>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_subtitle": {"type": "textarea", "label": "Group 1 Subtitle"}, "group_1_title": {"type": "text", "label": "Group 1 Title"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_title": {"type": "text", "label": "Group 2 Title"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_2_title_2": {"type": "text", "label": "Group 2 Title 2"}, "group_2_description_2": {"type": "textarea", "label": "Group 2 Description 2"}, "group_2_title_3": {"type": "text", "label": "Group 2 Title 3"}, "group_2_description_3": {"type": "textarea", "label": "Group 2 Description 3"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_subtitle": "// Doktorlar İçin", "group_1_title": "Mühendisliğin estetikle buluştuğu nokta", "group_1_description": "Woorhi cihazlarının doğru ve güvenli kullanımı için kurulum, uygulamalı eğitim ve demo planlamasıyla yanınızdayız.", "group_2_title": "İleri Teknoloji", "group_2_description": "Mekatronik kontrol mimarisi ve hassas parametre yönetimiyle tekrarlanabilir, kontrollü uygulamalar.", "group_2_title_2": "Klinik Dayanıklılık", "group_2_description_2": "Yoğun klinik kullanım için tasarlanmış sağlam gövde, kararlı güç yönetimi ve uzun ömürlü bileşenler.", "group_2_title_3": "Eğitim & Yerel Destek", "group_2_description_3": "Estetik Dermal güvencesiyle Türkiye'de kurulum, uygulamalı eğitim, demo ve kesintisiz teknik servis."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-woorhi-6'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'7 · CTA BANDI — WhatsApp / demo talebi',
             'html_template'=><<<'EDHTML'
<!-- 7 · CTA BANDI — WhatsApp / demo talebi -->

    <section style="padding:0 0 clamp(70px,9vw,108px);background:var(--wh-ink);">
      <div class="wrap">
        <div class="reveal" style="position:relative;overflow:hidden;border-radius:28px;background:linear-gradient(120deg,#0E2A44 0%,#0E1C2C 46%,#0B2A2A 100%);border:1px solid var(--wh-line-2);padding:clamp(40px,6vw,72px);text-align:center;box-shadow:0 0 60px rgba(45,168,255,.18);">
          <div aria-hidden="true" style="position:absolute;inset:0;background-image:radial-gradient(600px 300px at 14% 0%, rgba(45,168,255,.4), transparent 60%),radial-gradient(600px 300px at 90% 100%, rgba(91,230,212,.3), transparent 60%);"></div>
          <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:42px 42px;-webkit-mask-image:radial-gradient(700px 320px at 50% 50%,#000,transparent 75%);mask-image:radial-gradient(700px 320px at 50% 50%,#000,transparent 75%);"></div>
          <div style="position:relative;">
            <p class="wh-mono wh-tag" style="color:#9AD6FF;margin:0 0 14px;">{{group_3_subtitle}}</p>
            <h2 class="wh-disp wh-h2" style="margin:0 0 14px;">{{group_3_title}}</h2>
            <p class="wh-lead" style="color:#C7D3E2;max-width:600px;margin:0 auto 32px;">{{{group_3_body_html}}}</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a class="wh-btn wh-btn--wa" href="{{group_3_button_url}}" target="_blank" rel="noopener" style="padding:15px 32px;">
                <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
                {{group_3_button_text}}</a>
              <a class="wh-btn wh-btn--ghost" href="{{group_3_button_url_2}}" style="padding:15px 32px;">{{group_3_button_text_2}}</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_3_button_url": {"type": "text", "label": "Group 3 Button Url"}, "group_3_button_url_2": {"type": "text", "label": "Group 3 Button Url 2"}, "group_3_subtitle": {"type": "textarea", "label": "Group 3 Subtitle"}, "group_3_title": {"type": "text", "label": "Group 3 Title"}, "group_3_body_html": {"type": "textarea", "label": "Group 3 Body"}, "group_3_button_text": {"type": "textarea", "label": "Group 3 Button Text"}, "group_3_button_text_2": {"type": "textarea", "label": "Group 3 Button Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_3_button_url": "https://wa.me/905426205100", "group_3_button_url_2": "/iletisim", "group_3_subtitle": "// Demo & Teklif", "group_3_title": "Woorhi cihazları için demo / teklif alın", "group_3_body_html": "Raffine ve Woorhi cihaz serisi hakkında detaylı bilgi, demo planlaması ve fiyat teklifi için Estetik Dermal ekibine ulaşın.", "group_3_button_text": "WhatsApp ile Yaz", "group_3_button_text_2": "Bilgi Al"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-mi-medical-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'1. HERO (full-height)',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><style>
    /* ============================================================
       MI-MEDICAL INNOVATION — temiz klinik, güven-odaklı teal palet.
       NOT: global v2 :root EZİLMEZ; tüm marka token'ları .mi
       scope'unda tutulur ki paylaşılan nav/footer turuncu kimliği
       bozulmasın. Kontrastlar WCAG AA hedefli.
       ============================================================ */
    .mi {
      --teal:      #0E8C8C;   /* ana teal */
      --teal-deep: #0B6E6E;   /* koyu teal */
      --teal-ink:  #083F40;   /* en koyu — metin/başlık */
      --mist:      #E6F2F1;   /* açık teal-gri yüzey */
      --mist-soft: #F2F8F8;   /* daha açık zemin */
      --slate:     #4A6566;   /* yumuşak gri-teal metin */
      --line:      #D5E6E5;   /* ince çizgi */
      --paper:     #FFFFFF;   /* beyaz zemin */
      --wa:        #25D366;   /* whatsapp yeşil */
      --serif: "Newsreader", Georgia, "Times New Roman", serif;
      --grad-teal: linear-gradient(135deg, #0E8C8C 0%, #0B6E6E 100%);
      --grad-mist: linear-gradient(165deg, #F2F8F8 0%, #E6F2F1 60%, #FFFFFF 100%);
      --shadow-clean: 0 22px 54px rgba(11, 110, 110, .14);
      --shadow-soft:  0 8px 24px rgba(8, 63, 64, .08);

      font-family: "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
      color: var(--teal-ink);
      background: var(--paper);
    }
    .mi ::selection { background: #BFE3E1; color: #083F40; }

    /* yapı */
    .mi-wrap { width: min(1180px, 92%); margin-inline: auto; }
    .mi-serif { font-family: var(--serif); }
    .mi-eyebrow {
      display: inline-flex; align-items: center; gap: 10px;
      color: var(--teal); font-size: 12px; font-weight: 700;
      letter-spacing: 3px; text-transform: uppercase; margin: 0 0 22px;
    }
    .mi-eyebrow::before { content: ""; width: 26px; height: 1px; background: currentColor; }
    .mi-eyebrow--c { justify-content: center; }
    .mi-eyebrow--light { color: #BFE3E1; }

    /* butonlar */
    .mi-btn {
      display: inline-flex; align-items: center; gap: 9px;
      padding: 16px 32px; border-radius: 12px;
      font-weight: 600; font-size: 15px; letter-spacing: .2px;
      transition: transform .22s ease, box-shadow .22s ease, background .22s ease, color .22s ease, border-color .22s ease;
      will-change: transform;
    }
    .mi-btn-fill { background: var(--grad-teal); color: #fff; box-shadow: 0 14px 30px rgba(11,110,110,.28); }
    .mi-btn-fill:hover { transform: translateY(-2px); box-shadow: 0 18px 38px rgba(11,110,110,.36); color: #fff; }
    .mi-btn-wa { background: var(--wa); color: #fff; box-shadow: 0 12px 26px rgba(37,211,102,.26); }
    .mi-btn-wa:hover { transform: translateY(-2px); box-shadow: 0 16px 34px rgba(37,211,102,.34); color: #fff; }
    .mi-btn-ghost { background: #fff; color: var(--teal-deep); border: 1px solid var(--line); }
    .mi-btn-ghost:hover { transform: translateY(-2px); border-color: var(--teal); }

    .mi-link { color: var(--teal-deep); font-weight: 600; font-size: 14px; letter-spacing: .2px; transition: color .2s ease; }
    .mi-link:hover { color: var(--teal); }

    /* kart hover */
    .mi-card { transition: transform .26s ease, box-shadow .26s ease, border-color .26s ease; }
    .mi-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-clean); border-color: #B7DAD8; }

    /* ikon dairesi */
    .mi-ico {
      width: 56px; height: 56px; border-radius: 14px; margin-bottom: 20px;
      display: grid; place-items: center; color: #fff;
      background: var(--grad-teal); box-shadow: 0 10px 22px rgba(11,110,110,.24);
    }
    .mi-ico svg { width: 27px; height: 27px; }

    /* sakin, güven veren fade reveal (kendi motion) */
    .mi [data-mi] { opacity: 0; transform: translateY(20px); transition: opacity .8s cubic-bezier(.22,.61,.36,1), transform .8s cubic-bezier(.22,.61,.36,1); }
    .mi [data-mi].in { opacity: 1; transform: none; }
    .mi [data-mi].d1 { transition-delay: .08s; }
    .mi [data-mi].d2 { transition-delay: .16s; }
    .mi [data-mi].d3 { transition-delay: .24s; }
    @media (prefers-reduced-motion: reduce) {
      .mi [data-mi] { opacity: 1 !important; transform: none !important; transition: none !important; }
    }

    /* hero */
    .mi-hero { position: relative; overflow: hidden; background: var(--grad-mist); border-bottom: 1px solid var(--line); }
    .mi-hero__grid {
      position: absolute; inset: 0; pointer-events: none; opacity: .5;
      background-image:
        linear-gradient(rgba(14,140,140,.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(14,140,140,.06) 1px, transparent 1px);
      background-size: 46px 46px;
      -webkit-mask-image: radial-gradient(ellipse 80% 70% at 70% 30%, #000 0%, transparent 75%);
              mask-image: radial-gradient(ellipse 80% 70% at 70% 30%, #000 0%, transparent 75%);
    }
    .mi-glow { position: absolute; border-radius: 50%; pointer-events: none; }
    .mi-photo { background-size: cover; background-position: center; background-repeat: no-repeat; }

    /* full-height hero layout */
    .mi-hero__in {
      position: relative; min-height: calc(100vh - 76px);
      display: flex; flex-wrap: wrap; gap: 56px; align-items: center;
      padding: clamp(72px,8vw,96px) 0 clamp(72px,8vw,96px);
    }

    /* pill rozet */
    .mi-pill {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 7px 14px; border-radius: 999px; background: #fff;
      border: 1px solid var(--line); box-shadow: var(--shadow-soft);
      font-size: 12.5px; font-weight: 600; color: var(--teal-deep);
    }
    .mi-pill .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--teal); }

    /* hero trust strip */
    .mi-trust { display: flex; flex-wrap: wrap; gap: 28px 40px; margin-top: 42px; }
    .mi-trust div { font-size: 13.5px; color: var(--slate); line-height: 1.5; }
    .mi-trust b { display: block; font-size: 20px; color: var(--teal-ink); font-weight: 600; font-family: var(--serif); }

    /* kredibilite şeridi */
    .mi-cred { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px,1fr)); gap: 1px; background: var(--line); border: 1px solid var(--line); border-radius: 20px; overflow: hidden; }
    .mi-cred > div { background: #fff; padding: 32px 28px; }
    .mi-cred h4 { font-size: 16px; color: var(--teal-ink); margin: 0 0 8px; font-weight: 600; }
    .mi-cred p { font-size: 13.5px; color: var(--slate); line-height: 1.7; margin: 0; }

    /* split (öne çıkan spotlight) */
    .mi-split { display: flex; flex-wrap: wrap; gap: 60px; align-items: center; position: relative; }
    .mi-split .mi-col { flex: 1 1 380px; }
    .mi-feat-list { list-style: none; margin: 0 0 32px; padding: 0; display: grid; gap: 14px; }
    .mi-feat-list li { display: flex; align-items: flex-start; gap: 12px; color: var(--teal-ink); font-weight: 500; line-height: 1.5; }
    .mi-feat-list .ck { flex-shrink: 0; width: 22px; height: 22px; margin-top: 1px; border-radius: 7px; display: grid; place-items: center; background: var(--mist); color: var(--teal-deep); }
    .mi-feat-list .ck svg { width: 13px; height: 13px; }

    /* ürün grid */
    .mi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 26px; }
    .mi-prodcard {
      display: flex; flex-direction: column; background: #fff; border: 1px solid var(--line);
      border-radius: 22px; padding: 30px 28px; height: 100%;
    }
    .mi-prodcard__img {
      aspect-ratio: 4/3; border-radius: 14px; margin-bottom: 22px;
      background-color: var(--mist); background-size: cover; background-position: center;
    }

    /* doktorlar için kart */
    .mi-docgrid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px,1fr)); gap: 24px; }
    .mi-doc { background: var(--mist-soft); border: 1px solid var(--line); border-radius: 20px; padding: 30px 28px; }
    .mi-doc h4 { font-size: 17px; color: var(--teal-ink); margin: 16px 0 8px; font-weight: 600; }
    .mi-doc p { font-size: 14px; color: var(--slate); line-height: 1.7; margin: 0; }

    @media (max-width: 720px) {
      .mi-hero__in { min-height: auto; }
    }
  </style><!-- 1. HERO (full-height) -->

    <section class="mi-hero">
      <div class="mi-hero__grid" aria-hidden="true"></div>
      <div class="mi-glow" aria-hidden="true" style="top:-180px;right:-100px;width:520px;height:520px;background:radial-gradient(circle at 40% 40%, rgba(14,140,140,.18), transparent 70%);"></div>
      <div class="mi-glow" aria-hidden="true" style="bottom:-200px;left:-150px;width:440px;height:440px;background:radial-gradient(circle at 50% 50%, rgba(11,110,110,.12), transparent 70%);"></div>

      <div class="mi-wrap mi-hero__in">
        <div style="flex:1 1 480px;">
          <span class="mi-pill" data-mi><span class="dot"></span>{{text}}</span>
          <h1 class="mi-serif d1" data-mi style="font-size:clamp(42px,6vw,76px);line-height:1.04;font-weight:500;color:var(--teal-ink);margin:24px 0 24px;">{{title}}<br>{{title_2}} <em style="font-style:italic;color:var(--teal);">{{text_2}}</em></h1>
          <p class="d1" data-mi style="font-size:clamp(16px,2vw,19px);color:var(--slate);line-height:1.85;max-width:540px;margin:0 0 38px;">{{{body_html}}}</p>
          <div class="d2" data-mi style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;">
            <a href="{{button_url}}" class="mi-btn mi-btn-fill">{{button_text}}</a>
            <a href="{{button_url_2}}" target="_blank" rel="noopener" class="mi-btn mi-btn-wa">
              <svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
              {{button_text_2}}</a>
          </div>
          <div class="mi-trust d3" data-mi>
            <div><b>{{group_1_text}}</b>{{group_1_text_2}}</div>
            <div><b>{{group_2_text}}</b>{{group_2_text_2}}</div>
            <div><b>{{group_3_text}}</b>{{group_3_text_2}}</div>
          </div>
        </div>

        <!-- 🖼️ GÖRSEL: /assets/img/mi-medical-hero.jpg (oran 4:5)
             PROMPT: "Clean clinical close-up macro of a premium medical mesotherapy injection device, Pistor Eliance style precision injector, teal and white surgical aesthetic, soft diffused clinical lighting, sterile minimal background, professional medical innovation mood, shallow depth of field; photorealistic, high resolution; no text, no logo, no watermark"
             image-ready: dosya geldiğinde .mi-photo background-image'i otomatik gösterir; yoksa nötr teal-gri fallback. -->
        <div class="d1" data-mi style="position:relative;flex:1 1 360px;min-height:460px;">
          <div style="position:relative;background:#fff;border:1px solid var(--line);border-radius:26px;box-shadow:var(--shadow-clean);padding:22px;max-width:400px;margin:0 auto;">
            <div class="mi-photo" style="aspect-ratio:4/5;border-radius:16px;background-color:#D6E8E7;background-image:linear-gradient(160deg,rgba(230,242,241,.35),rgba(191,227,225,.35)),url('/assets/img/mi-medical-hero.jpg');"></div>
            <div style="position:absolute;bottom:-18px;left:-14px;background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow-soft);padding:14px 18px;display:flex;align-items:center;gap:12px;">
              <span style="width:40px;height:40px;flex-shrink:0;border-radius:11px;display:grid;place-items:center;background:var(--grad-teal);color:#fff;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="20" height="20"><path d="m18 2 4 4M17 7 7 17l-4 1 1-4L14 4z"/><path d="m13 5 6 6"/></svg>
              </span>
              <div><div style="font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--teal);font-weight:700;">{{text_3}}</div><div class="mi-serif" style="font-size:17px;color:var(--teal-ink);">{{text_4}}</div></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "title_2": {"type": "text", "label": "Title 2"}, "text_2": {"type": "textarea", "label": "Text 2"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "text_4": {"type": "textarea", "label": "Text 4"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400..600;1,6..72,400..500&family=Inter:wght@400;500;600&display=swap", "button_url": "#spotlight", "button_url_2": "https://wa.me/905426205100", "text": "Medikal İnovasyon · Türkiye temsilcisi Estetik Dermal", "title": "Premium", "title_2": "Enjeksiyon", "text_2": "Sistemleri", "body_html": "Hassasiyet ve inovasyonun buluşması. MI-Medical Innovation, doktorlara güvenilir dozaj ve konfor sunan klinik-sınıf enjeksiyon ve mezoterapi teknolojisi geliştirir.", "button_text": "Pistor Eliance'ı İncele", "button_text_2": "Bilgi Al — WhatsApp", "group_1_text": "Pistor Eliance", "group_1_text_2": "Enjeksiyon sistemi", "group_2_text": "Hassas dozaj", "group_2_text_2": "Kontrollü uygulama", "group_3_text": "Türkiye", "group_3_text_2": "Estetik Dermal temsilciliği", "text_3": "Öne çıkan sistem", "text_4": "Pistor Eliance"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-mi-medical-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'2. MARKA HİKAYESİ',
             'html_template'=><<<'EDHTML'
<!-- 2. MARKA HİKAYESİ -->

    <section style="padding:clamp(72px,9vw,104px) 0;background:var(--paper);">
      <div class="mi-wrap" style="max-width:840px;text-align:center;">
        <p class="mi-eyebrow mi-eyebrow--c" data-mi>{{subtitle}}</p>
        <p class="mi-serif" data-mi style="font-size:clamp(24px,3.4vw,36px);line-height:1.5;font-weight:400;color:var(--teal-ink);margin:0 0 14px;">{{{body_html}}}</p>
        <p class="d1" data-mi style="color:var(--slate);font-size:17px;line-height:1.85;max-width:660px;margin:24px auto 36px;">{{{body_html_2}}}</p>
        <div class="d1" data-mi style="display:flex;align-items:center;justify-content:center;gap:14px;margin:0 auto;max-width:240px;">
          <span style="flex:1;height:1px;background:linear-gradient(90deg,transparent,var(--line));"></span>
          <span style="color:var(--teal);font-size:16px;">{{group_2_text}}</span>
          <span style="flex:1;height:1px;background:linear-gradient(90deg,var(--line),transparent);"></span>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"subtitle": {"type": "textarea", "label": "Subtitle"}, "body_html": {"type": "textarea", "label": "Body"}, "body_html_2": {"type": "textarea", "label": "Body Html 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"subtitle": "Marka Hikayesi", "body_html": "MI-Medical Innovation, enjeksiyon teknolojisini hassasiyetin diliyle yeniden tanımlar — her uygulamada kontrol, güven ve konfor.", "body_html_2": "Medikal inovasyona odaklanan MI-Medical Innovation, doktorların ihtiyaç duyduğu hassas dozaj ve tutarlı uygulama deneyimini, klinik güvenle birleştirir. Estetik Dermal; markanın Türkiye temsilcisi olarak, premium enjeksiyon sistemlerini doğru kaynaktan, eğitim ve teknik destekle birlikte sunar.", "group_2_text": "✛"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-mi-medical-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'3. KREDİBİLİTE ŞERİDİ',
             'html_template'=><<<'EDHTML'
<!-- 3. KREDİBİLİTE ŞERİDİ -->

    <section style="padding:0 0 clamp(56px,7vw,88px);background:var(--paper);">
      <div class="mi-wrap">
        <div class="mi-cred" data-mi>
          <div>
            <span class="mi-ico" style="width:46px;height:46px;border-radius:12px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2 4 6v6c0 5 3.4 8.6 8 10 4.6-1.4 8-5 8-10V6z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <h4>{{group_1_eyebrow}}</h4>
            <p>{{group_1_description}}</p>
          </div>
          <div>
            <span class="mi-ico" style="width:46px;height:46px;border-radius:12px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <h4>{{group_2_eyebrow}}</h4>
            <p>{{group_2_description}}</p>
          </div>
          <div>
            <span class="mi-ico" style="width:46px;height:46px;border-radius:12px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg></span>
            <h4>{{group_3_eyebrow}}</h4>
            <p>{{group_3_subtitle}}</p>
          </div>
          <div>
            <span class="mi-ico" style="width:46px;height:46px;border-radius:12px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m18 2 4 4M17 7 7 17l-4 1 1-4L14 4z"/><path d="m13 5 6 6"/></svg></span>
            <h4>{{group_4_eyebrow}}</h4>
            <p>{{group_4_description}}</p>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_eyebrow": {"type": "text", "label": "Group 1 Eyebrow"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_eyebrow": {"type": "text", "label": "Group 2 Eyebrow"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_3_eyebrow": {"type": "text", "label": "Group 3 Eyebrow"}, "group_3_subtitle": {"type": "textarea", "label": "Group 3 Subtitle"}, "group_4_eyebrow": {"type": "text", "label": "Group 4 Eyebrow"}, "group_4_description": {"type": "textarea", "label": "Group 4 Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_eyebrow": "Premium Sınıf", "group_1_description": "Klinik standartlarda tasarlanmış, premium kalite enjeksiyon sistemleri.", "group_2_eyebrow": "Klinik Güven", "group_2_description": "Hassas dozaj ve tutarlı uygulama için güven veren mühendislik.", "group_3_eyebrow": "Türkiye Distribütörü", "group_3_subtitle": "Estetik Dermal güvencesiyle, doğru kaynaktan resmi tedarik.", "group_4_eyebrow": "İnovasyon Odaklı", "group_4_description": "Enjeksiyon teknolojisinde hassasiyeti ileri taşıyan yenilikçi yaklaşım."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-mi-medical-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'4. ÖNE ÇIKAN: PISTOR ELIANCE SPOTLIGHT',
             'html_template'=><<<'EDHTML'
<!-- 4. ÖNE ÇIKAN: PISTOR ELIANCE SPOTLIGHT -->

    <section id="spotlight" style="padding:clamp(72px,9vw,104px) 0;background:var(--mist-soft);position:relative;overflow:hidden;">
      <div class="mi-glow" aria-hidden="true" style="top:-120px;right:6%;width:320px;height:320px;background:radial-gradient(circle,rgba(14,140,140,.14),transparent 70%);"></div>
      <div class="mi-wrap mi-split" style="position:relative;">
        <!-- 🖼️ GÖRSEL: /assets/img/mi-medical-pistor-eliance.jpg (oran 4:5)
             PROMPT: "Premium medical injection system Pistor Eliance, clean clinical product shot, teal and white sterile aesthetic, precise mesotherapy injector device on minimal surgical surface, soft diffused professional lighting, medical innovation editorial; photorealistic, high resolution; no text, no logo, no watermark" -->
        <div class="mi-col" data-mi style="position:relative;min-height:440px;">
          <div style="position:relative;background:#fff;border:1px solid var(--line);border-radius:26px;box-shadow:var(--shadow-clean);padding:22px;max-width:420px;margin:0 auto;">
            <div class="mi-photo" style="aspect-ratio:4/5;border-radius:16px;background-color:#D6E8E7;background-image:linear-gradient(160deg,rgba(230,242,241,.4),rgba(191,227,225,.4)),url('/assets/img/mi-medical-pistor-eliance.jpg');"></div>
            <div style="position:absolute;top:14px;right:-12px;background:var(--grad-teal);color:#fff;border-radius:12px;box-shadow:var(--shadow-soft);padding:10px 14px;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;">{{text}}</div>
          </div>
        </div>
        <div class="mi-col d1" data-mi>
          <p class="mi-eyebrow">{{subtitle}}</p>
          <h2 class="mi-serif" style="font-size:clamp(30px,4.4vw,48px);font-weight:500;color:var(--teal-ink);margin:0 0 22px;line-height:1.1;">{{title}}<br><em style="font-style:italic;color:var(--teal);">{{text_2}}</em></h2>
          <p style="color:var(--slate);font-size:17px;line-height:1.85;margin:0 0 30px;max-width:520px;">{{{body_html}}}</p>
          <ul class="mi-feat-list">
            <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l4 4L19 6"/></svg></span> {{group_1_item_text}}</li>
            <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l4 4L19 6"/></svg></span> {{group_2_item_text}}</li>
            <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l4 4L19 6"/></svg></span> {{group_3_item_text}}</li>
            <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l4 4L19 6"/></svg></span> {{group_4_item_text}}</li>
          </ul>
          <a href="{{button_url}}" class="mi-btn mi-btn-fill">{{button_text}}</a>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "text_2": {"type": "textarea", "label": "Text 2"}, "body_html": {"type": "textarea", "label": "Body"}, "group_1_item_text": {"type": "textarea", "label": "Group 1 Item Text"}, "group_2_item_text": {"type": "textarea", "label": "Group 2 Item Text"}, "group_3_item_text": {"type": "textarea", "label": "Group 3 Item Text"}, "group_4_item_text": {"type": "textarea", "label": "Group 4 Item Text"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "/urun-detay", "text": "Premium Sistem", "subtitle": "Öne Çıkan · Enjeksiyon Sistemi", "title": "Pistor Eliance", "text_2": "hassas mezoterapi sistemi", "body_html": "Pistor Eliance; mezoterapi ve enjeksiyon uygulamalarında hassas, kontrollü ve tekrarlanabilir dozaj sunmak için tasarlanmış premium bir sistemdir. Doktora konfor, hastaya nezaket sağlayan ince mühendislik anlayışıyla, klinik güveni standart hale getirir.", "group_1_item_text": "Hassas ve tekrarlanabilir dozaj kontrolü", "group_2_item_text": "Ergonomik tasarımla uygulayıcı konforu", "group_3_item_text": "Mezoterapi ve enjeksiyon protokollerine uyum", "group_4_item_text": "Premium klinik kalite ve güvenilir yapı", "button_text": "Pistor Eliance'ı İncele →"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-mi-medical-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'5. ÜRÜN GRID',
             'html_template'=><<<'EDHTML'
<!-- 5. ÜRÜN GRID -->

    <section id="urunler" style="padding:clamp(72px,9vw,104px) 0;background:var(--paper);">
      <div class="mi-wrap">
        <div style="text-align:center;max-width:640px;margin:0 auto 56px;">
          <p class="mi-eyebrow mi-eyebrow--c" data-mi>{{subtitle}}</p>
          <h2 class="mi-serif d1" data-mi style="font-size:clamp(30px,4.4vw,46px);font-weight:500;color:var(--teal-ink);margin:0 0 16px;line-height:1.12;">{{title}}</h2>
          <p class="d1" data-mi style="color:var(--slate);font-size:17px;line-height:1.8;">{{description}}</p>
        </div>

        <!-- 🖼️ GÖRSEL (kart deseni): /assets/img/mi-medical-product-{slug}.jpg (oran 4:3)
             slug'lar: pistor-eliance, enjektor-uclari, dozaj-modulu, baglanti-seti
             PROMPT: "Clean clinical medical device component packshot, teal and white sterile aesthetic, soft diffused professional lighting, minimal surgical surface, premium medical editorial; photorealistic, high resolution; no text, no logo, no watermark" -->
        <div class="mi-grid" data-mi>
          <!-- Pistor Eliance -->
          {{{mi_prodcard_items_html}}}<!-- Enjektör Uçları -->
          <!-- Dozaj Modülü -->
          <!-- Bağlantı Seti -->
          </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"mi_prodcard_items": {"type": "repeater", "label": "Mi Prodcard Items", "repeat_kind": "items", "item_template": "<a href=\"{{button_url}}\" class=\"mi-prodcard mi-card\">\n            <div class=\"mi-prodcard__img\" style=\"background-image:url('{{background_image_url}}');\"></div>\n            <h3 class=\"mi-serif\" style=\"font-size:23px;font-weight:600;color:var(--teal-ink);margin:0 0 4px;\">{{title}}</h3>\n            <p style=\"font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--teal);font-weight:700;margin:0 0 12px;\">{{subtitle}}</p>\n            <p style=\"color:var(--slate);font-size:14.5px;line-height:1.75;margin:0 0 22px;flex:1;\">{{description}}</p>\n            <span class=\"mi-link\">{{text}}</span>\n          </a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "background_image_url": {"type": "image", "label": "Background Image Url"}, "title": {"type": "text", "label": "Title"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "description": {"type": "textarea", "label": "Description"}, "text": {"type": "textarea", "label": "Text"}}}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"mi_prodcard_items": [{"button_url": "/urun-detay", "background_image_url": "/assets/img/mi-medical-product-pistor-eliance.jpg", "title": "Pistor Eliance", "subtitle": "Ana Enjeksiyon Sistemi", "description": "Premium mezoterapi ve enjeksiyon sisteminin merkezi; hassas dozaj için tasarlandı.", "text": "İncele →"}, {"button_url": "/urun-detay", "background_image_url": "/assets/img/mi-medical-product-enjektor-uclari.jpg", "title": "Enjektör Uçları", "subtitle": "Sistem Bileşeni", "description": "Farklı protokollere uyumlu, hassas uygulama için tasarlanmış uç çözümleri.", "text": "İncele →"}, {"button_url": "/urun-detay", "background_image_url": "/assets/img/mi-medical-product-dozaj-modulu.jpg", "title": "Dozaj Modülü", "subtitle": "Sistem Bileşeni", "description": "Kontrollü ve tekrarlanabilir dozaj için Pistor Eliance ile uyumlu modül.", "text": "İncele →"}, {"button_url": "/urun-detay", "background_image_url": "/assets/img/mi-medical-product-baglanti-seti.jpg", "title": "Bağlantı Seti", "subtitle": "Sistem Bileşeni", "description": "Steril ve güvenli akış için tasarlanmış, sisteme entegre bağlantı bileşenleri.", "text": "İncele →"}], "subtitle": "Sistem & Bileşenler", "title": "Pistor Eliance ekosistemi", "description": "Hassas enjeksiyon için tasarlanmış ana sistem ve onu tamamlayan klinik bileşenler."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-mi-medical-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'6. DOKTORLAR İÇİN / UYGULAMA',
             'html_template'=><<<'EDHTML'
<!-- 6. DOKTORLAR İÇİN / UYGULAMA -->

    <section style="padding:clamp(72px,9vw,104px) 0;background:var(--mist-soft);">
      <div class="mi-wrap">
        <div style="max-width:680px;margin:0 0 48px;">
          <p class="mi-eyebrow" data-mi>{{subtitle}}</p>
          <h2 class="mi-serif d1" data-mi style="font-size:clamp(28px,4vw,42px);font-weight:500;color:var(--teal-ink);margin:0 0 16px;line-height:1.15;">{{title}}</h2>
          <p class="d1" data-mi style="color:var(--slate);font-size:17px;line-height:1.85;">{{{body_html}}}</p>
        </div>
        <div class="mi-docgrid" data-mi>
          <div class="mi-doc">
            <span class="mi-ico" style="width:50px;height:50px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 12h4l3 8 4-16 3 8h4"/></svg></span>
            <h4>{{group_1_eyebrow}}</h4>
            <p>{{group_1_description}}</p>
          </div>
          <div class="mi-doc">
            <span class="mi-ico" style="width:50px;height:50px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a4 4 0 0 0-4 4v6a4 4 0 0 0 8 0V6a4 4 0 0 0-4-4z"/><path d="M5 11v1a7 7 0 0 0 14 0v-1M12 19v3"/></svg></span>
            <h4>{{group_2_eyebrow}}</h4>
            <p>{{group_2_description}}</p>
          </div>
          <div class="mi-doc">
            <span class="mi-ico" style="width:50px;height:50px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></span>
            <h4>{{group_3_eyebrow}}</h4>
            <p>{{group_3_description}}</p>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "group_1_eyebrow": {"type": "text", "label": "Group 1 Eyebrow"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_eyebrow": {"type": "text", "label": "Group 2 Eyebrow"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_3_eyebrow": {"type": "text", "label": "Group 3 Eyebrow"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"subtitle": "Doktorlar & Uygulayıcılar İçin", "title": "Hassas dozaj, uygulayıcı konforu ve eğitim", "body_html": "MI-Medical Innovation sistemleri, klinik ve uygulayıcılar için kontrollü dozaj ve ergonomik kullanım sunar. Ürün, içerik ve uygulama detayları için ekibimiz yanınızda.", "group_1_eyebrow": "Hassas Dozaj", "group_1_description": "Kontrollü ve tekrarlanabilir uygulama için tasarlanmış dozaj yapısı.", "group_2_eyebrow": "Uygulayıcı Konforu", "group_2_description": "Ergonomik tasarımla uzun uygulamalarda dahi konforlu kullanım.", "group_3_eyebrow": "Eğitim & Destek", "group_3_description": "Sistemlerin doğru kullanımı için uygulamalı eğitim ve teknik destek."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-mi-medical-6'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'7. CTA BANDI',
             'html_template'=><<<'EDHTML'
<!-- 7. CTA BANDI -->

    <section style="padding:clamp(56px,8vw,96px) 0 clamp(72px,9vw,104px);background:var(--paper);">
      <div class="mi-wrap">
        <div data-mi style="position:relative;overflow:hidden;border-radius:32px;background:var(--grad-teal);padding:clamp(48px,7vw,84px);text-align:center;color:#fff;">
          <div class="mi-glow" aria-hidden="true" style="top:-60px;right:-40px;width:240px;height:240px;background:rgba(255,255,255,.12);"></div>
          <div class="mi-glow" aria-hidden="true" style="bottom:-80px;left:-50px;width:280px;height:280px;background:rgba(255,255,255,.08);"></div>
          <div style="position:relative;">
            <p class="mi-eyebrow mi-eyebrow--c mi-eyebrow--light" style="margin:0 auto 18px;">{{subtitle}}</p>
            <h2 class="mi-serif" style="font-size:clamp(28px,4.4vw,46px);font-weight:500;margin:0 0 18px;line-height:1.14;">{{title}}</h2>
            <p style="font-size:18px;opacity:.95;max-width:580px;margin:0 auto 36px;line-height:1.7;">{{{body_html}}}</p>
            <a href="{{button_url}}" target="_blank" rel="noopener" class="mi-btn" style="background:#25D366;color:#fff;font-weight:700;box-shadow:0 14px 36px rgba(8,63,64,.22);">
              <svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
              {{button_text}}</a>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "subtitle": {"type": "textarea", "label": "Subtitle"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "subtitle": "İletişim", "title": "Bu markanın ürünleri hakkında bilgi alın", "body_html": "Pistor Eliance ve MI-Medical Innovation sistemleri için ürün, içerik ve uygulama bilgilerine ekibimizden ulaşın. WhatsApp'tan yazın, hemen yanıtlayalım.", "button_text": "WhatsApp ile Bilgi Al"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-neogenesis-0'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'1 · HERO',
             'html_template'=><<<'EDHTML'
<link rel="preconnect" href="{{group_1_link_url}}"><link rel="preconnect" href="{{group_2_link_url}}" crossorigin><link href="{{group_3_link_url}}" rel="stylesheet"><style>
    .ng {
      --ng-green:   #5B8C6E;   /* yumuşak sage yeşil */
      --ng-deep:    #3F6E54;   /* koyu rejeneratif yeşil */
      --ng-forest:  #2C4A3A;   /* derin orman — koyu bölümler */
      --ng-leaf:    #7BA88A;   /* açık yaprak */
      --ng-sage:    #E7EFE7;   /* sage wash */
      --ng-ivory:   #F7F6F0;   /* fildişi */
      --ng-mist:    #EEF3EC;   /* açık doğal zemin */
      --ng-bark:    #4A4439;   /* sıcak doğal gövde metni */
      --ng-line:    #DCE6DC;
      --ng-ease:    cubic-bezier(.22,.61,.36,1);
      --ng-serif:   "Fraunces", Georgia, serif;
    }
    /* Organik başlık — global serif'i taklit eder ama yeşil mürekkep */
    .ng h1, .ng h2, .ng h3, .ng h4 { font-family:var(--ng-serif); color:var(--ng-forest); letter-spacing:-.018em; line-height:1.1; margin:0; }
    .ng .ng-eyebrow { font-family:"Inter",sans-serif; font-size:12px; font-weight:600; letter-spacing:.2em; text-transform:uppercase; color:var(--ng-deep); display:inline-flex; align-items:center; gap:9px; }
    .ng .ng-eyebrow::before { content:""; width:22px; height:1.5px; border-radius:2px; background:var(--ng-leaf); display:inline-block; }
    .ng p { color:var(--ng-bark); }

    /* HERO — yumuşak yeşil/doğal, organik formlar, hücre/doku dokusu */
    .ng-hero { position:relative; overflow:hidden;
      background:radial-gradient(1000px 560px at 80% -10%, var(--ng-sage), transparent 60%),
                 linear-gradient(180deg,#FFFFFF 0%, var(--ng-mist) 100%); }
    /* organik hücre / doku doku katmanı */
    .ng-hero__cells { position:absolute; inset:0; opacity:.5; pointer-events:none;
      background-image:radial-gradient(circle at 18% 30%, rgba(91,140,110,.10) 0 9px, transparent 10px),
        radial-gradient(circle at 70% 12%, rgba(123,168,138,.10) 0 14px, transparent 15px),
        radial-gradient(circle at 88% 62%, rgba(63,110,84,.08) 0 11px, transparent 12px),
        radial-gradient(circle at 42% 78%, rgba(91,140,110,.08) 0 7px, transparent 8px),
        radial-gradient(circle at 8% 70%, rgba(123,168,138,.08) 0 12px, transparent 13px);
      background-size:340px 340px; -webkit-mask-image:linear-gradient(180deg,#000,transparent 88%); mask-image:linear-gradient(180deg,#000,transparent 88%); }
    .ng-hero__in { position:relative; display:grid; grid-template-columns:1.05fr .95fr; gap:56px; align-items:center; padding:clamp(60px,9vh,104px) 0 clamp(64px,9vh,108px); }
    .ng-hero h1 { font-size:clamp(40px,5.6vw,72px); margin:22px 0 22px; }
    .ng-hero h1 em { font-style:italic; color:var(--ng-deep); }
    .ng-hero__sub { font-size:clamp(17px,1.5vw,20px); color:var(--ng-bark); max-width:540px; line-height:1.7; margin:0 0 34px; }
    .ng-pill { display:inline-flex; align-items:center; gap:8px; background:#fff; color:var(--ng-deep); border:1px solid var(--ng-line); border-radius:999px; padding:8px 16px; font-family:"Inter",sans-serif; font-size:12px; font-weight:600; letter-spacing:.16em; text-transform:uppercase; }
    .ng-cta { display:flex; gap:14px; flex-wrap:wrap; }
    /* yeşil butonlar — global .btn'i ezmeden, kendi sınıflarım */
    .ng-btn { display:inline-flex; align-items:center; gap:9px; padding:14px 28px; border-radius:999px; font-family:"Inter",sans-serif; font-size:15px; font-weight:600; transition:.25s var(--ng-ease); border:1.5px solid transparent; }
    .ng-btn--solid { background:var(--ng-green); color:#fff; box-shadow:0 14px 30px -12px rgba(63,110,84,.5); }
    .ng-btn--solid:hover { background:var(--ng-deep); transform:translateY(-2px); }
    .ng-btn--wa { background:#fff; color:var(--ng-forest); border-color:var(--ng-line); }
    .ng-btn--wa:hover { border-color:var(--ng-leaf); transform:translateY(-2px); }
    .ng-btn svg { width:18px; height:18px; }
    .ng-trust { display:flex; gap:28px; flex-wrap:wrap; margin-top:42px; padding-top:26px; border-top:1px solid var(--ng-line); }
    .ng-trust div { font-size:13px; color:var(--ng-bark); font-family:"Inter",sans-serif; }
    .ng-trust b { display:block; font-family:var(--ng-serif); font-size:19px; color:var(--ng-forest); font-weight:500; }

    /* hero görsel kartı — organik yumuşak köşeler */
    .ng-hero__visual { position:relative; min-height:420px; }
    .ng-hero__blob { position:absolute; inset:-6% -4%; background:linear-gradient(135deg,var(--ng-green),var(--ng-leaf)); opacity:.16;
      border-radius:46% 54% 58% 42% / 52% 46% 54% 48%; animation:ngMorph 14s ease-in-out infinite; }
    .ng-hero__card { position:relative; background:#fff; border:1px solid var(--ng-line); border-radius:28px; padding:22px; box-shadow:0 30px 70px -34px rgba(44,74,58,.34); }
    .ng-hero__img { aspect-ratio:4/5; border-radius:20px; overflow:hidden;
      background:linear-gradient(150deg,var(--ng-sage),var(--ng-mist)) center/cover no-repeat; background-image:url('/assets/img/neogenesis-hero.jpg'); position:relative; }
    .ng-hero__img span { position:absolute; top:14px; left:16px; font-family:"Inter",sans-serif; font-size:11px; font-weight:600; letter-spacing:.14em; text-transform:uppercase; color:var(--ng-deep); }
    .ng-hero__meta { display:flex; align-items:center; justify-content:space-between; margin-top:16px; }
    .ng-hero__meta small { font-family:"Inter",sans-serif; font-size:11px; color:var(--ng-deep); font-weight:600; text-transform:uppercase; letter-spacing:.1em; }
    .ng-hero__meta b { display:block; font-family:var(--ng-serif); color:var(--ng-forest); font-weight:520; font-size:17px; }
    .ng-tag { background:var(--ng-sage); color:var(--ng-deep); font-family:"Inter",sans-serif; font-size:11px; font-weight:600; padding:6px 13px; border-radius:999px; letter-spacing:.04em; }
    .ng-hero__float { position:absolute; bottom:-18px; left:-16px; background:#fff; border:1px solid var(--ng-line); border-radius:16px; box-shadow:0 18px 40px -20px rgba(44,74,58,.3); padding:13px 17px; display:flex; align-items:center; gap:11px; }
    .ng-hero__float .ic { width:36px; height:36px; flex-shrink:0; border-radius:11px; background:var(--ng-sage); color:var(--ng-deep); display:grid; place-items:center; }
    .ng-hero__float .ic svg { width:19px; height:19px; }
    .ng-hero__float small { font-family:"Inter",sans-serif; font-size:11px; color:var(--ng-bark); }
    .ng-hero__float b { display:block; font-family:var(--ng-serif); font-size:14px; color:var(--ng-forest); font-weight:520; }
    @keyframes ngMorph { 0%,100%{border-radius:46% 54% 58% 42% / 52% 46% 54% 48%;} 50%{border-radius:56% 44% 42% 58% / 44% 56% 44% 56%;} }

    /* genel bölüm sarmalı (sayfaya özel) */
    .ng-section { padding:clamp(72px,10vh,128px) 0; }
    .ng-head { max-width:660px; margin:0 0 50px; }
    .ng-head h2 { font-size:clamp(28px,4vw,44px); margin-top:18px; }
    .ng-head p { font-size:clamp(16px,1.4vw,19px); color:var(--ng-bark); line-height:1.7; margin:16px 0 0; }

    /* Hikaye / bilim+doğa */
    .ng-story { display:grid; grid-template-columns:1fr 1fr; gap:clamp(36px,6vw,84px); align-items:center; }
    .ng-story__media { position:relative; }
    .ng-story__blob { position:absolute; inset:-5%; background:linear-gradient(135deg,var(--ng-leaf),var(--ng-green)); opacity:.14; border-radius:54% 46% 48% 52% / 50% 54% 46% 50%; }
    .ng-story__img { position:relative; aspect-ratio:5/4; border-radius:26px; overflow:hidden; border:1px solid var(--ng-line);
      background:linear-gradient(150deg,var(--ng-sage),var(--ng-mist)) center/cover no-repeat; background-image:url('/assets/img/neogenesis-story.jpg'); }
    .ng-story h2 { font-size:clamp(26px,3.6vw,40px); margin:18px 0 16px; }
    .ng-pillars { list-style:none; margin:26px 0 0; padding:0; display:grid; gap:14px; }
    .ng-pillars li { display:flex; gap:14px; align-items:flex-start; }
    .ng-pillars .ic { width:30px; height:30px; flex-shrink:0; border-radius:9px; background:var(--ng-sage); color:var(--ng-deep); display:grid; place-items:center; margin-top:1px; }
    .ng-pillars .ic svg { width:17px; height:17px; }
    .ng-pillars b { color:var(--ng-forest); font-family:"Inter",sans-serif; font-weight:600; font-size:15.5px; }
    .ng-pillars span { display:block; color:var(--ng-bark); font-size:14px; line-height:1.6; }

    /* Kredibilite şeridi */
    .ng-cred { background:var(--ng-forest); border-radius:28px; overflow:hidden; }
    .ng-cred__grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:1px; background:rgba(255,255,255,.08); }
    .ng-cred__cell { background:var(--ng-forest); padding:34px 26px; text-align:center; }
    .ng-cred__cell .ic { width:40px; height:40px; margin:0 auto 14px; border-radius:12px; background:rgba(123,168,138,.2); color:var(--ng-leaf); display:grid; place-items:center; }
    .ng-cred__cell .ic svg { width:20px; height:20px; }
    .ng-cred__cell b { display:block; font-family:var(--ng-serif); color:#fff; font-size:17px; font-weight:520; }
    .ng-cred__cell span { display:block; color:rgba(255,255,255,.66); font-family:"Inter",sans-serif; font-size:13px; margin-top:4px; }

    /* Öne çıkan ürünler — split kartlar */
    .ng-feat { display:grid; grid-template-columns:1fr 1fr; gap:clamp(36px,6vw,80px); align-items:center; }
    .ng-feat + .ng-feat { margin-top:clamp(52px,8vw,104px); }
    .ng-feat--rev .ng-feat__media { order:2; }
    .ng-feat__media { position:relative; }
    .ng-feat__img { aspect-ratio:5/4; border-radius:24px; overflow:hidden; border:1px solid var(--ng-line);
      background:linear-gradient(150deg,var(--ng-sage),var(--ng-mist)) center/cover no-repeat; box-shadow:0 24px 60px -36px rgba(44,74,58,.4); }
    .ng-feat h3 { font-size:clamp(26px,3.4vw,40px); margin:16px 0 16px; }
    .ng-feat .lead2 { font-size:clamp(16px,1.4vw,19px); color:var(--ng-bark); line-height:1.75; }
    .ng-chip { display:inline-flex; align-items:center; gap:8px; background:var(--ng-sage); color:var(--ng-deep); border-radius:999px; padding:7px 15px; font-family:"Inter",sans-serif; font-size:13px; font-weight:600; margin:18px 0 4px; }
    .ng-link { color:var(--ng-deep); font-family:"Inter",sans-serif; font-weight:600; display:inline-flex; align-items:center; gap:8px; margin-top:22px; }
    .ng-link svg { width:16px; height:16px; transition:transform .25s var(--ng-ease); }
    .ng-link:hover svg { transform:translateX(4px); }

    /* Ürün yelpazesi grid */
    .ng-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:18px; }
    .ng-prod { display:flex; flex-direction:column; background:#fff; border:1px solid var(--ng-line); border-radius:20px; overflow:hidden; transition:.25s var(--ng-ease); }
    .ng-prod:hover { transform:translateY(-4px); border-color:var(--ng-leaf); box-shadow:0 22px 46px -30px rgba(63,110,84,.45); }
    .ng-prod__img { aspect-ratio:4/3; background:linear-gradient(150deg,var(--ng-sage),var(--ng-mist)) center/cover no-repeat; border-bottom:1px solid var(--ng-line); }
    .ng-prod__body { padding:20px 22px 22px; display:flex; flex-direction:column; flex:1; }
    .ng-prod__cat { font-family:"Inter",sans-serif; font-size:11px; font-weight:600; letter-spacing:.12em; text-transform:uppercase; color:var(--ng-deep); }
    .ng-prod h3 { font-size:19px; font-weight:520; margin:7px 0 9px; }
    .ng-prod p { font-size:13.5px; color:var(--ng-bark); line-height:1.6; margin:0 0 16px; flex:1; }
    .ng-prod__more { font-family:"Inter",sans-serif; font-size:14px; font-weight:600; color:var(--ng-deep); display:inline-flex; align-items:center; gap:7px; }
    .ng-prod__more svg { width:15px; height:15px; transition:transform .25s var(--ng-ease); }
    .ng-prod:hover .ng-prod__more svg { transform:translateX(3px); }

    /* Doktorlar için / uygulama */
    .ng-docs { background:var(--ng-forest); color:#fff; border-radius:28px; padding:clamp(40px,6vw,72px); position:relative; overflow:hidden; }
    .ng-docs__leaf { position:absolute; top:-60px; right:-50px; width:280px; height:280px; opacity:.1;
      background:linear-gradient(135deg,#fff,transparent); border-radius:54% 46% 48% 52% / 50% 54% 46% 50%; }
    .ng-docs > * { position:relative; }
    .ng-docs h2 { color:#fff; font-size:clamp(26px,3.6vw,42px); max-width:660px; }
    .ng-docs .lead2 { color:rgba(255,255,255,.7); max-width:600px; font-size:clamp(16px,1.4vw,19px); line-height:1.7; margin-top:14px; }
    .ng-docs__cards { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin:38px 0 30px; }
    .ng-docc { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.12); border-radius:18px; padding:26px; }
    .ng-docc .ic { width:46px; height:46px; border-radius:13px; background:rgba(123,168,138,.2); color:var(--ng-leaf); display:grid; place-items:center; margin-bottom:16px; }
    .ng-docc .ic svg { width:22px; height:22px; }
    .ng-docc h4 { color:#fff; font-family:"Inter",sans-serif; font-size:17px; font-weight:600; margin:0 0 8px; }
    .ng-docc p { color:rgba(255,255,255,.62); font-size:14px; margin:0; line-height:1.6; }
    .ng-note { font-family:"Inter",sans-serif; font-size:13px; color:rgba(255,255,255,.55); margin-top:6px; }

    /* CTA bandı */
    .ng-ctaband { position:relative; overflow:hidden; border-radius:28px; padding:clamp(40px,6vw,72px); text-align:center; color:#fff;
      background:linear-gradient(135deg,var(--ng-deep),var(--ng-green)); }
    .ng-ctaband__o1 { position:absolute; top:-50px; right:-40px; width:220px; height:220px; background:rgba(255,255,255,.1); border-radius:52% 48% 46% 54% / 48% 52% 48% 52%; }
    .ng-ctaband__o2 { position:absolute; bottom:-70px; left:-40px; width:260px; height:260px; background:rgba(255,255,255,.08); border-radius:46% 54% 52% 48% / 54% 46% 52% 48%; }
    .ng-ctaband > * { position:relative; }
    .ng-ctaband h2 { color:#fff; font-size:clamp(26px,4vw,42px); margin:0 0 14px; }
    .ng-ctaband p { color:rgba(255,255,255,.92); font-size:clamp(16px,1.4vw,19px); max-width:600px; margin:0 auto 30px; line-height:1.65; }
    .ng-btn--wagreen { background:#25D366; color:#fff; }
    .ng-btn--wagreen:hover { background:#1ebe5b; transform:translateY(-2px); }
    .ng-btn--outline { background:rgba(255,255,255,.12); color:#fff; border-color:rgba(255,255,255,.6); }
    .ng-btn--outline:hover { background:rgba(255,255,255,.2); transform:translateY(-2px); }

    @media (max-width:960px){
      .ng-hero__in,.ng-story,.ng-feat,.ng-feat--rev .ng-feat__media{ grid-template-columns:1fr; }
      .ng-feat--rev .ng-feat__media{ order:0; }
      .ng-hero__visual{ order:-1; max-width:420px; }
      .ng-docs__cards{ grid-template-columns:1fr; }
    }
    @media (prefers-reduced-motion:reduce){ .ng-hero__blob{ animation:none; } }
  </style><!-- 1 · HERO -->

    <section class="ng-hero">
      <div class="ng-hero__cells" aria-hidden="true"></div>
      <div class="wrap ng-hero__in">
        <div class="ng-hero__copy">
          <span class="ng-pill reveal">{{text}}</span>
          <h1 class="reveal d1">{{title}} <em>{{text_2}}</em><br>{{title_2}}</h1>
          <p class="ng-hero__sub reveal d2">{{{body_html}}}</p>
          <div class="ng-cta reveal d3">
            <a class="ng-btn ng-btn--solid" href="{{button_url}}">{{button_text}}</a>
            <a class="ng-btn ng-btn--wa" href="{{button_url_2}}" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 4 1.7 4 1.2 4.7 1.1.7-.1 2.4-1 2.7-1.9.3-1 .3-1.8.2-1.9z"/></svg>
              {{button_text_2}}</a>
          </div>
          <div class="ng-trust reveal d4">
            <div><b>{{group_1_text}}</b> {{group_1_text_2}}</div>
            <div><b>{{group_2_text}}</b> {{group_2_text_2}}</div>
            <div><b>{{group_3_text}}</b> {{group_3_text_2}}</div>
          </div>
        </div>
        <div class="ng-hero__visual reveal d2">
          <div class="ng-hero__blob" aria-hidden="true"></div>
          <div class="ng-hero__card">
            <!-- 🖼️ GÖRSEL: /assets/img/neogenesis-hero.jpg (oran 4:5)
                 PROMPT: "Botanical-scientific skincare hero, a frosted glass serum bottle with a soft green dropper resting among fresh dewy green leaves and water droplets, gentle sage and ivory tones, soft diffused natural light, regenerative biotech aesthetic, cell-renewal mood, organic and clean, shallow depth of field; photorealistic, high resolution; no text, no logo, no watermark"
                 DEĞİŞTİR → bu .ng-hero__img bloğunu istersen <img>'e çevir. background-image hazır. -->
            <div class="ng-hero__img"><span>{{text_3}}</span></div>
            <div class="ng-hero__meta">
              <div><small>{{text_4}}</small><b>{{text_5}}</b></div>
              <span class="ng-tag">{{text_6}}</span>
            </div>
          </div>
          <div class="ng-hero__float">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2c4 3.5 4 9 0 13-4-4-4-9.5 0-13z"/><path d="M12 8v13"/><path d="M12 15c-2-1-3-2-5-2"/></svg></span>
            <div><small>{{text_7}}</small><b>{{text_8}}</b></div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": {"type": "text", "label": "Group 1 Link Url"}, "group_2_link_url": {"type": "text", "label": "Group 2 Link Url"}, "group_3_link_url": {"type": "text", "label": "Group 3 Link Url"}, "button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "text_2": {"type": "textarea", "label": "Text 2"}, "title_2": {"type": "text", "label": "Title 2"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "text_3": {"type": "textarea", "label": "Text 3"}, "text_4": {"type": "textarea", "label": "Text 4"}, "text_5": {"type": "textarea", "label": "Text 5"}, "text_6": {"type": "textarea", "label": "Text 6"}, "text_7": {"type": "textarea", "label": "Text 7"}, "text_8": {"type": "textarea", "label": "Text 8"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_link_url": "https://fonts.googleapis.com", "group_2_link_url": "https://fonts.gstatic.com", "group_3_link_url": "https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap", "button_url": "#yelpaze", "button_url_2": "https://wa.me/905426205100", "text": "Rejeneratif Cilt Bilimi", "title": "Hücreden", "text_2": "yeniden", "title_2": "doğan cilt.", "body_html": "Neogenesis, bilim ve doğayı buluşturan rejeneratif bakım serisi. Büyüme faktörü ve hücre yenilenmesi temalı formülasyonlarla cildin kendini onarma sürecine eşlik eder — Estetik Dermal portföyünde.", "button_text": "Ürünleri İncele", "button_text_2": "Bilgi Al — WhatsApp", "group_1_text": "Rejeneratif", "group_1_text_2": "Bakım serisi", "group_2_text": "Bilim + Doğa", "group_2_text_2": "Biyoteknoloji yaklaşımı", "group_3_text": "Estetik Dermal", "group_3_text_2": "Resmi portföy", "text_3": "Rejeneratif Serum", "text_4": "Öne çıkan", "text_5": "Cellular Renewal Serum", "text_6": "Büyüme Faktörü", "text_7": "Bilim + Doğa", "text_8": "Biyoteknoloji"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-neogenesis-1'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'2 · MARKA HİKAYESİ',
             'html_template'=><<<'EDHTML'
<!-- 2 · MARKA HİKAYESİ -->

    <section class="ng-section">
      <div class="wrap">
        <div class="ng-story">
          <div class="ng-story__media reveal">
            <div class="ng-story__blob" aria-hidden="true"></div>
            <!-- 🖼️ GÖRSEL: /assets/img/neogenesis-story.jpg (oran 5:4)
                 PROMPT: "Close-up of a green botanical laboratory scene, leaf cells and plant tissue under soft light blending with a clear skincare gel droplet, sage green palette, science-meets-nature regenerative biotech mood, organic forms, calm and pristine; photorealistic, high resolution; no text, no logo" -->
            <div class="ng-story__img"></div>
          </div>
          <div class="reveal d1">
            <span class="ng-eyebrow">{{text}}</span>
            <h2>{{title}}</h2>
            <p class="lead2">{{{body_html}}}</p>
            <ul class="ng-pillars">
              <li>
                <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2c4 3.5 4 9 0 13-4-4-4-9.5 0-13z"/><path d="M12 8v13"/></svg></span>
                <div><b>{{group_1_text}}</b><span>{{group_1_text_2}}</span></div>
              </li>
              <li>
                <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/></svg></span>
                <div><b>{{group_2_text}}</b><span>{{group_2_text_2}}</span></div>
              </li>
              <li>
                <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 22s7-4 7-12V5l-7-3-7 3v5c0 8 7 12 7 12z"/></svg></span>
                <div><b>{{group_3_text}}</b><span>{{group_3_text_2}}</span></div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"text": "Marka Hikayesi", "title": "Doğanın yenilenme bilgeliği, bilimin hassasiyetiyle", "body_html": "Neogenesis, cildin doğuştan gelen onarım kabiliyetine duyulan inançtan doğdu. Rejeneratif yaklaşımı; bitkisel kaynaklı aktiflerle büyüme faktörü ve hücre yenilenmesi temalı biyoteknolojiyi bir araya getirir. Amaç cildi zorlamak değil, kendi yenilenme döngüsüne nazikçe eşlik etmektir.", "group_1_text": "Doğadan ilham", "group_1_text_2": "Bitkisel kaynaklı aktifler ve nazik, dengeli formülasyon felsefesi.", "group_2_text": "Bilim odaklı", "group_2_text_2": "Büyüme faktörü ve hücre yenilenmesi temalı rejeneratif kozmetik yaklaşımı.", "group_3_text": "Estetik Dermal portföyünde", "group_3_text_2": "2004'ten bu yana medikal estetik distribütörünün rejeneratif bakım serisi."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-neogenesis-2'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'3 · KREDİBİLİTE ŞERİDİ',
             'html_template'=><<<'EDHTML'
<!-- 3 · KREDİBİLİTE ŞERİDİ -->

    <section class="ng-section" style="padding-top:0;">
      <div class="wrap">
        <div class="ng-cred reveal">
          <div class="ng-cred__grid">
            <div class="ng-cred__cell">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 22s7-4 7-12V5l-7-3-7 3v5c0 8 7 12 7 12z"/></svg></div>
              <b>{{group_1_text}}</b><span>{{group_1_text_2}}</span>
            </div>
            <div class="ng-cred__cell">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2c4 3.5 4 9 0 13-4-4-4-9.5 0-13z"/><path d="M12 8v13"/></svg></div>
              <b>{{group_2_text}}</b><span>{{group_2_text_2}}</span>
            </div>
            <div class="ng-cred__cell">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg></div>
              <b>{{group_3_text}}</b><span>{{group_3_text_2}}</span>
            </div>
            <div class="ng-cred__cell">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 7 9 18l-5-5"/></svg></div>
              <b>{{group_4_text}}</b><span>{{group_4_text_2}}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"group_1_text": {"type": "textarea", "label": "Group 1 Text"}, "group_1_text_2": {"type": "textarea", "label": "Group 1 Text 2"}, "group_2_text": {"type": "textarea", "label": "Group 2 Text"}, "group_2_text_2": {"type": "textarea", "label": "Group 2 Text 2"}, "group_3_text": {"type": "textarea", "label": "Group 3 Text"}, "group_3_text_2": {"type": "textarea", "label": "Group 3 Text 2"}, "group_4_text": {"type": "textarea", "label": "Group 4 Text"}, "group_4_text_2": {"type": "textarea", "label": "Group 4 Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"group_1_text": "Estetik Dermal", "group_1_text_2": "Resmi portföy markası", "group_2_text": "Rejeneratif Seri", "group_2_text_2": "Cilt yenilenmesi temalı bakım", "group_3_text": "Bilim + Doğa", "group_3_text_2": "Biyoteknoloji yaklaşımı", "group_4_text": "Profesyonel Kullanım", "group_4_text_2": "Klinik protokollere uyumlu"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-neogenesis-3'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'4 · ÖNE ÇIKAN ÜRÜNLER (split)',
             'html_template'=><<<'EDHTML'
<!-- 4 · ÖNE ÇIKAN ÜRÜNLER (split) -->

    <section class="ng-section" style="background:var(--ng-ivory);border-block:1px solid var(--ng-line);">
      <div class="wrap">
        <div class="ng-head reveal">
          <span class="ng-eyebrow">{{text}}</span>
          <h2>{{title}}</h2>
          <p>{{description}}</p>
        </div>

        <div class="ng-feat">
          <div class="ng-feat__media reveal">
            <!-- 🖼️ GÖRSEL: /assets/img/neogenesis-serum.jpg (oran 5:4)
                 PROMPT: "Premium regenerative skincare serum packshot, frosted glass bottle with green-tinted dropper on a sage gradient backdrop, a single clear serum droplet, soft botanical reflections, science-meets-nature aesthetic; photorealistic; no text, no logo" -->
            <div class="ng-feat__img" style="background-image:url('{{background_image_url}}')"></div>
          </div>
          <div class="reveal d1">
            <span class="ng-eyebrow">{{text_2}}</span>
            <h3>{{title_2}}</h3>
            <p class="lead2">{{{body_html}}}</p>
            <span class="ng-chip">{{text_3}}</span>
            <br>
            <a class="ng-link" href="{{button_url}}">{{button_text}} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </div>

        <div class="ng-feat ng-feat--rev">
          <div class="ng-feat__media reveal">
            <!-- 🖼️ GÖRSEL: /assets/img/neogenesis-mask.jpg (oran 5:4)
                 PROMPT: "Regenerative repair sheet mask presentation, soft green spa setting with fresh leaves and water droplets, calm sage and ivory tones, science-meets-nature wellness mood; photorealistic; no text, no logo" -->
            <div class="ng-feat__img" style="background-image:url('{{background_image_url_2}}')"></div>
          </div>
          <div class="reveal d1">
            <span class="ng-eyebrow">{{text_4}}</span>
            <h3>{{title_3}}</h3>
            <p class="lead2">{{{body_html_2}}}</p>
            <span class="ng-chip">{{text_5}}</span>
            <br>
            <a class="ng-link" href="{{button_url_2}}">{{button_text_2}} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"background_image_url": {"type": "image", "label": "Background Image Url"}, "button_url": {"type": "text", "label": "Button Url"}, "background_image_url_2": {"type": "image", "label": "Background Image Url 2"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}, "text_2": {"type": "textarea", "label": "Text 2"}, "title_2": {"type": "text", "label": "Title 2"}, "body_html": {"type": "textarea", "label": "Body"}, "text_3": {"type": "textarea", "label": "Text 3"}, "button_text": {"type": "textarea", "label": "Button Text"}, "text_4": {"type": "textarea", "label": "Text 4"}, "title_3": {"type": "text", "label": "Title 3"}, "body_html_2": {"type": "textarea", "label": "Body Html 2"}, "text_5": {"type": "textarea", "label": "Text 5"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"background_image_url": "/assets/img/neogenesis-serum.jpg", "button_url": "/urun-detay", "background_image_url_2": "/assets/img/neogenesis-mask.jpg", "button_url_2": "#yelpaze", "text": "Öne Çıkan Ürünler", "title": "Serinin yenilenme imzası", "description": "Rejeneratif bakım rutininin merkezinde yer alan iki temel ürün — günlük yenilenme ve yoğun onarım için.", "text_2": "Yenileyici Serum", "title_2": "Cellular Renewal Serum", "body_html": "Büyüme faktörü temalı yenileyici serum; cildin doğal onarım döngüsünü desteklemek üzere hafif, hızlı emilen bir dokuyla geliştirildi. Günlük rejeneratif bakım rutininin temel adımı.", "text_3": "● Büyüme faktörü temalı · Günlük kullanım", "button_text": "Ürün detayını incele", "text_4": "Onarıcı Maske", "title_3": "Regenerative Repair Mask", "body_html_2": "Yoğun onarım için tasarlanan, besleyici onarıcı maske. İşlem sonrası bakım ve yenilenme dönemlerinde cildi yatıştırmaya ve nem dengesini desteklemeye yardımcı olacak nazik formülasyon.", "text_5": "● Onarıcı bakım · Haftalık ritüel", "button_text_2": "Tüm ürün yelpazesini gör"}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-neogenesis-4'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'5 · ÜRÜN YELPAZESİ GRID',
             'html_template'=><<<'EDHTML'
<!-- 5 · ÜRÜN YELPAZESİ GRID -->

    <section class="ng-section" id="yelpaze">
      <div class="wrap">
        <div class="ng-head reveal">
          <span class="ng-eyebrow">{{text}}</span>
          <h2>{{title}}</h2>
          <p>{{{body_html}}}</p>
        </div>
        <div class="ng-grid">

          {{{ng_prod_items_html}}}</div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"ng_prod_items": {"type": "repeater", "label": "Ng Prod Items", "repeat_kind": "items", "item_template": "<a class=\"ng-prod reveal\" href=\"{{button_url}}\">\n            <div class=\"ng-prod__img\" style=\"background-image:url('{{background_image_url}}')\"></div>\n            <div class=\"ng-prod__body\">\n              <span class=\"ng-prod__cat\">{{text}}</span>\n              <h3>{{title}}</h3>\n              <p>{{description}}</p>\n              <span class=\"ng-prod__more\">{{text_2}} <svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M5 12h14M13 6l6 6-6 6\"/></svg></span>\n            </div>\n          </a>", "fields": {"button_url": {"type": "text", "label": "Button Url"}, "background_image_url": {"type": "image", "label": "Background Image Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "description": {"type": "textarea", "label": "Description"}, "text_2": {"type": "textarea", "label": "Text 2"}}}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"ng_prod_items": [{"button_url": "/urun-detay", "background_image_url": "/assets/img/neogenesis-serum.jpg", "text": "Yenileyici Serum", "title": "Cellular Renewal Serum", "description": "Büyüme faktörü temalı, günlük yenilenme için hafif dokulu yenileyici serum.", "text_2": "Detay"}, {"button_url": "/urun-detay", "background_image_url": "/assets/img/neogenesis-concentrate.jpg", "text": "Konsantre", "title": "Growth Factor Concentrate", "description": "Büyüme faktörü konsantresi; yoğun yenilenme dönemleri için odaklı bakım.", "text_2": "Detay"}, {"button_url": "/urun-detay", "background_image_url": "/assets/img/neogenesis-mask.jpg", "text": "Onarıcı Maske", "title": "Regenerative Repair Mask", "description": "Yatıştırıcı, nem dengesini destekleyen onarıcı maske; haftalık yenilenme ritüeli.", "text_2": "Detay"}, {"button_url": "/urun-detay", "background_image_url": "/assets/img/neogenesis-cleanser.jpg", "text": "Temizleyici", "title": "Gentle Botanical Cleanser", "description": "Bitkisel kaynaklı, cildin doğal dengesini koruyan nazik temizleyici.", "text_2": "Detay"}, {"button_url": "/urun-detay", "background_image_url": "/assets/img/neogenesis-cream.jpg", "text": "Onarıcı Krem", "title": "Restorative Day Cream", "description": "Gün boyu nemlendiren, cildi koruyan ve yenilenmeyi destekleyen onarıcı krem.", "text_2": "Detay"}, {"button_url": "/urun-detay", "background_image_url": "/assets/img/neogenesis-set.jpg", "text": "Bakım Seti", "title": "Regenerative Care Set", "description": "Rejeneratif rutini bir arada sunan, birbirini tamamlayan ürünlerden oluşan bakım seti.", "text_2": "Detay"}], "text": "Ürün Yelpazesi", "title": "Rejeneratif bakımın her adımı", "body_html": "Temizlikten yoğun onarıma uzanan, birbirini tamamlayan rejeneratif seri. Her ürün cildin yenilenme döngüsünün bir aşamasını destekler."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-neogenesis-5'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'6 · DOKTORLAR İÇİN / UYGULAMA',
             'html_template'=><<<'EDHTML'
<!-- 6 · DOKTORLAR İÇİN / UYGULAMA -->

    <section class="ng-section" style="background:var(--ng-ivory);border-block:1px solid var(--ng-line);">
      <div class="wrap">
        <div class="ng-docs reveal">
          <div class="ng-docs__leaf" aria-hidden="true"></div>
          <span class="ng-eyebrow" style="color:var(--ng-leaf);">{{text}}</span>
          <h2 style="margin-top:18px;">{{title}}</h2>
          <p class="lead2">{{{body_html}}}</p>
          <div class="ng-docs__cards">
            <div class="ng-docc">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div>
              <h4>{{group_1_eyebrow}}</h4>
              <p>{{group_1_description}}</p>
            </div>
            <div class="ng-docc">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2c4 3.5 4 9 0 13-4-4-4-9.5 0-13z"/><path d="M12 8v13"/></svg></div>
              <h4>{{group_2_eyebrow}}</h4>
              <p>{{group_2_description}}</p>
            </div>
            <div class="ng-docc">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 14h2"/></svg></div>
              <h4>{{group_3_eyebrow}}</h4>
              <p>{{group_3_description}}</p>
            </div>
          </div>
          <a class="ng-btn ng-btn--wagreen" href="{{button_url}}" target="_blank" rel="noopener">
            <svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4z"/></svg>
            {{button_text}}</a>
          <p class="ng-note">{{description}}</p>
        </div>
      </div>
    </section>

    
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "text": {"type": "textarea", "label": "Text"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "group_1_eyebrow": {"type": "text", "label": "Group 1 Eyebrow"}, "group_1_description": {"type": "textarea", "label": "Group 1 Description"}, "group_2_eyebrow": {"type": "text", "label": "Group 2 Eyebrow"}, "group_2_description": {"type": "textarea", "label": "Group 2 Description"}, "group_3_eyebrow": {"type": "text", "label": "Group 3 Eyebrow"}, "group_3_description": {"type": "textarea", "label": "Group 3 Description"}, "button_text": {"type": "textarea", "label": "Button Text"}, "description": {"type": "textarea", "label": "Description"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "text": "Doktorlar İçin", "title": "Klinik bakım protokollerine uyumlu rejeneratif seri", "body_html": "Neogenesis ürünleri, hekimlerin işlem öncesi ve sonrası bakım önerilerinde tamamlayıcı bir seçenek olarak konumlandırılır. Doğru kullanım ve protokol uyumu için Estetik Dermal ekibi yanınızda.", "group_1_eyebrow": "Ürün Eğitimi", "group_1_description": "Serinin doğru kullanımı ve klinik rutinlere entegrasyonu için bilgilendirme.", "group_2_eyebrow": "Bakım Protokolü", "group_2_description": "İşlem sonrası tamamlayıcı bakım önerileri için pratik kullanım rehberliği.", "group_3_eyebrow": "Tedarik & Destek", "group_3_description": "Sipariş, stok ve bilgi talepleri için Estetik Dermal'dan kesintisiz destek.", "button_text": "Klinik bilgi talep edin", "description": "Neogenesis ürünleri bakım amaçlı kozmetik serisidir; tıbbi tedavi veya tedavi sonucu garantisi sunmaz."}
EDJSON, true),
             'is_active'=>true]
        );
        SectionTemplate::updateOrCreate(
            ['theme_id'=>$tid,'type'=>'content-block','variation'=>'v2-s-marka-neogenesis-6'],
            ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
             'name'=>'7 · CTA BANDI',
             'html_template'=><<<'EDHTML'
<!-- 7 · CTA BANDI -->

    <section class="ng-section" style="padding-top:clamp(56px,8vh,96px);">
      <div class="wrap">
        <div class="ng-ctaband reveal">
          <div class="ng-ctaband__o1" aria-hidden="true"></div>
          <div class="ng-ctaband__o2" aria-hidden="true"></div>
          <h2>{{title}}</h2>
          <p>{{{body_html}}}</p>
          <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
            <a class="ng-btn ng-btn--wagreen" href="{{button_url}}" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4z"/></svg>
              {{button_text}}</a>
            <a class="ng-btn ng-btn--outline" href="{{button_url_2}}">{{button_text_2}}</a>
          </div>
        </div>
      </div>
    </section>
EDHTML,
             'schema_json'=>json_decode(<<<'EDJSON'
{"button_url": {"type": "text", "label": "Button Url"}, "button_url_2": {"type": "text", "label": "Button Url 2"}, "title": {"type": "text", "label": "Title"}, "body_html": {"type": "textarea", "label": "Body"}, "button_text": {"type": "textarea", "label": "Button Text"}, "button_text_2": {"type": "textarea", "label": "Button Text 2"}}
EDJSON, true),
             'default_content_json'=>json_decode(<<<'EDJSON'
{"button_url": "https://wa.me/905426205100", "button_url_2": "/urunler", "title": "Neogenesis hakkında bilgi alın", "body_html": "Ürün, fiyat ve klinik kullanım talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da tüm portföyü inceleyin.", "button_text": "WhatsApp ile Yaz", "button_text_2": "Tüm Ürünleri Gör"}
EDJSON, true),
             'is_active'=>true]
        );
        $this->command?->info('EstetikDermalV2FieldChromeSeeder: <built-in function len> bölüm şablonu kuruldu.');
    }
}
