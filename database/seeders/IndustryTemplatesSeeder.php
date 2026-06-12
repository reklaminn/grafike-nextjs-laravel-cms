<?php

namespace Database\Seeders;

use App\Models\SiteTemplate;
use App\Models\Theme;
use Illuminate\Database\Seeder;

/**
 * Industry-specific SiteTemplate library (FAZ 4.3).
 *
 * Each entry produces one row in central.site_templates with:
 *   - industry      : sector code (clinic, lawyer, salon, hotel, tourism,
 *                     real_estate, corporate)
 *   - snapshot_json : pages + menus + settings to seed into a tenant
 *
 * Block sections reference GLOBAL SectionTemplates by `template_type` +
 * `template_variation`.  The generic industries use the neutral `base-*`
 * family (GlobalBaseCatalogSeeder); tourism uses the `turizm-*` family
 * (GlobalTourismCatalogSeeder).  At apply-time, IndustryTemplateApplier
 * resolves these to real (tenant_id IS NULL) section_template_id's and merges
 * `content` over default_content_json.
 *
 * Run (after the block seeders):
 *   php artisan db:seed --class=GlobalBaseCatalogSeeder
 *   php artisan db:seed --class=GlobalTourismCatalogSeeder
 *   php artisan db:seed --class=IndustryTemplatesSeeder
 */
class IndustryTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        // Bağlanacak global temalar (block seeder'ları önce çalışmış olmalı).
        // Bulunamazsa theme_id null kalır (uygulama yine bloklarla render olur).
        $baseThemeId   = Theme::where('slug', 'genel-temel')->value('id');
        $turizmThemeId = Theme::where('slug', 'turizm-modern')->value('id');

        foreach ($this->templates() as $i => $tpl) {
            SiteTemplate::updateOrCreate(
                ['slug' => $tpl['slug']],
                [
                    'name'          => $tpl['name'],
                    'industry'      => $tpl['industry'],
                    // Şablon uygulanınca tenant->theme_id buna set edilir →
                    // tema token'ları (renk/font) ve CSS/JS yüklenir.
                    'theme_id'      => $tpl['industry'] === 'tourism' ? $turizmThemeId : $baseThemeId,
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
            $this->tourism(),
            $this->realEstate(),
            $this->corporate(),
        ];
    }

    // Standart header menüsü üretici (page_slug listesinden).
    private function menu(array $items): array
    {
        return [['name' => 'Ana Menü', 'location' => 'header', 'items' => $items]];
    }

    private function contactSection(string $body): array
    {
        return ['template_type' => 'rich-text', 'template_variation' => 'base-content', 'content' => [
            'title' => 'İletişim Bilgileri', 'body_html' => $body,
        ]];
    }

    // ─── KLİNİK / SAĞLIK ────────────────────────────────────────────────
    private function clinic(): array
    {
        return [
            'slug'        => 'industry-clinic-modern',
            'name'        => 'Klinik — Modern',
            'industry'    => 'clinic',
            'summary'     => 'Diş, estetik, tıp merkezleri için modern bir başlangıç.',
            'description' => 'Hero + hizmetler + güven + yorumlar + iletişim. Mavi pastel, güven veren tasarım.',
            'snapshot_json' => [
                'settings' => [
                    'site.title' => 'Klinik', 'site.tagline' => 'Sağlığınız için modern çözümler',
                    'design.color_primary' => '#2563eb', 'design.color_secondary' => '#eff6ff', 'design.color_accent' => '#0f172a',
                    'contact.phone' => '+90 212 000 0000', 'contact.email' => 'info@klinik.com', 'contact.address' => 'İstanbul, Türkiye',
                ],
                'pages' => [
                    ['title' => 'Anasayfa', 'slug' => 'anasayfa', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Modern Sağlık', 'title' => 'Sağlığınız için güvenilir adres',
                            'subtitle' => 'Uzman ekibimiz ve modern donanımımızla yanınızdayız.',
                            'button_text' => 'Randevu Al', 'button_url' => '/iletisim',
                        ]],
                        ['template_type' => 'features', 'template_variation' => 'base-features', 'content' => [
                            'title' => 'Hizmet Alanlarımız', 'description' => 'Alanında uzman kadromuzla sunduğumuz başlıca tedaviler.',
                            'item1_icon' => '🦷', 'item1_title' => 'İmplant', 'item1_text' => 'Eksik dişler için kalıcı ve doğal çözümler.',
                            'item2_icon' => '✨', 'item2_title' => 'Gülüş Tasarımı', 'item2_text' => 'Estetik ve fonksiyonel gülüş planlaması.',
                            'item3_icon' => '🪥', 'item3_title' => 'Ortodonti', 'item3_text' => 'Şeffaf plak ve tel tedavileri.',
                        ]],
                        ['template_type' => 'rich-text', 'template_variation' => 'base-content', 'content' => [
                            'title' => 'Neden Bizi Tercih Etmelisiniz?',
                            'body_html' => '<p>15 yıllık deneyim, modern ekipman ve yüksek hijyen standardı. Her hastamıza özel bakım planı sunuyoruz.</p>',
                        ]],
                        ['template_type' => 'testimonials', 'template_variation' => 'base-testimonials', 'content' => [
                            'title' => 'Hastalarımız Ne Diyor?',
                            'quote1' => 'Çok ilgili ve güler yüzlü bir ekip, tedavim harika geçti.', 'author1' => 'Ayşe K.',
                            'quote2' => 'Modern bir klinik, kendimi güvende hissettim.', 'author2' => 'Murat D.',
                            'quote3' => 'Gülüş tasarımımdan çok memnunum.', 'author3' => 'Selin Y.',
                        ]],
                        ['template_type' => 'cta', 'template_variation' => 'base-cta', 'content' => [
                            'title' => 'Sağlıklı bir gülüş için ilk adımı atın', 'subtitle' => 'Ücretsiz muayene randevunuzu hemen oluşturun.',
                            'button_text' => 'Randevu Al', 'button_url' => '/iletisim',
                        ]],
                    ]],
                    ['title' => 'Hizmetlerimiz', 'slug' => 'hizmetler', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Tedavi Yöntemleri', 'title' => 'Hizmetlerimiz', 'subtitle' => 'Uluslararası standartlarda tedavi alanları.',
                            'button_text' => 'Randevu Al', 'button_url' => '/iletisim',
                        ]],
                        ['template_type' => 'features', 'template_variation' => 'base-features', 'content' => [
                            'title' => 'Tedavi Alanları', 'description' => 'İhtiyacınıza uygun çözümler.',
                            'item1_icon' => '🦷', 'item1_title' => 'Diş Beyazlatma', 'item1_text' => 'Profesyonel beyazlatma uygulamaları.',
                            'item2_icon' => '🩺', 'item2_title' => 'Diş Eti Tedavisi', 'item2_text' => 'Sağlıklı diş etleri için tedaviler.',
                            'item3_icon' => '😁', 'item3_title' => 'Protez', 'item3_text' => 'Sabit ve hareketli protez seçenekleri.',
                        ]],
                    ]],
                    ['title' => 'Hakkımızda', 'slug' => 'hakkimizda', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Hakkımızda', 'title' => 'Biz kimiz?', 'subtitle' => '15 yıllık deneyim ile sağlığınız için buradayız.',
                            'button_text' => 'İletişim', 'button_url' => '/iletisim',
                        ]],
                        ['template_type' => 'rich-text', 'template_variation' => 'base-content', 'content' => [
                            'title' => 'Hikayemiz',
                            'body_html' => '<p>2010 yılında kurulan kliniğimiz, alanında uzman doktor kadrosuyla hizmet vermektedir. Hasta memnuniyeti her zaman önceliğimizdir.</p>',
                        ]],
                    ]],
                    ['title' => 'İletişim', 'slug' => 'iletisim', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Bize Ulaşın', 'title' => 'İletişim', 'subtitle' => 'Randevu ve sorularınız için bizi arayın.',
                            'button_text' => 'Ara', 'button_url' => 'tel:+902120000000',
                        ]],
                        $this->contactSection('<p>📍 İstanbul, Türkiye<br>📞 +90 212 000 0000<br>✉ info@klinik.com</p>'),
                    ]],
                ],
                'menus' => $this->menu([
                    ['label' => 'Anasayfa', 'page_slug' => 'anasayfa'],
                    ['label' => 'Hizmetler', 'page_slug' => 'hizmetler'],
                    ['label' => 'Hakkımızda', 'page_slug' => 'hakkimizda'],
                    ['label' => 'İletişim', 'page_slug' => 'iletisim'],
                ]),
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
            'description' => 'Hero + uzmanlık alanları + güven + iletişim. Lacivert + altın aksanlı palet.',
            'snapshot_json' => [
                'settings' => [
                    'site.title' => 'Hukuk Bürosu', 'site.tagline' => 'Güvenilir hukuki danışmanlık',
                    'design.color_primary' => '#1e293b', 'design.color_secondary' => '#f1f5f9', 'design.color_accent' => '#b45309',
                    'contact.phone' => '+90 212 000 0000', 'contact.email' => 'info@hukukburosu.com',
                ],
                'pages' => [
                    ['title' => 'Anasayfa', 'slug' => 'anasayfa', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Hukuki Danışmanlık', 'title' => 'Haklarınızı koruyoruz',
                            'subtitle' => 'Ticaret, iş, aile ve ceza hukukunda uzman ekibimiz.',
                            'button_text' => 'Danışmanlık Talep Et', 'button_url' => '/iletisim',
                        ]],
                        ['template_type' => 'features', 'template_variation' => 'base-features', 'content' => [
                            'title' => 'Uzmanlık Alanları', 'description' => 'Geniş hukuk yelpazesinde profesyonel destek.',
                            'item1_icon' => '⚖️', 'item1_title' => 'Ticaret Hukuku', 'item1_text' => 'Şirketler, sözleşmeler ve ticari uyuşmazlıklar.',
                            'item2_icon' => '👨‍👩‍👧', 'item2_title' => 'Aile Hukuku', 'item2_text' => 'Boşanma, velayet ve nafaka davaları.',
                            'item3_icon' => '🛡️', 'item3_title' => 'Ceza Hukuku', 'item3_text' => 'Soruşturma ve kovuşturma süreçlerinde savunma.',
                        ]],
                        ['template_type' => 'rich-text', 'template_variation' => 'base-content', 'content' => [
                            'title' => 'Neden Bizi Seçmelisiniz?',
                            'body_html' => '<p>20 yılı aşkın deneyim. Her dava için titiz hazırlık ve şeffaf iletişim ilkesiyle çalışıyoruz.</p>',
                        ]],
                        ['template_type' => 'cta', 'template_variation' => 'base-cta', 'content' => [
                            'title' => 'Hukuki sürecinizde yalnız değilsiniz', 'subtitle' => 'İlk görüşme için bizimle iletişime geçin.',
                            'button_text' => 'Randevu Al', 'button_url' => '/iletisim',
                        ]],
                    ]],
                    ['title' => 'Uzmanlık Alanları', 'slug' => 'uzmanlik-alanlari', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Hukuk Hizmetleri', 'title' => 'Uzmanlık Alanlarımız', 'subtitle' => 'Tüm hukuk alanlarında profesyonel destek.',
                            'button_text' => 'İletişim', 'button_url' => '/iletisim',
                        ]],
                        ['template_type' => 'features', 'template_variation' => 'base-features', 'content' => [
                            'title' => 'Hizmet Verdiğimiz Alanlar', 'description' => 'Deneyimli kadromuzla yanınızdayız.',
                            'item1_icon' => '🏢', 'item1_title' => 'İş Hukuku', 'item1_text' => 'İşçi-işveren uyuşmazlıkları ve danışmanlık.',
                            'item2_icon' => '📜', 'item2_title' => 'İdare Hukuku', 'item2_text' => 'İdari işlem ve dava süreçleri.',
                            'item3_icon' => '🏠', 'item3_title' => 'Gayrimenkul', 'item3_text' => 'Tapu, kira ve emlak uyuşmazlıkları.',
                        ]],
                    ]],
                    ['title' => 'İletişim', 'slug' => 'iletisim', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Bize Ulaşın', 'title' => 'İletişim', 'subtitle' => 'Hukuki danışmanlık talepleriniz için bize yazın.',
                            'button_text' => 'E-posta Gönder', 'button_url' => 'mailto:info@hukukburosu.com',
                        ]],
                        $this->contactSection('<p>📍 İstanbul, Türkiye<br>📞 +90 212 000 0000<br>✉ info@hukukburosu.com</p>'),
                    ]],
                ],
                'menus' => $this->menu([
                    ['label' => 'Anasayfa', 'page_slug' => 'anasayfa'],
                    ['label' => 'Uzmanlık Alanları', 'page_slug' => 'uzmanlik-alanlari'],
                    ['label' => 'İletişim', 'page_slug' => 'iletisim'],
                ]),
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
            'description' => 'Hero + hizmetler + galeri + randevu CTA. Rose tonları, zarif tipografi.',
            'snapshot_json' => [
                'settings' => [
                    'site.title' => 'Salon', 'site.tagline' => 'Güzelliğinizi yansıtın',
                    'design.color_primary' => '#be185d', 'design.color_secondary' => '#fdf2f8', 'design.color_accent' => '#831843',
                    'contact.phone' => '+90 212 000 0000', 'contact.email' => 'info@salon.com',
                ],
                'pages' => [
                    ['title' => 'Anasayfa', 'slug' => 'anasayfa', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Profesyonel Bakım', 'title' => 'Stilinizi yeniden keşfedin',
                            'subtitle' => 'Saç, cilt ve makyaj alanında uzman ekibimiz.',
                            'button_text' => 'Randevu Al', 'button_url' => '/iletisim',
                        ]],
                        ['template_type' => 'features', 'template_variation' => 'base-features', 'content' => [
                            'title' => 'Hizmetlerimiz', 'description' => 'Size özel bakım çözümleri.',
                            'item1_icon' => '💇‍♀️', 'item1_title' => 'Saç Tasarımı', 'item1_text' => 'Kesim, boya ve bakım uygulamaları.',
                            'item2_icon' => '💅', 'item2_title' => 'Manikür & Pedikür', 'item2_text' => 'El ve ayak bakımının keyfini çıkarın.',
                            'item3_icon' => '💄', 'item3_title' => 'Makyaj', 'item3_text' => 'Özel gün ve günlük makyaj.',
                        ]],
                        ['template_type' => 'gallery', 'template_variation' => 'base-gallery', 'content' => [
                            'title' => 'Çalışmalarımızdan',
                        ]],
                        ['template_type' => 'cta', 'template_variation' => 'base-cta', 'content' => [
                            'title' => 'Kendinize iyi bakın', 'subtitle' => 'Hemen randevu alın, fark yaratın.',
                            'button_text' => 'Randevu Al', 'button_url' => '/iletisim',
                        ]],
                    ]],
                    ['title' => 'İletişim', 'slug' => 'iletisim', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Randevu', 'title' => 'Bize Ulaşın', 'subtitle' => 'Sizi salonumuzda ağırlamaktan mutluluk duyarız.',
                            'button_text' => 'Ara', 'button_url' => 'tel:+902120000000',
                        ]],
                        $this->contactSection('<p>📍 İstanbul<br>📞 +90 212 000 0000<br>✉ info@salon.com</p>'),
                    ]],
                ],
                'menus' => $this->menu([
                    ['label' => 'Anasayfa', 'page_slug' => 'anasayfa'],
                    ['label' => 'İletişim', 'page_slug' => 'iletisim'],
                ]),
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
            'description' => 'Hero + olanaklar + odalar + galeri + rezervasyon CTA. Toprak tonu palet.',
            'snapshot_json' => [
                'settings' => [
                    'site.title' => 'Butik Otel', 'site.tagline' => 'Konforun ve şıklığın buluşma noktası',
                    'design.color_primary' => '#92400e', 'design.color_secondary' => '#fef3c7', 'design.color_accent' => '#451a03',
                    'contact.phone' => '+90 242 000 0000', 'contact.email' => 'rezervasyon@otel.com', 'contact.address' => 'Antalya, Türkiye',
                ],
                'pages' => [
                    ['title' => 'Anasayfa', 'slug' => 'anasayfa', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Konaklama', 'title' => 'Unutulmaz bir tatil deneyimi',
                            'subtitle' => 'Doğanın kalbinde, sıcak misafirperverlikle.',
                            'button_text' => 'Rezervasyon Yap', 'button_url' => '/iletisim',
                        ]],
                        ['template_type' => 'features', 'template_variation' => 'base-features', 'content' => [
                            'title' => 'Olanaklarımız', 'description' => 'Konforlu bir konaklama için her şey.',
                            'item1_icon' => '🏊', 'item1_title' => 'Havuz & Plaj', 'item1_text' => 'Açık havuz ve özel plaj erişimi.',
                            'item2_icon' => '🍽️', 'item2_title' => 'Restoran', 'item2_text' => 'Şef yemekleri ve zengin kahvaltı.',
                            'item3_icon' => '💆', 'item3_title' => 'Spa & Wellness', 'item3_text' => 'Masaj, sauna ve bakım hizmetleri.',
                        ]],
                        ['template_type' => 'gallery', 'template_variation' => 'base-gallery', 'content' => [
                            'title' => 'Otelden Kareler',
                        ]],
                        ['template_type' => 'cta', 'template_variation' => 'base-cta', 'content' => [
                            'title' => 'Tatilinizi bugün planlayın', 'subtitle' => '7/24 rezervasyon hattımız hizmetinizde.',
                            'button_text' => 'Rezervasyon Yap', 'button_url' => '/iletisim',
                        ]],
                    ]],
                    ['title' => 'Odalar', 'slug' => 'odalar', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Konaklama Seçenekleri', 'title' => 'Oda Tipleri', 'subtitle' => 'Her ihtiyaca uygun konfor.',
                            'button_text' => 'Rezervasyon', 'button_url' => '/iletisim',
                        ]],
                        ['template_type' => 'features', 'template_variation' => 'base-features', 'content' => [
                            'title' => 'Oda Kategorileri', 'description' => 'Konforlu ve şık odalarımız.',
                            'item1_icon' => '🛏️', 'item1_title' => 'Standart Oda', 'item1_text' => 'Konforlu ve ekonomik seçenek.',
                            'item2_icon' => '🌅', 'item2_title' => 'Deluxe Oda', 'item2_text' => 'Deniz manzaralı geniş odalar.',
                            'item3_icon' => '👑', 'item3_title' => 'Süit', 'item3_text' => 'Ayrı oturma alanlı lüks süitler.',
                        ]],
                    ]],
                    ['title' => 'İletişim & Rezervasyon', 'slug' => 'iletisim', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Rezervasyon', 'title' => 'Bize Ulaşın', 'subtitle' => 'Rezervasyon için 7/24 hizmetinizdeyiz.',
                            'button_text' => 'Ara', 'button_url' => 'tel:+902420000000',
                        ]],
                        $this->contactSection('<p>📍 Antalya<br>📞 +90 242 000 0000<br>✉ rezervasyon@otel.com</p>'),
                    ]],
                ],
                'menus' => $this->menu([
                    ['label' => 'Anasayfa', 'page_slug' => 'anasayfa'],
                    ['label' => 'Odalar', 'page_slug' => 'odalar'],
                    ['label' => 'İletişim', 'page_slug' => 'iletisim'],
                ]),
            ],
        ];
    }

    // ─── TURİZM / TUR ACENTESİ ──────────────────────────────────────────
    // Global turizm bloklarını (GlobalTourismCatalogSeeder) referans alır:
    //   hero/turizm-hero, features/turizm-services, rich-text/turizm-content,
    //   article-list/turizm-tours, cta/turizm-cta, gallery/turizm-gallery.
    private function tourism(): array
    {
        return [
            'slug'        => 'industry-tourism-explorer',
            'name'        => 'Turizm — Kaşif',
            'industry'    => 'tourism',
            'summary'     => 'Tur acenteleri ve seyahat firmaları için kapsamlı bir başlangıç.',
            'description' => 'Hero + hizmetler + popüler turlar + galeri + rezervasyon CTA. Deniz tonlu, enerjik bir tasarım. Tours modülüyle birlikte kullanılması önerilir.',
            'snapshot_json' => [
                'settings' => [
                    'site.title'             => 'Tur Acentesi',
                    'site.tagline'           => 'Keşfet, yaşa, hatırla',
                    'site.footer_text'       => '© 2026 Tur Acentesi — TÜRSAB üyesidir.',
                    'design.color_primary'   => '#0ea5e9',
                    'design.color_secondary' => '#f0f9ff',
                    'design.color_accent'    => '#0f766e',
                    'contact.phone'          => '+90 242 000 0000',
                    'contact.email'          => 'info@turacentesi.com',
                    'contact.address'        => 'Antalya, Türkiye',
                    'contact.whatsapp_number' => '+90 532 000 0000',
                    'social.instagram'       => 'https://instagram.com/turacentesi',
                ],
                'pages' => [
                    ['title' => 'Anasayfa', 'slug' => 'anasayfa', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'turizm-hero', 'content' => [
                            'eyebrow' => 'Keşfetmeye Hazır mısın?', 'title' => 'Hayalindeki tatil bir tık uzağında',
                            'subtitle' => 'Yurt içi ve yurt dışı tur paketleri, günübirlik geziler ve özel rotalarla unutulmaz anılar biriktir.',
                            'button_text' => 'Turları İncele', 'button_url' => '/turlar',
                        ]],
                        ['template_type' => 'features', 'template_variation' => 'turizm-services', 'content' => [
                            'title' => 'Neden Bizi Tercih Etmelisiniz?', 'subtitle' => 'Profesyonel rehberlik, güvenli ulaşım ve özenle seçilmiş rotalarla yanınızdayız.',
                            'item1_icon' => '🌍', 'item1_title' => 'Uzman Rehberler', 'item1_text' => 'Yerel kültürü tanıyan deneyimli profesyonel rehber kadrosu.',
                            'item2_icon' => '🚌', 'item2_title' => 'Konforlu Ulaşım', 'item2_text' => 'Klimalı, güvenli araç filomuzla kapıdan kapıya hizmet.',
                            'item3_icon' => '🛡️', 'item3_title' => 'Sigorta Güvencesi', 'item3_text' => 'Tüm turlarımız seyahat sigortası kapsamındadır.',
                        ]],
                        ['template_type' => 'article-list', 'template_variation' => 'turizm-tours', 'content' => [
                            'title' => 'Popüler Turlar', 'description' => 'En çok tercih edilen rotalarımızdan bir seçki.',
                        ]],
                        ['template_type' => 'rich-text', 'template_variation' => 'turizm-content', 'content' => [
                            'title' => 'Biz Kimiz?',
                            'body_html' => '<p>20 yılı aşkın tecrübemizle binlerce misafirimizi hayalindeki destinasyonlara ulaştırdık. TÜRSAB üyesiyiz ve tüm turlarımız sigorta güvencesi altındadır.</p>',
                        ]],
                        ['template_type' => 'cta', 'template_variation' => 'turizm-cta', 'content' => [
                            'title' => 'Bir sonraki maceran için hazır mısın?', 'subtitle' => 'Uzman ekibimiz sana en uygun tur paketini bulmak için bekliyor.',
                            'button_text' => 'Hemen Rezervasyon Yap', 'button_url' => '/iletisim',
                        ]],
                    ]],
                    ['title' => 'Turlar', 'slug' => 'turlar', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'turizm-hero', 'content' => [
                            'eyebrow' => 'Tüm Rotalar', 'title' => 'Tur Paketlerimiz', 'subtitle' => 'Kültür turlarından doğa gezilerine, her zevke uygun seçenekler.',
                            'button_text' => 'Bize Ulaş', 'button_url' => '/iletisim',
                        ]],
                        ['template_type' => 'article-list', 'template_variation' => 'turizm-tours', 'content' => [
                            'title' => 'Öne Çıkan Turlar', 'description' => 'Sezonun en sevilen tur paketleri. Detaylar için bizimle iletişime geçin.',
                        ]],
                        ['template_type' => 'cta', 'template_variation' => 'turizm-cta', 'content' => [
                            'title' => 'Grup indirimlerimizden faydalanın', 'subtitle' => '10 kişi ve üzeri gruplara özel fiyatlar.',
                            'button_text' => 'Teklif Al', 'button_url' => '/iletisim',
                        ]],
                    ]],
                    ['title' => 'Hakkımızda', 'slug' => 'hakkimizda', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'turizm-hero', 'content' => [
                            'eyebrow' => 'Hakkımızda', 'title' => 'Seyahat tutkumuzu sizinle paylaşıyoruz', 'subtitle' => '20 yıllık deneyim, binlerce mutlu misafir.',
                            'button_text' => 'Turları Gör', 'button_url' => '/turlar',
                        ]],
                        ['template_type' => 'rich-text', 'template_variation' => 'turizm-content', 'content' => [
                            'title' => 'Hikayemiz',
                            'body_html' => '<p>2005 yılında küçük bir ofiste başlayan yolculuğumuz, bugün Türkiye\'nin önde gelen tur acentelerinden biri olmamızla sürüyor. Misyonumuz; her bütçeye uygun, güvenli ve keyifli seyahat deneyimleri sunmak.</p><p>TÜRSAB üyesiyiz; tüm turlarımız sigortalı ve lisanslı rehberler eşliğindedir.</p>',
                        ]],
                        ['template_type' => 'features', 'template_variation' => 'turizm-services', 'content' => [
                            'title' => 'Değerlerimiz', 'subtitle' => 'Her seyahatte arkanızdayız.',
                            'item1_icon' => '⭐', 'item1_title' => 'Müşteri Memnuniyeti', 'item1_text' => 'Misafir memnuniyeti her zaman önceliğimiz.',
                            'item2_icon' => '🤝', 'item2_title' => 'Güven', 'item2_text' => 'Şeffaf fiyat, dürüst iletişim.',
                            'item3_icon' => '🌱', 'item3_title' => 'Sürdürülebilirlik', 'item3_text' => 'Doğaya ve yerel kültüre saygılı turizm.',
                        ]],
                    ]],
                    ['title' => 'Galeri', 'slug' => 'galeri', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'turizm-hero', 'content' => [
                            'eyebrow' => 'Anılar', 'title' => 'Gezi Albümü', 'subtitle' => 'Misafirlerimizle paylaştığımız unutulmaz kareler.',
                            'button_text' => 'Sen de Katıl', 'button_url' => '/turlar',
                        ]],
                        ['template_type' => 'gallery', 'template_variation' => 'turizm-gallery', 'content' => [
                            'title' => 'Turlardan Kareler',
                        ]],
                    ]],
                    ['title' => 'İletişim', 'slug' => 'iletisim', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'turizm-hero', 'content' => [
                            'eyebrow' => 'Bize Ulaşın', 'title' => 'Rezervasyon & İletişim', 'subtitle' => 'Sorularınız ve rezervasyon talepleriniz için 7/24 buradayız.',
                            'button_text' => 'WhatsApp\'tan Yaz', 'button_url' => 'https://wa.me/905320000000',
                        ]],
                        ['template_type' => 'rich-text', 'template_variation' => 'turizm-content', 'content' => [
                            'title' => 'İletişim Bilgileri',
                            'body_html' => '<p>📍 Antalya, Türkiye<br>📞 +90 242 000 0000<br>📱 +90 532 000 0000 (WhatsApp)<br>✉ info@turacentesi.com</p><p>Çalışma saatleri: Hafta içi 09:00–19:00, Cumartesi 10:00–16:00</p>',
                        ]],
                    ]],
                ],
                'menus' => $this->menu([
                    ['label' => 'Anasayfa', 'page_slug' => 'anasayfa'],
                    ['label' => 'Turlar', 'page_slug' => 'turlar'],
                    ['label' => 'Hakkımızda', 'page_slug' => 'hakkimizda'],
                    ['label' => 'Galeri', 'page_slug' => 'galeri'],
                    ['label' => 'İletişim', 'page_slug' => 'iletisim'],
                ]),
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
            'description' => 'Hero + hizmetler + portföy + iletişim. Kurumsal mavi + beyaz.',
            'snapshot_json' => [
                'settings' => [
                    'site.title' => 'Emlak Ofisi', 'site.tagline' => 'Hayalinizdeki ev için doğru adres',
                    'design.color_primary' => '#1d4ed8', 'design.color_secondary' => '#eff6ff', 'design.color_accent' => '#1e3a8a',
                    'contact.phone' => '+90 212 000 0000', 'contact.email' => 'info@emlak.com',
                ],
                'pages' => [
                    ['title' => 'Anasayfa', 'slug' => 'anasayfa', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Gayrimenkul', 'title' => 'Doğru ev, doğru fiyat',
                            'subtitle' => 'Satılık & kiralık portföyümüzde size uygun seçenekler.',
                            'button_text' => 'Portföyü İncele', 'button_url' => '/portfoy',
                        ]],
                        ['template_type' => 'features', 'template_variation' => 'base-features', 'content' => [
                            'title' => 'Hizmetlerimiz', 'description' => 'Gayrimenkulde uçtan uca destek.',
                            'item1_icon' => '🏠', 'item1_title' => 'Alım & Satım', 'item1_text' => 'Doğru fiyatla güvenli alım-satım.',
                            'item2_icon' => '🔑', 'item2_title' => 'Kiralama', 'item2_text' => 'Konut ve iş yeri kiralama hizmetleri.',
                            'item3_icon' => '📊', 'item3_title' => 'Değerleme', 'item3_text' => 'Profesyonel ekspertiz ve yatırım danışmanlığı.',
                        ]],
                        ['template_type' => 'cta', 'template_variation' => 'base-cta', 'content' => [
                            'title' => 'Gayrimenkulünüzü değerinde satalım', 'subtitle' => 'Ücretsiz değerleme için hemen arayın.',
                            'button_text' => 'Ücretsiz Değerleme', 'button_url' => '/iletisim',
                        ]],
                    ]],
                    ['title' => 'Portföy', 'slug' => 'portfoy', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Satılık & Kiralık', 'title' => 'Portföyümüz', 'subtitle' => 'Sizin için seçtiğimiz öne çıkan ilanlar.',
                            'button_text' => 'İletişim', 'button_url' => '/iletisim',
                        ]],
                        ['template_type' => 'article-list', 'template_variation' => 'base-cards', 'content' => [
                            'title' => 'Öne Çıkan İlanlar', 'description' => 'Güncel portföyümüzden seçmeler.',
                            'items_html' => '<article style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 6px 24px rgba(15,23,42,.08);">'
                                .'<img src="https://picsum.photos/seed/ev1/600/360" alt="" style="width:100%;height:200px;object-fit:cover;display:block;">'
                                .'<div style="padding:20px;"><h3 style="margin:0 0 8px;font-size:19px;">3+1 Daire — Merkez</h3><p style="margin:0 0 12px;color:#64748b;">120 m² · Satılık</p><span style="font-weight:700;color:var(--color-primary,#1d4ed8);">İletişime geçin</span></div></article>'
                                .'<article style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 6px 24px rgba(15,23,42,.08);">'
                                .'<img src="https://picsum.photos/seed/ev2/600/360" alt="" style="width:100%;height:200px;object-fit:cover;display:block;">'
                                .'<div style="padding:20px;"><h3 style="margin:0 0 8px;font-size:19px;">2+1 Daire — Deniz Manzaralı</h3><p style="margin:0 0 12px;color:#64748b;">95 m² · Kiralık</p><span style="font-weight:700;color:var(--color-primary,#1d4ed8);">İletişime geçin</span></div></article>'
                                .'<article style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 6px 24px rgba(15,23,42,.08);">'
                                .'<img src="https://picsum.photos/seed/ev3/600/360" alt="" style="width:100%;height:200px;object-fit:cover;display:block;">'
                                .'<div style="padding:20px;"><h3 style="margin:0 0 8px;font-size:19px;">Villa — Bahçeli</h3><p style="margin:0 0 12px;color:#64748b;">260 m² · Satılık</p><span style="font-weight:700;color:var(--color-primary,#1d4ed8);">İletişime geçin</span></div></article>',
                        ]],
                    ]],
                    ['title' => 'İletişim', 'slug' => 'iletisim', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Bize Ulaşın', 'title' => 'İletişim', 'subtitle' => 'İlanlarımız ve danışmanlık için bize yazın.',
                            'button_text' => 'Ara', 'button_url' => 'tel:+902120000000',
                        ]],
                        $this->contactSection('<p>📍 İstanbul<br>📞 +90 212 000 0000<br>✉ info@emlak.com</p>'),
                    ]],
                ],
                'menus' => $this->menu([
                    ['label' => 'Anasayfa', 'page_slug' => 'anasayfa'],
                    ['label' => 'Portföy', 'page_slug' => 'portfoy'],
                    ['label' => 'İletişim', 'page_slug' => 'iletisim'],
                ]),
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
            'description' => 'Hero + hizmetler + hakkımızda + yorumlar + iletişim. Nötr palet, hızlı özelleştirilebilir.',
            'snapshot_json' => [
                'settings' => [
                    'site.title' => 'Şirket Adı', 'site.tagline' => 'Profesyonel çözümler, güvenilir hizmet',
                    'design.color_primary' => '#4f46e5', 'design.color_secondary' => '#f8fafc', 'design.color_accent' => '#0f172a',
                    'contact.phone' => '+90 212 000 0000', 'contact.email' => 'info@sirket.com',
                ],
                'pages' => [
                    ['title' => 'Anasayfa', 'slug' => 'anasayfa', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Hoş geldiniz', 'title' => 'İşinizi büyütmek için yanınızdayız',
                            'subtitle' => 'Profesyonel hizmetlerimizle hedeflerinize ulaşın.',
                            'button_text' => 'Daha Fazla Bilgi', 'button_url' => '/hakkimizda',
                        ]],
                        ['template_type' => 'features', 'template_variation' => 'base-features', 'content' => [
                            'title' => 'Hizmetlerimiz', 'description' => 'Müşterilerimize özel, uçtan uca çözümler.',
                            'item1_icon' => '💡', 'item1_title' => 'Danışmanlık', 'item1_text' => 'Stratejik yol haritası ve uzman görüşü.',
                            'item2_icon' => '🛠️', 'item2_title' => 'Uygulama', 'item2_text' => 'Projelerinizi hayata geçiriyoruz.',
                            'item3_icon' => '🎓', 'item3_title' => 'Eğitim & Destek', 'item3_text' => 'Sürekli eğitim ve teknik destek.',
                        ]],
                        ['template_type' => 'testimonials', 'template_variation' => 'base-testimonials', 'content' => [
                            'title' => 'Müşterilerimiz Ne Diyor?',
                            'quote1' => 'Profesyonel yaklaşımları işimizi bir üst seviyeye taşıdı.', 'author1' => 'A. Yılmaz',
                            'quote2' => 'Hızlı ve çözüm odaklı bir ekip.', 'author2' => 'B. Demir',
                            'quote3' => 'İş ortağı gibi çalışıyorlar, çok memnunuz.', 'author3' => 'C. Kaya',
                        ]],
                        ['template_type' => 'cta', 'template_variation' => 'base-cta', 'content' => [
                            'title' => 'Projeniz için konuşalım', 'subtitle' => 'Ücretsiz ön görüşme için bize ulaşın.',
                            'button_text' => 'İletişime Geç', 'button_url' => '/iletisim',
                        ]],
                    ]],
                    ['title' => 'Hakkımızda', 'slug' => 'hakkimizda', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Hakkımızda', 'title' => 'Kim olduğumuzu öğrenin', 'subtitle' => 'Yıllara dayanan deneyim ve uzmanlık.',
                            'button_text' => 'Hizmetler', 'button_url' => '/hizmetler',
                        ]],
                        ['template_type' => 'rich-text', 'template_variation' => 'base-content', 'content' => [
                            'title' => 'Hikayemiz',
                            'body_html' => '<p>Şirketimiz kuruluşundan bu yana sektörünün öncülerinden biri olmaya devam etmektedir. Sürdürülebilir, müşteri odaklı ve yenilikçi bir yaklaşımla hizmet veriyoruz.</p>',
                        ]],
                    ]],
                    ['title' => 'Hizmetler', 'slug' => 'hizmetler', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Ne yapıyoruz?', 'title' => 'Hizmetlerimiz', 'subtitle' => 'İhtiyacınıza özel çözümler.',
                            'button_text' => 'İletişim', 'button_url' => '/iletisim',
                        ]],
                        ['template_type' => 'features', 'template_variation' => 'base-features', 'content' => [
                            'title' => 'Hizmet Alanları', 'description' => 'Uçtan uca profesyonel destek.',
                            'item1_icon' => '📈', 'item1_title' => 'Strateji', 'item1_text' => 'Büyüme odaklı planlama.',
                            'item2_icon' => '⚙️', 'item2_title' => 'Operasyon', 'item2_text' => 'Verimli süreç yönetimi.',
                            'item3_icon' => '🤝', 'item3_title' => 'Destek', 'item3_text' => 'Kesintisiz müşteri desteği.',
                        ]],
                    ]],
                    ['title' => 'İletişim', 'slug' => 'iletisim', 'show_in_menu' => true, 'sections' => [
                        ['template_type' => 'hero', 'template_variation' => 'base-hero', 'content' => [
                            'eyebrow' => 'Bize Ulaşın', 'title' => 'İletişim', 'subtitle' => 'Sorularınız ve teklifleriniz için bize yazın.',
                            'button_text' => 'E-posta', 'button_url' => 'mailto:info@sirket.com',
                        ]],
                        $this->contactSection('<p>📍 İstanbul, Türkiye<br>📞 +90 212 000 0000<br>✉ info@sirket.com</p>'),
                    ]],
                ],
                'menus' => $this->menu([
                    ['label' => 'Anasayfa', 'page_slug' => 'anasayfa'],
                    ['label' => 'Hakkımızda', 'page_slug' => 'hakkimizda'],
                    ['label' => 'Hizmetler', 'page_slug' => 'hizmetler'],
                    ['label' => 'İletişim', 'page_slug' => 'iletisim'],
                ]),
            ],
        ];
    }
}
