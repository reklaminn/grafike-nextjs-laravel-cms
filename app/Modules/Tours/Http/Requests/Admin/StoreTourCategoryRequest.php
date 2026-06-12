<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Used for both store + update on TourCategory.  `slug` uniqueness
 * is enforced application-side via the explicit ignore-self rule
 * passed by the controller (cleaner than custom Rule classes).
 */
class StoreTourCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // admin.auth + tenant.module middleware already gate this
    }

    public function rules(): array
    {
        $categoryId = $this->route('tour_category')?->id;

        return [
            'slug'        => [
                'required', 'string', 'max:200', 'alpha_dash',
                \Illuminate\Validation\Rule::unique('tour_categories', 'slug')->ignore($categoryId),
            ],
            'parent_id'   => ['nullable', 'integer', 'exists:tour_categories,id'],
            'sort_order'  => ['nullable', 'integer'],
            'is_active'   => ['nullable', 'boolean'],

            // Translations — one row per language entered by the form.
            'translations'                       => ['required', 'array', 'min:1'],
            'translations.*.language_id'         => ['required', 'integer'],
            'translations.*.name'                => ['required', 'string', 'max:200'],
            'translations.*.description'         => ['nullable', 'string'],
            'translations.*.meta_title'          => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description'    => ['nullable', 'string'],
        ];
    }
}
