<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use App\Modules\Tours\Enums\PricingMode;
use App\Modules\Tours\Enums\SalesStatus;
use App\Modules\Tours\Enums\TourType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared validator for store + update on Tour.  When `tour` route
 * param is bound, ignored from the slug + SKU uniqueness checks.
 *
 * Phase 1.5.c alanları:
 *   - sku                   nullable unique
 *   - ship_id               cruise için app-level required (custom rule)
 *   - pricing_mode          PricingMode enum
 *   - sales_status          SalesStatus enum
 *   - includes_flight       bool
 *   - flight_info           JSON (array bağımsız alanlardan)
 *   - copied_from_tour_id   nullable self FK
 *   - destination_ids[]     m2m
 *   - tour_tag_ids[]        m2m
 *   - secondary_category_ids[] m2m (primary = tour_category_id)
 */
class StoreTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tourId = $this->route('tour')?->id;

        return [
            'type'             => ['required', Rule::in(TourType::values())],
            'slug'             => [
                'required', 'string', 'max:200', 'alpha_dash',
                Rule::unique('tours', 'slug')->ignore($tourId),
            ],
            'sku'              => [
                'nullable', 'string', 'max:60',
                Rule::unique('tours', 'sku')->ignore($tourId),
            ],
            'tour_category_id' => ['nullable', 'integer', 'exists:tour_categories,id'],
            'ship_id'          => ['nullable', 'integer', 'exists:ships,id'],
            'status'           => ['required', Rule::in(['draft', 'published', 'archived'])],

            'pricing_mode'     => ['nullable', Rule::in(PricingMode::values())],
            'sales_status'     => ['nullable', Rule::in(SalesStatus::values())],

            'currency'         => ['required', 'string', 'size:3'],
            'base_price'       => ['required', 'integer', 'min:0'],
            'capacity_default' => ['required', 'integer', 'min:0'],
            'duration_value'   => ['nullable', 'integer', 'min:0', 'max:365'],
            'duration_unit'    => ['nullable', Rule::in(['night', 'day', 'hour'])],

            'includes_flight'      => ['nullable', 'boolean'],
            'flight_airline'       => ['nullable', 'string', 'max:120'],
            'flight_departure'     => ['nullable', 'string', 'max:120'],
            'flight_arrival'       => ['nullable', 'string', 'max:120'],
            'flight_code'          => ['nullable', 'string', 'max:60'],
            'copied_from_tour_id'  => ['nullable', 'integer', 'exists:tours,id'],

            'type_config'      => ['nullable', 'string'],
            'sort_order'       => ['nullable', 'integer'],
            'is_featured'      => ['nullable', 'boolean'],
            'structured_data_json' => ['nullable', 'string'],

            // M2M relations (Phase 1.5.c)
            'destination_ids'         => ['nullable', 'array'],
            'destination_ids.*'       => ['integer', 'exists:destinations,id'],
            'tour_tag_ids'            => ['nullable', 'array'],
            'tour_tag_ids.*'          => ['integer', 'exists:tour_tags,id'],
            'secondary_category_ids'  => ['nullable', 'array'],
            'secondary_category_ids.*' => ['integer', 'exists:tour_categories,id'],

            // Media (Spatie collections: cover/gallery/brochure)
            'cover'        => ['nullable', 'image', 'max:5120'],
            'gallery'      => ['nullable', 'array'],
            'gallery.*'    => ['image', 'max:5120'],
            'brochure'     => ['nullable', 'file', 'mimes:pdf', 'max:20480'],

            // Translations (per active language)
            'translations'                       => ['required', 'array', 'min:1'],
            'translations.*.language_id'         => ['required', 'integer'],
            'translations.*.title'               => ['required', 'string', 'max:500'],
            'translations.*.subtitle'            => ['nullable', 'string', 'max:500'],
            'translations.*.short_description'   => ['nullable', 'string'],
            'translations.*.description'         => ['nullable', 'string'],
            'translations.*.highlights'          => ['nullable', 'string'],
            'translations.*.important_info'      => ['nullable', 'string'],
            'translations.*.meta_title'          => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description'    => ['nullable', 'string'],
            'translations.*.og_image_url'        => ['nullable', 'url', 'max:1000'],
            'translations.*.price_disclaimer'    => ['nullable', 'string'],

            // Tab 3 — Bilgi amaçlı ücretler (online tahsil edilmez)
            'info_extras'                   => ['nullable', 'array'],
            'info_extras.*.id'              => ['nullable', 'integer'],
            'info_extras.*.info_extra_id'   => ['nullable', 'integer', 'exists:tenant_info_extras,id'],
            'info_extras.*.name'            => ['nullable', 'string', 'max:200'],
            'info_extras.*.price'           => ['nullable', 'integer', 'min:0'],
            'info_extras.*.currency'        => ['nullable', 'string', 'size:3'],
            'info_extras.*.per_person'      => ['nullable', 'boolean'],

            // Tab 3 — Online ekstralar (sepete girer)
            'booking_extras'                => ['nullable', 'array'],
            'booking_extras.*.id'           => ['nullable', 'integer'],
            'booking_extras.*.name'         => ['nullable', 'string', 'max:200'],
            'booking_extras.*.description'  => ['nullable', 'string', 'max:500'],
            'booking_extras.*.price'        => ['nullable', 'integer', 'min:0'],
            'booking_extras.*.pricing_mode' => ['nullable', Rule::in(['per_passenger', 'per_booking'])],
            'booking_extras.*.is_required'  => ['nullable', 'boolean'],
        ];
    }

    /**
     * Cross-field rule: cruise tour requires ship_id.
     * App-level enforcement (DB-level NOT NULL koymadık çünkü polymorphic).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->input('type') === TourType::Cruise->value && ! $this->filled('ship_id')) {
                $v->errors()->add('ship_id', 'Cruise turu için gemi seçimi zorunludur.');
            }
        });
    }

    /**
     * Convenience: JSON-decode the `type_config` field server-side.
     */
    public function typeConfigArray(): ?array
    {
        $raw = $this->input('type_config');
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }
        try {
            $decoded = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Convenience: structured_data_json textarea → array.
     */
    public function structuredDataArray(): ?array
    {
        $raw = $this->input('structured_data_json');
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }
        try {
            $decoded = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Birden fazla bağımsız form alanını flight_info JSON'una birleştirir.
     * (airline, departure airport, arrival airport, optional flight code)
     *
     * @return array<string, string>|null
     */
    public function flightInfoArray(): ?array
    {
        if (! $this->boolean('includes_flight')) {
            return null;
        }

        $info = array_filter([
            'airline'   => trim((string) $this->input('flight_airline', '')),
            'departure' => trim((string) $this->input('flight_departure', '')),
            'arrival'   => trim((string) $this->input('flight_arrival', '')),
            'code'      => trim((string) $this->input('flight_code', '')),
        ], static fn ($v) => $v !== '');

        return $info !== [] ? $info : null;
    }
}
