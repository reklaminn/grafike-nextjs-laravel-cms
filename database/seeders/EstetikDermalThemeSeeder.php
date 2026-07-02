<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class EstetikDermalThemeSeeder extends Seeder
{
    /** Bu temalar yalnızca bu tenant'ın panelinde görünür (global değil). */
    private const TENANT_ID = 'estetik_dermal';

    public function run(): void
    {
        foreach ($this->themes() as $t) {
            Theme::updateOrCreate(
                ['slug' => $t['slug'], 'tenant_id' => self::TENANT_ID],
                [
                    'tenant_id'            => self::TENANT_ID,
                    'module'               => null,
                    'name'                 => $t['name'],
                    'engine'               => 'nextjs-basic-html',
                    'tokens_json'          => $t['tokens'],
                    'assets_json'          => ['css' => [], 'js' => []],
                    'settings_schema_json' => [],
                    'is_active'            => true,
                ]
            );
        }
    }

    private function themes(): array
    {
        // Ortak radius/container tokenları — tüm temalar ana temayla aynı.
        $base = [
            'radius_card'     => '18px',
            'radius_button'   => '999px',
            'container_width' => '1280px',
        ];

        return [
            [
                'slug'   => 'estetikdermal',
                'name'   => 'Estetik Dermal',
                'tokens' => array_merge([
                    'color_primary'   => '#E8702A',
                    'color_secondary' => '#FBF4EE',
                    'color_accent'    => '#F4A14E',
                ], $base),
            ],
            [
                'slug'   => 'estetikdermal-skintech',
                'name'   => 'Estetik Dermal — Skin Tech',
                'tokens' => array_merge([
                    'color_primary'   => '#0C6E72',
                    'color_secondary' => '#EAF6F4',
                    'color_accent'    => '#3FBFA8',
                ], $base),
            ],
            [
                'slug'   => 'estetikdermal-seffiline',
                'name'   => 'Estetik Dermal — Seffiline',
                'tokens' => array_merge([
                    'color_primary'   => '#C98A6D',
                    'color_secondary' => '#FBF0EF',
                    'color_accent'    => '#E8A0A8',
                ], $base),
            ],
            [
                'slug'   => 'estetikdermal-aespio',
                'name'   => 'Estetik Dermal — Grand Aespio',
                'tokens' => array_merge([
                    'color_primary'   => '#6C5CE0',
                    'color_secondary' => '#F0ECFB',
                    'color_accent'    => '#21D4B4',
                ], $base),
            ],
            [
                'slug'   => 'estetikdermal-woorhi',
                'name'   => 'Estetik Dermal — Woorhi',
                'tokens' => array_merge([
                    'color_primary'   => '#2DA8FF',
                    'color_secondary' => '#10131A',
                    'color_accent'    => '#16E0C8',
                ], $base),
            ],
            [
                'slug'   => 'estetikdermal-mi-medical',
                'name'   => 'Estetik Dermal — Mi Medical',
                'tokens' => array_merge([
                    'color_primary'   => '#C9A24B',
                    'color_secondary' => '#F7F3EA',
                    'color_accent'    => '#E3C97E',
                ], $base),
            ],
        ];
    }
}
