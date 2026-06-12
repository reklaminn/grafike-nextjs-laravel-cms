<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.b
     *
     * Booking passengers tablosunun FK kolonlarını yeni master model'lere
     * göre refactor et.
     *
     * Eski (Phase 1):
     *   - booking_passengers.tour_cabin_type_id → tour_cabin_types  (drop edilecek)
     *   - booking_passengers.price_tier_id     → tour_price_tiers   (drop edilecek)
     *
     * Yeni (Phase 1.5.b):
     *   - booking_passengers.cabin_id              → cabins (Ship master cabin'i)
     *   - booking_passengers.tour_cabin_price_id   → tour_cabin_prices (matrix satırı snapshot)
     *
     * tour_cabin_price_id snapshot için audit — booking yapıldığı anki
     * fiyat satırını referans verir, sonradan admin matrix'i değiştirse
     * bile bu booking'in hangi matrix row'una göre hesaplandığı bilinir.
     */
    public function up(): void
    {
        Schema::table('booking_passengers', function (Blueprint $table) {
            // Drop old FK columns (tour_cabin_types + tour_price_tiers
            // tabloları sonraki migration'da drop edilecek; önce
            // booking_passengers'taki referansları kaldırmamız lazım).
            $table->dropForeign(['tour_cabin_type_id']);
            $table->dropForeign(['price_tier_id']);
            $table->dropColumn(['tour_cabin_type_id', 'price_tier_id']);
        });

        Schema::table('booking_passengers', function (Blueprint $table) {
            // Add new FK columns
            $table->foreignId('cabin_id')
                ->nullable()
                ->after('id_number')
                ->constrained('cabins')
                ->nullOnDelete();

            $table->foreignId('tour_cabin_price_id')
                ->nullable()
                ->after('cabin_id')
                ->constrained('tour_cabin_prices')
                ->nullOnDelete();

            $table->index('cabin_id');
            $table->index('tour_cabin_price_id');
        });
    }

    public function down(): void
    {
        Schema::table('booking_passengers', function (Blueprint $table) {
            $table->dropForeign(['cabin_id']);
            $table->dropForeign(['tour_cabin_price_id']);
            $table->dropIndex(['cabin_id']);
            $table->dropIndex(['tour_cabin_price_id']);
            $table->dropColumn(['cabin_id', 'tour_cabin_price_id']);
        });

        // Note: not restoring tour_cabin_type_id / price_tier_id —
        // those reference tables that we drop in the next migration.
        // Rollback'i tam yapmak istenirse Phase 1 migration'larına
        // dön (db:rollback dik).
    }
};
