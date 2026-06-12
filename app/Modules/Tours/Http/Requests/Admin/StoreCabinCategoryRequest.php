<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCabinCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('cabin_category')?->id;

        return [
            'slug'        => [
                'required', 'string', 'max:60', 'alpha_dash',
                Rule::unique('cabin_categories', 'slug')->ignore($categoryId),
            ],
            'icon'        => ['nullable', 'string', 'max:60'],
            'sort_order'  => ['nullable', 'integer'],
            'is_active'   => ['nullable', 'boolean'],

            'translations'                  => ['required', 'array', 'min:1'],
            'translations.*.language_id'    => ['required', 'integer'],
            'translations.*.name'           => ['required', 'string', 'max:150'],
            'translations.*.description'    => ['nullable', 'string'],
        ];
    }
}
