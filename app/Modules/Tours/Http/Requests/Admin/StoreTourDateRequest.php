<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared store+update validator for TourDate.  The parent tour is
 * passed via the route parameter, not the body.
 */
class StoreTourDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'starts_at'      => ['required', 'date'],
            'ends_at'        => ['nullable', 'date', 'after_or_equal:starts_at'],
            'capacity_total' => ['required', 'integer', 'min:0'],
            'capacity_left'  => ['nullable', 'integer', 'min:0', 'lte:capacity_total'],
            'price_override' => ['nullable', 'integer', 'min:0'],
            'status'         => ['required', Rule::in(['open', 'closed', 'sold_out', 'cancelled'])],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ];
    }
}
