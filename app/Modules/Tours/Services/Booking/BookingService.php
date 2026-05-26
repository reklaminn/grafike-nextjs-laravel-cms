<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking;

use App\Modules\Tours\Enums\BookingStatus;
use App\Modules\Tours\Events\BookingCancelled;
use App\Modules\Tours\Events\BookingConfirmed;
use App\Modules\Tours\Events\BookingExpired;
use App\Modules\Tours\Events\BookingReserved;
use App\Modules\Tours\Jobs\ExpireUnpaidReservationJob;
use App\Modules\Tours\Models\Booking;
use App\Modules\Tours\Models\BookingExtra;
use App\Modules\Tours\Models\BookingPassenger;
use App\Modules\Tours\Models\TourDate;
use App\Modules\Tours\Models\TourExtra;
use App\Modules\Tours\Services\Booking\DTOs\BookingDraft;
use App\Modules\Tours\Services\Booking\DTOs\Quote;
use App\Modules\Tours\StateMachines\BookingStateMachine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

/**
 * Orchestrates a booking's life:
 *
 *   createReservation(BookingDraft)
 *       1. Compute Quote (QuoteService)
 *       2. Reserve seats under DB lock (CapacityLockService)
 *       3. Persist Booking + passengers + extras
 *       4. Transition to Reserved + dispatch hold-expiry job + event
 *
 *   confirm(Booking, paymentInfo)
 *       1. Bump status Reserved → Confirmed (state machine)
 *       2. Record amount_paid
 *       3. Dispatch BookingConfirmed event
 *
 *   cancel(Booking)
 *       1. Release capacity (CapacityLockService::release)
 *       2. State machine → Cancelled
 *       3. Dispatch BookingCancelled event
 *
 *   expireIfStillReserved(Booking)
 *       1. Re-check status (job may fire after user just paid)
 *       2. Release capacity + transition to Expired + event
 *
 * Money / state changes are wrapped in a transaction; capacity itself
 * has its own inner transaction inside CapacityLockService.
 */
class BookingService
{
    public function __construct(
        private readonly QuoteService $quotes,
        private readonly CapacityLockService $capacity,
        private readonly BookingStateMachine $machine,
    ) {
    }

    /**
     * Create a Pending → Reserved booking with capacity held.
     *
     * @throws \App\Modules\Tours\Exceptions\CapacityExhaustedException
     * @throws \App\Modules\Tours\Exceptions\QuoteValidationException
     */
    public function createReservation(BookingDraft $draft): Booking
    {
        /** @var TourDate $date */
        $date = TourDate::with('tour.priceTiers', 'tour.cabinTypes')
            ->findOrFail($draft->tourDateId);
        $tour = $date->tour;

        // 1. Quote (also validates the draft — throws on bad input)
        $quote = $this->quotes->compute($tour, $date, $draft);

        // 2. Reserve seats — race-safe via lockForUpdate.  Released
        // automatically by Eloquent transaction wrapper if anything
        // below throws (Booking persist failure → no orphan capacity).
        $this->capacity->reserve($date->id, $draft->passengerCount());

        try {
            /** @var Booking $booking */
            $booking = DB::transaction(function () use ($draft, $date, $quote) {
                $booking = Booking::create([
                    'booking_ref'        => $this->mintUniqueRef(),
                    'tour_date_id'       => $date->id,
                    'member_id'          => $draft->memberId,
                    'status'             => BookingStatus::Pending->value,
                    'currency'           => $quote->currency,
                    'subtotal'           => $quote->subtotal,
                    'extras_total'       => $quote->extrasTotal,
                    'discount_total'     => $quote->discountTotal,
                    'tax_total'          => $quote->taxTotal,
                    'total_amount'       => $quote->total,
                    'amount_paid'        => 0,
                    'amount_refunded'    => 0,
                    'deposit_due_amount' => $quote->depositDueAmount,
                    'locale'             => $draft->locale,
                    'customer_snapshot'  => $draft->customer,
                    'source'             => $draft->source,
                    'utm_params'         => $draft->utmParams,
                    'hold_expires_at'    => now()->addMinutes((int) config('payments.reservation_hold_minutes', 20)),
                ]);

                // Passengers — snapshot tier_id + price from Quote line items
                foreach ($draft->passengers as $idx => $p) {
                    $line = collect($quote->lines)->first(
                        fn ($l) => $l->kind === 'passenger' && ($l->meta['passenger_idx'] ?? null) === $idx
                    );

                    BookingPassenger::create([
                        'booking_id'         => $booking->id,
                        'passenger_type'     => $p->type->value,
                        'first_name'         => $p->firstName,
                        'last_name'          => $p->lastName,
                        'id_type'            => $p->idType,
                        'id_number'          => $p->idNumber,
                        'nationality'        => $p->nationality,
                        'date_of_birth'      => $p->dateOfBirth,
                        'gender'             => $p->gender,
                        'tour_cabin_type_id' => $p->cabinTypeId,
                        'is_lead'            => $p->isLead,
                        'price_tier_id'      => $line?->meta['tier_id'] ?? null,
                        'price'              => $line?->subtotal ?? 0,
                        'notes'              => $p->notes,
                    ]);
                }

                // Extras
                $extraIds = collect($draft->extras)->pluck('extraId')->all();
                $extras = $extraIds === []
                    ? collect()
                    : TourExtra::query()->whereIn('id', $extraIds)->get()->keyBy('id');

                foreach ($draft->extras as $sel) {
                    $extra = $extras->get($sel->extraId);
                    if (! $extra) {
                        continue;
                    }

                    $line = collect($quote->lines)->first(
                        fn ($l) => $l->kind === 'extra' && ($l->meta['extra_id'] ?? null) === $extra->id
                    );

                    BookingExtra::create([
                        'booking_id'    => $booking->id,
                        'tour_extra_id' => $extra->id,
                        'name_snapshot' => $extra->name,
                        'unit_price'    => $line?->unitPrice ?? $extra->price,
                        'quantity'      => $sel->quantity,
                        'subtotal'      => $line?->subtotal ?? 0,
                    ]);
                }

                // Transition Pending → Reserved (sets reserved_at)
                $this->machine->transition($booking, BookingStatus::Reserved);

                return $booking->refresh();
            });
        } catch (\Throwable $e) {
            // Booking persistence failed but capacity was already
            // decremented — release it before re-throwing so the slot
            // is not silently lost.
            try {
                $this->capacity->release($date->id, $draft->passengerCount());
            } catch (\Throwable $release) {
                report($release);
            }
            throw $e;
        }

        // 3. Side-effects fire outside the DB transaction.
        ExpireUnpaidReservationJob::dispatch($booking->id)
            ->delay(now()->addMinutes((int) config('payments.reservation_hold_minutes', 20)));

        Event::dispatch(new BookingReserved($booking));

        return $booking;
    }

    /**
     * Move a Reserved booking to Confirmed after payment capture.
     */
    public function confirm(Booking $booking, int $paidAmount, ?string $gatewayPaymentId = null): Booking
    {
        $booking = DB::transaction(function () use ($booking, $paidAmount) {
            $booking->amount_paid = (int) ($booking->amount_paid + $paidAmount);
            $booking->save();

            return $this->machine->transition($booking, BookingStatus::Confirmed);
        });

        Event::dispatch(new BookingConfirmed($booking, $paidAmount, $gatewayPaymentId));

        return $booking;
    }

    /**
     * Cancel a Reserved / Confirmed booking; release capacity.
     */
    public function cancel(Booking $booking, ?string $reason = null): Booking
    {
        $passengerCount = $booking->passengers()->count();

        $booking = DB::transaction(function () use ($booking) {
            // Release capacity FIRST so race with a parallel
            // reserve() can re-acquire the same seat.
            $this->capacity->release(
                tourDateId: (int) $booking->tour_date_id,
                count:      $booking->passengers()->count(),
            );

            return $this->machine->transition($booking, BookingStatus::Cancelled);
        });

        Event::dispatch(new BookingCancelled($booking, $reason, $passengerCount));

        return $booking;
    }

    /**
     * Called by ExpireUnpaidReservationJob.  Guard re-checks status
     * because the user might have just completed payment in the same
     * second the job fires.
     */
    public function expireIfStillReserved(int $bookingId): ?Booking
    {
        $booking = Booking::query()->find($bookingId);
        if (! $booking) {
            return null;
        }

        $status = $booking->status instanceof BookingStatus
            ? $booking->status
            : BookingStatus::from((string) $booking->status);

        if ($status !== BookingStatus::Reserved) {
            // User confirmed in time, or admin already cancelled.
            return $booking;
        }

        $passengerCount = $booking->passengers()->count();

        $booking = DB::transaction(function () use ($booking) {
            $this->capacity->release(
                tourDateId: (int) $booking->tour_date_id,
                count:      $booking->passengers()->count(),
            );

            return $this->machine->transition($booking, BookingStatus::Expired);
        });

        Event::dispatch(new BookingExpired($booking, $passengerCount));

        return $booking;
    }

    /**
     * Generate a unique booking ref, retrying on the (extremely
     * unlikely) collision against the unique index.
     */
    private function mintUniqueRef(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $ref = Booking::generateReference();
            if (! Booking::query()->where('booking_ref', $ref)->exists()) {
                return $ref;
            }
        }

        // Astronomically improbable; fail loudly.
        throw new \RuntimeException('Could not mint a unique booking_ref after 5 attempts.');
    }
}
