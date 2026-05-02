<?php

namespace App\Services\Tenants;

use App\Models\Article;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SeoEntry;
use App\Models\SiteSetting;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantStarterContentSeeder
{
    public function seed(Tenant $tenant): void
    {
        // Only seed empty tenant databases. Re-running provisioning must never
        // overwrite a live site's content after the agency starts editing it.
        if (Page::withTrashed()->exists()) {
            return;
        }

        DB::transaction(function () use ($tenant) {
            $tenantName = $tenant->name ?: Str::headline((string) $tenant->getTenantKey());
            $now = now();

            $home = $this->createHomePage($tenantName);
            $about = $this->createAboutPage($tenantName);
            $contact = $this->createContactPage();

            foreach ([$home, $about, $contact] as $page) {
                $page->forceFill(['root_page_id' => $page->id])->save();
            }

            $this->seedArticles($home, $now);
            $this->seedMenu([$home, $about, $contact]);
            $this->seedSettings($tenantName, $home);
            $this->seedSeo($tenantName, [$home, $about, $contact]);
        });
    }

    private function createHomePage(string $tenantName): Page
    {
        return Page::create([
            'title' => 'Ana Sayfa',
            'slug' => 'home',
            'status' => 'published',
            'show_in_menu' => true,
            'sort_order' => 1,
            'language_id' => null,
            'template' => 'starter-home',
            'frontend_variant' => 'starter-home',
            'show_breadcrumb' => false,
            'sections_json' => $this->sections([
                $this->row('home_hero', [
                    $this->block('home_hero_block', 'hero', 'starter-split', 1, [
                        'eyebrow' => $tenantName,
                        'title' => "{$tenantName} için modern web sitesi",
                        'subtitle' => 'Bu alan yeni builder ile gelen örnek hero bloğudur. İçeriği admin panelden düzenleyebilir veya farklı bloklarla değiştirebilirsin.',
                        'button_text' => 'Hakkımızda',
                        'button_url' => '/hakkimizda',
                    ]),
                ]),
                $this->row('home_intro', [
                    $this->block('home_intro_block', 'rich-text', 'starter-content', 1, [
                        'title' => 'Yeni tenant başlangıç içeriği',
                        'body_html' => '<p>Bu sayfa, yeni site açıldığında ajans akışını hızlandırmak için otomatik oluşturulur. Header, body ve footer alanlarına blok ekleyerek sayfayı canlı projeye dönüştürebilirsin.</p>',
                    ]),
                ]),
                $this->row('home_articles', [
                    $this->block('home_articles_block', 'article-list', 'starter-cards', 1, [
                        'title' => 'Örnek Yazılar',
                        'description' => 'Yeni sitede yazı/listing modülünün nasıl çalıştığını göstermek için eklenen başlangıç yazıları.',
                        'limit' => 3,
                    ]),
                ]),
            ]),
        ]);
    }

    private function createAboutPage(string $tenantName): Page
    {
        return Page::create([
            'title' => 'Hakkımızda',
            'slug' => 'hakkimizda',
            'status' => 'published',
            'show_in_menu' => true,
            'sort_order' => 2,
            'language_id' => null,
            'template' => 'starter-page',
            'frontend_variant' => 'starter-page',
            'show_breadcrumb' => true,
            'sections_json' => $this->sections([
                $this->row('about_hero', [
                    $this->block('about_hero_block', 'hero', 'starter-centered', 1, [
                        'eyebrow' => 'Kurumsal',
                        'title' => 'Hakkımızda',
                        'subtitle' => "{$tenantName} markasını, değerlerini ve hizmet yaklaşımını anlatan başlangıç sayfası.",
                    ]),
                ]),
                $this->row('about_content', [
                    $this->block('about_content_block', 'rich-text', 'starter-content', 1, [
                        'title' => 'Markanı anlatmaya buradan başla',
                        'body_html' => '<p>Bu içerik örnek olarak eklenir. Firma geçmişi, uzmanlık alanları, ekip yapısı ve müşteri yaklaşımı gibi bilgileri bu blok üzerinden düzenleyebilirsin.</p><p>Yeni builder yapısı Faz 2 component mode geçişine hazır olacak şekilde blok tipleri ve içerik alanlarıyla saklanır.</p>',
                    ]),
                ]),
            ]),
        ]);
    }

    private function createContactPage(): Page
    {
        return Page::create([
            'title' => 'İletişim',
            'slug' => 'iletisim',
            'status' => 'published',
            'show_in_menu' => true,
            'sort_order' => 3,
            'language_id' => null,
            'template' => 'starter-contact',
            'frontend_variant' => 'starter-contact',
            'show_breadcrumb' => true,
            'sections_json' => $this->sections([
                $this->row('contact_hero', [
                    $this->block('contact_hero_block', 'hero', 'starter-centered', 1, [
                        'eyebrow' => 'Bize ulaşın',
                        'title' => 'İletişim',
                        'subtitle' => 'Telefon, e-posta, adres ve form alanlarını proje bilgilerine göre güncelleyebilirsin.',
                    ]),
                ]),
                $this->row('contact_content', [
                    $this->block('contact_content_block', 'rich-text', 'starter-contact-info', 1, [
                        'title' => 'İletişim Bilgileri',
                        'body_html' => '<p><strong>Telefon:</strong> +90 000 000 00 00</p><p><strong>E-posta:</strong> info@example.com</p><p><strong>Adres:</strong> Firma adresi buraya gelecek.</p>',
                    ]),
                ]),
            ]),
        ]);
    }

    private function seedArticles(Page $home, Carbon $now): void
    {
        $articles = [
            [
                'title' => 'Yeni web sitenizi yönetmeye başlama',
                'slug' => 'yeni-web-sitenizi-yonetmeye-baslama',
                'excerpt' => 'Sayfa, yazı, menü ve blok mantığını anlamak için eklenen örnek başlangıç yazısı.',
                'body' => '<p>Bu yazı yeni tenant oluşturulduğunda otomatik eklenir. Yazılar menüsünden düzenleyebilir, yayından kaldırabilir veya silebilirsin.</p>',
                'sort_order' => 1,
                'is_featured' => true,
            ],
            [
                'title' => 'Blok şablonları ile sayfa kurma',
                'slug' => 'blok-sablonlari-ile-sayfa-kurma',
                'excerpt' => 'Yeni builder içinde blokların sayfaya nasıl bağlandığını gösteren örnek içerik.',
                'body' => '<p>Bloklar sayfa düzeninde header, body ve footer bölgelerine eklenir. Faz 2 component mode geçişinde aynı içerik alanları component tarafında kullanılabilir.</p>',
                'sort_order' => 2,
                'is_featured' => true,
            ],
            [
                'title' => 'İletişim bilgilerini güncelleme',
                'slug' => 'iletisim-bilgilerini-guncelleme',
                'excerpt' => 'Ayarlar ve iletişim sayfasındaki placeholder içeriklerin nasıl değiştirileceğini anlatan örnek yazı.',
                'body' => '<p>Telefon, e-posta, adres ve sosyal medya bağlantılarını proje tesliminden önce gerçek firma bilgileriyle değiştirmelisin.</p>',
                'sort_order' => 3,
                'is_featured' => false,
            ],
        ];

        foreach ($articles as $article) {
            Article::create([
                ...$article,
                'page_id' => $home->id,
                'language_id' => null,
                'status' => 'published',
                'listing_variant' => 'starter-card',
                'detail_variant' => 'starter-detail',
                'published_at' => $now,
                'display_date' => $now->toDateString(),
            ]);
        }
    }

    /**
     * @param  array<int, Page>  $pages
     */
    private function seedMenu(array $pages): void
    {
        $menu = Menu::updateOrCreate(
            ['slug' => 'main-menu'],
            [
                'name' => 'Ana Menü',
                'location' => 'header',
                'language_id' => null,
                'theme_variant' => 'starter',
                'is_active' => true,
            ],
        );

        foreach ($pages as $index => $page) {
            MenuItem::updateOrCreate(
                ['menu_id' => $menu->id, 'page_id' => $page->id],
                [
                    'title' => $page->title,
                    'url' => $page->slug === 'home' ? '/' : '/' . $page->slug,
                    'target' => '_self',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedSettings(string $tenantName, Page $home): void
    {
        SiteSetting::set('site.name', $tenantName, 'general');
        SiteSetting::set('site.email', 'info@example.com', 'contact');
        SiteSetting::set('site.phone', '+90 000 000 00 00', 'contact');
        SiteSetting::set('site.address', 'Firma adresi buraya gelecek.', 'contact');
        SiteSetting::set('site.footer_text', "© {$tenantName}", 'general');
        SiteSetting::set('cms.homepage_id', (string) $home->id, 'cms');
    }

    /**
     * @param  array<int, Page>  $pages
     */
    private function seedSeo(string $tenantName, array $pages): void
    {
        foreach ($pages as $page) {
            SeoEntry::create([
                'seoable_id' => $page->id,
                'seoable_type' => Page::class,
                'slug' => $page->slug,
                'language_id' => null,
                'meta_title' => $page->slug === 'home' ? "{$tenantName} | Ana Sayfa" : "{$page->title} | {$tenantName}",
                'meta_description' => match ($page->slug) {
                    'home' => "{$tenantName} için oluşturulan başlangıç ana sayfası.",
                    'hakkimizda' => "{$tenantName} hakkında kurumsal bilgiler.",
                    'iletisim' => "{$tenantName} iletişim bilgileri.",
                    default => $page->title,
                },
                'og_type' => 'website',
                'schema_type' => $page->slug === 'iletisim' ? 'LocalBusiness' : 'WebPage',
                'sitemap_priority' => $page->slug === 'home' ? 1.0 : 0.8,
                'sitemap_changefreq' => 'weekly',
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function sections(array $rows): array
    {
        return [
            'version' => 2,
            'regions' => [
                'header' => [],
                'body' => $rows,
                'footer' => [],
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function row(string $id, array $blocks): array
    {
        return [
            'id' => 'row_' . $id,
            'type' => 'row',
            'is_active' => true,
            'container' => null,
            'wrapper_tag' => null,
            'css_class' => null,
            'element_id' => null,
            'inline_style' => null,
            'custom_attributes' => null,
            'columns' => [[
                'id' => 'col_' . $id . '_1',
                'width' => null,
                'is_active' => true,
                'responsive' => [
                    'xs' => null,
                    'sm' => null,
                    'md' => null,
                    'lg' => null,
                    'xl' => null,
                ],
                'css_class' => null,
                'element_id' => null,
                'inline_style' => null,
                'custom_attributes' => null,
                'blocks' => $blocks,
            ]],
        ];
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function block(string $id, string $type, string $variation, int $sortOrder, array $content): array
    {
        return [
            'id' => $id,
            'type' => $type,
            'variation' => $variation,
            'render_mode' => 'component',
            'section_template_id' => null,
            'component_key' => null,
            'is_active' => true,
            'sort_order' => $sortOrder,
            'content' => $content,
            'wrapper_tag' => null,
            'css_class' => null,
            'element_id' => null,
            'inline_style' => null,
            'custom_attributes' => null,
            'html_override' => null,
        ];
    }
}
