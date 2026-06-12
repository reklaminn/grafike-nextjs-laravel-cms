<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use App\Modules\Tours\Enums\CalculationMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * TourPriceGroup CRUD validation — Tour-nested resource.
 *
 * cabin_prices[] matrix grid: her cabin için 1 row, 6-tier price column
 * + age brackets + calculation_method enum.  cabin_id NULL = non-cruise
 * generic price (PerPerson / PerReservation pricing modes).
 *
 * Prices kuruş cinsinden integer (100 = 1 TRY).  NULL = "Sorunuz".
 */
class StoreTourPriceGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'min_persons'      => ['nullable', 'integer', 'min:1'],
            'adult_priority'   => ['nullable', 'boolean'],
            'capacity_quota'   => ['nullable', 'integer', 'min:0'],
            'campaign_text'    => ['nullable', 'string', 'max:255'],
            'sort_order'       => ['nullable', 'integer'],
            'is_active'        => ['nullable', 'boolean'],

            // Translations
            'translations'                  => ['required', 'array', 'min:1'],
            'translations.*.language_id'    => ['required', 'integer'],
            'translations.*.name'           => ['required', 'string', 'max:200'],
            'translations.*.description'    => ['nullable', 'string'],

            // Date assignments (m2m TourDate)
            'date_ids'   => ['nullable', 'array'],
            'date_ids.*' => ['integer', 'exists:tour_dates,id'],

            // Oda/kabin satırları — her satır bir TourCabinPrice (repeatable)
            'cabin_prices'                          => ['nullable', 'array'],
            'cabin_prices.*.id'                     => ['nullable', 'integer'],
            'cabin_prices.*.room_label'             => ['nullable', 'string', 'max:200'],
            'cabin_prices.*.deck_label'             => ['nullable', 'string', 'max:120'],
            'cabin_prices.*.cabin_id'               => ['nullable', 'integer', 'exists:cabins,id'],
            'cabin_prices.*.price_definition'       => ['nullable', 'string', 'max:200'],
            'cabin_prices.*.calculation_method'     => ['nullable', Rule::in(CalculationMethod::values())],
            'cabin_prices.*.currency'               => ['nullable', 'string', 'size:3'],
            'cabin_prices.*.price_single'           => ['nullable', 'integer', 'min:0'],
            'cabin_prices.*.price_double'           => ['nullable', 'integer', 'min:0'],
            'cabin_prices.*.price_triple'           => ['nullable', 'integer', 'min:0'],
            'cabin_prices.*.price_quad'             => ['nullable', 'integer', 'min:0'],
            'cabin_prices.*.price_child'            => ['nullable', 'integer', 'min:0'],
            'cabin_prices.*.price_baby'             => ['nullable', 'integer', 'min:0'],
            'cabin_prices.*.child_age_min'          => ['nullable', 'integer', 'min:0', 'max:30'],
            'cabin_prices.*.child_age_max'          => ['nullable', 'integer', 'min:0', 'max:30'],
            'cabin_prices.*.baby_age_min'           => ['nullable', 'integer', 'min:0', 'max:30'],
            'cabin_prices.*.baby_age_max'           => ['nullable', 'integer', 'min:0', 'max:30'],
            'cabin_prices.*.sort_order'             => ['nullable', 'integer'],
            'cabin_prices.*.is_active'              => ['nullable', 'boolean'],
        ];
    }
}
