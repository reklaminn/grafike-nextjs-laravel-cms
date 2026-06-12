<?php

namespace App\Services\Ai;

use App\Models\Article;
use App\Models\Page;
use App\Models\SiteSetting;
use Throwable;

/**
 * Collects the active tenant's site context for AI prompt injection.
 *
 * Sources (fail-open on every query):
 *  - SiteSetting: company_name, sector, description, target_audience,
 *                 city, phone, email, css_framework, brand_colors
 *  - Page model:  existing page titles/slugs → for internal-link hints
 *  - Article:     recent published articles → for blog-reference hints
 *
 * CSS-framework note:
 *   The page-generator AI does NOT write raw HTML or Bootstrap classes —
 *   block templates handle all styling.  We store the framework name
 *   (e.g. "Bootstrap 5") solely so the AI can reference correct icon /
 *   component naming conventions when filling icon-name or class-name
 *   content fields, if any exist in the block schemas.
 */
class SiteContextBuilder
{
    /**
     * Build a context array from the current tenant's DB.
     *
     * @param  array  $overrides  Manual key-value overrides (from wizard form).
     *                            Merged on top of DB values — override wins.
     */
    public function build(array $overrides = []): array
    {
        try {
            $defaults = [
                'company_name'    => SiteSetting::get('site.company_name')
                                     ?? SiteSetting::get('business.name'),
                'site_title'      => SiteSetting::get('site.title'),
                'description'     => SiteSetting::get('site.description'),
                'sector'          => SiteSetting::get('site.sector')
                                     ?? SiteSetting::get('business.type'),
                'target_audience' => SiteSetting::get('site.target_audience'),
                'city'            => SiteSetting::get('business.address_city'),
                'phone'           => SiteSetting::get('contact.phone'),
                'email'           => SiteSetting::get('contact.email'),
                'css_framework'   => SiteSetting::get('site.css_framework') ?? 'Bootstrap 5',
                'existing_pages'  => $this->existingPages(),
                'recent_articles' => $this->recentArticles(),
                'brand_colors'    => $this->brandColors(),
            ];
        } catch (Throwable) {
            $defaults = [];
        }

        // Remove nulls/empties from DB values, then overlay overrides
        $filtered = array_filter(
            $defaults,
            fn ($v) => $v !== null && $v !== '' && $v !== [],
        );

        return array_merge($filtered, array_filter(
            $overrides,
            fn ($v) => $v !== null && $v !== '',
        ));
    }

    /**
     * Serialize context array → compact AI-prompt snippet.
     * Returns empty string when context has no useful fields.
     */
    public function toPromptSnippet(array $ctx): string
    {
        $lines = [];

        if (!empty($ctx['company_name'])) {
            $lines[] = "Firma / Site Adı: {$ctx['company_name']}";
        }
        if (!empty($ctx['sector'])) {
            $lines[] = "Sektör / İşletme Tipi: {$ctx['sector']}";
        }
        if (!empty($ctx['description'])) {
            $lines[] = "Firma Hakkında: {$ctx['description']}";
        }
        if (!empty($ctx['target_audience'])) {
            $lines[] = "Hedef Kitle: {$ctx['target_audience']}";
        }
        if (!empty($ctx['city'])) {
            $lines[] = "Hizmet Bölgesi / Şehir: {$ctx['city']}";
        }
        if (!empty($ctx['phone'])) {
            $lines[] = "Telefon (CTA butonlarında kullan): {$ctx['phone']}";
        }
        if (!empty($ctx['email'])) {
            $lines[] = "E-posta: {$ctx['email']}";
        }
        if (!empty($ctx['css_framework'])) {
            $lines[] = "Kullanılan CSS Çatısı: {$ctx['css_framework']} (ikon/bileşen adlarını bu çatıya göre yaz)";
        }

        // Existing pages — for internal linking hints
        if (!empty($ctx['existing_pages']) && is_array($ctx['existing_pages'])) {
            $pageList = collect($ctx['existing_pages'])
                ->map(fn ($p) => "{$p['title']} (/{$p['slug']})")
                ->implode(', ');
            $lines[] = "Sitedeki Mevcut Sayfalar (dahili link için): {$pageList}";
        }

        // Planned pages in bulk wizard — cross-page linking awareness
        if (!empty($ctx['planned_pages']) && is_array($ctx['planned_pages'])) {
            $planned = collect($ctx['planned_pages'])
                ->map(fn ($p) => $p['title'])
                ->implode(', ');
            $lines[] = "Aynı Anda Oluşturulan Diğer Sayfalar: {$planned}";
        }

        // Recent articles
        if (!empty($ctx['recent_articles']) && is_array($ctx['recent_articles'])) {
            $articleList = collect($ctx['recent_articles'])
                ->map(fn ($a) => $a['title'])
                ->implode(', ');
            $lines[] = "Son Yayımlanan Blog Yazıları: {$articleList}";
        }

        // Brand colours
        if (!empty($ctx['brand_colors']) && is_array($ctx['brand_colors'])) {
            $colorStr = collect($ctx['brand_colors'])
                ->map(fn ($v, $k) => "{$k}: {$v}")
                ->implode(', ');
            $lines[] = "Marka Renkleri: {$colorStr}";
        }

        if (empty($lines)) {
            return '';
        }

        return "=== SİTE BAĞLAMI — İçerikleri buna göre kişiselleştir ===\n"
            . implode("\n", $lines)
            . "\n================================================";
    }

    // ─────────────────────────────────────────────────────────────────────

    private function existingPages(): array
    {
        try {
            return Page::query()
                ->select('title', 'slug')
                ->whereNotIn('status', ['trash', 'archived'])
                ->orderBy('sort_order')
                ->limit(30)
                ->get()
                ->map(fn ($p) => ['title' => (string) $p->title, 'slug' => (string) $p->slug])
                ->toArray();
        } catch (Throwable) {
            return [];
        }
    }

    private function recentArticles(): array
    {
        try {
            return Article::query()
                ->select('title', 'slug')
                ->where('status', 'published')
                ->orderByDesc('published_at')
                ->limit(10)
                ->get()
                ->map(fn ($a) => ['title' => (string) $a->title, 'slug' => (string) $a->slug])
                ->toArray();
        } catch (Throwable) {
            return [];
        }
    }

    private function brandColors(): array
    {
        try {
            return array_filter([
                'primary'   => SiteSetting::get('design.primary_color'),
                'secondary' => SiteSetting::get('design.secondary_color'),
                'accent'    => SiteSetting::get('design.accent_color'),
            ], fn ($v) => !empty($v));
        } catch (Throwable) {
            return [];
        }
    }
}
