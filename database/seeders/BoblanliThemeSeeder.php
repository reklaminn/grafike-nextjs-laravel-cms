<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

/**
 * Boblanlı Yapı — TEMA (central, tenant 'boblanliyapi'a scope'lu).
 * Kurumsal tek-sayfa inşaat sitesi. Palet (docs/design_handoff_boblanli_yapi/README.md):
 *   antrasit #1A1A1A · kırmızı vurgu #E63329 (hover #c9271f) · keskin köşe (radius 0).
 * Fontlar: Space Grotesk (başlık) + Inter (gövde).
 */
class BoblanliThemeSeeder extends Seeder
{
    private const TENANT_ID = 'boblanliyapi';

    public function run(): void
    {
        Theme::updateOrCreate(
            ['slug' => 'boblanli', 'tenant_id' => self::TENANT_ID],
            [
                'tenant_id'            => self::TENANT_ID,
                'module'               => null,
                'name'                 => 'Boblanlı Yapı',
                'engine'               => 'nextjs-basic-html',
                'tokens_json'          => [
                    'color_primary'   => '#E63329', // kırmızı vurgu
                    'color_secondary' => '#F4F4F4', // açık gri bölüm
                    'color_accent'    => '#c9271f', // koyu kırmızı (hover)
                    'radius_card'     => '0px',     // keskin köşe dili
                    'radius_button'   => '0px',
                    'container_width' => '1220px',
                ],
                'assets_json'          => ['css' => [], 'js' => []],
                'settings_schema_json' => [],
                'is_active'            => true,
            ]
        );

        $this->command?->info('BoblanliThemeSeeder: boblanli teması kuruldu.');
    }
}
