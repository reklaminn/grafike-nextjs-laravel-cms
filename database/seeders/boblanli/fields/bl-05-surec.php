<?php

return [
    'variation' => 'bl-05-surec',
    'name'      => 'Çalışma Süreci (4 adım)',
    'html'      => <<<'HTML'
<section id="surec" style="position:relative;background:#111;color:#fff;padding:clamp(64px,8vw,110px) 26px;overflow:hidden">
  <div style="position:absolute;top:-15%;left:20%;width:3px;height:130%;background:var(--ac);transform:rotate(20deg);opacity:.25"></div>
  <div style="position:relative;max-width:1220px;margin:0 auto">
    <div data-reveal style="text-align:center;max-width:640px;margin:0 auto 54px">
      <div style="display:inline-flex;align-items:center;gap:12px;color:var(--ac);font-weight:700;font-size:13px;letter-spacing:.2em;text-transform:uppercase;margin-bottom:16px"><span style="width:28px;height:2px;background:var(--ac)"></span>{{eyebrow}}<span style="width:28px;height:2px;background:var(--ac)"></span></div>
      <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(2rem,4vw,3rem);text-transform:uppercase;margin:0">{{title}}</h2>
    </div>
    <div style="position:relative">
      <div style="position:absolute;top:22px;left:8%;right:8%;height:2px;background:linear-gradient(90deg,var(--ac),rgba(230,51,41,.15))"></div>
      <div style="position:relative;display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:26px">
        <div data-reveal><div style="width:44px;height:44px;border-radius:50%;background:var(--ac);border:4px solid #111;display:flex;align-items:center;justify-content:center;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:18px;color:#fff;margin-bottom:18px">1</div><h3 style="font-size:1.25rem;font-weight:700;margin:0 0 8px;display:flex;align-items:center;gap:10px"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ac)" stroke-width="1.7"><path d="M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16z"/><path d="M21 21l-4.3-4.3"/></svg>{{s1_title}}</h3><p style="font-size:.95rem;line-height:1.6;color:#a8a8a8;margin:0">{{s1_desc}}</p></div>
        <div data-reveal><div style="width:44px;height:44px;border-radius:50%;background:var(--ac);border:4px solid #111;display:flex;align-items:center;justify-content:center;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:18px;color:#fff;margin-bottom:18px">2</div><h3 style="font-size:1.25rem;font-weight:700;margin:0 0 8px;display:flex;align-items:center;gap:10px"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ac)" stroke-width="1.7"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6"/><path d="M9 13h6M9 17h6"/></svg>{{s2_title}}</h3><p style="font-size:.95rem;line-height:1.6;color:#a8a8a8;margin:0">{{s2_desc}}</p></div>
        <div data-reveal><div style="width:44px;height:44px;border-radius:50%;background:var(--ac);border:4px solid #111;display:flex;align-items:center;justify-content:center;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:18px;color:#fff;margin-bottom:18px">3</div><h3 style="font-size:1.25rem;font-weight:700;margin:0 0 8px;display:flex;align-items:center;gap:10px"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ac)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M15 12l-8.5 8.5a2.1 2.1 0 0 1-3-3L12 9"/><path d="M17.6 6.4 14 10l-2-2 3.6-3.6a2 2 0 0 1 2.8 0l1.2 1.2a2 2 0 0 1 0 2.8L18 12l-2-2"/></svg>{{s3_title}}</h3><p style="font-size:.95rem;line-height:1.6;color:#a8a8a8;margin:0">{{s3_desc}}</p></div>
        <div data-reveal><div style="width:44px;height:44px;border-radius:50%;background:var(--ac);border:4px solid #111;display:flex;align-items:center;justify-content:center;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:18px;color:#fff;margin-bottom:18px">4</div><h3 style="font-size:1.25rem;font-weight:700;margin:0 0 8px;display:flex;align-items:center;gap:10px"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--ac)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 9.5a4.5 4.5 0 1 0-4.9 4.48L9 15l-2 2 1 1-1 1 1 1 2-2 2-2-1.1-1.02A4.5 4.5 0 0 0 14.5 9.5z"/><path d="M16 8.5h.01"/></svg>{{s4_title}}</h3><p style="font-size:.95rem;line-height:1.6;color:#a8a8a8;margin:0">{{s4_desc}}</p></div>
      </div>
    </div>
  </div>
</section>
HTML,
    'schema'    => [
        'eyebrow'  => ['type' => 'text', 'label' => 'Üst Etiket'],
        'title'    => ['type' => 'text', 'label' => 'Başlık'],
        's1_title' => ['type' => 'text', 'label' => 'Adım 1 — Başlık'],
        's1_desc'  => ['type' => 'text', 'label' => 'Adım 1 — Açıklama'],
        's2_title' => ['type' => 'text', 'label' => 'Adım 2 — Başlık'],
        's2_desc'  => ['type' => 'text', 'label' => 'Adım 2 — Açıklama'],
        's3_title' => ['type' => 'text', 'label' => 'Adım 3 — Başlık'],
        's3_desc'  => ['type' => 'text', 'label' => 'Adım 3 — Açıklama'],
        's4_title' => ['type' => 'text', 'label' => 'Adım 4 — Başlık'],
        's4_desc'  => ['type' => 'text', 'label' => 'Adım 4 — Açıklama'],
    ],
    'default'   => [
        'eyebrow'  => 'SÜRECİMİZ',
        'title'    => 'Nasıl Çalışıyoruz?',
        's1_title' => 'Keşif & Görüşme',   's1_desc' => 'Yerinde keşif yapar, ihtiyacınızı dinleriz.',
        's2_title' => 'Teklif & Planlama',  's2_desc' => 'Net teklif ve iş planını birlikte belirleriz.',
        's3_title' => 'Uygulama',           's3_desc' => 'Titiz işçilikle, plana sadık kalarak uygularız.',
        's4_title' => 'Teslim & Destek',    's4_desc' => 'Eksiksiz teslim eder, sonrasında destek veririz.',
    ],
];
