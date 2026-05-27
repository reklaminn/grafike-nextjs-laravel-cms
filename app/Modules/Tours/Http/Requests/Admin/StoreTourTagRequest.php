<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Ortak validation: store + update (TourCategory pattern'i).
 *
 * TourTag.slug benzersizliği tenant scope'unda; ignore-self update için.
 * Çevirilerden en az biri zorunlu (name boş olmasın).
 */
class StoreTourTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tagId = $this->route('tour_tag')?->id;

        return [
            'slug'        => [
                'required', 'string', 'max:60', 'alpha_dash',
                Rule::unique('tour_tags', 'slug')->ignore($tagId),
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
