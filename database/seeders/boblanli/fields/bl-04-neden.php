<?php

return [
    'variation' => 'bl-04-neden',
    'name'      => 'Neden Biz (sayaç + avantaj)',
    'html'      => <<<'HTML'
<section id="neden-biz" style="background:#F4F4F4;padding:clamp(64px,8vw,110px) 26px">
  <div style="max-width:1220px;margin:0 auto">
    <div data-reveal style="text-align:center;max-width:640px;margin:0 auto 44px">
      <div style="display:inline-flex;align-items:center;gap:12px;color:var(--ac);font-weight:700;font-size:13px;letter-spacing:.2em;text-transform:uppercase;margin-bottom:16px"><span style="width:28px;height:2px;background:var(--ac)"></span>{{eyebrow}}<span style="width:28px;height:2px;background:var(--ac)"></span></div>
      <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(2rem,4vw,3rem);text-transform:uppercase;color:var(--ink);margin:0">{{title}}</h2>
    </div>

    <div data-reveal style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1px;background:#e0e0e0;margin-bottom:44px">
      <div style="background:#111;text-align:center;padding:32px 20px"><div style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(2.2rem,4vw,3rem);color:var(--ac)"><span data-count="{{c1_num}}" data-suffix="{{c1_suffix}}">0</span></div><div style="font-size:14px;font-weight:600;color:#cfcfcf;margin-top:6px">{{c1_label}}</div></div>
      <div style="background:#111;text-align:center;padding:32px 20px"><div style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(2.2rem,4vw,3rem);color:var(--ac)"><span data-count="{{c2_num}}" data-suffix="{{c2_suffix}}">0</span></div><div style="font-size:14px;font-weight:600;color:#cfcfcf;margin-top:6px">{{c2_label}}</div></div>
      <div style="background:#111;text-align:center;padding:32px 20px"><div style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(2.2rem,4vw,3rem);color:var(--ac)"><span data-count="{{c3_num}}" data-suffix="{{c3_suffix}}">0</span></div><div style="font-size:14px;font-weight:600;color:#cfcfcf;margin-top:6px">{{c3_label}}</div></div>
      <div style="background:#111;text-align:center;padding:32px 20px"><div style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(2.2rem,4vw,3rem);color:var(--ac)"><span data-count="{{c4_num}}" data-suffix="{{c4_suffix}}">0</span></div><div style="font-size:14px;font-weight:600;color:#cfcfcf;margin-top:6px">{{c4_label}}</div></div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:24px">
      <div data-reveal style="background:#fff;padding:32px 28px;border-left:4px solid var(--ac)"><div style="width:44px;height:44px;color:var(--ac);margin-bottom:16px"><svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M9.5 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 4a4 4 0 0 1 0 7.75"/></svg></div><h3 style="font-size:1.3rem;font-weight:700;color:var(--ink);margin:0 0 10px">{{a1_title}}</h3><p style="font-size:.97rem;line-height:1.6;color:#565656;margin:0">{{a1_desc}}</p></div>
      <div data-reveal style="background:#fff;padding:32px 28px;border-left:4px solid var(--ac)"><div style="width:44px;height:44px;color:var(--ac);margin-bottom:16px"><svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z"/><path d="M12 7v5l3 2"/></svg></div><h3 style="font-size:1.3rem;font-weight:700;color:var(--ink);margin:0 0 10px">{{a2_title}}</h3><p style="font-size:.97rem;line-height:1.6;color:#565656;margin:0">{{a2_desc}}</p></div>
      <div data-reveal style="background:#fff;padding:32px 28px;border-left:4px solid var(--ac)"><div style="width:44px;height:44px;color:var(--ac);margin-bottom:16px"><svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20.5 13.5 13 21a2 2 0 0 1-2.83 0L3 13.83V4h9.83l7.67 7.67a2 2 0 0 1 0 1.83z"/><path d="M7.5 7.5h.01"/></svg></div><h3 style="font-size:1.3rem;font-weight:700;color:var(--ink);margin:0 0 10px">{{a3_title}}</h3><p style="font-size:.97rem;line-height:1.6;color:#565656;margin:0">{{a3_desc}}</p></div>
      <div data-reveal style="background:#fff;padding:32px 28px;border-left:4px solid var(--ac)"><div style="width:44px;height:44px;color:var(--ac);margin-bottom:16px"><svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg></div><h3 style="font-size:1.3rem;font-weight:700;color:var(--ink);margin:0 0 10px">{{a4_title}}</h3><p style="font-size:.97rem;line-height:1.6;color:#565656;margin:0">{{a4_desc}}</p></div>
    </div>
  </div>
</section>
HTML,
    'schema'    => [
        'eyebrow'  => ['type' => 'text', 'label' => 'Üst Etiket'],
        'title'    => ['type' => 'text', 'label' => 'Başlık'],
        'c1_num'   => ['type' => 'text', 'label' => 'Sayaç 1 — Değer'],
        'c1_suffix'=> ['type' => 'text', 'label' => 'Sayaç 1 — Sonek'],
        'c1_label' => ['type' => 'text', 'label' => 'Sayaç 1 — Etiket'],
        'c2_num'   => ['type' => 'text', 'label' => 'Sayaç 2 — Değer'],
        'c2_suffix'=> ['type' => 'text', 'label' => 'Sayaç 2 — Sonek'],
        'c2_label' => ['type' => 'text', 'label' => 'Sayaç 2 — Etiket'],
        'c3_num'   => ['type' => 'text', 'label' => 'Sayaç 3 — Değer'],
        'c3_suffix'=> ['type' => 'text', 'label' => 'Sayaç 3 — Sonek'],
        'c3_label' => ['type' => 'text', 'label' => 'Sayaç 3 — Etiket'],
        'c4_num'   => ['type' => 'text', 'label' => 'Sayaç 4 — Değer'],
        'c4_suffix'=> ['type' => 'text', 'label' => 'Sayaç 4 — Sonek'],
        'c4_label' => ['type' => 'text', 'label' => 'Sayaç 4 — Etiket'],
        'a1_title' => ['type' => 'text', 'label' => 'Avantaj 1 — Başlık'],
        'a1_desc'  => ['type' => 'textarea', 'label' => 'Avantaj 1 — Açıklama'],
        'a2_title' => ['type' => 'text', 'label' => 'Avantaj 2 — Başlık'],
        'a2_desc'  => ['type' => 'textarea', 'label' => 'Avantaj 2 — Açıklama'],
        'a3_title' => ['type' => 'text', 'label' => 'Avantaj 3 — Başlık'],
        'a3_desc'  => ['type' => 'textarea', 'label' => 'Avantaj 3 — Açıklama'],
        'a4_title' => ['type' => 'text', 'label' => 'Avantaj 4 — Başlık'],
        'a4_desc'  => ['type' => 'textarea', 'label' => 'Avantaj 4 — Açıklama'],
    ],
    'default'   => [
        'eyebrow'  => 'NEDEN BOBLANLI YAPI?',
        'title'    => 'Neden Kuşadası\'nda Boblanlı Yapı?',
        'c1_num'   => '150', 'c1_suffix' => '+', 'c1_label' => 'Tamamlanan Proje',
        'c2_num'   => '10',  'c2_suffix' => '+', 'c2_label' => 'Yıl Tecrübe',
        'c3_num'   => '100', 'c3_suffix' => '%', 'c3_label' => 'Müşteri Memnuniyeti',
        'c4_num'   => '4',   'c4_suffix' => '',  'c4_label' => 'Ana Hizmet Alanı',
        'a1_title' => 'Deneyimli Ekip',       'a1_desc' => 'Aydın bölgesinde yılların saha tecrübesine sahip uzman kadro.',
        'a2_title' => 'Zamanında Teslim',     'a2_desc' => 'Söz verdiğimiz tarihte, eksiksiz ve temiz teslim.',
        'a3_title' => 'Şeffaf Fiyatlandırma', 'a3_desc' => 'Sürpriz maliyet yok; net keşif, açık teklif.',
        'a4_title' => 'Garantili İşçilik',    'a4_desc' => 'İnşaat, tadilat ve elektrik işlerinde garanti veriyoruz.',
    ],
];
