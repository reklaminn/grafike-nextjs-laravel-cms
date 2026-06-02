<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\SectionTemplate;
use App\Models\Theme;
use App\Models\Language;
use Illuminate\Database\Seeder;

/** EstetikDermalKlasikTenantSeeder: alan-tabanlı sayfalar. Her body bloğu kendi field-template'ine bağlı,
 *  content = template default_content (İçerik sekmesinde düzenlenir). header/footer html_override. */
class EstetikDermalKlasikTenantSeeder extends Seeder
{
    private function blk(string $id,string $variation,$tpl,?string $override,int $sort): array
    {
        return ['id'=>$id,'type'=>($tpl?->type ?? 'content-block'),'variation'=>$variation,'render_mode'=>'html',
            'section_template_id'=>$tpl?->id,'is_active'=>true,'sort_order'=>$sort,
            'content'=>($override===null ? ($tpl?->default_content_json ?? []) : []),
            'html_override'=>$override];
    }
    private function regRow(string $r, array $blocks): array
    {
        return ['id'=>'row_'.$r.'_1','type'=>'row','is_active'=>true,
            'columns'=>[['id'=>'col_'.$r.'_1','width'=>12,'is_active'=>true,'blocks'=>$blocks]]];
    }
    public function run(): void
    {
        $theme=Theme::where('slug','estetikdermal')->first();
        $byvar=fn($v)=>$theme?SectionTemplate::where('theme_id',$theme->id)->where('variation',$v)->first():null;
        $hdrTpl=$byvar('estetikdermal-header'); $ftrTpl=$byvar('estetikdermal-footer');
        if(! $byvar(SectionTemplate::where('theme_id',$theme?->id)->where('variation','like','%-s-%')->value('variation') ?? '__none')){
            $this->command?->warn('Alan şablonları yok — önce FieldChromeSeeder.'); return;
        }
        $lang=Language::query()->where('code','tr')->first() ?? Language::query()->first();
        $langId=$lang?->id;
        Page::where('slug','like','klasik%')->delete(); Page::where('slug','like','marka/%')->delete(); Page::where('slug','marka-neogenesis')->delete(); // v2 kaldırıldı: klasik kök slug'ları v2'yi ezer, v2-özel neogenesis silinir
        $hdr=<<<'EDV2HDRX'
<style>
/* ============================================================================
   Estetik Dermal — Design Tokens
   iraspa-cms token sistemiyle birebir uyumlu (:root, var(--token, fallback)).
   Marka rengini değiştirmek için sadece burayı düzenle; tüm bloklar otomatik güncellenir.

   ANA PALET (logodan: turuncu yaprak gradyanı + gri wordmark)
   - primary   #E8702A  Estetik Dermal turuncu
   - accent    #F4A14E  açık mercan (gradyan ikinci durağı / vurgu)
   - secondary #FBF4EE  sıcak krem (açık bölüm zemini)
   - text      #3A3A3A  antrasit (logo wordmark grisi)
   ============================================================================ */

:root {
  /* iraspa-cms eşlemeli tokenlar (Theme.tokens_json) */
  --color-primary: #E8702A;
  --color-secondary: #FBF4EE;
  --color-accent: #F4A14E;
  --radius-card: 18px;
  --radius-button: 999px;
  --container-width: 1280px;

  /* globals.css ekstraları */
  --bg-page: #ffffff;
  --text-main: #2a2a2a;
  --text-soft: #6b6b6b;
  --border-soft: #ece6df;

  /* türetilmiş yardımcılar (statik sitede; CMS bloklarında inline yazılır) */
  --primary-deep: #C85716;
  --primary-soft: #FCE9DC;
  --accent-soft: #FBE3CB;
  --grad-brand: linear-gradient(135deg, #E8702A 0%, #F4A14E 100%);
  --shadow-card: 0 14px 44px rgba(200, 87, 22, .10);
  --shadow-soft: 0 4px 18px rgba(58, 58, 58, .07);
}

/* ============================================================================
   estetikdermal.com — Base / Chrome stilleri
   iraspa-cms app/globals.css aynası + statik önizleme için header/footer kabuğu.

   ÖNEMLİ: İçerik blokları (hero, services, faq, cta ...) TAMAMEN inline style +
   var(--token, fallback) kullanır — base.css'e bağımlı DEĞİLDİR. Böylece her blok
   CMS seeder'ına html_template olarak birebir taşınabilir. Buradaki class'lar yalnızca
   sayfa kabuğu (container, header, footer, mobil menü) içindir; bunlar CMS'te
   menü + site ayarları + sistem placeholder'larından üretilir (README'ye bak).
   ============================================================================ */

/* ---- Reset (globals.css mirror) ---- */
*, *::before, *::after { box-sizing: border-box; }
html { -webkit-text-size-adjust: 100%; scroll-behavior: smooth; }
html, body {
  margin: 0; padding: 0;
  background: var(--bg-page, #fff);
  color: var(--text-main, #0f172a);
  font-family: "Segoe UI", system-ui, -apple-system, Arial, sans-serif;
  line-height: 1.65;
  -webkit-font-smoothing: antialiased;
}
a { color: inherit; text-decoration: none; }
img { max-width: 100%; display: block; }
button { font: inherit; cursor: pointer; }
h1, h2, h3, h4 { line-height: 1.18; margin: 0; }
p { margin: 0; }
:focus-visible { outline: 3px solid var(--color-accent, #F4A14E); outline-offset: 2px; border-radius: 4px; }

/* ---- Sayfa kabuğu (cms-page-content.tsx: <main class="container page-stack">) ---- */
.container { width: min(100% - 32px, var(--container-width, 1280px)); margin: 0 auto; }
.page-stack { display: flex; flex-direction: column; }

/* Erişilebilirlik: skip link */
.skip-link {
  position: absolute; left: -9999px; top: 0; z-index: 200;
  background: var(--color-primary, #E8702A); color: #fff;
  padding: 10px 18px; border-radius: 0 0 8px 0;
}
.skip-link:focus { left: 0; }

/* ============================================================================
   SITE HEADER (statik kabuk — CMS'te {{logo_url}} + {{{menu_header_html}}})
   Mobil menü: saf CSS checkbox-hack (sıfır JS)
   ============================================================================ */
.site-header {
  position: sticky; top: 0; z-index: 100;
  background: rgba(255, 255, 255, .92);
  backdrop-filter: saturate(180%) blur(10px);
  border-bottom: 1px solid var(--border-soft, #ece6df);
}
.site-header__inner {
  display: flex; align-items: center; justify-content: space-between;
  gap: 20px; min-height: 76px;
}
.brand { display: flex; align-items: center; gap: 12px; font-weight: 800; font-size: 20px; letter-spacing: .2px; color: var(--color-primary, #E8702A); }
/* Logo işareti: gerçek turuncu yaprak SVG'si (assets/img/logo-mark.svg).
   "ED" metni font-size:0 ile gizlenir, yaprak background olarak görünür.
   Resmi logo dosyasıyla logo-mark.svg değiştirilirse otomatik güncellenir. */
.brand__mark {
  width: 46px; height: 42px; flex-shrink: 0;
  background: url("/assets/img/logo-mark.svg") left center / contain no-repeat;
  font-size: 0;
}
.brand small { display: block; font-size: 11px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; color: var(--text-soft, #475569); }

.nav { display: flex; align-items: center; gap: 8px; }
.nav a {
  padding: 9px 14px; border-radius: 999px; font-weight: 600; font-size: 15px; color: var(--text-main, #0f172a);
  transition: background .18s, color .18s;
}
.nav a:hover, .nav a[aria-current="page"] { background: var(--color-secondary, #FBF4EE); color: var(--color-primary, #E8702A); }

/* WhatsApp CTA — WhatsApp yeşili (#25D366) + gerçek WhatsApp ikonu */
.header-cta {
  display: inline-flex; align-items: center; gap: 8px;
  background: #25D366; color: #fff;
  padding: 11px 18px; border-radius: var(--radius-button, 999px); font-weight: 700; font-size: 14px;
  box-shadow: 0 4px 14px rgba(37, 211, 102, .28);
  transition: transform .18s, box-shadow .18s, background .18s;
}
.header-cta:hover { transform: translateY(-1px); background: #20BD5B; box-shadow: 0 8px 22px rgba(37, 211, 102, .36); }
.header-cta svg { width: 17px; height: 17px; flex-shrink: 0; }

/* Checkbox-hack mobil menü */
.nav-toggle { display: none; }
.nav-toggle-label { display: none; }

@media (max-width: 920px) {
  .nav-toggle-label {
    display: inline-flex; flex-direction: column; gap: 5px;
    width: 46px; height: 46px; border: 1px solid var(--border-soft, #ece6df);
    border-radius: 12px; align-items: center; justify-content: center; background: #fff;
  }
  .nav-toggle-label span { display: block; width: 22px; height: 2px; background: var(--color-primary, #E8702A); border-radius: 2px; transition: .25s; }
  .nav {
    position: fixed; inset: 76px 0 auto 0;
    flex-direction: column; align-items: stretch; gap: 4px;
    background: #fff; border-bottom: 1px solid var(--border-soft, #ece6df);
    padding: 14px 16px 22px;
    transform: translateY(-130%); transition: transform .3s ease; box-shadow: var(--shadow-card, 0 10px 40px rgba(15,23,42,.08));
  }
  .nav a { padding: 13px 14px; font-size: 16px; }
  .header-cta { margin-top: 8px; justify-content: center; }
  .nav-toggle:checked ~ .nav { transform: translateY(0); }
  .nav-toggle:checked ~ .nav-toggle-label span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
  .nav-toggle:checked ~ .nav-toggle-label span:nth-child(2) { opacity: 0; }
  .nav-toggle:checked ~ .nav-toggle-label span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
}

/* ============================================================================
   SITE FOOTER (statik kabuk — CMS'te {{{menu_footer_html}}} + ayarlar)
   ============================================================================ */
.site-footer { background: #262220; color: #fff; margin-top: 8px; }
.site-footer a { color: rgba(255, 255, 255, .82); transition: color .18s; }
.site-footer a:hover { color: #fff; }
.site-footer__grid {
  display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.2fr; gap: 40px;
  padding: 64px 0 40px;
}
.site-footer h4 { font-size: 14px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--color-accent, #F4A14E); margin-bottom: 18px; }
.site-footer ul { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 11px; font-size: 15px; }
.site-footer .brand { color: #fff; }
.site-footer .brand small { color: rgba(255,255,255,.6); }
.footer-social { display: flex; gap: 12px; margin-top: 16px; }
.footer-social a {
  width: 42px; height: 42px; border-radius: 12px; background: rgba(255, 255, 255, .1);
  display: grid; place-items: center; font-size: 18px;
}
.footer-social a:hover { background: var(--color-accent, #F4A14E); color: var(--color-primary, #E8702A); }
.site-footer__bottom {
  border-top: 1px solid rgba(255, 255, 255, .14);
  padding: 22px 0; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between;
  font-size: 13.5px; color: rgba(255, 255, 255, .7);
}

@media (max-width: 820px) {
  .site-footer__grid { grid-template-columns: 1fr 1fr; gap: 32px; padding: 48px 0 32px; }
}
@media (max-width: 520px) {
  .site-footer__grid { grid-template-columns: 1fr; }
}

/* ============================================================================
   LOGO (resim) — header'da tam kilit logo.
   Öncelik: assets/img/dermallogo.png (resmi). Yoksa logo-full.svg (yedek).
   Resmi PNG dosyasını koyunca otomatik üstte gösterilir; bozuk görsel olmaz.
   ============================================================================ */
.brand__logo {
  display: block; width: 215px; height: 40px; flex-shrink: 0;
  background-image: url("/assets/img/dermallogo.png"), url("/assets/img/logo-full.svg");
  background-repeat: no-repeat, no-repeat;
  background-position: left center, left center;
  background-size: contain, contain;
}

/* ============================================================================
   HERO SLIDER — saf CSS (scroll-snap). Sıfır JS.
   Kaydırarak (swipe) gezilir; noktalar tıklanınca ilgili slayta kayar (#id).
   Slayt eklemek için: .hero-slides içine yeni .hero-slide ekle + .hero-dots'a
   yeni <a href="#hsN"> ekle. 3-4 slayt önerilir.
   ============================================================================ */
.hero-slider { position: relative; }
.hero-slides {
  display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth;
  border-radius: 24px; scrollbar-width: none; -ms-overflow-style: none;
}
.hero-slides::-webkit-scrollbar { display: none; }
.hero-slide { flex: 0 0 100%; scroll-snap-align: center; }
.hero-dots { display: flex; gap: 9px; justify-content: center; margin-top: 16px; }
.hero-dots a {
  width: 9px; height: 9px; border-radius: 999px;
  background: var(--border-soft, #ece6df); transition: background .2s, transform .2s, width .2s;
}
.hero-dots a:hover, .hero-dots a:focus { background: var(--color-primary, #E8702A); transform: scale(1.15); }

/* Slider ok butonları */
.hero-arrow {
  position: absolute; top: 42%; transform: translateY(-50%);
  width: 42px; height: 42px; border-radius: 999px;
  background: #fff; border: 1px solid var(--border-soft, #ece6df);
  box-shadow: var(--shadow-soft, 0 4px 18px rgba(58,58,58,.10));
  display: grid; place-items: center; color: var(--text-main, #2a2a2a);
  cursor: pointer; z-index: 3; padding: 0;
  transition: background .18s, color .18s, transform .18s, box-shadow .18s;
}
.hero-arrow:hover { background: var(--color-primary, #E8702A); color: #fff; transform: translateY(-50%) scale(1.07); box-shadow: 0 8px 22px rgba(232,112,42,.30); }
.hero-arrow svg { width: 19px; height: 19px; }
.hero-arrow--prev { left: -10px; }
.hero-arrow--next { right: -10px; }
@media (max-width: 520px) { .hero-arrow { width: 38px; height: 38px; } .hero-arrow--prev { left: 4px; } .hero-arrow--next { right: 4px; } }

/* Kategori çipleri (premium) */
.cat-pill { transition: border-color .18s, color .18s, background .18s; }
.cat-pill:hover { border-color: var(--color-primary, #E8702A); color: var(--color-primary, #E8702A); background: var(--primary-soft, #FCE9DC); }

.reveal{opacity:1 !important;transform:none !important}
</style><header class="site-header">
    <div class="container site-header__inner">
      <a class="brand" href="/" aria-label="Estetik Dermal ana sayfa">
        <span class="brand__logo" role="img" aria-label="Estetik Dermal — Medikal Estetik"></span>
      </a>
      <input type="checkbox" id="navToggle" class="nav-toggle" aria-hidden="true">
      <label for="navToggle" class="nav-toggle-label" aria-label="Menüyü aç / kapat"><span></span><span></span><span></span></label>
      <nav class="nav" aria-label="Ana menü">
        <a href="/" aria-current="page">Ana Sayfa</a>
        <a href="/hakkimizda">Hakkımızda</a>
        <a href="/urunler">Ürünler</a>
        <a href="/markalar">Markalar</a>
        <a href="/etkinlikler">Etkinlikler</a>
        <a href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener">MEDINET ↗</a>
        <a href="/iletisim">İletişim</a>
        <a class="header-cta" href="https://wa.me/905426205100" target="_blank" rel="noopener"><svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp</a>
      </nav>
    </div>
  </header>
EDV2HDRX;
        $ftr=<<<'EDV2FTRX'
<footer class="site-footer">
    <div class="container site-footer__grid">
      <div>
        <div class="brand"><span class="brand__mark">ED</span><span>Estetik Dermal<small>Medikal Estetik</small></span></div>
        <p style="margin:18px 0 0;color:rgba(255,255,255,.7);font-size:14.5px;line-height:1.7;max-width:300px;">2004'ten beri medikal estetiğin yenilikçi ürünlerini Türkiye geneline sunan resmi distribütör.</p>
        <div class="footer-social">
          <a href="https://www.instagram.com/estetikdermal/" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="19" height="19" aria-hidden="true"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg></a>
          <a href="https://wa.me/905426205100" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 32 32" fill="currentColor" width="19" height="19" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg></a>
        </div>
      </div>
      <div>
        <h4>Kurumsal</h4>
        <ul>
          <li><a href="/hakkimizda">Hakkımızda</a></li>
          <li><a href="/markalar">Markalar</a></li>
          <li><a href="/etkinlikler">Kongre & Etkinlikler</a></li>
          <li><a href="/iletisim">İletişim</a></li>
        </ul>
      </div>
      <div>
        <h4>Markalar</h4>
        <ul>
          <li><a href="/marka-skintech">Skin Tech Pharma</a></li>
          <li><a href="/marka-seffiline">Seffiline</a></li>
          <li><a href="/marka-aespio">Grand Aespio</a></li>
          <li><a href="/marka-woorhi">Woorhi Mechatronics</a></li>
          <li><a href="/marka-mi-medical">Mi Medical Innovation</a></li>
        </ul>
      </div>
      <div>
        <h4>İletişim</h4>
        <ul>
          <li><a href="tel:+902566121813">0 256 612 18 13</a></li>
          <li><a href="https://wa.me/905426205100" target="_blank" rel="noopener">+90 542 620 51 00</a></li>
          <li><a href="mailto:info@estetikdermal.com">info@estetikdermal.com</a></li>
          <li style="color:rgba(255,255,255,.7);">Türkmen Mah. Turgut Özel Bulvarı, Ada Modern A Blok No 83/3A Kuşadası/Aydın</li>
        </ul>
      </div>
    </div>
    <div class="container site-footer__bottom">
      <span>© 2026 Estetik Dermal. Tüm hakları saklıdır.</span>
      <span>Resmi medikal estetik distribütörü · Kuşadası / Aydın</span>
    </div>
  </footer>
EDV2FTRX;
        $pages=[
            ['slug'=>'home','title'=>'Ana Sayfa','order'=>1,'menu'=>true,'vars'=>[
                'klasik-s-home-0','klasik-s-home-1','klasik-s-home-2','klasik-s-home-3','klasik-s-home-4','klasik-s-home-5','klasik-s-home-6','klasik-s-home-7'
            ]],
            ['slug'=>'hakkimizda','title'=>'Hakkımızda','order'=>2,'menu'=>true,'vars'=>[
                'klasik-s-hakkimizda-0','klasik-s-hakkimizda-1','klasik-s-hakkimizda-2','klasik-s-hakkimizda-3','klasik-s-hakkimizda-4','klasik-s-hakkimizda-5','klasik-s-hakkimizda-6'
            ]],
            ['slug'=>'urunler','title'=>'Ürünler','order'=>3,'menu'=>true,'vars'=>[
                'klasik-s-urunler-0','klasik-s-urunler-1','klasik-s-urunler-2','klasik-s-urunler-3','klasik-s-urunler-4','klasik-s-urunler-5'
            ]],
            ['slug'=>'urun-detay','title'=>'RRS® HA Long Lasting','order'=>99,'menu'=>false,'vars'=>[
                'klasik-s-urun-detay-0','klasik-s-urun-detay-1','klasik-s-urun-detay-2','klasik-s-urun-detay-3','klasik-s-urun-detay-4'
            ]],
            ['slug'=>'markalar','title'=>'Markalar','order'=>4,'menu'=>true,'vars'=>[
                'klasik-s-markalar-0','klasik-s-markalar-1','klasik-s-markalar-2','klasik-s-markalar-3'
            ]],
            ['slug'=>'etkinlikler','title'=>'Kongre & Etkinlikler','order'=>5,'menu'=>true,'vars'=>[
                'klasik-s-etkinlikler-0','klasik-s-etkinlikler-1','klasik-s-etkinlikler-2','klasik-s-etkinlikler-3'
            ]],
            ['slug'=>'iletisim','title'=>'İletişim','order'=>6,'menu'=>true,'vars'=>[
                'klasik-s-iletisim-0','klasik-s-iletisim-1','klasik-s-iletisim-2'
            ]],
            ['slug'=>'marka-skintech','title'=>'Skin Tech Pharma Group','order'=>101,'menu'=>false,'vars'=>[
                'klasik-s-marka-skintech-0','klasik-s-marka-skintech-1','klasik-s-marka-skintech-2','klasik-s-marka-skintech-3','klasik-s-marka-skintech-4','klasik-s-marka-skintech-5'
            ]],
            ['slug'=>'marka-seffiline','title'=>'Seffiline','order'=>102,'menu'=>false,'vars'=>[
                'klasik-s-marka-seffiline-0','klasik-s-marka-seffiline-1','klasik-s-marka-seffiline-2','klasik-s-marka-seffiline-3','klasik-s-marka-seffiline-4','klasik-s-marka-seffiline-5'
            ]],
            ['slug'=>'marka-aespio','title'=>'Grand Aespio','order'=>103,'menu'=>false,'vars'=>[
                'klasik-s-marka-aespio-0','klasik-s-marka-aespio-1','klasik-s-marka-aespio-2','klasik-s-marka-aespio-3','klasik-s-marka-aespio-4','klasik-s-marka-aespio-5'
            ]],
            ['slug'=>'marka-woorhi','title'=>'Woorhi Mechatronics','order'=>104,'menu'=>false,'vars'=>[
                'klasik-s-marka-woorhi-0','klasik-s-marka-woorhi-1','klasik-s-marka-woorhi-2','klasik-s-marka-woorhi-3','klasik-s-marka-woorhi-4'
            ]],
            ['slug'=>'marka-mi-medical','title'=>'Mi Medical Innovation','order'=>105,'menu'=>false,'vars'=>[
                'klasik-s-marka-mi-medical-0','klasik-s-marka-mi-medical-1','klasik-s-marka-mi-medical-2','klasik-s-marka-mi-medical-3','klasik-s-marka-mi-medical-4'
            ]],
        ];
        foreach($pages as $p){
            $bodyBlocks=[];
            foreach($p['vars'] as $k=>$v){
                $bodyBlocks[]=$this->blk('b_body_'.$k, $v, $byvar($v), null, $k+1);
            }
            Page::updateOrCreate(['slug'=>$p['slug'],'language_id'=>$langId],
                ['title'=>$p['title'],'status'=>'published','show_in_menu'=>$p['menu'],
                 'sort_order'=>$p['order'],'show_breadcrumb'=>true,
                 'sections_json'=>['version'=>2,'regions'=>[
                   'header'=>[$this->regRow('header',[$this->blk('b_header','estetikdermal-header',$hdrTpl,$hdr,1)])],
                   'body'  =>[$this->regRow('body',$bodyBlocks)],
                   'footer'=>[$this->regRow('footer',[$this->blk('b_footer','estetikdermal-footer',$ftrTpl,$ftr,1)])],
                 ]]]
            );
        }
        foreach([
            ['site.title','Estetik Dermal','general'],['site.footer_text','© 2026 Estetik Dermal. Tüm hakları saklıdır.','general'],
            ['site.logo','','general'],['contact.phone','0 256 612 18 13','contact'],['contact.email','info@estetikdermal.com','contact'],
            ['contact.address','Türkmen Mah. Turgut Özel Bulvarı Ada Modern A Blok No 83/3A Kuşadası/Aydın','contact'],
            ['social.whatsapp','905426205100','social'],['social.instagram','https://www.instagram.com/estetikdermal/','social'],
        ] as $s){ SiteSetting::updateOrCreate(['key'=>$s[0]],['value'=>$s[1],'group'=>$s[2],'type'=>'text']); }

        $this->command?->info('EstetikDermalKlasikTenantSeeder: '.count($pages).' sayfa (alan-editli) kuruldu.');
    }
}
