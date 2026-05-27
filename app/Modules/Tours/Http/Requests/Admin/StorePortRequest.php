<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $portId = $this->route('port')?->id;

        return [
            'slug'         => [
                'required', 'string', 'max:120', 'alpha_dash',
                Rule::unique('ports', 'slug')->ignore($portId),
            ],
            'country_code' => ['nullable', 'string', 'size:2'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'population'   => ['nullable', 'integer', 'min:0'],
            'video_url'    => ['nullable', 'url', 'max:500'],
            'timezone'     => ['nullable', 'string', 'max:60'],
            'sort_order'   => ['nullable', 'integer'],
            'is_active'    => ['nullable', 'boolean'],

            'translations'                       => ['required', 'array', 'min:1'],
            'translations.*.language_id'         => ['required', 'integer'],
            'translations.*.name'                => ['required', 'string', 'max:200'],
            'translations.*.short_description'   => ['nullable', 'string'],
            'translations.*.long_description'    => ['nullable', 'string'],
            'translations.*.meta_title'          => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description'    => ['nullable', 'string'],
        ];
    }
}
