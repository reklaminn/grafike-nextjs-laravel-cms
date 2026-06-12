<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking\DTOs;

/**
 * Everything the wizard collects before we mint a Booking row.
 *
 * Kept as a plain DTO (not an Eloquent model) so validation can run
 * cleanly against the input without round-tripping through a draft
 * table.  BookingService::createReservation() consumes this and
 * produces a real Booking + capacity hold.
 */
final class BookingDraft
{
    /**
     * @param array<int, PassengerDraft>  $passengers
     * @param array<int, ExtraSelection>  $extras
     */
    public function __construct(
        public readonly int $tourDateId,
        public readonly array $passengers,
        public readonly array $extras,
        public readonly array $customer,        // ['full_name','email','phone','country','address','notes','marketing_opt_in']
        public readonly string $locale = 'tr',
        public readonly ?int $memberId = null,
        public readonly ?string $source = 'web',
        public readonly ?array $utmParams = null,
    ) {
    }

    public function passengerCount(): int
    {
        return count($this->passengers);
    }
}
