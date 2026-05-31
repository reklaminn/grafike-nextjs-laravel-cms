<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Page;
use App\Models\SeoEntry;
use App\Services\Seo\HreflangGenerator;
use App\Services\Seo\StructuredDataGenerator;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function __construct(
        protected StructuredDataGenerator $sdGenerator,
        protected HreflangGenerator $hreflangGenerator,
    ) {}

    public function index(Request $request)
    {
        $query = SeoEntry::with('seoable')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                    ->orWhere('meta_title', 'like', "%{$search}%");
            });
        }

        if ($request->input('noindex') === '1') {
            $query->where('is_noindex', true);
        }

        $seoEntries = $query->paginate(20)->withQueryString();

        return view('admin.seo.index', compact('seoEntries'));
    }

    public function edit(SeoEntry $seoEntry)
    {
        $seoEntry->load('seoable');
        $schemaTypes = StructuredDataGenerator::availableTypes();

        return view('admin.seo.edit', compact('seoEntry', 'schemaTypes'));
    }

    public function update(Request $request, SeoEntry $seoEntry)
    {
        $validated = $request->validate([
            'slug'              => 'required|string|max:255|unique:seo_entries,slug,' . $seoEntry->id,
            'meta_title'        => 'nullable|string|max:70',
            'meta_description'  => 'nullable|string|max:160',
            'meta_keywords'     => 'nullable|string|max:255',
            'h1_override'       => 'nullable|string|max:255',
            'canonical_url'     => 'nullable|url|max:500',
            'og_image'          => 'nullable|url|max:500',
            'og_type'           => 'nullable|string|in:website,article,product,service',
            'is_noindex'        => 'boolean',
            'page_css'          => 'nullable|string|max:10000',
            'page_js'           => 'nullable|string|max:10000',
            'schema_type'       => 'nullable|string|max:50',
            'structured_data'   => 'nullable|string',   // JSON string from editor
            'hreflang_tags'     => 'nullable|string',   // JSON string
            'sitemap_priority'  => 'nullable|numeric|min:0|max:1',
            'sitemap_changefreq'=> 'nullable|string|in:always,hourly,daily,weekly,monthly,yearly,never',
            'sitemap_exclude'   => 'boolean',
        ]);

        $validated['is_noindex']      = $request->boolean('is_noindex');
        $validated['sitemap_exclude'] = $request->boolean('sitemap_exclude');

        // Decode JSON fields
        if (isset($validated['structured_data']) && $validated['structured_data']) {
            $decoded = json_decode($validated['structured_data'], true);
            $validated['structured_data'] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        if (isset($validated['hreflang_tags']) && $validated['hreflang_tags']) {
            $decoded = json_decode($validated['hreflang_tags'], true);
            $validated['hreflang_tags'] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        $oldSlug = $seoEntry->slug;
        $seoEntry->update($validated);

        // Slug değiştiyse ilgili sayfa/yazının slug'ını da güncelle
        if ($validated['slug'] !== $oldSlug) {
            $seoable = $seoEntry->seoable;
            if ($seoable instanceof Page || $seoable instanceof Article) {
                // updateQuietly → observer tetiklenmesin (sonsuz döngü olmasın)
                $seoable->updateQuietly(['slug' => $validated['slug']]);
            }
        }

        return redirect()->route('admin.seo.index')
            ->with('success', 'SEO kaydı güncellendi.');
    }

    public function destroy(SeoEntry $seoEntry)
    {
        $seoEntry->delete();

        return redirect()->route('admin.seo.index')
            ->with('success', 'SEO kaydı silindi.');
    }

    // ─── AJAX: Generate Structured Data ───────────────────────────────────────

    public function generateStructuredData(Request $request, SeoEntry $seoEntry): \Illuminate\Http\JsonResponse
    {
        $schemaType = $request->input('schema_type', 'WebPage');
        $seoEntry->load('seoable');
        $entity = $seoEntry->seoable;

        if ($entity instanceof Page) {
            $schema = $this->sdGenerator->forPage($entity, $schemaType);
        } elseif ($entity instanceof Article) {
            $schema = $this->sdGenerator->forArticle($entity, $schemaType);
        } else {
            return response()->json(['error' => 'Desteklenmeyen içerik türü'], 422);
        }

        return response()->json([
            'schema'     => $schema,
            'schema_json' => json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    // ─── AJAX: Generate Hreflang ───────────────────────────────────────────────

    public function generateHreflang(SeoEntry $seoEntry): \Illuminate\Http\JsonResponse
    {
        $seoEntry->load('seoable');
        $entity = $seoEntry->seoable;

        if ($entity instanceof Page) {
            $tags = $this->hreflangGenerator->forPage($entity);
        } elseif ($entity instanceof Article) {
            $tags = $this->hreflangGenerator->forArticle($entity);
        } else {
            return response()->json(['error' => 'Desteklenmeyen içerik türü'], 422);
        }

        if (empty($tags)) {
            return response()->json([
                'tags'      => [],
                'tags_json' => '{}',
                'message'   => 'Bu içeriğin başka dil sürümü bulunamadı. Sayfaları root_page_id ile bağlayın.',
            ]);
        }

        return response()->json([
            'tags'      => $tags,
            'tags_json' => json_encode($tags, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    // ─── Bulk Analysis ────────────────────────────────────────────────────────

    public function bulkAnalysis()
    {
        $entries = SeoEntry::with('seoable')->get();

        $issues = [];
        foreach ($entries as $entry) {
            $entryIssues = [];

            if (empty($entry->meta_title)) {
                $entryIssues[] = 'Meta title eksik';
            } elseif (mb_strlen($entry->meta_title) > 60) {
                $entryIssues[] = 'Meta title 60 karakterden uzun';
            }

            if (empty($entry->meta_description)) {
                $entryIssues[] = 'Meta description eksik';
            } elseif (mb_strlen($entry->meta_description) > 155) {
                $entryIssues[] = 'Meta description 155 karakterden uzun';
            }

            if (empty($entry->slug)) {
                $entryIssues[] = 'Slug eksik';
            }

            if (! empty($entryIssues)) {
                $issues[] = [
                    'entry'  => $entry,
                    'issues' => $entryIssues,
                ];
            }
        }

        $stats = [
            'total'               => $entries->count(),
            'noindex'             => $entries->where('is_noindex', true)->count(),
            'missing_title'       => $entries->where('meta_title', null)->count(),
            'missing_description' => $entries->where('meta_description', null)->count(),
            'with_issues'         => count($issues),
        ];

        return view('admin.seo.analysis', compact('issues', 'stats'));
    }
}
