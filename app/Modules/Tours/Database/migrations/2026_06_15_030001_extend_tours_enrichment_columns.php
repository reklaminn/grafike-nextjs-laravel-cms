<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.c
     *
     * Tour enrichment kolonları:
     *   - ship_id           cruise tours için zorunlu (app-level guard);
     *                       paket/günlük turlar için NULL
     *   - sku               opsiyonel operatör referansı ("CRZ-MED-2026-07")
     *                       benzersizlik (tenant scope'ta) admin'in yararına
     *   - includes_flight   ucuz "uçaklı" rozeti + filtre için bool flag
     *   - flight_info       JSON — havayolu, kalkış/varış havalimanı,
     *                       opsiyonel uçuş kodu (paket turlar için)
     *   - copied_from_tour_id  "Tur Kopyala" akışında kaynağı tutar; istatistik
     *                       (en sık kopyalanan tur tipi) ve audit trail için
     *
     * Cruise rule (zorunlu ship_id) — DB-level NOT NULL koymadık çünkü
     * tek tablo polymorphic. App-level guard: TourPolicy / StoreTourRequest.
     */
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->unsignedBigInteger('ship_id')
                ->nullable()
                ->after('tour_category_id');

            $table->string('sku', 60)
                ->nullable()
                ->after('slug');

            $table->boolean('includes_flight')
                ->default(false)
                ->after('capacity_default');

            $table->json('flight_info')
                ->nullable()
                ->after('includes_flight');

            $table->unsignedBigInteger('copied_from_tour_id')
                ->nullable()
                ->after('structured_data_json');

            $table->foreign('ship_id')
                ->references('id')->on('ships')
                ->nullOnDelete();

            $table->foreign('copied_from_tour_id')
                ->references('id')->on('tours')
                ->nullOnDelete();

            $table->unique('sku', 'uniq_tours_sku');
            $table->index('ship_id');
            $table->index('includes_flight');
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropForeign(['ship_id']);
            $table->dropForeign(['copied_from_tour_id']);
            $table->dropUnique('uniq_tours_sku');
            $table->dropIndex(['ship_id']);
            $table->dropIndex(['includes_flight']);
            $table->dropColumn([
                'ship_id',
                'sku',
                'includes_flight',
                'flight_info',
                'copied_from_tour_id',
            ]);
        });
    }
};
