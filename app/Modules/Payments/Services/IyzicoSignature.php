<?php

declare(strict_types=1);

namespace App\Modules\Payments\Services;

/**
 * Iyzico HMAC-SHA256 request + webhook signature helper.
 *
 * Iyzico's auth scheme (v2):
 *
 *   randomKey = unix-millis + 16-byte hex
 *   signature = base64(hmac_sha256(secretKey, randomKey + uri + bodyJson))
 *   header    = "IYZWSv2 apiKey:" + apiKey
 *               + "&randomKey:"   + randomKey
 *               + "&signature:"   + signature
 *
 * The same routine validates webhook callbacks: the `x-iyz-signature-v3`
 * header carries the signature; we recompute over the raw body and a
 * shared secret (Iyzico documentation: webhook secret == secretKey).
 */
class IyzicoSignature
{
    /** Build an Authorization header for an outbound request. */
    public function authorizationHeader(
        IyzicoCredentials $credentials,
        string $uriPath,
        string $jsonBody,
    ): string {
        $randomKey = $this->randomKey();
        $signature = $this->compute(
            secretKey: $credentials->secretKey,
            payload:   $randomKey . $uriPath . $jsonBody,
        );

        return 'IYZWSv2 apiKey:' . $credentials->apiKey
            . '&randomKey:'      . $randomKey
            . '&signature:'      . $signature;
    }

    /**
     * Validate an inbound webhook signature.
     *
     * Iyzico documents two header variants in the wild:
     *   - x-iyz-signature-v3 (current)
     *   - x-iyz-signature    (legacy)
     * Both are HMAC-SHA256 of the raw body with the secretKey, base64'd.
     */
    public function verifyWebhook(string $secretKey, string $rawBody, ?string $headerValue): bool
    {
        if (! is_string($headerValue) || $headerValue === '') {
            return false;
        }

        $expected = $this->compute($secretKey, $rawBody);

        return hash_equals($expected, $headerValue);
    }

    public function compute(string $secretKey, string $payload): string
    {
        // base64(binary HMAC-SHA256)
        return base64_encode(hash_hmac('sha256', $payload, $secretKey, true));
    }

    /**
     * Iyzico's randomKey convention.  Uniqueness within the request
     * stream is the only constraint; the millisecond prefix + 16 hex
     * chars is comfortably collision-resistant at our throughput.
     */
    public function randomKey(): string
    {
        return (string) (int) (microtime(true) * 1000) . bin2hex(random_bytes(8));
    }
}
