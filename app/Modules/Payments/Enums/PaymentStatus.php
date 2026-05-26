<?php

declare(strict_types=1);

namespace App\Modules\Payments\Enums;

/**
 * Gateway-level payment lifecycle.
 *
 * Distinct from BookingStatus — a single Booking can have multiple
 * PaymentTransaction rows (initial deposit, balance charge, retry
 * after failure, …) and each carries its own status.
 *
 *   pending             — created in our DB, not yet sent to gateway
 *   awaiting_3ds        — 3DS HTML returned to user, waiting for bank flow
 *   awaiting_capture    — bank approved 3DS, ready for capture/auth call
 *   captured            — gateway confirmed funds moved
 *   failed              — gateway rejected at any stage
 *   refunded_full       — entire amount refunded (terminal)
 *   refunded_partial    — some amount refunded; further refunds possible
 *   expired             — 3DS link expired without completion
 *   cancelled           — user / admin cancelled before capture
 */
enum PaymentStatus: string
{
    case Pending           = 'pending';
    case Awaiting3DS       = 'awaiting_3ds';
    case AwaitingCapture   = 'awaiting_capture';
    case Captured          = 'captured';
    case Failed            = 'failed';
    case RefundedFull      = 'refunded_full';
    case RefundedPartial   = 'refunded_partial';
    case Expired           = 'expired';
    case Cancelled         = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending           => 'Beklemede',
            self::Awaiting3DS       => '3DS Bekleniyor',
            self::AwaitingCapture   => 'Yakalama Bekleniyor',
            self::Captured          => 'Tahsil Edildi',
            self::Failed            => 'Başarısız',
            self::RefundedFull      => 'Tam İade',
            self::RefundedPartial   => 'Kısmi İade',
            self::Expired           => 'Süresi Doldu',
            self::Cancelled         => 'İptal Edildi',
        };
    }

    public function isSuccessful(): bool
    {
        return match ($this) {
            self::Captured, self::RefundedPartial => true,
            default                               => false,
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Captured, self::Failed, self::RefundedFull,
            self::Expired, self::Cancelled                    => true,
            default                                           => false,
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }
}
