<?php

declare(strict_types=1);

namespace App\Modules\Payments\Contracts;

use App\Modules\Payments\DTOs\PaymentCallbackData;
use App\Modules\Payments\DTOs\PaymentCompleteResponse;
use App\Modules\Payments\DTOs\PaymentInitRequest;
use App\Modules\Payments\DTOs\PaymentInitResponse;
use App\Modules\Payments\DTOs\RefundRequest;
use App\Modules\Payments\DTOs\RefundResponse;

/**
 * The shared abstraction every payment gateway implements.
 *
 * Multi-tenant note: every method takes the tenantId (via the request
 * DTOs) so the implementation can resolve the right BYOK API keys at
 * the moment of the call.  Gateways must NOT cache keys statically.
 *
 * Phase 2 ships IyzicoGateway.  Future implementations (Stripe, PayTR)
 * just need to satisfy this contract and PaymentDispatcher will be able
 * to route through them.
 */
interface PaymentGateway
{
    /**
     * Stable slug — matches keys in config/payments.php under `gateways`.
     */
    public function name(): string;

    /**
     * Begin a 3DS payment flow.
     *
     * For Iyzico: returns HTML that the controller embeds so the
     * browser redirects to the bank's 3DS page.  The actual capture
     * happens later inside complete() when the bank POSTs to our
     * callback URL.
     */
    public function initialize3DS(PaymentInitRequest $request): PaymentInitResponse;

    /**
     * Finish a 3DS payment after the bank's callback fires.
     *
     * Idempotent: re-invoking with the same paymentId returns the
     * already-captured result rather than charging the user twice.
     */
    public function complete(PaymentCallbackData $callback, string $tenantId): PaymentCompleteResponse;

    /**
     * Refund (partial or full).  See RefundRequest for inputs.
     */
    public function refund(RefundRequest $request): RefundResponse;

    /**
     * Verify the HMAC signature on a webhook payload.  Returns true
     * when the body genuinely originates from the gateway.
     *
     * Throws InvalidPaymentSignatureException on mismatch (controllers
     * convert this into a 401 response).
     */
    public function verifyWebhookSignature(string $rawBody, array $headers, string $tenantId): bool;

    /**
     * Parse a normalised PaymentCallbackData out of an inbound webhook
     * or callback payload.  Used by IyzicoWebhookController to convert
     * raw HTTP input into our DTO shape.
     */
    public function parseCallback(array $payload): PaymentCallbackData;
}
