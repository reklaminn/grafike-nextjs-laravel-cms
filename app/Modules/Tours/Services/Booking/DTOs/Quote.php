<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking\DTOs;

/**
 * Output of QuoteService::compute() — totals + line breakdown.
 *
 * `total` is the canonical figure persisted to bookings.total_amount.
 * Sub-totals are exposed for display (admin invoice, customer
 * confirmation email).
 */
final class Quote
{
    /**
     * @param array<int, QuoteLineItem> $lines
     */
    public function __construct(
        public readonly string $currency,
        public readonly int $subtotal,         // passengers
        public readonly int $extrasTotal,
        public readonly int $discountTotal,    // signed; negative reduces total
        public readonly int $taxTotal,
        public readonly int $total,
        public readonly int $depositDueAmount, // immediate payment
        public readonly array $lines,
    ) {
    }
}
