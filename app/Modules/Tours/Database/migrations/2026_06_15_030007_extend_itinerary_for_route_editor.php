<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.f (Rota editörü)
     *
     * Eski sistem Tab 2 "Rota Takvimi" / "GECE PLANI" alanları için
     * eksik kolonlar:
     *   - tour_itineraries.origin_port_id  → "Tur Çıkış Şehri" (hareket limanı)
     *   - tour_itinerary_stops.title        → "Şehir / Bölge / Liman / Başlık"
     *   - tour_itinerary_stops.accommodation→ "Konaklama" (Gemi / otel / apart)
     *   - tour_itinerary_stops.description  → per-stop "Tur Programı"
     *
     * Idempotent (hasColumn guard).
     */
    public function up(): void
    {
        Schema::table('tour_itineraries', function (Blueprint $table) {
            if (! Schema::hasColumn('tour_itineraries', 'origin_port_id')) {
                $table->unsignedBigInteger('origin_port_id')->nullable()->after('language_id');
                $table->foreign('origin_port_id')->references('id')->on('ports')->nullOnDelete();
                $table->index('origin_port_id');
            }
        });

        Schema::table('tour_itinerary_stops', function (Blueprint $table) {
            if (! Schema::hasColumn('tour_itinerary_stops', 'title')) {
                $table->string('title', 255)->nullable()->after('port_id');
            }
            if (! Schema::hasColumn('tour_itinerary_stops', 'accommodation')) {
                $table->string('accommodation', 255)->nullable()->after('title');
            }
            if (! Schema::hasColumn('tour_itinerary_stops', 'description')) {
                $table->longText('description')->nullable()->after('accommodation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tour_itineraries', function (Blueprint $table) {
            $table->dropForeign(['origin_port_id']);
            $table->dropColumn('origin_port_id');
        });
        Schema::table('tour_itinerary_stops', function (Blueprint $table) {
            $table->dropColumn(['title', 'accommodation', 'description']);
        });
    }
};
