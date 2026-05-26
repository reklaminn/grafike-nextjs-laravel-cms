<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking\DTOs;

use App\Modules\Tours\Enums\PassengerType;

/**
 * Input shape for one passenger on a booking draft, sent by the
 * frontend wizard (Phase 4) or accepted via the public BookingController.
 */
final class PassengerDraft
{
    public function __construct(
        public readonly PassengerType $type,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $idType,           // 'tckn' | 'passport'
        public readonly ?string $idNumber,
        public readonly ?string $nationality,
        public readonly ?string $dateOfBirth,     // YYYY-MM-DD
        public readonly ?string $gender,
        public readonly ?int $cabinTypeId,        // cruise only; null otherwise
        public readonly bool $isLead = false,
        public readonly ?string $notes = null,
    ) {
    }
}
