<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCabinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ship_id'                => ['required', 'integer', 'exists:ships,id'],
            'cabin_category_id'      => ['required', 'integer', 'exists:cabin_categories,id'],
            'brand_subcategory'      => ['nullable', 'string', 'max:60'],
            'code'                   => ['nullable', 'string', 'max:30'],
            'deck_name'              => ['nullable', 'string', 'max:60'],
            'max_capacity'           => ['nullable', 'integer', 'min:1', 'max:20'],
            'base_price_per_person'  => ['nullable', 'integer', 'min:0'],
            'sort_order'             => ['nullable', 'integer'],
            'is_active'              => ['nullable', 'boolean'],

            'cover'      => ['nullable', 'image', 'max:5120'],
            'gallery'    => ['nullable', 'array'],
            'gallery.*'  => ['image', 'max:5120'],
            'floor_plan' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],

            'translations'                  => ['required', 'array', 'min:1'],
            'translations.*.language_id'    => ['required', 'integer'],
            'translations.*.name'           => ['required', 'string', 'max:200'],
            'translations.*.description'    => ['nullable', 'string'],
        ];
    }
}
