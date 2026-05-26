<?php

declare(strict_types=1);

namespace App\Modules\Payments\DTOs;

/**
 * Result of PaymentGateway::refund().
 *
 * Iyzico refunds are synchronous when the original payment was
 * captured on the same day, otherwise they queue and the final
 * confirmation arrives via webhook.  Either way our `success` flag
 * mirrors the gateway's immediate response.
 */
final class RefundResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $conversationId,
        public readonly ?string $gatewayRefundId,
        public readonly ?int $refundedAmount,
        public readonly ?string $currency,
        public readonly ?string $errorCode = null,
        public readonly ?string $errorMessage = null,
        public readonly array $rawPayload = [],
    ) {
    }
}
