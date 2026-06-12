<?php

declare(strict_types=1);

namespace App\Modules\Tours\Database\Seeders;

use App\Models\Language;
use App\Modules\Tours\Models\TourTag;
use App\Modules\Tours\Models\TourTagTranslation;
use Illuminate\Database\Seeder;

/**
 * 12 default marketing tag seed — eski sistemin Tab 1 "Tur Seçenekleri"
 * checkbox listesinden birebir.
 *
 * Tenant install sonrası otomatik düşer.  Admin sonradan ekleyebilir/
 * silebilir/yeniden adlandırabilir.
 */
class TourTagSeeder extends Seeder
{
    public function run(): void
    {
        // Pazarlama etiketleri (özellik filtresi) — type=marketing
        // Kampanyalar (zaman/promosyon) — type=campaign
        $tags = [
            ['slug' => 'cruise-only',         'icon' => '🚢', 'tr' => 'Sadece Gemi Turu',          'en' => 'Cruise Only',           'type' => 'marketing'],
            ['slug' => 'mini-cruise',         'icon' => '⚓', 'tr' => 'Mini Cruise',                'en' => 'Mini Cruise',           'type' => 'marketing'],
            ['slug' => 'river-cruise',        'icon' => '🌊', 'tr' => 'Nehir Turları',              'en' => 'River Cruises',         'type' => 'marketing'],
            ['slug' => 'flight-package',      'icon' => '✈️', 'tr' => 'Uçaklı Paket Gemi Turları',  'en' => 'Flight Package Cruises','type' => 'marketing'],
            ['slug' => 'ultra-luxury',        'icon' => '✨', 'tr' => 'Ultra Lüks Gemiler',         'en' => 'Ultra Luxury Ships',    'type' => 'marketing'],
            ['slug' => 'new-year',            'icon' => '🎆', 'tr' => 'Yılbaşı Gemi Turları',       'en' => 'New Year Cruises',      'type' => 'marketing'],
            ['slug' => 'holiday',             'icon' => '🎊', 'tr' => 'Bayram Turları',             'en' => 'Holiday Tours',         'type' => 'marketing'],
            ['slug' => 'eid-al-adha',         'icon' => '🕌', 'tr' => 'Kurban Bayramı',             'en' => 'Eid al-Adha',           'type' => 'marketing'],
            ['slug' => 'eid-al-fitr',         'icon' => '🌙', 'tr' => 'Şeker Bayramı',              'en' => 'Eid al-Fitr',           'type' => 'marketing'],
            ['slug' => 'semester-break',      'icon' => '📚', 'tr' => 'Sömestre Turları',           'en' => 'Semester Break Tours',  'type' => 'marketing'],
            ['slug' => 'family-friendly',     'icon' => '👨‍👩‍👧', 'tr' => 'Aile Dostu Turlar',         'en' => 'Family-Friendly Tours', 'type' => 'marketing'],

            ['slug' => 'early-booking',       'icon' => '🐦', 'tr' => 'Erken Rezervasyon Fırsatları','en' => 'Early Booking Deals',  'type' => 'campaign'],
            ['slug' => 'last-minute-deals',   'icon' => '⏰', 'tr' => 'Son Dakika İndirimleri',     'en' => 'Last Minute Discounts', 'type' => 'campaign'],
            ['slug' => 'second-guest-free',   'icon' => '🎁', 'tr' => '2. Kişi Ücretsiz',           'en' => '2nd Guest Free',        'type' => 'campaign'],
            ['slug' => 'one-full-one-half',   'icon' => '🧮', 'tr' => '1 Tam 1 Yarım',              'en' => '1 Full 1 Half',         'type' => 'campaign'],
            ['slug' => 'tl-holiday',          'icon' => '₺',  'tr' => 'TL ile Tatil Fırsatları',    'en' => 'Holiday in TRY',        'type' => 'campaign'],
        ];

        $languageIds = $this->resolveLanguageIds(['tr', 'en']);

        foreach ($tags as $i => $row) {
            /** @var TourTag $tag */
            $tag = TourTag::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'tag_type'   => $row['type'] ?? 'marketing',
                    'icon'       => $row['icon'],
                    'sort_order' => $i + 1,
                    'is_active'  => true,
                ],
            );

            foreach (['tr', 'en'] as $code) {
                $languageId = $languageIds[$code] ?? null;
                if ($languageId === null) {
                    continue;
                }
                TourTagTranslation::query()->updateOrCreate(
                    ['tour_tag_id' => $tag->id, 'language_id' => $languageId],
                    ['name' => $row[$code]],
                );
            }
        }
    }

    /**
     * @return array<string, int>
     */
    private function resolveLanguageIds(array $codes): array
    {
        return Language::query()
            ->whereIn('code', $codes)
            ->pluck('id', 'code')
            ->toArray();
    }
}
