<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SectionTemplate;
use App\Models\Theme;
use App\Models\Language;
use Illuminate\Database\Seeder;

/**
 * Estetik Dermal — TEMA 2 (Clinical Luxury) tenant sayfaları. TENANT CONTEXT'inde çalıştır.
 * version-2 REGION: header + footer ayrı bloklar; body bölüm-bölüm AYRI bloklara bölünür.
 * HTML html_override'da (Quill ezmez, Kod sekmesinden düzenlenir). Kök slug (home, marka-skintech...).
 * Önce: V2ThemeSeeder + V2ChromeSeeder (central).
 */
class EstetikDermalV2TenantSeeder extends Seeder
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
        $theme=Theme::where('slug','estetikdermal-v2')->first();
        $tpl=fn($type,$var)=>$theme?SectionTemplate::where('theme_id',$theme->id)->where('type',$type)->where('variation',$var)->first():null;
        $cb =$tpl('content-block','v2-free-html');
        $hdrTpl=$tpl('header','estetikdermal-v2-header');
        $ftrTpl=$tpl('footer','estetikdermal-v2-footer');
        if(! $cb){ $this->command?->warn('Tema2 content-block yok — önce V2ChromeSeeder.'); return; }
        $hid=$hdrTpl?->id ?? $cb->id;  $fid=$ftrTpl?->id ?? $cb->id;
        $lang=Language::query()->where('code','tr')->first() ?? Language::query()->first();
        $langId=$lang?->id;
        // Eski nested-slug marka sayfalarını temizle (route slash desteklemez → düz 'marka-x')
        Page::where('slug','like','marka/%')->delete();
        $hdr = <<<'EDV2HDRX'
<style>
/* ============================================================================
   Estetik Dermal — Alternatif Anasayfa (v2) "Clinical Luxury"
   Bağımsız tasarım sistemi — index.html / base.css'i ETKİLEMEZ.
   Palet: clinical white + medical navy + tek aksan (cool teal).
   Tipografi: Fraunces (display serif) + Inter (grotesque body).
   ============================================================================ */

:root {
  --bg:        #FCFAF7;   /* sıcak beyaz */
  --surface:   #FFFFFF;
  --ink:       #2A251F;   /* sıcak antrasit — başlık/koyu bölüm */
  --body:      #574F46;   /* gövde metni */
  --muted:     #8C8276;   /* ikincil */
  --line:      #ECE6DF;   /* ince çizgi */
  --line-2:    #E2DAD0;
  --accent:    #E8702A;   /* Estetik Dermal turuncu — aksan */
  --accent-ink:#C85716;   /* koyu turuncu */
  --accent-wash:#FCE9DC;  /* açık turuncu zemin */
  --navy-soft: #1F1A14;
  --radius:    16px;
  --radius-sm: 10px;
  --maxw:      1240px;
  --ease:      cubic-bezier(.22,.61,.36,1);
  --font-serif:"Fraunces", Georgia, "Times New Roman", serif;
  --font-sans: "Inter", system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
}

*,*::before,*::after { box-sizing: border-box; }
html { -webkit-text-size-adjust:100%; scroll-behavior:smooth; }
body {
  margin:0; background:var(--bg); color:var(--body);
  font-family:var(--font-sans); font-size:17px; line-height:1.7;
  -webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility;
}
img,svg { display:block; max-width:100%; }
a { color:inherit; text-decoration:none; }
button { font:inherit; cursor:pointer; }
::selection { background:var(--accent); color:#fff; }
:focus-visible { outline:2px solid var(--accent); outline-offset:3px; border-radius:4px; }

h1,h2,h3,h4 { font-family:var(--font-serif); color:var(--ink); font-weight:480; line-height:1.08; letter-spacing:-.02em; margin:0; }
.eyebrow {
  font-family:var(--font-sans); font-size:12.5px; font-weight:600; letter-spacing:.18em;
  text-transform:uppercase; color:var(--accent-ink); display:inline-flex; align-items:center; gap:9px;
}
.eyebrow::before { content:""; width:22px; height:1px; background:var(--accent); display:inline-block; }

.wrap { width:min(100% - 48px, var(--maxw)); margin-inline:auto; }
.section { padding:clamp(72px, 11vh, 140px) 0; }
.lead { font-size:clamp(17px,1.4vw,20px); color:var(--muted); line-height:1.7; }

/* Alt sayfa başlık bandı */
.phero { padding:clamp(48px,8vh,88px) 0 clamp(34px,5vh,52px); background:radial-gradient(820px 420px at 82% -25%, var(--accent-wash), transparent 60%); border-bottom:1px solid var(--line); }
.phero .crumb { font-size:13.5px; color:var(--muted); margin-bottom:14px; }
.phero .crumb a { color:var(--accent-ink); } .phero .crumb a:hover { text-decoration:underline; }
.phero h1 { font-size:clamp(34px,4.8vw,58px); margin-top:6px; }
.phero .lead { margin-top:14px; max-width:640px; }

/* ---- Buttons ---- */
.btn { display:inline-flex; align-items:center; gap:10px; padding:14px 26px; border-radius:999px;
  font-size:15px; font-weight:600; letter-spacing:.01em; transition:.25s var(--ease); border:1px solid transparent; }
.btn--primary { background:var(--accent); color:#fff; }
.btn--primary:hover { background:var(--accent-ink); transform:translateY(-2px); box-shadow:0 14px 30px rgba(232,112,42,.30); }
.btn--ghost { background:transparent; color:var(--ink); border-color:var(--line-2); }
.btn--ghost:hover { border-color:var(--ink); transform:translateY(-2px); }
.btn--wa { background:var(--accent); color:#fff; }
.btn--wa:hover { background:var(--accent-ink); transform:translateY(-2px); }
.btn svg { width:17px; height:17px; }
.link-arrow { color:var(--accent-ink); font-weight:600; display:inline-flex; align-items:center; gap:8px; }
.link-arrow svg { width:16px; height:16px; transition:transform .25s var(--ease); }
.link-arrow:hover svg { transform:translateX(4px); }

/* ============================ NAV ============================ */
.nav { position:sticky; top:0; z-index:100; background:rgba(250,250,250,.72);
  backdrop-filter:saturate(180%) blur(14px); border-bottom:1px solid transparent; transition:border-color .3s, background .3s; }
.nav.is-scrolled { border-bottom-color:var(--line); background:rgba(250,250,250,.88); }
.nav__in { display:flex; align-items:center; justify-content:space-between; height:74px; }
.logo { display:flex; align-items:center; gap:11px; font-family:var(--font-serif); font-size:21px; color:var(--ink); font-weight:500; letter-spacing:-.01em; }
.logo__mark { width:30px; height:30px; border-radius:8px; background:var(--ink); position:relative; flex-shrink:0; }
.logo__mark::after { content:""; position:absolute; inset:9px; border-radius:50%; border:1.5px solid var(--accent); }
.logo b { font-weight:600; } .logo span { color:var(--muted); font-weight:400; }
.nav__links { display:flex; align-items:center; gap:4px; }
.nav__links a { padding:9px 14px; border-radius:999px; font-size:15px; font-weight:500; color:var(--body); transition:color .2s, background .2s; }
.nav__links a:hover { color:var(--ink); background:rgba(14,26,43,.05); }
.nav__cta { margin-left:10px; }
.nav__toggle { display:none; width:44px; height:44px; border:1px solid var(--line-2); border-radius:11px; background:var(--surface); flex-direction:column; gap:5px; align-items:center; justify-content:center; }
.nav__toggle span { width:20px; height:1.6px; background:var(--ink); border-radius:2px; transition:.25s; }

@media (max-width:960px){
  .nav__toggle{ display:flex; }
  .nav__links{ position:fixed; inset:74px 0 auto 0; flex-direction:column; align-items:stretch; gap:2px;
    background:var(--surface); border-bottom:1px solid var(--line); padding:14px 24px 22px;
    transform:translateY(-130%); transition:transform .35s var(--ease); box-shadow:0 24px 50px rgba(14,26,43,.10); }
  .nav__links a{ padding:13px 14px; font-size:16px; }
  .nav.is-open .nav__links{ transform:translateY(0); }
  .nav.is-open .nav__toggle span:nth-child(1){ transform:translateY(6.6px) rotate(45deg); }
  .nav.is-open .nav__toggle span:nth-child(2){ opacity:0; }
  .nav.is-open .nav__toggle span:nth-child(3){ transform:translateY(-6.6px) rotate(-45deg); }
}

/* ============================ HERO ============================ */
.hero { position:relative; overflow:hidden; min-height:92vh; display:flex; align-items:center;
  background:radial-gradient(1100px 620px at 78% 18%, var(--accent-wash), transparent 62%), var(--bg); }
.hero__grid { position:absolute; inset:0; background-image:linear-gradient(var(--line) 1px,transparent 1px),linear-gradient(90deg,var(--line) 1px,transparent 1px);
  background-size:64px 64px; opacity:.35; -webkit-mask-image:radial-gradient(900px 600px at 75% 25%,#000,transparent 70%); mask-image:radial-gradient(900px 600px at 75% 25%,#000,transparent 70%); }
.hero__in { position:relative; display:grid; grid-template-columns:1.05fr .95fr; gap:56px; align-items:center; padding:60px 0; }
.hero h1 { font-size:clamp(40px,5.6vw,74px); color:var(--ink); margin:22px 0 24px; }
.hero h1 em { font-style:italic; color:var(--accent-ink); }
.hero__sub { font-size:clamp(17px,1.5vw,21px); color:var(--muted); max-width:540px; line-height:1.65; margin:0 0 36px; }
.hero__cta { display:flex; gap:14px; flex-wrap:wrap; }
.hero__trust { display:flex; gap:26px; flex-wrap:wrap; margin-top:46px; padding-top:26px; border-top:1px solid var(--line); }
.hero__trust div { font-size:13.5px; color:var(--muted); }
.hero__trust b { display:block; font-family:var(--font-serif); font-size:19px; color:var(--ink); font-weight:500; letter-spacing:-.01em; }
/* hero visual — macro product, image-ready */
.hero__visual { position:relative; }
.hero__card { position:relative; border-radius:24px; overflow:hidden; border:1px solid var(--line); background:var(--surface);
  box-shadow:0 30px 70px -30px rgba(14,26,43,.30); aspect-ratio:4/5; }
.hero__img { position:absolute; inset:0; background:linear-gradient(160deg,#F1F5F6,#E7EEF0) ; background-size:cover; background-position:center; }
.hero__badge { position:absolute; left:18px; bottom:18px; background:rgba(255,255,255,.92); backdrop-filter:blur(6px); border:1px solid var(--line);
  border-radius:14px; padding:13px 16px; display:flex; align-items:center; gap:11px; }
.hero__badge .ce { width:42px; height:42px; border-radius:10px; background:var(--accent-wash); color:var(--accent-ink); display:grid; place-items:center; font-weight:700; font-size:13px; font-family:var(--font-sans); }
.hero__badge small { color:var(--muted); font-size:12px; } .hero__badge b{ display:block; color:var(--ink); font-weight:600; font-size:14.5px; }

/* ============================ TRUST BAND ============================ */
.band { background:var(--surface); border-block:1px solid var(--line); }
.stats { display:grid; grid-template-columns:repeat(4,1fr); gap:30px; padding:54px 0; }
.stat__n { font-family:var(--font-serif); font-size:clamp(34px,4vw,52px); color:var(--ink); font-weight:480; letter-spacing:-.02em; }
.stat__n .accent { color:var(--accent); }
.stat__l { color:var(--muted); font-size:14.5px; margin-top:4px; }
.marquee { overflow:hidden; border-top:1px solid var(--line); padding:26px 0; -webkit-mask-image:linear-gradient(90deg,transparent,#000 12%,#000 88%,transparent); mask-image:linear-gradient(90deg,transparent,#000 12%,#000 88%,transparent); }
.marquee__track { display:flex; gap:64px; width:max-content; animation:marquee 34s linear infinite; }
.marquee:hover .marquee__track { animation-play-state:paused; }
.marquee span { font-family:var(--font-serif); font-size:20px; color:#9AA6B2; font-weight:500; white-space:nowrap; letter-spacing:.01em; }
@keyframes marquee { to { transform:translateX(-50%); } }

/* ============================ FEATURED PRODUCT ============================ */
.feature { display:grid; grid-template-columns:1fr 1fr; gap:clamp(36px,6vw,84px); align-items:center; }
.feature + .feature { margin-top:clamp(56px,9vw,120px); }
.feature--rev .feature__media { order:2; }
.feature__media { border-radius:22px; overflow:hidden; border:1px solid var(--line); background:var(--surface); aspect-ratio:5/4; box-shadow:0 24px 60px -34px rgba(14,26,43,.30); }
.feature__img { width:100%; height:100%; background:linear-gradient(160deg,#EFF4F5,#E4EDEF); background-size:cover; background-position:center; }
.badge-ce { display:inline-flex; align-items:center; gap:9px; background:var(--accent-wash); color:var(--accent-ink); border-radius:999px; padding:7px 15px; font-size:13px; font-weight:600; }
.feature h2 { font-size:clamp(28px,3.4vw,42px); margin:18px 0 16px; }

/* ============================ SPEC LIST (marka sayfaları) ============================ */
.spec { margin-top:24px; border:1px solid var(--line); border-radius:14px; background:var(--surface); overflow:hidden; }
.spec__row { display:flex; justify-content:space-between; gap:16px; padding:13px 18px; font-size:14.5px; border-top:1px solid var(--line); }
.spec__row:first-child { border-top:0; }
.spec__k { color:var(--muted); font-weight:600; letter-spacing:.04em; text-transform:uppercase; font-size:12px; align-self:center; }
.spec__v { color:var(--ink); font-weight:500; text-align:right; }

/* ============================ CATEGORIES ============================ */
.catgroup + .catgroup { margin-top:46px; }
.catgroup__h { display:flex; align-items:baseline; gap:14px; margin-bottom:18px; }
.catgroup__h h3 { font-size:20px; font-weight:520; } .catgroup__h span { font-size:13px; color:var(--muted); }
.catgrid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:14px; }
.cat { display:flex; align-items:center; gap:14px; background:var(--surface); border:1px solid var(--line); border-radius:14px; padding:18px 20px; transition:.25s var(--ease); }
.cat:hover { border-color:var(--accent); transform:translateY(-3px); box-shadow:0 16px 34px -22px rgba(232,112,42,.45); }
.cat__ic { width:40px; height:40px; flex-shrink:0; border-radius:11px; background:var(--accent-wash); color:var(--accent-ink); display:grid; place-items:center; }
.cat__ic svg { width:20px; height:20px; }
.cat b { font-family:var(--font-sans); font-weight:600; font-size:15.5px; color:var(--ink); }

/* ============================ BRANDS ============================ */
.brandgrid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:18px; }
.brandc { background:var(--surface); border:1px solid var(--line); border-radius:var(--radius); padding:30px; transition:.25s var(--ease); overflow:hidden; }
.brandc:hover { transform:translateY(-4px); box-shadow:0 26px 50px -32px rgba(14,26,43,.26); border-color:var(--line-2); }
.brandc__logo { font-family:var(--font-serif); font-size:21px; color:var(--ink); font-weight:520; letter-spacing:-.01em; }
.brandc__origin { font-size:12px; color:var(--accent-ink); font-weight:600; letter-spacing:.08em; text-transform:uppercase; margin-bottom:14px; }
.brandc p { font-size:14.5px; color:var(--muted); margin:8px 0 0; line-height:1.6; }
/* büyük marka kartı görsel başlığı (markalar-v2) */
.brandc__img { aspect-ratio:16/9; margin:-30px -30px 22px; border-bottom:1px solid var(--line);
  background:linear-gradient(160deg,#EFF4F5,#E4EDEF); background-size:cover; background-position:center; }
.brandc .link-arrow { display:inline-flex; }

/* ---- kısa bilgi bandı (markalar/etkinlikler) ---- */
.band-note { display:flex; align-items:center; gap:16px; background:var(--surface); border:1px solid var(--line);
  border-radius:var(--radius); padding:22px 26px; box-shadow:0 16px 34px -28px rgba(14,26,43,.20); }
.band-note__ic { flex-shrink:0; width:46px; height:46px; border-radius:12px; background:var(--accent-wash); color:var(--accent-ink); display:grid; place-items:center; }
.band-note__ic svg { width:24px; height:24px; }
.band-note p { margin:0; font-size:15.5px; color:var(--body); line-height:1.6; }
.band-note strong { color:var(--accent-ink); font-weight:600; }

/* ============================ EVENTS (etkinlikler-v2) ============================ */
.eventgrid { display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:22px; }
.eventc { display:flex; flex-direction:column; background:var(--surface); border:1px solid var(--line); border-radius:var(--radius); overflow:hidden; transition:.25s var(--ease); }
.eventc:hover { transform:translateY(-4px); box-shadow:0 26px 50px -32px rgba(14,26,43,.26); border-color:var(--line-2); }
.eventc__media { position:relative; aspect-ratio:16/9; background:linear-gradient(160deg,#EFF4F5,#E4EDEF); background-size:cover; background-position:center; }
.eventc__date { position:absolute; top:14px; right:14px; background:rgba(255,255,255,.94); backdrop-filter:blur(6px); border:1px solid var(--line); border-radius:13px; padding:8px 13px; text-align:center; line-height:1; }
.eventc__date small { display:block; font-size:11px; font-weight:600; letter-spacing:.1em; text-transform:uppercase; color:var(--accent-ink); font-family:var(--font-sans); }
.eventc__date b { display:block; font-family:var(--font-serif); font-size:22px; color:var(--ink); font-weight:500; margin-top:2px; }
.eventc__body { padding:24px; display:flex; flex-direction:column; flex:1; }
.eventc__body h3 { font-size:20px; font-weight:520; margin:0 0 10px; line-height:1.2; }
.eventc__place { display:flex; align-items:center; gap:7px; color:var(--body); font-size:14px; font-weight:500; margin:0 0 12px; }
.eventc__place svg { width:15px; height:15px; flex-shrink:0; color:var(--accent-ink); }
.eventc__desc { color:var(--muted); font-size:14.5px; line-height:1.65; margin:0; flex:1; }

/* ---- info band (etkinlikler) ---- */
.infoband { display:flex; flex-wrap:wrap; gap:28px; align-items:center; justify-content:space-between;
  background:var(--surface); border:1px solid var(--line); border-radius:var(--radius); padding:clamp(28px,4vw,44px); }
.infoband__copy { flex:1 1 380px; }
.infoband__copy h2 { font-size:clamp(22px,3vw,30px); margin:0 0 10px; line-height:1.2; }
.infoband__cta { display:flex; gap:14px; flex-wrap:wrap; }

/* ============================ FOR DOCTORS ============================ */
.docs { background:var(--ink); color:#fff; border-radius:28px; padding:clamp(40px,6vw,72px); position:relative; overflow:hidden; }
.docs__grid { position:absolute; inset:0; background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px); background-size:54px 54px; }
.docs > * { position:relative; }
.docs h2 { color:#fff; font-size:clamp(28px,3.6vw,44px); }
.docs .lead { color:rgba(255,255,255,.66); }
.docs__cards { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin:38px 0 32px; }
.docc { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.10); border-radius:var(--radius); padding:26px; }
.docc__ic { width:46px; height:46px; border-radius:12px; background:rgba(232,112,42,.18); color:#F4A14E; display:grid; place-items:center; margin-bottom:16px; }
.docc__ic svg { width:22px; height:22px; }
.docc h4 { color:#fff; font-family:var(--font-sans); font-size:17px; font-weight:600; margin:0 0 8px; }
.docc p { color:rgba(255,255,255,.6); font-size:14px; margin:0; line-height:1.6; }

/* ============================ ABOUT TEASER ============================ */
.about { display:grid; grid-template-columns:1fr 1fr; gap:clamp(36px,6vw,80px); align-items:center; }
.about__media { aspect-ratio:4/3; border-radius:22px; border:1px solid var(--line); background:linear-gradient(160deg,#EEF3F4,#E3ECEE); }

/* ============================ CONTACT ============================ */
.contact { display:grid; grid-template-columns:1fr 1fr; gap:clamp(30px,5vw,64px); }
.field { margin-bottom:16px; } .field label { display:block; font-size:13.5px; font-weight:600; color:var(--ink); margin-bottom:7px; }
.field input,.field textarea { width:100%; padding:13px 15px; border:1px solid var(--line-2); border-radius:11px; background:var(--surface); font:inherit; color:var(--ink); transition:border-color .2s; }
.field input:focus,.field textarea:focus { border-color:var(--accent); outline:none; }
.contact__info { display:flex; flex-direction:column; gap:14px; }
.info-row { display:flex; align-items:center; gap:14px; padding:16px; border:1px solid var(--line); border-radius:14px; background:var(--surface); }
.info-row .ic { width:42px; height:42px; flex-shrink:0; border-radius:11px; background:var(--accent-wash); color:var(--accent-ink); display:grid; place-items:center; }
.info-row .ic svg{ width:19px; height:19px; } .info-row small{ color:var(--muted); font-size:12.5px; } .info-row b{ display:block; color:var(--ink); font-weight:600; }
.contact__map { margin-top:8px; border-radius:14px; overflow:hidden; border:1px solid var(--line); min-height:200px; }
.contact__map iframe { width:100%; height:100%; min-height:200px; border:0; display:block; }

/* ============================ FOOTER ============================ */
.footer { background:var(--ink); color:rgba(255,255,255,.62); margin-top:8px; }
.footer__grid { display:grid; grid-template-columns:1.6fr 1fr 1fr 1.1fr; gap:40px; padding:64px 0 40px; }
.footer a { color:rgba(255,255,255,.62); transition:color .2s; } .footer a:hover{ color:#fff; }
.footer h5 { color:#fff; font-family:var(--font-sans); font-size:13px; letter-spacing:.12em; text-transform:uppercase; margin:0 0 16px; font-weight:600; }
.footer ul { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:10px; font-size:14.5px; }
.footer__brand { font-family:var(--font-serif); font-size:22px; color:#fff; font-weight:500; }
.footer__bottom { border-top:1px solid rgba(255,255,255,.12); padding:22px 0; display:flex; flex-wrap:wrap; gap:12px; justify-content:space-between; font-size:13px; color:rgba(255,255,255,.5); }

/* ============================ MOTION (scroll reveal) ============================ */
.reveal { opacity:0; transform:translateY(22px); transition:opacity .7s var(--ease), transform .7s var(--ease); }
.reveal.in { opacity:1; transform:none; }
.reveal.d1{ transition-delay:.08s } .reveal.d2{ transition-delay:.16s } .reveal.d3{ transition-delay:.24s } .reveal.d4{ transition-delay:.32s }
@media (prefers-reduced-motion:reduce){ .reveal{opacity:1;transform:none;transition:none} .marquee__track{animation:none} }

/* ============================ RESPONSIVE ============================ */
@media (max-width:960px){
  .hero__in,.feature,.feature--rev .feature__media,.about,.contact{ grid-template-columns:1fr; }
  .feature--rev .feature__media{ order:0; }
  .hero{ min-height:auto; } .hero__visual{ order:-1; max-width:440px; }
  .stats{ grid-template-columns:repeat(2,1fr); gap:24px 30px; }
  .docs__cards{ grid-template-columns:1fr; }
  .footer__grid{ grid-template-columns:1fr 1fr; gap:32px; }
}
@media (max-width:520px){
  body{ font-size:16px; } .footer__grid{ grid-template-columns:1fr; } .stats{ grid-template-columns:1fr 1fr; }
}

.reveal{opacity:1 !important;transform:none !important}
</style><header class="nav" id="nav">
    <div class="wrap nav__in">
      <a class="logo" href="/" aria-label="Estetik Dermal"><span class="logo__mark"></span><span><b>Estetik</b> Dermal</span></a>
      <button class="nav__toggle" id="navToggle" aria-label="Menü" aria-expanded="false"><span></span><span></span><span></span></button>
      <nav class="nav__links" id="navLinks" aria-label="Ana menü">
        <a href="/hakkimizda">Hakkımızda</a>
        <a href="/urunler">Ürünler</a>
        <a href="/markalar">Markalar</a>
        <a href="/etkinlikler">Eğitim &amp; Kongre</a>
        <a href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener">MEDINET</a>
        <a href="/iletisim">İletişim</a>
        <a class="btn btn--primary nav__cta" href="/iletisim">Bilgi Al</a>
      </nav>
    </div>
  </header>
EDV2HDRX;
        $ftr = <<<'EDV2FTRX'
<footer class="footer">
    <div class="wrap footer__grid">
      <div>
        <div class="footer__brand">Estetik Dermal</div>
        <p style="margin:16px 0 0;max-width:300px;font-size:14.5px;line-height:1.7;">2004'ten bu yana medikal estetiğin yenilikçi ürünlerini doktorlara sunan resmi distribütör. Kuşadası / Aydın.</p>
      </div>
      <div><h5>Kurumsal</h5><ul><li><a href="/hakkimizda">Hakkımızda</a></li><li><a href="/markalar">Markalar</a></li><li><a href="/etkinlikler">Eğitim &amp; Kongre</a></li><li><a href="/iletisim">İletişim</a></li></ul></div>
      <div><h5>Markalar</h5><ul><li><a href="/marka-skintech">Skin Tech Pharma</a></li><li><a href="/marka-seffiline">Seffiline</a></li><li><a href="/marka-aespio">Grand Aespio</a></li><li><a href="/marka-woorhi">Woorhi</a></li><li><a href="/marka-mi-medical">Mi Medical</a></li></ul></div>
      <div><h5>İletişim</h5><ul><li><a href="tel:+902566121813">0 256 612 18 13</a></li><li><a href="https://wa.me/905426205100" target="_blank" rel="noopener">+90 542 620 51 00</a></li><li><a href="mailto:info@estetikdermal.com">info@estetikdermal.com</a></li><li><a href="https://www.instagram.com/estetikdermal/" target="_blank" rel="noopener">Instagram</a></li></ul></div>
    </div>
    <div class="wrap footer__bottom"><span>© 2026 Estetik Dermal. Tüm hakları saklıdır.</span><span>Resmi medikal estetik distribütörü · Kuşadası / Aydın</span></div>
  </footer>
EDV2FTRX;
        $p0s0 = <<<'EDV2P0S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"><!-- HERO -->

    <section class="hero">
      <div class="hero__grid" aria-hidden="true"></div>
      <div class="wrap hero__in">
        <div class="hero__copy">
          <span class="eyebrow reveal">2004'ten bu yana · Resmi Distribütör</span>
          <h1 class="reveal d1">Medikal estetikte <em>güvenin</em> adresi.</h1>
          <p class="hero__sub reveal d2">CE Class III sertifikalı ürünler, uluslararası markalar ve doktorlara özel uygulamalı eğitim — kliniğinizin profesyonel tedarik ortağı.</p>
          <div class="hero__cta reveal d3">
            <a class="btn btn--primary" href="#urunler">Ürünleri Keşfet</a>
            <a class="btn btn--ghost" href="https://wa.me/905426205100" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 4 1.7 4 1.2 4.7 1.1.7-.1 2.4-1 2.7-1.9.3-1 .3-1.8.2-1.9z"/></svg>
              Bilgi Al — WhatsApp</a>
          </div>
          <div class="hero__trust reveal d4">
            <div><b>CE Class III</b> Sertifikalı portföy</div>
            <div><b>20+ yıl</b> Sektör deneyimi</div>
            <div><b>6 marka</b> Resmi temsilcilik</div>
          </div>
        </div>
        <div class="hero__visual reveal d2">
          <div class="hero__card">
            <!-- 🖼️ GÖRSEL: assets/img/v2-hero.jpg (oran 4:5) — macro ürün/lab çekimi, temiz açık zemin, klinik lüks -->
            <div class="hero__img" style="background-image:url('assets/img/v2-hero.jpg')"></div>
            <div class="hero__badge">
              <span class="ce">CE</span>
              <div><small>Öne çıkan ürün</small><b>RRS® HA Long Lasting</b></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDV2P0S0;
        $p0s1 = <<<'EDV2P0S1'
<!-- TRUST BAND + COUNTERS + MARQUEE -->

    <section class="band">
      <div class="wrap stats">
        <div class="reveal"><div class="stat__n"><span data-count="20">0</span><span class="accent">+</span></div><div class="stat__l">Yıllık deneyim</div></div>
        <div class="reveal d1"><div class="stat__n"><span data-count="6">0</span></div><div class="stat__l">Uluslararası marka temsilciliği</div></div>
        <div class="reveal d2"><div class="stat__n"><span data-count="14">0</span></div><div class="stat__l">Ürün kategorisi</div></div>
        <div class="reveal d3"><div class="stat__n">CE <span class="accent">III</span></div><div class="stat__l">Sertifikalı portföy</div></div>
      </div>
      <div class="marquee" aria-hidden="true">
        <div class="marquee__track">
          <span>Skin Tech Pharma Group</span><span>MI-Medical Innovation</span><span>Neogenesis</span><span>Seffiline</span><span>Woorhi Mechatronics</span><span>Grand Aespio</span>
          <span>Skin Tech Pharma Group</span><span>MI-Medical Innovation</span><span>Neogenesis</span><span>Seffiline</span><span>Woorhi Mechatronics</span><span>Grand Aespio</span>
        </div>
      </div>
    </section>

    
EDV2P0S1;
        $p0s2 = <<<'EDV2P0S2'
<!-- FEATURED PRODUCTS -->

    <section class="section" id="urunler">
      <div class="wrap">
        <div class="feature">
          <div class="feature__media reveal">
            <!-- 🖼️ GÖRSEL: assets/img/v2-rrs.jpg (oran 5:4) — RRS HA Long Lasting macro ürün çekimi -->
            <div class="feature__img" style="background-image:url('assets/img/v2-rrs.jpg')"></div>
          </div>
          <div class="reveal d1">
            <span class="eyebrow">Öne Çıkan Ürün · Skin Tech</span>
            <h2>RRS® HA Long Lasting</h2>
            <p class="lead">Çapraz bağlı, emilebilir hyalüronik asit içeren CE Class III dermal implant. Amino asit içeren koruyucu tampon solüsyonunda; cilt kalitesini içten iyileştiren uzun etkili skinbooster.</p>
            <p style="margin:18px 0 24px;"><span class="badge-ce">● CE Class III · Tıbbi cihaz sınıfı</span></p>
            <a class="link-arrow" href="urun-detay.html">Detaylı bilgi al <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </div>

        <div class="feature feature--rev">
          <div class="feature__media reveal">
            <!-- 🖼️ GÖRSEL: assets/img/v2-melablock.jpg (oran 5:4) — Melablock HSP SPF 50+ ürün çekimi -->
            <div class="feature__img" style="background-image:url('assets/img/v2-melablock.jpg')"></div>
          </div>
          <div class="reveal d1">
            <span class="eyebrow">Güneş Koruma · Skin Tech</span>
            <h2>Melablock HSP SPF 50+</h2>
            <p class="lead">Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktörlü formül; lazer ve peeling sonrası termal hasara karşı savunma sağlar.</p>
            <a class="link-arrow" href="urunler.html" style="margin-top:22px;">Tüm ürünleri gör <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </div>
      </div>
    </section>

    
EDV2P0S2;
        $p0s3 = <<<'EDV2P0S3'
<!-- CATEGORIES -->

    <section class="section" style="background:var(--surface);border-block:1px solid var(--line);">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:46px;">
          <span class="eyebrow">Ürün Kategorileri</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">14 kategoride, kliniğinizin ihtiyaç duyduğu her şey</h2>
        </div>
        <div class="catgroup reveal">
          <div class="catgroup__h"><h3>İnjeksiyon &amp; Mezoterapi</h3><span>5 kategori</span></div>
          <div class="catgrid">
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m18 2 4 4M17 7 7 17l-4 1 1-4L14 4z"/><path d="m13 5 6 6"/></svg></span><b>Mezoterapi</b></a>
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="9" y="2" width="6" height="6" rx="1"/><path d="M12 8v8M9 12h6M10 16h4l-2 6z"/></svg></span><b>Mezoterapi Tabancası</b></a>
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 21a5 5 0 0 1-5-5c0-2 1-3 3-5l7-7 4 4-7 7c-2 2-3 3-2 6z"/></svg></span><b>RRS</b></a>
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 21 21 3M14 4l6 6M16 8l-9 9"/></svg></span><b>Kanül &amp; İğne Ucu</b></a>
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2"/></svg></span><b>Otolog Rejeneratif Terapi</b></a>
          </div>
        </div>
        <div class="catgroup reveal d1">
          <div class="catgroup__h"><h3>Cilt Bakımı &amp; Peeling</h3><span>6 kategori</span></div>
          <div class="catgrid">
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 2h10l-1 6a4 4 0 0 1-8 0z"/><path d="M9 14h6v6a3 3 0 0 1-6 0z"/></svg></span><b>Kimyasal Peeling</b></a>
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v4M5 8l3 2M19 8l-3 2"/><circle cx="12" cy="15" r="6"/></svg></span><b>Peeling</b></a>
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="7" y="9" width="10" height="12" rx="2"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg></span><b>Kremler</b></a>
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/></svg></span><b>Kozmetik</b></a>
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><circle cx="9" cy="10" r="1"/><circle cx="15" cy="10" r="1"/><path d="M9 15c1 1 5 1 6 0"/></svg></span><b>Yüz Maskesi</b></a>
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20 20 4M8 4H4v4M16 20h4v-4"/><circle cx="7" cy="7" r="1"/><circle cx="17" cy="17" r="1"/></svg></span><b>Micro İğneleme</b></a>
          </div>
        </div>
        <div class="catgroup reveal d2">
          <div class="catgroup__h"><h3>Cihaz &amp; Profesyonel Sarf</h3><span>3 kategori</span></div>
          <div class="catgrid">
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6c6 2 10 4 16 12M6 4c5 6 9 10 14 16"/></svg></span><b>İp</b></a>
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3a9 9 0 1 0 9 9"/><path d="M12 7v5l3 2"/></svg></span><b>Terapi</b></a>
            <a class="cat" href="urunler.html"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 3h6v4l4 14H5l4-14z"/><path d="M8 13h8"/></svg></span><b>Profesyonel Ürünler</b></a>
          </div>
        </div>
      </div>
    </section>

    
EDV2P0S3;
        $p0s4 = <<<'EDV2P0S4'
<!-- REPRESENTED BRANDS -->

    <section class="section" id="markalar">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:44px;">
          <span class="eyebrow">Temsil Ettiğimiz Markalar</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">Dünyanın önde gelen markalarıyla çalışıyoruz</h2>
        </div>
        <div class="brandgrid">
          <a class="brandc reveal" href="marka/skintech.html"><div class="brandc__origin">İspanya</div><div class="brandc__logo">Skin Tech Pharma Group</div><p>Kimyasal peeling, mezoterapi ve RRS skinbooster serisinde dünya lideri.</p></a>
          <a class="brandc reveal d1" href="marka/mi-medical.html"><div class="brandc__origin">Premium</div><div class="brandc__logo">MI-Medical Innovation</div><p>Premium mezoterapi ve enjeksiyon sistemleri.</p></a>
          <div class="brandc reveal d2"><div class="brandc__origin">Portföy</div><div class="brandc__logo">Neogenesis</div><p>Rejeneratif cilt bakımı çözümleri.</p></div>
          <a class="brandc reveal" href="marka/seffiline.html"><div class="brandc__origin">Bakım &amp; Dolgu</div><div class="brandc__logo">Seffiline</div><p>Cilt, saç, intim bakım ve dolgu çözümleri serisi.</p></a>
          <a class="brandc reveal d1" href="marka/woorhi.html"><div class="brandc__origin">Güney Kore</div><div class="brandc__logo">Woorhi Mechatronics</div><p>Kore mühendisliğiyle geliştirilen medikal estetik cihazları.</p></a>
          <a class="brandc reveal d2" href="marka/aespio.html"><div class="brandc__origin">K-Beauty</div><div class="brandc__logo">Grand Aespio</div><p>Yüz maskeleri ve ip askı (thread lift) ürünleri.</p></a>
        </div>
      </div>
    </section>

    
EDV2P0S4;
        $p0s5 = <<<'EDV2P0S5'
<!-- FOR DOCTORS -->

    <section class="section" id="doktorlar">
      <div class="wrap">
        <div class="docs reveal">
          <div class="docs__grid" aria-hidden="true"></div>
          <span class="eyebrow" style="color:#F4A14E;">Doktorlar İçin</span>
          <h2 style="margin-top:18px;max-width:660px;">Sadece tedarik değil, uçtan uca profesyonel destek</h2>
          <p class="lead" style="max-width:600px;margin-top:14px;">Ürünlerin doğru ve güvenli kullanımı için uygulamalı eğitim, ulusal/uluslararası kongreler ve MEDINET dijital platformuyla yanınızdayız.</p>
          <div class="docs__cards">
            <div class="docc"><div class="docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div><h4>Uygulamalı Eğitim</h4><p>Hekimlere birebir, uygulamalı ürün ve teknik kullanım eğitimleri.</p></div>
            <div class="docc"><div class="docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M8 21h8M12 17v4M3 4h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg></div><h4>Kongre &amp; Etkinlik</h4><p>Sektörün önde gelen kongre ve fuarlarında aktif katılım.</p></div>
            <div class="docc"><div class="docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 14h2"/></svg></div><h4>MEDINET Platformu</h4><p>Dijital sipariş, takip ve bilgi erişimi için MEDINET portalı.</p></div>
          </div>
          <a class="btn btn--wa" href="etkinlikler.html">Eğitim ve etkinliklerimizi inceleyin</a>
        </div>
      </div>
    </section>

    
EDV2P0S5;
        $p0s6 = <<<'EDV2P0S6'
<!-- ABOUT TEASER -->

    <section class="section" id="hakkimizda">
      <div class="wrap about">
        <div class="about__media reveal">
          <!-- 🖼️ GÖRSEL: assets/img/v2-about.jpg (oran 4:3) — kurumsal/lab ortam, profesyonel -->
          <div class="about__media" style="background-image:url('assets/img/v2-about.jpg');background-size:cover;background-position:center;border:0;"></div>
        </div>
        <div class="reveal d1">
          <span class="eyebrow">Biz Kimiz</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin:18px 0 16px;">2004'ten bugüne, medikal estetikte güvenin temsilcisi</h2>
          <p class="lead">Estetik Dermal, medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline dağıtıyor. Uluslararası markaların resmi temsilcisi olarak, yalnızca ürün değil; doktorlara uygulamalı eğitim ve teknik destek de sunuyoruz.</p>
          <a class="link-arrow" href="hakkimizda.html" style="margin-top:22px;">Hikayemizi okuyun <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
      </div>
    </section>

    
EDV2P0S6;
        $p0s7 = <<<'EDV2P0S7'
<!-- CONTACT -->

    <section class="section" id="iletisim" style="background:var(--surface);border-top:1px solid var(--line);">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:40px;">
          <span class="eyebrow">İletişim</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">Ürün, fiyat ve eğitim için bize ulaşın</h2>
        </div>
        <div class="contact">
          <form class="reveal" action="#" method="post" novalidate>
            <p style="font-size:12.5px;color:var(--muted);margin:0 0 16px;">Form yalnızca yapı amaçlıdır; CMS'te /api/v1/forms/.../submit ile bağlanır.</p>
            <div class="field"><label for="ad">Ad Soyad</label><input id="ad" name="ad" type="text" autocomplete="name"></div>
            <div class="field"><label for="kurum">Klinik / Kurum</label><input id="kurum" name="kurum" type="text" autocomplete="organization"></div>
            <div class="field"><label for="tel">Telefon</label><input id="tel" name="tel" type="tel" autocomplete="tel"></div>
            <div class="field"><label for="mesaj">Mesaj</label><textarea id="mesaj" name="mesaj" rows="4"></textarea></div>
            <button class="btn btn--primary" type="submit" style="width:100%;justify-content:center;">Gönder</button>
          </form>
          <div class="contact__info reveal d1">
            <a class="info-row" href="https://wa.me/905426205100" target="_blank" rel="noopener"><span class="ic"><svg viewBox="0 0 32 32" fill="currentColor"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4z"/></svg></span><div><small>WhatsApp</small><b>+90 542 620 51 00</b></div></a>
            <a class="info-row" href="tel:+902566121813"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg></span><div><small>Telefon</small><b>0 256 612 18 13</b></div></a>
            <a class="info-row" href="mailto:info@estetikdermal.com"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg></span><div><small>E-posta</small><b>info@estetikdermal.com</b></div></a>
            <div class="info-row"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg></span><div><small>Adres</small><b style="font-weight:500;font-size:14px;">Türkmen Mah. Turgut Özel Bulvarı, Ada Modern A Blok No 83/3A · Kuşadası / Aydın</b></div></div>
            <div class="contact__map"><iframe title="Estetik Dermal — Kuşadası konumu" src="https://maps.google.com/maps?q=Kusadasi%20Aydin&t=&z=12&ie=UTF8&iwloc=&output=embed" loading="lazy"></iframe></div>
          </div>
        </div>
      </div>
    </section>
EDV2P0S7;
        $p1s0 = <<<'EDV2P1S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"><!-- PAGE HERO -->

    <section class="phero">
      <div class="wrap">
        <nav class="crumb" aria-label="Breadcrumb">
          <a href="/">Ana Sayfa</a> <span aria-hidden="true">/</span> Hakkımızda
        </nav>
        <h1 class="reveal">Hakkımızda</h1>
        <p class="lead reveal d1">2004'ten bu yana medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline ulaştıran resmi distribütör.</p>
      </div>
    </section>

    
EDV2P1S0;
        $p1s1 = <<<'EDV2P1S1'
<!-- ABOUT STORY (split) -->

    <section class="section">
      <div class="wrap">
        <div class="feature">
          <div class="feature__media reveal">
            <!-- 🖼️ GÖRSEL: assets/img/v2-about.jpg (oran 5:4) — kurumsal/showroom, ürün tanıtımı, klinik lüks -->
            <div class="feature__img" style="background-image:url('assets/img/v2-about.jpg')"></div>
          </div>
          <div class="reveal d1">
            <span class="eyebrow">Biz Kimiz?</span>
            <h2>2004'ten beri medikal estetikte güvenin adresi</h2>
            <p class="lead">2004'ten bu yana medikal estetik dünyasının en yenilikçi ve yüksek teknolojiye sahip ürünlerini Türkiye geneline dağıtıyoruz. Uluslararası markaların resmi temsilcisi olarak yalnızca ürün değil, doktorlara uygulamalı eğitim ve teknik destek de sunuyoruz.</p>
            <ul style="list-style:none;margin:24px 0 0;padding:0;display:grid;gap:12px;">
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;"><span style="color:var(--accent);font-size:18px;">✓</span> Uluslararası markaların resmi Türkiye temsilcisi</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;"><span style="color:var(--accent);font-size:18px;">✓</span> CE Class III sertifikalı RRS serisi</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;"><span style="color:var(--accent);font-size:18px;">✓</span> Doktorlara birebir, uygulamalı eğitim</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;"><span style="color:var(--accent);font-size:18px;">✓</span> Türkiye geneli, 81 ile dağıtım ağı</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    
EDV2P1S1;
        $p1s2 = <<<'EDV2P1S2'
<!-- VİZYON & MİSYON -->

    <section class="section" style="background:var(--surface);border-block:1px solid var(--line);">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:46px;">
          <span class="eyebrow">Vizyon &amp; Misyon</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">Sektörün uzmanlık seviyesini yükseltmek için varız</h2>
        </div>
        <div class="brandgrid">
          <div class="brandc reveal">
            <div class="cat__ic" style="margin-bottom:18px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.2" fill="currentColor" stroke="none"/></svg></div>
            <h3 style="font-size:21px;font-weight:520;margin:0 0 12px;">Vizyonumuz</h3>
            <p style="font-size:16px;color:var(--body);line-height:1.7;margin:0;">Medikal estetikte kalite ve güvenin simgesi olarak sektördeki uzmanlık seviyesini sürekli yükseltmek.</p>
          </div>
          <div class="brandc reveal d1">
            <div class="cat__ic" style="margin-bottom:18px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 15c-1.5 1.3-2 5-2 5s3.7-.5 5-2a2.6 2.6 0 0 0-3-3Z"/><path d="M9 13c2-5 5-8 11-8 0 6-3 9-8 11"/><path d="M9 13l-3-1a14 14 0 0 1 3-3l3 .5"/><path d="M11 15l1 3a14 14 0 0 0 3-3l-.5-3"/></svg></div>
            <h3 style="font-size:21px;font-weight:520;margin:0 0 12px;">Misyonumuz</h3>
            <p style="font-size:16px;color:var(--body);line-height:1.7;margin:0;">Hastaların cilt sağlığını güçlendirecek profesyonel çözümler sağlamak ve invazif olmayan gençleştirme yöntemlerinde öncü rol oynamak.</p>
          </div>
        </div>
      </div>
    </section>

    
EDV2P1S2;
        $p1s3 = <<<'EDV2P1S3'
<!-- STATS BAND -->

    <section class="band">
      <div class="wrap stats">
        <div class="reveal"><div class="stat__n"><span>20</span><span class="accent">+</span></div><div class="stat__l">Yıllık deneyim</div></div>
        <div class="reveal d1"><div class="stat__n"><span>6</span></div><div class="stat__l">Uluslararası marka temsilciliği</div></div>
        <div class="reveal d2"><div class="stat__n"><span>14</span></div><div class="stat__l">Ürün kategorisi</div></div>
        <div class="reveal d3"><div class="stat__n">CE <span class="accent">III</span></div><div class="stat__l">Sertifikalı portföy</div></div>
      </div>
    </section>

    
EDV2P1S3;
        $p1s4 = <<<'EDV2P1S4'
<!-- PORTFOLIO BRANDS -->

    <section class="section">
      <div class="wrap">
        <div class="reveal" style="max-width:640px;margin-bottom:44px;">
          <span class="eyebrow">Portföyümüz</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">Temsil ettiğimiz uluslararası markalar</h2>
          <p class="lead" style="margin-top:14px;">Her biri kendi alanında uzman; mezoterapiden cihaza, peelingden ip askıya geniş bir ürün yelpazesi.</p>
        </div>
        <div class="brandgrid">
          <a class="brandc reveal" href="/marka-skintech"><div class="brandc__origin">İspanya</div><div class="brandc__logo">Skin Tech Pharma Group</div><p>Kimyasal peeling, mezoterapi ve RRS skinbooster serisinde dünya lideri.</p></a>
          <a class="brandc reveal d1" href="/marka-mi-medical"><div class="brandc__origin">Premium</div><div class="brandc__logo">MI-Medical Innovation</div><p>Premium mezoterapi ve enjeksiyon sistemleri.</p></a>
          <div class="brandc reveal d2"><div class="brandc__origin">Kök Hücre</div><div class="brandc__logo">Neogenesis</div><p>Kök hücre teknolojili profesyonel cilt bakım serisi.</p></div>
          <a class="brandc reveal" href="/marka-seffiline"><div class="brandc__origin">Cilt &amp; Saç</div><div class="brandc__logo">Seffiline</div><p>Cilt, saç, intim bakım ve dolgu çözümleri serisi.</p></a>
          <a class="brandc reveal d1" href="/marka-woorhi"><div class="brandc__origin">Güney Kore</div><div class="brandc__logo">Woorhi Mechatronics</div><p>Kore mühendisliğiyle geliştirilen medikal estetik cihazları.</p></a>
          <a class="brandc reveal d2" href="/marka-aespio"><div class="brandc__origin">K-Beauty</div><div class="brandc__logo">Grand Aespio</div><p>Yüz maskeleri ve ip askı (thread lift) ürünleri.</p></a>
        </div>
      </div>
    </section>

    
EDV2P1S4;
        $p1s5 = <<<'EDV2P1S5'
<!-- FOR DOCTORS / TRAINING -->

    <section class="section" style="background:var(--surface);border-top:1px solid var(--line);">
      <div class="wrap">
        <div class="docs reveal">
          <div class="docs__grid" aria-hidden="true"></div>
          <span class="eyebrow" style="color:#F4A14E;">Neden Estetik Dermal?</span>
          <h2 style="margin-top:18px;max-width:660px;">Sadece ürün değil, uçtan uca profesyonel destek</h2>
          <p class="lead" style="max-width:600px;margin-top:14px;">Ürünlerin doğru ve güvenli kullanımı için uygulamalı eğitim, kesintisiz teknik danışmanlık ve orijinallik güvencesiyle hekimlerin yanındayız.</p>
          <div class="docs__cards">
            <div class="docc"><div class="docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div><h4>Uygulamalı Eğitim</h4><p>Hekimlere ürünlerin doğru ve güvenli kullanımı için birebir, uygulamalı eğitim programları.</p></div>
            <div class="docc"><div class="docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 14v-1a8 8 0 0 1 16 0v1"/><path d="M4 14a2 2 0 0 1 2-2h1v6H6a2 2 0 0 1-2-2Zm16 0a2 2 0 0 0-2-2h-1v6h1a2 2 0 0 0 2-2Z"/><path d="M18 18v.5a2.5 2.5 0 0 1-2.5 2.5H12"/></svg></div><h4>Teknik Destek</h4><p>Cihaz kurulumundan kullanım sürecine kadar kesintisiz teknik danışmanlık ve servis.</p></div>
            <div class="docc"><div class="docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3 5 6v5c0 4 3 7 7 8 4-1 7-4 7-8V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg></div><h4>Orijinallik Garantisi</h4><p>Resmi temsilci güvencesiyle %100 orijinal, sertifikalı ve takip edilebilir ürünler.</p></div>
          </div>
          <a class="btn btn--wa" href="/etkinlikler">Eğitim ve etkinliklerimizi inceleyin</a>
        </div>
      </div>
    </section>

    
EDV2P1S5;
        $p1s6 = <<<'EDV2P1S6'
<!-- CTA / CONTACT -->

    <section class="section">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:40px;">
          <span class="eyebrow">Bize Ulaşın</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">Profesyonel çözümler için buradayız</h2>
          <p class="lead" style="margin-top:14px;">Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.</p>
        </div>
        <div class="hero__cta reveal d1">
          <a class="btn btn--ghost" href="https://wa.me/905426205100" target="_blank" rel="noopener">
            <svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 4 1.7 4 1.2 4.7 1.1.7-.1 2.4-1 2.7-1.9.3-1 .3-1.8.2-1.9z"/></svg>
            WhatsApp ile Yaz</a>
          <a class="btn btn--ghost" href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener">MEDINET Portalı</a>
          <a class="btn btn--primary" href="/iletisim">İletişime Geç</a>
        </div>
      </div>
    </section>
EDV2P1S6;
        $p2s0 = <<<'EDV2P2S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"><!-- PAGE HERO -->

    <section class="phero">
      <div class="wrap">
        <nav class="crumb" aria-label="Breadcrumb">
          <a href="/">Ana Sayfa</a> <span aria-hidden="true">/</span> Ürünler
        </nav>
        <h1 class="reveal">Ürünler</h1>
        <p class="lead reveal d1">95+ profesyonel medikal estetik ürünü, 14 kategoride — uluslararası markaların resmi distribütör kataloğu.</p>
      </div>
    </section>

    
EDV2P2S0;
        $p2s1 = <<<'EDV2P2S1'
<!-- FILTER CHIPS (statik) -->

    <section style="background:var(--surface);border-bottom:1px solid var(--line);">
      <div class="wrap" style="display:flex;flex-wrap:wrap;gap:10px;padding:24px 0;" aria-label="Marka filtresi">
        <a class="btn btn--primary" href="/urunler" style="padding:10px 22px;font-size:14px;">Tümü</a>
        <a class="btn btn--ghost" href="/urunler" style="padding:10px 22px;font-size:14px;">Skin Tech</a>
        <a class="btn btn--ghost" href="/urunler" style="padding:10px 22px;font-size:14px;">Seffiline</a>
        <a class="btn btn--ghost" href="/urunler" style="padding:10px 22px;font-size:14px;">Grand Aespio</a>
        <a class="btn btn--ghost" href="/urunler" style="padding:10px 22px;font-size:14px;">Woorhi</a>
        <a class="btn btn--ghost" href="/urunler" style="padding:10px 22px;font-size:14px;">Mi Medical</a>
      </div>
    </section>

    
EDV2P2S1;
        $p2s2 = <<<'EDV2P2S2'
<!-- CATEGORY GROUPS -->

    <section class="section" style="background:var(--surface);border-bottom:1px solid var(--line);">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:46px;">
          <span class="eyebrow">Ürün Kategorileri</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">İhtiyacınız olan her şey, 14 kategoride</h2>
        </div>
        <div class="catgroup reveal">
          <div class="catgroup__h"><h3>İnjeksiyon &amp; Mezoterapi</h3><span>5 kategori</span></div>
          <div class="catgrid">
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m18 2 4 4M17 7 7 17l-4 1 1-4L14 4z"/><path d="m13 5 6 6"/></svg></span><b>Mezoterapi</b></a>
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="9" y="2" width="6" height="6" rx="1"/><path d="M12 8v8M9 12h6M10 16h4l-2 6z"/></svg></span><b>Mezoterapi Tabancası</b></a>
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 21a5 5 0 0 1-5-5c0-2 1-3 3-5l7-7 4 4-7 7c-2 2-3 3-2 6z"/></svg></span><b>RRS</b></a>
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 21 21 3M14 4l6 6M16 8l-9 9"/></svg></span><b>Kanül &amp; İğne Ucu</b></a>
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2"/></svg></span><b>Otolog Rejeneratif Terapi</b></a>
          </div>
        </div>
        <div class="catgroup reveal d1">
          <div class="catgroup__h"><h3>Cilt Bakımı &amp; Peeling</h3><span>6 kategori</span></div>
          <div class="catgrid">
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 2h10l-1 6a4 4 0 0 1-8 0z"/><path d="M9 14h6v6a3 3 0 0 1-6 0z"/></svg></span><b>Kimyasal Peeling</b></a>
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v4M5 8l3 2M19 8l-3 2"/><circle cx="12" cy="15" r="6"/></svg></span><b>Peeling</b></a>
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="7" y="9" width="10" height="12" rx="2"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg></span><b>Kremler</b></a>
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/></svg></span><b>Kozmetik</b></a>
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><circle cx="9" cy="10" r="1"/><circle cx="15" cy="10" r="1"/><path d="M9 15c1 1 5 1 6 0"/></svg></span><b>Yüz Maskesi</b></a>
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20 20 4M8 4H4v4M16 20h4v-4"/><circle cx="7" cy="7" r="1"/><circle cx="17" cy="17" r="1"/></svg></span><b>Micro İğneleme</b></a>
          </div>
        </div>
        <div class="catgroup reveal d2">
          <div class="catgroup__h"><h3>Cihaz &amp; Profesyonel Sarf</h3><span>3 kategori</span></div>
          <div class="catgrid">
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6c6 2 10 4 16 12M6 4c5 6 9 10 14 16"/></svg></span><b>İp</b></a>
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3a9 9 0 1 0 9 9"/><path d="M12 7v5l3 2"/></svg></span><b>Terapi</b></a>
            <a class="cat" href="/urunler"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 3h6v4l4 14H5l4-14z"/><path d="M8 13h8"/></svg></span><b>Profesyonel Ürünler</b></a>
          </div>
        </div>
      </div>
    </section>

    
EDV2P2S2;
        $p2s3 = <<<'EDV2P2S3'
<!-- PRODUCT GRID -->

    <section class="section">
      <div class="wrap">
        <div class="reveal" style="max-width:620px;margin-bottom:44px;">
          <span class="eyebrow">Tüm Ürünler</span>
          <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">Profesyonel medikal estetik portföyü</h2>
        </div>
        <div class="brandgrid">

          <!-- ════════════════════════════════════════════════════════════════════
               🖼️ ÜRÜN KART GÖRSELLERİ — image-ready (oran 5:4)
               İsimlendirme: assets/img/product-{slug}.jpg
               v2.css .feature__img gradyan fallback'i kullanılır; görsel yoksa nötr kalır.
          ════════════════════════════════════════════════════════════════════ -->

          <!-- Skin Tech -->
          <a class="brandc reveal" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-rrs-ha-long-lasting.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Skin Tech · RRS</div>
              <div class="brandc__logo" style="font-size:18px;">RRS® HA Long Lasting</div>
              <p>Çapraz bağlı HA içeren CE Class III dermal implant.</p>
            </div>
          </a>
          <a class="brandc reveal d1" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-melablock-hsp-spf-50.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Skin Tech · Krem</div>
              <div class="brandc__logo" style="font-size:18px;">Melablock HSP SPF 50+</div>
              <p>Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktör.</p>
            </div>
          </a>
          <a class="brandc reveal d2" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-benebellum-lumina-vit-c-18.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Skin Tech · Mezoterapi</div>
              <div class="brandc__logo" style="font-size:18px;">Benebellum LUMINA VİT-C 18%</div>
              <p>Yüksek konsantrasyonlu C vitamini ile aydınlatıcı bakım.</p>
            </div>
          </a>
          <a class="brandc reveal" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-benebellum-lumina-vit-a-e.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Skin Tech · Mezoterapi</div>
              <div class="brandc__logo" style="font-size:18px;">Benebellum LUMINA VİT. A+E</div>
              <p>A ve E vitamini ile besleyici, antioksidan bakım.</p>
            </div>
          </a>
          <a class="brandc reveal d1" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-benebellum-tx-solution.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Skin Tech · Mezoterapi</div>
              <div class="brandc__logo" style="font-size:18px;">Benebellum TX SOLUTION</div>
              <p>Traneksamik asit içeren leke karşıtı aydınlatıcı solüsyon.</p>
            </div>
          </a>
          <a class="brandc reveal d2" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-aclaranse.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Skin Tech · Peeling</div>
              <div class="brandc__logo" style="font-size:18px;">Aclaranse</div>
              <p>Lekeli ciltler için aydınlatıcı profesyonel peeling çözümü.</p>
            </div>
          </a>
          <a class="brandc reveal" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-actilift.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Skin Tech · İp</div>
              <div class="brandc__logo" style="font-size:18px;">Actilift</div>
              <p>Yüz ve boyunda anlık toparlama için ip askı sistemi.</p>
            </div>
          </a>
          <a class="brandc reveal d1" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-atrofillin.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Skin Tech · Mezoterapi</div>
              <div class="brandc__logo" style="font-size:18px;">Atrofillin</div>
              <p>Atrofik izler ve cilt onarımı için mezoterapi solüsyonu.</p>
            </div>
          </a>

          <!-- Grand Aespio -->
          <a class="brandc reveal d2" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-beta-glukan-mask.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Grand Aespio · Yüz Maskesi</div>
              <div class="brandc__logo" style="font-size:18px;">Beta-Glukan Mask</div>
              <p>Yatıştırıcı ve onarıcı beta-glukan içeren yüz maskesi.</p>
            </div>
          </a>
          <a class="brandc reveal" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-hyaluronic-acid-mask.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Grand Aespio · Yüz Maskesi</div>
              <div class="brandc__logo" style="font-size:18px;">Hyaluronic Acid Mask</div>
              <p>Yoğun nem ve dolgunluk veren hyalüronik asit maskesi.</p>
            </div>
          </a>
          <a class="brandc reveal d1" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-lfl-anchor.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Grand Aespio · İp</div>
              <div class="brandc__logo" style="font-size:18px;">LFL Anchor</div>
              <p>Güçlü tutuş sağlayan çapalı askı (anchor) ip serisi.</p>
            </div>
          </a>

          <!-- Seffiline -->
          <a class="brandc reveal d2" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-seffihair.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Seffiline · Mezoterapi</div>
              <div class="brandc__logo" style="font-size:18px;">SeffiHair</div>
              <p>Saç dökülmesine karşı saçlı deri mezoterapi solüsyonu.</p>
            </div>
          </a>
          <a class="brandc reveal" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-sefficare.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Seffiline · Kozmetik</div>
              <div class="brandc__logo" style="font-size:18px;">SeffiCare</div>
              <p>Günlük cilt bakımı için kozmetik onarım serisi.</p>
            </div>
          </a>

          <!-- Woorhi -->
          <a class="brandc reveal d1" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-raffine.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Woorhi · Mezoterapi Tabancası</div>
              <div class="brandc__logo" style="font-size:18px;">Raffine</div>
              <p>Kore mühendisliğiyle geliştirilen profesyonel mezoterapi cihazı.</p>
            </div>
          </a>

          <!-- Mi Medical -->
          <a class="brandc reveal d2" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-pistor-eliance.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Mi Medical · Mezoterapi Tabancası</div>
              <div class="brandc__logo" style="font-size:18px;">Pistor Eliance</div>
              <p>Premium enjeksiyon sistemi.</p>
            </div>
          </a>

        </div>
      </div>
    </section>

    
EDV2P2S3;
        $p2s4 = <<<'EDV2P2S4'
<!-- INFO BAND + CTA -->

    <section class="section" style="background:var(--surface);border-top:1px solid var(--line);">
      <div class="wrap">
        <div class="info-row reveal" style="padding:26px 30px;flex-wrap:wrap;gap:18px;">
          <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m21 8-9-5-9 5v8l9 5 9-5V8Z"/><path d="m3 8 9 5 9-5"/><path d="M12 13v8"/></svg></span>
          <div style="flex:1 1 320px;"><b style="font-weight:600;">Toplam 95+ ürün</b><small>Fiyat ve sipariş için WhatsApp: +90 542 620 51 00</small></div>
          <a class="btn btn--ghost" href="https://wa.me/905426205100" target="_blank" rel="noopener" style="flex-shrink:0;">
            <svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9z"/></svg>
            WhatsApp'tan Yaz</a>
        </div>
      </div>
    </section>
EDV2P2S4;
        $p3s0 = <<<'EDV2P3S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"><!-- PAGE HERO / BREADCRUMB -->

    <section class="phero">
      <div class="wrap">
        <nav class="crumb" aria-label="Breadcrumb">
          <a href="/">Ana Sayfa</a> <span aria-hidden="true">/</span>
          <a href="/urunler">Ürünler</a> <span aria-hidden="true">/</span>
          RRS® HA Long Lasting
        </nav>
        <h1 class="reveal">RRS® HA Long Lasting</h1>
        <p class="lead reveal d1">Çapraz bağlı, emilebilir hyalüronik asit içeren CE Class III dermal implant — uzun etkili skinbooster.</p>
      </div>
    </section>

    
EDV2P3S0;
        $p3s1 = <<<'EDV2P3S1'
<!-- PRODUCT DETAIL (split) -->

    <section class="section">
      <div class="wrap">
        <div class="feature">
          <div class="feature__media reveal">
            <!-- 🖼️ GÖRSEL: assets/img/product-rrs-ha-long-lasting.jpg (oran 5:4) — RRS HA macro ürün çekimi -->
            <div class="feature__img" style="background-image:url('assets/img/product-rrs-ha-long-lasting.jpg')"></div>
          </div>
          <div class="reveal d1">
            <span class="eyebrow">Skin Tech · RRS</span>
            <h2>RRS® HA Long Lasting</h2>
            <p class="lead">Çapraz bağlı, emilebilir hyalüronik asit içeren dermal implant. Amino asit içeren koruyucu tampon solüsyonunda; cilt kalitesini içten iyileştiren uzun etkili skinbooster.</p>
            <p style="margin:18px 0 22px;"><span class="badge-ce">● CE Class III · Tıbbi cihaz sınıfı</span></p>
            <ul style="list-style:none;margin:0 0 28px;padding:0;display:grid;gap:12px;">
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;font-size:15.5px;"><span style="color:var(--accent);font-size:18px;flex-shrink:0;">✓</span> CE Class III sertifikalı</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;font-size:15.5px;"><span style="color:var(--accent);font-size:18px;flex-shrink:0;">✓</span> Steril tıbbi enjektör formu</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;font-size:15.5px;"><span style="color:var(--accent);font-size:18px;flex-shrink:0;">✓</span> Amino asit içeren koruyucu tampon solüsyonu</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;font-size:15.5px;"><span style="color:var(--accent);font-size:18px;flex-shrink:0;">✓</span> Uzun etkili (long lasting)</li>
              <li style="display:flex;align-items:center;gap:12px;color:var(--ink);font-weight:600;font-size:15.5px;"><span style="color:var(--accent);font-size:18px;flex-shrink:0;">✓</span> Cilt gençleştirme &amp; nemlendirme</li>
            </ul>
            <div class="hero__cta">
              <a class="btn btn--ghost" href="https://wa.me/905426205100" target="_blank" rel="noopener">
                <svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9z"/></svg>
                WhatsApp ile Sipariş</a>
              <a class="btn btn--primary" href="/iletisim">Teklif İste</a>
            </div>
            <p style="margin:22px 0 0;font-size:13.5px;color:var(--muted);display:flex;align-items:center;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.8" aria-hidden="true" style="flex-shrink:0;"><path d="M12 3 5 6v5c0 4 3 7 7 8 4-1 7-4 7-8V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg> Yalnızca hekim/klinik kullanımına yöneliktir.</p>
          </div>
        </div>
      </div>
    </section>

    
EDV2P3S1;
        $p3s2 = <<<'EDV2P3S2'
<!-- PRODUCT DESCRIPTION (rich text) -->

    <section class="section" style="background:var(--surface);border-block:1px solid var(--line);">
      <div class="wrap" style="max-width:860px;">
        <div class="reveal" style="margin-bottom:46px;">
          <span class="eyebrow">Ürün Açıklaması</span>
          <h2 style="font-size:clamp(24px,3vw,32px);margin:18px 0 16px;">Çapraz bağlı HA ile uzun etkili tazelenme</h2>
          <p style="color:var(--body);font-size:17px;line-height:1.8;margin:0 0 14px;">RRS® HA Long Lasting, çapraz bağlı (cross-linked) ve tamamen emilebilir hyalüronik asit içeren steril bir dermal implanttır. Kullanıma hazır tıbbi enjektör formunda sunulan ürün, amino asit içeren koruyucu bir tampon solüsyonunda çözülerek dokuyla yüksek biyouyum sağlar.</p>
          <p style="color:var(--body);font-size:17px;line-height:1.8;margin:0;">Cildin derin katmanlarına uygulanan formül, dermisi içten nemlendirir, su tutma kapasitesini artırır ve doku kalitesini iyileştirir. Çapraz bağlı yapısı sayesinde etkisini daha uzun süre koruyarak (long lasting), tek seansta belirgin tazelenme ve canlanma sağlar.</p>
        </div>

        <div class="reveal" style="margin-bottom:46px;">
          <span class="eyebrow">Kullanım Alanları</span>
          <h2 style="font-size:clamp(24px,3vw,32px);margin:18px 0 16px;">Skinbooster protokolleri için</h2>
          <p style="color:var(--body);font-size:17px;line-height:1.8;margin:0 0 18px;">Cilt gençleştirme ve skinbooster protokollerinde, dokunun nem dengesini ve elastikiyetini desteklemek amacıyla aşağıdaki bölgelerde uygulanabilir:</p>
          <div class="catgrid">
            <div class="cat"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M9 15c1 1 5 1 6 0"/><circle cx="9" cy="10" r="1"/><circle cx="15" cy="10" r="1"/></svg></span><b>Yüz</b></div>
            <div class="cat"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M8 3v4a4 4 0 0 0 8 0V3M6 21a6 6 0 0 1 12 0"/></svg></span><b>Boyun</b></div>
            <div class="cat"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 8h16M6 8V5h12v3M5 8l1 12h12l1-12"/></svg></span><b>Dekolte</b></div>
            <div class="cat"><span class="cat__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 11V5a2 2 0 0 1 4 0v6M10 11V4a2 2 0 0 1 4 0v7M14 11V6a2 2 0 0 1 4 0v9a6 6 0 0 1-6 6H9a5 5 0 0 1-5-5l-1-3"/></svg></span><b>El sırtı</b></div>
          </div>
        </div>

        <div class="reveal">
          <span class="eyebrow">İçerik &amp; Özellikler</span>
          <h2 style="font-size:clamp(24px,3vw,32px);margin:18px 0 16px;">Teknik özet</h2>
          <ul style="list-style:none;margin:0;padding:0;display:grid;gap:12px;">
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--body);font-size:16px;line-height:1.7;"><span style="color:var(--accent-ink);font-weight:800;flex-shrink:0;">•</span><span><strong style="color:var(--ink);">Çapraz bağlı hyalüronik asit:</strong> emilebilir, uzun etkili dermal implant yapısı.</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--body);font-size:16px;line-height:1.7;"><span style="color:var(--accent-ink);font-weight:800;flex-shrink:0;">•</span><span><strong style="color:var(--ink);">Koruyucu tampon solüsyonu:</strong> amino asit içeren, dengeli çözücü ortam.</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--body);font-size:16px;line-height:1.7;"><span style="color:var(--accent-ink);font-weight:800;flex-shrink:0;">•</span><span><strong style="color:var(--ink);">Steril enjektör formu:</strong> kullanıma hazır, tek kullanımlık tıbbi sunum.</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--body);font-size:16px;line-height:1.7;"><span style="color:var(--accent-ink);font-weight:800;flex-shrink:0;">•</span><span><strong style="color:var(--ink);">Sertifikasyon:</strong> CE Class III tıbbi cihaz onayı.</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--body);font-size:16px;line-height:1.7;"><span style="color:var(--accent-ink);font-weight:800;flex-shrink:0;">•</span><span><strong style="color:var(--ink);">Endikasyon:</strong> cilt gençleştirme, nemlendirme ve doku kalitesi iyileştirme.</span></li>
            <li style="display:flex;align-items:flex-start;gap:12px;color:var(--body);font-size:16px;line-height:1.7;"><span style="color:var(--accent-ink);font-weight:800;flex-shrink:0;">•</span><span><strong style="color:var(--ink);">Üretici:</strong> Skin Tech Pharma Group.</span></li>
          </ul>
        </div>
      </div>
    </section>

    
EDV2P3S2;
        $p3s3 = <<<'EDV2P3S3'
<!-- RELATED PRODUCTS -->

    <section class="section">
      <div class="wrap">
        <div class="reveal" style="display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:44px;">
          <div style="max-width:560px;">
            <span class="eyebrow">Benzer Ürünler</span>
            <h2 style="font-size:clamp(28px,3.6vw,42px);margin-top:18px;">İlginizi çekebilecek diğer ürünler</h2>
          </div>
          <a class="link-arrow" href="/urunler">Tüm ürünler <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="brandgrid">
          <a class="brandc reveal" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-melablock-hsp-spf-50.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Skin Tech · Krem</div>
              <div class="brandc__logo" style="font-size:18px;">Melablock HSP SPF 50+</div>
              <p>Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktör.</p>
            </div>
          </a>
          <a class="brandc reveal d1" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-benebellum-lumina-vit-c-18.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Skin Tech · Mezoterapi</div>
              <div class="brandc__logo" style="font-size:18px;">Benebellum LUMINA VİT-C 18%</div>
              <p>Yüksek konsantrasyonlu C vitamini ile aydınlatıcı bakım.</p>
            </div>
          </a>
          <a class="brandc reveal d2" href="/urun-detay" style="padding:0;overflow:hidden;">
            <div class="feature__img" style="aspect-ratio:5/4;background-image:url('assets/img/product-atrofillin.jpg');"></div>
            <div style="padding:24px 26px;">
              <div class="brandc__origin">Skin Tech · RRS</div>
              <div class="brandc__logo" style="font-size:18px;">Atrofillin</div>
              <p>Atrofik ve yıpranmış cilt için yenileyici dermal enjeksiyon çözümü.</p>
            </div>
          </a>
        </div>
      </div>
    </section>

    
EDV2P3S3;
        $p3s4 = <<<'EDV2P3S4'
<!-- CTA / FOR DOCTORS -->

    <section class="section" style="background:var(--surface);border-top:1px solid var(--line);">
      <div class="wrap">
        <div class="docs reveal">
          <div class="docs__grid" aria-hidden="true"></div>
          <span class="eyebrow" style="color:#F4A14E;">Bize Ulaşın</span>
          <h2 style="margin-top:18px;max-width:660px;">Ürün, fiyat ve eğitim için ekibimiz hazır</h2>
          <p class="lead" style="max-width:600px;margin-top:14px;">WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın. Resmi distribütör güvencesiyle %100 orijinal ürünler.</p>
          <div class="hero__cta" style="margin-top:30px;">
            <a class="btn btn--wa" href="https://wa.me/905426205100" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4z"/></svg>
              WhatsApp ile Yaz</a>
            <a class="btn btn--ghost" href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener" style="color:#fff;border-color:rgba(255,255,255,.3);">MEDINET Portalı</a>
          </div>
        </div>
      </div>
    </section>
EDV2P3S4;
        $p4s0 = <<<'EDV2P4S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"><!-- PAGE HERO -->

    <section class="phero">
      <div class="wrap">
        <nav class="crumb" aria-label="Sayfa konumu">
          <a href="/">Ana Sayfa</a> / <span>Markalar</span>
        </nav>
        <span class="eyebrow reveal">Temsil Ettiğimiz Markalar</span>
        <h1 class="reveal d1">Temsil Ettiğimiz Markalar</h1>
        <p class="lead reveal d2">Her biri kendi alanında uzman, uluslararası 6 marka — Türkiye'deki resmi temsilcisi Estetik Dermal. Kimyasal peeling ve mezoterapiden cihaz teknolojisi ve K-beauty'ye uzanan kapsamlı bir portföy.</p>
      </div>
    </section>

    
EDV2P4S0;
        $p4s1 = <<<'EDV2P4S1'
<!-- BRAND CARDS -->

    <section class="section">
      <div class="wrap">
        <div class="brandgrid">

          <!-- Skin Tech Pharma Group -->
          <a class="brandc reveal" href="/marka-skintech">
            <!-- 🖼️ GÖRSEL: assets/img/brand-panel-skintech.jpg (oran 16:9) — premium minimal ürün still life -->
            <div class="brandc__img" style="background-image:url('assets/img/brand-panel-skintech.jpg')"></div>
            <div class="brandc__origin">İspanya · Amiral Marka</div>
            <div class="brandc__logo">Skin Tech Pharma Group</div>
            <p>Kimyasal peeling, mezoterapi ve RRS skinbooster serisinde dünya lideri. CE Class III sertifikalı portföyün omurgası.</p>
            <span class="link-arrow" style="margin-top:18px;">Markayı Keşfet <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>

          <!-- MI-Medical Innovation -->
          <a class="brandc reveal d1" href="/marka-mi-medical">
            <div class="brandc__img" style="background-image:url('assets/img/brand-panel-mi-medical.jpg')"></div>
            <div class="brandc__origin">Premium · Enjeksiyon Sistemleri</div>
            <div class="brandc__logo">MI-Medical Innovation</div>
            <p>Premium mezoterapi ve enjeksiyon sistemleri; hassas uygulama için geliştirilmiş profesyonel çözümler.</p>
            <span class="link-arrow" style="margin-top:18px;">Markayı Keşfet <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>

          <!-- Neogenesis -->
          <a class="brandc reveal d2" href="/marka-neogenesis">
            <div class="brandc__img" style="background-image:url('assets/img/brand-panel-neogenesis.jpg')"></div>
            <div class="brandc__origin">Portföy · Rejeneratif Bakım</div>
            <div class="brandc__logo">Neogenesis</div>
            <p>Rejeneratif cilt bakımı çözümleri; cilt yenilenmesini destekleyen ileri formüllerle portföyümüzü tamamlar.</p>
            <span class="link-arrow" style="margin-top:18px;">Markayı Keşfet <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>

          <!-- Seffiline -->
          <a class="brandc reveal" href="/marka-seffiline">
            <div class="brandc__img" style="background-image:url('assets/img/brand-panel-seffiline.jpg')"></div>
            <div class="brandc__origin">Bakım &amp; Dolgu Serisi</div>
            <div class="brandc__logo">Seffiline</div>
            <p>Cilt, saç, intim bakım ve dolgu çözümleri serisi. Geniş kullanım alanına yayılan bütünsel bir bakım yelpazesi.</p>
            <span class="link-arrow" style="margin-top:18px;">Markayı Keşfet <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>

          <!-- Woorhi Mechatronics -->
          <a class="brandc reveal d1" href="/marka-woorhi">
            <div class="brandc__img" style="background-image:url('assets/img/brand-panel-woorhi.jpg')"></div>
            <div class="brandc__origin">Güney Kore · Mekatronik</div>
            <div class="brandc__logo">Woorhi Mechatronics</div>
            <p>Kore mühendisliğiyle geliştirilen medikal estetik cihazları. Mekatronik hassasiyetle klinik performans.</p>
            <span class="link-arrow" style="margin-top:18px;">Markayı Keşfet <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>

          <!-- Grand Aespio -->
          <a class="brandc reveal d2" href="/marka-aespio">
            <div class="brandc__img" style="background-image:url('assets/img/brand-panel-aespio.jpg')"></div>
            <div class="brandc__origin">K-Beauty · Thread Lift</div>
            <div class="brandc__logo">Grand Aespio</div>
            <p>Yüz maskeleri ve ip askı (thread lift) ürünleri. Modern K-beauty yaklaşımıyla estetik bakım çözümleri.</p>
            <span class="link-arrow" style="margin-top:18px;">Markayı Keşfet <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>

        </div>
      </div>
    </section>

    
EDV2P4S1;
        $p4s2 = <<<'EDV2P4S2'
<!-- KISA BANT -->

    <section class="section" style="padding-block:0;">
      <div class="wrap">
        <div class="band-note reveal">
          <span class="band-note__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="9" r="6"/><path d="m9 14.5-2 7 5-3 5 3-2-7"/><path d="m12 6 1 2 2 .3-1.5 1.5.4 2L12 11l-1.9 1 .4-2L9 8.3 11 8Z"/></svg></span>
          <p>Portföyümüzde ayrıca <strong>Neogenesis</strong> rejeneratif bakım serisi ve <strong>CE Class III sertifikalı RRS</strong> skinbooster serisi yer alır.</p>
        </div>
      </div>
    </section>

    
EDV2P4S2;
        $p4s3 = <<<'EDV2P4S3'
<!-- CTA -->

    <section class="section">
      <div class="wrap">
        <div class="docs reveal" style="text-align:center;">
          <div class="docs__grid" aria-hidden="true"></div>
          <span class="eyebrow" style="color:#F4A14E;justify-content:center;">Bize Ulaşın</span>
          <h2 style="margin:18px auto 14px;max-width:660px;">Profesyonel çözümler için buradayız</h2>
          <p class="lead" style="max-width:600px;margin:0 auto 30px;">Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.</p>
          <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
            <a class="btn" href="https://wa.me/905426205100" target="_blank" rel="noopener" style="background:#25D366;color:#fff;">
              <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9z"/></svg>
              WhatsApp ile Yaz</a>
            <a class="btn btn--ghost" href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener" style="color:#fff;border-color:rgba(255,255,255,.32);">MEDINET Portalı</a>
          </div>
        </div>
      </div>
    </section>
EDV2P4S3;
        $p5s0 = <<<'EDV2P5S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"><!-- PAGE HERO -->

    <section class="phero">
      <div class="wrap">
        <nav class="crumb" aria-label="Sayfa konumu">
          <a href="/">Ana Sayfa</a> / <span>Eğitim &amp; Kongre</span>
        </nav>
        <span class="eyebrow reveal">Kongre &amp; Etkinlikler</span>
        <h1 class="reveal d1">Kongre &amp; Etkinlikler</h1>
        <p class="lead reveal d2">Estetik Dermal olarak yer aldığımız ulusal ve uluslararası kongreler, fuarlar ve hekimlere yönelik uygulamalı eğitim etkinlikleri.</p>
      </div>
    </section>

    
EDV2P5S0;
        $p5s1 = <<<'EDV2P5S1'
<!-- EVENT GRID -->

    <section class="section">
      <div class="wrap">
        <div class="band-note reveal" style="margin-bottom:40px;">
          <span class="band-note__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/></svg></span>
          <p>Aşağıdaki etkinlikler temsili örneklerdir; gerçek tarihler ve katılım bilgileri onaylandıkça güncellenecektir.</p>
        </div>

        <div class="eventgrid">

          <!-- Etkinlik 1 -->
          <article class="eventc reveal">
            <!-- 🖼️ GÖRSEL: assets/img/event-1.jpg (oran 16:9) — uluslararası estetik kongresi -->
            <div class="eventc__media" style="background-image:url('assets/img/event-1.jpg')">
              <span class="eventc__date"><small>Oca</small><b>29</b></span>
            </div>
            <div class="eventc__body">
              <h3>IMCAS World Congress 2026</h3>
              <p class="eventc__place"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>Paris, Fransa</p>
              <p class="eventc__desc">Dünyanın en kapsamlı estetik dermatoloji kongresinde son teknoloji ürün ve uygulamalarla yer alıyoruz.</p>
            </div>
          </article>

          <!-- Etkinlik 2 -->
          <article class="eventc reveal d1">
            <div class="eventc__media" style="background-image:url('assets/img/event-2.jpg')">
              <span class="eventc__date"><small>Mar</small><b>14</b></span>
            </div>
            <div class="eventc__body">
              <h3>Anti-Aging &amp; Estetik Kongresi</h3>
              <p class="eventc__place"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>İstanbul</p>
              <p class="eventc__desc">Yaşlanma karşıtı uygulamalarda güncel protokoller; standımızda ürün ve cihaz demoları.</p>
            </div>
          </article>

          <!-- Etkinlik 3 -->
          <article class="eventc reveal d2">
            <div class="eventc__media" style="background-image:url('assets/img/event-3.jpg')">
              <span class="eventc__date"><small>May</small><b>22</b></span>
            </div>
            <div class="eventc__body">
              <h3>Dermatoloji &amp; Kozmetoloji Günleri</h3>
              <p class="eventc__place"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>Antalya</p>
              <p class="eventc__desc">Mezoterapi ve peeling odaklı bilimsel oturumlar ile interaktif uygulama atölyeleri.</p>
            </div>
          </article>

          <!-- Etkinlik 4 -->
          <article class="eventc reveal">
            <div class="eventc__media" style="background-image:url('assets/img/event-4.jpg')">
              <span class="eventc__date"><small>Haz</small><b>18</b></span>
            </div>
            <div class="eventc__body">
              <h3>Skin Tech Uygulamalı Eğitim Workshop</h3>
              <p class="eventc__place"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>Kuşadası, Aydın</p>
              <p class="eventc__desc">Hekimlere yönelik birebir, uygulamalı RRS ve peeling eğitimi; sınırlı kontenjanlı atölye.</p>
            </div>
          </article>

          <!-- Etkinlik 5 -->
          <article class="eventc reveal d1">
            <div class="eventc__media" style="background-image:url('assets/img/event-5.jpg')">
              <span class="eventc__date"><small>Eyl</small><b>26</b></span>
            </div>
            <div class="eventc__body">
              <h3>FACE Aesthetic Conference</h3>
              <p class="eventc__place"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>İzmir</p>
              <p class="eventc__desc">Yüz estetiğinde ip askı ve dolgu yeniliklerinin paylaşıldığı bölgesel uzman buluşması.</p>
            </div>
          </article>

          <!-- Etkinlik 6 -->
          <article class="eventc reveal d2">
            <div class="eventc__media" style="background-image:url('assets/img/event-6.jpg')">
              <span class="eventc__date"><small>Kas</small><b>12</b></span>
            </div>
            <div class="eventc__body">
              <h3>Medikal Estetik Fuarı</h3>
              <p class="eventc__place"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>Ankara</p>
              <p class="eventc__desc">Temsil ettiğimiz tüm markaların ürün ve cihazlarını yakından inceleyebileceğiniz fuar standı.</p>
            </div>
          </article>

        </div>

        <p style="margin:32px 0 0;font-size:12.5px;color:var(--muted);line-height:1.6;font-style:italic;">* Bu sayfadaki etkinlikler temsili örnek amaçlıdır. Kesin tarihler, mekânlar ve katılım koşulları onaylandıkça güncellenecektir.</p>
      </div>
    </section>

    
EDV2P5S1;
        $p5s2 = <<<'EDV2P5S2'
<!-- INFO BLOCK -->

    <section class="section" style="padding-top:0;">
      <div class="wrap">
        <div class="infoband reveal">
          <div class="infoband__copy">
            <h2>Etkinlik takvimi ve katılım için bizimle iletişime geçin</h2>
            <p class="lead">Yaklaşan kongreler, fuar standlarımız ve uygulamalı eğitim atölyelerimize katılım hakkında güncel bilgi almak için ekibimize ulaşın.</p>
          </div>
          <div class="infoband__cta">
            <a class="btn" href="https://wa.me/905426205100" target="_blank" rel="noopener" style="background:#25D366;color:#fff;">
              <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9z"/></svg>
              WhatsApp ile Yaz</a>
            <a class="btn btn--ghost" href="/iletisim">İletişim</a>
          </div>
        </div>
      </div>
    </section>

    
EDV2P5S2;
        $p5s3 = <<<'EDV2P5S3'
<!-- CTA -->

    <section class="section" style="padding-top:0;">
      <div class="wrap">
        <div class="docs reveal" style="text-align:center;">
          <div class="docs__grid" aria-hidden="true"></div>
          <span class="eyebrow" style="color:#F4A14E;justify-content:center;">Bize Ulaşın</span>
          <h2 style="margin:18px auto 14px;max-width:660px;">Profesyonel çözümler için buradayız</h2>
          <p class="lead" style="max-width:600px;margin:0 auto 30px;">Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da MEDINET portalına giriş yapın.</p>
          <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
            <a class="btn" href="https://wa.me/905426205100" target="_blank" rel="noopener" style="background:#25D366;color:#fff;">
              <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9z"/></svg>
              WhatsApp ile Yaz</a>
            <a class="btn btn--ghost" href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener" style="color:#fff;border-color:rgba(255,255,255,.32);">MEDINET Portalı</a>
          </div>
        </div>
      </div>
    </section>
EDV2P5S3;
        $p6s0 = <<<'EDV2P6S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"><!-- PAGE HERO -->

    <section class="phero">
      <div class="wrap">
        <nav class="crumb" aria-label="Sayfa konumu">
          <a href="/">Ana Sayfa</a> / <span>İletişim</span>
        </nav>
        <span class="eyebrow reveal">İletişim</span>
        <h1 class="reveal d1">İletişim</h1>
        <p class="lead reveal d2">Ürün, fiyat ve eğitim talepleriniz için bize ulaşın. Ekibimiz en kısa sürede sizinle iletişime geçsin.</p>
      </div>
    </section>

    
EDV2P6S0;
        $p6s1 = <<<'EDV2P6S1'
<!-- CONTACT -->

    <section class="section">
      <div class="wrap">
        <div class="contact">

          <!-- SOL: form -->
          <form class="reveal" action="#" method="post" novalidate>
            <span class="eyebrow">Talep Formu</span>
            <h2 style="font-size:clamp(24px,3vw,34px);margin:16px 0 10px;">Bize yazın, size dönelim</h2>
            <p style="font-size:14px;color:var(--muted);margin:0 0 24px;line-height:1.6;">Form yalnızca yapı amaçlıdır; CMS'e bağlandığında <code>/api/v1/forms/.../submit</code> ile çalışacaktır.</p>
            <div class="field"><label for="ad">Ad Soyad</label><input id="ad" name="ad" type="text" autocomplete="name" placeholder="Adınız ve soyadınız"></div>
            <div class="field"><label for="kurum">Klinik / Kurum</label><input id="kurum" name="kurum" type="text" autocomplete="organization" placeholder="Klinik veya kurum adınız"></div>
            <div class="field"><label for="tel">Telefon</label><input id="tel" name="tel" type="tel" autocomplete="tel" placeholder="0 5xx xxx xx xx"></div>
            <div class="field"><label for="eposta">E-posta</label><input id="eposta" name="eposta" type="email" autocomplete="email" placeholder="ornek@eposta.com"></div>
            <div class="field"><label for="mesaj">Mesaj</label><textarea id="mesaj" name="mesaj" rows="4" placeholder="Talebinizi kısaca yazın..."></textarea></div>
            <button class="btn btn--primary" type="submit" style="width:100%;justify-content:center;">Gönder</button>
          </form>

          <!-- SAĞ: bilgi kartları + harita -->
          <div class="contact__info reveal d1">
            <a class="info-row" href="tel:+902566121813">
              <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg></span>
              <div><small>Telefon</small><b>0 256 612 18 13</b></div>
            </a>
            <a class="info-row" href="https://wa.me/905426205100" target="_blank" rel="noopener">
              <span class="ic" style="background:rgba(37,211,102,.14);color:#25D366;"><svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 4 1.7 4 1.2 4.7 1.1.7-.1 2.4-1 2.7-1.9.3-1 .3-1.8.2-1.9z"/></svg></span>
              <div><small>WhatsApp</small><b>+90 542 620 51 00</b></div>
            </a>
            <a class="info-row" href="mailto:info@estetikdermal.com">
              <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg></span>
              <div><small>E-posta</small><b>info@estetikdermal.com</b></div>
            </a>
            <a class="info-row" href="https://maps.google.com/maps?q=Kusadasi%20Aydin" target="_blank" rel="noopener">
              <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.5 7-11a7 7 0 0 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
              <div><small>Adres</small><b style="font-weight:500;font-size:14px;">Türkmen Mah. Turgut Özel Bulvarı, Ada Modern A Blok No 83/3A · Kuşadası / Aydın</b></div>
            </a>
            <div class="contact__map">
              <iframe title="Estetik Dermal — Kuşadası / Aydın konum haritası" src="https://maps.google.com/maps?q=Kusadasi%20Aydin&t=&z=12&ie=UTF8&iwloc=&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <a class="link-arrow" href="https://www.instagram.com/estetikdermal/" target="_blank" rel="noopener" style="margin-top:4px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg>
              Instagram'da takip edin
            </a>
          </div>

        </div>
      </div>
    </section>
EDV2P6S1;
        $p7s0 = <<<'EDV2P7S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400..600;1,6..72,400..500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"><style>
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
          <p class="st-hero__loc reveal"><span class="pin" aria-hidden="true"></span>İspanya · Klinik Dermokozmetik</p>
          <h1 id="st-h1" class="reveal d1">Skin Tech Pharma Group.<br><em>Klinik kanıtın</em> otoritesi.</h1>
          <p class="st-hero__sub reveal d2">Kimyasal peeling, mezoterapi ve RRS® skinbooster serisinde dünya çapında öncü. Laboratuvar disiplini, dermatolojik kanıt ve CE&nbsp;Class&nbsp;III standartlarıyla geliştirilen profesyonel çözümler.</p>
          <div class="st-hero__cta reveal d3">
            <a class="st-btn st-btn--solid" href="#urunler">Ürünleri İncele
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            <a class="st-btn st-btn--wa" href="https://wa.me/905426205100" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#062b14" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 4 1.7 4 1.2 4.7 1.1.7-.1 2.4-1 2.7-1.9.3-1 .3-1.8.2-1.9z"/></svg>
              Bilgi Al — WhatsApp</a>
          </div>
          <div class="st-hero__meta reveal d4">
            <div><small>Sertifikasyon</small><b>CE Class III</b></div>
            <div><small>Menşei</small><b>İspanya</b></div>
            <div><small>Portföy</small><b>84+ Ürün</b></div>
          </div>
        </div>

        <div class="st-hv reveal d2">
          <div class="st-hv__glow" aria-hidden="true"></div>
          <div class="st-hv__card">
            <div class="st-hv__img" role="img" aria-label="RRS skinbooster ampul ve serum şişesinin klinik laboratuvar çekimi">
              <span class="st-hv__tag">RRS® Skinbooster</span>
            </div>
            <div class="st-hv__cap">
              <div>
                <small>Skin Tech · RRS</small>
                <b>RRS® HA Long Lasting</b>
              </div>
              <span class="st-ce" aria-label="CE Class III"><span><b>CE</b><small>CLASS III</small></span></span>
            </div>
          </div>
          <div class="st-hv__float" aria-hidden="true">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2a7 7 0 0 1 7 7c0 1.6-.4 3-1 4l-6 9-6-9c-.6-1-1-2.4-1-4a7 7 0 0 1 7-7z"/><path d="M9 9l2 2 4-4"/></svg></span>
            <div><small>Dermatolojik</small><b>Klinik Test Edildi</b></div>
          </div>
        </div>
      </div>
    </section>

    
EDV2P7S0;
        $p7s1 = <<<'EDV2P7S1'
<!-- 2 · MARKA HİKAYESİ -->

    <section class="st-sec st-sec--paper" aria-labelledby="st-story-h">
      <div class="st-wrap st-story">
        <div class="st-story__media reveal">
          <div class="st-story__img" role="img" aria-label="Skin Tech Pharma Group dermatolojik laboratuvar ve üretim ortamı"></div>
          <div class="st-story__quote">
            <span aria-hidden="true">&ldquo;</span>
            <p>Bilim, formülasyonun her aşamasında kanıtla doğrulanır.</p>
          </div>
        </div>
        <div class="reveal d1">
          <span class="st-kick">Marka Hikayesi</span>
          <h2 id="st-story-h" style="font-size:clamp(28px,3.8vw,44px);line-height:1.1;color:var(--navy);margin:18px 0 22px;">İspanya'dan, dermatolojinin diliyle yazılan bir bilim</h2>
          <p class="lead">Skin Tech Pharma Group, İspanya merkezli laboratuvarlarında <strong>kimyasal peeling, mezoterapi ve skinbooster</strong> alanlarında dermatolojik kanıta dayalı profesyonel çözümler geliştirir. Easy Phytic ve RRS® gibi referans formülleriyle dünya genelinde hekimlerin güvendiği bir klinik otoritedir.</p>
          <p class="lead">Türkiye'de <strong>Estetik Dermal</strong>; 2004'ten bu yana taşıdığı medikal estetik birikimiyle bu portföyün resmi temsilcisidir. Yalnızca tedarik değil; hekimlere uygulamalı eğitim, protokol desteği ve sürekli teknik danışmanlık sunar.</p>
          <p style="margin:26px 0 0;"><a class="st-link" href="/markalar">Tüm temsil ettiğimiz markalar
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></p>
        </div>
      </div>
    </section>

    
EDV2P7S1;
        $p7s2 = <<<'EDV2P7S2'
<!-- 3 · KREDİBİLİTE ŞERİDİ -->

    <section class="st-cred" aria-label="Kredibilite ve sertifikasyon">
      <div class="st-wrap" style="padding-inline:0;">
        <div class="st-cred__grid">
          <div class="st-cred__item reveal">
            <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2 4 5v6c0 5 3.5 8.5 8 11 4.5-2.5 8-6 8-11V5z"/><path d="M9 12l2 2 4-4"/></svg></span>
            <b>CE Class III</b><small>Tıbbi cihaz sınıfı sertifikası</small>
          </div>
          <div class="st-cred__item reveal d1">
            <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 2h6M10 2v6L5 18a2 2 0 0 0 1.8 3h10.4A2 2 0 0 0 19 18L14 8V2"/><path d="M7.5 14h9"/></svg></span>
            <b>İspanya Menşeli</b><small>Avrupa üretim ve kalite standardı</small>
          </div>
          <div class="st-cred__item reveal d2">
            <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M2 12h20M12 3c2.5 2.4 4 5.6 4 9s-1.5 6.6-4 9c-2.5-2.4-4-5.6-4-9s1.5-6.6 4-9z"/></svg></span>
            <b>Dünya Lideri</b><small>Peeling ve skinbooster alanında öncü</small>
          </div>
          <div class="st-cred__item reveal d3">
            <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/></svg></span>
            <b>84+ Ürün</b><small>Geniş profesyonel klinik portföy</small>
          </div>
        </div>
        <p class="st-cred__note reveal">Türkiye distribütörü: <b>Estetik Dermal</b> · Resmi temsilci · 2004'ten bu yana</p>
      </div>
    </section>

    
EDV2P7S2;
        $p7s3 = <<<'EDV2P7S3'
<!-- 4 · ÖNE ÇIKAN ÜRÜNLER (split) -->

    <section class="st-sec st-sec--paper" id="urunler" aria-labelledby="st-feat-h">
      <div class="st-wrap">
        <div class="st-head reveal">
          <span class="st-kick">Öne Çıkan Ürünler</span>
          <h2 id="st-feat-h">Portföyün referans iki ürünü</h2>
          <p>Birinde dermal implant disiplini, diğerinde 360° fotokoruma — ikisi de klinik protokollerin temel taşı.</p>
        </div>
        <div class="st-split">
          <article class="st-feat st-feat--a reveal">
            <div class="st-feat__img" role="img" aria-label="RRS HA Long Lasting çapraz bağlı hyalüronik asit skinbooster ürün çekimi">
              <span class="st-feat__badge"><span class="st-chip"><span class="dot" aria-hidden="true"></span>CE Class III</span></span>
            </div>
            <div class="st-feat__body">
              <span class="st-kick">Skinbooster · RRS®</span>
              <h3>RRS® HA Long Lasting</h3>
              <span class="st-chip"><span class="dot" aria-hidden="true"></span>Çapraz bağlı HA · Dermal implant</span>
              <p>Çapraz bağlı, emilebilir hyalüronik asit içeren CE&nbsp;Class&nbsp;III dermal implant. Amino asit içeren koruyucu tampon solüsyonunda; cilt kalitesini içten destekleyen uzun etkili skinbooster.</p>
              <a class="st-link" href="/urun-detay">Ürün detayı
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </div>
          </article>
          <article class="st-feat st-feat--b reveal d1">
            <div class="st-feat__img" role="img" aria-label="Melablock HSP SPF 50+ yüksek faktörlü güneş koruma ürünü çekimi">
              <span class="st-feat__badge"><span class="st-chip"><span class="dot" aria-hidden="true"></span>SPF 50+</span></span>
            </div>
            <div class="st-feat__body">
              <span class="st-kick">Fotokoruma · Premium</span>
              <h3>Melablock HSP SPF 50+</h3>
              <span class="st-chip"><span class="dot" aria-hidden="true"></span>360° fotokoruma · Leke savunması</span>
              <p>Cildi güneşin zararlı etkilerine karşı 360° koruyan yüksek faktörlü formül; lazer ve peeling sonrası termal hasara karşı dermatolojik savunma sağlar.</p>
              <a class="st-link" href="/urun-detay">Ürün detayı
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </div>
          </article>
        </div>
      </div>
    </section>

    
EDV2P7S3;
        $p7s4 = <<<'EDV2P7S4'
<!-- 5 · ÜRÜN YELPAZESİ (grid) -->

    <section class="st-sec st-sec--ice" aria-labelledby="st-range-h">
      <div class="st-wrap">
        <div class="st-head st-head--center reveal">
          <span class="st-kick" style="margin-inline:auto;">Ürün Yelpazesi</span>
          <h2 id="st-range-h">Bilim temelli dört temel seri</h2>
          <p>Skinbooster'dan mezoterapiye, kimyasal peelingden güneş korumaya — her seri dermatolojik kanıt ve klinik standartla geliştirildi.</p>
        </div>
        <div class="st-fam">

          <div class="st-fam__col reveal">
            <div class="st-fam__h"><span class="tag">Skinbooster</span><h3>RRS®</h3></div>
            <div class="st-fam__list">
              <a class="st-prod" href="/urun-detay"><span><b>RRS® HA Long Lasting</b><small>Çapraz bağlı HA · CE III</small></span><span class="arr" aria-hidden="true">→</span></a>
              <a class="st-prod" href="/urun-detay"><span><b>RRS® HA Cellular</b><small>Hücresel revitalizasyon</small></span><span class="arr" aria-hidden="true">→</span></a>
            </div>
          </div>

          <div class="st-fam__col reveal d1">
            <div class="st-fam__h"><span class="tag">Mezoterapi</span><h3>Benebellum</h3></div>
            <div class="st-fam__list">
              <a class="st-prod" href="/urun-detay"><span><b>Lumina Vit-C 18%</b><small>Aydınlatıcı C vitamini</small></span><span class="arr" aria-hidden="true">→</span></a>
              <a class="st-prod" href="/urun-detay"><span><b>Vit A + E</b><small>Antioksidan onarım</small></span><span class="arr" aria-hidden="true">→</span></a>
              <a class="st-prod" href="/urun-detay"><span><b>TX Solution</b><small>Leke karşıtı çözüm</small></span><span class="arr" aria-hidden="true">→</span></a>
            </div>
          </div>

          <div class="st-fam__col reveal d2">
            <div class="st-fam__h"><span class="tag">Peeling</span><h3>Kimyasal Peeling</h3></div>
            <div class="st-fam__list">
              <a class="st-prod" href="/urun-detay"><span><b>Aclaranse</b><small>Depigmentasyon peelingi</small></span><span class="arr" aria-hidden="true">→</span></a>
              <a class="st-prod" href="/urun-detay"><span><b>Easy Phytic</b><small>Nötralizasyonsuz fitik asit</small></span><span class="arr" aria-hidden="true">→</span></a>
            </div>
          </div>

          <div class="st-fam__col reveal d3">
            <div class="st-fam__h"><span class="tag">Bakım & SPF</span><h3>Kremler</h3></div>
            <div class="st-fam__list">
              <a class="st-prod" href="/urun-detay"><span><b>Melablock HSP SPF 50+</b><small>Yüksek faktör · leke koruması</small></span><span class="arr" aria-hidden="true">→</span></a>
              <a class="st-prod" href="/urun-detay"><span><b>Actilift Krem</b><small>Sıkılaştırıcı bakım</small></span><span class="arr" aria-hidden="true">→</span></a>
            </div>
          </div>

        </div>
        <p style="text-align:center;margin:44px 0 0;" class="reveal">
          <a class="st-btn st-btn--ghost" href="/urunler">Tüm Skin Tech ürünlerini gör
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </p>
      </div>
    </section>

    
EDV2P7S4;
        $p7s5 = <<<'EDV2P7S5'
<!-- 6 · DOKTORLAR İÇİN / UYGULAMA -->

    <section class="st-sec st-sec--paper" aria-labelledby="st-doc-h">
      <div class="st-wrap">
        <div class="st-doc reveal">
          <div class="st-doc__lines" aria-hidden="true"></div>
          <div class="st-doc__in">
            <span class="st-kick st-kick--light">Doktorlar İçin · Klinik Kullanım</span>
            <h2 id="st-doc-h">Sadece tedarik değil; protokolün her adımında yanınızda</h2>
            <p class="st-doc__lead">Skin Tech ürünlerinin klinikte doğru ve güvenli kullanımı için Estetik Dermal; uygulamalı eğitim, protokol kurulumu ve sürekli teknik destek sunar. Tüm içerik yalnızca profesyonel hekim kullanımına yöneliktir.</p>
            <div class="st-doc__cards">
              <div class="st-doc__card">
                <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></span>
                <h4>Uygulamalı Eğitim</h4>
                <p>Hekimlere birebir, uygulamalı ürün ve teknik kullanım eğitimleri.</p>
              </div>
              <div class="st-doc__card">
                <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V5a2 2 0 0 1 2-2h9l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><path d="M14 3v5h5M8 13h8M8 17h5"/></svg></span>
                <h4>Protokol Desteği</h4>
                <p>Doğru endikasyon, dozaj ve uygulama protokolü kurulumu için rehberlik.</p>
              </div>
              <div class="st-doc__card">
                <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 14h2"/></svg></span>
                <h4>MEDINET Platformu</h4>
                <p>Dijital sipariş, takip ve ürün bilgisine erişim için MEDINET portalı.</p>
              </div>
            </div>
            <div style="display:flex;gap:14px;flex-wrap:wrap;">
              <a class="st-btn st-btn--lightline" href="/etkinlikler">Eğitim ve etkinlikler
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
              <a class="st-btn st-btn--lightline" href="https://apps.skintechpharmagroup.com/medinet" target="_blank" rel="noopener">MEDINET Portalı
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8"/></svg></a>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDV2P7S5;
        $p7s6 = <<<'EDV2P7S6'
<!-- 7 · CTA BANDI -->

    <section class="st-sec st-sec--paper" style="padding-top:0;" id="iletisim" aria-labelledby="st-cta-h">
      <div class="st-wrap">
        <div class="st-cta reveal">
          <span class="st-cta__orb" aria-hidden="true" style="top:-50px;right:-50px;width:220px;height:220px;"></span>
          <span class="st-cta__orb" aria-hidden="true" style="bottom:-70px;left:-40px;width:260px;height:260px;background:rgba(127,179,232,.09);"></span>
          <span class="st-kick st-kick--light" style="justify-content:center;">İletişim</span>
          <h2 id="st-cta-h" style="margin-top:16px;">Skin Tech ürünleri hakkında bilgi alın</h2>
          <p>Ürün, fiyat ve eğitim talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da iletişim sayfamızdan bize ulaşın.</p>
          <div class="st-cta__row">
            <a class="st-btn st-btn--wa" href="https://wa.me/905426205100" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#062b14" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 4 1.7 4 1.2 4.7 1.1.7-.1 2.4-1 2.7-1.9.3-1 .3-1.8.2-1.9z"/></svg>
              WhatsApp ile Yaz</a>
            <a class="st-btn st-btn--lightline" href="/iletisim">İletişim Sayfası
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </div>
      </div>
    </section>
EDV2P7S6;
        $p8s0 = <<<'EDV2P8S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"><style>
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
      <div class="sf-blob" aria-hidden="true" style="top:-160px;right:-120px;width:480px;height:480px;background:radial-gradient(circle at 35% 35%, rgba(232,160,168,.32), transparent 70%);"></div>
      <div class="sf-blob" aria-hidden="true" style="bottom:-180px;left:-140px;width:420px;height:420px;background:radial-gradient(circle at 50% 50%, rgba(201,138,109,.20), transparent 70%);"></div>
      <div class="sf-blob" aria-hidden="true" style="top:46%;left:54%;width:140px;height:140px;border:1px solid rgba(201,138,109,.30);"></div>

      <div class="sf-wrap" style="position:relative;display:flex;flex-wrap:wrap;gap:56px;align-items:center;padding:clamp(72px,8vw,100px) 0 clamp(80px,9vw,108px);">
        <div style="flex:1 1 460px;">
          <p class="sf-eyebrow" data-sf>Güzelliğin İnce Dokunuşu</p>
          <h1 class="sf-serif" data-sf style="font-size:clamp(42px,6vw,74px);line-height:1.05;font-weight:500;color:var(--plum);margin:0 0 26px;">Cildinize özel<br><em style="font-style:italic;color:var(--rose);">bütüncül bakım</em></h1>
          <p data-sf class="d1" style="font-size:clamp(16px,2vw,19px);color:var(--plum-soft);line-height:1.85;max-width:520px;margin:0 0 38px;">Seffiline; cilt, saç, intim bakım ve dolgu çözümlerinde feminen ve profesyonel bir kozmesötik seri. Kadın sağlığı ve güzelliğine, zarafetle ve bilimle yaklaşır.</p>
          <div data-sf class="d2" style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;">
            <a href="#koleksiyon" class="sf-btn sf-btn-fill">Koleksiyonu İncele</a>
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" class="sf-btn sf-btn-wa">
              <svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
              Bilgi Al — WhatsApp</a>
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
            <div><div style="font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--rose);font-weight:700;">Seffiller</div><div class="sf-serif" style="font-size:16px;color:var(--plum);">Dolgu Serisi</div></div>
          </div>
          <div style="position:absolute;bottom:24px;right:-8px;background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow-soft);padding:14px 18px;display:flex;align-items:center;gap:11px;">
            <span style="width:34px;height:34px;flex-shrink:0;border-radius:50%;background:var(--grad-rose);"></span>
            <div><div style="font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--rose);font-weight:700;">SeffiCare</div><div class="sf-serif" style="font-size:16px;color:var(--plum);">Cilt Bakımı</div></div>
          </div>
        </div>
      </div>
    </section>

    
EDV2P8S0;
        $p8s1 = <<<'EDV2P8S1'
<!-- 2. MARKA HİKAYESİ -->

    <section style="padding:clamp(72px,9vw,104px) 0;background:var(--paper);">
      <div class="sf-wrap" style="max-width:820px;text-align:center;">
        <p class="sf-eyebrow sf-eyebrow--c sf-eyebrow--blush" data-sf>Marka Hikayesi</p>
        <p class="sf-serif" data-sf style="font-size:clamp(24px,3.4vw,36px);line-height:1.55;font-weight:400;color:var(--plum);margin:0 0 14px;font-style:italic;">"Her kadının güzelliği biriciktir. Seffiline, kadın sağlığı ve güzelliğine bütüncül bir yaklaşımla; cildi, saçı ve hassas bölgeleri aynı özen ve zarafetle ele alır."</p>
        <p data-sf class="d1" style="color:var(--plum-soft);font-size:17px;line-height:1.85;max-width:640px;margin:24px auto 36px;">Bilim ile zarafeti aynı şişede buluşturan Seffiline; gündelik bakımdan profesyonel uygulamalara uzanan, baştan ayağa bütüncül bir güzellik ritüeli sunar. Yumuşak, sıcak ve incelikli — tıpkı kendisine değer veren bir kadının dokunuşu gibi.</p>
        <div data-sf class="d1" style="display:flex;align-items:center;justify-content:center;gap:14px;margin:0 auto;max-width:240px;">
          <span style="flex:1;height:1px;background:linear-gradient(90deg,transparent,var(--line));"></span>
          <span style="color:var(--rose);font-size:18px;">❀</span>
          <span style="flex:1;height:1px;background:linear-gradient(90deg,var(--line),transparent);"></span>
        </div>
      </div>
    </section>

    
EDV2P8S1;
        $p8s2 = <<<'EDV2P8S2'
<!-- 3. KREDİBİLİTE ŞERİDİ -->

    <section style="padding:0 0 clamp(56px,7vw,88px);background:var(--paper);">
      <div class="sf-wrap">
        <div class="sf-cred" data-sf>
          <div>
            <span class="sf-ico" style="width:46px;height:46px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2 4 6v6c0 5 3.4 8.6 8 10 4.6-1.4 8-5 8-10V6z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <h4>%100 Orijinal</h4>
            <p>Her ürün sertifikalı, takip edilebilir ve tamamen orijinaldir.</p>
          </div>
          <div>
            <span class="sf-ico" style="width:46px;height:46px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg></span>
            <h4>Estetik Dermal Güvencesi</h4>
            <p>Resmi distribütör güvencesiyle, doğru kaynaktan tedarik.</p>
          </div>
          <div>
            <span class="sf-ico" style="width:46px;height:46px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="5"/><path d="M12 13v8M9 18h6"/></svg></span>
            <h4>20+ Yıl Deneyim</h4>
            <p>2004'ten bu yana medikal estetikte birikmiş uzmanlık.</p>
          </div>
          <div>
            <span class="sf-ico" style="width:46px;height:46px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg></span>
            <h4>Bütüncül Bakım</h4>
            <p>Cilt, saç, intim ve dolgu — tek bir feminen seri çatısında.</p>
          </div>
        </div>
      </div>
    </section>

    
EDV2P8S2;
        $p8s3 = <<<'EDV2P8S3'
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
          <p class="sf-eyebrow">Öne Çıkan · Saç Mezoterapi</p>
          <h2 class="sf-serif" style="font-size:clamp(30px,4.4vw,46px);font-weight:500;color:var(--plum);margin:0 0 22px;line-height:1.12;">SeffiHair ile<br><em style="font-style:italic;color:var(--rose);">kökten güçlü saçlar</em></h2>
          <p style="color:var(--plum-soft);font-size:17px;line-height:1.85;margin:0 0 28px;max-width:520px;">Saç dökülmesiyle mücadelede mezoterapi temelli bir yaklaşım. SeffiHair serisi, saç köküne ihtiyaç duyduğu vitamin ve mineralleri ileterek folikülleri besler; daha sağlıklı, dolgun ve canlı bir görünüm için zarif bir bakım ritüeli sunar.</p>
          <ul class="sf-feat-list">
            <li><span class="pet">❀</span> Saç köküne yoğun besin desteği</li>
            <li><span class="pet">❀</span> Mezoterapi ile uyumlu profesyonel formül</li>
            <li><span class="pet">❀</span> Dolgun ve canlı bir saç görünümü</li>
          </ul>
          <a href="/urun-detay" class="sf-btn sf-btn-fill">SeffiHair'i İncele →</a>
        </div>
      </div>
    </section>

    
EDV2P8S3;
        $p8s4 = <<<'EDV2P8S4'
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
          <p class="sf-eyebrow">Öne Çıkan · Dolgu Serisi</p>
          <h2 class="sf-serif" style="font-size:clamp(30px,4.4vw,46px);font-weight:500;color:var(--plum);margin:0 0 22px;line-height:1.12;">Seffiller ile<br><em style="font-style:italic;color:var(--rose);">doğal ve zarif hatlar</em></h2>
          <p style="color:var(--plum-soft);font-size:17px;line-height:1.85;margin:0 0 28px;max-width:520px;">Hyalüronik asit bazlı Seffiller dolgu serisi; yüz hatlarına doğal hacim ve zarif bir tazelik kazandırmak için tasarlanmıştır. İnce dokulu, akışkan ve işlenebilir formülüyle, abartısız ve incelikli sonuçlara zemin hazırlar.</p>
          <ul class="sf-feat-list">
            <li><span class="pet">❀</span> Çapraz bağlı hyalüronik asit yapısı</li>
            <li><span class="pet">❀</span> Doğal hacim ve zarif tazelik</li>
            <li><span class="pet">❀</span> Farklı yoğunluklarda esnek seçenekler</li>
          </ul>
          <a href="/urun-detay" class="sf-btn sf-btn-fill">Seffiller'i İncele →</a>
        </div>
      </div>
    </section>

    
EDV2P8S4;
        $p8s5 = <<<'EDV2P8S5'
<!-- 5. ÜRÜN AİLESİ GRID -->

    <section id="koleksiyon" style="padding:clamp(72px,9vw,100px) 0;background:var(--cream);">
      <div class="sf-wrap">
        <div style="text-align:center;max-width:620px;margin:0 auto 56px;">
          <p class="sf-eyebrow sf-eyebrow--c" data-sf>Koleksiyon</p>
          <h2 class="sf-serif" data-sf style="font-size:clamp(30px,4.4vw,46px);font-weight:500;color:var(--plum);margin:0 0 16px;line-height:1.12;">Dört ince ürün ailesi</h2>
          <p data-sf class="d1" style="color:var(--plum-soft);font-size:17px;line-height:1.8;">Baştan ayağa bütüncül bir bakım ritüeli; her ihtiyaca uygun, zarif ve profesyonel.</p>
        </div>

        <!-- 🖼️ GÖRSEL (opsiyonel kart görseli deseni): /assets/img/seffiline-product-{slug}.jpg (oran 1:1)
             slug'lar (ASCII güvenli): sefficare, seffigyn, seffihair, seffiller
             PROMPT: "Elegant feminine cosmetic product packshot, soft blush-pink and cream backdrop with rose-gold accents, luxury editorial beauty lighting, gentle diffused glow, refined delicate styling, single fresh petal detail, premium magazine aesthetic; photorealistic, detailed, high resolution; no text, no logo, no watermark"
             Kart ikon dairesinin yerine eklenebilir:
             <img src="/assets/img/seffiline-product-sefficare.jpg" alt="Seffiline SeffiCare ürün çekimi" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:22px;margin-bottom:20px;"> -->
        <div class="sf-grid" data-sf>
          <!-- SeffiCare -->
          <a href="/urun-detay" class="sf-prodcard sf-card">
            <span class="sf-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2C7 7 7 11 12 22 17 11 17 7 12 2z"/><path d="M9 13c2 1.5 4 1.5 6 0"/></svg></span>
            <h3 class="sf-serif" style="font-size:25px;font-weight:600;color:var(--plum);margin:0 0 4px;">SeffiCare</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--rose);font-weight:700;margin:0 0 14px;">Cilt Bakımı</p>
            <p style="color:var(--plum-soft);font-size:14.5px;line-height:1.75;margin:0 0 22px;">Cildi besleyen, nemlendiren ve canlandıran zarif cilt bakım serisi.</p>
            <span class="sf-link">İncele →</span>
          </a>
          <!-- SeffiGyn -->
          <a href="/urun-detay" class="sf-prodcard sf-card">
            <span class="sf-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="5"/><path d="M12 13v8M9 18h6"/></svg></span>
            <h3 class="sf-serif" style="font-size:25px;font-weight:600;color:var(--plum);margin:0 0 4px;">SeffiGyn</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--rose);font-weight:700;margin:0 0 14px;">İntim Bakım</p>
            <p style="color:var(--plum-soft);font-size:14.5px;line-height:1.75;margin:0 0 22px;">Hassas bölgelerin sağlığı için pH dengeli, nazik ve güvenilir intim bakım.</p>
            <span class="sf-link">İncele →</span>
          </a>
          <!-- SeffiHair -->
          <a href="/urun-detay" class="sf-prodcard sf-card">
            <span class="sf-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 4c0 8-2 10-2 14M12 4c0 9-1 11-1 14M18 4c0 8 2 10 2 14"/></svg></span>
            <h3 class="sf-serif" style="font-size:25px;font-weight:600;color:var(--plum);margin:0 0 4px;">SeffiHair</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--rose);font-weight:700;margin:0 0 14px;">Saç Bakımı &amp; Mezoterapi</p>
            <p style="color:var(--plum-soft);font-size:14.5px;line-height:1.75;margin:0 0 22px;">Saç kökünü güçlendiren mezoterapi ve yoğun bakım çözümleri.</p>
            <span class="sf-link">İncele →</span>
          </a>
          <!-- Seffiller -->
          <a href="/urun-detay" class="sf-prodcard sf-card">
            <span class="sf-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 2h6v3l-1 1v4l3 9a2 2 0 0 1-1.9 2.6H8.9A2 2 0 0 1 7 19l3-9V6L9 5z"/><path d="M9 13h6"/></svg></span>
            <h3 class="sf-serif" style="font-size:25px;font-weight:600;color:var(--plum);margin:0 0 4px;">Seffiller</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--rose);font-weight:700;margin:0 0 14px;">Dolgu Serisi</p>
            <p style="color:var(--plum-soft);font-size:14.5px;line-height:1.75;margin:0 0 22px;">Hyalüronik asit bazlı, doğal ve zarif sonuçlar veren dolgu çözümleri.</p>
            <span class="sf-link">İncele →</span>
          </a>
        </div>
      </div>
    </section>

    
EDV2P8S5;
        $p8s6 = <<<'EDV2P8S6'
<!-- 6. DOKTORLAR İÇİN / UYGULAMA -->

    <section style="padding:clamp(72px,9vw,100px) 0;background:var(--paper);">
      <div class="sf-wrap">
        <div style="max-width:660px;margin:0 0 48px;">
          <p class="sf-eyebrow" data-sf>Doktorlar &amp; Uygulayıcılar İçin</p>
          <h2 class="sf-serif" data-sf style="font-size:clamp(28px,4vw,42px);font-weight:500;color:var(--plum);margin:0 0 16px;line-height:1.15;">Profesyonel kullanım için zarif ve güvenilir bir seri</h2>
          <p data-sf class="d1" style="color:var(--plum-soft);font-size:17px;line-height:1.85;">Seffiline; klinik ve uygulayıcılar için sertifikalı, takip edilebilir ve düzenli tedarik edilen bir kozmesötik portföydür. Ürün, içerik ve uygulama detayları için ekibimiz yanınızda.</p>
        </div>
        <div class="sf-docgrid" data-sf>
          <div class="sf-doc">
            <span class="sf-ico" style="width:50px;height:50px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></span>
            <h4>Ürün &amp; İçerik Bilgisi</h4>
            <p>Her seri için içerik, kullanım alanı ve sunum formatı bilgileri.</p>
          </div>
          <div class="sf-doc">
            <span class="sf-ico" style="width:50px;height:50px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l8.8 8.6 8.8-8.6a5.5 5.5 0 0 0 0-7.8z"/></svg></span>
            <h4>Bütüncül Portföy</h4>
            <p>Cilt, saç, intim ve dolgu serileri tek bir tedarik ortağında.</p>
          </div>
          <div class="sf-doc">
            <span class="sf-ico" style="width:50px;height:50px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 14h2"/></svg></span>
            <h4>Düzenli Tedarik</h4>
            <p>Resmi distribütör güvencesiyle istikrarlı ve takip edilebilir tedarik.</p>
          </div>
        </div>
      </div>
    </section>

    
EDV2P8S6;
        $p8s7 = <<<'EDV2P8S7'
<!-- 7. CTA BANDI -->

    <section style="padding:0 0 clamp(72px,9vw,104px);background:var(--paper);">
      <div class="sf-wrap">
        <div data-sf style="position:relative;overflow:hidden;border-radius:36px;background:var(--grad-rose);padding:clamp(48px,7vw,84px);text-align:center;color:#fff;">
          <div class="sf-blob" aria-hidden="true" style="top:-50px;right:-40px;width:220px;height:220px;background:rgba(255,255,255,.14);"></div>
          <div class="sf-blob" aria-hidden="true" style="bottom:-70px;left:-40px;width:260px;height:260px;background:rgba(255,255,255,.10);"></div>
          <div style="position:relative;">
            <p style="letter-spacing:3px;text-transform:uppercase;font-size:12px;font-weight:700;opacity:.9;margin:0 0 18px;">İletişim</p>
            <h2 class="sf-serif" style="font-size:clamp(28px,4.4vw,46px);font-weight:500;margin:0 0 18px;line-height:1.14;">Seffiline ürünleri için bize ulaşın</h2>
            <p style="font-size:18px;opacity:.95;max-width:560px;margin:0 auto 36px;line-height:1.7;">Ürün, içerik ve uygulama bilgileri için ekibimiz hazır. WhatsApp'tan zarifçe yazın, hemen yanıtlayalım.</p>
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" class="sf-btn" style="background:#25D366;color:#fff;font-weight:700;box-shadow:0 14px 36px rgba(74,46,53,.18);">
              <svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
              WhatsApp ile Yaz</a>
          </div>
        </div>
      </div>
    </section>
EDV2P8S7;
        $p9s0 = <<<'EDV2P9S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"><style>
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
          <span class="ae-eyebrow ae-rev">Grand Aespio · İleri Formül &amp; Cihaz Teknolojisi</span>
          <h1 class="ae-rev ae-d1">Yeni Nesil<br><span class="ae-chrome-text">Estetik Teknolojisi</span></h1>
          <p class="ae-hero__sub ae-rev ae-d2">Grand Aespio, ip askı (thread lift) sistemleri ve ileri formül yüz maskelerini tek bir mühendislik diliyle birleştirir. Hassas tutuş, kontrollü etki, ölçülebilir kalite.</p>
          <div class="ae-hero__cta ae-rev ae-d3">
            <a class="ae-btn ae-btn--chrome" href="#urunler">Ürünleri İncele
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            <a class="ae-btn ae-btn--wa" href="https://wa.me/905426205100" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
              Bilgi Al — WhatsApp</a>
          </div>
          <div class="ae-hero__spec ae-rev ae-d3">
            <div><b>3</b>İp askı sistemi</div>
            <div><b>2</b>İleri formül maske</div>
            <div><b>TR</b>Estetik Dermal temsilciliği</div>
          </div>
        </div>

        <!-- 🖼️ GÖRSEL: /assets/img/aespio-hero.jpg (oran 4:5)
             PROMPT: "Sleek dark futuristic macro shot of a high-tech medical aesthetics thread-lift device / cannula on a brushed metallic anthracite surface, chrome and silver reflections, cool cyan glow accent, moody studio lighting, advanced technology product photography, premium and precise; photorealistic, high resolution; no text, no logo, no watermark"
             KULLANIM → .ae-device__img elementine background-image olarak ekle. -->
        <div class="ae-hero__visual ae-rev ae-d2">
          <div class="ae-device">
            <div class="ae-device__frame">
              <div class="ae-device__img" style="background-image:url('/assets/img/aespio-hero.jpg');"></div>
              <div class="ae-device__glow" aria-hidden="true"></div>
              <div class="ae-device__sheen" aria-hidden="true"></div>
              <div class="ae-device__badge">
                <span class="tag">LFL</span>
                <div><small>Öne çıkan sistem</small><b>LFL Anchor — Thread Lift</b></div>
              </div>
            </div>
            <div class="ae-device__chip" aria-hidden="true"><span></span><b>Precision Hold</b></div>
          </div>
        </div>
      </div>
    </section>

    
EDV2P9S0;
        $p9s1 = <<<'EDV2P9S1'
<!-- 2) MARKA HİKAYESİ -->

    <section class="ae-sec">
      <div class="ae-wrap">
        <div class="ae-story">
          <!-- 🖼️ GÖRSEL: /assets/img/aespio-tech.jpg (oran 5:6)
               PROMPT: "Dark futuristic close-up of advanced skincare formulation — a sheet mask serum and biotech ingredient texture on a glossy black tech surface, cool silver-cyan rim light, laboratory precision aesthetic, sleek and high-tech; photorealistic, high resolution; no text, no logo, no watermark"
               KULLANIM → .ae-story__img elementine background-image olarak ekle. -->
          <div class="ae-story__visual ae-rev">
            <div class="ae-story__img" style="background-image:url('/assets/img/aespio-tech.jpg');"></div>
            <div class="ae-device__glow" aria-hidden="true"></div>
            <div class="ae-device__sheen" aria-hidden="true"></div>
          </div>
          <div class="ae-rev ae-d1">
            <span class="ae-eyebrow">Marka Yaklaşımı</span>
            <h2>Teknolojiyi formüle, formülü sonuca çeviren mühendislik.</h2>
            <p style="color:var(--ae-body);font-size:clamp(16px,1.3vw,18px);line-height:1.72;">Grand Aespio, estetik uygulamalara cihaz hassasiyetiyle yaklaşır. İp askı tarafında çapalı (anchor) tutuş geometrisi ve doku uyumlu malzeme; maske tarafında beta-glukan ve hyalüronik asit gibi ileri aktiflerin kontrollü salımı. Her ürün, tekrarlanabilir ve ölçülebilir bir sonuç hedefiyle tasarlanır.</p>
            <p style="color:var(--ae-body);font-size:clamp(16px,1.3vw,18px);line-height:1.72;">Yaklaşım nettir: gösterişten çok performans, moda yerine mühendislik. Yeni nesil formüller, hekimin elinde öngörülebilir bir araç setine dönüşür.</p>
            <ul class="ae-story__list">
              <li><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2.4"/></svg></span><div><b>Hassas geometri</b>Çapalı tutuş ve doku uyumlu ip askı tasarımı.</div></li>
              <li><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3h6M10 3v5L5 19a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1L14 8V3"/></svg></span><div><b>İleri aktif formül</b>Beta-glukan ve hyalüronik asit ile kontrollü salım.</div></li>
              <li><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg></span><div><b>Ölçülebilir kalite</b>Tekrarlanabilir, öngörülebilir üretim standardı.</div></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    
EDV2P9S1;
        $p9s2 = <<<'EDV2P9S2'
<!-- 3) KREDİBİLİTE ŞERİDİ -->

    <section class="ae-cred">
      <div class="ae-wrap ae-cred__in">
        <div class="ae-cred__item ae-rev"><b>Yenilikçi</b><span>İleri formül &amp; cihaz odaklı yeni nesil yaklaşım</span></div>
        <div class="ae-cred__item ae-rev ae-d1"><b>Estetik Dermal</b><span>Grand Aespio'nun Türkiye temsilcisi</span></div>
        <div class="ae-cred__item ae-rev ae-d2"><b>5 ürün</b><span>İp askı serisi + ileri formül maske serisi</span></div>
        <div class="ae-cred__item ae-rev ae-d3"><b>Hekime özel</b><span>Profesyonel uygulama ve teknik destek</span></div>
      </div>
    </section>

    
EDV2P9S2;
        $p9s3 = <<<'EDV2P9S3'
<!-- 4) ÖNE ÇIKAN SPLIT: LFL Anchor + Beta-Glukan -->

    <section class="ae-sec">
      <div class="ae-wrap">
        <div class="ae-rev" style="max-width:640px;margin-bottom:46px;">
          <span class="ae-eyebrow">Öne Çıkan İki Güç</span>
          <h2 style="font-size:clamp(28px,3.8vw,46px);margin-top:18px;">Tek portföyde thread lift ve ileri formül maske</h2>
        </div>
        <div class="ae-split">

          <!-- LFL Anchor -->
          <article class="ae-feat ae-rev">
            <div class="ae-feat__media">
              <!-- 🖼️ GÖRSEL: /assets/img/aespio-lfl-anchor.jpg (oran 16:10)
                   PROMPT: "Dark high-tech macro of an anchor-design PDO lifting thread with cannula on brushed metallic surface, chrome reflections, cool cyan glow, futuristic medical aesthetics product shot, sleek and precise; photorealistic, high resolution; no text, no logo, no watermark"
                   KULLANIM → .ae-feat__img elementine background-image olarak ekle. -->
              <div class="ae-feat__img" style="background-image:url('/assets/img/aespio-lfl-anchor.jpg');"></div>
              <span class="ae-feat__tag">İp Askı · Thread Lift</span>
            </div>
            <div class="ae-feat__body">
              <h3>LFL Anchor</h3>
              <p>Çapa (anchor) tasarımıyla dokuda güçlü ve dengeli bir tutuş için geliştirilen ip askı sistemi. FeelSoft ve FMC ile birlikte, farklı endikasyonlara cevap veren çok yönlü bir thread lift araç seti sunar.</p>
              <a class="ae-link" href="/urun-detay">LFL Anchor'ı incele
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </div>
          </article>

          <!-- Beta-Glukan Mask -->
          <article class="ae-feat ae-rev ae-d1">
            <div class="ae-feat__media">
              <!-- 🖼️ GÖRSEL: /assets/img/aespio-beta-glukan.jpg (oran 16:10)
                   PROMPT: "Dark futuristic product shot of a soothing beta-glucan sheet face mask sachet on a glossy black surface, cool silver-cyan rim light, biotech skincare aesthetic, sleek high-tech composition; photorealistic, high resolution; no text, no logo, no watermark"
                   KULLANIM → .ae-feat__img elementine background-image olarak ekle. -->
              <div class="ae-feat__img" style="background-image:url('/assets/img/aespio-beta-glukan.jpg');"></div>
              <span class="ae-feat__tag">Yüz Maskesi · İleri Formül</span>
            </div>
            <div class="ae-feat__body">
              <h3>Beta-Glukan Mask</h3>
              <p>Beta-glukan aktifinin kontrollü salımıyla hassas cildi yatıştırmaya ve onarım sürecini desteklemeye yönelik ileri formül yüz maskesi. Thread protokollerini tamamlayan bakım adımı olarak da konumlanır.</p>
              <a class="ae-link" href="/urun-detay">Beta-Glukan Mask'ı incele
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </div>
          </article>

        </div>
      </div>
    </section>

    
EDV2P9S3;
        $p9s4 = <<<'EDV2P9S4'
<!-- 5) ÜRÜN YELPAZESİ GRID -->

    <section class="ae-sec" id="urunler" style="background:var(--ae-bg-2);border-block:1px solid var(--ae-line);">
      <div class="ae-wrap">
        <div class="ae-rev" style="max-width:640px;margin-bottom:48px;">
          <span class="ae-eyebrow">Ürün Yelpazesi</span>
          <h2 style="font-size:clamp(28px,3.8vw,46px);margin-top:18px;">İleri formül maskelerden ip askı sistemlerine</h2>
          <p style="color:var(--ae-body);font-size:17px;line-height:1.7;margin-top:14px;">İki güçlü hat tek bir mühendislik dilinde: ileri formül maske serisi ve çok yönlü ip askı serisi.</p>
        </div>

        <!-- 🖼️ GÖRSEL deseni (tekrarlayan ürün kartı): /assets/img/aespio-product-{slug}.jpg (oran 4:3)
             Örn: aespio-product-beta-glukan-mask.jpg, aespio-product-hyaluronic-acid-mask.jpg,
                  aespio-product-feelsoft.jpg, aespio-product-fmc.jpg, aespio-product-lfl-anchor.jpg
             PROMPT: "Dark sleek futuristic product showcase (sheet face mask sachet OR PDO thread-lift product), brushed metallic anthracite backdrop, chrome and cool-cyan glow accents, advanced medical aesthetics studio lighting, crisp precise composition; photorealistic, high resolution; no text, no logo, no watermark" -->
        <div class="ae-pgrid">

          <a class="ae-pcard ae-rev" href="/urun-detay">
            <div class="ae-pcard__media"><div class="ae-pcard__img" style="background-image:url('/assets/img/aespio-product-beta-glukan-mask.jpg');"></div></div>
            <div class="ae-pcard__body">
              <span class="ae-pcard__cat">Yatıştırıcı Maske</span>
              <h3>Beta-Glukan Mask</h3>
              <p>Beta-glukan ile hassas cildi yatıştıran, onarıcı ileri formül yüz maskesi.</p>
              <span class="ae-pcard__go">İncele <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ae-pcard ae-rev ae-d1" href="/urun-detay">
            <div class="ae-pcard__media"><div class="ae-pcard__img" style="background-image:url('/assets/img/aespio-product-hyaluronic-acid-mask.jpg');"></div></div>
            <div class="ae-pcard__body">
              <span class="ae-pcard__cat">Nemlendirici Maske</span>
              <h3>Hyaluronic Acid Mask</h3>
              <p>Hyalüronik asit ile yoğun nem desteği; dolgun ve ışıltılı bir cilt hissi.</p>
              <span class="ae-pcard__go">İncele <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ae-pcard ae-rev ae-d2" href="/urun-detay">
            <div class="ae-pcard__media"><div class="ae-pcard__img" style="background-image:url('/assets/img/aespio-product-feelsoft.jpg');"></div></div>
            <div class="ae-pcard__body">
              <span class="ae-pcard__cat">İp Askı</span>
              <h3>FeelSoft</h3>
              <p>Yumuşak doku desteği için tasarlanmış, konforlu ip askı çözümü.</p>
              <span class="ae-pcard__go">İncele <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ae-pcard ae-rev" href="/urun-detay">
            <div class="ae-pcard__media"><div class="ae-pcard__img" style="background-image:url('/assets/img/aespio-product-fmc.jpg');"></div></div>
            <div class="ae-pcard__body">
              <span class="ae-pcard__cat">İp Askı</span>
              <h3>FMC</h3>
              <p>Hassas uygulamalar için ince işçilikli, çok yönlü ip askı ürünü.</p>
              <span class="ae-pcard__go">İncele <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ae-pcard ae-rev ae-d1" href="/urun-detay">
            <div class="ae-pcard__media"><div class="ae-pcard__img" style="background-image:url('/assets/img/aespio-product-lfl-anchor.jpg');"></div></div>
            <div class="ae-pcard__body">
              <span class="ae-pcard__cat">Thread Lift</span>
              <h3>LFL Anchor</h3>
              <p>Güçlü tutuş için çapalı (anchor) tasarımlı ip askı / thread lift sistemi.</p>
              <span class="ae-pcard__go">İncele <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

        </div>
      </div>
    </section>

    
EDV2P9S4;
        $p9s5 = <<<'EDV2P9S5'
<!-- 6) DOKTORLAR İÇİN / UYGULAMA -->

    <section class="ae-sec">
      <div class="ae-wrap">
        <div class="ae-docs ae-rev">
          <div class="ae-docs__grid" aria-hidden="true"></div>
          <span class="ae-eyebrow">Hekimler İçin</span>
          <h2>Cihaz hassasiyetiyle, profesyonel uygulamaya hazır</h2>
          <p class="ae-docs__lead">Grand Aespio ürünleri yalnızca profesyonel kullanıma yöneliktir. Estetik Dermal, doğru ve uyumlu uygulama için ürün bilgisi, teknik içerik ve eğitim desteğiyle hekimin yanındadır.</p>
          <div class="ae-docs__cards">
            <div class="ae-docc">
              <div class="ae-docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div>
              <h4>Uygulama Eğitimi</h4>
              <p>İp askı ve maske protokolleri için ürün ve teknik kullanım bilgisi.</p>
            </div>
            <div class="ae-docc">
              <div class="ae-docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 3h6M10 3v5L5 19a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1L14 8V3"/></svg></div>
              <h4>İleri Formül Bilgisi</h4>
              <p>Beta-glukan ve hyalüronik asit aktiflerine dair teknik içerik.</p>
            </div>
            <div class="ae-docc">
              <div class="ae-docc__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 14h2"/></svg></div>
              <h4>Tedarik &amp; Destek</h4>
              <p>Estetik Dermal temsilciliğiyle güvenilir tedarik ve teknik destek.</p>
            </div>
          </div>
          <a class="ae-btn ae-btn--ghost" href="/iletisim">Hekimler için bilgi alın
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
      </div>
    </section>

    
EDV2P9S5;
        $p9s6 = <<<'EDV2P9S6'
<!-- 7) CTA BANDI (WhatsApp) -->

    <section class="ae-sec" style="padding-top:0;">
      <div class="ae-wrap">
        <div class="ae-cta ae-rev">
          <div class="ae-cta__grid" aria-hidden="true"></div>
          <span class="ae-eyebrow" style="justify-content:center;">Grand Aespio · Estetik Dermal</span>
          <h2>Yeni nesil sistemler hakkında<br>bize ulaşın</h2>
          <p>İp askı serisi ve ileri formül maskeler için ürün bilgisi, fiyat ve eğitim talepleriniz için ekibimiz hazır.</p>
          <a class="ae-btn ae-btn--wa" href="https://wa.me/905426205100" target="_blank" rel="noopener" style="font-size:16px;padding:17px 36px;">
            <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true" style="width:20px;height:20px;"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
            WhatsApp ile Yaz</a>
        </div>
      </div>
    </section>
EDV2P9S6;
        $p10s0 = <<<'EDV2P10S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"><style>
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
          <p class="wh-mono wh-eyebrow" style="margin:0 0 26px;"><span class="dot" aria-hidden="true"></span>Güney Kore · Medikal Mekatronik</p>
          <h1 class="wh-disp" style="font-size:clamp(38px,5.6vw,66px);line-height:1.04;font-weight:700;color:#F4F8FF;margin:0 0 22px;">Mühendislik hassasiyetinde<br><span style="background:linear-gradient(110deg,#2DA8FF 0%,#5BE6D4 100%);-webkit-background-clip:text;background-clip:text;color:transparent;">estetik teknolojisi</span></h1>
          <p class="wh-lead" style="max-width:560px;margin:0 0 34px;">Woorhi Mechatronics Co. Ltd., Kore mühendisliğiyle geliştirilen medikal estetik cihazları üretir. Hassas kontrol, klinik dayanıklılık ve tekrarlanabilir sonuçlar — kliniğinizin teknolojik altyapısı için tasarlandı.</p>
          <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a class="wh-btn wh-btn--primary" href="#raffine">Cihazı İncele →</a>
            <a class="wh-btn wh-btn--wa" href="https://wa.me/905426205100" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
              Bilgi Al — WhatsApp</a>
          </div>
          <div class="wh-mono reveal d2" style="display:flex;gap:26px;flex-wrap:wrap;margin-top:42px;padding-top:26px;border-top:1px solid var(--wh-line);font-size:13px;color:var(--wh-txt-3);">
            <div><div style="color:var(--wh-cyan);font-size:11px;letter-spacing:1px;">ORIGIN</div><div style="color:var(--wh-txt);font-weight:500;margin-top:3px;">Seoul · KR</div></div>
            <div aria-hidden="true" style="width:1px;background:var(--wh-line);"></div>
            <div><div style="color:var(--wh-cyan);font-size:11px;letter-spacing:1px;">CLASS</div><div style="color:var(--wh-txt);font-weight:500;margin-top:3px;">Klinik Cihaz</div></div>
            <div aria-hidden="true" style="width:1px;background:var(--wh-line);"></div>
            <div><div style="color:var(--wh-cyan);font-size:11px;letter-spacing:1px;">DIST · TR</div><div style="color:var(--wh-txt);font-weight:500;margin-top:3px;">Estetik Dermal</div></div>
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
              <span class="wh-mono" style="font-size:11px;letter-spacing:1.5px;color:var(--wh-cyan);">WOORHI · UNIT-01</span>
              <span class="wh-mono" style="display:inline-flex;align-items:center;gap:6px;font-size:11px;color:#9AD6FF;"><span class="wh-live" aria-hidden="true" style="width:7px;height:7px;border-radius:50%;background:var(--wh-cyan);box-shadow:0 0 10px var(--wh-cyan);"></span>ONLINE</span>
            </div>
            <div style="position:relative;aspect-ratio:4/3;border-radius:16px;overflow:hidden;border:1px solid var(--wh-line-2);background:radial-gradient(130% 130% at 50% 0%,rgba(45,168,255,.24),rgba(14,17,22,.92)),url('/assets/img/woorhi-device.jpg');background-size:cover;background-position:center;display:grid;place-items:center;">
              <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(90deg,rgba(45,168,255,.10) 1px,transparent 1px),linear-gradient(rgba(45,168,255,.10) 1px,transparent 1px);background-size:26px 26px;"></div>
              <div class="wh-scanline" aria-hidden="true"></div>
              <!-- cihaz silüeti (görsel yokken görünür wireframe) -->
              <svg viewBox="0 0 120 90" width="58%" aria-hidden="true" style="position:relative;opacity:.5;filter:drop-shadow(0 0 10px rgba(45,168,255,.6));"><g fill="none" stroke="#2DA8FF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="30" y="14" width="60" height="40" rx="6"/><rect x="40" y="24" width="40" height="20" rx="3" stroke="#5BE6D4"/><path d="M60 54v14M44 68h32"/><circle cx="60" cy="76" r="4" stroke="#5BE6D4"/><path d="M36 20h6M78 20h6"/></g></svg>
            </div>
            <div class="wh-mono" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px;font-size:12px;">
              <div style="background:rgba(255,255,255,.04);border:1px solid var(--wh-line);border-radius:10px;padding:10px 12px;"><div style="color:var(--wh-txt-3);">PRECISION</div><div style="color:var(--wh-txt);margin-top:2px;">± hassas kontrol</div></div>
              <div style="background:rgba(255,255,255,.04);border:1px solid var(--wh-line);border-radius:10px;padding:10px 12px;"><div style="color:var(--wh-txt-3);">BUILD</div><div style="color:var(--wh-txt);margin-top:2px;">KR Engineering</div></div>
            </div>
          </div>
          <!-- floating rozeti -->
          <div style="position:absolute;bottom:-16px;left:-14px;background:rgba(22,26,34,.94);border:1px solid var(--wh-line-2);border-radius:14px;box-shadow:0 0 26px rgba(45,168,255,.28);padding:12px 16px;display:flex;align-items:center;gap:11px;">
            <span aria-hidden="true" style="width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,#2DA8FF,#5BE6D4);box-shadow:0 0 14px rgba(45,168,255,.5);flex-shrink:0;"></span>
            <div><div class="wh-mono" style="font-size:10px;color:var(--wh-cyan);letter-spacing:1px;">RAFFINE</div><div style="font-weight:700;font-size:13px;color:var(--wh-txt);">Ana Cihaz Serisi</div></div>
          </div>
        </div>
      </div>
    </section>

    
EDV2P10S0;
        $p10s1 = <<<'EDV2P10S1'
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
            <div class="wh-mono" style="position:absolute;left:18px;bottom:16px;font-size:11px;letter-spacing:1.5px;color:var(--wh-cyan);background:rgba(14,17,22,.7);border:1px solid var(--wh-line-2);border-radius:8px;padding:6px 11px;">// PRECISION ARCHITECTURE</div>
          </div>
        </div>
        <div class="reveal d1">
          <p class="wh-mono wh-eyebrow" style="margin:0 0 18px;">Marka Hikayesi</p>
          <h2 class="wh-disp wh-h2" style="margin-bottom:18px;">Kore mühendisliği, ölçülebilir hassasiyet</h2>
          <p class="wh-lead" style="margin:0 0 18px;">Woorhi Mechatronics, mekatronik kontrol mimarisini medikal estetik dünyasına taşıyan bir mühendislik markasıdır. Her cihaz; kararlı güç yönetimi, hassas parametre kontrolü ve uygulama tekrarlanabilirliği ilkeleri üzerine kurulur.</p>
          <p class="wh-lead" style="margin:0 0 30px;">Amaç basit ama disiplinli: hekimin belirlediği parametreyi her seferinde aynı doğrulukla sahaya yansıtmak. Bu yüzden Woorhi cihazları yoğun klinik kullanım için dayanıklılık ve sezgisel kontrol etrafında tasarlanır.</p>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
            <div class="wh-glass" style="padding:18px 16px;">
              <div class="wh-disp" style="font-size:26px;font-weight:700;background:linear-gradient(120deg,#2DA8FF,#5BE6D4);-webkit-background-clip:text;background-clip:text;color:transparent;">±</div>
              <div class="wh-mono" style="font-size:11px;color:var(--wh-txt-3);margin-top:6px;">Hassas kontrol</div>
            </div>
            <div class="wh-glass" style="padding:18px 16px;">
              <div class="wh-disp" style="font-size:26px;font-weight:700;background:linear-gradient(120deg,#2DA8FF,#5BE6D4);-webkit-background-clip:text;background-clip:text;color:transparent;">KR</div>
              <div class="wh-mono" style="font-size:11px;color:var(--wh-txt-3);margin-top:6px;">Made in Korea</div>
            </div>
            <div class="wh-glass" style="padding:18px 16px;">
              <div class="wh-disp" style="font-size:26px;font-weight:700;background:linear-gradient(120deg,#2DA8FF,#5BE6D4);-webkit-background-clip:text;background-clip:text;color:transparent;">∞</div>
              <div class="wh-mono" style="font-size:11px;color:var(--wh-txt-3);margin-top:6px;">Tekrarlanabilirlik</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDV2P10S1;
        $p10s2 = <<<'EDV2P10S2'
<!-- 3 · KREDİBİLİTE ŞERİDİ -->

    <section style="background:var(--wh-ink-2);border-top:1px solid var(--wh-line);border-bottom:1px solid var(--wh-line);">
      <div class="wrap" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;padding:40px 0;">
        <!-- Güney Kore -->
        <div class="reveal wh-glass" style="display:flex;align-items:center;gap:14px;padding:18px 20px;">
          <span aria-hidden="true" style="width:40px;height:40px;border-radius:11px;flex-shrink:0;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);box-shadow:0 0 12px rgba(45,168,255,.35);"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#9AD6FF" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">Güney Kore</div><div class="wh-mono" style="font-size:11px;color:var(--wh-txt-3);margin-top:2px;">made in korea</div></div>
        </div>
        <!-- CE uyumlu -->
        <div class="reveal d1 wh-glass" style="display:flex;align-items:center;gap:14px;padding:18px 20px;">
          <span aria-hidden="true" style="width:40px;height:40px;border-radius:11px;flex-shrink:0;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(91,230,212,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);box-shadow:0 0 12px rgba(91,230,212,.35);"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#7CF0E1" stroke-width="1.7"><path d="M12 2 4 5v6c0 5 3.4 8.5 8 11 4.6-2.5 8-6 8-11V5z"/><path d="m9 12 2 2 4-4"/></svg></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">CE Uyumlu</div><div class="wh-mono" style="font-size:11px;color:var(--wh-txt-3);margin-top:2px;">ce compliant</div></div>
        </div>
        <!-- Klinik cihaz -->
        <div class="reveal d2 wh-glass" style="display:flex;align-items:center;gap:14px;padding:18px 20px;">
          <span aria-hidden="true" style="width:40px;height:40px;border-radius:11px;flex-shrink:0;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);box-shadow:0 0 12px rgba(45,168,255,.35);"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#9AD6FF" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M12 9v6M9 12h6"/></svg></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">Klinik Cihaz</div><div class="wh-mono" style="font-size:11px;color:var(--wh-txt-3);margin-top:2px;">clinical grade</div></div>
        </div>
        <!-- Estetik Dermal Türkiye -->
        <div class="reveal d3 wh-glass" style="display:flex;align-items:center;gap:14px;padding:18px 20px;">
          <span aria-hidden="true" style="width:40px;height:40px;border-radius:11px;flex-shrink:0;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(91,230,212,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);box-shadow:0 0 12px rgba(91,230,212,.35);"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#7CF0E1" stroke-width="1.7"><path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.4"/></svg></span>
          <div><div style="font-weight:700;color:#F4F8FF;font-size:15px;">Estetik Dermal · TR</div><div class="wh-mono" style="font-size:11px;color:var(--wh-txt-3);margin-top:2px;">resmi distribütör</div></div>
        </div>
      </div>
    </section>

    
EDV2P10S2;
        $p10s3 = <<<'EDV2P10S3'
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
            <div class="wh-mono" style="position:absolute;left:16px;top:14px;font-size:11px;letter-spacing:2px;color:var(--wh-cyan);background:rgba(14,17,22,.7);border:1px solid var(--wh-line-2);border-radius:8px;padding:6px 11px;">RAFFINE · DEVICE</div>
          </div>
        </div>
        <!-- içerik + spec -->
        <div class="reveal d1">
          <p class="wh-mono wh-tag" style="display:inline-block;margin:0 0 14px;">// Öne Çıkan Cihaz</p>
          <h2 class="wh-disp wh-h2" style="margin-bottom:16px;">Raffine</h2>
          <p class="wh-lead" style="max-width:540px;margin:0 0 28px;">Woorhi'nin amiral gemisi medikal estetik cihazı. Mekatronik kontrol mimarisi, kararlı güç yönetimi ve uygulama tekrarlanabilirliği üzerine kurulu; klinik kullanım için dayanıklı bir gövde ve sezgisel arayüzle tasarlandı.</p>

          <div class="wh-mono wh-spec" style="max-width:540px;margin:0 0 30px;">
            <div class="wh-spec__row"><span class="wh-spec__k">[ TİP ]</span><span class="wh-spec__v">Medikal estetik mekatronik cihaz</span></div>
            <div class="wh-spec__row"><span class="wh-spec__k">[ UYGULAMA ]</span><span class="wh-spec__v">Yüz &amp; vücut profesyonel bakım</span></div>
            <div class="wh-spec__row"><span class="wh-spec__k">[ KONTROL ]</span><span class="wh-spec__v">Hassas dijital parametre yönetimi</span></div>
            <div class="wh-spec__row"><span class="wh-spec__k">[ GÖVDE ]</span><span class="wh-spec__v">Klinik dayanımlı, sezgisel arayüz</span></div>
            <div class="wh-spec__row"><span class="wh-spec__k">[ MENŞE ]</span><span class="wh-spec__v">Güney Kore mühendisliği</span></div>
            <div class="wh-spec__row"><span class="wh-spec__k">[ KULLANIM ]</span><span class="wh-spec__v" style="color:var(--wh-cyan);">Klinik / profesyonel</span></div>
          </div>

          <a class="wh-btn wh-btn--primary" href="/urun-detay">Raffine Detayları →</a>
        </div>
      </div>
    </section>

    
EDV2P10S3;
        $p10s4 = <<<'EDV2P10S4'
<!-- 5 · ÜRÜN / CİHAZ GRID (Raffine + ilgili sarf) -->

    <section class="wh-sec" style="background:var(--wh-ink-2);border-top:1px solid var(--wh-line);">
      <div class="wrap">
        <div class="reveal" style="max-width:640px;margin-bottom:46px;">
          <p class="wh-mono wh-tag" style="margin:0 0 12px;">// Cihaz &amp; Sarf</p>
          <h2 class="wh-disp wh-h2">Raffine ve ilgili profesyonel sarf</h2>
          <p class="wh-lead" style="margin-top:14px;">Cihaz, aplikatörleri ve sarf bileşenleriyle eksiksiz bir klinik kurulum. Tüm hat, Estetik Dermal güvencesiyle Türkiye'de.</p>
        </div>
        <!-- 🖼️ GÖRSEL deseni: /assets/img/woorhi-{slug}.jpg (oran 4:3) — koyu zemin, elektrik mavi-cyan ışık vurgulu teknik packshot -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;">

          <!-- Raffine ana cihaz -->
          <a class="wh-card wh-glass" href="/urun-detay" style="display:block;overflow:hidden;text-decoration:none;">
            <div style="position:relative;aspect-ratio:4/3;background:linear-gradient(160deg,#222730,#0E1116),url('/assets/img/woorhi-raffine.jpg');background-size:cover;background-position:center;border-bottom:1px solid var(--wh-line);">
              <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(90deg,rgba(45,168,255,.07) 1px,transparent 1px),linear-gradient(rgba(45,168,255,.07) 1px,transparent 1px);background-size:24px 24px;"></div>
              <span class="wh-mono" style="position:absolute;top:12px;left:12px;font-size:10px;letter-spacing:1.5px;color:var(--wh-ink);background:linear-gradient(135deg,#2DA8FF,#5BE6D4);border-radius:7px;padding:5px 9px;font-weight:600;">ANA CİHAZ</span>
            </div>
            <div style="padding:22px 24px;">
              <div class="wh-mono" style="font-size:11px;color:var(--wh-cyan);letter-spacing:1px;">RAFFINE</div>
              <h3 class="wh-disp" style="font-size:21px;font-weight:600;color:#F4F8FF;margin:6px 0 8px;">Raffine Cihazı</h3>
              <p style="color:var(--wh-txt-2);font-size:14.5px;line-height:1.65;margin:0;">Amiral gemisi mekatronik medikal estetik cihazı; hassas kontrol ve klinik dayanıklılık.</p>
            </div>
          </a>

          <!-- Aplikatör başlıkları -->
          <a class="wh-card wh-glass" href="/urun-detay" style="display:block;overflow:hidden;text-decoration:none;">
            <div style="position:relative;aspect-ratio:4/3;background:linear-gradient(160deg,#222730,#0E1116),url('/assets/img/woorhi-handpiece.jpg');background-size:cover;background-position:center;border-bottom:1px solid var(--wh-line);">
              <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(90deg,rgba(45,168,255,.07) 1px,transparent 1px),linear-gradient(rgba(45,168,255,.07) 1px,transparent 1px);background-size:24px 24px;"></div>
              <span class="wh-mono" style="position:absolute;top:12px;left:12px;font-size:10px;letter-spacing:1.5px;color:#9AD6FF;background:rgba(14,17,22,.7);border:1px solid var(--wh-line-2);border-radius:7px;padding:5px 9px;">APLİKATÖR</span>
            </div>
            <div style="padding:22px 24px;">
              <div class="wh-mono" style="font-size:11px;color:var(--wh-cyan);letter-spacing:1px;">HANDPIECE</div>
              <h3 class="wh-disp" style="font-size:21px;font-weight:600;color:#F4F8FF;margin:6px 0 8px;">Aplikatör Başlıkları</h3>
              <p style="color:var(--wh-txt-2);font-size:14.5px;line-height:1.65;margin:0;">Farklı endikasyonlara yönelik, değiştirilebilir hassas uygulama başlıkları.</p>
            </div>
          </a>

          <!-- Sarf & tüketim -->
          <a class="wh-card wh-glass" href="/urun-detay" style="display:block;overflow:hidden;text-decoration:none;">
            <div style="position:relative;aspect-ratio:4/3;background:linear-gradient(160deg,#222730,#0E1116),url('/assets/img/woorhi-consumable.jpg');background-size:cover;background-position:center;border-bottom:1px solid var(--wh-line);">
              <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(90deg,rgba(45,168,255,.07) 1px,transparent 1px),linear-gradient(rgba(45,168,255,.07) 1px,transparent 1px);background-size:24px 24px;"></div>
              <span class="wh-mono" style="position:absolute;top:12px;left:12px;font-size:10px;letter-spacing:1.5px;color:#9AD6FF;background:rgba(14,17,22,.7);border:1px solid var(--wh-line-2);border-radius:7px;padding:5px 9px;">SARF</span>
            </div>
            <div style="padding:22px 24px;">
              <div class="wh-mono" style="font-size:11px;color:var(--wh-cyan);letter-spacing:1px;">CONSUMABLE</div>
              <h3 class="wh-disp" style="font-size:21px;font-weight:600;color:#F4F8FF;margin:6px 0 8px;">İlgili Sarf Bileşenleri</h3>
              <p style="color:var(--wh-txt-2);font-size:14.5px;line-height:1.65;margin:0;">Cihazın kesintisiz çalışması için orijinal tüketim ve sarf malzemeleri.</p>
            </div>
          </a>

        </div>
        <div class="reveal" style="margin-top:36px;">
          <a class="wh-mono" href="/urunler" style="display:inline-flex;align-items:center;gap:8px;color:var(--wh-cyan);font-weight:500;font-size:14px;">Tüm Woorhi ürünlerini gör <span aria-hidden="true">→</span></a>
        </div>
      </div>
    </section>

    
EDV2P10S4;
        $p10s5 = <<<'EDV2P10S5'
<!-- 6 · DOKTORLAR İÇİN / UYGULAMA — klinik cihaz kullanımı, demo/eğitim -->

    <section class="wh-sec" style="background:var(--wh-ink);">
      <div class="wrap">
        <div style="text-align:center;max-width:660px;margin:0 auto 52px;">
          <p class="wh-mono wh-tag reveal" style="margin:0 0 12px;">// Doktorlar İçin</p>
          <h2 class="wh-disp wh-h2 reveal d1">Mühendisliğin estetikle buluştuğu nokta</h2>
          <p class="wh-lead reveal d2" style="margin-top:14px;">Woorhi cihazlarının doğru ve güvenli kullanımı için kurulum, uygulamalı eğitim ve demo planlamasıyla yanınızdayız.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:22px;">
          <!-- İleri teknoloji -->
          <div class="reveal wh-glass" style="padding:34px 30px;box-shadow:0 14px 40px rgba(0,0,0,.35);">
            <div aria-hidden="true" style="width:54px;height:54px;border-radius:15px;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);margin-bottom:20px;box-shadow:0 0 22px rgba(45,168,255,.3);"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#BFE4FF" stroke-width="1.6"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M9 9h6v6H9z"/><path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2"/></svg></div>
            <h3 class="wh-disp" style="font-size:20px;font-weight:600;color:#F4F8FF;margin:0 0 10px;">İleri Teknoloji</h3>
            <p style="color:var(--wh-txt-2);line-height:1.7;margin:0;font-size:15px;">Mekatronik kontrol mimarisi ve hassas parametre yönetimiyle tekrarlanabilir, kontrollü uygulamalar.</p>
          </div>
          <!-- Klinik dayanıklılık -->
          <div class="reveal d1 wh-glass" style="padding:34px 30px;box-shadow:0 14px 40px rgba(0,0,0,.35);">
            <div aria-hidden="true" style="width:54px;height:54px;border-radius:15px;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(91,230,212,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);margin-bottom:20px;box-shadow:0 0 22px rgba(91,230,212,.3);"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#9CF0E2" stroke-width="1.6"><path d="M12 2 4 5v6c0 5 3.4 8.5 8 11 4.6-2.5 8-6 8-11V5z"/></svg></div>
            <h3 class="wh-disp" style="font-size:20px;font-weight:600;color:#F4F8FF;margin:0 0 10px;">Klinik Dayanıklılık</h3>
            <p style="color:var(--wh-txt-2);line-height:1.7;margin:0;font-size:15px;">Yoğun klinik kullanım için tasarlanmış sağlam gövde, kararlı güç yönetimi ve uzun ömürlü bileşenler.</p>
          </div>
          <!-- Yerel destek & eğitim -->
          <div class="reveal d2 wh-glass" style="padding:34px 30px;box-shadow:0 14px 40px rgba(0,0,0,.35);">
            <div aria-hidden="true" style="width:54px;height:54px;border-radius:15px;display:grid;place-items:center;background:radial-gradient(120% 120% at 30% 20%,rgba(45,168,255,.5),rgba(14,17,22,.4));border:1px solid var(--wh-line-2);margin-bottom:20px;box-shadow:0 0 22px rgba(45,168,255,.3);"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#BFE4FF" stroke-width="1.6"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div>
            <h3 class="wh-disp" style="font-size:20px;font-weight:600;color:#F4F8FF;margin:0 0 10px;">Eğitim &amp; Yerel Destek</h3>
            <p style="color:var(--wh-txt-2);line-height:1.7;margin:0;font-size:15px;">Estetik Dermal güvencesiyle Türkiye'de kurulum, uygulamalı eğitim, demo ve kesintisiz teknik servis.</p>
          </div>
        </div>
      </div>
    </section>

    
EDV2P10S5;
        $p10s6 = <<<'EDV2P10S6'
<!-- 7 · CTA BANDI — WhatsApp / demo talebi -->

    <section style="padding:0 0 clamp(70px,9vw,108px);background:var(--wh-ink);">
      <div class="wrap">
        <div class="reveal" style="position:relative;overflow:hidden;border-radius:28px;background:linear-gradient(120deg,#0E2A44 0%,#0E1C2C 46%,#0B2A2A 100%);border:1px solid var(--wh-line-2);padding:clamp(40px,6vw,72px);text-align:center;box-shadow:0 0 60px rgba(45,168,255,.18);">
          <div aria-hidden="true" style="position:absolute;inset:0;background-image:radial-gradient(600px 300px at 14% 0%, rgba(45,168,255,.4), transparent 60%),radial-gradient(600px 300px at 90% 100%, rgba(91,230,212,.3), transparent 60%);"></div>
          <div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:42px 42px;-webkit-mask-image:radial-gradient(700px 320px at 50% 50%,#000,transparent 75%);mask-image:radial-gradient(700px 320px at 50% 50%,#000,transparent 75%);"></div>
          <div style="position:relative;">
            <p class="wh-mono wh-tag" style="color:#9AD6FF;margin:0 0 14px;">// Demo &amp; Teklif</p>
            <h2 class="wh-disp wh-h2" style="margin:0 0 14px;">Woorhi cihazları için demo / teklif alın</h2>
            <p class="wh-lead" style="color:#C7D3E2;max-width:600px;margin:0 auto 32px;">Raffine ve Woorhi cihaz serisi hakkında detaylı bilgi, demo planlaması ve fiyat teklifi için Estetik Dermal ekibine ulaşın.</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
              <a class="wh-btn wh-btn--wa" href="https://wa.me/905426205100" target="_blank" rel="noopener" style="padding:15px 32px;">
                <svg viewBox="0 0 32 32" fill="#fff" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
                WhatsApp ile Yaz</a>
              <a class="wh-btn wh-btn--ghost" href="/iletisim" style="padding:15px 32px;">Bilgi Al</a>
            </div>
          </div>
        </div>
      </div>
    </section>
EDV2P10S6;
        $p11s0 = <<<'EDV2P11S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400..600;1,6..72,400..500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"><style>
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
          <span class="mi-pill" data-mi><span class="dot"></span>Medikal İnovasyon · Türkiye temsilcisi Estetik Dermal</span>
          <h1 class="mi-serif d1" data-mi style="font-size:clamp(42px,6vw,76px);line-height:1.04;font-weight:500;color:var(--teal-ink);margin:24px 0 24px;">Premium<br>Enjeksiyon <em style="font-style:italic;color:var(--teal);">Sistemleri</em></h1>
          <p class="d1" data-mi style="font-size:clamp(16px,2vw,19px);color:var(--slate);line-height:1.85;max-width:540px;margin:0 0 38px;">Hassasiyet ve inovasyonun buluşması. MI-Medical Innovation, doktorlara güvenilir dozaj ve konfor sunan klinik-sınıf enjeksiyon ve mezoterapi teknolojisi geliştirir.</p>
          <div class="d2" data-mi style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;">
            <a href="#spotlight" class="mi-btn mi-btn-fill">Pistor Eliance'ı İncele</a>
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" class="mi-btn mi-btn-wa">
              <svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
              Bilgi Al — WhatsApp</a>
          </div>
          <div class="mi-trust d3" data-mi>
            <div><b>Pistor Eliance</b>Enjeksiyon sistemi</div>
            <div><b>Hassas dozaj</b>Kontrollü uygulama</div>
            <div><b>Türkiye</b>Estetik Dermal temsilciliği</div>
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
              <div><div style="font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--teal);font-weight:700;">Öne çıkan sistem</div><div class="mi-serif" style="font-size:17px;color:var(--teal-ink);">Pistor Eliance</div></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDV2P11S0;
        $p11s1 = <<<'EDV2P11S1'
<!-- 2. MARKA HİKAYESİ -->

    <section style="padding:clamp(72px,9vw,104px) 0;background:var(--paper);">
      <div class="mi-wrap" style="max-width:840px;text-align:center;">
        <p class="mi-eyebrow mi-eyebrow--c" data-mi>Marka Hikayesi</p>
        <p class="mi-serif" data-mi style="font-size:clamp(24px,3.4vw,36px);line-height:1.5;font-weight:400;color:var(--teal-ink);margin:0 0 14px;">MI-Medical Innovation, enjeksiyon teknolojisini hassasiyetin diliyle yeniden tanımlar — her uygulamada kontrol, güven ve konfor.</p>
        <p class="d1" data-mi style="color:var(--slate);font-size:17px;line-height:1.85;max-width:660px;margin:24px auto 36px;">Medikal inovasyona odaklanan MI-Medical Innovation, doktorların ihtiyaç duyduğu hassas dozaj ve tutarlı uygulama deneyimini, klinik güvenle birleştirir. Estetik Dermal; markanın Türkiye temsilcisi olarak, premium enjeksiyon sistemlerini doğru kaynaktan, eğitim ve teknik destekle birlikte sunar.</p>
        <div class="d1" data-mi style="display:flex;align-items:center;justify-content:center;gap:14px;margin:0 auto;max-width:240px;">
          <span style="flex:1;height:1px;background:linear-gradient(90deg,transparent,var(--line));"></span>
          <span style="color:var(--teal);font-size:16px;">✛</span>
          <span style="flex:1;height:1px;background:linear-gradient(90deg,var(--line),transparent);"></span>
        </div>
      </div>
    </section>

    
EDV2P11S1;
        $p11s2 = <<<'EDV2P11S2'
<!-- 3. KREDİBİLİTE ŞERİDİ -->

    <section style="padding:0 0 clamp(56px,7vw,88px);background:var(--paper);">
      <div class="mi-wrap">
        <div class="mi-cred" data-mi>
          <div>
            <span class="mi-ico" style="width:46px;height:46px;border-radius:12px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2 4 6v6c0 5 3.4 8.6 8 10 4.6-1.4 8-5 8-10V6z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <h4>Premium Sınıf</h4>
            <p>Klinik standartlarda tasarlanmış, premium kalite enjeksiyon sistemleri.</p>
          </div>
          <div>
            <span class="mi-ico" style="width:46px;height:46px;border-radius:12px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <h4>Klinik Güven</h4>
            <p>Hassas dozaj ve tutarlı uygulama için güven veren mühendislik.</p>
          </div>
          <div>
            <span class="mi-ico" style="width:46px;height:46px;border-radius:12px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg></span>
            <h4>Türkiye Distribütörü</h4>
            <p>Estetik Dermal güvencesiyle, doğru kaynaktan resmi tedarik.</p>
          </div>
          <div>
            <span class="mi-ico" style="width:46px;height:46px;border-radius:12px;margin-bottom:16px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m18 2 4 4M17 7 7 17l-4 1 1-4L14 4z"/><path d="m13 5 6 6"/></svg></span>
            <h4>İnovasyon Odaklı</h4>
            <p>Enjeksiyon teknolojisinde hassasiyeti ileri taşıyan yenilikçi yaklaşım.</p>
          </div>
        </div>
      </div>
    </section>

    
EDV2P11S2;
        $p11s3 = <<<'EDV2P11S3'
<!-- 4. ÖNE ÇIKAN: PISTOR ELIANCE SPOTLIGHT -->

    <section id="spotlight" style="padding:clamp(72px,9vw,104px) 0;background:var(--mist-soft);position:relative;overflow:hidden;">
      <div class="mi-glow" aria-hidden="true" style="top:-120px;right:6%;width:320px;height:320px;background:radial-gradient(circle,rgba(14,140,140,.14),transparent 70%);"></div>
      <div class="mi-wrap mi-split" style="position:relative;">
        <!-- 🖼️ GÖRSEL: /assets/img/mi-medical-pistor-eliance.jpg (oran 4:5)
             PROMPT: "Premium medical injection system Pistor Eliance, clean clinical product shot, teal and white sterile aesthetic, precise mesotherapy injector device on minimal surgical surface, soft diffused professional lighting, medical innovation editorial; photorealistic, high resolution; no text, no logo, no watermark" -->
        <div class="mi-col" data-mi style="position:relative;min-height:440px;">
          <div style="position:relative;background:#fff;border:1px solid var(--line);border-radius:26px;box-shadow:var(--shadow-clean);padding:22px;max-width:420px;margin:0 auto;">
            <div class="mi-photo" style="aspect-ratio:4/5;border-radius:16px;background-color:#D6E8E7;background-image:linear-gradient(160deg,rgba(230,242,241,.4),rgba(191,227,225,.4)),url('/assets/img/mi-medical-pistor-eliance.jpg');"></div>
            <div style="position:absolute;top:14px;right:-12px;background:var(--grad-teal);color:#fff;border-radius:12px;box-shadow:var(--shadow-soft);padding:10px 14px;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;">Premium Sistem</div>
          </div>
        </div>
        <div class="mi-col d1" data-mi>
          <p class="mi-eyebrow">Öne Çıkan · Enjeksiyon Sistemi</p>
          <h2 class="mi-serif" style="font-size:clamp(30px,4.4vw,48px);font-weight:500;color:var(--teal-ink);margin:0 0 22px;line-height:1.1;">Pistor Eliance<br><em style="font-style:italic;color:var(--teal);">hassas mezoterapi sistemi</em></h2>
          <p style="color:var(--slate);font-size:17px;line-height:1.85;margin:0 0 30px;max-width:520px;">Pistor Eliance; mezoterapi ve enjeksiyon uygulamalarında hassas, kontrollü ve tekrarlanabilir dozaj sunmak için tasarlanmış premium bir sistemdir. Doktora konfor, hastaya nezaket sağlayan ince mühendislik anlayışıyla, klinik güveni standart hale getirir.</p>
          <ul class="mi-feat-list">
            <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l4 4L19 6"/></svg></span> Hassas ve tekrarlanabilir dozaj kontrolü</li>
            <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l4 4L19 6"/></svg></span> Ergonomik tasarımla uygulayıcı konforu</li>
            <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l4 4L19 6"/></svg></span> Mezoterapi ve enjeksiyon protokollerine uyum</li>
            <li><span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l4 4L19 6"/></svg></span> Premium klinik kalite ve güvenilir yapı</li>
          </ul>
          <a href="/urun-detay" class="mi-btn mi-btn-fill">Pistor Eliance'ı İncele →</a>
        </div>
      </div>
    </section>

    
EDV2P11S3;
        $p11s4 = <<<'EDV2P11S4'
<!-- 5. ÜRÜN GRID -->

    <section id="urunler" style="padding:clamp(72px,9vw,104px) 0;background:var(--paper);">
      <div class="mi-wrap">
        <div style="text-align:center;max-width:640px;margin:0 auto 56px;">
          <p class="mi-eyebrow mi-eyebrow--c" data-mi>Sistem &amp; Bileşenler</p>
          <h2 class="mi-serif d1" data-mi style="font-size:clamp(30px,4.4vw,46px);font-weight:500;color:var(--teal-ink);margin:0 0 16px;line-height:1.12;">Pistor Eliance ekosistemi</h2>
          <p class="d1" data-mi style="color:var(--slate);font-size:17px;line-height:1.8;">Hassas enjeksiyon için tasarlanmış ana sistem ve onu tamamlayan klinik bileşenler.</p>
        </div>

        <!-- 🖼️ GÖRSEL (kart deseni): /assets/img/mi-medical-product-{slug}.jpg (oran 4:3)
             slug'lar: pistor-eliance, enjektor-uclari, dozaj-modulu, baglanti-seti
             PROMPT: "Clean clinical medical device component packshot, teal and white sterile aesthetic, soft diffused professional lighting, minimal surgical surface, premium medical editorial; photorealistic, high resolution; no text, no logo, no watermark" -->
        <div class="mi-grid" data-mi>
          <!-- Pistor Eliance -->
          <a href="/urun-detay" class="mi-prodcard mi-card">
            <div class="mi-prodcard__img" style="background-image:url('/assets/img/mi-medical-product-pistor-eliance.jpg');"></div>
            <h3 class="mi-serif" style="font-size:23px;font-weight:600;color:var(--teal-ink);margin:0 0 4px;">Pistor Eliance</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--teal);font-weight:700;margin:0 0 12px;">Ana Enjeksiyon Sistemi</p>
            <p style="color:var(--slate);font-size:14.5px;line-height:1.75;margin:0 0 22px;flex:1;">Premium mezoterapi ve enjeksiyon sisteminin merkezi; hassas dozaj için tasarlandı.</p>
            <span class="mi-link">İncele →</span>
          </a>
          <!-- Enjektör Uçları -->
          <a href="/urun-detay" class="mi-prodcard mi-card">
            <div class="mi-prodcard__img" style="background-image:url('/assets/img/mi-medical-product-enjektor-uclari.jpg');"></div>
            <h3 class="mi-serif" style="font-size:23px;font-weight:600;color:var(--teal-ink);margin:0 0 4px;">Enjektör Uçları</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--teal);font-weight:700;margin:0 0 12px;">Sistem Bileşeni</p>
            <p style="color:var(--slate);font-size:14.5px;line-height:1.75;margin:0 0 22px;flex:1;">Farklı protokollere uyumlu, hassas uygulama için tasarlanmış uç çözümleri.</p>
            <span class="mi-link">İncele →</span>
          </a>
          <!-- Dozaj Modülü -->
          <a href="/urun-detay" class="mi-prodcard mi-card">
            <div class="mi-prodcard__img" style="background-image:url('/assets/img/mi-medical-product-dozaj-modulu.jpg');"></div>
            <h3 class="mi-serif" style="font-size:23px;font-weight:600;color:var(--teal-ink);margin:0 0 4px;">Dozaj Modülü</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--teal);font-weight:700;margin:0 0 12px;">Sistem Bileşeni</p>
            <p style="color:var(--slate);font-size:14.5px;line-height:1.75;margin:0 0 22px;flex:1;">Kontrollü ve tekrarlanabilir dozaj için Pistor Eliance ile uyumlu modül.</p>
            <span class="mi-link">İncele →</span>
          </a>
          <!-- Bağlantı Seti -->
          <a href="/urun-detay" class="mi-prodcard mi-card">
            <div class="mi-prodcard__img" style="background-image:url('/assets/img/mi-medical-product-baglanti-seti.jpg');"></div>
            <h3 class="mi-serif" style="font-size:23px;font-weight:600;color:var(--teal-ink);margin:0 0 4px;">Bağlantı Seti</h3>
            <p style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--teal);font-weight:700;margin:0 0 12px;">Sistem Bileşeni</p>
            <p style="color:var(--slate);font-size:14.5px;line-height:1.75;margin:0 0 22px;flex:1;">Steril ve güvenli akış için tasarlanmış, sisteme entegre bağlantı bileşenleri.</p>
            <span class="mi-link">İncele →</span>
          </a>
        </div>
      </div>
    </section>

    
EDV2P11S4;
        $p11s5 = <<<'EDV2P11S5'
<!-- 6. DOKTORLAR İÇİN / UYGULAMA -->

    <section style="padding:clamp(72px,9vw,104px) 0;background:var(--mist-soft);">
      <div class="mi-wrap">
        <div style="max-width:680px;margin:0 0 48px;">
          <p class="mi-eyebrow" data-mi>Doktorlar &amp; Uygulayıcılar İçin</p>
          <h2 class="mi-serif d1" data-mi style="font-size:clamp(28px,4vw,42px);font-weight:500;color:var(--teal-ink);margin:0 0 16px;line-height:1.15;">Hassas dozaj, uygulayıcı konforu ve eğitim</h2>
          <p class="d1" data-mi style="color:var(--slate);font-size:17px;line-height:1.85;">MI-Medical Innovation sistemleri, klinik ve uygulayıcılar için kontrollü dozaj ve ergonomik kullanım sunar. Ürün, içerik ve uygulama detayları için ekibimiz yanınızda.</p>
        </div>
        <div class="mi-docgrid" data-mi>
          <div class="mi-doc">
            <span class="mi-ico" style="width:50px;height:50px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 12h4l3 8 4-16 3 8h4"/></svg></span>
            <h4>Hassas Dozaj</h4>
            <p>Kontrollü ve tekrarlanabilir uygulama için tasarlanmış dozaj yapısı.</p>
          </div>
          <div class="mi-doc">
            <span class="mi-ico" style="width:50px;height:50px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2a4 4 0 0 0-4 4v6a4 4 0 0 0 8 0V6a4 4 0 0 0-4-4z"/><path d="M5 11v1a7 7 0 0 0 14 0v-1M12 19v3"/></svg></span>
            <h4>Uygulayıcı Konforu</h4>
            <p>Ergonomik tasarımla uzun uygulamalarda dahi konforlu kullanım.</p>
          </div>
          <div class="mi-doc">
            <span class="mi-ico" style="width:50px;height:50px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></span>
            <h4>Eğitim &amp; Destek</h4>
            <p>Sistemlerin doğru kullanımı için uygulamalı eğitim ve teknik destek.</p>
          </div>
        </div>
      </div>
    </section>

    
EDV2P11S5;
        $p11s6 = <<<'EDV2P11S6'
<!-- 7. CTA BANDI -->

    <section style="padding:clamp(56px,8vw,96px) 0 clamp(72px,9vw,104px);background:var(--paper);">
      <div class="mi-wrap">
        <div data-mi style="position:relative;overflow:hidden;border-radius:32px;background:var(--grad-teal);padding:clamp(48px,7vw,84px);text-align:center;color:#fff;">
          <div class="mi-glow" aria-hidden="true" style="top:-60px;right:-40px;width:240px;height:240px;background:rgba(255,255,255,.12);"></div>
          <div class="mi-glow" aria-hidden="true" style="bottom:-80px;left:-50px;width:280px;height:280px;background:rgba(255,255,255,.08);"></div>
          <div style="position:relative;">
            <p class="mi-eyebrow mi-eyebrow--c mi-eyebrow--light" style="margin:0 auto 18px;">İletişim</p>
            <h2 class="mi-serif" style="font-size:clamp(28px,4.4vw,46px);font-weight:500;margin:0 0 18px;line-height:1.14;">Bu markanın ürünleri hakkında bilgi alın</h2>
            <p style="font-size:18px;opacity:.95;max-width:580px;margin:0 auto 36px;line-height:1.7;">Pistor Eliance ve MI-Medical Innovation sistemleri için ürün, içerik ve uygulama bilgilerine ekibimizden ulaşın. WhatsApp'tan yazın, hemen yanıtlayalım.</p>
            <a href="https://wa.me/905426205100" target="_blank" rel="noopener" class="mi-btn" style="background:#25D366;color:#fff;font-weight:700;box-shadow:0 14px 36px rgba(8,63,64,.22);">
              <svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 1 .4 1.8.7 2.4.9 1 .3 1.9.3 2.6.2.8-.1 2.6-1.1 2.9-2.1.4-1 .4-1.9.3-2.1-.1-.2-.4-.3-.8-.5z"/></svg>
              WhatsApp ile Bilgi Al</a>
          </div>
        </div>
      </div>
    </section>
EDV2P11S6;
        $p12s0 = <<<'EDV2P12S0'
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"><style>
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
          <span class="ng-pill reveal">Rejeneratif Cilt Bilimi</span>
          <h1 class="reveal d1">Hücreden <em>yeniden</em><br>doğan cilt.</h1>
          <p class="ng-hero__sub reveal d2">Neogenesis, bilim ve doğayı buluşturan rejeneratif bakım serisi. Büyüme faktörü ve hücre yenilenmesi temalı formülasyonlarla cildin kendini onarma sürecine eşlik eder — Estetik Dermal portföyünde.</p>
          <div class="ng-cta reveal d3">
            <a class="ng-btn ng-btn--solid" href="#yelpaze">Ürünleri İncele</a>
            <a class="ng-btn ng-btn--wa" href="https://wa.me/905426205100" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="#25D366" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.5c-2.5 0-4.9-.7-7-1.9l-.5-.3-4.7 1.2 1.3-4.6-.3-.5c-1.4-2.2-2.1-4.7-2.1-7.3C2.7 8.6 8.6 2.7 16 2.7S29.3 8.6 29.3 16 23.4 28.9 16 28.9zm8.2-9.9c-.4-.2-2.6-1.3-3-1.4-.4-.1-.7-.2-1 .2-.3.4-1.1 1.4-1.4 1.7-.3.3-.5.3-.9.1-.4-.2-1.9-.7-3.6-2.2-1.3-1.2-2.2-2.6-2.5-3-.3-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.3.3-.5.4-.8.1-.3 0-.5 0-.7-.1-.2-.9-2.4-1.3-3.3-.3-.8-.7-.7-.9-.7h-.8c-.3 0-.7.1-1.1.5-.4.4-1.4 1.4-1.4 3.4s1.5 4 1.7 4.3c.2.3 2.9 4.5 7.1 6.3 4 1.7 4 1.2 4.7 1.1.7-.1 2.4-1 2.7-1.9.3-1 .3-1.8.2-1.9z"/></svg>
              Bilgi Al — WhatsApp</a>
          </div>
          <div class="ng-trust reveal d4">
            <div><b>Rejeneratif</b> Bakım serisi</div>
            <div><b>Bilim + Doğa</b> Biyoteknoloji yaklaşımı</div>
            <div><b>Estetik Dermal</b> Resmi portföy</div>
          </div>
        </div>
        <div class="ng-hero__visual reveal d2">
          <div class="ng-hero__blob" aria-hidden="true"></div>
          <div class="ng-hero__card">
            <!-- 🖼️ GÖRSEL: /assets/img/neogenesis-hero.jpg (oran 4:5)
                 PROMPT: "Botanical-scientific skincare hero, a frosted glass serum bottle with a soft green dropper resting among fresh dewy green leaves and water droplets, gentle sage and ivory tones, soft diffused natural light, regenerative biotech aesthetic, cell-renewal mood, organic and clean, shallow depth of field; photorealistic, high resolution; no text, no logo, no watermark"
                 DEĞİŞTİR → bu .ng-hero__img bloğunu istersen <img>'e çevir. background-image hazır. -->
            <div class="ng-hero__img"><span>Rejeneratif Serum</span></div>
            <div class="ng-hero__meta">
              <div><small>Öne çıkan</small><b>Cellular Renewal Serum</b></div>
              <span class="ng-tag">Büyüme Faktörü</span>
            </div>
          </div>
          <div class="ng-hero__float">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2c4 3.5 4 9 0 13-4-4-4-9.5 0-13z"/><path d="M12 8v13"/><path d="M12 15c-2-1-3-2-5-2"/></svg></span>
            <div><small>Bilim + Doğa</small><b>Biyoteknoloji</b></div>
          </div>
        </div>
      </div>
    </section>

    
EDV2P12S0;
        $p12s1 = <<<'EDV2P12S1'
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
            <span class="ng-eyebrow">Marka Hikayesi</span>
            <h2>Doğanın yenilenme bilgeliği, bilimin hassasiyetiyle</h2>
            <p class="lead2">Neogenesis, cildin doğuştan gelen onarım kabiliyetine duyulan inançtan doğdu. Rejeneratif yaklaşımı; bitkisel kaynaklı aktiflerle büyüme faktörü ve hücre yenilenmesi temalı biyoteknolojiyi bir araya getirir. Amaç cildi zorlamak değil, kendi yenilenme döngüsüne nazikçe eşlik etmektir.</p>
            <ul class="ng-pillars">
              <li>
                <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2c4 3.5 4 9 0 13-4-4-4-9.5 0-13z"/><path d="M12 8v13"/></svg></span>
                <div><b>Doğadan ilham</b><span>Bitkisel kaynaklı aktifler ve nazik, dengeli formülasyon felsefesi.</span></div>
              </li>
              <li>
                <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/></svg></span>
                <div><b>Bilim odaklı</b><span>Büyüme faktörü ve hücre yenilenmesi temalı rejeneratif kozmetik yaklaşımı.</span></div>
              </li>
              <li>
                <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 22s7-4 7-12V5l-7-3-7 3v5c0 8 7 12 7 12z"/></svg></span>
                <div><b>Estetik Dermal portföyünde</b><span>2004'ten bu yana medikal estetik distribütörünün rejeneratif bakım serisi.</span></div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    
EDV2P12S1;
        $p12s2 = <<<'EDV2P12S2'
<!-- 3 · KREDİBİLİTE ŞERİDİ -->

    <section class="ng-section" style="padding-top:0;">
      <div class="wrap">
        <div class="ng-cred reveal">
          <div class="ng-cred__grid">
            <div class="ng-cred__cell">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 22s7-4 7-12V5l-7-3-7 3v5c0 8 7 12 7 12z"/></svg></div>
              <b>Estetik Dermal</b><span>Resmi portföy markası</span>
            </div>
            <div class="ng-cred__cell">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2c4 3.5 4 9 0 13-4-4-4-9.5 0-13z"/><path d="M12 8v13"/></svg></div>
              <b>Rejeneratif Seri</b><span>Cilt yenilenmesi temalı bakım</span>
            </div>
            <div class="ng-cred__cell">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg></div>
              <b>Bilim + Doğa</b><span>Biyoteknoloji yaklaşımı</span>
            </div>
            <div class="ng-cred__cell">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 7 9 18l-5-5"/></svg></div>
              <b>Profesyonel Kullanım</b><span>Klinik protokollere uyumlu</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    
EDV2P12S2;
        $p12s3 = <<<'EDV2P12S3'
<!-- 4 · ÖNE ÇIKAN ÜRÜNLER (split) -->

    <section class="ng-section" style="background:var(--ng-ivory);border-block:1px solid var(--ng-line);">
      <div class="wrap">
        <div class="ng-head reveal">
          <span class="ng-eyebrow">Öne Çıkan Ürünler</span>
          <h2>Serinin yenilenme imzası</h2>
          <p>Rejeneratif bakım rutininin merkezinde yer alan iki temel ürün — günlük yenilenme ve yoğun onarım için.</p>
        </div>

        <div class="ng-feat">
          <div class="ng-feat__media reveal">
            <!-- 🖼️ GÖRSEL: /assets/img/neogenesis-serum.jpg (oran 5:4)
                 PROMPT: "Premium regenerative skincare serum packshot, frosted glass bottle with green-tinted dropper on a sage gradient backdrop, a single clear serum droplet, soft botanical reflections, science-meets-nature aesthetic; photorealistic; no text, no logo" -->
            <div class="ng-feat__img" style="background-image:url('/assets/img/neogenesis-serum.jpg')"></div>
          </div>
          <div class="reveal d1">
            <span class="ng-eyebrow">Yenileyici Serum</span>
            <h3>Cellular Renewal Serum</h3>
            <p class="lead2">Büyüme faktörü temalı yenileyici serum; cildin doğal onarım döngüsünü desteklemek üzere hafif, hızlı emilen bir dokuyla geliştirildi. Günlük rejeneratif bakım rutininin temel adımı.</p>
            <span class="ng-chip">● Büyüme faktörü temalı · Günlük kullanım</span>
            <br>
            <a class="ng-link" href="/urun-detay">Ürün detayını incele <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </div>

        <div class="ng-feat ng-feat--rev">
          <div class="ng-feat__media reveal">
            <!-- 🖼️ GÖRSEL: /assets/img/neogenesis-mask.jpg (oran 5:4)
                 PROMPT: "Regenerative repair sheet mask presentation, soft green spa setting with fresh leaves and water droplets, calm sage and ivory tones, science-meets-nature wellness mood; photorealistic; no text, no logo" -->
            <div class="ng-feat__img" style="background-image:url('/assets/img/neogenesis-mask.jpg')"></div>
          </div>
          <div class="reveal d1">
            <span class="ng-eyebrow">Onarıcı Maske</span>
            <h3>Regenerative Repair Mask</h3>
            <p class="lead2">Yoğun onarım için tasarlanan, besleyici onarıcı maske. İşlem sonrası bakım ve yenilenme dönemlerinde cildi yatıştırmaya ve nem dengesini desteklemeye yardımcı olacak nazik formülasyon.</p>
            <span class="ng-chip">● Onarıcı bakım · Haftalık ritüel</span>
            <br>
            <a class="ng-link" href="#yelpaze">Tüm ürün yelpazesini gör <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </div>
      </div>
    </section>

    
EDV2P12S3;
        $p12s4 = <<<'EDV2P12S4'
<!-- 5 · ÜRÜN YELPAZESİ GRID -->

    <section class="ng-section" id="yelpaze">
      <div class="wrap">
        <div class="ng-head reveal">
          <span class="ng-eyebrow">Ürün Yelpazesi</span>
          <h2>Rejeneratif bakımın her adımı</h2>
          <p>Temizlikten yoğun onarıma uzanan, birbirini tamamlayan rejeneratif seri. Her ürün cildin yenilenme döngüsünün bir aşamasını destekler.</p>
        </div>
        <div class="ng-grid">

          <a class="ng-prod reveal" href="/urun-detay">
            <div class="ng-prod__img" style="background-image:url('/assets/img/neogenesis-serum.jpg')"></div>
            <div class="ng-prod__body">
              <span class="ng-prod__cat">Yenileyici Serum</span>
              <h3>Cellular Renewal Serum</h3>
              <p>Büyüme faktörü temalı, günlük yenilenme için hafif dokulu yenileyici serum.</p>
              <span class="ng-prod__more">Detay <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ng-prod reveal d1" href="/urun-detay">
            <div class="ng-prod__img" style="background-image:url('/assets/img/neogenesis-concentrate.jpg')"></div>
            <div class="ng-prod__body">
              <span class="ng-prod__cat">Konsantre</span>
              <h3>Growth Factor Concentrate</h3>
              <p>Büyüme faktörü konsantresi; yoğun yenilenme dönemleri için odaklı bakım.</p>
              <span class="ng-prod__more">Detay <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ng-prod reveal d2" href="/urun-detay">
            <div class="ng-prod__img" style="background-image:url('/assets/img/neogenesis-mask.jpg')"></div>
            <div class="ng-prod__body">
              <span class="ng-prod__cat">Onarıcı Maske</span>
              <h3>Regenerative Repair Mask</h3>
              <p>Yatıştırıcı, nem dengesini destekleyen onarıcı maske; haftalık yenilenme ritüeli.</p>
              <span class="ng-prod__more">Detay <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ng-prod reveal" href="/urun-detay">
            <div class="ng-prod__img" style="background-image:url('/assets/img/neogenesis-cleanser.jpg')"></div>
            <div class="ng-prod__body">
              <span class="ng-prod__cat">Temizleyici</span>
              <h3>Gentle Botanical Cleanser</h3>
              <p>Bitkisel kaynaklı, cildin doğal dengesini koruyan nazik temizleyici.</p>
              <span class="ng-prod__more">Detay <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ng-prod reveal d1" href="/urun-detay">
            <div class="ng-prod__img" style="background-image:url('/assets/img/neogenesis-cream.jpg')"></div>
            <div class="ng-prod__body">
              <span class="ng-prod__cat">Onarıcı Krem</span>
              <h3>Restorative Day Cream</h3>
              <p>Gün boyu nemlendiren, cildi koruyan ve yenilenmeyi destekleyen onarıcı krem.</p>
              <span class="ng-prod__more">Detay <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

          <a class="ng-prod reveal d2" href="/urun-detay">
            <div class="ng-prod__img" style="background-image:url('/assets/img/neogenesis-set.jpg')"></div>
            <div class="ng-prod__body">
              <span class="ng-prod__cat">Bakım Seti</span>
              <h3>Regenerative Care Set</h3>
              <p>Rejeneratif rutini bir arada sunan, birbirini tamamlayan ürünlerden oluşan bakım seti.</p>
              <span class="ng-prod__more">Detay <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
          </a>

        </div>
      </div>
    </section>

    
EDV2P12S4;
        $p12s5 = <<<'EDV2P12S5'
<!-- 6 · DOKTORLAR İÇİN / UYGULAMA -->

    <section class="ng-section" style="background:var(--ng-ivory);border-block:1px solid var(--ng-line);">
      <div class="wrap">
        <div class="ng-docs reveal">
          <div class="ng-docs__leaf" aria-hidden="true"></div>
          <span class="ng-eyebrow" style="color:var(--ng-leaf);">Doktorlar İçin</span>
          <h2 style="margin-top:18px;">Klinik bakım protokollerine uyumlu rejeneratif seri</h2>
          <p class="lead2">Neogenesis ürünleri, hekimlerin işlem öncesi ve sonrası bakım önerilerinde tamamlayıcı bir seçenek olarak konumlandırılır. Doğru kullanım ve protokol uyumu için Estetik Dermal ekibi yanınızda.</p>
          <div class="ng-docs__cards">
            <div class="ng-docc">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/></svg></div>
              <h4>Ürün Eğitimi</h4>
              <p>Serinin doğru kullanımı ve klinik rutinlere entegrasyonu için bilgilendirme.</p>
            </div>
            <div class="ng-docc">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2c4 3.5 4 9 0 13-4-4-4-9.5 0-13z"/><path d="M12 8v13"/></svg></div>
              <h4>Bakım Protokolü</h4>
              <p>İşlem sonrası tamamlayıcı bakım önerileri için pratik kullanım rehberliği.</p>
            </div>
            <div class="ng-docc">
              <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 14h2"/></svg></div>
              <h4>Tedarik &amp; Destek</h4>
              <p>Sipariş, stok ve bilgi talepleri için Estetik Dermal'dan kesintisiz destek.</p>
            </div>
          </div>
          <a class="ng-btn ng-btn--wagreen" href="https://wa.me/905426205100" target="_blank" rel="noopener">
            <svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4z"/></svg>
            Klinik bilgi talep edin</a>
          <p class="ng-note">Neogenesis ürünleri bakım amaçlı kozmetik serisidir; tıbbi tedavi veya tedavi sonucu garantisi sunmaz.</p>
        </div>
      </div>
    </section>

    
EDV2P12S5;
        $p12s6 = <<<'EDV2P12S6'
<!-- 7 · CTA BANDI -->

    <section class="ng-section" style="padding-top:clamp(56px,8vh,96px);">
      <div class="wrap">
        <div class="ng-ctaband reveal">
          <div class="ng-ctaband__o1" aria-hidden="true"></div>
          <div class="ng-ctaband__o2" aria-hidden="true"></div>
          <h2>Neogenesis hakkında bilgi alın</h2>
          <p>Ürün, fiyat ve klinik kullanım talepleriniz için ekibimiz hazır. WhatsApp'tan hızlıca yazın ya da tüm portföyü inceleyin.</p>
          <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
            <a class="ng-btn ng-btn--wagreen" href="https://wa.me/905426205100" target="_blank" rel="noopener">
              <svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L.3 31.6l7.9-2.1c2.3 1.3 5 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4z"/></svg>
              WhatsApp ile Yaz</a>
            <a class="ng-btn ng-btn--outline" href="/urunler">Tüm Ürünleri Gör</a>
          </div>
        </div>
      </div>
    </section>
EDV2P12S6;

        $pages=[
            ['slug'=>'home','title'=>'Ana Sayfa','sort_order'=>1,'show_in_menu'=>true,'sections'=>[
                ['HERO', $p0s0],
                ['TRUST BAND + COUNTERS + MARQUEE', $p0s1],
                ['FEATURED PRODUCTS', $p0s2],
                ['CATEGORIES', $p0s3],
                ['REPRESENTED BRANDS', $p0s4],
                ['FOR DOCTORS', $p0s5],
                ['ABOUT TEASER', $p0s6],
                ['CONTACT', $p0s7]
            ]],
            ['slug'=>'hakkimizda','title'=>'Hakkımızda','sort_order'=>2,'show_in_menu'=>true,'sections'=>[
                ['PAGE HERO', $p1s0],
                ['ABOUT STORY (split)', $p1s1],
                ['VİZYON & MİSYON', $p1s2],
                ['STATS BAND', $p1s3],
                ['PORTFOLIO BRANDS', $p1s4],
                ['FOR DOCTORS / TRAINING', $p1s5],
                ['CTA / CONTACT', $p1s6]
            ]],
            ['slug'=>'urunler','title'=>'Ürünler','sort_order'=>3,'show_in_menu'=>true,'sections'=>[
                ['PAGE HERO', $p2s0],
                ['FILTER CHIPS (statik)', $p2s1],
                ['CATEGORY GROUPS', $p2s2],
                ['PRODUCT GRID', $p2s3],
                ['INFO BAND + CTA', $p2s4]
            ]],
            ['slug'=>'urun-detay','title'=>'RRS® HA Long Lasting','sort_order'=>99,'show_in_menu'=>false,'sections'=>[
                ['PAGE HERO / BREADCRUMB', $p3s0],
                ['PRODUCT DETAIL (split)', $p3s1],
                ['PRODUCT DESCRIPTION (rich text)', $p3s2],
                ['RELATED PRODUCTS', $p3s3],
                ['CTA / FOR DOCTORS', $p3s4]
            ]],
            ['slug'=>'markalar','title'=>'Markalar','sort_order'=>4,'show_in_menu'=>true,'sections'=>[
                ['PAGE HERO', $p4s0],
                ['BRAND CARDS', $p4s1],
                ['KISA BANT', $p4s2],
                ['CTA', $p4s3]
            ]],
            ['slug'=>'etkinlikler','title'=>'Kongre & Etkinlikler','sort_order'=>5,'show_in_menu'=>true,'sections'=>[
                ['PAGE HERO', $p5s0],
                ['EVENT GRID', $p5s1],
                ['INFO BLOCK', $p5s2],
                ['CTA', $p5s3]
            ]],
            ['slug'=>'iletisim','title'=>'İletişim','sort_order'=>6,'show_in_menu'=>true,'sections'=>[
                ['PAGE HERO', $p6s0],
                ['CONTACT', $p6s1]
            ]],
            ['slug'=>'marka-skintech','title'=>'Skin Tech Pharma Group','sort_order'=>101,'show_in_menu'=>false,'sections'=>[
                ['1 · HERO', $p7s0],
                ['2 · MARKA HİKAYESİ', $p7s1],
                ['3 · KREDİBİLİTE ŞERİDİ', $p7s2],
                ['4 · ÖNE ÇIKAN ÜRÜNLER (split)', $p7s3],
                ['5 · ÜRÜN YELPAZESİ (grid)', $p7s4],
                ['6 · DOKTORLAR İÇİN / UYGULAMA', $p7s5],
                ['7 · CTA BANDI', $p7s6]
            ]],
            ['slug'=>'marka-seffiline','title'=>'Seffiline','sort_order'=>102,'show_in_menu'=>false,'sections'=>[
                ['1. HERO', $p8s0],
                ['2. MARKA HİKAYESİ', $p8s1],
                ['3. KREDİBİLİTE ŞERİDİ', $p8s2],
                ['4a. ÖNE ÇIKAN SPLIT: SeffiHair', $p8s3],
                ['4b. ÖNE ÇIKAN SPLIT: Seffiller (ters)', $p8s4],
                ['5. ÜRÜN AİLESİ GRID', $p8s5],
                ['6. DOKTORLAR İÇİN / UYGULAMA', $p8s6],
                ['7. CTA BANDI', $p8s7]
            ]],
            ['slug'=>'marka-aespio','title'=>'Grand Aespio','sort_order'=>103,'show_in_menu'=>false,'sections'=>[
                ['1) HERO', $p9s0],
                ['2) MARKA HİKAYESİ', $p9s1],
                ['3) KREDİBİLİTE ŞERİDİ', $p9s2],
                ['4) ÖNE ÇIKAN SPLIT: LFL Anchor + Beta-Glukan', $p9s3],
                ['5) ÜRÜN YELPAZESİ GRID', $p9s4],
                ['6) DOKTORLAR İÇİN / UYGULAMA', $p9s5],
                ['7) CTA BANDI (WhatsApp)', $p9s6]
            ]],
            ['slug'=>'marka-woorhi','title'=>'Woorhi Mechatronics','sort_order'=>104,'show_in_menu'=>false,'sections'=>[
                ['1 · HERO — gri mühendislik gridi + cihaz silüeti + elektrik glow', $p10s0],
                ['2 · MARKA HİKAYESİ — Kore mühendisliği, hassas kontrol, tekrarlanabilirlik', $p10s1],
                ['3 · KREDİBİLİTE ŞERİDİ', $p10s2],
                ['4 · RAFFINE SPOTLIGHT + monospace teknik spec tablosu', $p10s3],
                ['5 · ÜRÜN / CİHAZ GRID (Raffine + ilgili sarf)', $p10s4],
                ['6 · DOKTORLAR İÇİN / UYGULAMA — klinik cihaz kullanımı, demo/eğitim', $p10s5],
                ['7 · CTA BANDI — WhatsApp / demo talebi', $p10s6]
            ]],
            ['slug'=>'marka-mi-medical','title'=>'Mi Medical Innovation','sort_order'=>105,'show_in_menu'=>false,'sections'=>[
                ['1. HERO (full-height)', $p11s0],
                ['2. MARKA HİKAYESİ', $p11s1],
                ['3. KREDİBİLİTE ŞERİDİ', $p11s2],
                ['4. ÖNE ÇIKAN: PISTOR ELIANCE SPOTLIGHT', $p11s3],
                ['5. ÜRÜN GRID', $p11s4],
                ['6. DOKTORLAR İÇİN / UYGULAMA', $p11s5],
                ['7. CTA BANDI', $p11s6]
            ]],
            ['slug'=>'marka-neogenesis','title'=>'Neogenesis','sort_order'=>106,'show_in_menu'=>false,'sections'=>[
                ['1 · HERO', $p12s0],
                ['2 · MARKA HİKAYESİ', $p12s1],
                ['3 · KREDİBİLİTE ŞERİDİ', $p12s2],
                ['4 · ÖNE ÇIKAN ÜRÜNLER (split)', $p12s3],
                ['5 · ÜRÜN YELPAZESİ GRID', $p12s4],
                ['6 · DOKTORLAR İÇİN / UYGULAMA', $p12s5],
                ['7 · CTA BANDI', $p12s6]
            ]],
        ];
        foreach($pages as $p){
            $bodyBlocks=[];
            foreach($p['sections'] as $k=>$sec){
                $bodyBlocks[]=$this->blk('b_body_'.$k,'content-block','v2-free-html',$cb->id,$sec[1],$k+1);
            }
            Page::updateOrCreate(['slug'=>$p['slug'],'language_id'=>$langId],
                ['title'=>$p['title'],'status'=>'published','show_in_menu'=>$p['show_in_menu'],
                 'sort_order'=>$p['sort_order'],'show_breadcrumb'=>true,
                 'sections_json'=>['version'=>2,'regions'=>[
                     'header'=>[$this->regRow('header',[$this->blk('b_header','header','estetikdermal-v2-header',$hid,$hdr,1)])],
                     'body'  =>[$this->regRow('body',$bodyBlocks)],
                     'footer'=>[$this->regRow('footer',[$this->blk('b_footer','footer','estetikdermal-v2-footer',$fid,$ftr,1)])],
                 ]]]
            );
        }
        $this->command?->info('Tema 2: '.count($pages).' sayfa (bölünmüş body blokları) kuruldu.');
    }
}
