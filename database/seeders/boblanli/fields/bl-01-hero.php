<?php

return [
    'variation' => 'bl-01-hero',
    'name'      => 'Ana Sayfa — Hero',
    'html'      => <<<'HTML'
<section id="anasayfa" style="position:relative;background:#0d0d0d;color:#fff;overflow:hidden">
  <div style="position:absolute;inset:0;background:{{bg_css}};background-size:cover;background-position:center;opacity:.9"></div>
  <div style="position:absolute;inset:0;background:linear-gradient(90deg,rgba(11,11,11,.95) 0%,rgba(11,11,11,.8) 42%,rgba(11,11,11,.33) 100%)"></div>
  <div style="position:absolute;top:-15%;right:12%;width:3px;height:130%;background:var(--ac);transform:rotate(20deg);opacity:.9"></div>
  <div style="position:absolute;top:-15%;right:8%;width:3px;height:130%;background:var(--ac);transform:rotate(20deg);opacity:.35"></div>
  <div style="position:relative;max-width:1220px;margin:0 auto;min-height:clamp(560px,88vh,860px);display:flex;flex-direction:column;justify-content:center;padding:clamp(90px,15vh,150px) 26px clamp(70px,11vh,120px)">
    <div data-reveal style="display:inline-flex;align-items:center;gap:8px;align-self:flex-start;border:1px solid rgba(255,255,255,.28);padding:8px 15px;margin-bottom:26px;font-size:12.5px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#fff">
      <span style="width:8px;height:8px;background:var(--ac)"></span>{{eyebrow}}
    </div>
    <h1 data-reveal style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(2.4rem,6.2vw,5rem);line-height:.98;text-transform:uppercase;letter-spacing:-0.01em;max-width:16ch;margin:0 0 24px">{{{title_html}}}</h1>
    <p data-reveal style="font-size:clamp(1.02rem,1.7vw,1.25rem);line-height:1.6;color:#cfcfcf;max-width:560px;margin:0 0 34px">{{subtitle}}</p>
    <div data-reveal style="display:flex;flex-wrap:wrap;gap:14px">
      <a class="btn-slide" href="{{cta_primary_url}}" style="display:inline-flex;align-items:center;gap:10px;background:var(--ac);color:#fff;font-weight:600;font-size:15px;padding:15px 26px"><span style="display:inline-flex;align-items:center;gap:10px"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.4-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>{{cta_primary_label}}</span></a>
      <a class="btn-ghost" href="{{cta_secondary_url}}" style="display:inline-flex;align-items:center;color:#fff;font-weight:600;font-size:15px;padding:15px 26px;border:2px solid rgba(255,255,255,.3)"><span>{{cta_secondary_label}}</span></a>
    </div>
  </div>
</section>
HTML,
    'schema'    => [
        'eyebrow'             => ['type' => 'text', 'label' => 'Üst Etiket'],
        'title_html'          => ['type' => 'html', 'label' => 'Başlık (H1, kırmızı için <span style="color:var(--ac)">…</span>)'],
        'subtitle'            => ['type' => 'textarea', 'label' => 'Alt Metin'],
        'cta_primary_label'   => ['type' => 'text', 'label' => 'Birincil Buton Metni'],
        'cta_primary_url'     => ['type' => 'text', 'label' => 'Birincil Buton Linki'],
        'cta_secondary_label' => ['type' => 'text', 'label' => 'İkincil Buton Metni'],
        'cta_secondary_url'   => ['type' => 'text', 'label' => 'İkincil Buton Linki'],
        'bg_css'              => ['type' => 'text', 'label' => 'Arka Plan (CSS — url("...") veya gradient)'],
    ],
    'default'   => [
        'eyebrow'             => '▪ KUŞADASI · AYDIN BÖLGESİ',
        'title_html'          => 'Kuşadası\'nda <span style="color:var(--ac)">İnşaat, Tadilat</span> ve <span style="color:var(--ac)">Elektrik</span> Hizmetleri',
        'subtitle'            => 'Boblanlı Yapı; Kuşadası ve Aydın genelinde inşaat, iç dekorasyon, kapsamlı revizyon ve elektrik arıza-onarım hizmetlerini tek çatı altında sunar. Güvenilir işçilik, zamanında teslim, uygun fiyat.',
        'cta_primary_label'   => 'Ücretsiz Keşif İçin Arayın',
        'cta_primary_url'     => 'tel:+905326576271',
        'cta_secondary_label' => 'Hizmetlerimizi İnceleyin',
        'cta_secondary_url'   => '#hizmetler',
        'bg_css'              => 'repeating-linear-gradient(45deg,#141414,#141414 2px,#0d0d0d 2px,#0d0d0d 22px)',
    ],
];
