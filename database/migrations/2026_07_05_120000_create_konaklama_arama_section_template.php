<?php

use App\Models\SectionTemplate;
use App\Models\Theme;
use Illuminate\Database\Migrations\Migration;

/**
 * "Konaklama Arama" (genel) Block Şablonu — tema-scoped, tüm tenant'larda
 * kullanılabilir. Frontend HotelSearchSection bileşeni type='konaklama-arama'
 * bloğunu render eder (tek date-range + geçmiş-pasif + kapasiteli misafir);
 * bu şablon admin'e Block Ayarları alanlarını (etiketler, hedef, overlap…) sağlar.
 *
 * Listeleme şablonuyla aynı desen: render_mode='html' + placeholder html_template
 * (yalnızca editör önizlemesi); canlı site type='konaklama-arama' → section-renderer
 * → HotelSearchSection. Seeder deploy'da koşmadığından migration ile eklenir.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Theme::query()->get() as $theme) {
            $exists = SectionTemplate::query()
                ->where('theme_id', $theme->id)
                ->where('type', 'konaklama-arama')
                ->where('variation', 'genel')
                ->exists();

            if ($exists) {
                continue;
            }

            SectionTemplate::create([
                'theme_id'     => $theme->id,
                'tenant_id'    => null,
                'type'         => 'konaklama-arama',
                'variation'    => 'genel',
                'name'         => 'Konaklama Arama (tarih aralığı + kapasite)',
                'render_mode'  => 'html',
                'html_template' => '<div style="padding:1.5rem;border:1px dashed var(--color-border,#e5e7eb);border-radius:.75rem;text-align:center;color:var(--color-text-soft,#6b7280);font-size:.9rem"><strong>Konaklama Arama</strong> — canlı sitede tek date-range takvim (geçmiş günler pasif) + daire tipi + kapasiteli misafir seçici gösterilir.</div>',
                'schema_json'  => [
                    'eyebrow'        => ['type' => 'text',   'label' => 'Üst etiket (opsiyonel)'],
                    'title'          => ['type' => 'text',   'label' => 'Başlık (opsiyonel)'],
                    'date_label'     => ['type' => 'text',   'label' => 'Tarih alanı etiketi'],
                    'roomtype_label' => ['type' => 'text',   'label' => 'Daire tipi etiketi'],
                    'adults_label'   => ['type' => 'text',   'label' => 'Yetişkin etiketi'],
                    'children_label' => ['type' => 'text',   'label' => 'Çocuk etiketi'],
                    'search_label'   => ['type' => 'text',   'label' => 'Ara butonu metni'],
                    'any_room_label' => ['type' => 'text',   'label' => 'Daire tipi "farketmez" metni'],
                    'action_target'  => ['type' => 'text',   'label' => 'Arama hedefi (örn. /rezervasyon)'],
                    'overlap'        => ['type' => 'number', 'label' => 'Hero üstüne bindirme (px, boş = 0)'],
                ],
                'default_content_json' => [
                    'eyebrow'        => '',
                    'title'          => '',
                    'date_label'     => 'GİRİŞ — ÇIKIŞ',
                    'roomtype_label' => 'DAİRE TİPİ',
                    'adults_label'   => 'YETİŞKİN',
                    'children_label' => 'ÇOCUK',
                    'search_label'   => 'Müsaitlik Ara',
                    'any_room_label' => 'Farketmez',
                    'action_target'  => '/rezervasyon',
                    'overlap'        => '',
                ],
                'is_active'    => true,
            ]);
        }
    }

    public function down(): void
    {
        SectionTemplate::query()
            ->whereNull('tenant_id')
            ->where('type', 'konaklama-arama')
            ->forceDelete();
    }
};
