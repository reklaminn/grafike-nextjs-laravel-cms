<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $shipId = $this->route('ship')?->id;

        return [
            'ship_company_id'    => ['required', 'integer', 'exists:ship_companies,id'],
            'slug'               => [
                'required', 'string', 'max:150', 'alpha_dash',
                Rule::unique('ships', 'slug')->ignore($shipId),
            ],
            'name'               => ['required', 'string', 'max:200'],
            'star_rating'        => ['nullable', 'integer', 'between:1,7'],
            'local_agent'        => ['nullable', 'string', 'max:200'],
            'flag_country_code'  => ['nullable', 'string', 'size:2'],
            'imo_number'         => ['nullable', 'string', 'max:20'],
            'year_built'         => ['nullable', 'integer', 'min:1800', 'max:' . (int) date('Y')],
            'passenger_capacity' => ['nullable', 'integer', 'min:0'],
            'crew_count'         => ['nullable', 'integer', 'min:0'],
            'deck_count'         => ['nullable', 'integer', 'min:0'],
            'tonnage'            => ['nullable', 'integer', 'min:0'],
            'length_m'           => ['nullable', 'numeric'],
            'beam_m'             => ['nullable', 'numeric'],
            'cruise_speed_knots' => ['nullable', 'numeric'],
            'facilities'         => ['nullable', 'array'],
            'facilities.*'       => ['string', 'max:60'],
            'sort_order'         => ['nullable', 'integer'],
            'is_active'          => ['nullable', 'boolean'],

            'cover'                => ['nullable', 'image', 'max:5120'],
            'gallery'              => ['nullable', 'array'],
            'gallery.*'            => ['image', 'max:5120'],
            'deck_plans'           => ['nullable', 'array'],
            'deck_plans.*'         => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],

            'translations'                     => ['required', 'array', 'min:1'],
            'translations.*.language_id'       => ['required', 'integer'],
            'translations.*.description'       => ['nullable', 'string'],
            'translations.*.meta_title'        => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description'  => ['nullable', 'string'],
        ];
    }
}
