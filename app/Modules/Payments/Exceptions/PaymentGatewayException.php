<?php

declare(strict_types=1);

namespace App\Modules\Payments\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Generic gateway failure — network error, malformed response, 5xx, …
 *
 * Specific subclasses (InvalidPaymentSignatureException,
 * PaymentDeclinedException, MissingBYOKKeysException, …) extend this so
 * one catch in the controller can normalise gateway errors into a user
 * message while specific catches can react to known causes.
 */
class PaymentGatewayException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $gatewayCode = null,
        public readonly ?array $debugContext = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function withContext(array $extra): self
    {
        return new self(
            $this->getMessage(),
            $this->gatewayCode,
            array_merge($this->debugContext ?? [], $extra),
            $this->getPrevious(),
        );
    }
}
