<?php

declare(strict_types=1);

namespace App\Modules\Tours\Exceptions;

use RuntimeException;

/**
 * Thrown by CapacityLockService when a reservation can't be made
 * because the requested seat count exceeds capacity_left under lock.
 *
 * BookingController catches this and returns 409 Conflict with a
 * localised "Bu tarihte yeterli kapasite kalmadı" message so the
 * user can pick another departure.
 */
class CapacityExhaustedException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $tourDateId,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct($message);
    }
}
