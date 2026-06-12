<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $destinationId = $this->route('destination')?->id;

        return [
            'slug'                    => [
                'required', 'string', 'max:120', 'alpha_dash',
                Rule::unique('destinations', 'slug')->ignore($destinationId),
            ],
            'latitude'                => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'               => ['nullable', 'numeric', 'between:-180,180'],
            'compatible_tour_types'   => ['nullable', 'array'],
            'compatible_tour_types.*' => ['string', 'in:cruise,package,daily,ferry'],
            'port_ids'                => ['nullable', 'array'],
            'port_ids.*'              => ['integer', 'exists:ports,id'],
            'sort_order'              => ['nullable', 'integer'],
            'is_active'               => ['nullable', 'boolean'],
            'is_featured'             => ['nullable', 'boolean'],

            'translations'                     => ['required', 'array', 'min:1'],
            'translations.*.language_id'       => ['required', 'integer'],
            'translations.*.name'              => ['required', 'string', 'max:200'],
            'translations.*.description'       => ['nullable', 'string'],
            'translations.*.meta_title'        => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description'  => ['nullable', 'string'],
        ];
    }
}
