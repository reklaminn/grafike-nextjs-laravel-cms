<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking\DTOs;

/**
 * Customer's pick of one TourExtra and quantity.
 */
final class ExtraSelection
{
    public function __construct(
        public readonly int $extraId,
        public readonly int $quantity = 1,
    ) {
    }
}
