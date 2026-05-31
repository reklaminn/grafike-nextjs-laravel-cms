<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Article;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\SectionTemplate;
use App\Models\SeoEntry;
use App\Models\SiteSetting;
use App\Services\Ai\AiPageGenerator;
use App\Services\Ai\Exceptions\AiQuotaExceededException;
use App\Services\Ai\SiteContextBuilder;
use App\Support\FrontendSections;
use App\Support\LegacyLayoutToSections;
use App\Support\PageEditorData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $query = Page::with(['language', 'parent', 'seo'])
            ->withCount('articles');

        // Search
        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by language
        if ($langId = $request->input('language_id')) {
            $query->where('language_id', $langId);
        }

        // Filter by parent (show only root pages or children of a specific page)
        if ($request->has('parent_id')) {
            $parentId = $request->input('parent_id');
            $query->where('parent_id', $parentId ?: null);
        }

        $pages = $query->orderBy('sort_order')->orderBy('title')->paginate(25);
        $languages = Language::where('is_active', true)->get();

        return view('admin.pages.index', compact('pages', 'languages'));
    }

    public function create()
    {
        $languages = Language::where('is_active', true)->get();
        $parentPages = Page::whereNull('parent_id')
            ->orderBy('title')
            ->get(['id', 'title', 'language_id']);
        // Block picker = global (shared) blocks + this tenant's own blocks,
        // optionally narrowed to the tenant's active theme (matches edit()).
        $tenantThemeId = tenancy()->tenant?->theme_id;
        $availableFrontendSectionTemplates = SectionTemplate::query()
            ->visibleTo(session('active_tenant'))
            ->visibleForModules(tenancy()->tenant?->enabledModules())
            ->when($tenantThemeId, fn ($query, $themeId) => $query->where('theme_id', $themeId))
            ->active()
            ->orderBy('name')
            ->get()
            ->values();

        $editorData = PageEditorData::for(null, $availableFrontendSectionTemplates);

        $homepageId   = SiteSetting::get('cms.homepage_id');
        $memberGroups = \App\Models\MemberGroup::where('is_active', true)->orderBy('name')->get();

        return view('admin.pages.create', compact('languages', 'parentPages', 'editorData', 'homepageId', 'memberGroups'));
    }

    public function store(PageRequest $request)
    {
        $data = $request->validated();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        // Handle boolean fields
        $data['show_in_menu'] = $request->boolean('show_in_menu');
        $data['is_password_protected'] = $request->boolean('is_password_protected');
        $data['show_social_share'] = $request->boolean('show_social_share');
        $data['show_facebook_comments'] = $request->boolean('show_facebook_comments');
        $data['show_breadcrumb'] = $request->boolean('show_breadcrumb');

        // Parse layout_json — if empty, remove from data so existing value is preserved
        if (!empty($data['layout_json'])) {
            $data['layout_json'] = json_decode($data['layout_json'], true);
        } else {
            unset($data['layout_json']);
        }

        // Parse sections_json — if empty/null (e.g. JS didn't run, redirect ate POST body),
        // remove from data so existing DB value is NOT overwritten with null.
        if (!empty($data['sections_json'])) {
            $data['sections_json'] = json_decode($data['sections_json'], true);
        } else {
            unset($data['sections_json']);
        }

        unset($data['sections_json_dirty']);

        $page = Page::create($data);

        // If root_page_id was not provided, this page IS the root of its own translation group
        if (empty($page->root_page_id)) {
            $page->updateQuietly(['root_page_id' => $page->id]);
        }

        // Handle cover image
        if ($request->hasFile('cover_image')) {
            $page->addMediaFromRequest('cover_image')
                ->toMediaCollection('cover');
        }

        // Handle SEO
        $this->saveSeo($page, $request);
        $this->syncHomepageSetting($page, $request);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Sayfa başarıyla oluşturuldu.');
    }

    public function edit(string $page)
    {
        $page = $this->resolveTenantPage($page);

        $page->load(['language', 'parent', 'seo', 'children', 'media', 'translations.language']);

        $languages = Language::where('is_active', true)->get();
        $parentPages = Page::where('id', '!=', $page->id)
            ->whereNull('parent_id')
            ->orderBy('title')
            ->get(['id', 'title', 'language_id']);

        $sectionTemplateIds = FrontendSections::collectTemplateIds($page->sections_json);

        $frontendSectionTemplates = SectionTemplate::query()
            ->whereIn('id', $sectionTemplateIds)
            ->get()
            ->keyBy('id');

        // In tenant context the current tenant's theme_id filters section templates.
        // tenancy()->tenant is the Tenant model instance set by stancl middleware.
        $tenantThemeId = tenancy()->tenant?->theme_id;

        $availableFrontendSectionTemplates = SectionTemplate::query()
            ->visibleTo(session('active_tenant'))
            ->visibleForModules(tenancy()->tenant?->enabledModules())
            ->when($tenantThemeId, fn ($query, $themeId) => $query->where('theme_id', $themeId))
            ->active()
            ->orderBy('name')
            ->get()
            ->values();

        // Articles live in the tenant DB — no site() relation needed.
        $siteArticles = Article::latest('published_at')->limit(10)->get();

        $frontendEditorSections = FrontendSections::flattenBlocks($page->sections_json);
        $frontendRegions = FrontendSections::normalize($page->sections_json);
        $editorData   = PageEditorData::for($page, $availableFrontendSectionTemplates);
        $homepageId   = SiteSetting::get('cms.homepage_id');
        $memberGroups = \App\Models\MemberGroup::where('is_active', true)->orderBy('name')->get();

        return view('admin.pages.edit', compact(
            'page',
            'languages',
            'parentPages',
            'frontendSectionTemplates',
            'availableFrontendSectionTemplates',
            'siteArticles',
            'frontendEditorSections',
            'frontendRegions',
            'editorData',
            'homepageId',
            'memberGroups'
        ));
    }

    public function update(PageRequest $request, string $page)
    {
        $page = $this->resolveTenantPage($page);
        $data = $request->validated();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $page->id);
        }

        // Handle boolean fields
        $data['show_in_menu'] = $request->boolean('show_in_menu');
        $data['is_password_protected'] = $request->boolean('is_password_protected');
        $data['show_social_share'] = $request->boolean('show_social_share');
        $data['show_facebook_comments'] = $request->boolean('show_facebook_comments');
        $data['show_breadcrumb'] = $request->boolean('show_breadcrumb');

        // Parse layout_json — if empty, remove from data so existing value is preserved
        if (!empty($data['layout_json'])) {
            $data['layout_json'] = json_decode($data['layout_json'], true);
        } else {
            unset($data['layout_json']);
        }

        // Parse sections_json. If the editor reports "not dirty" but the submitted
        // payload is empty while the DB has blocks, preserve the existing content.
        // This protects pages from Alpine/form boundary glitches during plain saves.
        if (!empty($data['sections_json'])) {
            $decodedSections = json_decode($data['sections_json'], true);
            $incomingHasBlocks = count(FrontendSections::flattenBlocks($decodedSections)) > 0;
            $existingHasBlocks = count(FrontendSections::flattenBlocks($page->sections_json)) > 0;

            if (! $request->boolean('sections_json_dirty') && ! $incomingHasBlocks && $existingHasBlocks) {
                unset($data['sections_json']);
            } else {
                $data['sections_json'] = $decodedSections;
            }
        } else {
            unset($data['sections_json']);
        }

        unset($data['sections_json_dirty']);

        $page->update($data);

        // Handle cover image
        if ($request->hasFile('cover_image')) {
            $page->clearMediaCollection('cover');
            $page->addMediaFromRequest('cover_image')
                ->toMediaCollection('cover');
        }

        // Handle SEO
        $this->saveSeo($page, $request);
        $this->syncHomepageSetting($page, $request);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Sayfa başarıyla güncellendi.')
            ->with('preview_refresh', now()->timestamp);
    }

    // ─── Translation ─────────────────────────────────────────────────────────

    /**
     * Show the "create translation" form pre-filled with source page data.
     * GET /admin/pages/{page}/create-translation?lang={language_id}
     */
    public function createTranslation(string $page, Request $request)
    {
        $page = $this->resolveTenantPage($page);

        $page->load(['language', 'seo', 'translations.language']);

        $languages = Language::where('is_active', true)->get();
        $parentPages = Page::whereNull('parent_id')
            ->orderBy('title')
            ->get(['id', 'title', 'language_id']);

        $availableFrontendSectionTemplates = SectionTemplate::query()
            ->when($page->site?->theme_id, fn ($q, $id) => $q->where('theme_id', $id))
            ->active()->orderBy('name')->get()->values();

        // Languages that already have a translation
        $usedLanguageIds = $page->translations->pluck('language_id')
            ->push($page->language_id)
            ->unique();

        $availableLanguages = $languages->whereNotIn('id', $usedLanguageIds)->values();

        // Pre-select language from query string
        $targetLanguageId = $request->integer('lang') ?: $availableLanguages->first()?->id;

        $editorData = PageEditorData::for(null, $availableFrontendSectionTemplates);

        return view('admin.pages.create-translation', compact(
            'page',
            'languages',
            'availableLanguages',
            'targetLanguageId',
            'parentPages',
            'editorData',
        ));
    }

    public function destroy(string $page)
    {
        $page = $this->resolveTenantPage($page);

        if ($page->isSystemPage()) {
            return back()->with('error', 'Bu sistem sayfası silinemez. Tasarımını ve içeriğini sayfa düzenleme ekranından güncelleyebilirsiniz.');
        }

        // Soft delete - children will become orphaned, warn user
        if ($page->children()->count() > 0) {
            return back()->with('error', 'Bu sayfanın alt sayfaları var. Önce alt sayfaları silin veya taşıyın.');
        }

        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Sayfa başarıyla silindi.');
    }

    public function migrateToSections(string $page)
    {
        $page = $this->resolveTenantPage($page);

        if (empty($page->layout_json) || ! is_array($page->layout_json)) {
            return back()->with('error', 'Bu sayfada dönüştürülecek legacy layout verisi bulunmuyor.');
        }

        Page::recordSnapshot($page, 'before-legacy-migration');

        $sections = LegacyLayoutToSections::convert($page, $page->site?->theme);

        $page->forceFill([
            'sections_json' => $sections,
        ])->saveQuietly();

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Sayfa yeni builder yapısına dönüştürüldü. Artık Next.js builder tarafında düzenlenebilir.')
            ->with('preview_refresh', now()->timestamp);
    }

    public function migratePreview(string $page)
    {
        $page = $this->resolveTenantPage($page);

        if (empty($page->layout_json) || ! is_array($page->layout_json)) {
            return response()->json(['error' => 'Bu sayfada dönüştürülecek legacy layout verisi bulunmuyor.'], 422);
        }

        return response()->json([
            'current_sections'  => $page->sections_json ?? [],
            'preview_sections'  => LegacyLayoutToSections::convert($page, $page->site?->theme),
        ]);
    }

    public function restoreRevision(string $page, string $revision)
    {
        $page = $this->resolveTenantPage($page);
        $revision = PageRevision::findOrFail($revision);

        abort_unless($revision->page_id === $page->id, 404);

        Page::recordSnapshot($page, "restore-from-revision-{$revision->id}");

        $page->forceFill([
            'sections_json' => $revision->snapshot['sections_json'] ?? null,
            'layout_json'   => $revision->snapshot['layout_json'] ?? null,
        ])->saveQuietly();

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', "Revizyon #{$revision->id} geri yüklendi.")
            ->with('preview_refresh', now()->timestamp);
    }

    /**
     * Reorder pages via AJAX.
     */
    public function reorder(Request $request)
    {
        $request->validate(['items' => 'required|array']);

        foreach ($request->items as $index => $item) {
            Page::where('id', $item['id'])->update([
                'sort_order' => $index,
                'parent_id' => $item['parent_id'] ?? null,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Save/update SEO entry for a page.
     */
    protected function saveSeo(Page $page, Request $request): void
    {
        if ($request->filled('seo_title') || $request->filled('seo_description') || $request->filled('seo_keywords')) {
            $page->seo()->updateOrCreate(
                ['seoable_id' => $page->id, 'seoable_type' => Page::class],
                [
                    'slug' => $page->slug,
                    'language_id' => $page->language_id,
                    'meta_title' => $request->input('seo_title'),
                    'meta_description' => $request->input('seo_description'),
                    'meta_keywords' => $request->input('seo_keywords'),
                    'h1_override' => $request->input('seo_h1'),
                    'canonical_url' => $request->input('seo_canonical'),
                    'is_noindex' => $request->boolean('seo_noindex'),
                ]
            );
        }
    }

    protected function syncHomepageSetting(Page $page, Request $request): void
    {
        if ($request->boolean('is_homepage')) {
            SiteSetting::set('cms.homepage_id', (string) $page->id, 'cms');
        }
    }

    protected function resolveTenantPage(string|int $page): Page
    {
        return Page::query()->findOrFail($page);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /admin/pages/{page}/ai-generate-blocks
     *
     * Mevcut sayfanın içeriğini (sections_json) AI ile doldurur.
     * Sadece sections_json güncellenir — başlık/slug/durum değişmez.
     */
    public function aiGenerateBlocks(
        string $page,
        Request $request,
        AiPageGenerator $generator,
        SiteContextBuilder $contextBuilder,
    ): JsonResponse {
        $pageModel = Page::findOrFail($page);
        $tenant    = tenancy()->initialized ? tenant() : null;
        $locale    = $request->input('locale', 'tr');

        try {
            $siteContext = $contextBuilder->build();
        } catch (Throwable) {
            $siteContext = [];
        }

        $purpose = trim((string) $request->input('purpose', ''));
        $prompt  = "\"{$pageModel->title}\" sayfası";
        if ($purpose !== '') {
            $prompt .= ". {$purpose}";
        }
        if (! empty($siteContext['company_name'])) {
            $prompt .= ". Firma: {$siteContext['company_name']}";
            if (! empty($siteContext['sector'])) {
                $prompt .= " ({$siteContext['sector']})";
            }
        }

        try {
            $result = $generator->generate(
                prompt:      $prompt,
                tenant:      $tenant,
                locale:      $locale,
                siteContext: $siteContext,
            );
        } catch (AiQuotaExceededException $e) {
            return response()->json([
                'ok'         => false,
                'error_code' => 'quota_exceeded',
                'message'    => $e->getMessage(),
            ], 402);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }

        $pageModel->update(['sections_json' => $result['sections_json']]);

        return response()->json([
            'ok'          => true,
            'block_count' => count($result['picked_template_ids'] ?? []),
            'message'     => count($result['picked_template_ids'] ?? []) . ' blok oluşturuldu.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate a unique slug with Turkish character support.
     */
    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $turkishMap = [
            'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g',
            'ı' => 'i', 'İ' => 'i', 'ö' => 'o', 'Ö' => 'o',
            'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u',
        ];

        $slug = Str::slug(strtr($title, $turkishMap));

        $original = $slug;
        $counter = 1;
        $query = Page::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $original . '-' . $counter++;
            $query = Page::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}
