<?php

declare(strict_types=1);

namespace App\Modules\Tours\Exceptions;

use App\Modules\Tours\Enums\BookingStatus;
use RuntimeException;

/**
 * BookingStateMachine refused a state transition (e.g. trying to
 * confirm an already-cancelled booking, or reserve from completed).
 */
class InvalidBookingTransitionException extends RuntimeException
{
    public function __construct(
        public readonly BookingStatus $from,
        public readonly BookingStatus $to,
    ) {
        parent::__construct(sprintf(
            'Booking cannot transition from [%s] to [%s].',
            $from->value,
            $to->value,
        ));
    }
}
