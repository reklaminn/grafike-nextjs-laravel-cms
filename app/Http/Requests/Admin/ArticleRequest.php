<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $articleId = $this->route('article')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/',
                Rule::unique('articles', 'slug')->ignore($articleId),
            ],
            'body'         => ['nullable', 'string'],
            'content_json' => ['nullable', 'string'],   // raw JSON from block editor, decoded in controller
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'extra_info' => ['nullable', 'string'],
            'page_id' => ['nullable', 'exists:pages,id'],
            'parent_article_id' => ['nullable', 'exists:articles,id'],
            'language_id' => ['required', Rule::exists('central.languages', 'id')],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'external_url' => ['nullable', 'url', 'max:500'],
            'link_target' => ['nullable', Rule::in(['_self', '_blank'])],
            'content_type_id' => ['nullable', 'integer'],
            'form_id' => ['nullable', 'exists:forms,id'],
            'is_featured' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'display_date' => ['nullable', 'string', 'max:100'],
            'listing_variant' => ['nullable', 'string', 'max:100'],
            'detail_variant' => ['nullable', 'string', 'max:100'],
            'author_id' => ['nullable', 'exists:admins,id'],
            'custom_css' => ['nullable', 'string'],
            'custom_js' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'gallery_images.*' => ['nullable', 'image', 'max:5120'],

            // SEO fields
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'Yazı başlığı',
            'body' => 'İçerik',
            'page_id' => 'Sayfa',
            'language_id' => 'Dil',
            'status' => 'Durum',
            'cover_image' => 'Kapak görseli',
        ];
    }
}
