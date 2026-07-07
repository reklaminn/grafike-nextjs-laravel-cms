<?php

return [
    'variation' => 'bl-page-hero',
    'name'      => 'Alt Sayfa — Başlık Banner',
    'html'      => <<<'HTML'
<section style="position:relative;background:#0d0d0d;color:#fff;overflow:hidden;padding:clamp(70px,12vh,120px) 26px clamp(48px,8vh,72px)">
  <div style="position:absolute;inset:0;background:repeating-linear-gradient(45deg,#141414,#141414 2px,#0d0d0d 2px,#0d0d0d 22px);opacity:.5"></div>
  <div style="position:absolute;top:-15%;right:12%;width:3px;height:130%;background:var(--ac);transform:rotate(20deg);opacity:.9"></div>
  <div style="position:absolute;top:-15%;right:8%;width:3px;height:130%;background:var(--ac);transform:rotate(20deg);opacity:.35"></div>
  <div style="position:relative;max-width:1220px;margin:0 auto">
    <div data-reveal style="display:inline-flex;align-items:center;gap:10px;color:var(--ac);font-weight:700;font-size:12.5px;letter-spacing:.2em;text-transform:uppercase;margin-bottom:16px"><span style="width:26px;height:2px;background:var(--ac)"></span>{{eyebrow}}</div>
    <h1 data-reveal style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(2rem,5vw,3.4rem);line-height:1.02;text-transform:uppercase;letter-spacing:-0.01em;margin:0 0 16px;max-width:20ch">{{title}}</h1>
    <p data-reveal style="font-size:clamp(1rem,1.5vw,1.15rem);line-height:1.6;color:#b0b0b0;max-width:640px;margin:0 0 18px">{{subtitle}}</p>
    <nav data-reveal aria-label="breadcrumb" style="font-size:13px;color:#9a9a9a">
      <a href="/" style="color:#9a9a9a">Ana Sayfa</a> <span style="color:var(--ac);margin:0 6px">/</span> <span style="color:#e0e0e0">{{crumb}}</span>
    </nav>
  </div>
</section>
HTML,
    'schema'    => [
        'eyebrow'  => ['type' => 'text', 'label' => 'Üst Etiket'],
        'title'    => ['type' => 'text', 'label' => 'Başlık (H1)'],
        'subtitle' => ['type' => 'textarea', 'label' => 'Alt Metin'],
        'crumb'    => ['type' => 'text', 'label' => 'Breadcrumb Adı'],
    ],
    'default'   => [
        'eyebrow'  => 'BOBLANLI YAPI',
        'title'    => 'Sayfa Başlığı',
        'subtitle' => 'Kuşadası ve Aydın genelinde güvenilir yapı hizmetleri.',
        'crumb'    => 'Sayfa',
    ],
];
