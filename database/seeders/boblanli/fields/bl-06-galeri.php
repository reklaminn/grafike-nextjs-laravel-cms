<?php

return [
    'variation' => 'bl-06-galeri',
    'name'      => 'Çalışmalarımız / Galeri',
    'html'      => <<<'HTML'
<section id="calismalar" style="background:#fff;padding:clamp(64px,8vw,110px) 26px">
  <div style="max-width:1220px;margin:0 auto">
    <div data-reveal style="text-align:center;max-width:640px;margin:0 auto 44px">
      <div style="display:inline-flex;align-items:center;gap:12px;color:var(--ac);font-weight:700;font-size:13px;letter-spacing:.2em;text-transform:uppercase;margin-bottom:16px"><span style="width:28px;height:2px;background:var(--ac)"></span>{{eyebrow}}<span style="width:28px;height:2px;background:var(--ac)"></span></div>
      <h2 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(2rem,4vw,3rem);text-transform:uppercase;color:var(--ink);margin:0 0 14px">{{title}}</h2>
      <p style="font-size:1.1rem;color:#5a5a5a;margin:0">{{subtitle}}</p>
    </div>
    <div class="gal-grid" data-reveal style="display:grid;grid-template-columns:repeat(4,1fr);grid-auto-rows:190px;gap:14px">{{{items_html}}}</div>
  </div>
</section>
HTML,
    'schema'    => [
        'eyebrow'  => ['type' => 'text', 'label' => 'Üst Etiket'],
        'title'    => ['type' => 'text', 'label' => 'Başlık'],
        'subtitle' => ['type' => 'text', 'label' => 'Alt Metin'],
        'items'    => [
            'type'          => 'repeater',
            'label'         => 'Galeri Öğeleri',
            'item_template' => '<figure class="{{cls}}" style="grid-column:span {{col}};grid-row:span {{row}};margin:0;position:relative;overflow:hidden"><div class="gal-img" style="width:100%;height:100%;background:{{img}};background-size:cover;background-position:center"></div><figcaption class="gal-ov" style="position:absolute;inset:0;display:flex;align-items:flex-end;padding:16px;background:linear-gradient(0deg,rgba(230,51,41,.82),rgba(230,51,41,.2));font-family:\'Space Grotesk\',sans-serif;font-weight:700;color:#fff">{{label}}</figcaption></figure>',
            'fields'        => [
                'label' => ['type' => 'text', 'label' => 'İş Adı'],
                'img'   => ['type' => 'text', 'label' => 'Görsel (url("...") veya renk)'],
                'col'   => ['type' => 'text', 'label' => 'Genişlik (1 / 2)'],
                'row'   => ['type' => 'text', 'label' => 'Yükseklik (1 / 2)'],
                'cls'   => ['type' => 'text', 'label' => 'Sınıf (gal / gal big)'],
            ],
        ],
    ],
    'default'   => [
        'eyebrow'  => 'REFERANSLAR',
        'title'    => 'Tamamlanan İşlerimiz',
        'subtitle' => 'Kuşadası ve çevresinde hayata geçirdiğimiz projelerden bir seçki.',
        'items'    => [
            ['label' => 'İnşaat Projesi',   'img' => 'linear-gradient(135deg,#2b2b2b,#171717)', 'col' => '2', 'row' => '2', 'cls' => 'gal big'],
            ['label' => 'İç Dekorasyon',    'img' => 'linear-gradient(135deg,#333,#1d1d1d)',   'col' => '1', 'row' => '1', 'cls' => 'gal'],
            ['label' => 'Elektrik İşleri',  'img' => 'linear-gradient(135deg,#2a2a2a,#161616)', 'col' => '1', 'row' => '1', 'cls' => 'gal'],
            ['label' => 'Banyo Revizyonu',  'img' => 'linear-gradient(135deg,#333,#1d1d1d)',   'col' => '2', 'row' => '1', 'cls' => 'gal big'],
            ['label' => 'Daire Tadilatı',   'img' => 'linear-gradient(135deg,#2b2b2b,#171717)', 'col' => '1', 'row' => '1', 'cls' => 'gal'],
            ['label' => 'Mutfak Yenileme',  'img' => 'linear-gradient(135deg,#2a2a2a,#161616)', 'col' => '1', 'row' => '1', 'cls' => 'gal'],
            ['label' => 'İş Yeri Tadilatı', 'img' => 'linear-gradient(135deg,#333,#1d1d1d)',   'col' => '2', 'row' => '1', 'cls' => 'gal big'],
            ['label' => 'Aydınlatma',       'img' => 'linear-gradient(135deg,#2b2b2b,#171717)', 'col' => '1', 'row' => '1', 'cls' => 'gal'],
        ],
    ],
];
