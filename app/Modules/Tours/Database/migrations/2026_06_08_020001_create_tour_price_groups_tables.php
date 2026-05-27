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
     * Named pricing groups assigned to one or more tour dates.
     *
     * Eski sistem Tab 4 "Yeni Fiyat Grubu Ekle" formunun karşılığı.
     * Admin "Yaz 2026 Fiyatları", "Erken Rezervasyon Fiyatları" gibi
     * gruplar oluşturur, her grubu N tarihe atar, her grup için cabin
     * × person-tier matrix doldurur (TourCabinPrice).
     *
     * Aynı tarihe **birden çok grup** atanabilir ("Standart fiyat" +
     * "Erken Rezervasyon Fiyatı" iki seçenek olarak müşteriye sunulur).
     *
     * Bir grup **birden çok tarihe** atanabilir (Yaz 2026 → 3 Tem,
     * 14 Ağu, 25 Eyl hepsi aynı fiyat grubunu kullanır).
     */
    public function up(): void
    {
        Schema::create('tour_price_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();

            // Group metadata (eski Tab 4 üst seviye alanları)
            $table->unsignedSmallInteger('min_persons')->default(1);
            $table->boolean('adult_priority')->default(false);
            $table->unsignedInteger('capacity_quota')->default(0); // 0 = tarih kapasitesi geçerli
            $table->string('campaign_text', 200)->nullable();      // grup-level kampanya etiketi

            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tour_id', 'sort_order']);
            $table->index(['tour_id', 'is_active']);
        });

        Schema::create('tour_price_group_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_price_group_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->string('name', 200);                       // "Yaz 2026 Fiyatları"
            $table->text('description')->nullable();           // İsteğe bağlı grup açıklaması

            $table->timestamps();

            $table->unique(['tour_price_group_id', 'language_id'], 'uniq_tour_price_group_lang');
            $table->index('language_id');
        });

        // Pivot — TourPriceGroup ↔ TourDate m2m
        // Tek grup N tarihe atanır; tek tarih N gruba atanır (alternatif fiyatlandırma).
        Schema::create('tour_price_group_tour_date', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_price_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tour_date_id')->constrained('tour_dates')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tour_price_group_id', 'tour_date_id'], 'uniq_tour_price_group_date');
            $table->index('tour_date_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_price_group_tour_date');
        Schema::dropIfExists('tour_price_group_translations');
        Schema::dropIfExists('tour_price_groups');
    }
};
