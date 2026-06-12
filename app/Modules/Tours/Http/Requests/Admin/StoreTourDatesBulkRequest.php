<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Toplu departure tarihi girişi — cruise/günlük turlar için.
 *
 * Cruise turlarında her sefer ayrı bir tarihtir; tek tek tarih aralığı
 * girmek yerine birden çok kalkış tarihi seçilir.  Bitiş tarihi gece
 * sayısından (nights) otomatik hesaplanır; kalkış saati paylaşımlıdır.
 */
class StoreTourDatesBulkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nights'         => ['nullable', 'integer', 'min:0', 'max:365'],
            'depart_time'    => ['nullable', 'date_format:H:i'],
            'dates'          => ['required', 'array', 'min:1'],
            'dates.*'        => ['required', 'date'],
            'capacity_total' => ['required', 'integer', 'min:0'],
            'price_override' => ['nullable', 'integer', 'min:0'],
            'status'         => ['required', Rule::in(['open', 'closed', 'sold_out', 'cancelled'])],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'dates.required' => 'En az bir kalkış tarihi eklemelisiniz.',
            'dates.*.date'   => 'Geçersiz tarih formatı.',
        ];
    }
}
