<?php

declare(strict_types=1);

namespace App\Modules\Tours\Enums;

/**
 * Tour sales / CTA mode.  Frontend bu enum'a göre farklı UI render eder
 * (Phase 4'te wired) — eski sistem Tab 1 "Satış Durumu" dropdown'unun
 * karşılığı.  5 değer (Araç Kiralama Formu kullanıcı tarafından
 * "gereksiz" olarak işaretlendi, atlandı):
 *
 *   live_payment              — Satışta Ödemeli
 *                               Standart online checkout + Iyzico 3DS
 *                               Booking + capacity lock + payment
 *
 *   live_contact              — Satışta Ödemesiz
 *                               Booking oluşur (capacity lock) ama ödeme yok
 *                               Operatör müşteriyi arar, manuel tahsil
 *
 *   quote_with_accommodation  — Teklif Al Formu (Konaklamalı)
 *                               Quote request form, ek konaklama alanları
 *                               Booking oluşmaz, sadece TourQuoteRequest kaydı
 *
 *   quote_without_accommodation — Teklif Al Formu 2 (Konaklamasız)
 *                                Quote request form, konaklama yok
 *
 *   info_only                 — Sadece Bilgi
 *                               CTA gösterilmez, sadece içerik
 */
enum SalesStatus: string
{
    case LivePayment                = 'live_payment';
    case LiveContact                = 'live_contact';
    case QuoteWithAccommodation     = 'quote_with_accommodation';
    case QuoteWithoutAccommodation  = 'quote_without_accommodation';
    case InfoOnly                   = 'info_only';

    public function label(): string
    {
        return match ($this) {
            self::LivePayment               => 'Satışta Ödemeli',
            self::LiveContact               => 'Satışta Ödemesiz',
            self::QuoteWithAccommodation    => 'Teklif Al Formu (Konaklamalı)',
            self::QuoteWithoutAccommodation => 'Teklif Al Formu 2 (Konaklamasız)',
            self::InfoOnly                  => 'Sadece Bilgi',
        };
    }

    public function ctaText(): string
    {
        return match ($this) {
            self::LivePayment               => 'Hemen Rezerve Et',
            self::LiveContact               => 'Rezervasyon Talebi',
            self::QuoteWithAccommodation,
            self::QuoteWithoutAccommodation => 'Teklif Al',
            self::InfoOnly                  => '', // CTA yok
        };
    }

    public function allowsOnlinePayment(): bool
    {
        return $this === self::LivePayment;
    }

    public function createsBooking(): bool
    {
        return in_array($this, [self::LivePayment, self::LiveContact], true);
    }

    public function showsCta(): bool
    {
        return $this !== self::InfoOnly;
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }
}
