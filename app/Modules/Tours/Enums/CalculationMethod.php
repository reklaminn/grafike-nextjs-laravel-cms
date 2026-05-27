<?php

declare(strict_types=1);

namespace App\Modules\Tours\Enums;

/**
 * Per-cabin calculation method.  TourCabinPrice.calculation_method
 * column'unun değeri.  Eski sistem Tab 4 (Pricing Group Form) içindeki
 * "Hesaplama Yöntemi" dropdown'unun karşılığı — 3 değer:
 *
 *   standart_doublex2 — Standart (Doublex2)
 *                       Toplam = price_double × passenger_count
 *                       Çoğu cruise için (kişi başı double × kişi sayısı).
 *                       1 kişi solo: price_single
 *                       3rd/4th kişi: ek price_triple / price_quad
 *
 *   person_sum         — Kişi Toplama (1+2+3+4)
 *                       Toplam = her n-th yolcunun tier price'ı
 *                       1. kişi: price_single
 *                       2. kişi: price_double
 *                       3. kişi: price_triple
 *                       4. kişi: price_quad
 *                       Child/baby ek olarak eklenir.
 *
 *   flat_cabin         — Tek Kabin Fiyatı
 *                       Toplam = price_single (flat cabin price)
 *                       Kişi sayısından bağımsız.  Suite/villa için.
 *                       per_reservation pricing mode ile birlikte kullanılır.
 */
enum CalculationMethod: string
{
    case StandartDoublex2 = 'standart_doublex2';
    case PersonSum        = 'person_sum';
    case FlatCabin        = 'flat_cabin';

    public function label(): string
    {
        return match ($this) {
            self::StandartDoublex2 => 'Standart (Doublex2)',
            self::PersonSum        => 'Kişi Toplama (1+2+3+4)',
            self::FlatCabin        => 'Tek Kabin Fiyatı',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::StandartDoublex2 => 'price_double × yolcu sayısı (3rd/4th eklenir)',
            self::PersonSum        => 'her yolcuya o pozisyonun price\'ı (1st=single, 2nd=double, ...)',
            self::FlatCabin        => 'flat kabin fiyatı, yolcu sayısı önemsiz',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }
}
