<?php

namespace Database\Seeders;

use App\Models\SectionTemplate;
use App\Models\Theme;
use Illuminate\Database\Seeder;

/**
 * Boblanlı Yapı — ALAN-TABANLI (field) section template'leri (central, tenant 'boblanliyapi').
 * Her bölüm ayrı tanım dosyası: database/seeders/boblanli/fields/*.php
 * Bölümler paylaşılan chrome CSS/JS'ini kullanır (svc-card, gal, data-reveal, data-count, .field).
 * Mustache: {{alan}} escaped, {{{alan}}} ham, repeater X → {{{X_html}}} + schema['X'] type=repeater.
 */
class BoblanliFieldChromeSeeder extends Seeder
{
    private const TENANT_ID = 'boblanliyapi';

    public function run(): void
    {
        $theme = Theme::where('slug','boblanli')->where('tenant_id',self::TENANT_ID)->first()
              ?? Theme::where('slug','boblanli')->first();
        if (! $theme) { $this->command?->warn('boblanli teması yok — önce BoblanliThemeSeeder.'); return; }

        $files = glob(__DIR__.'/boblanli/fields/*.php') ?: [];
        sort($files);
        $n = 0;
        foreach ($files as $f) {
            $def = require $f;
            if (! is_array($def) || empty($def['variation'])) {
                $this->command?->warn('Geçersiz alan tanımı: '.basename($f)); continue;
            }
            SectionTemplate::updateOrCreate(
                ['theme_id'=>$theme->id,'type'=>'content-block','variation'=>$def['variation']],
                ['tenant_id'=>self::TENANT_ID,'module'=>null,'render_mode'=>'html',
                 'name'=>$def['name'] ?? $def['variation'],
                 'html_template'=>$def['html'] ?? '',
                 'schema_json'=>$def['schema'] ?? [],
                 'default_content_json'=>$def['default'] ?? [],
                 'is_active'=>true]
            );
            $n++;
        }
        $this->command?->info("BoblanliFieldChromeSeeder: {$n} alan-şablonu kuruldu.");
    }
}
