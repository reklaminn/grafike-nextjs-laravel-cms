<?php

declare(strict_types=1);

namespace App\Modules\Payments\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Central\PaymentAuditLog;
use App\Modules\Payments\Exceptions\InvalidPaymentSignatureException;
use App\Modules\Payments\Exceptions\PaymentGatewayException;
use App\Modules\Payments\Services\PaymentDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Inbound Iyzico events.
 *
 * Two adjacent endpoints:
 *   POST /api/v1/payments/iyzico/callback    — 3DS bank redirect
 *   POST /api/v1/payments/iyzico/webhook     — async notification
 *
 * Both routes are CSRF-exempt (configured at the route group level)
 * because they come from a third-party origin.
 *
 * Tenant context: the calling URL hits the tenant's domain, so by the
 * time this controller runs, stancl's InitializeTenancyByDomain
 * middleware has already set tenancy()->tenant.  We pull the tenant
 * id from there rather than from the request body.
 */
class IyzicoWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentDispatcher $dispatcher,
    ) {
    }

    /**
     * 3DS callback — bank redirects the user's browser here after the
     * 3DS challenge completes.  We:
     *   1. Verify the conversationId matches one of OUR transactions.
     *   2. Run gateway->complete() to capture funds.
     *   3. Hand off to BookingService::confirm() (Phase 2C wiring).
     *   4. Redirect the user to a friendly result page on the frontend.
     */
    public function callback(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId();

        $gateway = $this->dispatcher->gateway('iyzico');
        $callback = $gateway->parseCallback($request->all());

        $this->audit($tenantId, 'callback', $callback->conversationId, $callback->paymentId, [
            'status'   => $callback->status,
            'mdStatus' => $callback->mdStatus,
        ], $request->ip());

        try {
            $completed = $gateway->complete($callback, $tenantId);
        } catch (PaymentGatewayException $e) {
            Log::error('Iyzico callback complete() failed', [
                'tenant'          => $tenantId,
                'conversation_id' => $callback->conversationId,
                'error'           => $e->getMessage(),
            ]);

            $this->audit($tenantId, 'capture_failed', $callback->conversationId, $callback->paymentId, [
                'error' => $e->getMessage(),
            ], $request->ip());

            return response()->json(['ok' => false, 'error' => $e->getMessage()], 502);
        }

        $this->audit(
            tenantId:        $tenantId,
            event:           $completed->success ? 'capture' : 'capture_failed',
            conversationId:  $completed->conversationId,
            paymentId:       $completed->paymentId,
            payload:         [
                'amount'   => $completed->paidAmount,
                'currency' => $completed->currency,
                'fraud'    => $completed->fraudStatus,
                'error'    => $completed->errorMessage,
            ],
            sourceIp:        $request->ip(),
        );

        // The actual booking confirmation transition happens in the
        // BookingService listener wired in Phase 2C — keep this
        // controller thin and gateway-only.

        return response()->json([
            'ok'              => $completed->success,
            'conversation_id' => $completed->conversationId,
            'payment_id'      => $completed->paymentId,
        ]);
    }

    /**
     * Async webhook — Iyzico fires this for status changes that happen
     * server-side (refund settlement, dispute opened, etc.).  Signed
     * with the secretKey shared with us at provisioning time.
     */
    public function webhook(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId();
        $gateway  = $this->dispatcher->gateway('iyzico');

        try {
            $gateway->verifyWebhookSignature(
                rawBody:  $request->getContent(),
                headers:  $request->headers->all(),
                tenantId: $tenantId,
            );
        } catch (InvalidPaymentSignatureException $e) {
            Log::warning('Iyzico webhook rejected: invalid signature', [
                'tenant' => $tenantId,
                'ip'     => $request->ip(),
            ]);
            return response()->json(['ok' => false, 'error' => 'signature'], 401);
        } catch (Throwable $e) {
            Log::error('Iyzico webhook verify exploded', [
                'tenant' => $tenantId,
                'error'  => $e->getMessage(),
            ]);
            return response()->json(['ok' => false, 'error' => 'verify'], 500);
        }

        $payload = $request->all();

        $this->audit(
            tenantId:        $tenantId,
            event:           'webhook',
            conversationId:  (string) ($payload['conversationId']         ?? ''),
            paymentId:       isset($payload['paymentId']) ? (string) $payload['paymentId'] : null,
            payload:         $payload,
            sourceIp:        $request->ip(),
        );

        // Phase 2C: dispatch a domain event so Tours/Commerce can
        // react to refund-settled / dispute-opened notifications
        // without coupling Payments to either module.

        return response()->json(['ok' => true]);
    }

    private function tenantId(): string
    {
        $tenant = tenancy()->tenant;

        if ($tenant === null) {
            abort(404, 'Tenant context not initialised');
        }

        return (string) $tenant->getTenantKey();
    }

    private function audit(
        string $tenantId,
        string $event,
        string $conversationId,
        ?string $paymentId,
        array $payload,
        ?string $sourceIp,
    ): void {
        try {
            PaymentAuditLog::record([
                'tenant_id'          => $tenantId,
                'gateway'            => 'iyzico',
                'event'              => $event,
                'conversation_id'    => $conversationId,
                'gateway_payment_id' => $paymentId,
                'payload'            => $payload,
                'source_ip'          => $sourceIp,
            ]);
        } catch (Throwable $e) {
            // Audit must NEVER block the callback response.  Log + swallow.
            report($e);
        }
    }
}
