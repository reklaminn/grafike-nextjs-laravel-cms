<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.g
     *
     * 3. taksonomi (kampanya) için TourTag'e tip ayracı.  Eski sistemde
     * Tab 1'de "Tur Seçenekleri" (marketing) + "Kampanya Kategorisi"
     * ayrı listeler; ikisini tek tabloda `tag_type` ile ayırıyoruz:
     *
     *   marketing → "Mini Cruise", "Ultra Lüks", "Aile Dostu" (özellik filtresi)
     *   campaign  → "Erken Rezervasyon", "Son Dakika İndirimi" (zaman/promosyon)
     *
     * Phase 5'te campaign tag'leri discount-taşıyan Campaign modeline
     * evrilebilir; şimdilik tag olarak yeterli (Tab 1 + Tab 4 tarihe özel).
     *
     * Idempotent (hasColumn guard).
     */
    public function up(): void
    {
        Schema::table('tour_tags', function (Blueprint $table) {
            if (! Schema::hasColumn('tour_tags', 'tag_type')) {
                $table->string('tag_type', 20)->default('marketing')->after('slug');
                $table->index('tag_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tour_tags', function (Blueprint $table) {
            $table->dropIndex(['tag_type']);
            $table->dropColumn('tag_type');
        });
    }
};
