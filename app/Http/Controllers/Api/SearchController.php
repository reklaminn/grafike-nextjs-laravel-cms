<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/v1/search?q=…&type=page|article&limit=20
 *
 * Tenant sitesi içi arama: yayında olan sayfalar + yazılar.
 * Başlık eşleşmesi içerik eşleşmesinden önce gelir (relevance sıralaması).
 * Frontend'in site içi arama kutusu bu endpoint'i kullanır.
 */
class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $v = $request->validate([
            'q'     => 'required|string|min:2|max:100',
            'type'  => 'nullable|in:page,article',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $q     = trim($v['q']);
        $type  = $v['type'] ?? null;
        $limit = (int) ($v['limit'] ?? 20);
        $like  = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';

        $results = collect();

        if ($type === null || $type === 'page') {
            $pages = Page::query()
                ->where('status', 'published')
                ->whereNull('external_url')
                ->where(fn ($w) => $w
                    ->where('title', 'like', $like)
                    ->orWhere('slug', 'like', $like))
                ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', [$like])
                ->limit($limit)
                ->get(['id', 'title', 'slug', 'updated_at'])
                ->map(fn (Page $page) => [
                    'type'       => 'page',
                    'title'      => $page->title,
                    'slug'       => $page->slug,
                    'url_path'   => '/'.ltrim($page->slug, '/'),
                    'excerpt'    => null,
                    'updated_at' => $page->updated_at?->toIso8601String(),
                ]);

            $results = $results->concat($pages);
        }

        if ($type === null || $type === 'article') {
            $articles = Article::query()
                ->where('status', 'published')
                ->where(fn ($w) => $w
                    ->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('body', 'like', $like))
                ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', [$like])
                ->orderByDesc('published_at')
                ->limit($limit)
                ->with('page:id,slug')
                ->get(['id', 'title', 'slug', 'excerpt', 'page_id', 'published_at'])
                ->map(fn (Article $article) => [
                    'type'         => 'article',
                    'title'        => $article->title,
                    'slug'         => $article->slug,
                    'url_path'     => '/'.trim(($article->page?->slug ?? 'blog').'/'.$article->slug, '/'),
                    'excerpt'      => $article->excerpt ? mb_strimwidth($article->excerpt, 0, 160, '…') : null,
                    'published_at' => $article->published_at?->toIso8601String(),
                ]);

            $results = $results->concat($articles);
        }

        return response()->json([
            'query'   => $q,
            'count'   => $results->count(),
            'results' => $results->take($limit)->values(),
        ]);
    }
}
