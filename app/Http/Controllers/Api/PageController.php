<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\AddsHttpCacheHeaders;
use App\Http\Resources\Api\PageResource;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Services\SeoManager\SeoManager;

class PageController extends Controller
{
    use AddsHttpCacheHeaders;

    public function __construct(protected SeoManager $seoManager) {}

    public function show(string $slug)
    {
        // Tenant context is established by stancl middleware (domain-based).
        // All Page queries automatically run on the current tenant's DB.
        // No site_id filter needed — the DB connection is the isolation boundary.

        if ($slug === 'home') {
            $homepageId = SiteSetting::get('cms.homepage_id', config('cms.homepage_id'));
            $page = Page::query()
                ->where(fn ($query) => $query
                    ->where('legacy_id', $homepageId)
                    ->orWhere('id', $homepageId)
                )
                ->published()
                ->with(['seo', 'language', 'parent'])
                ->first();

            abort_if(! $page, 404);

            return $this->pageResponse($page);
        }

        $resolved = $this->seoManager->resolve($slug);

        if (! $resolved) {
            $page = Page::query()
                ->where('slug', $slug)
                ->published()
                ->with(['seo', 'language', 'parent'])
                ->first();

            abort_if(! $page, 404);

            return $this->pageResponse($page);
        }

        if (($resolved['type'] ?? null) === 'redirect') {
            return response()->json([
                'type'        => 'redirect',
                'url'         => $resolved['url'],
                'status_code' => $resolved['status_code'],
            ]);
        }

        $entity = $resolved['entity'] ?? null;
        abort_if(! $entity instanceof Page, 404);
        abort_if($entity->status !== 'published', 404);

        $entity->loadMissing(['seo', 'language', 'parent']);

        return $this->pageResponse($entity);
    }

    private function pageResponse(Page $page)
    {
        $lastModified = $page->seo?->updated_at ?? $page->updated_at;

        return $this->cachedResponse(
            PageResource::make($page),
            $lastModified,
            "page-{$page->id}",
        );
    }
}
