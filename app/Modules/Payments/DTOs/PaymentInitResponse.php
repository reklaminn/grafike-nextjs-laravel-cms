<?php

declare(strict_types=1);

namespace App\Modules\Payments\DTOs;

/**
 * Result of PaymentGateway::initialize3DS().
 *
 * Two paths possible:
 *   - 3DS:        $threeDsHtml is set, $paymentId is null until
 *                 callback. Controller embeds the HTML so the browser
 *                 redirects to the bank's 3DS page.
 *   - Non-3DS:    $paymentId set immediately (rare; Iyzico always uses
 *                 3DS for cards in TR market — kept for completeness).
 */
final class PaymentInitResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $conversationId,
        public readonly ?string $paymentId = null,
        public readonly ?string $threeDsHtml = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $errorMessage = null,
        public readonly array $rawPayload = [],
    ) {
    }

    public static function threeDsChallenge(string $conversationId, string $html, array $raw = []): self
    {
        return new self(
            success: true,
            conversationId: $conversationId,
            threeDsHtml: $html,
            rawPayload: $raw,
        );
    }

    public static function failed(string $conversationId, string $code, string $message, array $raw = []): self
    {
        return new self(
            success: false,
            conversationId: $conversationId,
            errorCode: $code,
            errorMessage: $message,
            rawPayload: $raw,
        );
    }
}
