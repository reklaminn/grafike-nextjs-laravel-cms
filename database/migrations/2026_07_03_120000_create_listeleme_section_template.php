<?php

use App\Models\SectionTemplate;
use App\Models\Theme;
use Illuminate\Database\Migrations\Migration;

/**
 * "Listeleme" (genel) Block Şablonu — merkezi/global (tenant_id null), tüm
 * tenant'larda kullanılabilir. Frontend ListingSection bileşeni type='listeleme'
 * bloğunu content.source'a göre (oda tipleri / yazılar / alt sayfalar) render eder;
 * bu şablon admin'e Block Ayarları alanlarını (source, item_template, …) sağlar.
 *
 * Seeder deploy'da koşmadığından (yalnızca migrate) template'i migration ile ekliyoruz.
 */
return new class extends Migration
{
    public function up(): void
    {
        // section_templates tema-scoped (theme_id NOT NULL, unique theme+type+variation).
        // Her tema için birer "Listeleme" şablonu → tenant hangi temayı kullanırsa
        // kullansın Block Ayarları'nda bu blok görünür. Testte tema yoksa no-op.
        foreach (Theme::query()->get() as $theme) {
            $exists = SectionTemplate::query()
                ->where('theme_id', $theme->id)
                ->where('type', 'listeleme')
                ->where('variation', 'genel')
                ->exists();

            if ($exists) {
                continue;
            }

            SectionTemplate::create([
                'theme_id'     => $theme->id,
                'tenant_id'    => null,
                'type'         => 'listeleme',
                'variation'    => 'genel',
                'name'         => 'Listeleme (Oda Tipi / Yazı / Alt Sayfa)',
                'render_mode'  => 'html',
            // Editör önizlemesinde ipucu; canlı site type='listeleme' → ListingSection.
            'html_template' => '<div style="padding:1.5rem;border:1px dashed var(--color-border,#e5e7eb);border-radius:.75rem;text-align:center;color:var(--color-text-soft,#6b7280);font-size:.9rem"><strong>Listeleme</strong> — canlı sitede seçili kaynak (oda tipleri / yazılar / alt sayfalar) otomatik listelenir.</div>',
            'schema_json'  => [
                'source' => [
                    'type'    => 'select',
                    'label'   => 'Kaynak',
                    'options' => [
                        ['value' => 'room_types', 'label' => 'Oda / Daire tipleri (Konaklama)'],
                        ['value' => 'articles',   'label' => 'Yazılar'],
                        ['value' => 'pages',      'label' => 'Alt sayfalar'],
                    ],
                ],
                'title'          => ['type' => 'text',     'label' => 'Başlık'],
                'subtitle'       => ['type' => 'text',     'label' => 'Üst etiket (küçük)'],
                'columns'        => ['type' => 'number',   'label' => 'Sütun sayısı (1-4, boş = otomatik)'],
                'limit'          => ['type' => 'number',   'label' => 'Maksimum öğe sayısı'],
                'reserve_label'  => ['type' => 'text',     'label' => 'Buton metni (oda tipleri)'],
                'reserve_target' => ['type' => 'text',     'label' => 'Buton hedefi (örn. /rezervasyon)'],
                'empty_text'     => ['type' => 'text',     'label' => 'Boş durum metni'],
                'item_template'  => ['type' => 'html',     'label' => 'Kart HTML şablonu (opsiyonel) — {{title}} {{summary}} {{price}} {{link}} {{{image}}}'],
            ],
            'default_content_json' => [
                'source'         => 'room_types',
                'title'          => 'Daireler',
                'subtitle'       => '',
                'columns'        => '',
                'limit'          => '',
                'reserve_label'  => 'Rezervasyon',
                'reserve_target' => '/rezervasyon',
                'empty_text'     => '',
                'item_template'  => '',
            ],
                'is_active'    => true,
            ]);
        }
    }

    public function down(): void
    {
        SectionTemplate::query()
            ->whereNull('tenant_id')
            ->where('type', 'listeleme')
            ->forceDelete();
    }
};
