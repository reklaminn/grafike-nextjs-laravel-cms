<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Form;
use App\Models\Page;
use App\Models\SectionTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /admin/search?q=…
 *
 * Admin header'daki global arama kutusu. Aktif tenant'ın sayfa / yazı /
 * form kayıtlarında ve block şablonu kataloğunda arar; gruplu JSON döner.
 * Tenant tabloları aktif site gerektirir — site seçilmemişse yalnızca
 * katalog (şablonlar) aranır.
 */
class GlobalSearchController extends Controller
{
    private const LIMIT_PER_GROUP = 5;

    public function __invoke(Request $request): JsonResponse
    {
        $v = $request->validate(['q' => 'required|string|min:2|max:100']);

        $q    = trim($v['q']);
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';

        $groups = [];

        // ── Tenant tabloları (aktif site varsa) ───────────────────────────
        if (tenancy()->initialized || session('active_tenant')) {
            try {
                $groups['pages'] = Page::query()
                    ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like))
                    ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', [$like])
                    ->limit(self::LIMIT_PER_GROUP)
                    ->get(['id', 'title', 'slug', 'status'])
                    ->map(fn (Page $page) => [
                        'label'    => $page->title,
                        'sublabel' => '/'.$page->slug.' · '.($page->status === 'published' ? 'yayında' : $page->status),
                        'url'      => route('admin.pages.edit', $page, false),
                    ])
                    ->values()->all();

                $groups['articles'] = Article::query()
                    ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like))
                    ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', [$like])
                    ->limit(self::LIMIT_PER_GROUP)
                    ->get(['id', 'title', 'slug', 'status'])
                    ->map(fn (Article $article) => [
                        'label'    => $article->title,
                        'sublabel' => '/'.$article->slug,
                        'url'      => route('admin.articles.edit', $article, false),
                    ])
                    ->values()->all();

                $groups['forms'] = Form::query()
                    ->where('name', 'like', $like)
                    ->limit(self::LIMIT_PER_GROUP)
                    ->get(['id', 'name'])
                    ->map(fn (Form $form) => [
                        'label'    => $form->name,
                        'sublabel' => 'form',
                        'url'      => route('admin.forms.edit', $form, false),
                    ])
                    ->values()->all();
            } catch (\Throwable) {
                // Tenant DB erişilemiyorsa (site seçilmemiş vb.) sessizce geç
            }
        }

        // ── Block şablonu kataloğu (central) ──────────────────────────────
        $groups['templates'] = SectionTemplate::query()
            ->visibleTo(session('active_tenant'))
            ->where(fn ($w) => $w
                ->where('name', 'like', $like)
                ->orWhere('type', 'like', $like)
                ->orWhere('variation', 'like', $like))
            ->limit(self::LIMIT_PER_GROUP)
            ->get(['id', 'name', 'type', 'variation'])
            ->map(fn (SectionTemplate $template) => [
                'label'    => $template->name,
                'sublabel' => $template->type.'/'.$template->variation,
                'url'      => route('admin.section-templates.edit', $template, false),
            ])
            ->values()->all();

        // Boş grupları at
        $groups = array_filter($groups, fn (array $rows) => $rows !== []);

        return response()->json([
            'query'  => $q,
            'groups' => $groups,
        ]);
    }
}
