<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreRoomTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Auto-derive a slug from the name when the admin leaves it blank.
     */
    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug');

        if (blank($slug) && filled($this->input('name'))) {
            $slug = Str::slug((string) $this->input('name'));
        }

        $this->merge(['slug' => $slug ? Str::slug((string) $slug) : $slug]);
    }

    public function rules(): array
    {
        $roomTypeId = $this->route('room_type')?->id;

        return [
            'name'         => ['required', 'string', 'max:255'],
            'slug'         => ['required', 'string', 'max:200', Rule::unique('room_types', 'slug')->ignore($roomTypeId)],
            'summary'      => ['nullable', 'string', 'max:500'],
            'description'  => ['nullable', 'string'],
            'capacity_min' => ['nullable', 'integer', 'min:1', 'max:100'],
            'capacity_max' => ['nullable', 'integer', 'min:1', 'max:100', 'gte:capacity_min'],
            'size_m2'      => ['nullable', 'integer', 'min:0', 'max:10000'],
            'bedrooms'     => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms'    => ['nullable', 'integer', 'min:0', 'max:50'],
            'base_price'   => ['nullable', 'numeric', 'min:0'],
            'currency'     => ['nullable', 'string', 'size:3'],
            'unit_count'   => ['nullable', 'integer', 'min:1', 'max:1000'],
            'amenities'    => ['nullable', 'string'], // newline-separated in the form
            'sort_order'   => ['nullable', 'integer'],
            'is_active'    => ['nullable', 'boolean'],
            'images.*'     => ['nullable', 'image', 'max:8192'],
        ];
    }
}
