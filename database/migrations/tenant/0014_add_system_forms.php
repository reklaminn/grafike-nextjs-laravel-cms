<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1. forms tablosuna is_system kolonu ekle.
 * 2. Her tenant DB'sine İletişim Formu + E-Bülten'i seed et
 *    (eğer aynı slug ile form yoksa).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Kolon ekle ──────────────────────────────────────────────
        Schema::table('forms', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('is_active')
                  ->comment('Sistem formları silinemez ve adları değiştirilemez.');
        });

        // ── 2. Varsayılan sistem formlarını seed et ─────────────────────
        $now = now();

        $defaultForms = [
            [
                'slug'   => 'iletisim',
                'name'   => 'İletişim Formu',
                'desc'   => 'Ziyaretçilerin size ulaşabileceği genel iletişim formu.',
                'fields' => [
                    ['label' => 'Ad Soyad',     'name' => 'full_name',  'type' => 'text',     'is_required' => true,  'sort_order' => 1],
                    ['label' => 'E-posta',       'name' => 'email',      'type' => 'email',    'is_required' => true,  'sort_order' => 2],
                    ['label' => 'Telefon',       'name' => 'phone',      'type' => 'phone',    'is_required' => false, 'sort_order' => 3],
                    ['label' => 'Konu',          'name' => 'subject',    'type' => 'text',     'is_required' => false, 'sort_order' => 4],
                    ['label' => 'Mesajınız',     'name' => 'message',    'type' => 'textarea', 'is_required' => true,  'sort_order' => 5],
                ],
            ],
            [
                'slug'   => 'ebulten',
                'name'   => 'E-Bülten',
                'desc'   => 'E-posta bülteninize abone olun.',
                'fields' => [
                    ['label' => 'Ad',    'name' => 'first_name', 'type' => 'text',  'is_required' => false, 'sort_order' => 1],
                    ['label' => 'E-posta', 'name' => 'email',    'type' => 'email', 'is_required' => true,  'sort_order' => 2],
                ],
            ],
        ];

        foreach ($defaultForms as $formDef) {
            // Zaten varsa atla
            if (DB::table('forms')->where('slug', $formDef['slug'])->exists()) {
                // Mevcut formu is_system=true yap
                DB::table('forms')->where('slug', $formDef['slug'])->update(['is_system' => true]);
                continue;
            }

            $formId = DB::table('forms')->insertGetId([
                'name'             => $formDef['name'],
                'slug'             => $formDef['slug'],
                'description'      => $formDef['desc'],
                'is_active'        => true,
                'is_system'        => true,
                'requires_captcha' => true,
                'allow_submissions'=> true,
                'save_to_database' => true,
                'allow_listing'    => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            foreach ($formDef['fields'] as $field) {
                DB::table('form_fields')->insert([
                    'form_id'     => $formId,
                    'label'       => $field['label'],
                    'name'        => $field['name'],
                    'type'        => $field['type'],
                    'is_required' => $field['is_required'],
                    'sort_order'  => $field['sort_order'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // is_system=true olan formları sil (seed edilenler)
        $ids = DB::table('forms')->where('is_system', true)->pluck('id');
        if ($ids->isNotEmpty()) {
            DB::table('form_fields')->whereIn('form_id', $ids)->delete();
            DB::table('forms')->whereIn('id', $ids)->delete();
        }

        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
