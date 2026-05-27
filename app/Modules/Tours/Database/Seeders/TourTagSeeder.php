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
        $tags = [
            ['slug' => 'cruise-only',         'icon' => '🚢', 'tr' => 'Sadece Gemi Turu',          'en' => 'Cruise Only'],
            ['slug' => 'mini-cruise',         'icon' => '⚓', 'tr' => 'Mini Cruise',                'en' => 'Mini Cruise'],
            ['slug' => 'river-cruise',        'icon' => '🌊', 'tr' => 'Nehir Turları',              'en' => 'River Cruises'],
            ['slug' => 'flight-package',      'icon' => '✈️', 'tr' => 'Uçaklı Paket Gemi Turları',  'en' => 'Flight Package Cruises'],
            ['slug' => 'ultra-luxury',        'icon' => '✨', 'tr' => 'Ultra Lüks Gemiler',         'en' => 'Ultra Luxury Ships'],
            ['slug' => 'last-minute',         'icon' => '⏰', 'tr' => 'Son Dakika Fırsatları',      'en' => 'Last Minute Deals'],
            ['slug' => 'new-year',            'icon' => '🎆', 'tr' => 'Yılbaşı Gemi Turları',       'en' => 'New Year Cruises'],
            ['slug' => 'holiday',             'icon' => '🎊', 'tr' => 'Bayram Turları',             'en' => 'Holiday Tours'],
            ['slug' => 'eid-al-adha',         'icon' => '🕌', 'tr' => 'Kurban Bayramı',             'en' => 'Eid al-Adha'],
            ['slug' => 'eid-al-fitr',         'icon' => '🌙', 'tr' => 'Şeker Bayramı',              'en' => 'Eid al-Fitr'],
            ['slug' => 'semester-break',      'icon' => '📚', 'tr' => 'Sömestre Turları',           'en' => 'Semester Break Tours'],
            ['slug' => 'family-friendly',     'icon' => '👨‍👩‍👧', 'tr' => 'Aile Dostu Turlar',         'en' => 'Family-Friendly Tours'],
        ];

        $languageIds = $this->resolveLanguageIds(['tr', 'en']);

        foreach ($tags as $i => $row) {
            /** @var TourTag $tag */
            $tag = TourTag::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
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
