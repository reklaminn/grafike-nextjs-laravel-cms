<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.g (Tab 3 Genel Fiyatlar)
     *
     * Eski sistem Tab 3:
     *   - "EKSTRA AKTİVİTE VE ÜCRETLER" → online tahsil edilMEyen bilgi
     *     amaçlı kalemler (vize, havaalanı vergisi).  pricing_mode='info_only'.
     *   - "Genel Açıklama" → fiyat disclaimer (doluluğa göre değişir vb.)
     *
     * tour_extras eklemeleri:
     *   - currency       → her ekstra kendi para biriminde (EUR vize gibi)
     *   - per_person     → kişi başı çarpan (legacy "Kişi Başı" checkbox)
     *   - info_extra_id  → tenant_info_extras master referansı (snapshot)
     *
     * tour_translations:
     *   - price_disclaimer → per-language fiyat açıklaması
     *
     * Idempotent (hasColumn guard).
     */
    public function up(): void
    {
        Schema::table('tour_extras', function (Blueprint $table) {
            if (! Schema::hasColumn('tour_extras', 'currency')) {
                $table->string('currency', 3)->nullable()->after('price');
            }
            if (! Schema::hasColumn('tour_extras', 'per_person')) {
                $table->boolean('per_person')->default(false)->after('currency');
            }
            if (! Schema::hasColumn('tour_extras', 'info_extra_id')) {
                $table->unsignedBigInteger('info_extra_id')->nullable()->after('tour_id');
                $table->foreign('info_extra_id')->references('id')->on('tenant_info_extras')->nullOnDelete();
                $table->index('info_extra_id');
            }
        });

        Schema::table('tour_translations', function (Blueprint $table) {
            if (! Schema::hasColumn('tour_translations', 'price_disclaimer')) {
                $table->longText('price_disclaimer')->nullable()->after('important_info');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tour_extras', function (Blueprint $table) {
            $table->dropForeign(['info_extra_id']);
            $table->dropColumn(['currency', 'per_person', 'info_extra_id']);
        });
        Schema::table('tour_translations', function (Blueprint $table) {
            $table->dropColumn('price_disclaimer');
        });
    }
};
