<?php

declare(strict_types=1);

namespace App\Modules\Tours\Enums;

/**
 * Tour-level pricing mode.  Set per-tour, drives the OVERALL pricing
 * strategy that the booking wizard surfaces.
 *
 * Eski sistem Tab 1 "Fiyatlandırma Türü" dropdown'ının karşılığı —
 * 3 değer:
 *
 *   per_person       — Kişibaşı Fiyat
 *                      Her yolcu kendi tier'ı ile fiyatlanır
 *                      (single/double/triple/quad/child/baby).
 *                      Cruise için tipik.  TourCabinPrice 6-tier matrix
 *                      kullanır + CalculationMethod ile total hesaplar.
 *
 *   per_person_group — Kişibaşı Grup Fiyatı
 *                      Aynı tier matrix ama TourPriceGroup'a min_persons
 *                      enforced.  Grup rezervasyonlarına (10+ kişi)
 *                      indirimli per-person rate.
 *
 *   per_reservation  — Rezervasyon Başına Fiyat
 *                      Tek flat rakam, kişi sayısından bağımsız.
 *                      Charter cruise, özel kiralama, günlük tur grupları.
 *                      TourCabinPrice'ta sadece price_single (flat) +
 *                      calc_method=flat_cabin kullanılır.
 */
enum PricingMode: string
{
    case PerPerson      = 'per_person';
    case PerPersonGroup = 'per_person_group';
    case PerReservation = 'per_reservation';

    public function label(): string
    {
        return match ($this) {
            self::PerPerson      => 'Kişibaşı Fiyat',
            self::PerPersonGroup => 'Kişibaşı Grup Fiyatı',
            self::PerReservation => 'Rezervasyon Başına Fiyat',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PerPerson      => 'Her yolcu kendi tier\'ı ile (single/double/3rd/4th/child/baby) fiyatlanır',
            self::PerPersonGroup => 'Aynı tier yapısı ama gruba minimum kişi sayısı zorunluluğu var',
            self::PerReservation => 'Tek flat fiyat, kişi sayısından bağımsız (charter/grup rezervasyonu)',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }
}
