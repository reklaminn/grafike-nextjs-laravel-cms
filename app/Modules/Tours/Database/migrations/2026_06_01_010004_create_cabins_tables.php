<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.a
     *
     * Cabin master — bir Ship'in gerçek kabin envanteri.
     *
     * KEY DESIGN DECISIONS:
     *   - `ship_id` ZORUNLU: her cabin bir gemiye ait
     *   - `cabin_category_id` ZORUNLU: endüstri standart tip ayraçı
     *     (İç/Dış/Balkonlu/Okyanus/Suite)
     *   - `brand_subcategory` opsiyonel: brand-spesifik isim
     *     ("MSC Yacht Club", "NCL Haven" — kategori 'suite' iken
     *      bu alan firma-spesifik isimlendirme tutar)
     *   - `base_price_per_person`: default fiyat, tour-level TourCabinPrice
     *     override edilmedikçe kullanılır
     *
     * Cabin ↔ CabinGroup m2m pivot ayrı migration'da (010005).
     *
     * Phase 1'deki `tour_cabin_types` tablosunun replacement'ı — eski
     * per-tour pattern yerine per-ship master.  Tour'lar Ship.cabins'ten
     * okur, TourCabinPrice ile per-tour fiyat override eder.
     */
    public function up(): void
    {
        Schema::create('cabins', function (Blueprint $table) {
            $table->id();

            // Required parents
            $table->foreignId('ship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cabin_category_id')->constrained()->restrictOnDelete();

            // Brand-specific subcategory (örn. "MSC Yacht Club" — category=suite)
            $table->string('brand_subcategory', 100)->nullable();

            // Code shown on booking documents / manifest (örn. "BAL-7-101")
            $table->string('code', 30)->nullable();

            // Deck info (örn. "Deck 7 — Lido", "Kat 12")
            $table->string('deck_name', 100)->nullable();

            // Capacity
            $table->unsignedSmallInteger('max_capacity')->default(2);

            // Default per-person price (minor units, kuruş)
            // Tour-level override yoksa bu kullanılır
            $table->unsignedInteger('base_price_per_person')->default(0);

            // Status + ordering
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ship_id', 'sort_order']);
            $table->index(['ship_id', 'cabin_category_id']);
            $table->index(['ship_id', 'is_active']);
        });

        Schema::create('cabin_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabin_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->string('name', 200);                  // örn. "Promo İç Kabin"
            $table->text('description')->nullable();      // donanım, m², yatak düzeni

            $table->timestamps();

            $table->unique(['cabin_id', 'language_id'], 'uniq_cabin_language');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabin_translations');
        Schema::dropIfExists('cabins');
    }
};
