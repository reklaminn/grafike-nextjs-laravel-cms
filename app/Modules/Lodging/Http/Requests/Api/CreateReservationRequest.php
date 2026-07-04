<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Http\Requests\Api;

use App\Modules\Lodging\Models\RoomType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for a public reservation request (POST /api/v1/lodging/requests).
 * Honeypot + Turnstile are checked in the controller (they are not fields).
 */
class CreateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_type'   => ['nullable', 'string', 'max:200', 'exists:room_types,slug'],
            'guest_name'  => ['required', 'string', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:40'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'checkin'     => ['required', 'date', 'after_or_equal:today'],
            'checkout'    => ['required', 'date', 'after:checkin'],
            'adults'      => ['nullable', 'integer', 'min:1', 'max:30'],
            'children'    => ['nullable', 'integer', 'min:0', 'max:30'],
            'message'     => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_name.required'  => 'Ad Soyad zorunludur.',
            'guest_phone.required' => 'Telefon zorunludur.',
            'checkin.after_or_equal' => 'Giriş tarihi geçmiş bir tarih olamaz.',
            'checkout.after'         => 'Çıkış tarihi giriş tarihinden sonra olmalıdır.',
            'room_type.exists'       => 'Seçilen oda tipi bulunamadı.',
        ];
    }

    /**
     * Kapasite (doluluk) kontrolü — belirli bir oda tipi seçiliyse toplam misafir
     * (yetişkin + çocuk) o oda tipinin `capacity_max`'ını aşamaz. "Farketmez"
     * (room_type boş) durumunda kontrol atlanır; rezervasyon oluşurken oda
     * atanınca kapasite yeniden değerlendirilebilir. Envanter (adet) müsaitliği
     * ayrı bir kontroldür (bkz. ReservationController::store → AvailabilityService).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $slug = $this->input('room_type');
            if (! is_string($slug) || $slug === '') {
                return;
            }

            $room = RoomType::query()->where('slug', $slug)->first();
            if (! $room || $room->capacity_max === null) {
                return;
            }

            $guests = (int) $this->input('adults', 1) + (int) $this->input('children', 0);
            if ($guests > (int) $room->capacity_max) {
                $v->errors()->add(
                    'adults',
                    "Bu daire en fazla {$room->capacity_max} misafir alır (seçtiğiniz: {$guests}). Lütfen daha büyük bir daire seçin veya kişi sayısını azaltın.",
                );
            }
        });
    }
}
