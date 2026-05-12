<?php

namespace Database\Seeders;

use App\Models\SiteTemplate;
use Illuminate\Database\Seeder;

/**
 * Industry-specific SiteTemplate library (FAZ 4.3).
 *
 * Each entry produces one row in central.site_templates with:
 *   - industry      : sector code (clinic, lawyer, salon, hotel, real_estate, corporate)
 *   - snapshot_json : pages + menus + settings to seed into a tenant
 *
 * Block sections reference SectionTemplates by `template_type` (or
 * `template_type` + `template_variation`). At apply-time, the
 * IndustryTemplateApplier resolves these to real section_template_id's
 * from the central catalog and merges `content` over default_content_json.
 *
 * Adding a new industry: append a new entry to $TEMPLATES — see the
 * comments inline for the snapshot shape. Run:
 *   php artisan db:seed --class=IndustryTemplatesSeeder
 */
class IndustryTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $i => $tpl) {
            SiteTemplate::updateOrCreate(
                ['slug' => $tpl['slug']],
                [
                    'name'          => $tpl['name'],
                    'industry'      => $tpl['industry'],
                    'description'   => $tpl['description'],
                    'summary'       => $tpl['summary'],
                    'preview_image' => $tpl['preview_image'] ?? null,
                    'is_active'     => true,
                    'sort_order'    => $i + 1,
                    'snapshot_json' => $tpl['snapshot_json'],
                ],
            );
        }
    }

    private function templates(): array
    {
        return [
            $this->clinic(),
            $this->lawyer(),
            $this->salon(),
            $this->hotel(),
            $this->realEstate(),
            $this->corporate(),
        ];
    }

    // ─── KLİNİK / SAĞLIK ────────────────────────────────────────────────
    private function clinic(): array
    {
        return [
            'slug'        => 'industry-clinic-modern',
            'name'        => 'Klinik — Modern',
            'industry'    => 'clinic',
            'summary'     => 'Diş, estetik, tıp merkezleri için modern bir başlangıç.',
            'description' => 'Hero + hizmetler + ekip + iletişim. Mavi pastel tonlu, güven veren bir tasarım.',
            'snapshot_json' => [
                'settings' => [
                    'site.title'            => 'Klinik',
                    'site.tagline'          => 'Sağlığınız için modern çözümler',
                    'design.color_primary'  => '#2563eb',
                    'design.color_secondary' => '#eff6ff',
                    'design.color_accent'   => '#0f172a',
                    'contact.phone'         => '+90 212 000 0000',
                    'contact.email'         => 'info@klinik.com',
                    'contact.address'       => 'İstanbul, Türkiye',
                ],
                'pages' => [
                    [
                        'title'        => 'Anasayfa',
                        'slug'         => 'anasayfa',
                        'show_in_menu' => true,
                        'sections'     => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow'     => 'Modern Sağlık',
                                'title'       => 'Sağlığınız için güvenilir adres',
                                'subtitle'    => 'Uzman ekibimiz ve modern donanımımızla yanınızdayız.',
                                'button_text' => 'Randevu Al',
                                'button_url'  => '/iletisim',
                            ]],
                            ['template_type' => 'features', 'content' => [
                                'title'       => 'Hizmet Alanlarımız',
                                'description' => 'İmplant, gülüş tasarımı, ortodonti ve daha fazlası.',
                            ]],
                            ['template_type' => 'rich-text', 'content' => [
                                'title'     => 'Neden Bizi Tercih Etmelisiniz?',
                                'body_html' => '<p>15 yıllık deneyim, modern ekipman, hijyen standardı. Her hastamıza özel bakım planı sunuyoruz.</p>',
                            ]],
                        ],
                    ],
                    [
                        'title'        => 'Hizmetlerimiz',
                        'slug'         => 'hizmetler',
                        'show_in_menu' => true,
                        'sections'     => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow'     => 'Tedavi Yöntemleri',
                                'title'       => 'Hizmetlerimiz',
                                'subtitle'    => 'Uzman ekibimizin sunduğu tedavi alanları.',
                                'button_text' => 'Randevu Al',
                                'button_url'  => '/iletisim',
                            ]],
                            ['template_type' => 'features', 'content' => [
                                'title'       => 'Tedavi Alanları',
                                'description' => 'Tüm tedavi alanlarımızda uluslararası standartlar.',
                            ]],
                        ],
                    ],
                    [
                        'title'        => 'Hakkımızda',
                        'slug'         => 'hakkimizda',
                        'show_in_menu' => true,
                        'sections'     => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow'  => 'Hakkımızda',
                                'title'    => 'Biz kimiz?',
                                'subtitle' => '15 yıllık deneyim ile sağlığınız için buradayız.',
                            ]],
                            ['template_type' => 'rich-text', 'content' => [
                                'title'     => 'Hikayemiz',
                                'body_html' => '<p>2010 yılında kurulan kliniğimiz, alanında uzman doktor kadrosuyla hizmet vermektedir.</p>',
                            ]],
                        ],
                    ],
                    [
                        'title'        => 'İletişim',
                        'slug'         => 'iletisim',
                        'show_in_menu' => true,
                        'sections'     => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow' => 'Bize Ulaşın',
                                'title'   => 'İletişim',
                                'subtitle' => 'Randevu ve sorularınız için bizi arayın.',
                            ]],
                            ['template_type' => 'rich-text', 'content' => [
                                'title'     => 'Adres & Telefon',
                                'body_html' => '<p>📍 İstanbul, Türkiye<br>📞 +90 212 000 0000<br>✉ info@klinik.com</p>',
                            ]],
                        ],
                    ],
                ],
                'menus' => [
                    [
                        'name'     => 'Ana Menü',
                        'location' => 'header',
                        'items' => [
                            ['label' => 'Anasayfa',    'page_slug' => 'anasayfa'],
                            ['label' => 'Hizmetler',   'page_slug' => 'hizmetler'],
                            ['label' => 'Hakkımızda',  'page_slug' => 'hakkimizda'],
                            ['label' => 'İletişim',    'page_slug' => 'iletisim'],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ─── AVUKAT / HUKUK ─────────────────────────────────────────────────
    private function lawyer(): array
    {
        return [
            'slug'        => 'industry-lawyer-corporate',
            'name'        => 'Avukat — Kurumsal',
            'industry'    => 'lawyer',
            'summary'     => 'Hukuk büroları için ciddi ve güven veren kurumsal tasarım.',
            'description' => 'Hero + uzmanlık alanları + ekip + iletişim. Lacivert + altın aksanlı kurumsal palet.',
            'snapshot_json' => [
                'settings' => [
                    'site.title'            => 'Hukuk Bürosu',
                    'site.tagline'          => 'Güvenilir hukuki danışmanlık',
                    'design.color_primary'  => '#1e293b',
                    'design.color_secondary' => '#f1f5f9',
                    'design.color_accent'   => '#b45309',
                    'contact.phone'         => '+90 212 000 0000',
                    'contact.email'         => 'info@hukukburosu.com',
                ],
                'pages' => [
                    [
                        'title' => 'Anasayfa', 'slug' => 'anasayfa', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow'     => 'Hukuki Danışmanlık',
                                'title'       => 'Haklarınızı koruyoruz',
                                'subtitle'    => 'Ticaret, iş, aile ve ceza hukukunda uzman ekibimiz.',
                                'button_text' => 'Danışmanlık Talep Et',
                                'button_url'  => '/iletisim',
                            ]],
                            ['template_type' => 'features', 'content' => [
                                'title'       => 'Uzmanlık Alanları',
                                'description' => 'Ticaret hukuku, iş hukuku, aile hukuku, ceza hukuku, idare hukuku.',
                            ]],
                            ['template_type' => 'rich-text', 'content' => [
                                'title'     => 'Neden Bizi Seçmelisiniz?',
                                'body_html' => '<p>20 yılı aşkın deneyim. Her dava için titiz hazırlık. Şeffaf iletişim.</p>',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Uzmanlık Alanları', 'slug' => 'uzmanlik-alanlari', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow' => 'Hukuk Hizmetleri',
                                'title'   => 'Uzmanlık Alanlarımız',
                                'subtitle' => 'Tüm hukuk alanlarında profesyonel destek.',
                            ]],
                            ['template_type' => 'features', 'content' => [
                                'title'       => 'Hizmet Verdiğimiz Alanlar',
                                'description' => 'Ticaret, iş, ceza, aile, idare ve daha fazla.',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'İletişim', 'slug' => 'iletisim', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow' => 'Bize Ulaşın',
                                'title'   => 'İletişim',
                                'subtitle' => 'Hukuki danışmanlık talepleriniz için bize yazın.',
                            ]],
                            ['template_type' => 'rich-text', 'content' => [
                                'title'     => 'Ofis Bilgileri',
                                'body_html' => '<p>📍 İstanbul, Türkiye<br>📞 +90 212 000 0000<br>✉ info@hukukburosu.com</p>',
                            ]],
                        ],
                    ],
                ],
                'menus' => [['name' => 'Ana Menü', 'location' => 'header', 'items' => [
                    ['label' => 'Anasayfa',           'page_slug' => 'anasayfa'],
                    ['label' => 'Uzmanlık Alanları',  'page_slug' => 'uzmanlik-alanlari'],
                    ['label' => 'İletişim',           'page_slug' => 'iletisim'],
                ]]],
            ],
        ];
    }

    // ─── GÜZELLİK SALONU / BERBER ───────────────────────────────────────
    private function salon(): array
    {
        return [
            'slug'        => 'industry-salon-elegant',
            'name'        => 'Salon — Şık',
            'industry'    => 'salon',
            'summary'     => 'Güzellik salonu ve berberler için zarif bir tasarım.',
            'description' => 'Hero + hizmetler + galeri + randevu. Rose + zarif tipografi.',
            'snapshot_json' => [
                'settings' => [
                    'site.title'           => 'Salon',
                    'site.tagline'         => 'Güzelliğinizi yansıtın',
                    'design.color_primary' => '#be185d',
                    'design.color_secondary' => '#fdf2f8',
                ],
                'pages' => [
                    [
                        'title' => 'Anasayfa', 'slug' => 'anasayfa', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow'     => 'Profesyonel Bakım',
                                'title'       => 'Stilinizi yeniden keşfedin',
                                'subtitle'    => 'Saç, cilt ve makyaj alanında uzman ekibimiz.',
                                'button_text' => 'Randevu Al',
                                'button_url'  => '/iletisim',
                            ]],
                            ['template_type' => 'features', 'content' => [
                                'title'       => 'Hizmetlerimiz',
                                'description' => 'Saç kesimi, boya, manikür, pedikür, cilt bakımı, makyaj.',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'İletişim', 'slug' => 'iletisim', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow' => 'Randevu',
                                'title'   => 'Bize Ulaşın',
                            ]],
                            ['template_type' => 'rich-text', 'content' => [
                                'title'     => 'Konum',
                                'body_html' => '<p>📍 İstanbul<br>📞 +90 212 000 0000</p>',
                            ]],
                        ],
                    ],
                ],
                'menus' => [['name' => 'Ana Menü', 'location' => 'header', 'items' => [
                    ['label' => 'Anasayfa', 'page_slug' => 'anasayfa'],
                    ['label' => 'İletişim', 'page_slug' => 'iletisim'],
                ]]],
            ],
        ];
    }

    // ─── OTEL / KONAKLAMA ───────────────────────────────────────────────
    private function hotel(): array
    {
        return [
            'slug'        => 'industry-hotel-boutique',
            'name'        => 'Otel — Butik',
            'industry'    => 'hotel',
            'summary'     => 'Butik oteller ve pansiyonlar için sıcak bir tasarım.',
            'description' => 'Hero + odalar + olanaklar + rezervasyon CTA. Toprak tonu palet.',
            'snapshot_json' => [
                'settings' => [
                    'site.title'           => 'Butik Otel',
                    'site.tagline'         => 'Konforun ve şıklığın buluşma noktası',
                    'design.color_primary' => '#92400e',
                    'design.color_secondary' => '#fef3c7',
                ],
                'pages' => [
                    [
                        'title' => 'Anasayfa', 'slug' => 'anasayfa', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow'     => 'Konaklama',
                                'title'       => 'Unutulmaz bir tatil deneyimi',
                                'subtitle'    => 'Doğanın kalbinde, sıcak misafirperverlikle.',
                                'button_text' => 'Rezervasyon Yap',
                                'button_url'  => '/iletisim',
                            ]],
                            ['template_type' => 'features', 'content' => [
                                'title'       => 'Olanaklarımız',
                                'description' => 'Spa, restoran, havuz, plaj, konferans salonu, transfer.',
                            ]],
                            ['template_type' => 'rich-text', 'content' => [
                                'title'     => 'Bizi Tercih Etme Sebepleriniz',
                                'body_html' => '<p>Doğa manzaralı odalar. Şef yemekleri. 7/24 resepsiyon. Ücretsiz Wi-Fi.</p>',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Odalar', 'slug' => 'odalar', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow' => 'Konaklama Seçenekleri',
                                'title'   => 'Oda Tipleri',
                            ]],
                            ['template_type' => 'features', 'content' => [
                                'title'       => 'Oda Kategorileri',
                                'description' => 'Standart, Deluxe, Süit ve Aile odaları.',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'İletişim & Rezervasyon', 'slug' => 'iletisim', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow' => 'Rezervasyon',
                                'title'   => 'Bize Ulaşın',
                                'subtitle' => 'Rezervasyon için 7/24 hizmetinizdeyiz.',
                            ]],
                            ['template_type' => 'rich-text', 'content' => [
                                'title'     => 'İletişim Bilgileri',
                                'body_html' => '<p>📍 Antalya<br>📞 +90 242 000 0000<br>✉ rezervasyon@otel.com</p>',
                            ]],
                        ],
                    ],
                ],
                'menus' => [['name' => 'Ana Menü', 'location' => 'header', 'items' => [
                    ['label' => 'Anasayfa', 'page_slug' => 'anasayfa'],
                    ['label' => 'Odalar',   'page_slug' => 'odalar'],
                    ['label' => 'İletişim', 'page_slug' => 'iletisim'],
                ]]],
            ],
        ];
    }

    // ─── EMLAK ───────────────────────────────────────────────────────────
    private function realEstate(): array
    {
        return [
            'slug'        => 'industry-real-estate-bold',
            'name'        => 'Emlak — Cesur',
            'industry'    => 'real_estate',
            'summary'     => 'Emlak ofisleri için güvenilir ve modern bir başlangıç.',
            'description' => 'Hero + portföy + uzmanlık + iletişim. Kurumsal mavi + beyaz.',
            'snapshot_json' => [
                'settings' => [
                    'site.title'           => 'Emlak Ofisi',
                    'site.tagline'         => 'Hayalinizdeki ev için doğru adres',
                    'design.color_primary' => '#1d4ed8',
                    'design.color_secondary' => '#eff6ff',
                ],
                'pages' => [
                    [
                        'title' => 'Anasayfa', 'slug' => 'anasayfa', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow'     => 'Gayrimenkul',
                                'title'       => 'Doğru ev, doğru fiyat',
                                'subtitle'    => 'Satılık & kiralık portföyümüzde size uygun seçenekler.',
                                'button_text' => 'Portföyü İncele',
                                'button_url'  => '/portfoy',
                            ]],
                            ['template_type' => 'features', 'content' => [
                                'title'       => 'Hizmetlerimiz',
                                'description' => 'Alım, satım, kiralama, değerleme, yatırım danışmanlığı.',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Portföy', 'slug' => 'portfoy', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow' => 'Satılık & Kiralık',
                                'title'   => 'Portföyümüz',
                            ]],
                            ['template_type' => 'article-list', 'content' => [
                                'title'       => 'Öne Çıkan İlanlar',
                                'description' => 'Müşterilerimize özel ilanlarımız.',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'İletişim', 'slug' => 'iletisim', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow' => 'Bize Ulaşın',
                                'title'   => 'İletişim',
                            ]],
                            ['template_type' => 'rich-text', 'content' => [
                                'title'     => 'Ofis',
                                'body_html' => '<p>📍 İstanbul<br>📞 +90 212 000 0000</p>',
                            ]],
                        ],
                    ],
                ],
                'menus' => [['name' => 'Ana Menü', 'location' => 'header', 'items' => [
                    ['label' => 'Anasayfa', 'page_slug' => 'anasayfa'],
                    ['label' => 'Portföy',  'page_slug' => 'portfoy'],
                    ['label' => 'İletişim', 'page_slug' => 'iletisim'],
                ]]],
            ],
        ];
    }

    // ─── GENEL KURUMSAL ─────────────────────────────────────────────────
    private function corporate(): array
    {
        return [
            'slug'        => 'industry-corporate-clean',
            'name'        => 'Kurumsal — Sade',
            'industry'    => 'corporate',
            'summary'     => 'Her sektöre uygun, sade ve profesyonel kurumsal başlangıç.',
            'description' => 'Hero + hizmetler + hakkımızda + iletişim. Nötr palet, hızlı özelleştirilebilir.',
            'snapshot_json' => [
                'settings' => [
                    'site.title'           => 'Şirket Adı',
                    'site.tagline'         => 'Profesyonel çözümler, güvenilir hizmet',
                    'design.color_primary' => '#0f172a',
                    'design.color_secondary' => '#f8fafc',
                    'design.color_accent'   => '#4f46e5',
                ],
                'pages' => [
                    [
                        'title' => 'Anasayfa', 'slug' => 'anasayfa', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow'     => 'Hoş geldiniz',
                                'title'       => 'İşinizi büyütmek için yanınızdayız',
                                'subtitle'    => 'Profesyonel hizmetlerimizle hedeflerinize ulaşın.',
                                'button_text' => 'Daha Fazla Bilgi',
                                'button_url'  => '/hakkimizda',
                            ]],
                            ['template_type' => 'features', 'content' => [
                                'title'       => 'Hizmetlerimiz',
                                'description' => 'Müşterilerimize özel, uçtan uca çözümler.',
                            ]],
                            ['template_type' => 'rich-text', 'content' => [
                                'title'     => 'Vizyonumuz',
                                'body_html' => '<p>Sürdürülebilir, müşteri odaklı ve yenilikçi bir yaklaşımla hizmet veriyoruz.</p>',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Hakkımızda', 'slug' => 'hakkimizda', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow' => 'Hakkımızda',
                                'title'   => 'Kim olduğumuzu öğrenin',
                                'subtitle' => 'Yıllara dayanan deneyim ve uzmanlık.',
                            ]],
                            ['template_type' => 'rich-text', 'content' => [
                                'title'     => 'Hikayemiz',
                                'body_html' => '<p>Şirketimiz kuruluşundan bu yana sektörünün öncülerinden biri olmaya devam etmektedir.</p>',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Hizmetler', 'slug' => 'hizmetler', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow' => 'Ne yapıyoruz?',
                                'title'   => 'Hizmetlerimiz',
                            ]],
                            ['template_type' => 'features', 'content' => [
                                'title'       => 'Hizmet Alanları',
                                'description' => 'Danışmanlık, uygulama, eğitim, destek.',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'İletişim', 'slug' => 'iletisim', 'show_in_menu' => true,
                        'sections' => [
                            ['template_type' => 'hero', 'content' => [
                                'eyebrow' => 'Bize Ulaşın',
                                'title'   => 'İletişim',
                                'subtitle' => 'Sorularınız ve teklifleriniz için bize yazın.',
                            ]],
                            ['template_type' => 'rich-text', 'content' => [
                                'title'     => 'İletişim Bilgileri',
                                'body_html' => '<p>📍 İstanbul, Türkiye<br>📞 +90 212 000 0000<br>✉ info@sirket.com</p>',
                            ]],
                        ],
                    ],
                ],
                'menus' => [['name' => 'Ana Menü', 'location' => 'header', 'items' => [
                    ['label' => 'Anasayfa',   'page_slug' => 'anasayfa'],
                    ['label' => 'Hakkımızda', 'page_slug' => 'hakkimizda'],
                    ['label' => 'Hizmetler',  'page_slug' => 'hizmetler'],
                    ['label' => 'İletişim',   'page_slug' => 'iletisim'],
                ]]],
            ],
        ];
    }
}
