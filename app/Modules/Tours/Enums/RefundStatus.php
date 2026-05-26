<?php

declare(strict_types=1);

namespace App\Modules\Tours\Enums;

/**
 * Refund lifecycle.
 *
 *   requested  — user/admin filed the refund request; no money moved yet
 *   processing — gateway (Iyzico) refund call sent, awaiting webhook
 *   completed  — gateway confirmed refund, booking transitioned to refunded
 *   rejected   — request denied by admin (e.g. outside cancellation window)
 *   failed     — gateway reported failure (network, fraud signal, …)
 */
enum RefundStatus: string
{
    case Requested  = 'requested';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Rejected   = 'rejected';
    case Failed     = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Requested  => 'Talep Alındı',
            self::Processing => 'İşleniyor',
            self::Completed  => 'Tamamlandı',
            self::Rejected   => 'Reddedildi',
            self::Failed     => 'Başarısız',
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed, self::Rejected, self::Failed => true,
            default                                       => false,
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }
}
