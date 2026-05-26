<?php

declare(strict_types=1);

namespace App\Modules\Payments\Exceptions;

/**
 * Thrown when a webhook / callback signature does not validate.
 *
 * The IyzicoWebhookController catches this and returns 401 — so an
 * attacker forging webhook calls cannot poison our booking state.
 */
class InvalidPaymentSignatureException extends PaymentGatewayException
{
}
