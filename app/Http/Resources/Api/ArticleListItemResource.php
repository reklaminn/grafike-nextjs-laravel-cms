<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleListItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $coverUrl   = $this->getFirstMediaUrl('cover');
        $coverThumb = $this->getFirstMedia('cover')?->hasGeneratedConversion('thumb')
            ? $this->getFirstMediaUrl('cover', 'thumb')
            : $coverUrl;

        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'slug'         => $this->slug,
            'excerpt'      => $this->excerpt,
            // products-grid (estetikdermal) — Article kolonları (yoksa null).
            'category'     => $this->category,
            'brand'        => $this->brand,
            'display_date' => $this->display_date?->toDateString()
                ?? $this->published_at?->toDateString(),
            'published_at' => $this->published_at?->toAtomString(),
            'is_featured'  => (bool) $this->is_featured,
            'cover'        => $coverUrl ? [
                'url'   => $coverUrl,
                'thumb' => $coverThumb,
                'alt'   => $this->title,
            ] : null,
            'author'   => $this->whenLoaded('author', fn () => $this->author ? [
                'id'   => $this->author->id,
                'name' => $this->author->name,
            ] : null),
            'language' => $this->whenLoaded('language', fn () => $this->language ? [
                'id'   => $this->language->id,
                'code' => $this->language->code,
            ] : null),
            'page' => $this->whenLoaded('page', fn () => $this->page ? [
                'id'    => $this->page->id,
                'title' => $this->page->title,
                'slug'  => $this->page->slug,
            ] : null),
        ];
    }
}
