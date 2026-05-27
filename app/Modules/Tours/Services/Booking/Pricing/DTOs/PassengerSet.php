<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Booking\Pricing\DTOs;

/**
 * Bir cabin'e atanmış passenger composition'ı — PriceCalculator
 * strategy'leri buna göre total hesaplar.
 *
 * Cruise için: BookingDraft.passengers içinden cabin_id'ye göre filtrelenip
 * oluşturulur (bir cabin'de 1-4 yolcu).
 *
 * Non-cruise için: tüm draft.passengers tek PassengerSet'e konur
 * (cabin_id NULL, generic pricing).
 *
 * Yaş bilgileri PassengerSet içinde tutulur — TourCabinPrice'taki
 * child_age_min/max + baby_age_min/max ile karşılaştırılarak çocuk/
 * bebek/yetişkin sınıflandırması yapılır.
 */
final class PassengerSet
{
    /**
     * @param array<int, int|null> $ages   Her yolcunun yaşı (null = bilinmiyor → adult kabul edilir)
     * @param array<int, string>   $labels Her yolcunun gösterim adı ("John Doe", "Mary Smith")
     */
    public function __construct(
        public readonly array $ages,
        public readonly array $labels = [],
        public readonly ?int $cabinId = null,
    ) {
    }

    public function count(): int
    {
        return count($this->ages);
    }

    /**
     * Adult / child / baby sayılarını verilen yaş aralıklarına göre
     * dağıt.  TourCabinPrice.classifyAge() ile uyumlu mantık.
     *
     * @return array{adult: int, child: int, baby: int}
     */
    public function distribution(
        int $childMin = 2,
        int $childMax = 11,
        int $babyMin = 0,
        int $babyMax = 1,
    ): array {
        $adult = 0;
        $child = 0;
        $baby  = 0;

        foreach ($this->ages as $age) {
            if ($age === null) {
                $adult++;
                continue;
            }
            if ($age >= $babyMin && $age <= $babyMax) {
                $baby++;
            } elseif ($age >= $childMin && $age <= $childMax) {
                $child++;
            } else {
                $adult++;
            }
        }

        return compact('adult', 'child', 'baby');
    }
}
