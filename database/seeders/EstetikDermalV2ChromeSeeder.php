<?php

namespace Database\Seeders;

use App\Models\SectionTemplate;
use App\Models\Theme;
use Illuminate\Database\Seeder;

/** Estetik Dermal TEMA 2 chrome: v2 header/footer (v2.css inline) + content-block. */
class EstetikDermalV2ChromeSeeder extends Seeder
{
    private const TENANT_ID='estetik_dermal';
    public function run(): void
    {
        $theme = Theme::where('slug','estetikdermal-v2')->first();
        if(! $theme){ $this->command?->warn('Tema 2 yok — önce V2ThemeSeeder.'); return; }
        $style = <<<'EDV2CSS'
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
</style>
EDV2CSS;

        $headerHtml = <<<'EDV2HDR'
<header class="nav" id="nav">
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
EDV2HDR;

        $footerHtml = <<<'EDV2FTR'
<footer class="footer">
    <div class="wrap footer__grid">
      <div>
        <div class="footer__brand">Estetik Dermal</div>
        <p style="margin:16px 0 0;max-width:300px;font-size:14.5px;line-height:1.7;">2004'ten bu yana medikal estetiğin yenilikçi ürünlerini doktorlara sunan resmi distribütör. Kuşadası / Aydın.</p>
      </div>
      <div><h5>Kurumsal</h5><ul><li><a href="/hakkimizda">Hakkımızda</a></li><li><a href="/markalar">Markalar</a></li><li><a href="/etkinlikler">Eğitim &amp; Kongre</a></li><li><a href="/iletisim">İletişim</a></li></ul></div>
      <div><h5>Markalar</h5><ul><li><a href="/marka/skintech">Skin Tech Pharma</a></li><li><a href="/marka/seffiline">Seffiline</a></li><li><a href="/marka/aespio">Grand Aespio</a></li><li><a href="/marka/woorhi">Woorhi</a></li><li><a href="/marka/mi-medical">Mi Medical</a></li></ul></div>
      <div><h5>İletişim</h5><ul><li><a href="tel:+902566121813">0 256 612 18 13</a></li><li><a href="https://wa.me/905426205100" target="_blank" rel="noopener">+90 542 620 51 00</a></li><li><a href="mailto:info@estetikdermal.com">info@estetikdermal.com</a></li><li><a href="https://www.instagram.com/estetikdermal/" target="_blank" rel="noopener">Instagram</a></li></ul></div>
    </div>
    <div class="wrap footer__bottom"><span>© 2026 Estetik Dermal. Tüm hakları saklıdır.</span><span>Resmi medikal estetik distribütörü · Kuşadası / Aydın</span></div>
  </footer>
EDV2FTR;

        $blocks=[
            ['type'=>'header','variation'=>'estetikdermal-v2-header','name'=>'Tema2 / Header','html'=>$style."\n".$headerHtml],
            ['type'=>'footer','variation'=>'estetikdermal-v2-footer','name'=>'Tema2 / Footer','html'=>$footerHtml],
            ['type'=>'content-block','variation'=>'v2-free-html','name'=>'Tema2 / İçerik Bloğu','html'=>'{{{html}}}'],
        ];
        foreach($blocks as $b){
            SectionTemplate::updateOrCreate(
                ['theme_id'=>$theme->id,'type'=>$b['type'],'variation'=>$b['variation']],
                ['tenant_id'=>self::TENANT_ID,'module'=>null,'name'=>$b['name'],'render_mode'=>'html',
                 'html_template'=>$b['html'],'schema_json'=>($b['type']==='content-block'?['html'=>['type'=>'html','label'=>'Serbest HTML']]:[]),
                 'default_content_json'=>($b['type']==='content-block'?['html'=>'']:[]),'is_active'=>true]
            );
        }
        $this->command?->info('Tema 2 chrome + content-block kuruldu.');
    }
}
