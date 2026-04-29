<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\AddsHttpCacheHeaders;
use App\Http\Controllers\Api\Concerns\ResolvesApiLanguage;
use App\Http\Resources\Api\ArticleCollection;
use App\Http\Resources\Api\ArticleResource;
use App\Models\Article;
use App\Services\SeoManager\SeoManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    use AddsHttpCacheHeaders, ResolvesApiLanguage;

    public function __construct(protected SeoManager $seoManager) {}

    public function index(Request $request): ArticleCollection
    {
        $perPage  = min(max((int) $request->integer('limit', 12), 1), 50);
        $siteId   = $request->integer('site_id') ?: null;
        $pageId   = $request->integer('page_id') ?: null;
        $language = $this->resolveLanguage($request->query('lang'));
        $featured = $request->boolean('featured_only');

        $cacheEnabled = config('cms.cache.enabled', true);
        $cacheTtl     = (int) config('cms.cache.ttl', 600);
        $cacheKey     = 'api.articles.' . md5(serialize($request->only(
            'limit', 'page', 'site_id', 'page_id', 'lang', 'featured_only'
        )));

        $paginator = $cacheEnabled
            ? Cache::remember($cacheKey, $cacheTtl, fn () => $this->buildQuery($siteId, $pageId, $language?->id, $featured)->paginate($perPage)->withQueryString())
            : $this->buildQuery($siteId, $pageId, $language?->id, $featured)->paginate($perPage)->withQueryString();

        return new ArticleCollection($paginator);
    }

    public function show(string $slug)
    {
        $resolved = $this->seoManager->resolve($slug);

        abort_if(! $resolved, 404);

        if (($resolved['type'] ?? null) === 'redirect') {
            return response()->json([
                'type'        => 'redirect',
                'url'         => $resolved['url'],
                'status_code' => $resolved['status_code'],
            ]);
        }

        $entity = $resolved['entity'] ?? null;
        abort_if(! $entity instanceof Article, 404);
        abort_if($entity->status !== 'published', 404);

        $entity->loadMissing(['page', 'seo', 'language', 'author', 'media']);

        $lastModified = $entity->seo?->updated_at ?? $entity->updated_at;

        return $this->cachedResponse(
            ArticleResource::make($entity),
            $lastModified,
            "article-{$entity->id}",
        );
    }

    // ─────────────────────────────────────────────────────────────────────

    private function buildQuery(?int $siteId, ?int $pageId, ?int $languageId, bool $featured)
    {
        return Article::query()
            ->published()
            ->with(['page', 'language', 'author', 'media'])
            ->when($siteId,     fn ($q) => $q->where('site_id', $siteId))
            ->when($pageId,     fn ($q) => $q->where('page_id', $pageId))
            ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
            ->when($featured,   fn ($q) => $q->featured())
            ->orderByDesc('published_at')
            ->orderBy('sort_order');
    }
}
