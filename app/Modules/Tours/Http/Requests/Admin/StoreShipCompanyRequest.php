<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShipCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('ship_company')?->id;

        return [
            'slug'              => [
                'required', 'string', 'max:120', 'alpha_dash',
                Rule::unique('ship_companies', 'slug')->ignore($companyId),
            ],
            'name'              => ['required', 'string', 'max:200'],
            'company_type'      => ['nullable', 'string', 'max:60'],
            'operator'          => ['nullable', 'string', 'max:200'],
            'founded_year'      => ['nullable', 'integer', 'min:1800', 'max:' . (int) date('Y')],
            'headquarters'      => ['nullable', 'string', 'max:200'],
            'website'           => ['nullable', 'url', 'max:300'],
            'uses_cabin_groups' => ['nullable', 'boolean'],
            'sort_order'        => ['nullable', 'integer'],
            'is_active'         => ['nullable', 'boolean'],
            'logo'              => ['nullable', 'image', 'max:5120'],   // 5MB

            'translations'                     => ['required', 'array', 'min:1'],
            'translations.*.language_id'       => ['required', 'integer'],
            'translations.*.description'       => ['nullable', 'string'],
            'translations.*.meta_title'        => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description'  => ['nullable', 'string'],
        ];
    }
}
