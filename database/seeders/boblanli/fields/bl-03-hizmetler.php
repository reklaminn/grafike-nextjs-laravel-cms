<?php

return [
    'variation' => 'bl-03-hizmetler',
    'name'      => 'Hizmetler (4 kart)',
    'html'      => <<<'HTML'
<section id="hizmetler" style="background:#fff;padding:clamp(64px,8vw,110px) 26px">
  <div style="max-width:1220px;margin:0 auto">
    <div data-reveal style="text-align:center;max-width:640px;margin:0 auto 48px">
      <div style="display:inline-flex;align-items:center;gap:12px;color:var(--ac);font-weight:700;font-size:13px;letter-spacing:.2em;text-transform:uppercase;margin-bottom:16px"><span style="width:28px;height:2px;background:var(--ac)"></span>{{eyebrow}}<span style="width:28px;height:2px;background:var(--ac)"></span></div>
      <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(2rem,4vw,3rem);text-transform:uppercase;color:var(--ink);margin:0 0 14px">{{title}}</h2>
      <p style="font-size:1.15rem;color:#5a5a5a;margin:0">{{subtitle}}</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(255px,1fr));gap:24px">
      <a class="svc-card" data-reveal href="{{s1_url}}" style="display:flex;flex-direction:column;background:#fff;border:1px solid #e9e9e9;padding:34px 28px 32px;position:relative;text-decoration:none;color:inherit"><span class="svc-bar" style="position:absolute;top:0;left:0;right:0;height:4px;background:var(--ac)"></span>
        <span style="width:56px;height:56px;color:var(--ac);margin-bottom:20px"><svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 4.5 13.5H11l-1 8.5L20 10h-6.5L13 2z"/></svg></span>
        <span style="font-family:'Space Grotesk',sans-serif;font-size:1.4rem;font-weight:700;color:var(--ink);margin:0 0 12px;display:block">{{s1_title}}</span>
        <span style="font-size:.98rem;line-height:1.65;color:#565656;flex:1;display:block">{{s1_desc}}</span>
        <span style="margin-top:18px;color:var(--ac);font-weight:700;font-size:14px;display:inline-flex;align-items:center;gap:6px">Detayı Gör →</span>
      </a>
      <a class="svc-card" data-reveal href="{{s2_url}}" style="display:flex;flex-direction:column;background:#fff;border:1px solid #e9e9e9;padding:34px 28px 32px;position:relative;text-decoration:none;color:inherit"><span class="svc-bar" style="position:absolute;top:0;left:0;right:0;height:4px;background:var(--ac)"></span>
        <span style="width:56px;height:56px;color:var(--ac);margin-bottom:20px"><svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9z"/><path d="M3 7.5 12 12l9-4.5"/><path d="M12 12v9"/></svg></span>
        <span style="font-family:'Space Grotesk',sans-serif;font-size:1.4rem;font-weight:700;color:var(--ink);margin:0 0 12px;display:block">{{s2_title}}</span>
        <span style="font-size:.98rem;line-height:1.65;color:#565656;flex:1;display:block">{{s2_desc}}</span>
        <span style="margin-top:18px;color:var(--ac);font-weight:700;font-size:14px;display:inline-flex;align-items:center;gap:6px">Detayı Gör →</span>
      </a>
      <a class="svc-card" data-reveal href="{{s3_url}}" style="display:flex;flex-direction:column;background:#fff;border:1px solid #e9e9e9;padding:34px 28px 32px;position:relative;text-decoration:none;color:inherit"><span class="svc-bar" style="position:absolute;top:0;left:0;right:0;height:4px;background:var(--ac)"></span>
        <span style="width:56px;height:56px;color:var(--ac);margin-bottom:20px"><svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5h13v5H3z"/><path d="M16 7h3.5a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H12v3"/><path d="M9.5 15h5v6h-5z"/></svg></span>
        <span style="font-family:'Space Grotesk',sans-serif;font-size:1.4rem;font-weight:700;color:var(--ink);margin:0 0 12px;display:block">{{s3_title}}</span>
        <span style="font-size:.98rem;line-height:1.65;color:#565656;flex:1;display:block">{{s3_desc}}</span>
        <span style="margin-top:18px;color:var(--ac);font-weight:700;font-size:14px;display:inline-flex;align-items:center;gap:6px">Detayı Gör →</span>
      </a>
      <a class="svc-card" data-reveal href="{{s4_url}}" style="display:flex;flex-direction:column;background:#fff;border:1px solid #e9e9e9;padding:34px 28px 32px;position:relative;text-decoration:none;color:inherit"><span class="svc-bar" style="position:absolute;top:0;left:0;right:0;height:4px;background:var(--ac)"></span>
        <span style="width:56px;height:56px;color:var(--ac);margin-bottom:20px"><svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V6l7-3v18"/><path d="M12 21V9l7 2.5V21"/><path d="M8 8v.01M8 12v.01M8 16v.01"/></svg></span>
        <span style="font-family:'Space Grotesk',sans-serif;font-size:1.4rem;font-weight:700;color:var(--ink);margin:0 0 12px;display:block">{{s4_title}}</span>
        <span style="font-size:.98rem;line-height:1.65;color:#565656;flex:1;display:block">{{s4_desc}}</span>
        <span style="margin-top:18px;color:var(--ac);font-weight:700;font-size:14px;display:inline-flex;align-items:center;gap:6px">Detayı Gör →</span>
      </a>
    </div>
  </div>
</section>
HTML,
    'schema'    => [
        'eyebrow'  => ['type' => 'text', 'label' => 'Üst Etiket'],
        'title'    => ['type' => 'text', 'label' => 'Başlık'],
        'subtitle' => ['type' => 'text', 'label' => 'Alt Metin'],
        's1_title' => ['type' => 'text', 'label' => 'Hizmet 1 — Başlık'],
        's1_desc'  => ['type' => 'textarea', 'label' => 'Hizmet 1 — Açıklama'],
        's1_url'   => ['type' => 'text', 'label' => 'Hizmet 1 — Detay Linki'],
        's2_title' => ['type' => 'text', 'label' => 'Hizmet 2 — Başlık'],
        's2_desc'  => ['type' => 'textarea', 'label' => 'Hizmet 2 — Açıklama'],
        's2_url'   => ['type' => 'text', 'label' => 'Hizmet 2 — Detay Linki'],
        's3_title' => ['type' => 'text', 'label' => 'Hizmet 3 — Başlık'],
        's3_desc'  => ['type' => 'textarea', 'label' => 'Hizmet 3 — Açıklama'],
        's3_url'   => ['type' => 'text', 'label' => 'Hizmet 3 — Detay Linki'],
        's4_title' => ['type' => 'text', 'label' => 'Hizmet 4 — Başlık'],
        's4_desc'  => ['type' => 'textarea', 'label' => 'Hizmet 4 — Açıklama'],
        's4_url'   => ['type' => 'text', 'label' => 'Hizmet 4 — Detay Linki'],
    ],
    'default'   => [
        'eyebrow'  => 'HİZMETLERİMİZ',
        'title'    => 'Sunduğumuz Hizmetler',
        'subtitle' => 'Tek elden, uçtan uca çözüm.',
        's1_title' => 'Kuşadası Elektrik Arıza ve Onarım',
        's1_desc'  => 'Kuşadası ve Aydın çevresinde ev, ofis ve işyerlerinde elektrik arıza tespiti ve onarımı. Sigorta atması, kaçak akım, pano arızası, priz ve aydınlatma sorunlarına hızlı müdahale; tesisat yenileme ve pano montajında garantili işçilik.',
        's1_url'   => '/kusadasi-elektrik-ariza-onarim',
        's2_title' => 'Toptan Elektrik Malzemesi Satışı',
        's2_desc'  => 'Kaliteli elektrik malzemelerini toptan ve perakende uygun fiyatlarla temin ediyoruz. Kablo, pano, sigorta, priz-anahtar, LED ve aydınlatma ürünlerinde geniş stok. Müteahhit ve işletmeler için Aydın bölgesinde hızlı tedarik.',
        's2_url'   => '/toptan-elektrik-malzemesi',
        's3_title' => 'Kuşadası Dekorasyon ve Tadilat',
        's3_desc'  => 'Daire, villa, ofis ve işyerleri için komple tadilat, iç dekorasyon ve kapsamlı revizyon. Boya-badana, alçıpan, zemin döşeme, mutfak ve banyo yenileme tek elden. Anahtar teslim daire tadilatında planlı süreç ve şeffaf fiyatlandırma.',
        's3_url'   => '/kusadasi-dekorasyon-tadilat',
        's4_title' => 'Kuşadası ve Aydın İnşaat Hizmetleri',
        's4_desc'  => 'Projelendirmeden anahtar teslime kadar profesyonel inşaat hizmeti. Konut, villa ve ticari yapı projelerinde sağlam mühendislik, kaliteli malzeme ve zamanında teslim. Kaba-ince yapı ve tadilat-güçlendirme işlerinde deneyimli ekip.',
        's4_url'   => '/kusadasi-aydin-insaat',
    ],
];
