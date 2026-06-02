<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\SectionTemplate;
use App\Models\Theme;
use App\Models\Language;
use Illuminate\Database\Seeder;

/**
 * Estetik Dermal — TEMA 1 (Klasik / turuncu modern) tenant sayfaları.
 * version-2 REGION formatı: header/body/footer ayrı bloklar, HTML html_override'da (Quill ezmez).
 * Slug'lar düz 'klasik-...' önekli → Tema 2 (kök slug) ile ÇAKIŞMAZ; ikisi yan yana canlı.
 *   /tr/klasik , /tr/klasik-hakkimizda , /tr/klasik-marka-skintech ...
 * TENANT CONTEXT'inde çalıştır. Önce: ThemeSeeder + ChromeSeeder (central) çalışmış olmalı.
 */
class EstetikDermalKlasikTenantSeeder extends Seeder
{
    private function blk(string $id,string $type,string $variation,int $tid,string $override,int $sort): array
    {
        return ['id'=>$id,'type'=>$type,'variation'=>$variation,'render_mode'=>'html',
            'section_template_id'=>$tid,'is_active'=>true,'sort_order'=>$sort,'content'=>[],'html_override'=>$override];
    }
    private function regRow(string $r, array $blocks): array
    {
        return ['id'=>'row_'.$r.'_1','type'=>'row','is_active'=>true,
            'columns'=>[['id'=>'col_'.$r.'_1','width'=>12,'is_active'=>true,'blocks'=>$blocks]]];
    }

    public function run(): void
    {
        $theme = Theme::where('slug','estetikdermal')->first();
        $tpl=fn($type,$var)=>$theme?SectionTemplate::where('theme_id',$theme->id)->where('type',$type)->where('variation',$var)->first():null;
        $cb = $tpl('content-block','free-html');
        $hdrTpl=$tpl('header','estetikdermal-header');
        $ftrTpl=$tpl('footer','estetikdermal-footer');
        if (! $cb) { $this->command?->warn('content-block (free-html) yok — önce EstetikDermalChromeSeeder.'); return; }
        $hid=$hdrTpl?->id ?? $cb->id;  $fid=$ftrTpl?->id ?? $cb->id;
        $lang = Language::query()->where('code','tr')->first() ?? Language::query()->first();
        $langId = $lang?->id;
        // Eski nested-slug klasik sayfalarını temizle (route slash desteklemez → düz 'klasik-x')
        Page::where('slug','like','klasik/%')->delete();
        $hdr = <<<'EDKHDRX'
<style>/* ============================================================================
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

/* CMS: JS yok — slider 1. slaytı gösterir, reveal görünür */
.reveal{opacity:1 !important;transform:none !important}
</style><header class="site-header">
    <div class="container site-header__inner">
      <a class="brand" href="/klasik" aria-label="Estetik Dermal ana sayfa">
        <span class="brand__logo" role="img" aria-label="Estetik Dermal — Medikal Estetik"></span>
      </a>
      <input type="checkbox" id="navToggle" class="nav-toggle" aria-hidden="true">
      <label for="navToggle" class="nav-toggle-label" aria-label="Menüyü aç / kapat"><span></span><span></span><span></span></label>
      <nav class="nav" aria-label="Ana menü">
        <a href="/klasik" aria-current="page">Ana Sayfa</a>
        <a href="/klasik-hakkimizda">Hakkımızda</a>
        <a href="/klasik-urunler">Ürünler</a>
        <a href="/klasik-markalar">Markalar</a>
        <a href="/klasik-etkinlikler">Etkinlikler</a>
        <a href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener">MEDINET ↗</a>
        <a href="/klasik-iletisim">İletişim</a>
        <a class="header-cta" href="https://wa.me/905426205100" target="_blank" rel="noopener"><svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp</a>
      </nav>
    </div>
  </header>
EDKHDRX;
        $ftr = <<<'EDKFTRX'
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
          <li><a href="/klasik-hakkimizda">Hakkımızda</a></li>
          <li><a href="/klasik-markalar">Markalar</a></li>
          <li><a href="/klasik-etkinlikler">Kongre & Etkinlikler</a></li>
          <li><a href="/klasik-iletisim">İletişim</a></li>
        </ul>
      </div>
      <div>
        <h4>Markalar</h4>
        <ul>
          <li><a href="/klasik-marka-skintech">Skin Tech Pharma</a></li>
          <li><a href="/klasik-marka-seffiline">Seffiline</a></li>
          <li><a href="/klasik-marka-aespio">Grand Aespio</a></li>
          <li><a href="/klasik-marka-woorhi">Woorhi Mechatronics</a></li>
          <li><a href="/klasik-marka-mi-medical">Mi Medical Innovation</a></li>
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
EDKFTRX;
        $p0s0 = <<<'EDKP0S0'
<!-- HERO (split) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(1200px 600px at 80% -10%, rgba(244,161,78,.28), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:48px;align-items:center;padding:84px 0 92px;">
        <div style="flex:1 1 460px;">
          <p style="display:inline-flex;align-items:center;gap:8px;background:var(--primary-soft,#FCE9DC);color:var(--primary-deep,#C85716);border-radius:999px;padding:8px 16px;font-size:13px;font-weight:700;letter-spacing:.4px;margin:0 0 22px;">● 2004'ten beri Türkiye'nin güveni</p>
          <h1 style="font-size:clamp(34px,5vw,56px);line-height:1.08;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 20px;">Medikal estetikte<br><span style="background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));-webkit-background-clip:text;background-clip:text;color:transparent;">yenilikçi çözümler</span></h1>
          <p style="font-size:clamp(16px,2vw,20px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:560px;margin:0 0 32px;">Tek seansta uzun etkili sonuçlarla yüksek memnuniyet. Uluslararası markaların resmi temsilcisi olarak, doktorlara ürün ve uygulamalı eğitim sunuyoruz.</p>
          <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a href="/klasik-urunler" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#E8702A);color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 10px 30px rgba(232,112,42,.32);">Ürünleri Keşfet →</a>
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);padding:15px 26px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;"><svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp Danışma</a>
          </div>
          <div style="display:flex;gap:28px;flex-wrap:wrap;margin-top:38px;">
            <div><div style="font-size:13px;color:var(--text-soft,#6b6b6b);">Sertifika</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">CE Class III</div></div>
            <div style="width:1px;background:var(--border-soft,#ece6df);"></div>
            <div><div style="font-size:13px;color:var(--text-soft,#6b6b6b);">Kapsama</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">Türkiye Geneli</div></div>
            <div style="width:1px;background:var(--border-soft,#ece6df);"></div>
            <div><div style="font-size:13px;color:var(--text-soft,#6b6b6b);">Destek</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">Uygulamalı Eğitim</div></div>
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
              <article class="hero-slide" id="hs1">
                <div style="background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:24px;box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:26px;">
                  <!-- 🖼️ GÖRSEL: /assets/img/hero-rrs.jpg (oran 4:3) — RRS HA Long Lasting ürün çekimi (krem zemin, klinik premium) -->
                  <div style="aspect-ratio:4/3;border-radius:16px;background:var(--color-secondary,#FBF4EE) url('assets/img/hero-rrs.jpg') center/cover no-repeat;"></div>
                  <div style="display:flex;align-items:center;justify-content:space-between;margin-top:18px;">
                    <div><div style="font-size:12px;color:var(--color-primary,#E8702A);font-weight:700;text-transform:uppercase;letter-spacing:1px;">Skin Tech</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">RRS® HA Long Lasting</div></div>
                    <span style="background:var(--primary-soft,#FCE9DC);color:var(--primary-deep,#C85716);font-size:12px;font-weight:700;padding:6px 12px;border-radius:999px;">CE III</span>
                  </div>
                </div>
              </article>
              <article class="hero-slide" id="hs2">
                <div style="background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:24px;box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:26px;">
                  <!-- 🖼️ GÖRSEL: /assets/img/hero-melablock.jpg (oran 4:3) — Melablock HSP SPF 50+ güneş koruma ürün çekimi -->
                  <div style="aspect-ratio:4/3;border-radius:16px;background:var(--color-secondary,#FBF4EE) url('assets/img/hero-melablock.jpg') center/cover no-repeat;"></div>
                  <div style="display:flex;align-items:center;justify-content:space-between;margin-top:18px;">
                    <div><div style="font-size:12px;color:var(--color-primary,#E8702A);font-weight:700;text-transform:uppercase;letter-spacing:1px;">Skin Tech</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">Melablock HSP SPF 50+</div></div>
                    <span style="background:var(--primary-soft,#FCE9DC);color:var(--primary-deep,#C85716);font-size:12px;font-weight:700;padding:6px 12px;border-radius:999px;">SPF 50+</span>
                  </div>
                </div>
              </article>
              <article class="hero-slide" id="hs3">
                <div style="background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:24px;box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:26px;">
                  <!-- 🖼️ GÖRSEL: /assets/img/hero-benebellum.jpg (oran 4:3) — Benebellum LUMINA VİT-C mezoterapi ürün çekimi -->
                  <div style="aspect-ratio:4/3;border-radius:16px;background:var(--color-secondary,#FBF4EE) url('assets/img/hero-benebellum.jpg') center/cover no-repeat;"></div>
                  <div style="display:flex;align-items:center;justify-content:space-between;margin-top:18px;">
                    <div><div style="font-size:12px;color:var(--color-primary,#E8702A);font-weight:700;text-transform:uppercase;letter-spacing:1px;">Skin Tech</div><div style="font-weight:800;color:var(--text-main,#2a2a2a);">Benebellum LUMINA VİT-C</div></div>
                    <span style="background:var(--primary-soft,#FCE9DC);color:var(--primary-deep,#C85716);font-size:12px;font-weight:700;padding:6px 12px;border-radius:999px;">VİT-C</span>
                  </div>
                </div>
              </article>
            </div>
            <div class="hero-dots">
              <a href="#hs1" aria-label="1. ürün"></a>
              <a href="#hs2" aria-label="2. ürün"></a>
              <a href="#hs3" aria-label="3. ürün"></a>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDKP0S0;
        $p0s1 = <<<'EDKP0S1'
<!-- TRUST STATS -->

    <section style="background:#fff;">
      <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:24px;padding:48px 0;border-bottom:1px solid var(--border-soft,#ece6df);">
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">20+</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">Yıllık Deneyim</div></div>
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">5+</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">Uluslararası Marka</div></div>
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">95+</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">Profesyonel Ürün</div></div>
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">81</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">İl Dağıtım Ağı</div></div>
      </div>
    </section>

    
EDKP0S1;
        $p0s2 = <<<'EDKP0S2'
<!-- BRAND SHOWCASE -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container">
        <div style="text-align:center;max-width:640px;margin:0 auto 52px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Temsil Ettiğimiz Markalar</p>
          <h2 style="font-size:clamp(28px,4vw,40px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 14px;line-height:1.15;">Dünyanın önde gelen markaları, Türkiye'de tek adreste</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.7;">Her biri kendi alanında uzman; mezoterapiden cihaza, peelingden ip askıya geniş bir portföy.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;">
          <a href="/klasik-marka-skintech" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #0C6E72;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#0C6E72;margin-bottom:10px;">İspanya</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;">Skin Tech Pharma</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 16px;">Peeling, mezoterapi ve RRS skinbooster serisi.</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">Keşfet →</span>
          </a>
          <a href="/klasik-marka-seffiline" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #C98A6D;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#C98A6D;margin-bottom:10px;">Cilt & Saç</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;">Seffiline</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 16px;">Cilt, saç, intim bakım ve dolgu çözümleri.</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">Keşfet →</span>
          </a>
          <a href="/klasik-marka-aespio" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #6C5CE0;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#6C5CE0;margin-bottom:10px;">K-Beauty</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;">Grand Aespio</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 16px;">Yüz maskeleri ve ip askı (thread) ürünleri.</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">Keşfet →</span>
          </a>
          <a href="/klasik-marka-woorhi" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #2DA8FF;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#2480C8;margin-bottom:10px;">Güney Kore</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;">Woorhi Mechatronics</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 16px;">Kore mühendisliği medikal estetik cihazları.</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">Keşfet →</span>
          </a>
          <a href="/klasik-marka-mi-medical" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #C9A24B;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#B08A38;margin-bottom:10px;">Premium</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;">Mi Medical Innovation</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 16px;">Premium mezoterapi ve enjeksiyon sistemleri.</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">Keşfet →</span>
          </a>
        </div>
      </div>
    </section>

    
EDKP0S2;
        $p0s3 = <<<'EDKP0S3'
<!-- FEATURED PRODUCTS -->

    <section style="padding:84px 0;background:var(--color-secondary,#FBF4EE);">
      <div class="container">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:44px;">
          <div>
            <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 10px;">Öne Çıkan Ürünler</p>
            <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">Kliniğinizin en çok tercih ettikleri</h2>
          </div>
          <a href="/klasik-urunler" style="color:var(--color-primary,#E8702A);font-weight:700;font-size:15px;white-space:nowrap;">Tüm ürünler →</a>
        </div>
        <!-- 🖼️ GÖRSEL (öne çıkan ürün kartları – temsili blok, her kart için aynı desen):
             /assets/img/product-{slug}.jpg (oran 4:3) — örn. product-rrs-ha-long-lasting.jpg, product-melablock-spf50.jpg, product-benebellum-lumina-vitc.jpg, product-beta-glukan-mask.jpg
             PROMPT: "Clean studio product photography of a {ürün adı} professional medical aesthetics product, isolated on a soft cream-to-peach gradient background, premium clinical packaging, subtle soft shadow and reflection, warm terracotta and amber brand lighting, dermatology catalog style, no text, no logo, no watermark, high resolution"
             DEĞİŞTİR → her kartın gradyanlı div'i yerine: <img src="assets/img/product-{slug}.jpg" alt="{ÜRÜN ADI} ürün görseli" style="width:100%;height:100%;object-fit:cover;"> -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px;">
          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-rrs-ha-long-lasting.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · RRS</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">RRS® HA Long Lasting</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Çapraz bağlı hyalüronik asit içeren CE Class III dermal implant.</p>
            </div>
          </a>
          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-melablock-hsp-spf-50.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · Krem</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Melablock HSP SPF 50+</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktör.</p>
            </div>
          </a>
          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-benebellum-lumina-vit-c-18.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · Mezoterapi</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Benebellum LUMINA VİT-C 18%</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Yüksek konsantrasyonlu C vitamini ile aydınlatıcı bakım.</p>
            </div>
          </a>
          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-beta-glukan-mask.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#6C5CE0;text-transform:uppercase;letter-spacing:.6px;">Grand Aespio · Maske</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Beta-Glukan Mask</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Yatıştırıcı ve onarıcı beta-glukan içeren yüz maskesi.</p>
            </div>
          </a>
        </div>
      </div>
    </section>

    
EDKP0S3;
        $p0s4 = <<<'EDKP0S4'
<!-- CATEGORY GRID -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container">
        <div style="text-align:center;max-width:600px;margin:0 auto 48px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Ürün Kategorileri</p>
          <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">İhtiyacınız olan her şey, 14 kategoride</h2>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;max-width:940px;margin:0 auto;">
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">İp</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Kanül &amp; İğne Ucu</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Kimyasal Peeling</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Kozmetik</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Kremler</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Mezoterapi</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Mezoterapi Tabancası</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Micro İğneleme</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Otolog Rejeneratif Terapi</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Peeling</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Profesyonel Ürünler</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">RRS</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Terapi</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Yüz Maskesi</a>
        </div>
      </div>
    </section>

    
EDKP0S4;
        $p0s5 = <<<'EDKP0S5'
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
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Biz Kimiz?</p>
          <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 18px;line-height:1.18;">2004'ten beri medikal estetikte güvenin adresi</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.75;margin:0 0 18px;">Estetik Dermal, medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline dağıtıyor. Uluslararası markaların resmi temsilcisi olarak, yalnızca ürün değil; doktorlara uygulamalı eğitim ve teknik destek de sunuyoruz.</p>
          <ul style="list-style:none;margin:0 0 28px;padding:0;display:grid;gap:12px;">
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">✓</span> Uluslararası markaların resmi Türkiye temsilcisi</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">✓</span> CE Class III sertifikalı RRS serisi</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">✓</span> Doktorlara uygulamalı eğitim ve teknik destek</li>
          </ul>
          <a href="/klasik-hakkimizda" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#E8702A);color:#fff;padding:14px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">Hakkımızda →</a>
        </div>
      </div>
    </section>

    
EDKP0S5;
        $p0s6 = <<<'EDKP0S6'
<!-- TRAINING / SUPPORT -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container">
        <div style="text-align:center;max-width:620px;margin:0 auto 52px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Neden Estetik Dermal?</p>
          <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">Sadece ürün değil, uçtan uca destek</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;">
          <div style="background:var(--color-secondary,#FBF4EE);border-radius:var(--radius-card,18px);padding:34px 30px;">
            <div style="width:54px;height:54px;border-radius:14px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;margin-bottom:20px;color:var(--color-primary,#E8702A);"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div>
            <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">Uygulamalı Eğitim</h3>
            <p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">Hekimlere ürünlerin doğru ve güvenli kullanımı için birebir, uygulamalı eğitim programları.</p>
          </div>
          <div style="background:var(--color-secondary,#FBF4EE);border-radius:var(--radius-card,18px);padding:34px 30px;">
            <div style="width:54px;height:54px;border-radius:14px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;margin-bottom:20px;color:var(--color-primary,#E8702A);"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 14v-1a8 8 0 0 1 16 0v1"/><path d="M4 14a2 2 0 0 1 2-2h1v6H6a2 2 0 0 1-2-2Zm16 0a2 2 0 0 0-2-2h-1v6h1a2 2 0 0 0 2-2Z"/><path d="M18 18v.5a2.5 2.5 0 0 1-2.5 2.5H12"/></svg></div>
            <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">Teknik Destek</h3>
            <p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">Cihaz kurulumundan kullanım sürecine kadar kesintisiz teknik danışmanlık ve servis.</p>
          </div>
          <div style="background:var(--color-secondary,#FBF4EE);border-radius:var(--radius-card,18px);padding:34px 30px;">
            <div style="width:54px;height:54px;border-radius:14px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;margin-bottom:20px;color:var(--color-primary,#E8702A);"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 5 6v5c0 4 3 7 7 8 4-1 7-4 7-8V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg></div>
            <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">Orijinallik Garantisi</h3>
            <p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">Resmi temsilci güvencesiyle %100 orijinal, sertifikalı ve takip edilebilir ürünler.</p>
          </div>
        </div>
      </div>
    </section>

    
EDKP0S6;
        $p0s7 = <<<'EDKP0S7'
<!-- CTA -->

    <section style="padding:20px 0 84px;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:#262220;padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-70px;right:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.30),transparent 70%);"></div>
          <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.16),transparent 70%);"></div>
          <div style="position:relative;">
            <span style="display:inline-block;color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">Bize Ulaşın</span>
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.18;">Profesyonel çözümler için <span style="color:var(--color-accent,#F4A14E);">buradayız</span></h2>
            <p style="font-size:18px;color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 34px;line-height:1.65;">Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile Yaz</a>
              <a href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener" style="background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.32);padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">MEDINET Portalı ↗</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDKP0S7;
        $p1s0 = <<<'EDKP1S0'
<!-- PAGE HERO (compact başlık bandı) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(900px 480px at 85% -20%, rgba(244,161,78,.26), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);border-bottom:1px solid var(--border-soft,#ece6df);">
      <div class="container" style="padding:54px 0 60px;">
        <nav aria-label="Breadcrumb" style="margin:0 0 18px;">
          <ol style="list-style:none;display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:0;padding:0;font-size:14px;color:var(--text-soft,#6b6b6b);">
            <li><a href="/klasik" style="color:var(--text-soft,#6b6b6b);font-weight:600;">Ana Sayfa</a></li>
            <li aria-hidden="true" style="color:var(--border-soft,#ece6df);">/</li>
            <li aria-current="page" style="color:var(--color-primary,#E8702A);font-weight:700;">Hakkımızda</li>
          </ol>
        </nav>
        <h1 style="font-size:clamp(32px,5vw,52px);line-height:1.1;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 16px;">Hakkımızda</h1>
        <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:640px;margin:0;">2004'ten bu yana medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline ulaştıran resmi distribütör.</p>
      </div>
    </section>

    
EDKP1S0;
        $p1s1 = <<<'EDKP1S1'
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
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Biz Kimiz?</p>
          <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 18px;line-height:1.18;">2004'ten beri medikal estetikte güvenin adresi</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.75;margin:0 0 18px;">2004'ten bu yana medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline dağıtıyoruz. Uluslararası markaların resmi temsilcisi olarak yalnızca ürün değil, doktorlara uygulamalı eğitim ve teknik destek de sunuyoruz.</p>
          <ul style="list-style:none;margin:0;padding:0;display:grid;gap:12px;">
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">✓</span> Uluslararası markaların resmi Türkiye temsilcisi</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">✓</span> CE Class III sertifikalı RRS serisi</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">✓</span> Doktorlara birebir, uygulamalı eğitim</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="color:var(--color-primary,#E8702A);font-size:18px;">✓</span> Türkiye geneli, 81 ile dağıtım ağı</li>
          </ul>
        </div>
      </div>
    </section>

    
EDKP1S1;
        $p1s2 = <<<'EDKP1S2'
<!-- VİZYON & MİSYON (2 kart) -->

    <section style="padding:84px 0;background:var(--color-secondary,#FBF4EE);">
      <div class="container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;">
          <div style="background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:38px 34px;">
            <div style="width:56px;height:56px;border-radius:16px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;color:var(--color-primary,#E8702A);margin-bottom:20px;"><svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.2" fill="currentColor" stroke="none"/></svg></div>
            <h3 style="font-size:21px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 12px;">Vizyonumuz</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.75;margin:0;">Medikal estetikte kalite ve güvenin simgesi olarak sektördeki uzmanlık seviyesini yükseltmek.</p>
          </div>
          <div style="background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:38px 34px;">
            <div style="width:56px;height:56px;border-radius:16px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;color:var(--color-primary,#E8702A);margin-bottom:20px;"><svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 15c-1.5 1.3-2 5-2 5s3.7-.5 5-2a2.6 2.6 0 0 0-3-3Z"/><path d="M9 13c2-5 5-8 11-8 0 6-3 9-8 11"/><path d="M9 13l-3-1a14 14 0 0 1 3-3l3 .5"/><path d="M11 15l1 3a14 14 0 0 0 3-3l-.5-3"/></svg></div>
            <h3 style="font-size:21px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 12px;">Misyonumuz</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.75;margin:0;">Hastaların cilt sağlığını güçlendirecek profesyonel çözümler sağlamak ve invazif olmayan gençleştirme yöntemlerinde öncü rol oynamak.</p>
          </div>
        </div>
      </div>
    </section>

    
EDKP1S2;
        $p1s3 = <<<'EDKP1S3'
<!-- STATS BAR -->

    <section style="background:#fff;">
      <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:24px;padding:48px 0;border-bottom:1px solid var(--border-soft,#ece6df);">
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">20+</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">Yıllık Deneyim</div></div>
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">5+</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">Uluslararası Marka</div></div>
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">95+</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">Profesyonel Ürün</div></div>
        <div style="text-align:center;"><div style="font-size:clamp(30px,4vw,44px);font-weight:800;color:var(--color-primary,#E8702A);">81</div><div style="color:var(--text-soft,#6b6b6b);font-weight:600;">İl Dağıtım Ağı</div></div>
      </div>
    </section>

    
EDKP1S3;
        $p1s4 = <<<'EDKP1S4'
<!-- PORTFOLIO BRANDS -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container">
        <div style="text-align:center;max-width:640px;margin:0 auto 52px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Portföyümüz</p>
          <h2 style="font-size:clamp(28px,4vw,40px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 14px;line-height:1.15;">Temsil ettiğimiz uluslararası markalar</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.7;">Her biri kendi alanında uzman; mezoterapiden cihaza, peelingden ip askıya geniş bir ürün yelpazesi.</p>
        </div>
        <!-- 🖼️ GÖRSEL (opsiyonel marka logo rozetleri – temsili blok, her kartta harf-tile yerine):
             /assets/img/brand-{slug}.jpg (oran 1:1) — örn. brand-skintech.jpg, brand-mi-medical.jpg, brand-neogenesis.jpg, brand-seffiline.jpg, brand-woorhi.jpg, brand-aespio.jpg
             PROMPT: "Minimalist square brand emblem tile for a medical aesthetics brand, single bold monogram on a flat solid brand-color background, clean modern flat design, soft subtle gradient, professional pharma identity look, no text, no logo, no watermark, high resolution"
             DEĞİŞTİR → her kartın harf-tile div'i yerine: <img src="assets/img/brand-{slug}.jpg" alt="{MARKA ADI} logosu" style="width:52px;height:52px;object-fit:cover;border-radius:14px;"> -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:22px;">
          <a href="/klasik-marka-skintech" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #0C6E72;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#0C6E72;margin-bottom:10px;">İspanya</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">Skin Tech Pharma</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">Peeling, mezoterapi ve RRS skinbooster serisi.</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">Keşfet →</span>
          </a>
          <a href="/klasik-marka-mi-medical" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #C9A24B;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#B08A38;margin-bottom:10px;">Premium</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">Mi Medical Innovation</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">Premium mezoterapi ve enjeksiyon sistemleri.</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">Keşfet →</span>
          </a>
          <div style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #2BA39A;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#2BA39A;margin-bottom:10px;">Kök Hücre</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">Neogenesis</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">Kök hücre teknolojili profesyonel cilt bakım serisi.</p>
            <span style="color:var(--text-soft,#6b6b6b);font-weight:700;font-size:14px;">Portföyümüzde</span>
          </div>
          <a href="/klasik-marka-seffiline" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #C98A6D;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#C98A6D;margin-bottom:10px;">Cilt &amp; Saç</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">Seffiline</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">Cilt, saç, intim bakım ve dolgu çözümleri.</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">Keşfet →</span>
          </a>
          <a href="/klasik-marka-woorhi" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #2DA8FF;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#2480C8;margin-bottom:10px;">Güney Kore</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">Woorhi Mechatronics</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">Kore mühendisliği medikal estetik cihazları.</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">Keşfet →</span>
          </a>
          <a href="/klasik-marka-aespio" style="display:block;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #6C5CE0;border-radius:0 0 var(--radius-card,18px) var(--radius-card,18px);padding:28px 26px 26px;transition:transform .2s,box-shadow .2s;">
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#6C5CE0;margin-bottom:10px;">K-Beauty</div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 6px;">Grand Aespio</h3>
            <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0 0 14px;">Yüz maskeleri ve ip askı (thread) ürünleri.</p>
            <span style="color:var(--color-primary,#E8702A);font-weight:700;font-size:14px;">Keşfet →</span>
          </a>
        </div>
      </div>
    </section>

    
EDKP1S4;
        $p1s5 = <<<'EDKP1S5'
<!-- TRAINING / SUPPORT -->

    <section style="padding:84px 0;background:var(--color-secondary,#FBF4EE);">
      <div class="container">
        <div style="text-align:center;max-width:620px;margin:0 auto 52px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Neden Estetik Dermal?</p>
          <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">Sadece ürün değil, uçtan uca destek</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;">
          <div style="background:#fff;border-radius:var(--radius-card,18px);padding:34px 30px;border:1px solid var(--border-soft,#ece6df);">
            <div style="width:56px;height:56px;border-radius:16px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;color:var(--color-primary,#E8702A);margin-bottom:20px;"><svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div>
            <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">Uygulamalı Eğitim</h3>
            <p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">Hekimlere ürünlerin doğru ve güvenli kullanımı için birebir, uygulamalı eğitim programları.</p>
          </div>
          <div style="background:#fff;border-radius:var(--radius-card,18px);padding:34px 30px;border:1px solid var(--border-soft,#ece6df);">
            <div style="width:56px;height:56px;border-radius:16px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;color:var(--color-primary,#E8702A);margin-bottom:20px;"><svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 14v-1a8 8 0 0 1 16 0v1"/><path d="M4 14a2 2 0 0 1 2-2h1v6H6a2 2 0 0 1-2-2Zm16 0a2 2 0 0 0-2-2h-1v6h1a2 2 0 0 0 2-2Z"/><path d="M18 18v.5a2.5 2.5 0 0 1-2.5 2.5H12"/></svg></div>
            <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">Teknik Destek</h3>
            <p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">Cihaz kurulumundan kullanım sürecine kadar kesintisiz teknik danışmanlık ve servis.</p>
          </div>
          <div style="background:#fff;border-radius:var(--radius-card,18px);padding:34px 30px;border:1px solid var(--border-soft,#ece6df);">
            <div style="width:56px;height:56px;border-radius:16px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;color:var(--color-primary,#E8702A);margin-bottom:20px;"><svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 5 6v5c0 4 3 7 7 8 4-1 7-4 7-8V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg></div>
            <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">Orijinallik Garantisi</h3>
            <p style="color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0;">Resmi temsilci güvencesiyle %100 orijinal, sertifikalı ve takip edilebilir ürünler.</p>
          </div>
        </div>
      </div>
    </section>

    
EDKP1S5;
        $p1s6 = <<<'EDKP1S6'
<!-- CTA -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:#262220;padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-70px;right:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.30),transparent 70%);"></div>
          <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.16),transparent 70%);"></div>
          <div style="position:relative;">
            <span style="display:inline-block;color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">Bize Ulaşın</span>
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.18;">Profesyonel çözümler için <span style="color:var(--color-accent,#F4A14E);">buradayız</span></h2>
            <p style="font-size:18px;color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 34px;line-height:1.65;">Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile Yaz</a>
              <a href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener" style="background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.32);padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">MEDINET Portalı ↗</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDKP1S6;
        $p2s0 = <<<'EDKP2S0'
<!-- PAGE HERO (compact) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(900px 400px at 85% -20%, rgba(244,161,78,.26), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);">
      <div class="container" style="padding:48px 0 56px;">
        <nav aria-label="Breadcrumb" style="font-size:13.5px;color:var(--text-soft,#6b6b6b);margin:0 0 16px;">
          <a href="/klasik" style="color:var(--text-soft,#6b6b6b);font-weight:600;">Ana Sayfa</a>
          <span style="margin:0 8px;opacity:.6;">/</span>
          <span style="color:var(--color-primary,#E8702A);font-weight:700;">Ürünler</span>
        </nav>
        <h1 style="font-size:clamp(30px,4.5vw,46px);line-height:1.1;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 14px;">Ürünler</h1>
        <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:560px;margin:0;">95+ profesyonel medikal estetik ürünü, 14 kategoride.</p>
      </div>
    </section>

    
EDKP2S0;
        $p2s1 = <<<'EDKP2S1'
<!-- FILTER CHIPS (görsel, statik) -->

    <section style="background:#fff;border-bottom:1px solid var(--border-soft,#ece6df);">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:10px;padding:24px 0;">
        <a href="/klasik-urunler" style="display:inline-flex;align-items:center;background:var(--color-primary,#E8702A);color:#fff;border:1.5px solid var(--color-primary,#E8702A);border-radius:999px;padding:9px 20px;font-weight:700;font-size:14px;box-shadow:0 6px 18px rgba(232,112,42,.26);">Tümü</a>
        <a href="/klasik-urunler" style="display:inline-flex;align-items:center;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:9px 20px;font-weight:700;font-size:14px;">Skin Tech</a>
        <a href="/klasik-urunler" style="display:inline-flex;align-items:center;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:9px 20px;font-weight:700;font-size:14px;">Seffiline</a>
        <a href="/klasik-urunler" style="display:inline-flex;align-items:center;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:9px 20px;font-weight:700;font-size:14px;">Grand Aespio</a>
        <a href="/klasik-urunler" style="display:inline-flex;align-items:center;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:9px 20px;font-weight:700;font-size:14px;">Woorhi</a>
        <a href="/klasik-urunler" style="display:inline-flex;align-items:center;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:9px 20px;font-weight:700;font-size:14px;">Mi Medical</a>
      </div>
    </section>

    
EDKP2S1;
        $p2s2 = <<<'EDKP2S2'
<!-- CATEGORY GRID (14 kategori) -->

    <section style="padding:64px 0 56px;background:#fff;">
      <div class="container">
        <div style="text-align:center;max-width:600px;margin:0 auto 40px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Ürün Kategorileri</p>
          <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">İhtiyacınız olan her şey, 14 kategoride</h2>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;max-width:940px;margin:0 auto;">
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">İp</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Kanül &amp; İğne Ucu</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Kimyasal Peeling</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Kozmetik</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Kremler</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Mezoterapi</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Mezoterapi Tabancası</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Micro İğneleme</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Otolog Rejeneratif Terapi</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Peeling</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Profesyonel Ürünler</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">RRS</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Terapi</a>
          <a href="/klasik-urunler" class="cat-pill" style="background:#fff;border:1.5px solid var(--border-soft,#ece6df);border-radius:999px;padding:11px 22px;font-weight:600;font-size:15px;color:var(--text-main,#2a2a2a);">Yüz Maskesi</a>
        </div>
      </div>
    </section>

    
EDKP2S2;
        $p2s3 = <<<'EDKP2S3'
<!-- PRODUCT GRID -->

    <section style="padding:8px 0 72px;background:#fff;">
      <div class="container">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:36px;">
          <div>
            <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 10px;">Tüm Ürünler</p>
            <h2 style="font-size:clamp(26px,4vw,36px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">Profesyonel medikal estetik portföyü</h2>
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
          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-rrs-ha-long-lasting.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · RRS</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">RRS® HA Long Lasting</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Çapraz bağlı HA içeren CE Class III dermal implant.</p>
            </div>
          </a>

          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-melablock-hsp-spf-50.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · Krem</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Melablock HSP SPF 50+</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">360° güneş koruması.</p>
            </div>
          </a>

          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-benebellum-lumina-vit-c-18.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · Mezoterapi</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Benebellum LUMINA VİT-C 18%</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Yüksek konsantrasyonlu C vitamini.</p>
            </div>
          </a>

          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-benebellum-lumina-vit-a-e.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · Mezoterapi</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Benebellum LUMINA VİT. A+E</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">A ve E vitamini ile besleyici, antioksidan bakım.</p>
            </div>
          </a>

          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-benebellum-tx-solution.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · Mezoterapi</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Benebellum TX SOLUTION</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Traneksamik asit içeren leke karşıtı aydınlatıcı solüsyon.</p>
            </div>
          </a>

          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-aclaranse.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · Peeling</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Aclaranse</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Lekeli ciltler için aydınlatıcı profesyonel peeling çözümü.</p>
            </div>
          </a>

          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-actilift.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · İp</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Actilift</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Yüz ve boyunda anlık toparlama için ip askı sistemi.</p>
            </div>
          </a>

          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-atrofillin.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · Mezoterapi</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Atrofillin</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Atrofik izler ve cilt onarımı için mezoterapi solüsyonu.</p>
            </div>
          </a>

          <!-- Grand Aespio -->
          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-beta-glukan-mask.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#6C5CE0;text-transform:uppercase;letter-spacing:.6px;">Grand Aespio · Yüz Maskesi</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Beta-Glukan Mask</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Yatıştırıcı ve onarıcı beta-glukan içeren yüz maskesi.</p>
            </div>
          </a>

          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-hyaluronic-acid-mask.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#6C5CE0;text-transform:uppercase;letter-spacing:.6px;">Grand Aespio · Yüz Maskesi</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Hyaluronic Acid Mask</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Yoğun nem ve dolgunluk veren hyalüronik asit maskesi.</p>
            </div>
          </a>

          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-lfl-anchor.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#6C5CE0;text-transform:uppercase;letter-spacing:.6px;">Grand Aespio · İp</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">LFL Anchor</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Güçlü tutuş sağlayan çapalı askı (anchor) ip serisi.</p>
            </div>
          </a>

          <!-- Seffiline -->
          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-seffihair.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#C98A6D;text-transform:uppercase;letter-spacing:.6px;">Seffiline · Mezoterapi</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">SeffiHair</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Saç dökülmesine karşı saçlı deri mezoterapi solüsyonu.</p>
            </div>
          </a>

          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-sefficare.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#C98A6D;text-transform:uppercase;letter-spacing:.6px;">Seffiline · Kozmetik</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">SeffiCare</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Günlük cilt bakımı için kozmetik onarım serisi.</p>
            </div>
          </a>

          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-seffiller.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#C98A6D;text-transform:uppercase;letter-spacing:.6px;">Seffiline · Mezoterapi</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Seffiller</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Hacim ve dolgunluk için hyalüronik asit bazlı dolgu serisi.</p>
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
          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-raffine.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#2DA8FF;text-transform:uppercase;letter-spacing:.6px;">Woorhi · Mezoterapi Tabancası</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Raffine</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Kore mühendisliği cihaz.</p>
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
          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-pistor-eliance.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#C9A24B;text-transform:uppercase;letter-spacing:.6px;">Mi Medical · Mezoterapi Tabancası</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Pistor Eliance</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Premium enjeksiyon sistemi.</p>
            </div>
          </a>

        </div>
      </div>
    </section>

    
EDKP2S3;
        $p2s4 = <<<'EDKP2S4'
<!-- BİLGİ ŞERİDİ -->

    <section style="padding:0 0 56px;background:#fff;">
      <div class="container">
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:26px 30px;">
          <div style="flex:1 1 380px;display:flex;align-items:center;gap:16px;">
            <span style="width:46px;height:46px;flex-shrink:0;border-radius:12px;background:var(--primary-soft,#FCE9DC);display:grid;place-items:center;color:var(--color-primary,#E8702A);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 8-9-5-9 5v8l9 5 9-5V8Z"/><path d="m3 8 9 5 9-5"/><path d="M12 13v8"/></svg></span>
            <p style="margin:0;color:var(--text-main,#2a2a2a);font-size:16px;line-height:1.6;font-weight:600;">Toplam 95+ ürün. Fiyat ve sipariş için WhatsApp: <strong style="color:var(--color-primary,#E8702A);">+90 542 620 51 00</strong></p>
          </div>
          <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="flex-shrink:0;display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:14px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp'tan Yaz</a>
        </div>
      </div>
    </section>

    
EDKP2S4;
        $p2s5 = <<<'EDKP2S5'
<!-- CTA -->

    <section style="padding:20px 0 84px;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:#262220;padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-70px;right:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.30),transparent 70%);"></div>
          <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.16),transparent 70%);"></div>
          <div style="position:relative;">
            <span style="display:inline-block;color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">Bize Ulaşın</span>
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.18;">Profesyonel çözümler için <span style="color:var(--color-accent,#F4A14E);">buradayız</span></h2>
            <p style="font-size:18px;color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 34px;line-height:1.65;">Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile Yaz</a>
              <a href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener" style="background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.32);padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">MEDINET Portalı ↗</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDKP2S5;
        $p3s0 = <<<'EDKP3S0'
<!-- PAGE HERO (compact) + BREADCRUMB -->

    <section style="background:radial-gradient(900px 460px at 85% -20%, rgba(244,161,78,.24), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);border-bottom:1px solid var(--border-soft,#ece6df);">
      <div class="container" style="padding:34px 0 30px;">
        <nav aria-label="Breadcrumb" style="font-size:14px;color:var(--text-soft,#6b6b6b);display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
          <a href="/klasik" style="color:var(--text-soft,#6b6b6b);font-weight:600;">Ana Sayfa</a>
          <span aria-hidden="true" style="opacity:.5;">/</span>
          <a href="/klasik-urunler" style="color:var(--text-soft,#6b6b6b);font-weight:600;">Ürünler</a>
          <span aria-hidden="true" style="opacity:.5;">/</span>
          <span aria-current="page" style="color:var(--color-primary,#E8702A);font-weight:700;">RRS® HA Long Lasting</span>
        </nav>
      </div>
    </section>

    
EDKP3S0;
        $p3s1 = <<<'EDKP3S1'
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
          <span style="display:inline-block;font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:1px;background:rgba(12,110,114,.08);padding:6px 12px;border-radius:999px;">SKIN TECH · RRS</span>
          <h1 style="font-size:clamp(28px,4vw,42px);line-height:1.12;font-weight:800;color:var(--text-main,#2a2a2a);margin:16px 0 14px;">RRS® HA Long Lasting</h1>
          <p style="font-size:17px;color:var(--text-soft,#6b6b6b);line-height:1.7;margin:0 0 26px;max-width:520px;">Çapraz bağlı, emilebilir Hyalüronik asit içeren dermal implant.</p>

          <ul style="list-style:none;margin:0 0 30px;padding:0;display:grid;gap:13px;">
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;font-size:15.5px;"><span style="color:var(--color-primary,#E8702A);font-size:18px;flex-shrink:0;">✓</span> CE Class III sertifikalı</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;font-size:15.5px;"><span style="color:var(--color-primary,#E8702A);font-size:18px;flex-shrink:0;">✓</span> Steril tıbbi enjektör formu</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;font-size:15.5px;"><span style="color:var(--color-primary,#E8702A);font-size:18px;flex-shrink:0;">✓</span> Amino asit içeren koruyucu tampon solüsyonu</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;font-size:15.5px;"><span style="color:var(--color-primary,#E8702A);font-size:18px;flex-shrink:0;">✓</span> Uzun etkili (long lasting)</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#2a2a2a);font-weight:600;font-size:15.5px;"><span style="color:var(--color-primary,#E8702A);font-size:18px;flex-shrink:0;">✓</span> Cilt gençleştirme &amp; nemlendirme</li>
          </ul>

          <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile Sipariş</a>
            <a href="/klasik-iletisim" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);padding:15px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">Teklif İste</a>
          </div>

          <p style="margin:22px 0 0;font-size:13.5px;color:var(--text-soft,#6b6b6b);display:flex;align-items:center;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary,#E8702A)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;"><path d="M12 3 5 6v5c0 4 3 7 7 8 4-1 7-4 7-8V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg> Yalnızca hekim/klinik kullanımına yöneliktir.</p>
        </div>
      </div>
    </section>

    
EDKP3S1;
        $p3s2 = <<<'EDKP3S2'
<!-- PRODUCT DESCRIPTION (rich-text) -->

    <section style="padding:64px 0;background:var(--color-secondary,#FBF4EE);">
      <div class="container" style="max-width:860px;">
        <div style="margin-bottom:42px;">
          <h2 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 16px;line-height:1.2;">Ürün Açıklaması</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.8;margin:0 0 14px;">RRS® HA Long Lasting, çapraz bağlı (cross-linked) ve tamamen emilebilir hyalüronik asit içeren steril bir dermal implanttır. Kullanıma hazır tıbbi enjektör formunda sunulan ürün, amino asit içeren koruyucu bir tampon solüsyonunda çözülerek dokuyla yüksek biyouyum sağlar.</p>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.8;margin:0;">Cildin derin katmanlarına uygulanan formül, dermisi içten nemlendirir, su tutma kapasitesini artırır ve doku kalitesini iyileştirir. Çapraz bağlı yapısı sayesinde etkisini daha uzun süre koruyarak (long lasting), tek seansta belirgin tazelenme ve canlanma sağlar.</p>
        </div>

        <div style="margin-bottom:42px;">
          <h2 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 16px;line-height:1.2;">Kullanım Alanları</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:17px;line-height:1.8;margin:0 0 16px;">Cilt gençleştirme ve skinbooster protokollerinde, dokunun nem dengesini ve elastikiyetini desteklemek amacıyla aşağıdaki bölgelerde uygulanabilir:</p>
          <ul style="list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
            <li style="display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:14px;padding:14px 18px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="width:7px;height:7px;border-radius:50%;background:var(--color-primary,#E8702A);flex-shrink:0;"></span> Yüz</li>
            <li style="display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:14px;padding:14px 18px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="width:7px;height:7px;border-radius:50%;background:var(--color-primary,#E8702A);flex-shrink:0;"></span> Boyun</li>
            <li style="display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:14px;padding:14px 18px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="width:7px;height:7px;border-radius:50%;background:var(--color-primary,#E8702A);flex-shrink:0;"></span> Dekolte</li>
            <li style="display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:14px;padding:14px 18px;color:var(--text-main,#2a2a2a);font-weight:600;"><span style="width:7px;height:7px;border-radius:50%;background:var(--color-primary,#E8702A);flex-shrink:0;"></span> El sırtı</li>
          </ul>
        </div>

        <div>
          <h2 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 16px;line-height:1.2;">İçerik &amp; Özellikler</h2>
          <ul style="list-style:none;margin:0;padding:0;display:grid;gap:12px;">
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;"><span style="color:#0C6E72;font-weight:800;flex-shrink:0;">•</span><span><strong style="color:var(--text-main,#2a2a2a);">Çapraz bağlı hyalüronik asit:</strong> emilebilir, uzun etkili dermal implant yapısı.</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;"><span style="color:#0C6E72;font-weight:800;flex-shrink:0;">•</span><span><strong style="color:var(--text-main,#2a2a2a);">Koruyucu tampon solüsyonu:</strong> amino asit içeren, dengeli çözücü ortam.</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;"><span style="color:#0C6E72;font-weight:800;flex-shrink:0;">•</span><span><strong style="color:var(--text-main,#2a2a2a);">Steril enjektör formu:</strong> kullanıma hazır, tek kullanımlık tıbbi sunum.</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;"><span style="color:#0C6E72;font-weight:800;flex-shrink:0;">•</span><span><strong style="color:var(--text-main,#2a2a2a);">Sertifikasyon:</strong> CE Class III tıbbi cihaz onayı.</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;"><span style="color:#0C6E72;font-weight:800;flex-shrink:0;">•</span><span><strong style="color:var(--text-main,#2a2a2a);">Endikasyon:</strong> cilt gençleştirme, nemlendirme ve doku kalitesi iyileştirme.</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;"><span style="color:#0C6E72;font-weight:800;flex-shrink:0;">•</span><span><strong style="color:var(--text-main,#2a2a2a);">Üretici:</strong> Skin Tech Pharma Group.</span></li>
          </ul>
        </div>
      </div>
    </section>

    
EDKP3S2;
        $p3s3 = <<<'EDKP3S3'
<!-- RELATED PRODUCTS -->

    <section style="padding:84px 0;background:#fff;">
      <div class="container">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:44px;">
          <div>
            <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 10px;">Benzer Ürünler</p>
            <h2 style="font-size:clamp(26px,4vw,38px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0;line-height:1.15;">İlginizi çekebilecek diğer ürünler</h2>
          </div>
          <a href="/klasik-urunler" style="color:var(--color-primary,#E8702A);font-weight:700;font-size:15px;white-space:nowrap;">Tüm ürünler →</a>
        </div>
        <!-- ════════════════════════════════════════════════════════════════════
             🖼️ BENZER ÜRÜN KART GÖRSELLERİ (her kart için tekrarlanır, oran 4:3)
             Aşağıdaki her kartın üstündeki gradyan+emoji kutusu bir görsel
             placeholder'dır. İsimlendirme: /assets/img/product-{slug}.jpg
             (örn. product-melablock-hsp-spf50.jpg, product-benebellum-lumina-vitc.jpg,
              product-atrofillin.jpg) — /klasik-urunler ile aynı görseller tekrar kullanılır.
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
          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-melablock-hsp-spf-50.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · Krem</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Melablock HSP SPF 50+</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktör.</p>
            </div>
          </a>
          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-benebellum-lumina-vit-c-18.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · Mezoterapi</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Benebellum LUMINA VİT-C 18%</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Yüksek konsantrasyonlu C vitamini ile aydınlatıcı bakım.</p>
            </div>
          </a>
          <a href="/klasik-urun-detay" style="display:block;background:#fff;border-radius:var(--radius-card,18px);overflow:hidden;border:1px solid var(--border-soft,#ece6df);transition:transform .2s,box-shadow .2s;">
            <div style="aspect-ratio:4/3;background:var(--color-secondary,#FBF4EE) url('assets/img/product-atrofillin.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
            <div style="padding:22px;">
              <span style="font-size:12px;font-weight:700;color:#0C6E72;text-transform:uppercase;letter-spacing:.6px;">Skin Tech · RRS</span>
              <h3 style="font-size:18px;font-weight:800;color:var(--text-main,#2a2a2a);margin:6px 0 8px;">Atrofillin</h3>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14px;line-height:1.6;margin:0;">Atrofik ve yıpranmış cilt için yenileyici dermal enjeksiyon çözümü.</p>
            </div>
          </a>
        </div>
      </div>
    </section>

    
EDKP3S3;
        $p3s4 = <<<'EDKP3S4'
<!-- CTA (full) -->

    <section style="padding:20px 0 84px;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:#262220;padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-70px;right:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.30),transparent 70%);"></div>
          <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.16),transparent 70%);"></div>
          <div style="position:relative;">
            <span style="display:inline-block;color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">Bize Ulaşın</span>
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.18;">Profesyonel çözümler için <span style="color:var(--color-accent,#F4A14E);">buradayız</span></h2>
            <p style="font-size:18px;color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 34px;line-height:1.65;">Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile Yaz</a>
              <a href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener" style="background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.32);padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">MEDINET Portalı ↗</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDKP3S4;
        $p4s0 = <<<'EDKP4S0'
<!-- PAGE HERO (compact) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(900px 460px at 85% -20%, rgba(244,161,78,.26), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);">
      <div class="container" style="padding:56px 0 60px;">
        <nav aria-label="Breadcrumb" style="margin:0 0 18px;">
          <ol style="list-style:none;display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:0;padding:0;font-size:14px;color:var(--text-soft,#6b6b6b);">
            <li><a href="/klasik" style="color:var(--text-soft,#6b6b6b);font-weight:600;">Ana Sayfa</a></li>
            <li aria-hidden="true" style="color:var(--border-soft,#ece6df);">/</li>
            <li aria-current="page" style="color:var(--color-primary,#E8702A);font-weight:700;">Markalar</li>
          </ol>
        </nav>
        <h1 style="font-size:clamp(30px,4.6vw,48px);line-height:1.1;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 16px;max-width:760px;">Temsil Ettiğimiz Markalar</h1>
        <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:640px;margin:0;">Her biri kendi alanında uzman, uluslararası 5 marka — Türkiye'de resmi temsilcisi Estetik Dermal.</p>
      </div>
    </section>

    
EDKP4S0;
        $p4s1 = <<<'EDKP4S1'
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
            <span style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#0C6E72;">İspanya · Amiral Marka</span>
            <span style="flex-shrink:0;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);color:var(--text-soft,#6b6b6b);font-size:12px;font-weight:700;padding:5px 12px;border-radius:999px;">84+ ürün</span>
          </div>
          <h2 style="font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">Skin Tech Pharma Group</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:15px;line-height:1.7;margin:0 0 22px;">Kimyasal peeling, mezoterapi ve RRS skinbooster serisinde dünya lideri.</p>
          <a href="/klasik-marka-skintech" style="margin-top:auto;align-self:flex-start;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">Markayı Keşfet →</a>
        
          </div>
        </article>

        <!-- Seffiline -->
        <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #C98A6D;border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
          <div style="aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/brand-panel-seffiline.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
          <div style="display:flex;flex-direction:column;flex:1;padding:26px 30px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <span style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#a5694f;">Bakım &amp; Dolgu Serisi</span>
            <span style="flex-shrink:0;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);color:var(--text-soft,#6b6b6b);font-size:12px;font-weight:700;padding:5px 12px;border-radius:999px;">4 ürün</span>
          </div>
          <h2 style="font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">Seffiline</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:15px;line-height:1.7;margin:0 0 22px;">Cilt, saç, intim bakım ve dolgu çözümleri serisi.</p>
          <a href="/klasik-marka-seffiline" style="margin-top:auto;align-self:flex-start;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">Markayı Keşfet →</a>
        
          </div>
        </article>

        <!-- Grand Aespio -->
        <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #6C5CE0;border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
          <div style="aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/brand-panel-aespio.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
          <div style="display:flex;flex-direction:column;flex:1;padding:26px 30px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <span style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#6C5CE0;">K-Beauty · Thread Lift</span>
            <span style="flex-shrink:0;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);color:var(--text-soft,#6b6b6b);font-size:12px;font-weight:700;padding:5px 12px;border-radius:999px;">5 ürün</span>
          </div>
          <h2 style="font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">Grand Aespio</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:15px;line-height:1.7;margin:0 0 22px;">Yüz maskeleri ve ip askı (thread lift) ürünleri. Modern K-beauty yaklaşımı.</p>
          <a href="/klasik-marka-aespio" style="margin-top:auto;align-self:flex-start;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">Markayı Keşfet →</a>
        
          </div>
        </article>

        <!-- Woorhi Mechatronics Co. Ltd. -->
        <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #2DA8FF;border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
          <div style="aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/brand-panel-woorhi.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
          <div style="display:flex;flex-direction:column;flex:1;padding:26px 30px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <span style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#1486d6;">Güney Kore · Mekatronik</span>
            <span style="flex-shrink:0;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);color:var(--text-soft,#6b6b6b);font-size:12px;font-weight:700;padding:5px 12px;border-radius:999px;">Cihaz</span>
          </div>
          <h2 style="font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">Woorhi Mechatronics Co. Ltd.</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:15px;line-height:1.7;margin:0 0 22px;">Güney Kore · Medikal estetik cihaz ve mekatronik mühendisliği.</p>
          <a href="/klasik-marka-woorhi" style="margin-top:auto;align-self:flex-start;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">Markayı Keşfet →</a>
        
          </div>
        </article>

        <!-- Mi Medical Innovation -->
        <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-top:3px solid #C9A24B;border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
          <div style="aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/brand-panel-mi-medical.jpg') center/cover no-repeat;border-bottom:1px solid var(--border-soft,#ece6df);"></div>
          <div style="display:flex;flex-direction:column;flex:1;padding:26px 30px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <span style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#a4812f;">Enjeksiyon Sistemleri</span>
            <span style="flex-shrink:0;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);color:var(--text-soft,#6b6b6b);font-size:12px;font-weight:700;padding:5px 12px;border-radius:999px;">Premium</span>
          </div>
          <h2 style="font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;">Mi Medical Innovation</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:15px;line-height:1.7;margin:0 0 22px;">Premium mezoterapi ve enjeksiyon sistemleri.</p>
          <a href="/klasik-marka-mi-medical" style="margin-top:auto;align-self:flex-start;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">Markayı Keşfet →</a>
        
          </div>
        </article>

      </div>
    </section>

    
EDKP4S1;
        $p4s2 = <<<'EDKP4S2'
<!-- KISA BANT -->

    <section style="background:var(--color-secondary,#FBF4EE);">
      <div class="container" style="padding:34px 0;">
        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;justify-content:center;text-align:center;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:24px 30px;">
          <span style="flex-shrink:0;color:var(--color-primary,#E8702A);"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="9" r="6"/><path d="m9 14.5-2 7 5-3 5 3-2-7"/><path d="m12 6 1 2 2 .3-1.5 1.5.4 2L12 11l-1.9 1 .4-2L9 8.3 11 8Z"/></svg></span>
          <p style="color:var(--text-main,#2a2a2a);font-size:16px;font-weight:600;line-height:1.6;margin:0;">Portföyümüzde ayrıca <strong style="color:var(--color-primary,#E8702A);">Neogenesis</strong> ve <strong style="color:var(--color-primary,#E8702A);">CE Class III sertifikalı RRS serisi</strong> yer alır.</p>
        </div>
      </div>
    </section>

    
EDKP4S2;
        $p4s3 = <<<'EDKP4S3'
<!-- CTA -->

    <section style="padding:64px 0 84px;background:var(--color-secondary,#FBF4EE);">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:#262220;padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-70px;right:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.30),transparent 70%);"></div>
          <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.16),transparent 70%);"></div>
          <div style="position:relative;">
            <span style="display:inline-block;color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">Bize Ulaşın</span>
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.18;">Profesyonel çözümler için <span style="color:var(--color-accent,#F4A14E);">buradayız</span></h2>
            <p style="font-size:18px;color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 34px;line-height:1.65;">Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile Yaz</a>
              <a href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener" style="background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.32);padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">MEDINET Portalı ↗</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDKP4S3;
        $p5s0 = <<<'EDKP5S0'
<!-- PAGE HERO (compact) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(1000px 500px at 85% -20%, rgba(244,161,78,.26), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);">
      <div class="container" style="padding:54px 0 60px;">
        <nav aria-label="Sayfa konumu" style="font-size:13.5px;color:var(--text-soft,#6b6b6b);margin:0 0 20px;">
          <a href="/klasik" style="color:var(--text-soft,#6b6b6b);font-weight:600;">Ana Sayfa</a>
          <span style="margin:0 8px;color:var(--border-soft,#ece6df);">/</span>
          <span style="color:var(--color-primary,#E8702A);font-weight:700;">Etkinlikler</span>
        </nav>
        <p style="display:inline-flex;align-items:center;gap:8px;background:var(--primary-soft,#FCE9DC);color:var(--primary-deep,#C85716);border-radius:999px;padding:7px 15px;font-size:13px;font-weight:700;letter-spacing:.4px;margin:0 0 18px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/></svg>Kongre &amp; Etkinlikler</p>
        <h1 style="font-size:clamp(30px,4.6vw,48px);line-height:1.1;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 16px;max-width:760px;">Kongre & Etkinlikler</h1>
        <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:640px;margin:0;">Estetik Dermal olarak yer aldığımız ulusal ve uluslararası kongreler, fuarlar ve eğitim etkinlikleri.</p>
      </div>
    </section>

    
EDKP5S0;
        $p5s1 = <<<'EDKP5S1'
<!-- EVENT GRID -->

    <section style="padding:72px 0 40px;background:#fff;">
      <div class="container">
        <p style="display:inline-flex;align-items:center;gap:9px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);color:var(--text-soft,#6b6b6b);border-radius:12px;padding:10px 16px;font-size:13.5px;line-height:1.5;margin:0 0 36px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/></svg>Aşağıdaki etkinlikler temsili örneklerdir; gerçek tarihler ve katılım bilgileri yakında güncellenecektir.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px;">

          <!-- Kart 1 -->
          <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
            <!-- 🖼️ GÖRSEL: /assets/img/event-1.jpg (oran 16:10)
                 PROMPT: "Wide shot of a large international aesthetic dermatology congress in Paris, busy modern convention hall with exhibition booths and professional attendees, bright clean atmosphere, warm cream and terracotta accent tones, photorealistic event photography, no text, no logo, no watermark, high resolution"
                 DEĞİŞTİR → <img src="assets/img/event-1.jpg" alt="IMCAS World Congress 2026 kongre salonu" style="width:100%;height:100%;object-fit:cover;"> (tarih rozetini koru) -->
            <div style="position:relative;aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/event-1.jpg') center/cover no-repeat;">
              <span style="position:absolute;top:14px;right:14px;background:#fff;border-radius:14px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:8px 12px;text-align:center;line-height:1;">
                <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#E8702A);">Oca</span>
                <span style="display:block;font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);">29</span>
              </span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;flex:1;">
              <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;line-height:1.25;">IMCAS World Congress 2026</h3>
              <p style="display:flex;align-items:center;gap:7px;color:var(--text-soft,#6b6b6b);font-size:14px;font-weight:600;margin:0 0 12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>Paris, Fransa</p>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14.5px;line-height:1.65;margin:0 0 18px;flex:1;">Dünyanın en kapsamlı estetik dermatoloji kongresinde son teknoloji ürün ve uygulamalarla yer alıyoruz.</p>
              <a href="#" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">Detay →</a>
            </div>
          </article>

          <!-- Kart 2 -->
          <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
            <!-- 🖼️ GÖRSEL: /assets/img/event-2.jpg (oran 16:10)
                 PROMPT: "Modern anti-aging and aesthetic medicine conference in Istanbul, professional stage with speaker and audience, sleek exhibition stand showing skincare devices, elegant warm lighting with cream and amber tones, photorealistic event photography, no text, no logo, no watermark, high resolution"
                 DEĞİŞTİR → <img src="assets/img/event-2.jpg" alt="Anti-Aging & Estetik Kongresi sahne ve katılımcılar" style="width:100%;height:100%;object-fit:cover;"> (tarih rozetini koru) -->
            <div style="position:relative;aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/event-2.jpg') center/cover no-repeat;">
              <span style="position:absolute;top:14px;right:14px;background:#fff;border-radius:14px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:8px 12px;text-align:center;line-height:1;">
                <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#E8702A);">Mar</span>
                <span style="display:block;font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);">14</span>
              </span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;flex:1;">
              <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;line-height:1.25;">Anti-Aging & Estetik Kongresi</h3>
              <p style="display:flex;align-items:center;gap:7px;color:var(--text-soft,#6b6b6b);font-size:14px;font-weight:600;margin:0 0 12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>İstanbul</p>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14.5px;line-height:1.65;margin:0 0 18px;flex:1;">Yaşlanma karşıtı uygulamalarda güncel protokoller; standımızda ürün ve cihaz demoları.</p>
              <a href="#" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">Detay →</a>
            </div>
          </article>

          <!-- Kart 3 -->
          <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
            <!-- 🖼️ GÖRSEL: /assets/img/event-3.jpg (oran 16:10)
                 PROMPT: "Dermatology and cosmetology scientific days in Antalya, interactive hands-on workshop with doctors practicing mesotherapy and peeling techniques on training models, bright clinical training room, warm cream and terracotta accents, photorealistic event photography, no text, no logo, no watermark, high resolution"
                 DEĞİŞTİR → <img src="assets/img/event-3.jpg" alt="Dermatoloji & Kozmetoloji Günleri uygulama atölyesi" style="width:100%;height:100%;object-fit:cover;"> (tarih rozetini koru) -->
            <div style="position:relative;aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/event-3.jpg') center/cover no-repeat;">
              <span style="position:absolute;top:14px;right:14px;background:#fff;border-radius:14px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:8px 12px;text-align:center;line-height:1;">
                <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#E8702A);">May</span>
                <span style="display:block;font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);">22</span>
              </span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;flex:1;">
              <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;line-height:1.25;">Dermatoloji & Kozmetoloji Günleri</h3>
              <p style="display:flex;align-items:center;gap:7px;color:var(--text-soft,#6b6b6b);font-size:14px;font-weight:600;margin:0 0 12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>Antalya</p>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14.5px;line-height:1.65;margin:0 0 18px;flex:1;">Mezoterapi ve peeling odaklı bilimsel oturumlar ile interaktif uygulama atölyeleri.</p>
              <a href="#" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">Detay →</a>
            </div>
          </article>

          <!-- Kart 4 -->
          <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
            <!-- 🖼️ GÖRSEL: /assets/img/event-4.jpg (oran 16:10)
                 PROMPT: "Intimate hands-on medical training workshop in Kusadasi, an instructor demonstrating RRS skinbooster and chemical peeling application to a small group of doctors around a treatment table, warm cream and terracotta clinical interior, photorealistic event photography, no text, no logo, no watermark, high resolution"
                 DEĞİŞTİR → <img src="assets/img/event-4.jpg" alt="Skin Tech uygulamalı eğitim workshop'u" style="width:100%;height:100%;object-fit:cover;"> (tarih rozetini koru) -->
            <div style="position:relative;aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/event-4.jpg') center/cover no-repeat;">
              <span style="position:absolute;top:14px;right:14px;background:#fff;border-radius:14px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:8px 12px;text-align:center;line-height:1;">
                <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#E8702A);">Haz</span>
                <span style="display:block;font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);">18</span>
              </span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;flex:1;">
              <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;line-height:1.25;">Skin Tech Uygulamalı Eğitim Workshop</h3>
              <p style="display:flex;align-items:center;gap:7px;color:var(--text-soft,#6b6b6b);font-size:14px;font-weight:600;margin:0 0 12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>Kuşadası, Aydın</p>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14.5px;line-height:1.65;margin:0 0 18px;flex:1;">Hekimlere yönelik birebir, uygulamalı RRS ve peeling eğitimi; sınırlı kontenjanlı atölye.</p>
              <a href="#" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">Detay →</a>
            </div>
          </article>

          <!-- Kart 5 -->
          <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
            <!-- 🖼️ GÖRSEL: /assets/img/event-5.jpg (oran 16:10)
                 PROMPT: "Regional face aesthetics conference in Izmir, a speaker presenting thread lift and dermal filler innovations on a large screen to an engaged professional audience, modern auditorium with warm amber and cream lighting, photorealistic event photography, no text, no logo, no watermark, high resolution"
                 DEĞİŞTİR → <img src="assets/img/event-5.jpg" alt="FACE Aesthetic Conference sunumu" style="width:100%;height:100%;object-fit:cover;"> (tarih rozetini koru) -->
            <div style="position:relative;aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/event-5.jpg') center/cover no-repeat;">
              <span style="position:absolute;top:14px;right:14px;background:#fff;border-radius:14px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:8px 12px;text-align:center;line-height:1;">
                <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#E8702A);">Eyl</span>
                <span style="display:block;font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);">26</span>
              </span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;flex:1;">
              <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;line-height:1.25;">FACE Aesthetic Conference</h3>
              <p style="display:flex;align-items:center;gap:7px;color:var(--text-soft,#6b6b6b);font-size:14px;font-weight:600;margin:0 0 12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>İzmir</p>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14.5px;line-height:1.65;margin:0 0 18px;flex:1;">Yüz estetiğinde ip askı ve dolgu yeniliklerinin paylaşıldığı bölgesel uzman buluşması.</p>
              <a href="#" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">Detay →</a>
            </div>
          </article>

          <!-- Kart 6 -->
          <article style="display:flex;flex-direction:column;background:#fff;border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);overflow:hidden;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));">
            <!-- 🖼️ GÖRSEL: /assets/img/event-6.jpg (oran 16:10)
                 PROMPT: "Medical aesthetics trade fair in Ankara, a well-designed branded exhibition booth displaying skincare products and aesthetic devices, visitors browsing, bright exhibition hall, warm cream and terracotta brand accents, photorealistic event photography, no text, no logo, no watermark, high resolution"
                 DEĞİŞTİR → <img src="assets/img/event-6.jpg" alt="Medikal Estetik Fuarı stant alanı" style="width:100%;height:100%;object-fit:cover;"> (tarih rozetini koru) -->
            <div style="position:relative;aspect-ratio:16/9;background:var(--color-secondary,#FBF4EE) url('assets/img/event-6.jpg') center/cover no-repeat;">
              <span style="position:absolute;top:14px;right:14px;background:#fff;border-radius:14px;box-shadow:var(--shadow-soft,0 4px 18px rgba(58,58,58,.07));padding:8px 12px;text-align:center;line-height:1;">
                <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#E8702A);">Kas</span>
                <span style="display:block;font-size:22px;font-weight:800;color:var(--text-main,#2a2a2a);">12</span>
              </span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;flex:1;">
              <h3 style="font-size:19px;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 8px;line-height:1.25;">Medikal Estetik Fuarı</h3>
              <p style="display:flex;align-items:center;gap:7px;color:var(--text-soft,#6b6b6b);font-size:14px;font-weight:600;margin:0 0 12px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:var(--color-primary,#E8702A);"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>Ankara</p>
              <p style="color:var(--text-soft,#6b6b6b);font-size:14.5px;line-height:1.65;margin:0 0 18px;flex:1;">Temsil ettiğimiz tüm markaların ürün ve cihazlarını yakından inceleyebileceğiniz fuar standı.</p>
              <a href="#" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary,#E8702A);font-weight:700;font-size:14.5px;">Detay →</a>
            </div>
          </article>

        </div>

        <p style="margin:30px 0 0;font-size:12.5px;color:var(--text-soft,#6b6b6b);line-height:1.6;font-style:italic;">* Bu sayfadaki etkinlikler temsili örnek amaçlıdır. Kesin tarihler, mekânlar ve katılım koşulları onaylandıkça güncellenecektir.</p>
      </div>
    </section>

    
EDKP5S1;
        $p5s2 = <<<'EDKP5S2'
<!-- INFO BLOCK -->

    <section style="padding:40px 0 84px;background:#fff;">
      <div class="container">
        <div style="display:flex;flex-wrap:wrap;gap:32px;align-items:center;justify-content:space-between;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:clamp(28px,4vw,44px);">
          <div style="flex:1 1 380px;">
            <h2 style="font-size:clamp(22px,3vw,30px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;line-height:1.2;">Etkinlik takvimi ve katılım için bizimle iletişime geçin</h2>
            <p style="color:var(--text-soft,#6b6b6b);font-size:16px;line-height:1.7;margin:0;">Yaklaşan kongreler, fuar standlarımız ve uygulamalı eğitim atölyelerimize katılım hakkında güncel bilgi almak için ekibimize ulaşın.</p>
          </div>
          <div style="display:flex;gap:14px;flex-wrap:wrap;flex:0 1 auto;">
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile Yaz</a>
            <a href="/klasik-iletisim" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--text-main,#2a2a2a);border:1.5px solid var(--border-soft,#ece6df);padding:15px 26px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">İletişim →</a>
          </div>
        </div>
      </div>
    </section>

    
EDKP5S2;
        $p5s3 = <<<'EDKP5S3'
<!-- CTA (full) -->

    <section style="padding:20px 0 84px;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:#262220;padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-70px;right:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.30),transparent 70%);"></div>
          <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,112,42,.16),transparent 70%);"></div>
          <div style="position:relative;">
            <span style="display:inline-block;color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">Bize Ulaşın</span>
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.18;">Profesyonel çözümler için <span style="color:var(--color-accent,#F4A14E);">buradayız</span></h2>
            <p style="font-size:18px;color:rgba(255,255,255,.72);max-width:600px;margin:0 auto 34px;line-height:1.65;">Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 8px 24px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile Yaz</a>
              <a href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener" style="background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.32);padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">MEDINET Portalı ↗</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDKP5S3;
        $p6s0 = <<<'EDKP6S0'
<!-- PAGE HERO (compact) -->

    <section style="position:relative;overflow:hidden;background:radial-gradient(1000px 460px at 85% -20%, rgba(244,161,78,.26), transparent 60%), linear-gradient(180deg,#FFFFFF 0%, var(--color-secondary,#FBF4EE) 100%);">
      <div class="container" style="padding:54px 0 58px;">
        <nav aria-label="Breadcrumb" style="margin:0 0 18px;font-size:14px;color:var(--text-soft,#6b6b6b);">
          <a href="/klasik" style="color:var(--text-soft,#6b6b6b);font-weight:600;">Ana Sayfa</a>
          <span style="margin:0 8px;color:var(--border-soft,#ece6df);">/</span>
          <span style="color:var(--color-primary,#E8702A);font-weight:700;">İletişim</span>
        </nav>
        <h1 style="font-size:clamp(32px,5vw,52px);line-height:1.1;font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 14px;">İletişim</h1>
        <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#6b6b6b);line-height:1.7;max-width:560px;margin:0;">Ürün, fiyat ve eğitim talepleriniz için bize ulaşın.</p>
      </div>
    </section>

    
EDKP6S0;
        $p6s1 = <<<'EDKP6S1'
<!-- CONTACT SPLIT -->

    <section style="padding:72px 0;background:#fff;">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:40px;align-items:stretch;">

        <!-- SOL: iletişim bilgi kartları -->
        <div style="flex:1 1 380px;">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Bize Ulaşın</p>
          <h2 style="font-size:clamp(24px,3.5vw,32px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 26px;line-height:1.18;">İletişim bilgilerimiz</h2>

          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
            <!-- Telefon -->
            <a href="tel:+902566121813" style="display:flex;align-items:flex-start;gap:14px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 22px;">
              <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;color:#fff;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/></svg></span>
              <span style="display:block;">
                <span style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft,#6b6b6b);margin-bottom:4px;">Telefon</span>
                <span style="display:block;font-weight:800;color:var(--text-main,#2a2a2a);font-size:16px;">0 256 612 18 13</span>
              </span>
            </a>
            <!-- WhatsApp -->
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:flex;align-items:flex-start;gap:14px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 22px;">
              <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;color:#fff;"><svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true" style="width:21px;height:21px;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg></span>
              <span style="display:block;">
                <span style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft,#6b6b6b);margin-bottom:4px;">WhatsApp</span>
                <span style="display:block;font-weight:800;color:var(--text-main,#2a2a2a);font-size:16px;">+90 542 620 51 00</span>
              </span>
            </a>
            <!-- E-posta -->
            <a href="mailto:info@estetikdermal.com" style="display:flex;align-items:flex-start;gap:14px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 22px;">
              <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;color:#fff;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
              <span style="display:block;">
                <span style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft,#6b6b6b);margin-bottom:4px;">E-posta</span>
                <span style="display:block;font-weight:800;color:var(--text-main,#2a2a2a);font-size:16px;word-break:break-word;">info@estetikdermal.com</span>
              </span>
            </a>
            <!-- Adres -->
            <a href="https://maps.google.com/maps?q=Kusadasi%20Aydin" target="_blank" rel="noopener" style="display:flex;align-items:flex-start;gap:14px;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:var(--radius-card,18px);padding:20px 22px;">
              <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:var(--grad-brand,linear-gradient(135deg,#E8702A,#F4A14E));display:grid;place-items:center;color:#fff;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
              <span style="display:block;">
                <span style="display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft,#6b6b6b);margin-bottom:4px;">Adres</span>
                <span style="display:block;font-weight:700;color:var(--text-main,#2a2a2a);font-size:14.5px;line-height:1.55;">Türkmen Mah. Turgut Özel Bulvarı Ada Modern A Blok No 83/3A Kuşadası/Aydın</span>
              </span>
            </a>
          </div>

          <!-- Çalışma saatleri -->
          <div style="display:flex;align-items:center;gap:14px;margin-top:18px;background:var(--primary-soft,#FCE9DC);border-radius:var(--radius-card,18px);padding:18px 22px;">
            <span style="width:46px;height:46px;flex-shrink:0;border-radius:13px;background:#fff;display:grid;place-items:center;color:var(--color-primary,#E8702A);"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <div>
              <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--primary-deep,#C85716);margin-bottom:4px;">Çalışma Saatleri</div>
              <div style="font-weight:800;color:var(--text-main,#2a2a2a);font-size:16px;">Hafta içi 09:00–18:00</div>
            </div>
          </div>

          <!-- Instagram -->
          <a href="https://www.instagram.com/estetikdermal/" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:10px;margin-top:18px;color:var(--color-primary,#E8702A);font-weight:700;font-size:15px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg> Instagram'da takip edin →
          </a>
        </div>

        <!-- SAĞ: Google Maps -->
        <div style="flex:1 1 380px;display:flex;">
          <iframe
            title="Estetik Dermal — Kuşadası / Aydın konum haritası"
            src="https://maps.google.com/maps?q=Kusadasi%20Aydin&t=&z=13&ie=UTF8&iwloc=&output=embed"
            style="width:100%;min-height:380px;border:0;border-radius:var(--radius-card,18px);box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

      </div>
    </section>

    
EDKP6S1;
        $p6s2 = <<<'EDKP6S2'
<!-- CONTACT FORM (KVKK'lı) -->

    <section style="padding:0 0 84px;background:#fff;">
      <div class="container">
        <div style="max-width:760px;margin:0 auto;background:var(--color-secondary,#FBF4EE);border:1px solid var(--border-soft,#ece6df);border-radius:24px;box-shadow:var(--shadow-card,0 14px 44px rgba(200,87,22,.10));padding:clamp(28px,5vw,48px);">
          <p style="color:var(--color-primary,#E8702A);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Talep Formu</p>
          <h2 style="font-size:clamp(24px,3.5vw,32px);font-weight:800;color:var(--text-main,#2a2a2a);margin:0 0 10px;line-height:1.18;">Bize yazın, size dönelim</h2>
          <p style="color:var(--text-soft,#6b6b6b);font-size:15px;line-height:1.65;margin:0 0 8px;">Aşağıdaki formu doldurun; ekibimiz en kısa sürede sizinle iletişime geçsin.</p>
          <p style="font-size:13px;color:var(--text-soft,#6b6b6b);background:var(--primary-soft,#FCE9DC);border-radius:10px;padding:10px 14px;margin:0 0 28px;line-height:1.55;">Not: Form CMS'e bağlandığında <code style="font-size:12.5px;">/api/v1/forms/.../submit</code> ile çalışacaktır.</p>

          <form action="#" method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">

            <!-- Ad Soyad -->
            <div style="display:flex;flex-direction:column;gap:7px;">
              <label for="ad" style="font-weight:700;font-size:14px;color:var(--text-main,#2a2a2a);">Ad Soyad</label>
              <input type="text" id="ad" name="ad" autocomplete="name" placeholder="Adınız ve soyadınız" style="width:100%;padding:13px 15px;border:1.5px solid var(--border-soft,#ece6df);border-radius:12px;background:#fff;font-size:15px;color:var(--text-main,#2a2a2a);">
            </div>

            <!-- Telefon -->
            <div style="display:flex;flex-direction:column;gap:7px;">
              <label for="telefon" style="font-weight:700;font-size:14px;color:var(--text-main,#2a2a2a);">Telefon</label>
              <input type="tel" id="telefon" name="telefon" autocomplete="tel" placeholder="0 5xx xxx xx xx" style="width:100%;padding:13px 15px;border:1.5px solid var(--border-soft,#ece6df);border-radius:12px;background:#fff;font-size:15px;color:var(--text-main,#2a2a2a);">
            </div>

            <!-- E-posta -->
            <div style="display:flex;flex-direction:column;gap:7px;">
              <label for="eposta" style="font-weight:700;font-size:14px;color:var(--text-main,#2a2a2a);">E-posta</label>
              <input type="email" id="eposta" name="eposta" autocomplete="email" placeholder="ornek@eposta.com" style="width:100%;padding:13px 15px;border:1.5px solid var(--border-soft,#ece6df);border-radius:12px;background:#fff;font-size:15px;color:var(--text-main,#2a2a2a);">
            </div>

            <!-- Marka / Konu -->
            <div style="display:flex;flex-direction:column;gap:7px;">
              <label for="konu" style="font-weight:700;font-size:14px;color:var(--text-main,#2a2a2a);">İlgilendiğiniz Marka / Konu</label>
              <select id="konu" name="konu" style="width:100%;padding:13px 15px;border:1.5px solid var(--border-soft,#ece6df);border-radius:12px;background:#fff;font-size:15px;color:var(--text-main,#2a2a2a);">
                <option value="skintech">Skin Tech</option>
                <option value="seffiline">Seffiline</option>
                <option value="grand-aespio">Grand Aespio</option>
                <option value="woorhi">Woorhi</option>
                <option value="mi-medical">Mi Medical</option>
                <option value="genel">Genel</option>
              </select>
            </div>

            <!-- Mesaj -->
            <div style="display:flex;flex-direction:column;gap:7px;grid-column:1 / -1;">
              <label for="mesaj" style="font-weight:700;font-size:14px;color:var(--text-main,#2a2a2a);">Mesaj</label>
              <textarea id="mesaj" name="mesaj" rows="5" placeholder="Talebinizi kısaca yazın..." style="width:100%;padding:13px 15px;border:1.5px solid var(--border-soft,#ece6df);border-radius:12px;background:#fff;font-size:15px;color:var(--text-main,#2a2a2a);line-height:1.6;resize:vertical;font-family:inherit;"></textarea>
            </div>

            <!-- KVKK onay -->
            <div style="grid-column:1 / -1;display:flex;align-items:flex-start;gap:12px;">
              <input type="checkbox" id="kvkk" name="kvkk" style="width:20px;height:20px;flex-shrink:0;margin-top:2px;accent-color:var(--color-primary,#E8702A);">
              <label for="kvkk" style="font-size:14px;color:var(--text-soft,#6b6b6b);line-height:1.6;">Kişisel verilerimin işlenmesine ilişkin aydınlatma metnini okudum, onaylıyorum.</label>
            </div>

            <!-- Gönder -->
            <div style="grid-column:1 / -1;">
              <button type="submit" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#E8702A);color:#fff;border:0;padding:15px 34px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 10px 30px rgba(232,112,42,.32);">Gönder →</button>
            </div>

          </form>
        </div>
      </div>
    </section>
EDKP6S2;
        $p7s0 = <<<'EDKP7S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"><style>
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
          <p style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--primary-deep,#0B2E34);border:1px solid var(--color-accent,#3FBFA8);border-radius:999px;padding:8px 16px;font-size:12px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;margin:0 0 24px;">İSPANYA · KLİNİK DERMOKOZMETİK</p>
          <h1 style="font-size:clamp(34px,5.2vw,58px);line-height:1.06;font-weight:800;color:var(--primary-deep,#0B2E34);margin:0 0 22px;letter-spacing:-.5px;">Klinik kanıtlı<br><span style="background:var(--grad-brand,linear-gradient(135deg,#0C6E72,#3FBFA8));-webkit-background-clip:text;background-clip:text;color:transparent;">cilt bilimi</span></h1>
          <p style="font-size:clamp(16px,2vw,19px);color:#3A5860;line-height:1.75;max-width:580px;margin:0 0 34px;">Skin Tech Pharma Group; kimyasal peeling, mezoterapi ve RRS® skinbooster serisinde dünya çapında öncü. Laboratuvar disiplini, dermatolojik kanıt ve CE Class III standartlarıyla geliştirilen profesyonel çözümler.</p>
          <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a href="/klasik-urunler" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#0C6E72);color:#fff;padding:15px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 12px 32px rgba(12,110,114,.30);">Ürünleri Gör →</a>
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;background:#25D366;color:#fff;border:1.5px solid #25D366;padding:15px 28px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;"><svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp Danışma</a>
          </div>
          <div style="display:flex;gap:26px;flex-wrap:wrap;margin-top:40px;">
            <div><div style="font-size:12px;color:#5A7B82;letter-spacing:.3px;">Sertifikasyon</div><div style="font-weight:800;color:var(--primary-deep,#0B2E34);">CE Class III</div></div>
            <div style="width:1px;background:var(--border-soft,#cfe6e1);"></div>
            <div><div style="font-size:12px;color:#5A7B82;letter-spacing:.3px;">Menşei</div><div style="font-weight:800;color:var(--primary-deep,#0B2E34);">İspanya</div></div>
            <div style="width:1px;background:var(--border-soft,#cfe6e1);"></div>
            <div><div style="font-size:12px;color:#5A7B82;letter-spacing:.3px;">Portföy</div><div style="font-weight:800;color:var(--primary-deep,#0B2E34);">84+ Ürün</div></div>
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
              <span style="position:absolute;top:14px;left:16px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:var(--color-primary,#0C6E72);text-transform:uppercase;">RRS® Skinbooster</span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:18px;">
              <div><div style="font-size:11px;color:var(--color-primary,#0C6E72);font-weight:800;text-transform:uppercase;letter-spacing:1.2px;">Skin Tech · RRS</div><div style="font-weight:800;color:var(--primary-deep,#0B2E34);">RRS® HA Long Lasting</div></div>
              <span style="background:var(--primary-soft,#D6EFEB);color:var(--primary-deep,#0B2E34);font-size:11px;font-weight:800;padding:6px 12px;border-radius:999px;letter-spacing:.5px;">CE III</span>
            </div>
          </div>
          <div style="position:absolute;bottom:-18px;left:-18px;background:#fff;border:1px solid var(--border-soft,#cfe6e1);border-radius:16px;box-shadow:var(--shadow-soft,0 4px 18px rgba(11,46,52,.08));padding:13px 18px;display:flex;align-items:center;gap:10px;">
            <span style="width:34px;height:34px;flex-shrink:0;border-radius:10px;background:var(--primary-soft,#D6EFEB);"></span>
            <div><div style="font-size:11px;color:#5A7B82;">Dermatolojik</div><div style="font-weight:800;font-size:14px;color:var(--primary-deep,#0B2E34);">Klinik Test Edildi</div></div>
          </div>
        </div>
      </div>
    </section>

    
EDKP7S0;
        $p7s1 = <<<'EDKP7S1'
<!-- GÜVEN / KREDİBİLİTE ŞERİDİ -->

    <section style="background:var(--primary-deep,#0B2E34);">
      <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1px;padding:0;background:rgba(255,255,255,.08);">
        <div style="background:var(--primary-deep,#0B2E34);padding:30px 24px;text-align:center;">
          <div style="width:34px;height:34px;margin:0 auto 10px;border-radius:10px;background:rgba(63,191,168,.18);border:1px solid rgba(63,191,168,.45);"></div>
          <div style="font-weight:800;color:#fff;font-size:16px;">CE Class III</div>
          <div style="color:rgba(255,255,255,.6);font-size:13px;margin-top:3px;">Tıbbi cihaz sertifikası</div>
        </div>
        <div style="background:var(--primary-deep,#0B2E34);padding:30px 24px;text-align:center;">
          <div style="width:34px;height:34px;margin:0 auto 10px;border-radius:10px;background:rgba(63,191,168,.18);border:1px solid rgba(63,191,168,.45);"></div>
          <div style="font-weight:800;color:#fff;font-size:16px;">Klinik Test</div>
          <div style="color:rgba(255,255,255,.6);font-size:13px;margin-top:3px;">Kanıta dayalı formülasyon</div>
        </div>
        <div style="background:var(--primary-deep,#0B2E34);padding:30px 24px;text-align:center;">
          <div style="width:34px;height:34px;margin:0 auto 10px;border-radius:10px;background:rgba(63,191,168,.18);border:1px solid rgba(63,191,168,.45);"></div>
          <div style="font-weight:800;color:#fff;font-size:16px;">İspanya Menşeli</div>
          <div style="color:rgba(255,255,255,.6);font-size:13px;margin-top:3px;">Avrupa üretim standardı</div>
        </div>
        <div style="background:var(--primary-deep,#0B2E34);padding:30px 24px;text-align:center;">
          <div style="width:34px;height:34px;margin:0 auto 10px;border-radius:10px;background:rgba(63,191,168,.18);border:1px solid rgba(63,191,168,.45);"></div>
          <div style="font-weight:800;color:#fff;font-size:16px;">84+ Ürün</div>
          <div style="color:rgba(255,255,255,.6);font-size:13px;margin-top:3px;">Geniş profesyonel portföy</div>
        </div>
      </div>
    </section>

    
EDKP7S1;
        $p7s2 = <<<'EDKP7S2'
<!-- ÜRÜN AİLELERİ -->

    <section style="padding:88px 0;background:#fff;">
      <div class="container">
        <div style="text-align:center;max-width:660px;margin:0 auto 56px;">
          <p style="color:var(--color-primary,#0C6E72);font-weight:800;letter-spacing:1.8px;text-transform:uppercase;font-size:12px;margin:0 0 12px;">Ürün Aileleri</p>
          <h2 style="font-size:clamp(28px,4vw,40px);font-weight:800;color:var(--primary-deep,#0B2E34);margin:0 0 14px;line-height:1.14;letter-spacing:-.4px;">Bilim temelli dört temel seri</h2>
          <p style="color:#3A5860;font-size:17px;line-height:1.7;">Skinbooster'dan mezoterapiye, kimyasal peelingden güneş korumaya — her seri dermatolojik kanıt ve klinik standartla geliştirildi.</p>
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
              <div><div style="font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:var(--color-accent,#3FBFA8);">Skinbooster</div><h3 style="font-size:21px;font-weight:800;color:#fff;margin:4px 0 0;">RRS® Skinbooster</h3></div>
            </div>
            <div style="padding:8px 16px 18px;">
              <a href="/klasik-urun-detay" class="st-card" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:16px 14px;border-radius:12px;border:1px solid transparent;background:var(--color-secondary,#EAF6F4);margin-top:10px;">
                <span><span style="display:block;font-weight:800;color:var(--primary-deep,#0B2E34);font-size:15px;">RRS® HA Long Lasting</span><span style="display:block;color:#5A7B82;font-size:13px;margin-top:2px;">Çapraz bağlı HA · CE Class III</span></span>
                <span style="color:var(--color-primary,#0C6E72);font-weight:800;flex-shrink:0;">→</span>
              </a>
            </div>
          </div>

          <!-- Benebellum Mezoterapi -->
          <div style="background:#fff;border:1px solid var(--border-soft,#cfe6e1);border-radius:var(--radius-card,18px);overflow:hidden;">
            <div style="background:linear-gradient(135deg,var(--primary-deep,#0B2E34),var(--color-primary,#0C6E72));padding:24px 26px;display:flex;align-items:center;justify-content:space-between;">
              <div><div style="font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:var(--color-accent,#3FBFA8);">Mezoterapi</div><h3 style="font-size:21px;font-weight:800;color:#fff;margin:4px 0 0;">Benebellum</h3></div>
            </div>
            <div style="padding:8px 16px 18px;">
              <a href="/klasik-urun-detay" class="st-card" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 14px;border-radius:12px;border:1px solid transparent;background:var(--color-secondary,#EAF6F4);margin-top:10px;">
                <span><span style="display:block;font-weight:800;color:var(--primary-deep,#0B2E34);font-size:15px;">LUMINA VİT-C 18%</span><span style="display:block;color:#5A7B82;font-size:13px;margin-top:2px;">Aydınlatıcı C vitamini</span></span>
                <span style="color:var(--color-primary,#0C6E72);font-weight:800;flex-shrink:0;">→</span>
              </a>
              <a href="/klasik-urun-detay" class="st-card" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 14px;border-radius:12px;border:1px solid transparent;background:var(--color-secondary,#EAF6F4);margin-top:10px;">
                <span><span style="display:block;font-weight:800;color:var(--primary-deep,#0B2E34);font-size:15px;">VİT A + E</span><span style="display:block;color:#5A7B82;font-size:13px;margin-top:2px;">Antioksidan onarım</span></span>
                <span style="color:var(--color-primary,#0C6E72);font-weight:800;flex-shrink:0;">→</span>
              </a>
              <a href="/klasik-urun-detay" class="st-card" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 14px;border-radius:12px;border:1px solid transparent;background:var(--color-secondary,#EAF6F4);margin-top:10px;">
                <span><span style="display:block;font-weight:800;color:var(--primary-deep,#0B2E34);font-size:15px;">TX SOLUTION</span><span style="display:block;color:#5A7B82;font-size:13px;margin-top:2px;">Leke karşıtı çözüm</span></span>
                <span style="color:var(--color-primary,#0C6E72);font-weight:800;flex-shrink:0;">→</span>
              </a>
            </div>
          </div>

          <!-- Kimyasal Peeling -->
          <div style="background:#fff;border:1px solid var(--border-soft,#cfe6e1);border-radius:var(--radius-card,18px);overflow:hidden;">
            <div style="background:linear-gradient(135deg,var(--primary-deep,#0B2E34),var(--color-primary,#0C6E72));padding:24px 26px;display:flex;align-items:center;justify-content:space-between;">
              <div><div style="font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:var(--color-accent,#3FBFA8);">Peeling</div><h3 style="font-size:21px;font-weight:800;color:#fff;margin:4px 0 0;">Kimyasal Peeling</h3></div>
            </div>
            <div style="padding:8px 16px 18px;">
              <a href="/klasik-urun-detay" class="st-card" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 14px;border-radius:12px;border:1px solid transparent;background:var(--color-secondary,#EAF6F4);margin-top:10px;">
                <span><span style="display:block;font-weight:800;color:var(--primary-deep,#0B2E34);font-size:15px;">Aclaranse</span><span style="display:block;color:#5A7B82;font-size:13px;margin-top:2px;">Depigmentasyon peelingi</span></span>
                <span style="color:var(--color-primary,#0C6E72);font-weight:800;flex-shrink:0;">→</span>
              </a>
              <a href="/klasik-urun-detay" class="st-card" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 14px;border-radius:12px;border:1px solid transparent;background:var(--color-secondary,#EAF6F4);margin-top:10px;">
                <span><span style="display:block;font-weight:800;color:var(--primary-deep,#0B2E34);font-size:15px;">Easy Phytic</span><span style="display:block;color:#5A7B82;font-size:13px;margin-top:2px;">Nötralizasyonsuz fitik asit</span></span>
                <span style="color:var(--color-primary,#0C6E72);font-weight:800;flex-shrink:0;">→</span>
              </a>
            </div>
          </div>

          <!-- Güneş Koruma -->
          <div style="background:#fff;border:1px solid var(--border-soft,#cfe6e1);border-radius:var(--radius-card,18px);overflow:hidden;">
            <div style="background:linear-gradient(135deg,var(--primary-deep,#0B2E34),var(--color-primary,#0C6E72));padding:24px 26px;display:flex;align-items:center;justify-content:space-between;">
              <div><div style="font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:var(--color-accent,#3FBFA8);">SPF</div><h3 style="font-size:21px;font-weight:800;color:#fff;margin:4px 0 0;">Güneş Koruma</h3></div>
            </div>
            <div style="padding:8px 16px 18px;">
              <a href="/klasik-urun-detay" class="st-card" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:16px 14px;border-radius:12px;border:1px solid transparent;background:var(--color-secondary,#EAF6F4);margin-top:10px;">
                <span><span style="display:block;font-weight:800;color:var(--primary-deep,#0B2E34);font-size:15px;">Melablock HSP SPF 50+</span><span style="display:block;color:#5A7B82;font-size:13px;margin-top:2px;">Yüksek faktör · leke koruması</span></span>
                <span style="color:var(--color-primary,#0C6E72);font-weight:800;flex-shrink:0;">→</span>
              </a>
            </div>
          </div>

        </div>
      </div>
    </section>

    
EDKP7S2;
        $p7s3 = <<<'EDKP7S3'
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
                <div style="display:inline-block;background:#fff;border:1px solid var(--color-accent,#3FBFA8);color:var(--color-primary,#0C6E72);font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:7px 16px;border-radius:999px;">Çapraz Bağlı HA</div>
              </div>
            </div>
          </div>
          <!-- Açıklama -->
          <div style="flex:1 1 440px;">
            <p style="display:inline-flex;align-items:center;gap:8px;color:var(--color-primary,#0C6E72);font-weight:800;letter-spacing:1.6px;text-transform:uppercase;font-size:12px;margin:0 0 14px;">★ Öne Çıkan Ürün</p>
            <h2 style="font-size:clamp(27px,4vw,40px);font-weight:800;color:var(--primary-deep,#0B2E34);margin:0 0 18px;line-height:1.15;letter-spacing:-.4px;">RRS® HA Long Lasting</h2>
            <p style="color:#3A5860;font-size:17px;line-height:1.78;margin:0 0 24px;">Çapraz bağlı hyalüronik asit içeren <strong style="color:var(--primary-deep,#0B2E34);">CE Class III dermal implant</strong>. Cildin derin nem rezervlerini destekleyerek uzun süreli sıkılık, elastikiyet ve canlılık sağlar. Skinbooster protokollerinde profesyonel kullanım için geliştirilmiştir.</p>
            <ul style="list-style:none;margin:0 0 30px;padding:0;display:grid;gap:13px;">
              <li style="display:flex;align-items:center;gap:12px;color:var(--primary-deep,#0B2E34);font-weight:600;"><span style="width:26px;height:26px;flex-shrink:0;border-radius:8px;background:var(--primary-soft,#D6EFEB);color:var(--color-primary,#0C6E72);display:grid;place-items:center;font-weight:800;">✓</span> CE Class III tıbbi cihaz sınıflandırması</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--primary-deep,#0B2E34);font-weight:600;"><span style="width:26px;height:26px;flex-shrink:0;border-radius:8px;background:var(--primary-soft,#D6EFEB);color:var(--color-primary,#0C6E72);display:grid;place-items:center;font-weight:800;">✓</span> Çapraz bağlı HA ile uzun etkili sonuç</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--primary-deep,#0B2E34);font-weight:600;"><span style="width:26px;height:26px;flex-shrink:0;border-radius:8px;background:var(--primary-soft,#D6EFEB);color:var(--color-primary,#0C6E72);display:grid;place-items:center;font-weight:800;">✓</span> Skinbooster protokolleri için optimize</li>
            </ul>
            <a href="/klasik-urun-detay" style="display:inline-flex;align-items:center;gap:8px;background:var(--color-primary,#0C6E72);color:#fff;padding:14px 30px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;box-shadow:0 12px 30px rgba(12,110,114,.28);">Ürün Detayı →</a>
          </div>
        </div>
      </div>
    </section>

    
EDKP7S3;
        $p7s4 = <<<'EDKP7S4'
<!-- EĞİTİM & DESTEK NOTU -->

    <section style="padding:72px 0;background:#fff;">
      <div class="container">
        <div style="border:1px solid var(--border-soft,#cfe6e1);border-radius:var(--radius-card,18px);padding:clamp(28px,4vw,44px);display:flex;flex-wrap:wrap;gap:28px;align-items:center;background:linear-gradient(180deg,#fff,var(--color-secondary,#EAF6F4));">
          <div style="width:64px;height:64px;flex-shrink:0;border-radius:18px;background:var(--grad-brand,linear-gradient(135deg,#0C6E72,#3FBFA8));"></div>
          <div style="flex:1 1 360px;">
            <h3 style="font-size:clamp(20px,3vw,26px);font-weight:800;color:var(--primary-deep,#0B2E34);margin:0 0 8px;">Uygulamalı eğitim ve teknik destek</h3>
            <p style="color:#3A5860;font-size:16px;line-height:1.7;margin:0;">Skin Tech ürünleri için Estetik Dermal'dan uygulamalı eğitim ve teknik destek. Hekimlere protokol kurulumundan güvenli uygulamaya kadar birebir rehberlik sunuyoruz.</p>
          </div>
          <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--primary-deep,#0B2E34);border:1.5px solid var(--color-accent,#3FBFA8);padding:13px 26px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;flex-shrink:0;">Eğitim Talep Et →</a>
        </div>
      </div>
    </section>

    
EDKP7S4;
        $p7s5 = <<<'EDKP7S5'
<!-- CTA BANDI -->

    <section style="padding:20px 0 88px;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:var(--grad-brand,linear-gradient(135deg,#0C6E72,#3FBFA8));padding:clamp(40px,6vw,72px);text-align:center;color:#fff;">
          <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.06) 1px,transparent 1px);background-size:40px 40px;"></div>
          <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.12);"></div>
          <div style="position:absolute;bottom:-60px;left:-30px;width:240px;height:240px;border-radius:50%;background:rgba(255,255,255,.08);"></div>
          <div style="position:relative;">
            <h2 style="font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 14px;line-height:1.15;letter-spacing:-.4px;">Skin Tech ürünleri hakkında bilgi alın</h2>
            <p style="font-size:18px;opacity:.95;max-width:600px;margin:0 auto 32px;line-height:1.6;">Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;background:#25D366;color:#fff;padding:15px 32px;border-radius:var(--radius-button,999px);font-weight:800;font-size:15px;"><svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile Yaz</a>
              <a href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.6);padding:15px 32px;border-radius:var(--radius-button,999px);font-weight:700;font-size:15px;">MEDINET Portalı ↗</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDKP7S5;
        $p8s0 = <<<'EDKP8S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet"><style>
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
          <p style="display:inline-flex;align-items:center;gap:9px;color:var(--color-primary,#C98A6D);font-size:12px;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin:0 0 26px;"><span style="width:26px;height:1px;background:var(--color-primary,#C98A6D);"></span>Güzelliğin İnce Dokunuşu</p>
          <h1 class="sf-serif" style="font-size:clamp(40px,6vw,72px);line-height:1.05;font-weight:500;color:var(--text-main,#4A2E35);margin:0 0 26px;">Cildinize özel<br><em style="font-style:italic;color:var(--color-primary,#C98A6D);">bütüncül bakım</em></h1>
          <p style="font-size:clamp(16px,2vw,19px);color:var(--text-soft,#8A6A70);line-height:1.85;max-width:520px;margin:0 0 38px;">Seffiline; cilt, saç, intim bakım ve dolgu çözümlerinde feminen ve profesyonel bir seri. Kadın sağlığı ve güzelliğine, zarafetle ve bilimle yaklaşır.</p>
          <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;">
            <a href="#koleksiyon" class="sf-btn-fill" style="display:inline-flex;align-items:center;gap:9px;background:var(--grad-rose,linear-gradient(135deg,#C98A6D,#E8A0A8));color:#fff;padding:16px 34px;border-radius:999px;font-weight:600;font-size:15px;letter-spacing:.3px;box-shadow:0 12px 30px rgba(201,138,109,.30);transition:transform .2s,box-shadow .2s;">Koleksiyonu Keşfet</a>
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;border:1px solid #25D366;padding:15px 30px;border-radius:999px;font-weight:600;font-size:15px;transition:transform .2s,box-shadow .2s;"><svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp Danışma</a>
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
            <div><div style="font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#C98A6D);font-weight:700;">Seffiller</div><div class="sf-serif" style="font-size:16px;color:var(--text-main,#4A2E35);">Dolgu Serisi</div></div>
          </div>
          <div style="position:absolute;bottom:24px;right:-8px;background:#fff;border:1px solid var(--border-soft,#EBD9D6);border-radius:18px;box-shadow:var(--shadow-soft,0 6px 22px rgba(74,46,53,.07));padding:14px 18px;display:flex;align-items:center;gap:11px;">
            <span style="width:34px;height:34px;flex-shrink:0;border-radius:50%;background:linear-gradient(150deg,#FBE7E6,#F4D4CE);"></span>
            <div><div style="font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--color-primary,#C98A6D);font-weight:700;">SeffiCare</div><div class="sf-serif" style="font-size:16px;color:var(--text-main,#4A2E35);">Cilt Bakımı</div></div>
          </div>
        </div>
      </div>
    </section>

    
EDKP8S0;
        $p8s1 = <<<'EDKP8S1'
<!-- 2. MARKA FELSEFESİ -->

    <section style="padding:96px 0;background:#FFFCFB;">
      <div class="container" style="max-width:780px;text-align:center;">
        <p style="color:var(--color-accent,#E8A0A8);font-weight:700;letter-spacing:3px;text-transform:uppercase;font-size:12px;margin:0 0 22px;">Marka Felsefesi</p>
        <p class="sf-serif" style="font-size:clamp(24px,3.4vw,34px);line-height:1.55;font-weight:400;color:var(--text-main,#4A2E35);margin:0 0 32px;font-style:italic;">"Her kadının güzelliği biriciktir. Seffiline, kadın sağlığı ve güzelliğine bütüncül bir yaklaşımla; cildi, saçı ve hassas bölgeleri aynı özen ve zarafetle ele alır."</p>
        <!-- ince zarif ayraç -->
        <div style="display:flex;align-items:center;justify-content:center;gap:14px;margin:0 auto;max-width:240px;">
          <span style="flex:1;height:1px;background:linear-gradient(90deg,transparent,var(--border-soft,#EBD9D6));"></span>
          <span style="color:var(--color-primary,#C98A6D);font-size:18px;">❀</span>
          <span style="flex:1;height:1px;background:linear-gradient(90deg,var(--border-soft,#EBD9D6),transparent);"></span>
        </div>
      </div>
    </section>

    
EDKP8S1;
        $p8s2 = <<<'EDKP8S2'
<!-- 3. 4 ÜRÜN AİLESİ -->

    <section id="koleksiyon" style="padding:32px 0 100px;background:#FFFCFB;">
      <div class="container">
        <div style="text-align:center;max-width:620px;margin:0 auto 56px;">
          <p style="color:var(--color-primary,#C98A6D);font-weight:700;letter-spacing:3px;text-transform:uppercase;font-size:12px;margin:0 0 16px;">Koleksiyon</p>
          <h2 class="sf-serif" style="font-size:clamp(30px,4.4vw,46px);font-weight:500;color:var(--text-main,#4A2E35);margin:0 0 16px;line-height:1.12;">Dört ince ürün ailesi</h2>
          <p style="color:var(--text-soft,#8A6A70);font-size:17px;line-height:1.8;">Baştan ayağa bütüncül bir bakım ritüeli; her ihtiyaca uygun, zarif ve profesyonel.</p>
        </div>

        <!-- 🖼️ GÖRSEL (tekrarlayan ürün ailesi kartı görsel deseni): /assets/img/seffiline-product-{slug}.jpg (oran 1:1)
             Örn: seffiline-product-seffícare.jpg → ASCII güvenli: seffiline-product-sefficare.jpg, seffiline-product-seffigyn.jpg, seffiline-product-seffihair.jpg, seffiline-product-seffiller.jpg
             PROMPT: "Elegant feminine cosmetic product packshot (skincare bottle / intimate care / hair mesotherapy / dermal filler), soft blush-pink and cream backdrop with rose-gold accents, luxury editorial beauty lighting, gentle diffused glow, refined and delicate styling, single fresh petal detail, premium magazine aesthetic; photorealistic, detailed, high resolution; no text, no logo, no watermark"
             KULLANIM → Her ürün kartının başına (yuvarlak ikon dairesinin üstüne) bir görsel alanı eklenebilir, örn:
             <img src="/assets/img/seffiline-product-sefficare.jpg" alt="Seffiline SeffiCare cilt bakımı ürün çekimi" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:22px;margin-bottom:20px;"> -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:26px;">
          <!-- SeffiCare -->
          <a href="/klasik-urun-detay" class="sf-card" style="display:block;background:#fff;border:1px solid var(--border-soft,#EBD9D6);border-radius:28px;padding:36px 30px;transition:transform .25s,box-shadow .25s;">
            <div style="width:66px;height:66px;border-radius:50%;background:linear-gradient(150deg,#FBE7E6,#F4D4CE);margin-bottom:24px;"></div>
            <h3 class="sf-serif" style="font-size:25px;font-weight:600;color:var(--text-main,#4A2E35);margin:0 0 4px;">SeffiCare</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--color-primary,#C98A6D);font-weight:700;margin:0 0 14px;">Cilt Bakımı</p>
            <p style="color:var(--text-soft,#8A6A70);font-size:14.5px;line-height:1.75;margin:0 0 22px;">Cildi besleyen, nemlendiren ve canlandıran zarif cilt bakım serisi.</p>
            <span style="color:var(--color-primary,#C98A6D);font-weight:600;font-size:14px;letter-spacing:.3px;">İncele →</span>
          </a>
          <!-- SeffiGyn -->
          <a href="/klasik-urun-detay" class="sf-card" style="display:block;background:#fff;border:1px solid var(--border-soft,#EBD9D6);border-radius:28px;padding:36px 30px;transition:transform .25s,box-shadow .25s;">
            <div style="width:66px;height:66px;border-radius:50%;background:linear-gradient(150deg,#FBE7E6,#F4D4CE);margin-bottom:24px;"></div>
            <h3 class="sf-serif" style="font-size:25px;font-weight:600;color:var(--text-main,#4A2E35);margin:0 0 4px;">SeffiGyn</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--color-primary,#C98A6D);font-weight:700;margin:0 0 14px;">İntim Bakım</p>
            <p style="color:var(--text-soft,#8A6A70);font-size:14.5px;line-height:1.75;margin:0 0 22px;">Hassas bölgelerin sağlığı için pH dengeli, nazik ve güvenilir intim bakım.</p>
            <span style="color:var(--color-primary,#C98A6D);font-weight:600;font-size:14px;letter-spacing:.3px;">İncele →</span>
          </a>
          <!-- SeffiHair -->
          <a href="/klasik-urun-detay" class="sf-card" style="display:block;background:#fff;border:1px solid var(--border-soft,#EBD9D6);border-radius:28px;padding:36px 30px;transition:transform .25s,box-shadow .25s;">
            <div style="width:66px;height:66px;border-radius:50%;background:linear-gradient(150deg,#FBE7E6,#F4D4CE);margin-bottom:24px;"></div>
            <h3 class="sf-serif" style="font-size:25px;font-weight:600;color:var(--text-main,#4A2E35);margin:0 0 4px;">SeffiHair</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--color-primary,#C98A6D);font-weight:700;margin:0 0 14px;">Saç Bakımı & Mezoterapi</p>
            <p style="color:var(--text-soft,#8A6A70);font-size:14.5px;line-height:1.75;margin:0 0 22px;">Saç kökünü güçlendiren mezoterapi ve yoğun bakım çözümleri.</p>
            <span style="color:var(--color-primary,#C98A6D);font-weight:600;font-size:14px;letter-spacing:.3px;">İncele →</span>
          </a>
          <!-- Seffiller -->
          <a href="/klasik-urun-detay" class="sf-card" style="display:block;background:#fff;border:1px solid var(--border-soft,#EBD9D6);border-radius:28px;padding:36px 30px;transition:transform .25s,box-shadow .25s;">
            <div style="width:66px;height:66px;border-radius:50%;background:linear-gradient(150deg,#FBE7E6,#F4D4CE);margin-bottom:24px;"></div>
            <h3 class="sf-serif" style="font-size:25px;font-weight:600;color:var(--text-main,#4A2E35);margin:0 0 4px;">Seffiller</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--color-primary,#C98A6D);font-weight:700;margin:0 0 14px;">Dolgu Serisi</p>
            <p style="color:var(--text-soft,#8A6A70);font-size:14.5px;line-height:1.75;margin:0 0 22px;">Hyalüronik asit bazlı, doğal ve zarif sonuçlar veren dolgu çözümleri.</p>
            <span style="color:var(--color-primary,#C98A6D);font-weight:600;font-size:14px;letter-spacing:.3px;">İncele →</span>
          </a>
        </div>
      </div>
    </section>

    
EDKP8S2;
        $p8s3 = <<<'EDKP8S3'
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
          <p style="display:inline-flex;align-items:center;gap:9px;color:var(--color-primary,#C98A6D);font-weight:700;letter-spacing:3px;text-transform:uppercase;font-size:12px;margin:0 0 22px;"><span style="width:26px;height:1px;background:var(--color-primary,#C98A6D);"></span>Öne Çıkan</p>
          <h2 class="sf-serif" style="font-size:clamp(30px,4.4vw,46px);font-weight:500;color:var(--text-main,#4A2E35);margin:0 0 22px;line-height:1.12;">SeffiHair ile<br><em style="font-style:italic;color:var(--color-primary,#C98A6D);">kökten güçlü saçlar</em></h2>
          <p style="color:var(--text-soft,#8A6A70);font-size:17px;line-height:1.85;margin:0 0 28px;max-width:520px;">Saç dökülmesiyle mücadelede mezoterapi temelli bir yaklaşım. SeffiHair serisi, saç köküne ihtiyaç duyduğu vitamin ve mineralleri ileterek folikülleri besler; daha sağlıklı, dolgun ve canlı bir görünüm için zarif bir bakım ritüeli sunar.</p>
          <ul style="list-style:none;margin:0 0 32px;padding:0;display:grid;gap:14px;">
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#4A2E35);font-weight:500;"><span style="color:var(--color-accent,#E8A0A8);font-size:16px;">❀</span> Saç köküne yoğun besin desteği</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#4A2E35);font-weight:500;"><span style="color:var(--color-accent,#E8A0A8);font-size:16px;">❀</span> Mezoterapi ile uyumlu profesyonel formül</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#4A2E35);font-weight:500;"><span style="color:var(--color-accent,#E8A0A8);font-size:16px;">❀</span> Dolgun ve canlı bir saç görünümü</li>
          </ul>
          <a href="/klasik-urun-detay" class="sf-btn-fill" style="display:inline-flex;align-items:center;gap:9px;background:var(--grad-rose,linear-gradient(135deg,#C98A6D,#E8A0A8));color:#fff;padding:15px 32px;border-radius:999px;font-weight:600;font-size:15px;letter-spacing:.3px;box-shadow:0 12px 30px rgba(201,138,109,.30);transition:transform .2s,box-shadow .2s;">SeffiHair'i İncele →</a>
        </div>
      </div>
    </section>

    
EDKP8S3;
        $p8s4 = <<<'EDKP8S4'
<!-- 5. GÜVEN / KALİTE NOTU -->

    <section style="padding:96px 0;background:#FFFCFB;">
      <div class="container" style="max-width:760px;text-align:center;">
        <div style="display:inline-block;width:70px;height:70px;border-radius:50%;background:linear-gradient(150deg,#FBE7E6,#F4D4CE);margin:0 0 26px;"></div>
        <h2 class="sf-serif" style="font-size:clamp(26px,3.8vw,38px);font-weight:500;color:var(--text-main,#4A2E35);margin:0 0 18px;line-height:1.2;">Estetik Dermal güvencesiyle,<br><em style="font-style:italic;color:var(--color-primary,#C98A6D);">%100 orijinal</em></h2>
        <p style="color:var(--text-soft,#8A6A70);font-size:17px;line-height:1.85;margin:0;">Seffiline ürünleri, resmi distribütör Estetik Dermal güvencesiyle sunulur. Her ürün sertifikalı, takip edilebilir ve tamamen orijinaldir; güzelliğiniz emin ellerde.</p>
      </div>
    </section>

    
EDKP8S4;
        $p8s5 = <<<'EDKP8S5'
<!-- 6. CTA BANDI -->

    <section style="padding:20px 0 96px;background:#FFFCFB;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:36px;background:var(--grad-rose,linear-gradient(135deg,#C98A6D,#E8A0A8));padding:clamp(48px,7vw,84px);text-align:center;color:#fff;">
          <div aria-hidden="true" style="position:absolute;top:-50px;right:-40px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.14);"></div>
          <div aria-hidden="true" style="position:absolute;bottom:-70px;left:-40px;width:260px;height:260px;border-radius:50%;background:rgba(255,255,255,.10);"></div>
          <div style="position:relative;">
            <p style="letter-spacing:3px;text-transform:uppercase;font-size:12px;font-weight:700;opacity:.9;margin:0 0 18px;">İletişim</p>
            <h2 class="sf-serif" style="font-size:clamp(28px,4.4vw,46px);font-weight:500;margin:0 0 18px;line-height:1.14;">Seffiline ürünleri için bize ulaşın</h2>
            <p style="font-size:18px;opacity:.95;max-width:560px;margin:0 auto 36px;line-height:1.7;">Ürün, fiyat ve uygulama bilgileri için ekibimiz hazır. WhatsApp'tan zarifçe yazın, hemen yanıtlayalım.</p>
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:16px 38px;border-radius:999px;font-weight:700;font-size:15px;letter-spacing:.3px;box-shadow:0 14px 36px rgba(74,46,53,.18);"><svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile Yaz</a>
          </div>
        </div>
      </div>
    </section>
EDKP8S5;
        $p9s0 = <<<'EDKP9S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800;900&display=swap" rel="stylesheet"><style>
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
          <p style="display:inline-flex;align-items:center;gap:9px;background:rgba(255,255,255,.16);border:1.5px solid rgba(255,255,255,.32);color:#fff;border-radius:999px;padding:9px 18px;font-size:12.5px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;margin:0 0 26px;backdrop-filter:blur(6px);">✦ K-Beauty Teknolojisi</p>
          <h1 style="font-size:clamp(38px,6vw,68px);line-height:1.02;font-weight:900;color:#fff;margin:0 0 22px;letter-spacing:-1px;">Cildin için<br>yeni nesil bakım</h1>
          <p style="font-size:clamp(16px,2vw,21px);color:rgba(255,255,255,.92);line-height:1.65;max-width:560px;margin:0 0 36px;font-weight:500;">Grand Aespio, yüz maskeleri ve ip askı (thread lift) ürünlerinde modern Kore yaklaşımını sahaya taşıyor. Yeni nesil formüller, cesur sonuçlar.</p>
          <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a href="#urunler" style="display:inline-flex;align-items:center;gap:9px;background:#fff;color:var(--aespio-deep,#4A3BC0);padding:16px 34px;border-radius:999px;font-weight:800;font-size:16px;box-shadow:0 16px 40px rgba(33,212,180,.35);">Ürünleri Gör →</a>
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;border:1.5px solid rgba(255,255,255,.5);padding:16px 30px;border-radius:999px;font-weight:800;font-size:16px;box-shadow:0 10px 30px rgba(37,211,102,.35);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:20px;height:20px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp Danışma</a>
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
                <div style="font-size:11px;color:var(--color-primary,#6C5CE0);font-weight:800;text-transform:uppercase;letter-spacing:1.2px;">Grand Aespio</div>
                <div style="font-weight:900;color:var(--text-main,#221A40);font-size:17px;">Beta-Glukan Mask</div>
              </div>
              <span style="background:var(--aespio-mint-soft,#DFF8F2);color:#0E9E86;font-size:11.5px;font-weight:800;padding:7px 14px;border-radius:999px;">K-BEAUTY</span>
            </div>
          </div>
          <div style="position:absolute;bottom:-20px;left:-18px;background:#fff;border-radius:18px;box-shadow:0 12px 34px rgba(74,59,192,.22);padding:14px 18px;display:flex;align-items:center;gap:11px;">
            <span style="width:34px;height:34px;border-radius:11px;background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));flex-shrink:0;"></span>
            <div>
              <div style="font-size:11px;color:var(--text-soft,#6B6088);font-weight:600;">Thread Lift</div>
              <div style="font-weight:900;font-size:14px;color:var(--text-main,#221A40);">LFL Anchor</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDKP9S0;
        $p9s1 = <<<'EDKP9S1'
<!-- STAT / ÖZELLİK ŞERİDİ -->

    <section style="background:#fff;">
      <div class="container" style="display:flex;flex-wrap:wrap;gap:18px;padding:40px 0;justify-content:center;">
        <div class="aespio-pill" style="flex:1 1 200px;display:flex;align-items:center;gap:14px;background:var(--grad-aespio-soft,linear-gradient(135deg,#ECE6FB,#DFF8F2));border-radius:20px;padding:20px 22px;">
          <span style="width:48px;height:48px;border-radius:14px;background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));flex-shrink:0;"></span>
          <div><div style="font-weight:900;font-size:16px;color:var(--text-main,#221A40);">Yeni Nesil Formül</div><div style="font-size:13px;color:var(--text-soft,#6B6088);font-weight:600;">İleri Kore bilimi</div></div>
        </div>
        <div class="aespio-pill" style="flex:1 1 200px;display:flex;align-items:center;gap:14px;background:var(--grad-aespio-soft,linear-gradient(135deg,#ECE6FB,#DFF8F2));border-radius:20px;padding:20px 22px;">
          <span style="width:48px;height:48px;border-radius:14px;background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));flex-shrink:0;"></span>
          <div><div style="font-weight:900;font-size:16px;color:var(--text-main,#221A40);">K-Beauty</div><div style="font-size:13px;color:var(--text-soft,#6B6088);font-weight:600;">Modern Kore yaklaşımı</div></div>
        </div>
        <div class="aespio-pill" style="flex:1 1 200px;display:flex;align-items:center;gap:14px;background:var(--grad-aespio-soft,linear-gradient(135deg,#ECE6FB,#DFF8F2));border-radius:20px;padding:20px 22px;">
          <span style="width:48px;height:48px;border-radius:14px;background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));flex-shrink:0;"></span>
          <div><div style="font-weight:900;font-size:16px;color:var(--text-main,#221A40);">Maske + Thread</div><div style="font-size:13px;color:var(--text-soft,#6B6088);font-weight:600;">Tek portföyde iki güç</div></div>
        </div>
      </div>
    </section>

    
EDKP9S1;
        $p9s2 = <<<'EDKP9S2'
<!-- ÜRÜN SHOWCASE -->

    <section id="urunler" style="padding:84px 0;background:var(--color-secondary,#F0ECFB);">
      <div class="container">
        <div style="text-align:center;max-width:640px;margin:0 auto 52px;">
          <p style="color:var(--color-primary,#6C5CE0);font-weight:800;letter-spacing:1.6px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Ürün Serisi</p>
          <h2 style="font-size:clamp(30px,4.5vw,46px);font-weight:900;color:var(--text-main,#221A40);margin:0 0 14px;line-height:1.08;letter-spacing:-.5px;">Maskeden ip askıya, eksiksiz bir seri</h2>
          <p style="color:var(--text-soft,#6B6088);font-size:17px;line-height:1.7;font-weight:500;">Yatıştırıcı maskeler, nemlendirici bakım ve thread lift çözümleri — hepsi tek bir cesur marka altında.</p>
        </div>
        <!-- 🖼️ GÖRSEL (tekrarlayan ürün showcase kartı görsel deseni): /assets/img/aespio-product-{slug}.jpg (oran 4:3)
             Örn: aespio-product-beta-glukan-mask.jpg, aespio-product-hyaluronic-acid-mask.jpg, aespio-product-feelsoft.jpg, aespio-product-fmc.jpg, aespio-product-lfl-anchor.jpg
             PROMPT: "Modern K-beauty product showcase shot (sheet face mask sachet or PDO thread-lift product), vibrant purple-to-mint gradient backdrop, bright glossy studio lighting, youthful dynamic Korean beauty aesthetic, clean crisp composition, holographic mint and violet accents, fresh and energetic; photorealistic, detailed, high resolution; no text, no logo, no watermark"
             KULLANIM → Her kartın başındaki aspect-ratio:4/3 gradyan+emoji görsel bloğunu ilgili görselle değiştir, örn:
             <img src="/assets/img/aespio-product-beta-glukan-mask.jpg" alt="Grand Aespio Beta-Glukan Mask ürün çekimi" style="width:100%;aspect-ratio:4/3;object-fit:cover;"> -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px;">

          <a href="/klasik-urun-detay" class="aespio-card" style="display:block;background:#fff;border-radius:24px;overflow:hidden;">
            <div style="aspect-ratio:4/3;background:linear-gradient(135deg,#C6BBF7,#8E7DF0) url('/assets/img/aespio-product-beta-glukan-mask.jpg') center/cover no-repeat;"></div>
            <div style="padding:24px;">
              <span style="font-size:11.5px;font-weight:800;color:var(--color-primary,#6C5CE0);text-transform:uppercase;letter-spacing:.8px;">Yatıştırıcı Maske</span>
              <h3 style="font-size:19px;font-weight:900;color:var(--text-main,#221A40);margin:7px 0 9px;">Beta-Glukan Mask</h3>
              <p style="color:var(--text-soft,#6B6088);font-size:14px;line-height:1.6;margin:0 0 14px;font-weight:500;">Beta-glukan ile hassas cildi yatıştıran, onarıcı yeni nesil yüz maskesi.</p>
              <span style="color:var(--color-accent,#21D4B4);font-weight:800;font-size:14px;">İncele →</span>
            </div>
          </a>

          <a href="/klasik-urun-detay" class="aespio-card" style="display:block;background:#fff;border-radius:24px;overflow:hidden;">
            <div style="aspect-ratio:4/3;background:linear-gradient(135deg,#7FE9D8,#2FC4C0) url('/assets/img/aespio-product-hyaluronic-acid-mask.jpg') center/cover no-repeat;"></div>
            <div style="padding:24px;">
              <span style="font-size:11.5px;font-weight:800;color:#0E9E86;text-transform:uppercase;letter-spacing:.8px;">Nemlendirici Maske</span>
              <h3 style="font-size:19px;font-weight:900;color:var(--text-main,#221A40);margin:7px 0 9px;">Hyaluronic Acid Mask</h3>
              <p style="color:var(--text-soft,#6B6088);font-size:14px;line-height:1.6;margin:0 0 14px;font-weight:500;">Hyalüronik asit ile yoğun nem desteği; dolgun, ışıltılı bir cilt hissi.</p>
              <span style="color:var(--color-accent,#21D4B4);font-weight:800;font-size:14px;">İncele →</span>
            </div>
          </a>

          <a href="/klasik-urun-detay" class="aespio-card" style="display:block;background:#fff;border-radius:24px;overflow:hidden;">
            <div style="aspect-ratio:4/3;background:linear-gradient(135deg,#B8AEF5,#6C5CE0) url('/assets/img/aespio-product-feelsoft.jpg') center/cover no-repeat;"></div>
            <div style="padding:24px;">
              <span style="font-size:11.5px;font-weight:800;color:var(--color-primary,#6C5CE0);text-transform:uppercase;letter-spacing:.8px;">İp Askı</span>
              <h3 style="font-size:19px;font-weight:900;color:var(--text-main,#221A40);margin:7px 0 9px;">FeelSoft</h3>
              <p style="color:var(--text-soft,#6B6088);font-size:14px;line-height:1.6;margin:0 0 14px;font-weight:500;">Yumuşak doku desteği için tasarlanmış konforlu ip askı çözümü.</p>
              <span style="color:var(--color-accent,#21D4B4);font-weight:800;font-size:14px;">İncele →</span>
            </div>
          </a>

          <a href="/klasik-urun-detay" class="aespio-card" style="display:block;background:#fff;border-radius:24px;overflow:hidden;">
            <div style="aspect-ratio:4/3;background:linear-gradient(135deg,#9FE7DC,#5BD0C0) url('/assets/img/aespio-product-fmc.jpg') center/cover no-repeat;"></div>
            <div style="padding:24px;">
              <span style="font-size:11.5px;font-weight:800;color:#0E9E86;text-transform:uppercase;letter-spacing:.8px;">İp Askı</span>
              <h3 style="font-size:19px;font-weight:900;color:var(--text-main,#221A40);margin:7px 0 9px;">FMC</h3>
              <p style="color:var(--text-soft,#6B6088);font-size:14px;line-height:1.6;margin:0 0 14px;font-weight:500;">Hassas uygulamalar için ince işçilikli, çok yönlü ip askı ürünü.</p>
              <span style="color:var(--color-accent,#21D4B4);font-weight:800;font-size:14px;">İncele →</span>
            </div>
          </a>

          <a href="/klasik-urun-detay" class="aespio-card" style="display:block;background:#fff;border-radius:24px;overflow:hidden;">
            <div style="aspect-ratio:4/3;background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4)) url('/assets/img/aespio-product-lfl-anchor.jpg') center/cover no-repeat;"></div>
            <div style="padding:24px;">
              <span style="font-size:11.5px;font-weight:800;color:var(--color-primary,#6C5CE0);text-transform:uppercase;letter-spacing:.8px;">Thread Lift</span>
              <h3 style="font-size:19px;font-weight:900;color:var(--text-main,#221A40);margin:7px 0 9px;">LFL Anchor</h3>
              <p style="color:var(--text-soft,#6B6088);font-size:14px;line-height:1.6;margin:0 0 14px;font-weight:500;">Güçlü tutuş için çapalı (anchor) tasarımlı ip askı / thread lift sistemi.</p>
              <span style="color:var(--color-accent,#21D4B4);font-weight:800;font-size:14px;">İncele →</span>
            </div>
          </a>

        </div>
      </div>
    </section>

    
EDKP9S2;
        $p9s3 = <<<'EDKP9S3'
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
          <p style="color:var(--color-primary,#6C5CE0);font-weight:800;letter-spacing:1.6px;text-transform:uppercase;font-size:13px;margin:0 0 12px;">Öne Çıkan · LFL Anchor</p>
          <h2 style="font-size:clamp(28px,4vw,42px);font-weight:900;color:var(--text-main,#221A40);margin:0 0 18px;line-height:1.1;letter-spacing:-.5px;">Thread lift'te <span style="background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));-webkit-background-clip:text;background-clip:text;color:transparent;">çapalı tutuş</span> gücü</h2>
          <p style="color:var(--text-soft,#6B6088);font-size:17px;line-height:1.75;margin:0 0 24px;font-weight:500;">LFL Anchor, çapa (anchor) tasarımıyla dokuda güçlü ve dengeli bir tutuş sağlayacak şekilde geliştirildi. Grand Aespio'nun ip askı serisi — FeelSoft, FMC ve LFL Anchor — farklı endikasyonlar için modern, çok yönlü bir araç seti sunar.</p>
          <ul style="list-style:none;margin:0 0 30px;padding:0;display:grid;gap:13px;">
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#221A40);font-weight:700;"><span style="width:26px;height:26px;border-radius:8px;background:var(--aespio-mint-soft,#DFF8F2);color:#0E9E86;display:grid;place-items:center;font-size:14px;flex-shrink:0;">✓</span> Çapalı (anchor) tasarımla güçlü tutuş</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#221A40);font-weight:700;"><span style="width:26px;height:26px;border-radius:8px;background:var(--aespio-purple-soft,#ECE6FB);color:var(--color-primary,#6C5CE0);display:grid;place-items:center;font-size:14px;flex-shrink:0;">✓</span> Maske + thread tamamlayıcı protokoller</li>
            <li style="display:flex;align-items:center;gap:12px;color:var(--text-main,#221A40);font-weight:700;"><span style="width:26px;height:26px;border-radius:8px;background:var(--aespio-mint-soft,#DFF8F2);color:#0E9E86;display:grid;place-items:center;font-size:14px;flex-shrink:0;">✓</span> Yeni nesil K-beauty üretim kalitesi</li>
          </ul>
          <a href="/klasik-urun-detay" style="display:inline-flex;align-items:center;gap:9px;background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));color:#fff;padding:15px 32px;border-radius:999px;font-weight:800;font-size:15px;box-shadow:0 14px 36px rgba(108,92,224,.32);">LFL Anchor'ı İncele →</a>
        </div>
      </div>
    </section>

    
EDKP9S3;
        $p9s4 = <<<'EDKP9S4'
<!-- GÜVEN NOTU -->

    <section style="padding:0 0 84px;background:#fff;">
      <div class="container">
        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;justify-content:center;background:var(--grad-aespio-soft,linear-gradient(135deg,#ECE6FB,#DFF8F2));border-radius:24px;padding:32px 36px;text-align:center;">
          <span style="width:44px;height:44px;border-radius:13px;background:var(--grad-aespio,linear-gradient(135deg,#6C5CE0,#21D4B4));flex-shrink:0;"></span>
          <p style="margin:0;font-size:clamp(16px,2.4vw,21px);font-weight:700;color:var(--text-main,#221A40);line-height:1.5;">Grand Aespio, <strong style="color:var(--color-primary,#6C5CE0);">Estetik Dermal</strong> resmi distribütörlüğüyle Türkiye'de.</p>
        </div>
      </div>
    </section>

    
EDKP9S4;
        $p9s5 = <<<'EDKP9S5'
<!-- CTA BANDI -->

    <section style="padding:0 0 90px;background:#fff;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:32px;background:linear-gradient(120deg,#6C5CE0 0%, #5A4BD4 45%, #21D4B4 110%);padding:clamp(44px,6vw,76px);text-align:center;color:#fff;">
          <div style="position:absolute;top:-50px;right:-40px;width:220px;height:220px;border-radius:48% 52% 60% 40%/55% 45% 55% 45%;background:rgba(255,255,255,.12);"></div>
          <div style="position:absolute;bottom:-70px;left:-40px;width:260px;height:260px;border-radius:60% 40% 50% 50%/40% 60% 40% 60%;background:rgba(255,255,255,.08);"></div>
          <div style="position:relative;">
            <p style="display:inline-flex;background:rgba(255,255,255,.16);border:1.5px solid rgba(255,255,255,.32);color:#fff;border-radius:999px;padding:8px 18px;font-size:12px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;margin:0 0 20px;">✦ Grand Aespio</p>
            <h2 style="font-size:clamp(28px,4.5vw,46px);font-weight:900;margin:0 0 16px;line-height:1.1;letter-spacing:-.5px;">Grand Aespio ürünleri için<br>bize ulaşın</h2>
            <p style="font-size:18px;opacity:.95;max-width:600px;margin:0 auto 34px;line-height:1.6;font-weight:500;">Maske ve ip askı serisi hakkında ürün bilgisi, fiyat ve eğitim talepleriniz için ekibimiz hazır.</p>
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:17px 38px;border-radius:999px;font-weight:900;font-size:16px;box-shadow:0 16px 40px rgba(37,211,102,.4);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:20px;height:20px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile Yaz</a>
          </div>
        </div>
      </div>
    </section>
EDKP9S5;
        $p10s0 = <<<'EDKP10S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"><style>
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
          <p class="wh-mono" style="display:inline-flex;align-items:center;gap:9px;background:rgba(45,168,255,.10);border:1px solid rgba(45,168,255,.35);color:#9AD6FF;border-radius:999px;padding:8px 16px;font-size:12px;letter-spacing:1.2px;text-transform:uppercase;margin:0 0 26px;"><span aria-hidden="true" style="width:7px;height:7px;border-radius:50%;background:#16E0C8;box-shadow:0 0 8px #16E0C8;"></span>Güney Kore · Medikal Mekatronik</p>
          <h1 class="wh-display" style="font-size:clamp(36px,5.4vw,60px);line-height:1.05;font-weight:700;color:#F4F8FF;margin:0 0 22px;">Mühendislik hassasiyetinde<br><span style="background:linear-gradient(110deg,#2DA8FF 0%,#16E0C8 100%);-webkit-background-clip:text;background-clip:text;color:transparent;">estetik teknolojisi</span></h1>
          <p style="font-size:clamp(16px,2vw,19px);color:#AEBCCC;line-height:1.75;max-width:560px;margin:0 0 34px;">Woorhi Mechatronics Co. Ltd., Kore mühendisliğiyle geliştirilen medikal estetik cihazları üretir. Hassas kontrol, klinik dayanıklılık ve tekrarlanabilir sonuçlar — kliniğinizin teknolojik altyapısı için tasarlandı.</p>
          <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a class="wh-link" href="/klasik-urun-detay" style="display:inline-flex;align-items:center;gap:9px;background:linear-gradient(135deg,#2DA8FF,#16E0C8);color:#0B0E14;padding:15px 30px;border-radius:999px;font-weight:800;font-size:15px;box-shadow:0 0 30px rgba(45,168,255,.45);">Cihazı İncele →</a>
            <a class="wh-link" href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;border:1px solid rgba(255,255,255,.18);padding:15px 28px;border-radius:999px;font-weight:700;font-size:15px;box-shadow:0 10px 30px rgba(37,211,102,.35);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp Danışma</a>
          </div>
          <div class="wh-mono" style="display:flex;gap:26px;flex-wrap:wrap;margin-top:40px;color:#8FA2B6;font-size:13px;">
            <div><div style="color:#16E0C8;font-size:11px;letter-spacing:1px;">ORIGIN</div><div style="color:#E6EDF5;font-weight:500;">Seoul · KR</div></div>
            <div style="width:1px;background:rgba(255,255,255,.12);"></div>
            <div><div style="color:#16E0C8;font-size:11px;letter-spacing:1px;">CLASS</div><div style="color:#E6EDF5;font-weight:500;">Klinik Cihaz</div></div>
            <div style="width:1px;background:rgba(255,255,255,.12);"></div>
            <div><div style="color:#16E0C8;font-size:11px;letter-spacing:1px;">DIST · TR</div><div style="color:#E6EDF5;font-weight:500;">Estetik Dermal</div></div>
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
              <span class="wh-mono" style="font-size:11px;letter-spacing:1.5px;color:#16E0C8;">WOORHI · UNIT-01</span>
              <span style="display:inline-flex;align-items:center;gap:6px;" class="wh-mono"><span style="width:7px;height:7px;border-radius:50%;background:#16E0C8;box-shadow:0 0 10px #16E0C8;"></span><span style="font-size:11px;color:#9AD6FF;">ONLINE</span></span>
            </div>
            <div style="aspect-ratio:4/3;border-radius:16px;background:radial-gradient(120% 120% at 50% 0%,rgba(45,168,255,.22),rgba(11,14,20,.9)) url('/assets/img/woorhi-device.jpg') center/cover no-repeat;border:1px solid rgba(45,168,255,.28);display:grid;place-items:center;position:relative;overflow:hidden;">
              <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(90deg,rgba(45,168,255,.10) 1px,transparent 1px),linear-gradient(rgba(45,168,255,.10) 1px,transparent 1px);background-size:26px 26px;"></div>
            </div>
            <div class="wh-mono" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:18px;font-size:12px;">
              <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);border-radius:10px;padding:10px 12px;"><div style="color:#7E92A8;">PRECISION</div><div style="color:#E6EDF5;">± hassas kontrol</div></div>
              <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);border-radius:10px;padding:10px 12px;"><div style="color:#7E92A8;">BUILD</div><div style="color:#E6EDF5;">KR Engineering</div></div>
            </div>
          </div>
          <div style="position:absolute;bottom:-16px;left:-14px;background:rgba(16,19,26,.92);border:1px solid rgba(45,168,255,.3);border-radius:14px;box-shadow:0 0 24px rgba(45,168,255,.25);padding:12px 16px;display:flex;align-items:center;gap:10px;">
            <span style="width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,#2DA8FF,#16E0C8);box-shadow:0 0 14px rgba(45,168,255,.5);flex-shrink:0;"></span>
            <div><div class="wh-mono" style="font-size:10px;color:#16E0C8;letter-spacing:1px;">RAFFINE</div><div style="font-weight:700;font-size:13px;color:#E6EDF5;">Ana Cihaz Serisi</div></div>
          </div>
        </div>
      </div>
    </section>

    
EDKP10S0;
        $p10s1 = <<<'EDKP10S1'
<!-- 2 · TEKNOLOJİ / MÜHENDİSLİK ŞERİDİ -->

    <section style="background:#10131A;border-top:1px solid rgba(255,255,255,.06);border-bottom:1px solid rgba(255,255,255,.06);">
      <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;padding:40px 0;">
        <div style="display:flex;align-items:center;gap:14px;background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.10);border-radius:16px;padding:18px 20px;backdrop-filter:blur(6px);">
          <span style="width:32px;height:32px;border-radius:9px;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.55),rgba(11,14,20,.4));border:1px solid rgba(45,168,255,.4);box-shadow:0 0 10px rgba(45,168,255,.4);flex-shrink:0;"></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">Hassas Kontrol</div><div class="wh-mono" style="font-size:11px;color:#8FA2B6;">precision control</div></div>
        </div>
        <div style="display:flex;align-items:center;gap:14px;background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.10);border-radius:16px;padding:18px 20px;backdrop-filter:blur(6px);">
          <span style="width:32px;height:32px;border-radius:9px;background:radial-gradient(120% 120% at 30% 20%,rgba(22,224,200,.55),rgba(11,14,20,.4));border:1px solid rgba(22,224,200,.4);box-shadow:0 0 10px rgba(22,224,200,.4);flex-shrink:0;"></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">Kore Mühendisliği</div><div class="wh-mono" style="font-size:11px;color:#8FA2B6;">made in korea</div></div>
        </div>
        <div style="display:flex;align-items:center;gap:14px;background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.10);border-radius:16px;padding:18px 20px;backdrop-filter:blur(6px);">
          <span style="width:32px;height:32px;border-radius:9px;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.55),rgba(11,14,20,.4));border:1px solid rgba(45,168,255,.4);box-shadow:0 0 10px rgba(45,168,255,.4);flex-shrink:0;"></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">CE Uyumlu</div><div class="wh-mono" style="font-size:11px;color:#8FA2B6;">ce compliant</div></div>
        </div>
        <div style="display:flex;align-items:center;gap:14px;background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.10);border-radius:16px;padding:18px 20px;backdrop-filter:blur(6px);">
          <span style="width:32px;height:32px;border-radius:9px;background:radial-gradient(120% 120% at 30% 20%,rgba(22,224,200,.55),rgba(11,14,20,.4));border:1px solid rgba(22,224,200,.4);box-shadow:0 0 10px rgba(22,224,200,.4);flex-shrink:0;"></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">Klinik Cihaz</div><div class="wh-mono" style="font-size:11px;color:#8FA2B6;">clinical grade</div></div>
        </div>
      </div>
    </section>

    
EDKP10S1;
        $p10s2 = <<<'EDKP10S2'
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
              <div class="wh-mono" style="font-size:12px;letter-spacing:2px;color:#16E0C8;">RAFFINE · DEVICE</div>
            </div>
          </div>
        </div>
        <!-- sağ içerik -->
        <div style="flex:1 1 420px;">
          <p class="wh-mono" style="display:inline-block;color:#16E0C8;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 14px;">// Ana Ürün</p>
          <h2 class="wh-display" style="font-size:clamp(30px,4.4vw,46px);font-weight:700;color:#F4F8FF;margin:0 0 18px;line-height:1.1;">Raffine</h2>
          <p style="color:#AEBCCC;font-size:17px;line-height:1.75;margin:0 0 28px;max-width:520px;">Woorhi'nin amiral gemisi medikal estetik cihazı. Mekatronik kontrol mimarisi, kararlı güç yönetimi ve uygulama tekrarlanabilirliği üzerine kurulu; klinik kullanım için dayanıklı bir gövde ve sezgisel arayüzle tasarlandı.</p>

          <!-- monospace spec listesi -->
          <div class="wh-mono" style="background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.02));border:1px solid rgba(45,168,255,.22);border-radius:16px;padding:6px 0;margin:0 0 30px;max-width:520px;backdrop-filter:blur(6px);">
            <div style="display:flex;justify-content:space-between;gap:14px;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,.07);font-size:13px;"><span style="color:#7E92A8;">[ TİP ]</span><span style="color:#E6EDF5;">Medikal estetik mekatronik cihaz</span></div>
            <div style="display:flex;justify-content:space-between;gap:14px;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,.07);font-size:13px;"><span style="color:#7E92A8;">[ UYGULAMA ]</span><span style="color:#E6EDF5;">Yüz & vücut profesyonel bakım</span></div>
            <div style="display:flex;justify-content:space-between;gap:14px;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,.07);font-size:13px;"><span style="color:#7E92A8;">[ KONTROL ]</span><span style="color:#E6EDF5;">Hassas dijital parametre yönetimi</span></div>
            <div style="display:flex;justify-content:space-between;gap:14px;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,.07);font-size:13px;"><span style="color:#7E92A8;">[ MENŞE ]</span><span style="color:#E6EDF5;">Güney Kore mühendisliği</span></div>
            <div style="display:flex;justify-content:space-between;gap:14px;padding:12px 20px;font-size:13px;"><span style="color:#7E92A8;">[ KULLANIM ]</span><span style="color:#16E0C8;">Klinik / profesyonel</span></div>
          </div>

          <a class="wh-link" href="/klasik-urun-detay" style="display:inline-flex;align-items:center;gap:9px;background:linear-gradient(135deg,#2DA8FF,#16E0C8);color:#0B0E14;padding:15px 30px;border-radius:999px;font-weight:800;font-size:15px;box-shadow:0 0 30px rgba(45,168,255,.45);">Raffine Detayları →</a>
        </div>
      </div>
    </section>

    
EDKP10S2;
        $p10s3 = <<<'EDKP10S3'
<!-- 4 · NEDEN WOORHI -->

    <section style="padding:92px 0;background:#10131A;border-top:1px solid rgba(255,255,255,.06);">
      <div class="container">
        <div style="text-align:center;max-width:640px;margin:0 auto 52px;">
          <p class="wh-mono" style="color:#16E0C8;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 12px;">// Neden Woorhi</p>
          <h2 class="wh-display" style="font-size:clamp(28px,4vw,40px);font-weight:700;color:#F4F8FF;margin:0;line-height:1.15;">Mühendisliğin estetikle buluştuğu nokta</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:22px;">
          <!-- 🖼️ KART GÖRSELLERİ (tekrarlayan grid) — her kart ikonunun yerine opsiyonel görsel.
               Dosya adı deseni: /assets/img/woorhi-feature-1.jpg, woorhi-feature-2.jpg, woorhi-feature-3.jpg (oran 1:1)
               PROMPT (örnek/temsili): "Photorealistic close-up macro detail of high-tech medical aesthetic device component, glowing neon blue and cyan light, dark studio background, brushed metal and glass texture, futuristic engineering, dramatic lighting, no text, no logo, no watermark, high resolution"
               DEĞİŞTİR (kart ikonu <div>...</div> yerine) → <img src="/assets/img/woorhi-feature-1.jpg" alt="Woorhi cihaz detayı — neon mavi-cyan ışıklı yüksek teknoloji bileşeni" style="width:54px;height:54px;border-radius:15px;object-fit:cover;"> -->
          <div style="background:linear-gradient(160deg,rgba(255,255,255,.06),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:34px 30px;backdrop-filter:blur(8px);box-shadow:0 14px 40px rgba(0,0,0,.35);">
            <div style="width:54px;height:54px;border-radius:15px;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.5),rgba(11,14,20,.4)) url('/assets/img/woorhi-feature-1.jpg') center/cover no-repeat;border:1px solid rgba(45,168,255,.4);margin-bottom:20px;box-shadow:0 0 22px rgba(45,168,255,.3);"></div>
            <h3 class="wh-display" style="font-size:20px;font-weight:600;color:#F4F8FF;margin:0 0 10px;">İleri Teknoloji</h3>
            <p style="color:#AEBCCC;line-height:1.7;margin:0;font-size:15px;">Mekatronik kontrol mimarisi ve hassas parametre yönetimiyle tekrarlanabilir, kontrollü uygulamalar.</p>
          </div>
          <div style="background:linear-gradient(160deg,rgba(255,255,255,.06),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:34px 30px;backdrop-filter:blur(8px);box-shadow:0 14px 40px rgba(0,0,0,.35);">
            <div style="width:54px;height:54px;border-radius:15px;background:radial-gradient(120% 120% at 30% 20%,rgba(22,224,200,.5),rgba(11,14,20,.4)) url('/assets/img/woorhi-feature-2.jpg') center/cover no-repeat;border:1px solid rgba(22,224,200,.4);margin-bottom:20px;box-shadow:0 0 22px rgba(22,224,200,.3);"></div>
            <h3 class="wh-display" style="font-size:20px;font-weight:600;color:#F4F8FF;margin:0 0 10px;">Klinik Dayanıklılık</h3>
            <p style="color:#AEBCCC;line-height:1.7;margin:0;font-size:15px;">Yoğun klinik kullanım için tasarlanmış sağlam gövde, kararlı güç yönetimi ve uzun ömürlü bileşenler.</p>
          </div>
          <div style="background:linear-gradient(160deg,rgba(255,255,255,.06),rgba(255,255,255,.02));border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:34px 30px;backdrop-filter:blur(8px);box-shadow:0 14px 40px rgba(0,0,0,.35);">
            <div style="width:54px;height:54px;border-radius:15px;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.5),rgba(11,14,20,.4)) url('/assets/img/woorhi-feature-3.jpg') center/cover no-repeat;border:1px solid rgba(45,168,255,.4);margin-bottom:20px;box-shadow:0 0 22px rgba(45,168,255,.3);"></div>
            <h3 class="wh-display" style="font-size:20px;font-weight:600;color:#F4F8FF;margin:0 0 10px;">Yerel Destek</h3>
            <p style="color:#AEBCCC;line-height:1.7;margin:0;font-size:15px;">Estetik Dermal güvencesiyle Türkiye'de kurulum, uygulamalı eğitim ve kesintisiz teknik servis desteği.</p>
          </div>
        </div>
      </div>
    </section>

    
EDKP10S3;
        $p10s4 = <<<'EDKP10S4'
<!-- 5 · CTA BANDI (neon gradient koyu) -->

    <section style="padding:30px 0 92px;background:#0B0E14;">
      <div class="container">
        <div style="position:relative;overflow:hidden;border-radius:28px;background:linear-gradient(120deg,#0E2A44 0%,#0B1C2C 45%,#0A2A2A 100%);border:1px solid rgba(45,168,255,.3);padding:clamp(40px,6vw,72px);text-align:center;box-shadow:0 0 60px rgba(45,168,255,.18);">
          <div aria-hidden="true" style="position:absolute;inset:0;background-image:radial-gradient(600px 300px at 15% 0%, rgba(45,168,255,.4), transparent 60%),radial-gradient(600px 300px at 90% 100%, rgba(22,224,200,.3), transparent 60%);"></div>
          <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:42px 42px;mask-image:radial-gradient(700px 300px at 50% 50%,#000,transparent 75%);"></div>
          <div style="position:relative;">
            <p class="wh-mono" style="color:#9AD6FF;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 14px;">// Demo & Teklif</p>
            <h2 class="wh-display" style="font-size:clamp(26px,4vw,40px);font-weight:700;margin:0 0 14px;line-height:1.15;color:#F4F8FF;">Woorhi cihazları için demo / teklif alın</h2>
            <p style="font-size:18px;color:#C7D3E2;max-width:600px;margin:0 auto 32px;line-height:1.65;">Raffine ve Woorhi cihaz serisi hakkında detaylı bilgi, demo planlaması ve fiyat teklifi için Estetik Dermal ekibine ulaşın.</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a class="wh-link" href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;padding:15px 32px;border-radius:999px;font-weight:800;font-size:15px;box-shadow:0 0 30px rgba(37,211,102,.5);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile Yaz</a>
              <a class="wh-link" href="/klasik-urun-detay" style="background:rgba(255,255,255,.06);color:#E6EDF5;border:1px solid rgba(255,255,255,.22);padding:15px 32px;border-radius:999px;font-weight:700;font-size:15px;">Cihazı İncele</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDKP10S4;
        $p11s0 = <<<'EDKP11S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Playfair+Display:wght@500;600;700&display=swap"><style>
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
            Premium Enjeksiyon Sistemleri
          </p>
          <h1 class="mi-serif" style="font-size:clamp(38px,5.4vw,66px);line-height:1.06;font-weight:600;color:var(--color-secondary,#F7F3EA);margin:0 0 26px;letter-spacing:.3px;">
            Hassasiyetin ve<br><span style="font-style:italic;background:var(--mi-gold-grad,linear-gradient(135deg,#C9A24B,#E3C97E));-webkit-background-clip:text;background-clip:text;color:transparent;">zarafetin</span> buluşması
          </h1>
          <p style="font-size:clamp(16px,1.6vw,19px);color:rgba(247,243,234,.72);line-height:1.85;max-width:540px;margin:0 0 40px;">
            Mi Medical Innovation; premium mezoterapi ve enjeksiyon sistemlerinde inovasyonu zarafetle buluşturur. Her ayrıntısı, hekimin elinde kusursuz kontrol ve hastada üst düzey konfor için tasarlanmıştır.
          </p>
          <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <a href="/klasik-urun-detay" style="display:inline-flex;align-items:center;gap:9px;background:var(--mi-gold-grad,linear-gradient(135deg,#C9A24B,#E3C97E));color:#141414;padding:16px 32px;border-radius:999px;font-weight:700;font-size:15px;box-shadow:0 14px 34px rgba(201,162,75,.26);">Pistor Eliance'ı Keşfet →</a>
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#25D366;color:#fff;border:1px solid rgba(255,255,255,.18);padding:16px 30px;border-radius:999px;font-weight:600;font-size:15px;box-shadow:0 10px 30px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>Ayrıcalıklı Danışmanlık</a>
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
                <div class="mi-serif" style="font-size:19px;color:var(--color-secondary,#F7F3EA);">Pistor Eliance</div>
                <div style="font-size:11.5px;letter-spacing:2px;text-transform:uppercase;color:var(--color-accent,#E3C97E);margin-top:3px;">Mi Medical Innovation</div>
              </div>
              <span aria-hidden="true" style="width:28px;height:28px;border-radius:50%;background:var(--mi-gold-grad,linear-gradient(135deg,#C9A24B,#E3C97E));flex-shrink:0;"></span>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDKP11S0;
        $p11s1 = <<<'EDKP11S1'
<!-- 2 · İNCE ALTIN AYRAÇLI DEĞER ŞERİDİ -->

    <section style="background:var(--mi-black-soft,#1d1d1d);border-top:1px solid var(--mi-gold-line,rgba(201,162,75,.32));border-bottom:1px solid var(--mi-gold-line,rgba(201,162,75,.32));">
      <!-- 🖼️ DEĞER KARTI GÖRSELLERİ (tekrarlayan grid) — her kartın ◇/◈ glifinin yerine opsiyonel görsel.
           Dosya adı deseni: /assets/img/mi-medical-value-1.jpg, mi-medical-value-2.jpg, mi-medical-value-3.jpg (oran 1:1)
           PROMPT (örnek/temsili): "Photorealistic elegant macro detail of premium medical injection device feature, sophisticated black background with soft golden light and refined gold reflections, minimal luxury aesthetic, polished premium materials, dramatic studio lighting, no text, no logo, no watermark, high resolution"
           DEĞİŞTİR (◇ glif <div>...</div> yerine) → <img src="/assets/img/mi-medical-value-1.jpg" alt="Mi Medical premium cihaz detayı — siyah zeminde altın ışıklı lüks makro çekim" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin:0 auto 12px;display:block;"> -->
      <div class="container" style="display:flex;flex-wrap:wrap;align-items:stretch;padding:54px 0;gap:0;">
        <div style="flex:1 1 240px;padding:8px 36px;text-align:center;">
          <div style="width:60px;height:60px;border-radius:50%;margin:0 auto 12px;display:grid;place-items:center;font-size:30px;color:var(--color-primary,#C9A24B);background:url('/assets/img/mi-medical-value-1.jpg') center/cover no-repeat;">◇</div>
          <h3 class="mi-serif" style="font-size:23px;font-weight:600;color:var(--color-secondary,#F7F3EA);margin:0 0 8px;">Hassas Dozaj</h3>
          <p style="color:rgba(247,243,234,.62);font-size:14.5px;line-height:1.7;margin:0;">Mikrolitre düzeyinde kontrol ile her uygulamada tutarlı, öngörülebilir sonuç.</p>
        </div>
        <div aria-hidden="true" style="width:1px;background:var(--mi-gold-line,rgba(201,162,75,.32));align-self:stretch;"></div>
        <div style="flex:1 1 240px;padding:8px 36px;text-align:center;">
          <div style="width:60px;height:60px;border-radius:50%;margin:0 auto 12px;display:grid;place-items:center;font-size:30px;color:var(--color-primary,#C9A24B);background:url('/assets/img/mi-medical-value-2.jpg') center/cover no-repeat;">◈</div>
          <h3 class="mi-serif" style="font-size:23px;font-weight:600;color:var(--color-secondary,#F7F3EA);margin:0 0 8px;">Premium Malzeme</h3>
          <p style="color:rgba(247,243,234,.62);font-size:14.5px;line-height:1.7;margin:0;">Dengeli ergonomi ve uzun ömürlü, üst sınıf bileşenlerle işlenmiş cihaz estetiği.</p>
        </div>
        <div aria-hidden="true" style="width:1px;background:var(--mi-gold-line,rgba(201,162,75,.32));align-self:stretch;"></div>
        <div style="flex:1 1 240px;padding:8px 36px;text-align:center;">
          <div style="width:60px;height:60px;border-radius:50%;margin:0 auto 12px;display:grid;place-items:center;font-size:30px;color:var(--color-primary,#C9A24B);background:url('/assets/img/mi-medical-value-3.jpg') center/cover no-repeat;">◇</div>
          <h3 class="mi-serif" style="font-size:23px;font-weight:600;color:var(--color-secondary,#F7F3EA);margin:0 0 8px;">Klinik Güven</h3>
          <p style="color:rgba(247,243,234,.62);font-size:14.5px;line-height:1.7;margin:0;">Hekimlerin tercihi; güvenli, konforlu ve tekrarlanabilir profesyonel uygulama.</p>
        </div>
      </div>
    </section>

    
EDKP11S1;
        $p11s2 = <<<'EDKP11S2'
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
            <div class="mi-serif" style="font-size:26px;font-weight:600;">Pistor Eliance</div>
            <div style="font-size:11.5px;letter-spacing:2.5px;text-transform:uppercase;color:var(--color-accent,#E3C97E);margin-top:8px;">Premium Enjeksiyon Sistemi</div>
          </div>
        </div>
        <!-- içerik -->
        <div style="flex:1 1 420px;">
          <p style="display:inline-flex;align-items:center;gap:12px;color:#9a7d33;font-size:12px;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin:0 0 18px;">
            <span aria-hidden="true" style="display:inline-block;width:30px;height:1px;background:var(--color-primary,#C9A24B);"></span>
            Öne Çıkan Sistem
          </p>
          <h2 class="mi-serif" style="font-size:clamp(30px,4.4vw,48px);font-weight:600;color:var(--mi-ink,#2a2622);margin:0 0 20px;line-height:1.12;">Pistor Eliance</h2>
          <p style="color:var(--mi-ink-soft,#6c6258);font-size:17px;line-height:1.85;margin:0 0 30px;max-width:520px;">
            Premium mezoterapi tabancası ve enjeksiyon sistemi; hassas dozaj kontrolü, sessiz mekanizması ve ergonomik dengesiyle hekime kusursuz hâkimiyet, hastaya ise belirgin biçimde daha konforlu bir uygulama deneyimi sunar.
          </p>
          <ul style="list-style:none;margin:0 0 36px;padding:0;display:grid;gap:15px;">
            <li style="display:flex;align-items:flex-start;gap:14px;color:var(--mi-ink,#2a2622);font-size:16px;font-weight:500;"><span aria-hidden="true" style="color:var(--color-primary,#C9A24B);font-size:18px;line-height:1.4;">✓</span> Ayarlanabilir, mikro hassasiyetli dozaj kontrolü</li>
            <li style="display:flex;align-items:flex-start;gap:14px;color:var(--mi-ink,#2a2622);font-size:16px;font-weight:500;"><span aria-hidden="true" style="color:var(--color-primary,#C9A24B);font-size:18px;line-height:1.4;">✓</span> Dengeli, ergonomik tutuş ve düşük titreşim</li>
            <li style="display:flex;align-items:flex-start;gap:14px;color:var(--mi-ink,#2a2622);font-size:16px;font-weight:500;"><span aria-hidden="true" style="color:var(--color-primary,#C9A24B);font-size:18px;line-height:1.4;">✓</span> Hasta konforunu artıran konforlu, hızlı uygulama</li>
            <li style="display:flex;align-items:flex-start;gap:14px;color:var(--mi-ink,#2a2622);font-size:16px;font-weight:500;"><span aria-hidden="true" style="color:var(--color-primary,#C9A24B);font-size:18px;line-height:1.4;">✓</span> Premium malzeme ve uzun ömürlü cihaz kalitesi</li>
          </ul>
          <a href="/klasik-urun-detay" style="display:inline-flex;align-items:center;gap:9px;background:var(--mi-black,#141414);color:var(--color-accent,#E3C97E);border:1px solid var(--color-primary,#C9A24B);padding:15px 30px;border-radius:999px;font-weight:600;font-size:15px;">Ürün Detayını Gör →</a>
        </div>
      </div>
    </section>

    
EDKP11S2;
        $p11s3 = <<<'EDKP11S3'
<!-- 4 · MARKA VAADİ (fildişi, zarif) -->

    <section style="background:var(--mi-ivory-soft,#FBF8F0);padding:100px 0;border-top:1px solid rgba(201,162,75,.18);">
      <div class="container" style="max-width:780px;margin:0 auto;text-align:center;">
        <div aria-hidden="true" style="width:46px;height:1px;background:var(--color-primary,#C9A24B);margin:0 auto 30px;"></div>
        <p style="color:#9a7d33;font-size:12px;font-weight:700;letter-spacing:3.5px;text-transform:uppercase;margin:0 0 24px;">Marka Vaadi</p>
        <p class="mi-serif-light" style="font-size:clamp(24px,3.4vw,36px);line-height:1.5;color:var(--mi-ink,#2a2622);margin:0;font-weight:500;">
          İnovasyon ile zarafetin aynı çizgide buluştuğuna inanıyoruz. Mi Medical Innovation, en ileri mühendisliği rafine bir tasarım diliyle harmanlayarak, profesyonel uygulamanın her anına <span style="font-style:italic;color:#a8842f;">sessiz bir lüks</span> kazandırır.
        </p>
      </div>
    </section>

    
EDKP11S3;
        $p11s4 = <<<'EDKP11S4'
<!-- 5 · CTA BANDI (siyah + altın) -->

    <section style="background:linear-gradient(180deg,#181715 0%, #141414 100%);padding:90px 0;border-top:1px solid var(--mi-gold-line,rgba(201,162,75,.32));">
      <div class="container">
        <div style="position:relative;overflow:hidden;border:1px solid var(--mi-gold-line,rgba(201,162,75,.32));border-radius:24px;padding:clamp(44px,6vw,76px);text-align:center;background:radial-gradient(700px 360px at 50% -20%, rgba(201,162,75,.14), transparent 60%);">
          <div aria-hidden="true" style="width:46px;height:1px;background:var(--color-primary,#C9A24B);margin:0 auto 26px;"></div>
          <h2 class="mi-serif" style="font-size:clamp(28px,4.4vw,44px);font-weight:600;color:var(--color-secondary,#F7F3EA);margin:0 0 18px;line-height:1.15;">Mi Medical çözümleri için<br>ayrıcalıklı danışmanlık</h2>
          <p style="font-size:17px;color:rgba(247,243,234,.7);max-width:560px;margin:0 auto 36px;line-height:1.75;">Pistor Eliance ve premium enjeksiyon sistemleri hakkında ürün, fiyat ve uygulama bilgisi için Estetik Dermal ekibiyle iletişime geçin.</p>
          <a href="https://wa.me/905426205100" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:10px;background:#25D366;color:#fff;padding:16px 36px;border-radius:999px;font-weight:700;font-size:15px;box-shadow:0 14px 34px rgba(37,211,102,.3);"><svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:19px;height:19px;flex-shrink:0;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>WhatsApp ile İletişime Geç</a>
        </div>
      </div>
    </section>
EDKP11S4;

        $pages = [
            ['slug'=>'klasik','title'=>'Ana Sayfa (Klasik)','sort_order'=>1,'show_in_menu'=>true,'sections'=>[
                ['HERO (split)', $p0s0],
                ['TRUST STATS', $p0s1],
                ['BRAND SHOWCASE', $p0s2],
                ['FEATURED PRODUCTS', $p0s3],
                ['CATEGORY GRID', $p0s4],
                ['ABOUT TEASER', $p0s5],
                ['TRAINING / SUPPORT', $p0s6],
                ['CTA', $p0s7]
            ]],
            ['slug'=>'klasik-hakkimizda','title'=>'Hakkımızda','sort_order'=>2,'show_in_menu'=>true,'sections'=>[
                ['PAGE HERO (compact başlık bandı)', $p1s0],
                ['ABOUT STORY (2 kolon split)', $p1s1],
                ['VİZYON & MİSYON (2 kart)', $p1s2],
                ['STATS BAR', $p1s3],
                ['PORTFOLIO BRANDS', $p1s4],
                ['TRAINING / SUPPORT', $p1s5],
                ['CTA', $p1s6]
            ]],
            ['slug'=>'klasik-urunler','title'=>'Ürünler','sort_order'=>3,'show_in_menu'=>true,'sections'=>[
                ['PAGE HERO (compact)', $p2s0],
                ['FILTER CHIPS (görsel, statik)', $p2s1],
                ['CATEGORY GRID (14 kategori)', $p2s2],
                ['PRODUCT GRID', $p2s3],
                ['BİLGİ ŞERİDİ', $p2s4],
                ['CTA', $p2s5]
            ]],
            ['slug'=>'klasik-urun-detay','title'=>'RRS® HA Long Lasting','sort_order'=>99,'show_in_menu'=>false,'sections'=>[
                ['PAGE HERO (compact) + BREADCRUMB', $p3s0],
                ['PRODUCT DETAIL (2 kolon)', $p3s1],
                ['PRODUCT DESCRIPTION (rich-text)', $p3s2],
                ['RELATED PRODUCTS', $p3s3],
                ['CTA (full)', $p3s4]
            ]],
            ['slug'=>'klasik-markalar','title'=>'Markalar','sort_order'=>4,'show_in_menu'=>true,'sections'=>[
                ['PAGE HERO (compact)', $p4s0],
                ['BRAND CARDS', $p4s1],
                ['KISA BANT', $p4s2],
                ['CTA', $p4s3]
            ]],
            ['slug'=>'klasik-etkinlikler','title'=>'Kongre & Etkinlikler','sort_order'=>5,'show_in_menu'=>true,'sections'=>[
                ['PAGE HERO (compact)', $p5s0],
                ['EVENT GRID', $p5s1],
                ['INFO BLOCK', $p5s2],
                ['CTA (full)', $p5s3]
            ]],
            ['slug'=>'klasik-iletisim','title'=>'İletişim','sort_order'=>6,'show_in_menu'=>true,'sections'=>[
                ['PAGE HERO (compact)', $p6s0],
                ['CONTACT SPLIT', $p6s1],
                ['CONTACT FORM (KVKK\'lı)', $p6s2]
            ]],
            ['slug'=>'klasik-marka-skintech','title'=>'Skin Tech Pharma Group','sort_order'=>101,'show_in_menu'=>false,'sections'=>[
                ['HERO (split)', $p7s0],
                ['GÜVEN / KREDİBİLİTE ŞERİDİ', $p7s1],
                ['ÜRÜN AİLELERİ', $p7s2],
                ['RRS SPOTLIGHT', $p7s3],
                ['EĞİTİM & DESTEK NOTU', $p7s4],
                ['CTA BANDI', $p7s5]
            ]],
            ['slug'=>'klasik-marka-seffiline','title'=>'Seffiline','sort_order'=>102,'show_in_menu'=>false,'sections'=>[
                ['1. HERO', $p8s0],
                ['2. MARKA FELSEFESİ', $p8s1],
                ['3. 4 ÜRÜN AİLESİ', $p8s2],
                ['4. ÖNE ÇIKAN: SeffiHair (editoryal split)', $p8s3],
                ['5. GÜVEN / KALİTE NOTU', $p8s4],
                ['6. CTA BANDI', $p8s5]
            ]],
            ['slug'=>'klasik-marka-aespio','title'=>'Grand Aespio','sort_order'=>103,'show_in_menu'=>false,'sections'=>[
                ['HERO', $p9s0],
                ['STAT / ÖZELLİK ŞERİDİ', $p9s1],
                ['ÜRÜN SHOWCASE', $p9s2],
                ['ÖNE ÇIKAN SPLIT', $p9s3],
                ['GÜVEN NOTU', $p9s4],
                ['CTA BANDI', $p9s5]
            ]],
            ['slug'=>'klasik-marka-woorhi','title'=>'Woorhi Mechatronics','sort_order'=>104,'show_in_menu'=>false,'sections'=>[
                ['1 · BESPOKE KOYU HERO', $p10s0],
                ['2 · TEKNOLOJİ / MÜHENDİSLİK ŞERİDİ', $p10s1],
                ['3 · RAFFINE CİHAZ SPOTLIGHT', $p10s2],
                ['4 · NEDEN WOORHI', $p10s3],
                ['5 · CTA BANDI (neon gradient koyu)', $p10s4]
            ]],
            ['slug'=>'klasik-marka-mi-medical','title'=>'Mi Medical Innovation','sort_order'=>105,'show_in_menu'=>false,'sections'=>[
                ['1 · BESPOKE PREMIUM HERO', $p11s0],
                ['2 · İNCE ALTIN AYRAÇLI DEĞER ŞERİDİ', $p11s1],
                ['3 · PISTOR ELIANCE SPOTLIGHT (fildişi)', $p11s2],
                ['4 · MARKA VAADİ (fildişi, zarif)', $p11s3],
                ['5 · CTA BANDI (siyah + altın)', $p11s4]
            ]],
        ];
        foreach ($pages as $p) {
            $bodyBlocks=[];
            foreach($p['sections'] as $k=>$sec){
                $bodyBlocks[]=$this->blk('b_body_'.$k,'content-block','free-html',$cb->id,$sec[1],$k+1);
            }
            Page::updateOrCreate(['slug'=>$p['slug'],'language_id'=>$langId],
                ['title'=>$p['title'],'status'=>'published','show_in_menu'=>$p['show_in_menu'],
                 'sort_order'=>$p['sort_order'],'show_breadcrumb'=>true,
                 'sections_json'=>['version'=>2,'regions'=>[
                     'header'=>[$this->regRow('header',[$this->blk('b_header','header','estetikdermal-header',$hid,$hdr,1)])],
                     'body'  =>[$this->regRow('body',$bodyBlocks)],
                     'footer'=>[$this->regRow('footer',[$this->blk('b_footer','footer','estetikdermal-footer',$fid,$ftr,1)])],
                 ]]]
            );
        }

        // ---- Site ayarları (idempotent; her iki tema da kullanır) ----
        $settings = [
            ['site.title','Estetik Dermal','general'],
            ['site.footer_text','© 2026 Estetik Dermal. Tüm hakları saklıdır.','general'],
            ['site.logo','','general'],
            ['contact.phone','0 256 612 18 13','contact'],
            ['contact.email','info@estetikdermal.com','contact'],
            ['contact.address','Türkmen Mah. Turgut Özel Bulvarı Ada Modern A Blok No 83/3A Kuşadası/Aydın','contact'],
            ['social.whatsapp','905426205100','social'],
            ['social.instagram','https://www.instagram.com/estetikdermal/','social'],
        ];
        foreach ($settings as $s) {
            SiteSetting::updateOrCreate(['key'=>$s[0]],['value'=>$s[1],'group'=>$s[2],'type'=>'text']);
        }

        $this->command?->info('Tema 1 (Klasik): '.count($pages).' sayfa /klasik/ önekiyle kuruldu.');
    }
}
