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
     * Cabin × person-tier price matrix.  Eski sistem Tab 4'teki kabin
     * fiyat tablosunun gerçek karşılığı:
     *
     *   Row = TourCabinPrice (TourPriceGroup için 1 cabin satırı)
     *   Columns = price_single / price_double / price_triple / price_quad / price_child / price_baby
     *
     * `cabin_id` NULLABLE:
     *   - cruise tours: zorunlu (her kabin için ayrı row)
     *   - paket/günlük: NULL (kabin yok, generic price satırı)
     *
     * `calculation_method` enum (3 değer) — QuoteService bu method'a
     * göre PriceCalculator strategy seçer:
     *   - standart_doublex2  → price = double × passenger_count
     *   - person_sum         → price = sum of nth-person prices
     *   - flat_cabin         → price = price_single (flat)
     *
     * NULL price = "Sorunuz" (admin fiyat girmemiş, müşteri manuel
     * teklif istemeli — QuoteService PriceNotAvailableException atar).
     *
     * `child_age_min/max`, `baby_age_min/max` — yaş aralıkları
     * eski sistem Tab 4'te per-room ayarlanıyordu.
     */
    public function up(): void
    {
        Schema::create('tour_cabin_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tour_price_group_id')
                ->constrained()->cascadeOnDelete();

            // NULL for non-cruise tours (paket/günlük/feribot cabin yok)
            $table->foreignId('cabin_id')
                ->nullable()
                ->constrained('cabins')
                ->cascadeOnDelete();

            // Admin hint — eski sistemde "Fiyat Tanımı" alanı
            //   örn. "1 Tam 1 Yarım Gibi Fiyat"
            $table->string('price_definition', 200)->nullable();

            // Calculation method enum
            //   standart_doublex2 / person_sum / flat_cabin
            $table->string('calculation_method', 40)->default('standart_doublex2');

            // 6-tier person prices (minor units, kuruş)
            // NULL = "Sorunuz" — admin fiyat vermemiş
            $table->unsignedInteger('price_single')->nullable();
            $table->unsignedInteger('price_double')->nullable();
            $table->unsignedInteger('price_triple')->nullable();
            $table->unsignedInteger('price_quad')->nullable();
            $table->unsignedInteger('price_child')->nullable();
            $table->unsignedInteger('price_baby')->nullable();

            // Currency — Tour'dan varsayılan, satır bazlı override edilebilir
            $table->string('currency', 3)->default('EUR');

            // Yaş aralıkları
            $table->unsignedTinyInteger('child_age_min')->default(2);
            $table->unsignedTinyInteger('child_age_max')->default(11);
            $table->unsignedTinyInteger('baby_age_min')->default(0);
            $table->unsignedTinyInteger('baby_age_max')->default(1);

            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['tour_price_group_id', 'sort_order'], 'idx_tcp_group_sort');
            $table->index(['tour_price_group_id', 'cabin_id'], 'idx_tcp_group_cabin');
            // Same group + same cabin (or null) should be unique
            $table->unique(['tour_price_group_id', 'cabin_id'], 'uniq_tcp_group_cabin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_cabin_prices');
    }
};
