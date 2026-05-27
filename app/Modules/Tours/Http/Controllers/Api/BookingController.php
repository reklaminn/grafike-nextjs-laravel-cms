<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Tours\Exceptions\CapacityExhaustedException;
use App\Modules\Tours\Exceptions\QuoteValidationException;
use App\Modules\Tours\Http\Requests\Api\CreateBookingRequest;
use App\Modules\Tours\Models\Booking;
use App\Modules\Tours\Models\Tour;
use App\Modules\Tours\Models\TourDate;
use App\Modules\Tours\Services\Booking\BookingService;
use App\Modules\Tours\Services\Booking\QuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public-facing booking API for the Next.js wizard.
 *
 * Three endpoints:
 *   POST /api/v1/tours/quote        — preview total without committing
 *   POST /api/v1/tours/bookings     — create reservation (holds capacity)
 *   GET  /api/v1/tours/bookings/{ref} — lookup by ref for payment-resume URL
 *
 * The actual payment flow (init 3DS → callback → confirm) is owned
 * by the Payments module + a tiny glue layer that wires
 * PaymentsModule's complete → BookingService::confirm.  Phase 4
 * frontend will hit the Payments init endpoint with the booking_ref.
 */
class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookings,
        private readonly QuoteService $quotes,
    ) {
    }

    /**
     * Compute a quote without persisting — used by the wizard's price
     * preview as users change passenger counts / extras.
     */
    public function quote(CreateBookingRequest $request): JsonResponse
    {
        try {
            $draft = $request->toDraft();

            // Phase 1.5.b: Tour.priceTiers / cabinTypes relation'ları kaldırıldı.
            // QuoteService kendi içinde priceGroup'u resolve eder, sadece
            // ship.cabins eager-load yeterli (matrix lookup'ı için).
            /** @var TourDate $date */
            $date = TourDate::with('tour.ship.cabins')
                ->findOrFail($draft->tourDateId);

            $quote = $this->quotes->compute($date->tour, $date, $draft);

            return response()->json([
                'currency'      => $quote->currency,
                'subtotal'      => $quote->subtotal,
                'extras_total'  => $quote->extrasTotal,
                'discount_total' => $quote->discountTotal,
                'tax_total'     => $quote->taxTotal,
                'total'         => $quote->total,
                'deposit_due'   => $quote->depositDueAmount,
                'lines'         => array_map(static fn ($l) => [
                    'kind'       => $l->kind,
                    'label'      => $l->label,
                    'unit_price' => $l->unitPrice,
                    'quantity'   => $l->quantity,
                    'subtotal'   => $l->subtotal,
                    'meta'       => $l->meta,
                ], $quote->lines),
            ]);
        } catch (QuoteValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors,
            ], 422);
        }
    }

    /**
     * Create a Reserved booking with capacity held.
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        $draft = $request->toDraft();

        try {
            $booking = $this->bookings->createReservation($draft);
        } catch (CapacityExhaustedException $e) {
            return response()->json([
                'message'   => 'Bu tarihte yeterli kapasite kalmadı.',
                'requested' => $e->requested,
                'available' => $e->available,
            ], 409);
        } catch (QuoteValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors,
            ], 422);
        }

        return response()->json([
            'booking_ref'        => $booking->booking_ref,
            'status'             => $booking->status->value,
            'total_amount'       => $booking->total_amount,
            'currency'           => $booking->currency,
            'deposit_due_amount' => $booking->deposit_due_amount,
            'hold_expires_at'    => $booking->hold_expires_at?->toIso8601String(),
        ], 201);
    }

    /**
     * Lookup by booking_ref — used by the payment-resume page to
     * re-hydrate state when the user clicks the email link.
     */
    public function show(string $ref): JsonResponse
    {
        /** @var Booking|null $booking */
        $booking = Booking::with(['passengers', 'extras', 'tourDate.tour'])
            ->where('booking_ref', $ref)
            ->first();

        if (! $booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json([
            'booking_ref'        => $booking->booking_ref,
            'status'             => $booking->status->value,
            'currency'           => $booking->currency,
            'total_amount'       => $booking->total_amount,
            'amount_paid'        => $booking->amount_paid,
            'deposit_due_amount' => $booking->deposit_due_amount,
            'hold_expires_at'    => $booking->hold_expires_at?->toIso8601String(),
            'tour' => [
                'slug'  => $booking->tourDate?->tour?->slug,
                'type'  => $booking->tourDate?->tour?->type?->value,
            ],
            'departure_at'   => $booking->tourDate?->starts_at?->toIso8601String(),
            'passenger_count' => $booking->passengers->count(),
        ]);
    }
}
