<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Payments;

use App\Modules\Payments\Services\IyzicoCredentials;
use App\Modules\Payments\Services\IyzicoSignature;
use PHPUnit\Framework\TestCase;

/**
 * IyzicoSignature is pure (no Laravel, no DB) — testable as a plain
 * PHPUnit unit test so it runs in milliseconds.  Covers:
 *
 *   - HMAC-SHA256 + base64 produces stable output for known input.
 *   - randomKey() returns 24+ char strings, no collisions across 1k samples.
 *   - Authorization header has the documented `IYZWSv2 …` shape.
 *   - Webhook verification accepts a valid signature and rejects a tampered body.
 */
class IyzicoSignatureTest extends TestCase
{
    public function test_compute_is_deterministic_for_known_input(): void
    {
        $sig = new IyzicoSignature();

        // base64(hmac_sha256("the-secret", "hello-world"))
        // = "LJZRJpqlpQfhJWFs1aZ/LZ9cP2OAuIyZpEFLPGW3xis=" (computed externally)
        $expected = base64_encode(hash_hmac('sha256', 'hello-world', 'the-secret', true));

        $this->assertSame($expected, $sig->compute('the-secret', 'hello-world'));
        $this->assertSame($expected, $sig->compute('the-secret', 'hello-world')); // stable on repeat
    }

    public function test_random_keys_are_unique_across_many_calls(): void
    {
        $sig = new IyzicoSignature();
        $seen = [];

        for ($i = 0; $i < 1000; $i++) {
            $k = $sig->randomKey();
            $this->assertGreaterThanOrEqual(24, strlen($k), 'randomKey too short');
            $this->assertArrayNotHasKey($k, $seen, 'duplicate randomKey within batch');
            $seen[$k] = true;
        }
    }

    public function test_authorization_header_has_iyzico_v2_shape(): void
    {
        $sig = new IyzicoSignature();
        $creds = new IyzicoCredentials(
            tenantId:  'acme',
            apiKey:    'api-key-1',
            secretKey: 'secret-1',
            sandbox:   true,
        );

        $header = $sig->authorizationHeader($creds, '/payment/3dsecure/initialize', '{"foo":"bar"}');

        $this->assertStringStartsWith('IYZWSv2 apiKey:api-key-1', $header);
        $this->assertStringContainsString('&randomKey:', $header);
        $this->assertStringContainsString('&signature:', $header);
    }

    public function test_webhook_signature_round_trip(): void
    {
        $sig    = new IyzicoSignature();
        $secret = 'my-webhook-secret';
        $body   = '{"event":"payment.captured","paymentId":"42"}';

        $valid = $sig->compute($secret, $body);

        $this->assertTrue(
            $sig->verifyWebhook($secret, $body, $valid),
            'Signature computed by compute() must validate via verifyWebhook()'
        );
    }

    public function test_webhook_signature_rejects_tampered_body(): void
    {
        $sig    = new IyzicoSignature();
        $secret = 'my-webhook-secret';
        $body   = '{"event":"payment.captured","paymentId":"42"}';

        $signatureOfOriginal = $sig->compute($secret, $body);
        $tamperedBody        = str_replace('"42"', '"999"', $body);

        $this->assertFalse(
            $sig->verifyWebhook($secret, $tamperedBody, $signatureOfOriginal),
            'Tampered body must NOT verify against original signature'
        );
    }

    public function test_webhook_signature_rejects_missing_header(): void
    {
        $sig = new IyzicoSignature();

        $this->assertFalse($sig->verifyWebhook('s', 'body', null));
        $this->assertFalse($sig->verifyWebhook('s', 'body', ''));
    }
}
