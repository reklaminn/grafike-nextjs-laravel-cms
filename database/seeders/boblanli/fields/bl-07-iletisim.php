<?php

return [
    'variation' => 'bl-07-iletisim',
    'name'      => 'İletişim — Bilgiler + Harita',
    'html'      => <<<'HTML'
<section id="iletisim" style="position:relative;background:#111;color:#fff;padding:clamp(64px,8vw,90px) 26px 30px;overflow:hidden">
  <div style="position:absolute;top:-15%;right:14%;width:3px;height:130%;background:var(--ac);transform:rotate(20deg);opacity:.22"></div>
  <div style="position:relative;max-width:1220px;margin:0 auto">
    <div data-reveal style="max-width:760px;margin:0 0 44px">
      <div style="display:inline-flex;align-items:center;gap:12px;color:var(--ac);font-weight:700;font-size:13px;letter-spacing:.2em;text-transform:uppercase;margin-bottom:16px"><span style="width:28px;height:2px;background:var(--ac)"></span>{{eyebrow}}</div>
      <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(2rem,4vw,3rem);text-transform:uppercase;margin:0 0 16px">{{title}}</h2>
      <p style="font-size:1.05rem;line-height:1.6;color:#b0b0b0;margin:0">{{subtitle}}</p>
    </div>
    <div data-reveal style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:44px;align-items:start">
      <div style="display:flex;flex-direction:column;gap:18px">
        <a href="tel:{{phone_href}}" style="display:flex;align-items:center;gap:16px;color:#fff"><span style="width:50px;height:50px;flex-shrink:0;background:var(--ac);display:flex;align-items:center;justify-content:center"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.7"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.4-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg></span><span><span style="display:block;font-size:12px;color:#9a9a9a;text-transform:uppercase;letter-spacing:.1em">Telefon</span><span style="font-weight:600;font-size:16px">{{phone}}</span></span></a>
        <a href="https://wa.me/{{whatsapp}}" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:16px;color:#fff"><span style="width:50px;height:50px;flex-shrink:0;background:var(--ac);display:flex;align-items:center;justify-content:center"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.7"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.4 8.4 0 0 1-4-.9L3 21l1.9-4.5a8.4 8.4 0 0 1-.9-4A8.4 8.4 0 0 1 12 4a8.4 8.4 0 0 1 9 7.5z"/></svg></span><span><span style="display:block;font-size:12px;color:#9a9a9a;text-transform:uppercase;letter-spacing:.1em">WhatsApp</span><span style="font-weight:600;font-size:16px">Hemen Yazın</span></span></a>
        <div style="display:flex;align-items:flex-start;gap:16px"><span style="width:50px;height:50px;flex-shrink:0;background:#2a2a2a;display:flex;align-items:center;justify-content:center"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--ac)" stroke-width="1.7"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span><span><span style="display:block;font-size:12px;color:#9a9a9a;text-transform:uppercase;letter-spacing:.1em">Adres</span><span style="font-size:15px;line-height:1.5;color:#e0e0e0">{{address}}</span></span></div>
        <div style="display:flex;gap:22px;margin-top:4px">
          <a href="{{instagram_url}}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;color:#b0b0b0;font-size:14px"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>{{instagram_label}}</a>
          <a href="https://boblanliyapi.com" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;color:#b0b0b0;font-size:14px"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"/></svg>boblanliyapi.com</a>
        </div>
      </div>
      <div style="width:100%">
        <div style="aspect-ratio:16/9;border-radius:14px;overflow:hidden;background:#1c1c1c;border:1px solid #2a2a2a">{{{map_embed}}}</div>
      </div>
    </div>
  </div>
</section>
HTML,
    'schema'    => [
        'eyebrow'         => ['type' => 'text', 'label' => 'Üst Etiket'],
        'title'           => ['type' => 'text', 'label' => 'Başlık'],
        'subtitle'        => ['type' => 'textarea', 'label' => 'Alt Metin'],
        'phone'           => ['type' => 'text', 'label' => 'Telefon (görünen)'],
        'phone_href'      => ['type' => 'text', 'label' => 'Telefon (tel: linki)'],
        'whatsapp'        => ['type' => 'text', 'label' => 'WhatsApp No'],
        'address'         => ['type' => 'textarea', 'label' => 'Adres'],
        'instagram_url'   => ['type' => 'text', 'label' => 'Instagram Linki'],
        'instagram_label' => ['type' => 'text', 'label' => 'Instagram Etiketi'],
        'map_embed'       => ['type' => 'html', 'label' => 'Harita Embed (iframe)'],
    ],
    'default'   => [
        'eyebrow'         => 'İLETİŞİM',
        'title'           => 'Kuşadası İnşaat ve Tadilat İçin Bize Ulaşın',
        'subtitle'        => 'Kuşadası, Aydın\'da inşaat, dekorasyon, revizyon veya elektrik hizmeti mi arıyorsunuz? Ücretsiz keşif için bir telefon uzağınızdayız. Türkmen Mahallesi\'ndeki ofisimize uğrayın ya da WhatsApp\'tan hemen yazın.',
        'phone'           => '+90 532 657 62 71',
        'phone_href'      => '+905326576271',
        'whatsapp'        => '905326576271',
        'address'         => 'Türkmen Mahallesi, Bahçearası Sok. No:2 İç Kapı:3, Kuşadası / Aydın',
        'instagram_url'   => 'https://instagram.com/tahsinboblanli',
        'instagram_label' => '@tahsinboblanli',
        'map_embed'       => '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#666;font-size:14px;background:repeating-linear-gradient(45deg,#1e1e1e,#1e1e1e 10px,#1a1a1a 10px,#1a1a1a 20px)">Harita — Google Maps embed eklenecek (Türkmen Mah., Kuşadası)</div>',
    ],
];
