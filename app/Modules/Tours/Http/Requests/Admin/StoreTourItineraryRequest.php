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

    /**
     * "Denizde" (at sea) durağı port dropdown'unda sanal `sea` değeriyle
     * gelir; gerçek bir liman değil.  Doğrulamadan önce: port_id=null +
     * point_type='sea' olarak normalize edilir (DB'de liman FK boş kalır).
     */
    protected function prepareForValidation(): void
    {
        $stops = $this->input('stops');
        if (! is_array($stops)) {
            return;
        }

        foreach ($stops as $i => $stop) {
            if (is_array($stop) && (($stop['port_id'] ?? null) === 'sea')) {
                $stops[$i]['port_id']    = null;
                $stops[$i]['point_type'] = 'sea';
            }
        }

        $this->merge(['stops' => $stops]);
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
            'stops.*.point_type'        => ['nullable', 'in:meeting,visit,sea'],
            'stops.*.title'             => ['nullable', 'string', 'max:255'],
            'stops.*.port_id'           => ['nullable', 'integer', 'exists:ports,id'],
            'stops.*.arrival_time'      => ['nullable', 'date_format:H:i'],
            'stops.*.departure_time'    => ['nullable', 'date_format:H:i'],
            'stops.*.accommodation'     => ['nullable', 'string', 'max:255'],
            'stops.*.description'       => ['nullable', 'string'],
        ];
    }
}
