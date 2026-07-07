<?php

return [
    'variation' => 'bl-rich-text',
    'name'      => 'SEO Metin Bölümü',
    'html'      => <<<'HTML'
<section style="background:{{bg}};padding:clamp(48px,7vw,90px) 26px">
  <div data-reveal style="max-width:880px;margin:0 auto">
    <div style="display:inline-flex;align-items:center;gap:12px;color:var(--ac);font-weight:700;font-size:13px;letter-spacing:.2em;text-transform:uppercase;margin-bottom:16px"><span style="width:28px;height:2px;background:var(--ac)"></span>{{eyebrow}}</div>
    <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(1.7rem,3.6vw,2.6rem);text-transform:uppercase;color:var(--ink);line-height:1.1;margin:0 0 22px">{{title}}</h2>
    <div class="bl-prose">{{{body_html}}}</div>
  </div>
  <style>
    .bl-prose{font-size:1.03rem;line-height:1.85;color:#3f3f3f}
    .bl-prose p{margin:0 0 18px}
    .bl-prose h3{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.35rem;color:var(--ink);margin:30px 0 12px}
    .bl-prose ul,.bl-prose ol{margin:0 0 18px;padding-left:22px}
    .bl-prose li{margin:0 0 8px}
    .bl-prose strong{color:var(--ink)}
    .bl-prose a{color:var(--ac);font-weight:600}
    .bl-prose a:hover{color:var(--ac-d)}
  </style>
</section>
HTML,
    'schema'    => [
        'eyebrow'   => ['type' => 'text', 'label' => 'Üst Etiket'],
        'title'     => ['type' => 'text', 'label' => 'Başlık (H2)'],
        'body_html' => ['type' => 'html', 'label' => 'İçerik (HTML — h3/p/ul serbest)'],
        'bg'        => ['type' => 'text', 'label' => 'Arka Plan (#fff / #F4F4F4)'],
    ],
    'default'   => [
        'eyebrow'   => 'BİLGİ',
        'title'     => 'Başlık',
        'body_html' => '<p>İçerik metni buraya gelecek.</p>',
        'bg'        => '#ffffff',
    ],
];
