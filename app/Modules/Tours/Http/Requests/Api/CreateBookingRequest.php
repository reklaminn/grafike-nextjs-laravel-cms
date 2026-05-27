<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Requests\Api;

use App\Modules\Tours\Enums\PassengerType;
use App\Modules\Tours\Services\Booking\DTOs\BookingDraft;
use App\Modules\Tours\Services\Booking\DTOs\ExtraSelection;
use App\Modules\Tours\Services\Booking\DTOs\PassengerDraft;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the booking-wizard payload from Next.js and converts it
 * into a BookingDraft DTO for BookingService::createReservation().
 *
 * Keep validation rules close to user input shape — the DTOs let us
 * change internal naming without rewriting form requests.
 */
class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tour_date_id'              => ['required', 'integer', 'exists:tour_dates,id'],

            'customer'                  => ['required', 'array'],
            'customer.full_name'        => ['required', 'string', 'max:200'],
            'customer.email'            => ['required', 'email', 'max:200'],
            'customer.phone'            => ['required', 'string', 'max:50'],
            'customer.country'          => ['nullable', 'string', 'max:100'],
            'customer.address'          => ['nullable', 'string', 'max:1000'],
            'customer.notes'            => ['nullable', 'string', 'max:1000'],
            'customer.marketing_opt_in' => ['nullable', 'boolean'],

            'passengers'                => ['required', 'array', 'min:1', 'max:50'],
            'passengers.*.type'         => ['required', Rule::in(PassengerType::values())],
            'passengers.*.first_name'   => ['required', 'string', 'max:100'],
            'passengers.*.last_name'    => ['required', 'string', 'max:100'],
            'passengers.*.id_type'      => ['required', Rule::in(['tckn', 'passport'])],
            'passengers.*.id_number'    => ['nullable', 'string', 'max:50'],
            'passengers.*.nationality'  => ['nullable', 'string', 'max:3'],
            'passengers.*.date_of_birth' => ['nullable', 'date'],
            'passengers.*.gender'       => ['nullable', Rule::in(['male', 'female', 'other'])],
            // Phase 1.5: cabin_id references new Cabin model (per-ship master).
            // cabin_type_id legacy key tolerated by toDraft() for backward compat.
            'passengers.*.cabin_id'      => ['nullable', 'integer', 'exists:cabins,id'],
            'passengers.*.cabin_type_id' => ['nullable', 'integer'],   // legacy ignore

            'passengers.*.is_lead'      => ['nullable', 'boolean'],
            'passengers.*.notes'        => ['nullable', 'string', 'max:500'],

            'extras'                    => ['nullable', 'array'],
            'extras.*.extra_id'         => ['required_with:extras', 'integer', 'exists:tour_extras,id'],
            'extras.*.quantity'         => ['nullable', 'integer', 'min:1', 'max:20'],

            'locale'                    => ['nullable', 'string', 'max:10'],
            'member_id'                 => ['nullable', 'integer', 'exists:members,id'],
            'source'                    => ['nullable', 'string', 'max:50'],
            'utm_params'                => ['nullable', 'array'],
        ];
    }

    /**
     * Hydrate the validated request into a BookingDraft DTO.
     */
    public function toDraft(): BookingDraft
    {
        $data = $this->validated();

        $passengers = collect($data['passengers'])
            ->map(fn (array $p) => new PassengerDraft(
                type:         PassengerType::from($p['type']),
                firstName:    $p['first_name'],
                lastName:     $p['last_name'],
                idType:       $p['id_type'],
                idNumber:     $p['id_number']      ?? null,
                nationality:  $p['nationality']    ?? null,
                dateOfBirth:  $p['date_of_birth']  ?? null,
                gender:       $p['gender']         ?? null,
                cabinId:      $p['cabin_id']       ?? ($p['cabin_type_id'] ?? null),  // backward-compat key
                isLead:       (bool) ($p['is_lead'] ?? false),
                notes:        $p['notes']          ?? null,
            ))->all();

        $extras = collect($data['extras'] ?? [])
            ->map(fn (array $e) => new ExtraSelection(
                extraId:  (int) $e['extra_id'],
                quantity: (int) ($e['quantity'] ?? 1),
            ))->all();

        return new BookingDraft(
            tourDateId:  (int) $data['tour_date_id'],
            passengers:  $passengers,
            extras:      $extras,
            customer:    $data['customer'],
            locale:      $data['locale']     ?? 'tr',
            memberId:    $data['member_id']  ?? null,
            source:      $data['source']     ?? 'web',
            utmParams:   $data['utm_params'] ?? null,
        );
    }
}
