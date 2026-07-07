<?php

return [
    'variation' => 'bl-02-guven',
    'name'      => 'Güven Şeridi',
    'html'      => <<<'HTML'
<section style="background:#111;border-top:1px solid rgba(255,255,255,.08)">
  <div style="max-width:1220px;margin:0 auto;padding:20px 26px;display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:14px 30px;font-size:15px;font-weight:600;color:#fff">
    <span style="display:inline-flex;align-items:center;gap:10px"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--ac)" stroke-width="1.7"><path d="M13 2 4.5 13.5H11l-1 8.5L20 10h-6.5L13 2z"/></svg>{{item1}}</span>
    <span style="color:var(--ac);font-weight:700">/</span>
    <span style="display:inline-flex;align-items:center;gap:10px"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--ac)" stroke-width="1.7"><path d="M22 11.5V12a10 10 0 1 1-5.9-9.1"/><path d="M22 4 12 14.1l-3-3"/></svg>{{item2}}</span>
    <span style="color:var(--ac);font-weight:700">/</span>
    <span style="display:inline-flex;align-items:center;gap:10px"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--ac)" stroke-width="1.7"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>{{item3}}</span>
  </div>
</section>
HTML,
    'schema'    => [
        'item1' => ['type' => 'text', 'label' => 'Öğe 1'],
        'item2' => ['type' => 'text', 'label' => 'Öğe 2'],
        'item3' => ['type' => 'text', 'label' => 'Öğe 3'],
    ],
    'default'   => [
        'item1' => 'Hızlı Müdahale',
        'item2' => 'Garantili İşçilik',
        'item3' => 'Kuşadası & Çevresi',
    ],
];
