<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Http\Requests\Api;

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
}
