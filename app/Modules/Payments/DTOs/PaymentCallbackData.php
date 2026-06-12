<?php

declare(strict_types=1);

namespace App\Modules\Payments\DTOs;

/**
 * Normalised view of the data the gateway POSTs to our callback URL
 * after the bank's 3DS page closes.
 *
 * For Iyzico:
 *   POST {callback_url}
 *   {
 *     "status":  "success" | "failure",
 *     "paymentId": "12345",
 *     "conversationData": "...",
 *     "conversationId":   "our-correlation-id",
 *     "mdStatus":         "1"
 *   }
 */
final class PaymentCallbackData
{
    public function __construct(
        public readonly string $conversationId,
        public readonly ?string $paymentId,
        public readonly string $status,        // 'success' | 'failure'
        public readonly ?string $mdStatus,     // '1' approved, '0' failed, etc.
        public readonly ?string $conversationData,
        public readonly array $rawPayload,
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success' && $this->mdStatus === '1';
    }
}
