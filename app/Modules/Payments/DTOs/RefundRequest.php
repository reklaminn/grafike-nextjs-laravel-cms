<?php

declare(strict_types=1);

namespace App\Modules\Payments\DTOs;

/**
 * Input to PaymentGateway::refund().
 *
 * `paymentTransactionId` refers to the gateway's id (Iyzico's
 * paymentTransactionId, NOT the parent paymentId — Iyzico requires the
 * inner transaction id for refunds of installment payments).
 */
final class RefundRequest
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $paymentTransactionId,
        public readonly int $amount,        // minor units
        public readonly string $currency,
        public readonly string $reason,
        public readonly string $conversationId,
        public readonly ?string $ip = null,
    ) {
    }
}
