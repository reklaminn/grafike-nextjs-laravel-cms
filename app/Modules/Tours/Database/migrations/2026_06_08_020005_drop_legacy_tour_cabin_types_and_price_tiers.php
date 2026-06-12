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
     * Phase 1'in `tour_cabin_types` ve `tour_price_tiers` tablolarını
     * drop et.  Yerlerine Phase 1.5.a + 1.5.b'de:
     *   - cabins (Ship → Cabin master)
     *   - tour_price_groups + tour_cabin_prices (5D matrix pricing)
     *
     * Hard cut migration — kullanıcı henüz canlı tenant verisi yok,
     * data migration script gerekmiyor.  İleride prod tenant'lar
     * olursa app/Modules/Tours/Console/MigrateLegacyTourData.php
     * ile veri taşınır.
     *
     * `tour_extras` ve `tour_includes` tabloları KORUNUR — onlar
     * Phase 1'in 0006_create_tour_pricing_tables migration'ında
     * yaratıldı ama farklı modeller (TourExtra, TourInclude).  Sadece
     * `tour_price_tiers` drop edilir.
     *
     * Önce booking_passengers FK refactor'ı (önceki migration) çalışmalı
     * — yoksa FK constraint hatası alır.
     */
    public function up(): void
    {
        // tour_price_tiers — Phase 1 model TourPriceTier'ın tablosu
        Schema::dropIfExists('tour_price_tiers');

        // tour_cabin_types — Phase 1 model TourCabinType'ın tablosu
        Schema::dropIfExists('tour_cabin_types');
    }

    public function down(): void
    {
        // Rollback yapmaya çalışırsanız Phase 1 migration'ları (000005,
        // 000006) yeniden çalıştırılmalı — burada tabloları yeniden
        // yaratacak migration kodu duplicate edilemez (DRY).  Pratikte
        // bu drop irreversible kabul edilir.
        throw new \RuntimeException(
            'Phase 1.5.b drop is one-way.  To restore tour_cabin_types / '
            . 'tour_price_tiers, re-run Phase 1 migrations 000005 and 000006.'
        );
    }
};
