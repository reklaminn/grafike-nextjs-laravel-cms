<?php

declare(strict_types=1);

namespace App\Modules\Tours\Database\Seeders;

use App\Models\Language;
use App\Modules\Tours\Models\CabinCategory;
use App\Modules\Tours\Models\CabinCategoryTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 5 default cabin category seed — endüstri standart cruise kabin tipleri.
 *
 * Çağrılan yer:
 *   - ModuleManager::install('tours') sonrası otomatik (Phase 1.5 install pipeline)
 *   - Manuel: php artisan tenants:seed --class=CabinCategorySeeder
 *
 * Default seed bir kez düşer (idempotent — slug'a göre find-or-create).
 * Admin sonradan ekleyebilir / silebilir / yeniden adlandırabilir.
 *
 * Sıralama (sort_order) endüstri konvansiyonu — ucuzdan pahalıya.
 */
class CabinCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug'       => 'inside',
                'sort_order' => 1,
                'icon'       => '🛏',
                'translations' => [
                    'tr' => ['name' => 'İç Kabin',          'description' => 'Penceresiz, geminin iç kısmında, en ekonomik kabin tipi.'],
                    'en' => ['name' => 'Inside Cabin',      'description' => 'Windowless, located in the ship interior — the most economical category.'],
                ],
            ],
            [
                'slug'       => 'outside',
                'sort_order' => 2,
                'icon'       => '🪟',
                'translations' => [
                    'tr' => ['name' => 'Dış Kabin',         'description' => 'Pencereli, deniz manzaralı, gün ışığı alır.'],
                    'en' => ['name' => 'Outside Cabin',     'description' => 'With window, sea view, natural daylight.'],
                ],
            ],
            [
                'slug'       => 'ocean_view',
                'sort_order' => 3,
                'icon'       => '🌊',
                'translations' => [
                    'tr' => ['name' => 'Okyanus Manzaralı', 'description' => 'Geniş pencereli, panoramik okyanus manzarası sunar.'],
                    'en' => ['name' => 'Ocean View',        'description' => 'Large window, panoramic ocean view.'],
                ],
            ],
            [
                'slug'       => 'balcony',
                'sort_order' => 4,
                'icon'       => '🌅',
                'translations' => [
                    'tr' => ['name' => 'Balkonlu Kabin',    'description' => 'Özel balkonlu, açık hava ve doğrudan manzara.'],
                    'en' => ['name' => 'Balcony Cabin',     'description' => 'Private balcony, fresh air and direct view.'],
                ],
            ],
            [
                'slug'       => 'suite',
                'sort_order' => 5,
                'icon'       => '✨',
                'translations' => [
                    'tr' => ['name' => 'Suite',             'description' => 'Lüks, geniş alanlı, ayrı oturma bölümü ve premium hizmetler.'],
                    'en' => ['name' => 'Suite',             'description' => 'Luxury, spacious, separate living area and premium amenities.'],
                ],
            ],
        ];

        // Language ID resolution — central DB'den dil koduna göre.
        // Tenant kurulduğunda en az TR olmalı; EN opsiyonel.
        $languageIds = $this->resolveLanguageIds(['tr', 'en']);

        foreach ($categories as $row) {
            /** @var CabinCategory $category */
            $category = CabinCategory::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'sort_order' => $row['sort_order'],
                    'icon'       => $row['icon'],
                    'is_active'  => true,
                ],
            );

            foreach ($row['translations'] as $langCode => $copy) {
                $languageId = $languageIds[$langCode] ?? null;
                if ($languageId === null) {
                    continue; // o dil bu tenant'ta yok, atla
                }

                CabinCategoryTranslation::query()->updateOrCreate(
                    ['cabin_category_id' => $category->id, 'language_id' => $languageId],
                    ['name' => $copy['name'], 'description' => $copy['description']],
                );
            }
        }
    }

    /**
     * @param  array<int, string> $codes
     * @return array<string, int>  ['tr' => 1, 'en' => 2]
     */
    private function resolveLanguageIds(array $codes): array
    {
        // Language modeli central DB'de — `code` (örn. 'tr') -> id mapping.
        return Language::query()
            ->whereIn('code', $codes)
            ->pluck('id', 'code')
            ->toArray();
    }
}
