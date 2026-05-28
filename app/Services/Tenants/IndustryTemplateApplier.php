<?php

namespace App\Services\Tenants;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\SiteTemplate;
use App\Models\Tenant;
use App\Support\FrontendSections;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stancl\Tenancy\Facades\Tenancy;
use Throwable;

/**
 * Applies an industry SiteTemplate to a tenant — seeds the tenant DB
 * with pages, menus, and site settings from `snapshot_json`.
 *
 * Snapshot shape (see seeders):
 *   {
 *     "pages": [
 *       { "title":"Anasayfa", "slug":"anasayfa", "is_home":true,
 *         "sections": [ {template_type, content, variation?}, ... ] },
 *       …
 *     ],
 *     "menus": [
 *       { "name":"Ana Menü", "location":"header",
 *         "items":[ {"label":"Anasayfa", "page_slug":"anasayfa"}, … ] }
 *     ],
 *     "settings": { "site.title":"…", "design.color_primary":"#…", … },
 *     "custom_css": "/* optional global override *\/"
 *   }
 *
 * Idempotency: by default the applier REPLACES existing pages/menus
 * matching the snapshot's slugs. Pass `merge: true` to keep existing
 * rows untouched (skip on slug collision). Tenant must be initialized
 * before calling; the service ensures it via tenancy()->initialize().
 */
class IndustryTemplateApplier
{
    public function __construct(
        private readonly TenantStarterContentSeeder $starterSeeder,
    ) {
    }

    /**
     * @param  Tenant  $tenant
     * @param  SiteTemplate  $template
     * @param  array{
     *     replace?: bool,  // wipe existing pages/menus first (default true)
     *     skip_pages?: bool,
     *     skip_menus?: bool,
     *     skip_settings?: bool,
     * }  $options
     */
    public function apply(Tenant $tenant, SiteTemplate $template, array $options = []): array
    {
        $snapshot = $template->snapshot_json;
        if (! is_array($snapshot)) {
            throw new \RuntimeException("Site template '{$template->slug}' has no snapshot_json.");
        }

        $opts = array_merge([
            'replace'       => true,
            'skip_pages'    => false,
            'skip_menus'    => false,
            'skip_settings' => false,
        ], $options);

        $summary = ['pages' => 0, 'menus' => 0, 'settings' => 0];

        Tenancy::initialize($tenant);
        try {
            DB::connection('tenant')->transaction(function () use ($snapshot, $opts, &$summary, $tenant, $template) {
                if (! $opts['skip_pages']) {
                    $summary['pages'] = $this->seedPages($snapshot['pages'] ?? [], $opts['replace']);
                }
                if (! $opts['skip_menus']) {
                    $summary['menus'] = $this->seedMenus($snapshot['menus'] ?? [], $opts['replace']);
                }
                if (! $opts['skip_settings']) {
                    $summary['settings'] = $this->seedSettings($snapshot['settings'] ?? []);
                }

                // Store the applied template slug + custom_css on the tenant
                // record so the agency can later see "this site started from
                // industry/clinic-modern".
                if (! empty($snapshot['custom_css'])) {
                    SiteSetting::set('design.custom_css', (string) $snapshot['custom_css'], 'design');
                }
                SiteSetting::set('industry.template_slug', $template->slug, 'industry');
                SiteSetting::set('industry.industry', (string) ($template->industry ?? ''), 'industry');
            });
        } finally {
            Tenancy::end();
        }

        return $summary;
    }

    // ────────────────────────────────────────────────────────────────────

    /**
     * @param  array<int, array<string,mixed>>  $pages
     */
    private function seedPages(array $pages, bool $replace): int
    {
        $count = 0;

        foreach ($pages as $index => $pageDef) {
            $slug = $this->slugify((string) ($pageDef['slug'] ?? ($pageDef['title'] ?? 'page-'.$index)));
            $existing = Page::query()->where('slug', $slug)->first();

            if ($existing) {
                if (! $replace) {
                    continue;
                }
                $existing->delete();
            }

            $sectionsJson = $this->buildSectionsJson($pageDef['sections'] ?? []);

            Page::create([
                'title'         => (string) ($pageDef['title'] ?? Str::headline($slug)),
                'slug'          => $slug,
                'status'        => 'published',
                'show_in_menu'  => (bool) ($pageDef['show_in_menu'] ?? false),
                'sort_order'    => $index + 1,
                'sections_json' => $sectionsJson,
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Walk the snapshot's flat block list and resolve `template_type` /
     * `template_variation` / `template_id` references to actual
     * SectionTemplate ids. Then wrap into the region-based v2 shape.
     */
    private function buildSectionsJson(array $sectionsDef): array
    {
        if (empty($sectionsDef)) {
            return FrontendSections::emptyStructure();
        }

        $resolved = [];
        $i = 0;
        foreach ($sectionsDef as $section) {
            if (! is_array($section)) continue;

            $tpl = $this->resolveSectionTemplate($section);
            if (! $tpl) {
                continue; // template not found → skip silently
            }

            $resolved[] = [
                'id'                  => 'block_'.($i + 1).'_'.Str::random(4),
                'type'                => (string) $tpl->type,
                'variation'           => (string) ($tpl->variation ?? ''),
                'render_mode'         => (string) ($tpl->render_mode ?? 'html'),
                'section_template_id' => (int) $tpl->id,
                'is_active'           => true,
                'sort_order'          => $i + 1,
                'content'             => array_merge(
                    is_array($tpl->default_content_json) ? $tpl->default_content_json : [],
                    is_array($section['content'] ?? null) ? $section['content'] : [],
                ),
            ];
            $i++;
        }

        return FrontendSections::normalize($resolved);
    }

    private function resolveSectionTemplate(array $section): ?\App\Models\SectionTemplate
    {
        // 1) Explicit template_id wins
        if (! empty($section['template_id'])) {
            return \App\Models\SectionTemplate::query()->find((int) $section['template_id']);
        }

        $type = $section['template_type'] ?? null;
        if (! $type) return null;
        $variation = $section['template_variation'] ?? null;

        // Industry templates are agency-global, so they must seed pages that
        // reference GLOBAL (tenant_id IS NULL) blocks only. This prevents the
        // type-only fallback from grabbing another tenant's private block now
        // that the catalog is tenant-scoped.
        $query = \App\Models\SectionTemplate::query()
            ->whereNull('tenant_id')
            ->where('is_active', true)
            ->where('type', $type);

        // 2) Type + variation match preferred
        if ($variation) {
            $exact = (clone $query)->where('variation', $variation)->first();
            if ($exact) return $exact;
        }

        // 3) Type-only fallback (first active template of that type)
        return $query->orderBy('id')->first();
    }

    private function seedMenus(array $menus, bool $replace): int
    {
        $count = 0;

        foreach ($menus as $menuDef) {
            $name     = (string) ($menuDef['name'] ?? 'Ana Menü');
            $location = (string) ($menuDef['location'] ?? 'header');

            $existing = Menu::query()->where('location', $location)->first();
            if ($existing) {
                if (! $replace) continue;
                $existing->items()->delete();
                $existing->delete();
            }

            $menu = Menu::create([
                'name'      => $name,
                // tenant `menus.slug` is NOT NULL + unique — must be supplied.
                'slug'      => $this->uniqueMenuSlug($name, $location),
                'location'  => $location,
                'is_active' => true,
            ]);

            $items = $menuDef['items'] ?? [];
            foreach ($items as $i => $item) {
                $pageId = null;
                if (! empty($item['page_slug'])) {
                    $page = Page::query()->where('slug', $item['page_slug'])->first();
                    if ($page) {
                        $pageId = $page->id;
                    }
                }
                MenuItem::create([
                    'menu_id'    => $menu->id,
                    // tenant column is `title` (snapshot uses `label`).
                    'title'      => (string) ($item['label'] ?? $item['title'] ?? 'Bağlantı'),
                    'page_id'    => $pageId,
                    'url'        => $item['url'] ?? null,
                    'sort_order' => $i + 1,
                    'is_active'  => true,
                ]);
            }
            $count++;
        }

        return $count;
    }

    private function seedSettings(array $settings): int
    {
        $count = 0;
        foreach ($settings as $key => $value) {
            if (! is_string($key) || $key === '') continue;
            SiteSetting::set($key, is_scalar($value) ? (string) $value : json_encode($value), $this->groupFromKey($key));
            $count++;
        }

        return $count;
    }

    private function groupFromKey(string $key): string
    {
        $first = explode('.', $key, 2)[0] ?? 'general';

        return match ($first) {
            'site'    => 'general',
            'contact' => 'contact',
            'social'  => 'social',
            'design'  => 'design',
            'theme'   => 'theme',
            'industry' => 'industry',
            default   => 'general',
        };
    }

    private function slugify(string $value): string
    {
        $slug = Str::slug($value, '-', 'tr');

        return $slug !== '' ? $slug : 'page';
    }

    /**
     * Build a menu slug that is unique within the tenant DB (menus.slug is
     * UNIQUE + NOT NULL). Falls back to the location and appends -2, -3, … on
     * collision (e.g. a header + footer menu sharing the same name).
     */
    private function uniqueMenuSlug(string $name, string $location): string
    {
        $base = $this->slugify(trim($name) !== '' ? $name : $location);
        $slug = $base;
        $n = 2;

        while (Menu::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$n;
            $n++;
        }

        return $slug;
    }
}
