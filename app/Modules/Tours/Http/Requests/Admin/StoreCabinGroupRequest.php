<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCabinGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $groupId = $this->route('cabin_group')?->id;

        return [
            'ship_company_id' => ['required', 'integer', 'exists:ship_companies,id'],
            'slug'            => [
                'required', 'string', 'max:120', 'alpha_dash',
                Rule::unique('cabin_groups', 'slug')->ignore($groupId),
            ],
            'sort_order'      => ['nullable', 'integer'],
            'is_active'       => ['nullable', 'boolean'],
            'cabin_ids'       => ['nullable', 'array'],
            'cabin_ids.*'     => ['integer', 'exists:cabins,id'],

            'translations'                  => ['required', 'array', 'min:1'],
            'translations.*.language_id'    => ['required', 'integer'],
            'translations.*.name'           => ['required', 'string', 'max:200'],
            'translations.*.description'    => ['nullable', 'string'],
        ];
    }
}
