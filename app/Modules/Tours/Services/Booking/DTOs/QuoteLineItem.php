<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking\DTOs;

/**
 * One line on a Quote breakdown (admin display, customer invoice).
 *
 * `kind` discriminates between passenger seats, extras, taxes, etc.
 * so the UI can group rows visually without parsing labels.
 */
final class QuoteLineItem
{
    public function __construct(
        public readonly string $kind,      // 'passenger' | 'extra' | 'discount' | 'tax'
        public readonly string $label,
        public readonly int $unitPrice,    // minor units
        public readonly int $quantity,
        public readonly int $subtotal,     // minor units, post quantity (and sign for discounts)
        public readonly array $meta = [],  // optional: { tier_id, extra_id, passenger_idx, … }
    ) {
    }
}
