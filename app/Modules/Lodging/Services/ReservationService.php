<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Services;

use App\Modules\Lodging\Enums\AvailabilityStatus;
use App\Modules\Lodging\Enums\ReservationSource;
use App\Modules\Lodging\Enums\ReservationStatus;
use App\Modules\Lodging\Mail\ReservationRequestMail;
use App\Modules\Lodging\Models\LodgingSetting;
use App\Modules\Lodging\Models\Reservation;
use App\Modules\Lodging\Models\RoomAvailability;
use App\Modules\Lodging\Models\RoomType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Reservation orchestration: create a request (with code + estimate +
 * notification), and transition it confirmed / cancelled (keeping the
 * derived availability row in sync).
 */
class ReservationService
{
    public function __construct(private readonly PricingService $pricing)
    {
    }

    /**
     * Create a pending reservation from a normalized payload.
     *
     * Expected keys: guest_name, guest_phone, checkin, checkout (required);
     * room_type_id|room_type (slug), guest_email, adults, children, message,
     * source, meta (optional).
     */
    public function createRequest(array $payload): Reservation
    {
        $roomType = $this->resolveRoomType($payload);

        $checkin  = (string) $payload['checkin'];
        $checkout = (string) $payload['checkout'];
        $nights   = $this->pricing->nights($checkin, $checkout);

        $currency = $roomType?->currency ?? LodgingSetting::current()->default_currency ?? 'TRY';
        $estTotal = $roomType ? $this->pricing->estimate($roomType, $checkin, $checkout)['total'] : null;

        $source = $payload['source'] ?? ReservationSource::Web->value;

        $reservation = Reservation::create([
            'code'         => $this->generateCode(),
            'room_type_id' => $roomType?->id,
            'guest_name'   => trim((string) $payload['guest_name']),
            'guest_phone'  => trim((string) $payload['guest_phone']),
            'guest_email'  => $payload['guest_email'] ?? null,
            'checkin'      => $checkin,
            'checkout'     => $checkout,
            'nights'       => $nights,
            'adults'       => (int) ($payload['adults'] ?? 1),
            'children'     => (int) ($payload['children'] ?? 0),
            'est_total'    => $estTotal,
            'currency'     => $currency,
            'message'      => $payload['message'] ?? null,
            'status'       => ReservationStatus::Pending->value,
            'source'       => $source,
            'meta'         => $payload['meta'] ?? null,
        ]);

        $this->notify($reservation, $roomType);

        return $reservation;
    }

    /**
     * Confirm a reservation and hold its dates by writing a derived
     * availability row (idempotent — replaces any prior row for it).
     */
    public function confirm(Reservation $reservation): Reservation
    {
        DB::transaction(function () use ($reservation) {
            $reservation->update(['status' => ReservationStatus::Confirmed->value]);

            // Drop any stale derived rows, then (re)create one for the stay.
            $reservation->availabilities()->delete();

            if ($reservation->room_type_id) {
                RoomAvailability::create([
                    'room_type_id'   => $reservation->room_type_id,
                    'start_date'     => $reservation->checkin,
                    'end_date'       => $reservation->checkout,
                    'qty'            => 1,
                    'status'         => AvailabilityStatus::Booked->value,
                    'source'         => 'reservation',
                    'reservation_id' => $reservation->id,
                    'note'           => 'Rezervasyon ' . $reservation->code,
                ]);
            }
        });

        return $reservation->refresh();
    }

    /**
     * Cancel a reservation and free any dates it was holding.
     */
    public function cancel(Reservation $reservation): Reservation
    {
        DB::transaction(function () use ($reservation) {
            $reservation->update(['status' => ReservationStatus::Cancelled->value]);
            $reservation->availabilities()->delete();
        });

        return $reservation->refresh();
    }

    // ─────────────────────────────────────────────────────────────────────

    private function resolveRoomType(array $payload): ?RoomType
    {
        if (! empty($payload['room_type_id'])) {
            return RoomType::query()->find($payload['room_type_id']);
        }

        if (! empty($payload['room_type'])) {
            return RoomType::query()->where('slug', $payload['room_type'])->first();
        }

        return null;
    }

    /**
     * PREFIX-YYMM-XXX, unique within the tenant DB.  Prefix comes from
     * settings so the module stays generic (Vatan Suit → "VS").
     */
    private function generateCode(): string
    {
        $prefix = strtoupper(LodgingSetting::current()->reservation_code_prefix ?: 'RZ');
        $ym     = now()->format('ym');

        do {
            $code = sprintf('%s-%s-%s', $prefix, $ym, strtoupper(Str::random(3)));
        } while (Reservation::query()->where('code', $code)->exists());

        return $code;
    }

    private function notify(Reservation $reservation, ?RoomType $roomType): void
    {
        $email = LodgingSetting::current()->notification_email;

        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->send(new ReservationRequestMail($reservation, $roomType?->name));
        } catch (\Throwable $e) {
            // Never let a mail failure break the request (form-system pattern).
            Log::error("Lodging reservation notification failed [{$reservation->code}]: {$e->getMessage()}");
        }
    }
}
