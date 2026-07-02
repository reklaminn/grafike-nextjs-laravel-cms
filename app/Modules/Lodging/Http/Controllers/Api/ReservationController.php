<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Lodging\Enums\ReservationSource;
use App\Modules\Lodging\Http\Requests\Api\CreateReservationRequest;
use App\Modules\Lodging\Models\RoomType;
use App\Modules\Lodging\Services\AvailabilityService;
use App\Modules\Lodging\Services\PricingService;
use App\Modules\Lodging\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Public, tenant-scoped reservation API consumed by the `reservation`
 * frontend block.  Endpoints:
 *   GET  /api/v1/lodging/room-types
 *   GET  /api/v1/lodging/availability?room_type={slug}&from=&to=
 *   POST /api/v1/lodging/requests
 */
class ReservationController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PricingService $pricing,
        private readonly ReservationService $reservations,
    ) {
    }

    /**
     * GET /api/v1/lodging/room-types
     */
    public function roomTypes(): JsonResponse
    {
        $types = RoomType::query()->active()->ordered()->get();

        return response()->json([
            'data' => $types->map(fn (RoomType $rt) => [
                'slug'         => $rt->slug,
                'name'         => $rt->name,
                'summary'      => $rt->summary,
                'description'  => $rt->description,
                'capacity_min' => $rt->capacity_min,
                'capacity_max' => $rt->capacity_max,
                'size_m2'      => $rt->size_m2,
                'bedrooms'     => $rt->bedrooms,
                'bathrooms'    => $rt->bathrooms,
                'base_price'   => (float) $rt->base_price,
                'currency'     => $rt->currency,
                'unit_count'   => $rt->unit_count,
                'amenities'    => $rt->amenities ?? [],
                'images'       => $rt->imageUrls(),
            ])->values(),
        ]);
    }

    /**
     * GET /api/v1/lodging/availability?room_type={slug}&from=&to=
     * → { booked: ["2026-07-10", …] }
     */
    public function availability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_type' => ['required', 'string', 'exists:room_types,slug'],
            'from'      => ['required', 'date'],
            'to'        => ['required', 'date', 'after:from'],
        ]);

        $roomType = RoomType::query()->where('slug', $validated['room_type'])->firstOrFail();

        return response()->json([
            'booked' => $this->availability->bookedDates(
                $roomType,
                $validated['from'],
                $validated['to'],
            ),
        ]);
    }

    /**
     * POST /api/v1/lodging/requests
     */
    public function store(CreateReservationRequest $request): JsonResponse
    {
        // Honeypot — a hidden field humans never fill.  Fake success so bots
        // waste the attempt silently (form-system pattern).
        if (filled($request->input('_hp_url'))) {
            return response()->json([
                'success' => true,
                'message' => 'Rezervasyon talebiniz alındı. Teşekkürler!',
            ]);
        }

        // Cloudflare Turnstile — verify when configured.
        if (config('cms.turnstile.enabled')) {
            if (! $this->verifyTurnstile($request->input('cf-turnstile-response'), $request->ip())) {
                return response()->json(['error' => 'Doğrulama başarısız. Lütfen tekrar deneyin.'], 422);
            }
        }

        $data = $request->validated();

        // Optional availability guard: if a specific room type is chosen and
        // the range is already full, reject with a clear message.
        if (! empty($data['room_type'])) {
            $roomType = RoomType::query()->where('slug', $data['room_type'])->first();
            if ($roomType && ! $this->availability->isRangeAvailable($roomType, $data['checkin'], $data['checkout'])) {
                return response()->json([
                    'error' => 'Seçtiğiniz tarihler bu oda tipi için dolu. Lütfen farklı tarihler deneyin.',
                ], 422);
            }
        }

        $reservation = $this->reservations->createRequest([
            ...$data,
            'source' => ReservationSource::Web->value,
            'meta'   => [
                'ip'  => $request->ip(),
                'ua'  => $request->userAgent(),
                'ref' => $request->headers->get('referer'),
            ],
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Rezervasyon talebiniz alındı. En kısa sürede size dönüş yapacağız.',
            'code'      => $reservation->code,
            'est_total' => $reservation->est_total !== null ? (float) $reservation->est_total : null,
            'currency'  => $reservation->currency,
            'nights'    => $reservation->nights,
        ]);
    }

    /**
     * Verify a Cloudflare Turnstile token server-side.
     */
    protected function verifyTurnstile(?string $token, ?string $ip): bool
    {
        if (! $token) {
            return false;
        }

        try {
            $resp = Http::asForm()->timeout(8)->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'secret'   => (string) config('cms.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $ip,
                ],
            );

            return (bool) ($resp->json('success') ?? false);
        } catch (\Throwable $e) {
            Log::error('Lodging Turnstile verify failed: ' . $e->getMessage());

            return false;
        }
    }
}
