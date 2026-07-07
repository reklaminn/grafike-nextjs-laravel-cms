<?php

namespace Database\Seeders;

use App\Models\SectionTemplate;
use App\Models\Theme;
use Illuminate\Database\Seeder;

/**
 * Boblanlı Yapı — CMS chrome (central, tenant 'boblanliyapi').
 * OtelVatan/Homeland deseni: header/footer her sayfanın sections_json'ına html_override gömülür.
 *
 * Tek-sayfa site → header + tüm bölümler + footer aynı 'home' sayfasında. Header'ın <style>'ı
 * TÜM paylaşılan CSS'i (reveal, counter, shrink, svc-card, gal, btn-slide, .field, wa-pulse …)
 * ve :root token'larını taşır; alan bölümleri (FieldChrome) bu class'ları kullanır. Footer'ın
 * <script>'i reveal/counter/shrink IntersectionObserver'larını çalıştırır.
 * Mobil menü: saf CSS checkbox-hack (JS yok). Renk/font değişimi = buradaki :root.
 */
class BoblanliChromeSeeder extends Seeder
{
    private const TENANT_ID = 'boblanliyapi';
    private const PHONE = '+905326576271';
    private const WA    = '905326576271';

    public function run(): void
    {
        $theme = Theme::where('slug', 'boblanli')->where('tenant_id', self::TENANT_ID)->first()
              ?? Theme::where('slug', 'boblanli')->first();
        if (! $theme) { $this->command?->warn('boblanli teması yok — önce BoblanliThemeSeeder.'); return; }

        $blocks = [
            ['type'=>'header','variation'=>'boblanli-header','name'=>'Boblanlı Yapı / Header',
             'html'=>self::headerHtml(),'schema'=>[],'content'=>[]],
            ['type'=>'footer','variation'=>'boblanli-footer','name'=>'Boblanlı Yapı / Footer',
             'html'=>self::footerHtml(),'schema'=>[],'content'=>[]],
            ['type'=>'content-block','variation'=>'free-html','name'=>'İçerik Bloğu / Serbest HTML',
             'html'=>'{{{html}}}','schema'=>['html'=>['type'=>'html','label'=>'Serbest HTML']],'content'=>['html'=>'']],
        ];

        foreach ($blocks as $b) {
            SectionTemplate::updateOrCreate(
                ['theme_id'=>$theme->id,'type'=>$b['type'],'variation'=>$b['variation']],
                ['tenant_id'=>self::TENANT_ID,'module'=>null,'name'=>$b['name'],'render_mode'=>'html',
                 'html_template'=>$b['html'],'schema_json'=>$b['schema'],
                 'default_content_json'=>$b['content'],'is_active'=>true]
            );
        }
        $this->command?->info('BoblanliChromeSeeder: header + footer + content-block kuruldu.');
    }

    public static function headerHtml(): string
    {
        return <<<'BLHDR'
<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

/* ============================================================================
   Boblanlı Yapı — Design Tokens (docs/design_handoff_boblanli_yapi)
   :root hem iraspa-cms hem paylaşılan component'lerin (form-section vb.) --color-*
   değişkenlerini besler. Renk/font değişimi için SADECE burayı düzenle.
   ============================================================================ */
:root{
  --ac:#E63329; --ac-d:#c9271f; --ink:#1A1A1A;
  --color-primary:#E63329; --color-secondary:#F4F4F4; --color-accent:#c9271f;
  --color-bg:#ffffff; --color-surface:#ffffff;
  --color-heading:#1A1A1A; --color-text:#1A1A1A; --color-text-soft:#565656;
  --color-border:#e9e9e9; --color-error:#dc2626;
  --font-heading:'Space Grotesk','Inter',sans-serif; --font-body:'Inter',system-ui,sans-serif;
  --radius-card:0px; --radius-button:0px; --container-width:1220px;
}
*,*::before,*::after{box-sizing:border-box}
html{-webkit-text-size-adjust:100%;scroll-behavior:smooth}
html,body{margin:0;padding:0;background:#fff;color:var(--ink,#1A1A1A);overflow-x:hidden;
  font-family:var(--font-body,'Inter'),system-ui,sans-serif;line-height:1.65;-webkit-font-smoothing:antialiased}
a{color:var(--ac,#E63329);text-decoration:none} a:hover{color:var(--ac-d,#c9271f)}
h1,h2,h3,h4{font-family:var(--font-heading,'Space Grotesk'),sans-serif;margin:0;line-height:1.18}
p{margin:0} img{max-width:100%;display:block} button{font:inherit;cursor:pointer}
input,textarea,select,button{font-family:inherit}
.bl-container{width:min(100% - 40px,1220px);margin:0 auto}

/* scroll reveal */
[data-reveal]{opacity:0;transform:translateY(30px);transition:opacity .7s cubic-bezier(.22,.61,.36,1),transform .7s cubic-bezier(.22,.61,.36,1)}
[data-reveal].in{opacity:1;transform:none}

/* header shrink */
.site-head{transition:padding .3s ease,box-shadow .3s ease,background .3s ease}
.site-head.shrink{box-shadow:0 6px 24px rgba(0,0,0,.10)}
.site-head.shrink .head-inner{padding-top:9px;padding-bottom:9px}

/* whatsapp float pulse */
@keyframes wa-pulse{0%{box-shadow:0 0 0 0 rgba(230,51,41,.55)}70%{box-shadow:0 0 0 16px rgba(230,51,41,0)}100%{box-shadow:0 0 0 0 rgba(230,51,41,0)}}
.wa-float{animation:wa-pulse 2.4s infinite} .wa-float:hover{transform:scale(1.06)}

/* service card hover */
.svc-card{transition:transform .28s ease,box-shadow .28s ease}
.svc-card:hover{transform:translateY(-8px);box-shadow:0 22px 44px rgba(0,0,0,.13)}
.svc-card .svc-bar{transition:height .28s ease} .svc-card:hover .svc-bar{height:8px}

/* gallery hover */
.gal{position:relative;overflow:hidden}
.gal .gal-img{transition:transform .5s ease} .gal:hover .gal-img{transform:scale(1.08)}
.gal .gal-ov{opacity:0;transition:opacity .35s ease} .gal:hover .gal-ov{opacity:1}

/* buttons */
.btn-slide{position:relative;overflow:hidden;z-index:0}
.btn-slide>span{position:relative;z-index:2}
.btn-slide::before{content:'';position:absolute;inset:0;background:var(--ac-d);transform:translateX(-101%);transition:transform .32s cubic-bezier(.22,.61,.36,1);z-index:1}
.btn-slide:hover::before{transform:translateX(0)}
.btn-ghost{position:relative;overflow:hidden;z-index:0;transition:color .3s ease}
.btn-ghost>span{position:relative;z-index:2}
.btn-ghost::before{content:'';position:absolute;inset:0;background:var(--ink);transform:translateY(101%);transition:transform .32s cubic-bezier(.22,.61,.36,1);z-index:1}
.btn-ghost:hover{color:#fff} .btn-ghost:hover::before{transform:translateY(0)}

/* form fields (design + form-section blocks) */
.field,.cms-field{transition:border-color .25s ease,background .25s ease}
.field:focus{border-color:var(--ac);background:rgba(230,51,41,.04)}

/* header nav responsive + mobile menu (checkbox-hack) */
.site-head{position:sticky;top:0;z-index:80;background:rgba(255,255,255,.94);backdrop-filter:blur(10px);border-bottom:1px solid #ededed}
.head-inner{max-width:1220px;margin:0 auto;padding:15px 26px;display:flex;align-items:center;justify-content:space-between;gap:20px}
.bl-brand{display:flex;align-items:center;gap:12px}
.bl-brand__img{display:none;height:48px;width:auto;object-fit:contain}
.bl-brand__txt{display:flex;flex-direction:column;line-height:1.05}
.bl-brand__txt b{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:20px;letter-spacing:.04em;color:var(--ink)}
.bl-brand__txt b i{color:var(--ac);font-style:normal}
.bl-brand__txt small{font-size:9.5px;font-weight:600;letter-spacing:.26em;color:#9a9a9a}
.desktop-nav{display:flex;align-items:center;gap:30px}
.desktop-nav a{font-family:'Inter',sans-serif;font-weight:600;font-size:14.5px;color:var(--ink)}
.desktop-nav a:hover{color:var(--ac)}
.head-wa{display:inline-flex;align-items:center;gap:8px;background:var(--ac);color:#fff !important;font-weight:600;font-size:14px;padding:11px 19px}
.head-wa svg{width:17px;height:17px}
.bl-navtoggle{display:none} .menu-btn{display:none}
.mobile-nav{display:none}
@media(max-width:880px){
  .desktop-nav{display:none}
  .menu-btn{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;background:var(--ink);color:#fff;font-size:20px;border:none}
  .bl-navtoggle:checked ~ .mobile-nav{display:flex;flex-direction:column}
  .mobile-nav{position:absolute;left:0;right:0;top:100%;background:#fff;border-bottom:1px solid #ededed;padding:10px 26px 20px;gap:2px;box-shadow:0 14px 30px rgba(0,0,0,.10)}
  .mobile-nav a{padding:13px 4px;font-weight:600;font-size:16px;color:var(--ink);border-bottom:1px solid #f0f0f0}
  .mobile-nav .head-wa{margin-top:12px;justify-content:center;padding:14px}
}
</style>
<header class="site-head">
  <div class="head-inner" style="position:relative">
    <a class="bl-brand" href="#anasayfa" aria-label="Boblanlı Yapı ana sayfa">
      <img src="{{logo_url}}" alt="Boblanlı Yapı" class="bl-brand__img" onload="if(this.naturalWidth>0)this.style.display='block'">
      <span class="bl-brand__txt"><b>BOBLANLI <i>YAPI</i></b><small>İNŞAAT | CONSTRUCTING</small></span>
    </a>
    <input type="checkbox" id="blNav" class="bl-navtoggle" aria-hidden="true">
    <label for="blNav" class="menu-btn" aria-label="Menüyü aç/kapat">☰</label>
    <nav class="desktop-nav" aria-label="Ana menü">
      <a href="#anasayfa">Ana Sayfa</a>
      <a href="#hizmetler">Hizmetler</a>
      <a href="#neden-biz">Neden Biz</a>
      <a href="#calismalar">Çalışmalar</a>
      <a href="#iletisim">İletişim</a>
      <a class="head-wa btn-slide" href="https://wa.me/905326576271" target="_blank" rel="noopener"><span style="display:inline-flex;align-items:center;gap:8px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.4 8.4 0 0 1-4-.9L3 21l1.9-4.5a8.4 8.4 0 0 1-.9-4A8.4 8.4 0 0 1 12 4a8.4 8.4 0 0 1 9 7.5z"/></svg>WhatsApp</span></a>
    </nav>
    <nav class="mobile-nav" aria-label="Mobil menü">
      <a href="#anasayfa">Ana Sayfa</a>
      <a href="#hizmetler">Hizmetler</a>
      <a href="#neden-biz">Neden Biz</a>
      <a href="#calismalar">Çalışmalar</a>
      <a href="#iletisim">İletişim</a>
      <a class="head-wa" href="https://wa.me/905326576271" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.4 8.4 0 0 1-4-.9L3 21l1.9-4.5a8.4 8.4 0 0 1-.9-4A8.4 8.4 0 0 1 12 4a8.4 8.4 0 0 1 9 7.5z"/></svg>WhatsApp</a>
    </nav>
  </div>
</header>
BLHDR;
    }

    public static function footerHtml(): string
    {
        return <<<'BLFTR'
<footer id="site-footer" style="background:#1A1A1A;color:#b0b0b0">
  <div style="max-width:1220px;margin:0 auto;padding:56px 26px 22px">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:42px;padding-bottom:36px;border-bottom:1px solid #2c2c2c">
      <div>
        <div style="display:flex;align-items:center;gap:11px;margin-bottom:16px">
          <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:19px;letter-spacing:.04em;color:#fff">BOBLANLI <span style="color:#E63329">YAPI</span></span>
        </div>
        <p style="font-size:14px;line-height:1.7;color:#9a9a9a;max-width:280px;margin:0">Güvenle inşa eder, özenle tamamlarız. Kuşadası ve Aydın genelinde inşaat, tadilat ve elektrik hizmetleri.</p>
      </div>
      <div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:13px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#fff;margin-bottom:16px">Hizmetler</div>
        <div style="display:flex;flex-direction:column;gap:11px;font-size:14px">
          <a href="#hizmetler" style="color:#b0b0b0">Elektrik Arıza & Onarım</a>
          <a href="#hizmetler" style="color:#b0b0b0">Toptan Elektrik Malzemesi</a>
          <a href="#hizmetler" style="color:#b0b0b0">Dekorasyon & Tadilat</a>
          <a href="#hizmetler" style="color:#b0b0b0">İnşaat Hizmetleri</a>
        </div>
      </div>
      <div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:13px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#fff;margin-bottom:16px">İletişim</div>
        <div style="display:flex;flex-direction:column;gap:11px;font-size:14px">
          <a href="tel:+905326576271" style="color:#b0b0b0">+90 532 657 62 71</a>
          <a href="https://wa.me/905326576271" target="_blank" rel="noopener" style="color:#b0b0b0">WhatsApp</a>
          <span style="color:#9a9a9a;line-height:1.55">Türkmen Mah. Bahçearası Sok. No:2 İç Kapı:3, Kuşadası / Aydın</span>
        </div>
      </div>
      <div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:13px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#fff;margin-bottom:16px">Takip Edin</div>
        <div style="display:flex;flex-direction:column;gap:11px;font-size:14px">
          <a href="https://instagram.com/tahsinboblanli" target="_blank" rel="noopener" style="color:#b0b0b0;display:inline-flex;align-items:center;gap:8px"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>@tahsinboblanli</a>
          <a href="https://boblanliyapi.com" target="_blank" rel="noopener" style="color:#b0b0b0;display:inline-flex;align-items:center;gap:8px"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"/></svg>boblanliyapi.com</a>
        </div>
      </div>
    </div>
    <div style="padding-top:20px;text-align:center;font-size:13px;color:#7a7a7a">© 2026 Boblanlı Yapı — Tüm hakları saklıdır.</div>
  </div>
</footer>

<a class="wa-float" href="https://wa.me/905326576271" target="_blank" rel="noopener" aria-label="WhatsApp"
   style="position:fixed;right:22px;bottom:22px;z-index:90;width:58px;height:58px;border-radius:50%;background:#E63329;display:flex;align-items:center;justify-content:center;transition:transform .2s">
  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.4 8.4 0 0 1-4-.9L3 21l1.9-4.5a8.4 8.4 0 0 1-.9-4A8.4 8.4 0 0 1 12 4a8.4 8.4 0 0 1 9 7.5z"/></svg>
</a>

<script>
(function(){
  function init(){
    // scroll reveal
    var reveals = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window && reveals.length){
      var io = new IntersectionObserver(function(entries){
        entries.forEach(function(en){ if(en.isIntersecting){ en.target.classList.add('in'); io.unobserve(en.target); } });
      }, { threshold:0.12, rootMargin:'0px 0px -8% 0px' });
      reveals.forEach(function(el){ io.observe(el); });
    } else {
      reveals.forEach(function(el){ el.classList.add('in'); });
    }
    // güvenlik: 2.5sn sonra hepsini aç (içerik asla gizli kalmasın)
    setTimeout(function(){ reveals.forEach(function(el){ el.classList.add('in'); }); }, 2500);

    // counters
    var counters = document.querySelectorAll('[data-count]');
    function runCount(el){
      var target = parseInt(el.getAttribute('data-count'),10) || 0;
      var suffix = el.getAttribute('data-suffix') || '';
      var dur = 1400, start = null;
      function tick(now){
        if(start===null) start = now;
        var t = Math.min(1,(now-start)/dur);
        var eased = 1 - Math.pow(1-t,3);
        el.textContent = Math.round(target*eased).toString() + suffix;
        if(t<1) requestAnimationFrame(tick); else el.textContent = target.toString() + suffix;
      }
      requestAnimationFrame(tick);
    }
    if ('IntersectionObserver' in window && counters.length){
      var cio = new IntersectionObserver(function(entries){
        entries.forEach(function(en){ if(en.isIntersecting){ runCount(en.target); cio.unobserve(en.target); } });
      }, { threshold:0.5 });
      counters.forEach(function(el){ cio.observe(el); });
    } else {
      counters.forEach(function(el){ runCount(el); });
    }

    // header shrink
    var head = document.querySelector('.site-head');
    function onScroll(){ if(!head) return; if(window.scrollY>40) head.classList.add('shrink'); else head.classList.remove('shrink'); }
    window.addEventListener('scroll', onScroll, { passive:true });
    onScroll();

    // mobil menü: link tıklanınca kapat (checkbox'ı kaldır)
    var toggle = document.getElementById('blNav');
    document.querySelectorAll('.mobile-nav a').forEach(function(a){
      a.addEventListener('click', function(){ if(toggle) toggle.checked = false; });
    });
  }
  if(document.readyState!=='loading') init(); else document.addEventListener('DOMContentLoaded', init);
})();
</script>
BLFTR;
    }
}
