<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TranslationController extends Controller
{
    /**
     * Translation coverage dashboard.
     * Shows pages and articles that are missing translations in active languages.
     */
    public function index(Request $request)
    {
        $languages  = Language::where('is_active', true)->orderBy('sort_order')->get();
        $type       = $request->input('type', 'pages'); // pages | articles
        $langFilter = $request->input('missing_lang'); // show only items missing this lang code

        if ($type === 'articles') {
            $items = $this->articleMatrix($languages, $langFilter);
        } else {
            $items = $this->pageMatrix($languages, $langFilter);
        }

        return view('admin.translations.index', compact('languages', 'type', 'items', 'langFilter'));
    }

    /**
     * Bulk translate: creates translation records for selected items.
     * POST /admin/translations/bulk
     *
     * Body: { type: 'page'|'article', ids: int[], target_language_id: int }
     *
     * Each item is created as a draft copy with a flag to indicate AI translation is needed.
     * The actual text translation is done client-side via the AI AJAX endpoint.
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'type'               => 'required|in:page,article',
            'ids'                => 'required|array|min:1|max:50',
            'ids.*'              => 'integer',
            'target_language_id' => ['required', Rule::exists('central.languages', 'id')],
        ]);

        $targetLangId = $request->integer('target_language_id');
        $targetLang   = Language::findOrFail($targetLangId);
        $created      = 0;
        $skipped      = 0;

        if ($request->type === 'page') {
            foreach ($request->ids as $id) {
                $source = Page::find($id);
                if (! $source) { $skipped++; continue; }

                // Check if translation already exists
                $exists = Page::where('root_page_id', $source->root_page_id ?? $source->id)
                    ->where('language_id', $targetLangId)
                    ->exists();

                if ($exists) { $skipped++; continue; }

                // Create translation draft — same content, target language, draft status
                $rootId = $source->root_page_id ?? $source->id;

                Page::create([
                    'title'        => "[{$targetLang->code}] " . $source->title,
                    'slug'         => $source->slug . '-' . $targetLang->code,
                    'language_id'  => $targetLangId,
                    'root_page_id' => $rootId,
                    'parent_id'    => null,
                    'status'       => 'draft',
                    'sort_order'   => $source->sort_order,
                    'show_in_menu' => false,
                    'sections_json' => $source->sections_json,
                    'layout_json'  => $source->layout_json,
                ]);

                $created++;
            }
        } else {
            foreach ($request->ids as $id) {
                $source = Article::find($id);
                if (! $source) { $skipped++; continue; }

                $exists = Article::where('parent_article_id', $id)
                    ->orWhere('id', $id)
                    ->where('language_id', $targetLangId)
                    ->exists();

                if ($exists) { $skipped++; continue; }

                Article::create([
                    'title'             => "[{$targetLang->code}] " . $source->title,
                    'slug'              => $source->slug . '-' . $targetLang->code,
                    'language_id'       => $targetLangId,
                    'parent_article_id' => $id,
                    'page_id'           => $source->page_id,
                    'status'            => 'draft',
                    'body'              => $source->body,
                    'excerpt'           => $source->excerpt,
                    'content_json'      => $source->content_json,
                    'sort_order'        => $source->sort_order,
                    'author_id'         => auth('admin')->id(),
                ]);

                $created++;
            }
        }

        return redirect()
            ->route('admin.translations.index', ['type' => $request->type])
            ->with('success', "{$created} taslak çeviri oluşturuldu" . ($skipped ? ", {$skipped} zaten mevcut atlandı." : '.'));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function pageMatrix(mixed $languages, ?string $missingLangCode): array
    {
        // Load root pages (root_page_id = id, i.e. the canonical versions)
        $pages = Page::with(['language', 'translations.language'])
            ->whereColumn('root_page_id', 'id')
            ->orWhereNull('root_page_id')
            ->orderBy('title')
            ->get();

        return $pages->map(function (Page $page) use ($languages, $missingLangCode) {
            $existingLangIds = $page->translations->pluck('language_id')
                ->push($page->language_id)
                ->unique();

            $missing = $languages->whereNotIn('id', $existingLangIds)->values();

            if ($missingLangCode) {
                $filterLang = $languages->firstWhere('code', $missingLangCode);
                if ($filterLang && ! $missing->contains('id', $filterLang->id)) {
                    return null; // Has this translation — skip
                }
            }

            return [
                'id'              => $page->id,
                'title'           => $page->title,
                'language'        => $page->language,
                'translations'    => $page->translations,
                'missing'         => $missing,
                'edit_url'        => route('admin.pages.edit', $page),
                'translate_url'   => route('admin.pages.create-translation', $page),
            ];
        })->filter()->values()->all();
    }

    private function articleMatrix(mixed $languages, ?string $missingLangCode): array
    {
        // Load source articles (parent_article_id = null, i.e. originals)
        $articles = Article::with(['language', 'translations.language', 'page'])
            ->whereNull('parent_article_id')
            ->orderByDesc('published_at')
            ->get();

        return $articles->map(function (Article $article) use ($languages, $missingLangCode) {
            $existingLangIds = $article->translations->pluck('language_id')
                ->push($article->language_id)
                ->unique();

            $missing = $languages->whereNotIn('id', $existingLangIds)->values();

            if ($missingLangCode) {
                $filterLang = $languages->firstWhere('code', $missingLangCode);
                if ($filterLang && ! $missing->contains('id', $filterLang->id)) {
                    return null;
                }
            }

            return [
                'id'              => $article->id,
                'title'           => $article->title,
                'language'        => $article->language,
                'translations'    => $article->translations,
                'missing'         => $missing,
                'page'            => $article->page,
                'edit_url'        => route('admin.articles.edit', $article),
                'translate_url'   => route('admin.articles.create-translation', $article),
            ];
        })->filter()->values()->all();
    }
}
