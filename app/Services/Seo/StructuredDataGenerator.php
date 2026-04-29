<?php

namespace App\Services\Seo;

use App\Models\Article;
use App\Models\Page;
use App\Models\SiteSetting;

/**
 * Generates JSON-LD structured data for pages, articles, and site-level entities.
 *
 * Supported schema types:
 *   WebPage, Article, BlogPosting, Product, FAQPage, Service,
 *   BreadcrumbList, WebSite, Organization
 */
class StructuredDataGenerator
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('app.url', url('/')), '/');
    }

    // ─── Page ──────────────────────────────────────────────────────────────────

    public function forPage(Page $page, string $schemaType = 'WebPage'): array
    {
        $seo  = $page->seo;
        $url  = $seo?->canonical_url ?: ($this->baseUrl . '/' . ltrim($page->slug, '/'));
        $name = $seo?->meta_title    ?: $page->title;
        $desc = $seo?->meta_description ?: '';

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => $schemaType,
            'url'      => $url,
            'name'     => $name,
        ];

        if ($desc) {
            $schema['description'] = $desc;
        }

        if ($page->updated_at) {
            $schema['dateModified'] = $page->updated_at->toIso8601String();
        }

        if ($page->created_at) {
            $schema['datePublished'] = $page->created_at->toIso8601String();
        }

        // Cover image
        $coverUrl = $page->getFirstMediaUrl('cover');
        if ($coverUrl) {
            $schema['image'] = $coverUrl;
        }

        // Breadcrumb list
        $breadcrumbs = $this->breadcrumbForPage($page);
        if (! empty($breadcrumbs)) {
            $schema['breadcrumb'] = $breadcrumbs;
        }

        return $this->wrapOrganization($schema);
    }

    // ─── Article ───────────────────────────────────────────────────────────────

    public function forArticle(Article $article, string $schemaType = 'Article'): array
    {
        $url  = $this->baseUrl . '/' . ltrim($article->slug, '/');
        $name = $article->title;
        $desc = $article->excerpt ?? '';

        $schema = [
            '@context'      => 'https://schema.org',
            '@type'         => $schemaType,
            'headline'      => $name,
            'url'           => $url,
            'datePublished' => $article->published_at?->toIso8601String()
                                ?? $article->created_at?->toIso8601String(),
            'dateModified'  => $article->updated_at?->toIso8601String(),
        ];

        if ($desc) {
            $schema['description'] = $desc;
        }

        // Cover image
        $coverUrl = $article->getFirstMediaUrl('cover');
        if ($coverUrl) {
            $schema['image'] = [
                '@type'  => 'ImageObject',
                'url'    => $coverUrl,
                'width'  => 1200,
                'height' => 630,
            ];
        }

        // Author
        if ($article->author) {
            $schema['author'] = [
                '@type' => 'Person',
                'name'  => $article->author->name,
            ];
        }

        // Publisher = Organization
        $schema['publisher'] = $this->organizationSnippet();

        return $schema;
    }

    // ─── WebSite (for homepage) ────────────────────────────────────────────────

    public function webSite(?string $searchUrl = null): array
    {
        $siteName = SiteSetting::get('site_title', config('app.name'));

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'url'      => $this->baseUrl . '/',
            'name'     => $siteName,
        ];

        // Google Sitelinks SearchBox
        if ($searchUrl) {
            $schema['potentialAction'] = [
                '@type'        => 'SearchAction',
                'target'       => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => $searchUrl . '?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ];
        }

        return $schema;
    }

    // ─── Organization ─────────────────────────────────────────────────────────

    public function organization(): array
    {
        $siteName = SiteSetting::get('site_title', config('app.name'));
        $logoUrl  = SiteSetting::get('logo_url');
        $phone    = SiteSetting::get('contact.phone');
        $email    = SiteSetting::get('contact.email');
        $address  = SiteSetting::get('contact.address');

        // Business-specific settings
        $street   = SiteSetting::get('business.address_street');
        $city     = SiteSetting::get('business.address_city');
        $postal   = SiteSetting::get('business.address_postal_code');
        $country  = SiteSetting::get('business.address_country', 'TR');
        $lat      = SiteSetting::get('business.geo_lat');
        $lng      = SiteSetting::get('business.geo_lng');
        $bizType  = SiteSetting::get('business.business_type', 'Organization');

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => $bizType,
            'name'     => $siteName,
            'url'      => $this->baseUrl . '/',
        ];

        if ($logoUrl) {
            $schema['logo'] = [
                '@type' => 'ImageObject',
                'url'   => $logoUrl,
            ];
        }

        if ($phone) {
            $schema['telephone'] = $phone;
        }

        if ($email) {
            $schema['email'] = $email;
        }

        // Compose address
        $hasAddress = $street || $city || $postal || $country || $address;
        if ($hasAddress) {
            $schema['address'] = [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $street   ?: $address ?: '',
                'addressLocality' => $city     ?: '',
                'postalCode'      => $postal   ?: '',
                'addressCountry'  => $country,
            ];
        }

        // Geo coordinates
        if ($lat && $lng) {
            $schema['geo'] = [
                '@type'     => 'GeoCoordinates',
                'latitude'  => (float) $lat,
                'longitude' => (float) $lng,
            ];
        }

        return $schema;
    }

    // ─── FAQPage (auto-detect from sections_json) ─────────────────────────────

    public function detectFaqBlocks(array $sectionsJson): ?array
    {
        $faqs = [];

        $blocks = $sectionsJson['regions'] ?? [];

        array_walk_recursive($blocks, function ($block) use (&$faqs) {
            if (! is_array($block)) {
                return;
            }
            $type = $block['type'] ?? '';
            if (! in_array($type, ['faq', 'accordion', 'faq-section'], true)) {
                return;
            }
            $content = $block['content'] ?? [];
            $items   = $content['items'] ?? [];

            foreach ($items as $item) {
                $q = $item['question'] ?? $item['title'] ?? '';
                $a = $item['answer']   ?? $item['body']  ?? '';
                if ($q && $a) {
                    $faqs[] = [
                        '@type'          => 'Question',
                        'name'           => $q,
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text'  => strip_tags($a),
                        ],
                    ];
                }
            }
        });

        if (empty($faqs)) {
            return null;
        }

        return [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $faqs,
        ];
    }

    // ─── BreadcrumbList ────────────────────────────────────────────────────────

    public function breadcrumbForPage(Page $page): array
    {
        $chain   = [];
        $current = $page;

        while ($current) {
            array_unshift($chain, $current);
            $current = $current->parent;
        }

        $items = [];
        foreach ($chain as $pos => $item) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $pos + 1,
                'name'     => $item->title,
                'item'     => $this->baseUrl . '/' . ltrim($item->slug, '/'),
            ];
        }

        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    protected function organizationSnippet(): array
    {
        return [
            '@type' => 'Organization',
            'name'  => SiteSetting::get('site_title', config('app.name')),
            'url'   => $this->baseUrl . '/',
            'logo'  => SiteSetting::get('logo_url') ?: $this->baseUrl . '/logo.png',
        ];
    }

    protected function wrapOrganization(array $schema): array
    {
        $schema['isPartOf'] = [
            '@type' => 'WebSite',
            'url'   => $this->baseUrl . '/',
        ];

        return $schema;
    }

    // ─── Schema types available for the UI ────────────────────────────────────

    public static function availableTypes(): array
    {
        return [
            'WebPage'     => 'Web Sayfası (WebPage)',
            'Article'     => 'Makale (Article)',
            'BlogPosting' => 'Blog Yazısı (BlogPosting)',
            'NewsArticle' => 'Haber (NewsArticle)',
            'Product'     => 'Ürün (Product)',
            'Service'     => 'Hizmet (Service)',
            'FAQPage'     => 'SSS Sayfası (FAQPage)',
            'ContactPage' => 'İletişim Sayfası (ContactPage)',
            'AboutPage'   => 'Hakkımızda (AboutPage)',
            'Custom'      => 'Özel JSON (Custom)',
        ];
    }
}
