<?php

namespace App\Http\Requests\Admin;

use App\Models\SectionTemplate;
use App\Support\FrontendSections;
use App\Support\FrontendSectionSchemaValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled by middleware
    }

    public function rules(): array
    {
        $routePage = $this->route('page');
        $pageId = $routePage instanceof \App\Models\Page ? $routePage->id : $routePage;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/',
                // DB unique index: (slug, language_id) composite — aynı dilde çakışmayı engelle
                Rule::unique('pages', 'slug')
                    ->ignore($pageId)
                    ->where('language_id', $this->input('language_id')),
            ],
            'parent_id' => ['nullable', 'exists:pages,id'],
            'root_page_id' => ['nullable', 'exists:pages,id'],
            'language_id' => ['required', Rule::exists('central.languages', 'id')],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'template' => ['nullable', 'string', 'max:100'],
            'page_template' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'show_in_menu' => ['boolean'],
            'is_password_protected'  => ['boolean'],
            'page_password'          => ['nullable', 'string', 'max:255'],
            'allowed_group_ids'      => ['nullable', 'array'],
            'allowed_group_ids.*'    => ['integer'],
            'show_social_share' => ['boolean'],
            'show_facebook_comments' => ['boolean'],
            'show_breadcrumb' => ['boolean'],
            'external_url' => ['nullable', 'url', 'max:500'],
            'link_target' => ['nullable', Rule::in(['_self', '_blank'])],
            'layout_json' => ['nullable', 'json'],
            'sections_json' => ['nullable', 'json'],
            'sections_json_dirty' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'max:5120'],

            // SEO fields
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
            'seo_h1' => ['nullable', 'string', 'max:255'],
            'seo_canonical' => ['nullable', 'url', 'max:500'],
            'seo_noindex' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $rawJson = $this->input('sections_json');

            if (! $rawJson || $v->errors()->has('sections_json')) {
                return;
            }

            $decoded = json_decode($rawJson, true);

            if (! is_array($decoded)) {
                return;
            }

            $templateIds = FrontendSections::collectTemplateIds($decoded);

            if (empty($templateIds)) {
                return;
            }

            $templates = SectionTemplate::whereIn('id', $templateIds)->get()->keyBy('id');
            $schemaErrors = FrontendSectionSchemaValidator::validate($decoded, $templates);

            foreach ($schemaErrors as $field => $messages) {
                foreach ($messages as $message) {
                    $v->errors()->add($field, $message);
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'title' => 'Sayfa başlığı',
            'slug' => 'URL slug',
            'parent_id' => 'Üst sayfa',
            'language_id' => 'Dil',
            'status' => 'Durum',
            'sort_order' => 'Sıralama',
            'cover_image' => 'Kapak görseli',
            'seo_title' => 'SEO başlık',
            'seo_description' => 'SEO açıklama',
        ];
    }
}
