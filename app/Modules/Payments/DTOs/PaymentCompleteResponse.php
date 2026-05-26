<?php

declare(strict_types=1);

namespace App\Modules\Payments\DTOs;

/**
 * Result of PaymentGateway::complete() — the post-3DS capture call.
 *
 * After Iyzico's 3DS page redirects to our callback, the controller
 * invokes complete() to actually move the funds.  Success means the
 * money is captured on the cardholder's bank and ready to be settled.
 */
final class PaymentCompleteResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $conversationId,
        public readonly ?string $paymentId,
        public readonly ?int $paidAmount,         // minor units, what the bank actually charged
        public readonly ?string $currency,
        public readonly ?string $fraudStatus,     // gateway-specific
        public readonly ?string $errorCode = null,
        public readonly ?string $errorMessage = null,
        public readonly array $rawPayload = [],
    ) {
    }

    public static function captured(
        string $conversationId,
        string $paymentId,
        int $paidAmount,
        string $currency,
        ?string $fraudStatus = null,
        array $raw = [],
    ): self {
        return new self(
            success: true,
            conversationId: $conversationId,
            paymentId: $paymentId,
            paidAmount: $paidAmount,
            currency: $currency,
            fraudStatus: $fraudStatus,
            rawPayload: $raw,
        );
    }

    public static function failed(string $conversationId, ?string $paymentId, string $code, string $message, array $raw = []): self
    {
        return new self(
            success: false,
            conversationId: $conversationId,
            paymentId: $paymentId,
            paidAmount: null,
            currency: null,
            fraudStatus: null,
            errorCode: $code,
            errorMessage: $message,
            rawPayload: $raw,
        );
    }
}
