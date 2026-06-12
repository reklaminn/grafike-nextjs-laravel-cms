<?php

declare(strict_types=1);

namespace App\Modules\Tours\Exceptions;

use RuntimeException;

/**
 * QuoteService input validation failed — bad tour_date id, mismatched
 * cabin selection on cruise tour, unknown extra id, etc.
 *
 * Distinct from CapacityExhaustedException so callers can show a
 * different message (this one is "your selection is invalid", that
 * one is "no seats left").
 */
class QuoteValidationException extends RuntimeException
{
    /**
     * @param array<int, string> $errors Field-keyed validation errors
     */
    public function __construct(string $message, public readonly array $errors = [])
    {
        parent::__construct($message);
    }
}
