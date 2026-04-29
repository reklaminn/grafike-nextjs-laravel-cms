<?php

namespace App\Http\Resources\Api;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Article $article */
        $article = $this->resource;

        $coverUrl   = $article->getFirstMediaUrl('cover');
        $coverThumb = $article->getFirstMedia('cover')?->hasGeneratedConversion('thumb')
            ? $article->getFirstMediaUrl('cover', 'thumb')
            : $coverUrl;

        return [
            'article' => [
                'id'              => $article->id,
                'title'           => $article->title,
                'slug'            => $article->slug,
                'excerpt'         => $article->excerpt,
                'body'            => $article->body,
                'content_json'    => $article->content_json ?? [],
                'display_date'    => $article->display_date?->toDateString()
                    ?? $article->published_at?->toDateString(),
                'published_at'    => $article->published_at?->toAtomString(),
                'updated_at'      => $article->updated_at?->toAtomString(),
                'listing_variant' => $article->listing_variant,
                'detail_variant'  => $article->detail_variant,
                'is_featured'     => (bool) $article->is_featured,
                'cover' => $coverUrl ? [
                    'url'   => $coverUrl,
                    'thumb' => $coverThumb,
                    'alt'   => $article->title,
                ] : null,
                'gallery' => $article->getMedia('gallery')->map(fn ($media) => [
                    'url'   => $media->getUrl(),
                    'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                    'name'  => $media->name,
                    'alt'   => $media->name,
                ])->values(),
            ],
            'author' => $article->author ? [
                'id'   => $article->author->id,
                'name' => $article->author->name,
            ] : null,
            'language' => $article->language ? [
                'id'     => $article->language->id,
                'code'   => $article->language->code,
                'locale' => $article->language->locale,
                'name'   => $article->language->name,
            ] : null,
            'page' => [
                'id'    => $article->page?->id,
                'title' => $article->page?->title,
                'slug'  => $article->page?->slug,
            ],
            'seo' => [
                'title'           => $article->seo?->meta_title       ?: $article->title,
                'description'     => $article->seo?->meta_description ?: ($article->excerpt ?: ''),
                'canonical'       => $article->seo?->canonical_url     ?: url($article->slug),
                'noindex'         => (bool) ($article->seo?->is_noindex ?? false),
                'og_image'        => $article->seo?->og_image ?: ($article->getFirstMediaUrl('cover') ?: null),
                'og_type'         => $article->seo?->og_type  ?: 'article',
                'hreflang_tags'   => $article->seo?->hreflang_tags   ?: [],
                'structured_data' => $article->seo?->structured_data ?: null,
            ],
        ];
    }
}
