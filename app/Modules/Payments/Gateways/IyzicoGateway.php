<?php

declare(strict_types=1);

namespace App\Modules\Payments\Gateways;

use App\Models\Tenant;
use App\Modules\Payments\Contracts\PaymentGateway;
use App\Modules\Payments\DTOs\PaymentCallbackData;
use App\Modules\Payments\DTOs\PaymentCompleteResponse;
use App\Modules\Payments\DTOs\PaymentInitRequest;
use App\Modules\Payments\DTOs\PaymentInitResponse;
use App\Modules\Payments\DTOs\RefundRequest;
use App\Modules\Payments\DTOs\RefundResponse;
use App\Modules\Payments\Exceptions\InvalidPaymentSignatureException;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Services\IyzicoBYOKResolver;
use App\Modules\Payments\Services\IyzicoHttpClient;
use App\Modules\Payments\Services\IyzicoSignature;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * Iyzico v2 implementation of PaymentGateway.
 *
 * BYOK-aware: each method receives the tenant id (via the request DTOs)
 * and resolves credentials via IyzicoBYOKResolver right before each
 * outbound call.  Credentials never live longer than one request.
 *
 * Endpoints used (Iyzico v2 REST):
 *   POST /payment/3dsecure/initialize  — start 3DS, returns HTML
 *   POST /payment/3dsecure/auth        — capture after callback
 *   POST /payment/refund               — refund a captured transaction
 *
 * Iyzico's request/response shapes are bigger than what we surface
 * here — see iyzico.com/docs.  Only the fields we need for our flow
 * are mapped; the rest comes back inside rawPayload on the DTOs for
 * audit logging.
 */
class IyzicoGateway implements PaymentGateway
{
    public function __construct(
        private readonly IyzicoBYOKResolver $byok,
        private readonly IyzicoHttpClient $client,
        private readonly IyzicoSignature $signature,
    ) {
    }

    public function name(): string
    {
        return 'iyzico';
    }

    // ─── Initialize 3DS ──────────────────────────────────────────────────────

    public function initialize3DS(PaymentInitRequest $request): PaymentInitResponse
    {
        $tenant      = $this->tenant($request->tenantId);
        $credentials = $this->byok->resolve($tenant);

        $payload = [
            'locale'          => $request->locale ?? 'tr',
            'conversationId'  => $request->conversationId,
            'price'           => $this->minorToDecimalString($request->amount),
            'paidPrice'       => $this->minorToDecimalString($request->amount),
            'currency'        => $request->currency,
            'installment'     => (int) ($request->installment ?? 1),
            'basketId'        => (string) $request->payableId,
            'paymentChannel'  => 'WEB',
            'paymentGroup'    => 'PRODUCT',
            'callbackUrl'     => $request->callbackUrl,
            'paymentCard'     => $this->normaliseCard($request->card),
            'buyer'           => $this->normaliseBuyer($request->buyer),
            'shippingAddress' => $request->shippingAddress,
            'billingAddress'  => $request->billingAddress,
            'basketItems'     => array_values($request->items),
        ];

        $raw = $this->client->post($credentials, '/payment/3dsecure/initialize', $payload);

        if (($raw['status'] ?? null) !== 'success') {
            $this->logFailure('initialize3DS', $request->conversationId, $raw);

            return PaymentInitResponse::failed(
                conversationId: $request->conversationId,
                code:           (string) ($raw['errorCode']    ?? 'unknown'),
                message:        (string) ($raw['errorMessage'] ?? 'Iyzico initialize failed'),
                raw:            $raw,
            );
        }

        $html = (string) ($raw['threeDSHtmlContent'] ?? '');

        if ($html === '') {
            throw new PaymentGatewayException(
                'Iyzico initialize succeeded but no threeDSHtmlContent returned',
                debugContext: ['conversation_id' => $request->conversationId],
            );
        }

        // Iyzico returns the HTML base64-encoded; decode for direct embed.
        $decodedHtml = base64_decode($html, true);

        return PaymentInitResponse::threeDsChallenge(
            conversationId: $request->conversationId,
            html:           $decodedHtml !== false ? $decodedHtml : $html,
            raw:            $raw,
        );
    }

    // ─── Complete (after 3DS callback) ───────────────────────────────────────

    public function complete(PaymentCallbackData $callback, string $tenantId): PaymentCompleteResponse
    {
        $tenant      = $this->tenant($tenantId);
        $credentials = $this->byok->resolve($tenant);

        if (! $callback->isSuccess()) {
            return PaymentCompleteResponse::failed(
                conversationId: $callback->conversationId,
                paymentId:      $callback->paymentId,
                code:           $callback->mdStatus ?? 'callback_failure',
                message:        'Iyzico 3DS callback reported failure',
                raw:            $callback->rawPayload,
            );
        }

        if ($callback->paymentId === null) {
            throw new PaymentGatewayException(
                'Iyzico callback missing paymentId',
                debugContext: ['conversation_id' => $callback->conversationId],
            );
        }

        $payload = [
            'locale'           => 'tr',
            'conversationId'   => $callback->conversationId,
            'paymentId'        => $callback->paymentId,
            'conversationData' => $callback->conversationData,
        ];

        $raw = $this->client->post($credentials, '/payment/3dsecure/auth', $payload);

        if (($raw['status'] ?? null) !== 'success') {
            $this->logFailure('complete', $callback->conversationId, $raw);

            return PaymentCompleteResponse::failed(
                conversationId: $callback->conversationId,
                paymentId:      $callback->paymentId,
                code:           (string) ($raw['errorCode']    ?? 'unknown'),
                message:        (string) ($raw['errorMessage'] ?? 'Iyzico capture failed'),
                raw:            $raw,
            );
        }

        return PaymentCompleteResponse::captured(
            conversationId: $callback->conversationId,
            paymentId:      (string) ($raw['paymentId'] ?? $callback->paymentId),
            paidAmount:     $this->decimalStringToMinor((string) ($raw['paidPrice'] ?? '0')),
            currency:       (string) ($raw['currency']    ?? 'TRY'),
            fraudStatus:    isset($raw['fraudStatus']) ? (string) $raw['fraudStatus'] : null,
            raw:            $raw,
        );
    }

    // ─── Refund ──────────────────────────────────────────────────────────────

    public function refund(RefundRequest $request): RefundResponse
    {
        $tenant      = $this->tenant($request->tenantId);
        $credentials = $this->byok->resolve($tenant);

        $payload = [
            'locale'                => 'tr',
            'conversationId'        => $request->conversationId,
            'paymentTransactionId'  => $request->paymentTransactionId,
            'price'                 => $this->minorToDecimalString($request->amount),
            'currency'              => $request->currency,
            'reason'                => $request->reason,
            'ip'                    => $request->ip ?? '127.0.0.1',
        ];

        $raw = $this->client->post($credentials, '/payment/refund', $payload);

        $success = ($raw['status'] ?? null) === 'success';

        return new RefundResponse(
            success:          $success,
            conversationId:   $request->conversationId,
            gatewayRefundId:  isset($raw['paymentTransactionId']) ? (string) $raw['paymentTransactionId'] : null,
            refundedAmount:   $success ? $this->decimalStringToMinor((string) ($raw['price'] ?? '0')) : null,
            currency:         $success ? (string) ($raw['currency'] ?? 'TRY') : null,
            errorCode:        $success ? null : (string) ($raw['errorCode']    ?? 'unknown'),
            errorMessage:     $success ? null : (string) ($raw['errorMessage'] ?? 'Iyzico refund failed'),
            rawPayload:       $raw,
        );
    }

    // ─── Webhook ─────────────────────────────────────────────────────────────

    public function verifyWebhookSignature(string $rawBody, array $headers, string $tenantId): bool
    {
        $tenant      = $this->tenant($tenantId);
        $credentials = $this->byok->resolve($tenant);

        // Headers can come in mixed case from PSR-7 / Symfony; normalise.
        $sig = $headers['x-iyz-signature-v3']
            ?? $headers['X-Iyz-Signature-V3']
            ?? $headers['x-iyz-signature']
            ?? $headers['X-Iyz-Signature']
            ?? null;

        $valid = $this->signature->verifyWebhook($credentials->secretKey, $rawBody, is_array($sig) ? ($sig[0] ?? null) : $sig);

        if (! $valid) {
            throw new InvalidPaymentSignatureException(
                'Iyzico webhook signature mismatch',
                debugContext: ['tenant' => $tenantId, 'header_present' => $sig !== null],
            );
        }

        return true;
    }

    public function parseCallback(array $payload): PaymentCallbackData
    {
        return new PaymentCallbackData(
            conversationId:    (string) ($payload['conversationId'] ?? ''),
            paymentId:         isset($payload['paymentId']) ? (string) $payload['paymentId'] : null,
            status:            (string) ($payload['status'] ?? 'failure'),
            mdStatus:          isset($payload['mdStatus']) ? (string) $payload['mdStatus'] : null,
            conversationData:  isset($payload['conversationData']) ? (string) $payload['conversationData'] : null,
            rawPayload:        $payload,
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function tenant(string $tenantId): Tenant|BaseTenant
    {
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            throw new PaymentGatewayException(
                "Tenant not found for payment: {$tenantId}",
            );
        }

        return $tenant;
    }

    /**
     * Iyzico expects prices as decimal strings with `.` separator
     * (e.g. "125.50" for 125 lira 50 kuruş).  Convert from our minor-
     * unit integer storage.
     */
    private function minorToDecimalString(int $minor): string
    {
        return number_format($minor / 100, 2, '.', '');
    }

    private function decimalStringToMinor(string $decimal): int
    {
        return (int) round(((float) $decimal) * 100);
    }

    private function normaliseCard(array $card): array
    {
        // Pass through with explicit keys to avoid leaking extras.
        return [
            'cardHolderName' => (string) ($card['cardHolderName'] ?? $card['holder']     ?? ''),
            'cardNumber'     => (string) ($card['cardNumber']     ?? $card['number']     ?? ''),
            'expireMonth'    => (string) ($card['expireMonth']    ?? $card['expMonth']   ?? ''),
            'expireYear'     => (string) ($card['expireYear']     ?? $card['expYear']    ?? ''),
            'cvc'            => (string) ($card['cvc']            ?? $card['cvv']        ?? ''),
            'registerCard'   => (int)    ($card['registerCard']   ?? 0),
        ];
    }

    private function normaliseBuyer(array $buyer): array
    {
        return array_merge([
            'id'                  => '',
            'name'                => '',
            'surname'             => '',
            'gsmNumber'           => '',
            'email'               => '',
            'identityNumber'      => '',
            'lastLoginDate'       => null,
            'registrationDate'    => null,
            'registrationAddress' => '',
            'ip'                  => '127.0.0.1',
            'city'                => '',
            'country'             => 'Turkey',
            'zipCode'             => '',
        ], $buyer);
    }

    private function logFailure(string $stage, string $conversationId, array $raw): void
    {
        Log::warning("Iyzico {$stage} failed", [
            'conversation_id' => $conversationId,
            'error_code'      => $raw['errorCode']    ?? null,
            'error_message'   => $raw['errorMessage'] ?? null,
        ]);
    }
}
