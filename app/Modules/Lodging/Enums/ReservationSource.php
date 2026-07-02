<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Enums;

/**
 * How a reservation request arrived.  `web` is the public block/form;
 * `whatsapp` / `phone` are for operator-entered requests.
 */
enum ReservationSource: string
{
    case Web      = 'web';
    case Whatsapp = 'whatsapp';
    case Phone    = 'phone';

    public function label(): string
    {
        return match ($this) {
            self::Web      => 'Web',
            self::Whatsapp => 'WhatsApp',
            self::Phone    => 'Telefon',
        };
    }
}
