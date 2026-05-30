<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesCatalogToTenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionTemplateRequest;
use App\Models\Menu;
use App\Models\SectionTemplate;
use App\Models\SectionTemplateVersion;
use App\Models\SiteSetting;
use App\Models\Theme;
use App\Services\SectionTemplate\SectionTemplateRenderer;
use App\Support\FrontendSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionTemplateController extends Controller
{
    use ScopesCatalogToTenant;

    private const COMMON_TYPE_CATALOG = [
        'header' => 'Header',
        'footer' => 'Footer',
        'hero' => 'Hero / Banner',
        'hero-banner' => 'Hero Banner',
        'slider' => 'Slider',
        'rich-text' => 'Rich Text / Metin',
        'content-block' => 'İçerik Bloğu',
        'article-list' => 'Yazı Liste',
        'features' => 'Özellik Alanı',
        'cta' => 'CTA Alanı',
        'gallery' => 'Galeri',
        'spacer' => 'Boşluk / Spacer',
        'video-embed' => 'Video Embed',
        'page-header' => 'Sayfa Başlığı',
        'menu' => 'Menu',
        'testimonials' => 'Testimonials',
        'cards' => 'Kart Grubu',
    ];

    public function index(Request $request)
    {
        $tenantId = $this->catalogTenantId();
        $modules  = $this->catalogModuleFilter();

        $trashed = $request->boolean('trashed');
        $query = $trashed
            ? SectionTemplate::onlyTrashed()->visibleTo($tenantId)->visibleForModules($modules)->with('theme')
            : SectionTemplate::query()->visibleTo($tenantId)->visibleForModules($modules)->with('theme');

        if ($request->filled('q')) {
            $search = trim((string) $request->string('q'));

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('variation', 'like', "%{$search}%")
                    ->orWhere('legacy_module_key', 'like', "%{$search}%");
            });
        }

        if ($request->filled('theme_id')) {
            $query->where('theme_id', $request->integer('theme_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('render_mode')) {
            $query->where('render_mode', $request->input('render_mode'));
        }

        if (! $trashed && $request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $sectionTemplates = $query
            ->orderByDesc('is_active')
            ->orderBy('theme_id')
            ->orderBy('type')
            ->orderBy('variation')
            ->paginate(18)
            ->withQueryString();

        $themes = Theme::query()->visibleTo($tenantId)->visibleForModules($modules)->orderBy('name')->get(['id', 'name', 'slug']);
        $typeOptions = $this->buildTypeOptions();

        $usageMap = $this->computeUsageMap();
        $usageCounts = collect($usageMap)->map(fn (array $pages) => count($pages))->all();
        $trashedCount = SectionTemplate::onlyTrashed()->visibleTo($tenantId)->visibleForModules($modules)->count();

        return view('admin.section-templates.index', compact('sectionTemplates', 'themes', 'typeOptions', 'usageCounts', 'usageMap', 'trashed', 'trashedCount'));
    }

    private function computeUsageCounts(): array
    {
        return collect($this->computeUsageMap())
            ->map(fn (array $pages) => count($pages))
            ->all();
    }

    /**
     * @return array<int, array<int, array{id: int, title: string, slug: string}>>
     */
    private function computeUsageMap(): array
    {
        // `pages` is a TENANT table. Section templates are a central/global
        // catalog that an agency admin can browse without selecting a site —
        // in that state the default connection points at the central DB, which
        // has no `pages` table (→ 500). Usage is per-tenant and meaningless
        // without an active site, so return an empty map instead.
        if (! tenancy()->initialized) {
            return [];
        }

        return DB::table('pages')
            ->whereNotNull('sections_json')
            ->get(['id', 'title', 'slug', 'sections_json'])
            ->reduce(function (array $carry, object $row): array {
                $sections = json_decode($row->sections_json, true);
                foreach (FrontendSections::flattenBlocks($sections ?: []) as $block) {
                    $id = $block['section_template_id'] ?? null;
                    if ($id) {
                        $carry[$id] ??= [];
                        $carry[$id][$row->id] = [
                            'id' => (int) $row->id,
                            'title' => (string) $row->title,
                            'slug' => (string) $row->slug,
                        ];
                    }
                }
                return $carry;
            }, []);
    }

    public function create()
    {
        $sectionTemplate = new SectionTemplate([
            'render_mode' => 'html',
            'is_active' => true,
            'schema_json' => [],
            'default_content_json' => [],
            'legacy_config_map_json' => [],
        ]);

        return view('admin.section-templates.create', $this->buildFormViewData($sectionTemplate));
    }

    public function store(SectionTemplateRequest $request)
    {
        $data = $request->validated();
        $data['tenant_id'] = $this->newCatalogOwnerId();

        $sectionTemplate = SectionTemplate::create($data);

        if ($request->hasFile('preview_image')) {
            $sectionTemplate->addMediaFromRequest('preview_image')
                ->toMediaCollection('preview_image');
        }

        return redirect()
            ->route('admin.section-templates.edit', $sectionTemplate)
            ->with('success', 'Block şablonu oluşturuldu.');
    }

    public function edit(SectionTemplate $sectionTemplate)
    {
        $this->authorizeCatalogRead($sectionTemplate->tenant_id);

        return view('admin.section-templates.edit', $this->buildFormViewData($sectionTemplate));
    }

    public function update(SectionTemplateRequest $request, SectionTemplate $sectionTemplate)
    {
        $this->authorizeCatalogWrite($sectionTemplate->tenant_id);

        // Snapshot before overwrite if html_template or schema changed
        $dirty = array_intersect(
            array_keys($request->validated()),
            ['html_template', 'schema_json', 'default_content_json']
        );
        if (! empty($dirty)) {
            $sectionTemplate->recordVersion('pre-update');
            // Prune versions beyond 30 most recent
            if ($sectionTemplate->versions()->count() > 30) {
                $keepIds = $sectionTemplate->versions()->limit(30)->pluck('id');
                $sectionTemplate->versions()->whereNotIn('id', $keepIds)->delete();
            }
        }

        $sectionTemplate->update($request->validated());

        if ($request->hasFile('preview_image')) {
            $sectionTemplate->addMediaFromRequest('preview_image')
                ->toMediaCollection('preview_image');
        } elseif ($request->boolean('remove_preview_image')) {
            $sectionTemplate->clearMediaCollection('preview_image');
        }

        return redirect()
            ->route('admin.section-templates.edit', $sectionTemplate)
            ->with('success', 'Block şablonu güncellendi.');
    }

    public function versions(SectionTemplate $sectionTemplate)
    {
        $this->authorizeCatalogRead($sectionTemplate->tenant_id);

        $versions = $sectionTemplate->versions()->limit(50)->get();

        return response()->json($versions->map(fn (SectionTemplateVersion $v) => [
            'id'         => $v->id,
            'label'      => $v->label,
            'reason'     => $v->reason,
            'created_at' => $v->created_at?->format('d.m.Y H:i'),
            'admin'      => $v->admin?->name,
        ]));
    }

    public function saveVersion(Request $request, SectionTemplate $sectionTemplate)
    {
        $this->authorizeCatalogWrite($sectionTemplate->tenant_id);

        $label = $request->input('label');
        $sectionTemplate->recordVersion('manual', $label ?: null);

        return back()->with('success', 'Versiyon kaydedildi.');
    }

    public function restoreVersion(SectionTemplate $sectionTemplate, SectionTemplateVersion $version)
    {
        $this->authorizeCatalogWrite($sectionTemplate->tenant_id);

        // Snapshot current before restore
        $sectionTemplate->recordVersion('pre-restore');

        $sectionTemplate->update([
            'html_template'        => $version->html_template,
            'schema_json'          => $version->schema_json,
            'default_content_json' => $version->default_content_json,
        ]);

        return redirect()
            ->route('admin.section-templates.edit', $sectionTemplate)
            ->with('success', 'Versiyon geri yüklendi.');
    }

    public function duplicate(SectionTemplate $sectionTemplate)
    {
        // A tenant may fork any block they can SEE (global or own); the copy
        // becomes owned by the current tenant so they can customise it.
        $this->authorizeCatalogRead($sectionTemplate->tenant_id);

        $baseVariation = $sectionTemplate->variation.'-copy';
        $variation = $baseVariation;
        $suffix = 2;

        while (SectionTemplate::query()
            ->where('theme_id', $sectionTemplate->theme_id)
            ->where('type', $sectionTemplate->type)
            ->where('variation', $variation)
            ->exists()) {
            $variation = $baseVariation.'-'.$suffix;
            $suffix++;
        }

        $copy = $sectionTemplate->replicate();
        $copy->tenant_id  = $this->newCatalogOwnerId();
        $copy->name       = $sectionTemplate->name . ' (kopya)';
        $copy->variation  = $variation;
        $copy->is_active  = false;
        $copy->save();

        return redirect()
            ->route('admin.section-templates.edit', $copy)
            ->with('success', 'Block şablonu kopyalandı. Adı ve varyasyonu düzenleyin.');
    }

    public function destroy(SectionTemplate $sectionTemplate)
    {
        $this->authorizeCatalogWrite($sectionTemplate->tenant_id);

        $sectionTemplate->delete(); // soft delete

        return redirect()
            ->route('admin.section-templates.index')
            ->with('success', 'Block şablonu arşivlendi.');
    }

    public function restore(SectionTemplate $sectionTemplate)
    {
        $this->authorizeCatalogWrite($sectionTemplate->tenant_id);

        $sectionTemplate->restore();

        return redirect()
            ->route('admin.section-templates.index', ['trashed' => 1])
            ->with('success', 'Block şablonu geri yüklendi.');
    }

    public function forceDelete(SectionTemplate $sectionTemplate)
    {
        $this->authorizeCatalogWrite($sectionTemplate->tenant_id);

        $sectionTemplate->clearMediaCollection('preview_image');
        $sectionTemplate->forceDelete();

        return redirect()
            ->route('admin.section-templates.index', ['trashed' => 1])
            ->with('success', 'Block şablonu kalıcı olarak silindi.');
    }

    public function preview(Request $request, SectionTemplate $sectionTemplate): mixed
    {
        $this->authorizeCatalogRead($sectionTemplate->tenant_id);

        $renderer = app(SectionTemplateRenderer::class);

        if ($request->isMethod('POST')) {
            // Live preview: caller posts current html_template + content override
            $htmlTemplate = $request->input('html_template');
            $content      = $request->input('content', []);

            if (is_string($content)) {
                $content = json_decode($content, true) ?? [];
            }

            $clone = clone $sectionTemplate;
            $clone->html_template        = $htmlTemplate;
            $clone->default_content_json = $content;

            return response($renderer->render($clone))
                ->header('Content-Type', 'text/html; charset=utf-8')
                ->header('X-Frame-Options', 'SAMEORIGIN');
        }

        $rendered = $renderer->render($sectionTemplate);
        $theme    = $sectionTemplate->theme;

        return response()->view('admin.section-templates.preview', compact('sectionTemplate', 'rendered', 'theme'))
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    public function menuPlaceholders(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->buildMenuPlaceholders());
    }

    private function buildFormViewData(SectionTemplate $sectionTemplate): array
    {
        $themes = Theme::query()->visibleTo($this->catalogTenantId())->visibleForModules($this->catalogModuleFilter())->orderBy('name')->get();
        $typeOptions = $this->buildTypeOptions();
        $variationOptions = $this->buildVariationOptions($themes, $sectionTemplate);
        $menuPlaceholders = $this->buildMenuPlaceholders();
        $systemPlaceholders = $this->buildSystemPlaceholders();
        $legacyModuleOptions = $this->buildLegacyModuleOptions();
        $componentKeyOptions = $this->buildComponentKeyOptions();
        $usagePages = $sectionTemplate->exists
            ? array_values($this->computeUsageMap()[$sectionTemplate->id] ?? [])
            : [];

        return compact('sectionTemplate', 'themes', 'typeOptions', 'variationOptions', 'menuPlaceholders', 'systemPlaceholders', 'legacyModuleOptions', 'componentKeyOptions', 'usagePages');
    }

    /**
     * @return array<string, string>  module_key => human label
     */
    private function buildLegacyModuleOptions(): array
    {
        $modulesPath = app_path('Services/ModuleRenderer/Modules');

        return collect(glob("{$modulesPath}/*.php"))
            ->map(fn (string $path) => basename($path, '.php'))
            ->reject(fn (string $class) => in_array($class, ['BaseModule', 'NotFoundModule']))
            ->sort()
            ->mapWithKeys(fn (string $class) => [
                $class => str($class)->replaceLast('Module', '')->headline()->value(),
            ])
            ->all();
    }

    /**
     * @return array<int, array{key: string, label: string, type: string}>
     */
    private function buildComponentKeyOptions(): array
    {
        $manifestPath = base_path('apps/frontend/public/component-manifest.json');

        if (! file_exists($manifestPath)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($manifestPath), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, string>
     */
    private function buildTypeOptions(): array
    {
        $existingTypes = SectionTemplate::query()
            ->visibleTo($this->catalogTenantId())
            ->visibleForModules($this->catalogModuleFilter())
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->filter()
            ->mapWithKeys(fn (string $type) => [$type => self::COMMON_TYPE_CATALOG[$type] ?? str($type)->replace('-', ' ')->headline()->value()])
            ->all();

        return array_replace(self::COMMON_TYPE_CATALOG, $existingTypes);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Theme>  $themes
     * @return array<int|string, array<string, array<int, string>>>
     */
    private function buildVariationOptions($themes, SectionTemplate $sectionTemplate): array
    {
        $existing = SectionTemplate::query()
            ->visibleTo($this->catalogTenantId())
            ->visibleForModules($this->catalogModuleFilter())
            ->select(['theme_id', 'type', 'variation'])
            ->orderBy('variation')
            ->get()
            ->groupBy('theme_id')
            ->map(function ($themeGroup) {
                return $themeGroup
                    ->groupBy('type')
                    ->map(fn ($typeGroup) => $typeGroup->pluck('variation')->filter()->unique()->values()->all())
                    ->all();
            })
            ->all();

        $defaultsByType = [
            'header' => ['default-header', 'topbar-header', 'mega-header'],
            'footer' => ['default-footer', 'simple-footer', 'multi-column-footer'],
            'hero' => ['porto-split', 'centered', 'full-width'],
            'hero-banner' => ['porto-hero', 'simple-hero'],
            'slider' => ['owl-carousel', 'hero-slider'],
            'rich-text' => ['porto-content', 'plain-content'],
            'content-block' => ['default-content-block'],
            'article-list' => ['porto-cards', 'grid', 'minimal-list'],
            'features' => ['porto-icons', 'cards', 'inline-icons'],
            'cta' => ['banner', 'split', 'minimal'],
            'gallery' => ['grid-gallery', 'masonry-gallery'],
            'spacer' => ['default-spacer'],
            'video-embed' => ['default-video', 'cover-video'],
            'page-header' => ['default-header', 'minimal-header'],
            'menu' => ['header-menu', 'footer-menu'],
            'testimonials' => ['cards', 'carousel'],
            'cards' => ['three-cards', 'four-cards'],
        ];

        foreach ($themes as $theme) {
            $existing[$theme->id] = array_replace_recursive(
                $defaultsByType,
                $existing[$theme->id] ?? []
            );
        }

        if ($sectionTemplate->theme_id && $sectionTemplate->type && $sectionTemplate->variation) {
            $existing[$sectionTemplate->theme_id][$sectionTemplate->type] = array_values(array_unique(array_merge(
                $existing[$sectionTemplate->theme_id][$sectionTemplate->type] ?? [],
                [$sectionTemplate->variation]
            )));
        }

        return $existing;
    }

    /**
     * @return array<int, array{label: string, key: string, html_token: string, items_token: string}>
     */
    private function buildMenuPlaceholders(): array
    {
        // Menus are tenant-scoped; skip the lookup when no site is active so
        // the (central) section-template editor still loads for agency admins.
        if (! tenancy()->initialized) {
            return [];
        }

        return Menu::query()
            ->where('is_active', true)
            ->orderBy('location')
            ->orderBy('name')
            ->get(['name', 'slug', 'location'])
            ->flatMap(function (Menu $menu) {
                return collect([$menu->location, $menu->slug])
                    ->filter()
                    ->map(fn (string $key) => $this->normalizePlaceholderKey($key))
                    ->filter()
                    ->unique()
                    ->map(fn (string $key) => [
                        'label' => trim($menu->name.' / '.$key, ' /'),
                        'key' => $key,
                        'html_token' => '{{{menu_'.$key.'_html}}}',
                        'items_token' => '{{{menu_'.$key.'_items_html}}}',
                    ]);
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, token: string, source: string}>
     */
    private function buildSystemPlaceholders(): array
    {
        $fixed = collect([
            ['label' => 'Site adı', 'token' => '{{site_name}}', 'source' => 'system'],
            ['label' => 'Tema slug', 'token' => '{{theme_slug}}', 'source' => 'system'],
            ['label' => 'Site domain', 'token' => '{{site_domain}}', 'source' => 'system'],
            ['label' => 'Telefon', 'token' => '{{phone}}', 'source' => 'settings'],
            ['label' => 'E-posta', 'token' => '{{email}}', 'source' => 'settings'],
            ['label' => 'Adres', 'token' => '{{address}}', 'source' => 'settings'],
            ['label' => 'WhatsApp', 'token' => '{{whatsapp_number}}', 'source' => 'settings'],
            ['label' => 'Çalışma saatleri', 'token' => '{{working_hours}}', 'source' => 'settings'],
            ['label' => 'Vergi no', 'token' => '{{tax_id}}', 'source' => 'settings'],
            ['label' => 'Footer metni', 'token' => '{{footer_text}}', 'source' => 'settings'],
            ['label' => 'Logo URL', 'token' => '{{logo_url}}', 'source' => 'settings'],
            ['label' => 'Favicon URL', 'token' => '{{favicon_url}}', 'source' => 'settings'],
        ]);

        // SiteSetting is tenant-scoped; without an active site, expose only the
        // fixed system tokens so the editor never hits the central DB (no
        // `site_settings` table there → 500).
        if (! tenancy()->initialized) {
            return $fixed->values()->all();
        }

        $settings = SiteSetting::query()
            ->select(['key', 'group'])
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->map(function (SiteSetting $setting) {
                $key = $this->normalizePlaceholderKey((string) $setting->key);

                return [
                    'label' => $setting->key,
                    'token' => '{{'.$key.'}}',
                    'source' => $setting->group ?: 'settings',
                ];
            });

        return $fixed
            ->merge($settings)
            ->unique('token')
            ->values()
            ->all();
    }

    private function normalizePlaceholderKey(string $key): string
    {
        return trim((string) str($key)->lower()->replaceMatches('/[^a-z0-9_]+/', '_'), '_');
    }
}
