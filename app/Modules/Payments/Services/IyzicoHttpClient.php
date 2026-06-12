<?php

declare(strict_types=1);

namespace App\Modules\Payments\Services;

use App\Modules\Payments\Exceptions\PaymentGatewayException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;

/**
 * Thin HTTP wrapper for Iyzico REST API.
 *
 * Single responsibility: serialise an array → JSON → POST with
 * IyzicoSignature::authorizationHeader → decode JSON response.
 *
 * Higher-level intent (initialize3DS, complete, refund) lives in
 * IyzicoGateway; this class knows nothing about Iyzico's business
 * endpoints, only its auth + transport conventions.  That separation
 * keeps the gateway code testable via Http::fake without monkey-patching.
 */
class IyzicoHttpClient
{
    public function __construct(
        private readonly IyzicoSignature $signature,
    ) {
    }

    /**
     * POST a JSON payload to an Iyzico endpoint and return the decoded
     * array body.  Throws PaymentGatewayException on transport or
     * decode failure (5xx / malformed JSON / connection timeout).
     *
     * Business-level errors (4xx with valid Iyzico error JSON) are
     * returned to the caller as decoded array — caller decides whether
     * to throw PaymentDeclinedException or persist the failure.
     */
    public function post(
        IyzicoCredentials $credentials,
        string $uriPath,
        array $payload,
    ): array {
        try {
            $jsonBody = json_encode(
                $payload,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (JsonException $e) {
            throw new PaymentGatewayException(
                'Iyzico request payload encode failed: ' . $e->getMessage(),
                previous: $e,
            );
        }

        $authorization = $this->signature->authorizationHeader($credentials, $uriPath, $jsonBody);

        try {
            $response = Http::withHeaders([
                'Authorization' => $authorization,
                'Accept'        => 'application/json',
            ])
                ->withBody($jsonBody, 'application/json')
                ->timeout((int) config('payments.gateways.iyzico.timeout', 30))
                ->post($credentials->baseUrl() . $uriPath);
        } catch (ConnectionException $e) {
            throw new PaymentGatewayException(
                'Iyzico bağlantı hatası: ' . $e->getMessage(),
                debugContext: ['uri' => $uriPath],
                previous: $e,
            );
        }

        return $this->decode($response, $uriPath);
    }

    private function decode(Response $response, string $uriPath): array
    {
        $body = $response->body();

        if ($body === '') {
            throw new PaymentGatewayException(
                'Iyzico empty response (HTTP ' . $response->status() . ')',
                debugContext: ['uri' => $uriPath, 'http_status' => $response->status()],
            );
        }

        try {
            $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Log::error('Iyzico: malformed JSON response', [
                'uri'       => $uriPath,
                'http_code' => $response->status(),
                'body'      => mb_substr($body, 0, 500),
            ]);
            throw new PaymentGatewayException(
                'Iyzico malformed JSON response',
                debugContext: ['uri' => $uriPath, 'http_status' => $response->status()],
                previous: $e,
            );
        }

        if (! is_array($decoded)) {
            throw new PaymentGatewayException(
                'Iyzico unexpected response shape (expected JSON object)',
                debugContext: ['uri' => $uriPath, 'http_status' => $response->status()],
            );
        }

        return $decoded;
    }
}
