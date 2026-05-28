<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Rota Takvimi (itinerary) kaydı — dil bazlı.
 *
 * Eski sistem Tab 2 "GECE PLANI" tekrarlı bölümünün karşılığı: her durak
 * bir gün numarası + port + saatler + konaklama + program taşır.  Yapı
 * dil bazlı (her dil kendi rotasını taşır).
 */
class StoreTourItineraryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language_id'    => ['required', 'integer'],
            'origin_port_id' => ['nullable', 'integer', 'exists:ports,id'],
            'title'          => ['nullable', 'string', 'max:255'],
            'summary'        => ['nullable', 'string'],

            'stops'                     => ['nullable', 'array'],
            'stops.*.day_number'        => ['required', 'integer', 'min:1', 'max:365'],
            'stops.*.title'             => ['nullable', 'string', 'max:255'],
            'stops.*.port_id'           => ['nullable', 'integer', 'exists:ports,id'],
            'stops.*.arrival_time'      => ['nullable', 'date_format:H:i'],
            'stops.*.departure_time'    => ['nullable', 'date_format:H:i'],
            'stops.*.accommodation'     => ['nullable', 'string', 'max:255'],
            'stops.*.description'       => ['nullable', 'string'],
        ];
    }
}
