<?php

declare(strict_types=1);

namespace App\Modules\Payments\Exceptions;

/**
 * Gateway accepted the request but the bank declined the card
 * (insufficient funds, 3DS failure, fraud-shield rejection, …).
 *
 * Caller flows display the gateway's localised reason to the user and
 * mark the PaymentTransaction `failed`, but the parent Booking is
 * NOT moved to `cancelled` — the user can retry with another card.
 */
class PaymentDeclinedException extends PaymentGatewayException
{
}
