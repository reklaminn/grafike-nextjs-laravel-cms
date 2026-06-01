<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

/** Estetik Dermal — TEMA 2 (Clinical Luxury). Tenant 'estetik_dermal'a scope'lu. */
class EstetikDermalV2ThemeSeeder extends Seeder
{
    public function run(): void
    {
        Theme::updateOrCreate(
            ['slug'=>'estetikdermal-v2','tenant_id'=>'estetik_dermal'],
            ['tenant_id'=>'estetik_dermal','module'=>null,'name'=>'Estetik Dermal — Tema 2 (Clinical Luxury)',
             'engine'=>'nextjs-basic-html',
             'tokens_json'=>['color_primary'=>'#E8702A','color_secondary'=>'#FCFAF7','color_accent'=>'#F4A14E',
                'radius_card'=>'16px','radius_button'=>'999px','container_width'=>'1240px'],
             'assets_json'=>['css'=>[],'js'=>[]],'settings_schema_json'=>[],'is_active'=>true]
        );
        $this->command?->info('Tema 2 (Clinical Luxury) kuruldu.');
    }
}
