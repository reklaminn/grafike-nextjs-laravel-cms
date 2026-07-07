<?php

return [
    'variation' => 'bl-cta',
    'name'      => 'CTA Bandı',
    'html'      => <<<'HTML'
<section style="position:relative;background:var(--ac);color:#fff;overflow:hidden;padding:clamp(48px,7vw,80px) 26px">
  <div style="position:absolute;top:-20%;right:14%;width:3px;height:140%;background:#fff;transform:rotate(20deg);opacity:.18"></div>
  <div style="position:absolute;top:-20%;right:10%;width:3px;height:140%;background:#fff;transform:rotate(20deg);opacity:.1"></div>
  <div data-reveal style="position:relative;max-width:1000px;margin:0 auto;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:24px">
    <div style="max-width:620px">
      <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(1.6rem,3.4vw,2.4rem);text-transform:uppercase;line-height:1.1;margin:0 0 10px">{{title}}</h2>
      <p style="font-size:1.05rem;line-height:1.55;color:rgba(255,255,255,.9);margin:0">{{subtitle}}</p>
    </div>
    <a class="btn-ghost" href="{{cta_url}}" style="flex-shrink:0;display:inline-flex;align-items:center;gap:10px;background:#1A1A1A;color:#fff;font-weight:600;font-size:15px;padding:16px 30px"><span style="display:inline-flex;align-items:center;gap:10px"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.4-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>{{cta_label}}</span></a>
  </div>
</section>
HTML,
    'schema'    => [
        'title'     => ['type' => 'text', 'label' => 'Başlık'],
        'subtitle'  => ['type' => 'textarea', 'label' => 'Alt Metin'],
        'cta_label' => ['type' => 'text', 'label' => 'Buton Metni'],
        'cta_url'   => ['type' => 'text', 'label' => 'Buton Linki'],
    ],
    'default'   => [
        'title'     => 'Projeniz İçin Ücretsiz Keşif',
        'subtitle'  => 'Kuşadası ve Aydın genelinde inşaat, tadilat ve elektrik işleriniz için bugün bize ulaşın.',
        'cta_label' => 'Hemen Arayın',
        'cta_url'   => 'tel:+905326576271',
    ],
];
