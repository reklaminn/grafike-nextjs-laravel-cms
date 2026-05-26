<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use App\Modules\Tours\Enums\TourType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared validator for store + update on Tour.  When `tour` route
 * param is bound, ignored from the slug uniqueness check.
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
            'tour_category_id' => ['nullable', 'integer', 'exists:tour_categories,id'],
            'status'           => ['required', Rule::in(['draft', 'published', 'archived'])],

            'currency'         => ['required', 'string', 'size:3'],
            'base_price'       => ['required', 'integer', 'min:0'],
            'capacity_default' => ['required', 'integer', 'min:0'],

            'type_config'      => ['nullable', 'string'],   // JSON string from textarea
            'sort_order'       => ['nullable', 'integer'],
            'is_featured'      => ['nullable', 'boolean'],

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
        ];
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
}
