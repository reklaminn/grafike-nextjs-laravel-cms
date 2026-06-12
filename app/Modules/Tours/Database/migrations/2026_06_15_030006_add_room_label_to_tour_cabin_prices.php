<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.e UX rework
     *
     * Eski sistem Tab 4 "Yeni Grup Ekle" formundaki oda satırları serbest
     * "ODA ADI" metni taşıyor (cruise dışı turlarda "Standart Oda" gibi,
     * cabin master'a bağlı olmadan).  cabin_id opsiyonel kalmaya devam
     * ediyor; room_label her satırda görünen ad.
     *
     *   - room_label  → "ODA ADI / TÜRÜ" (Standart Oda, İç Kabin, Suite)
     *   - deck_label  → "Güverte / Kat" serbest metin (cabin.deck_name'den
     *                   bağımsız, gruba özel override)
     *
     * Idempotent (hasColumn guard) — drift-recovery güvenli.
     */
    public function up(): void
    {
        Schema::table('tour_cabin_prices', function (Blueprint $table) {
            if (! Schema::hasColumn('tour_cabin_prices', 'room_label')) {
                $table->string('room_label', 200)->nullable()->after('cabin_id');
            }
            if (! Schema::hasColumn('tour_cabin_prices', 'deck_label')) {
                $table->string('deck_label', 120)->nullable()->after('room_label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tour_cabin_prices', function (Blueprint $table) {
            $table->dropColumn(['room_label', 'deck_label']);
        });
    }
};
