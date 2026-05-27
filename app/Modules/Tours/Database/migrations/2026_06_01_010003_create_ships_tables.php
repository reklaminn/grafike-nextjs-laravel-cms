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
     * Ship master — belirli bir gemi (örn. MSC EURIBIA, Azamara Onward).
     * Bir ShipCompany'nin birden çok gemisi olur.
     *
     * Teknik veriler (Tab 2 — Gemi Profili):
     *   - year_built, passenger_capacity, crew_count, deck_count
     *   - tonnage (GRT), length/beam/speed
     *   - facilities JSON (15+ slug array — casino, pool, wifi vs.)
     *
     * star_rating eski sistemde yapılı alan değildi (tour ismi conventionu);
     * biz structured alan olarak ekliyoruz — filtre + frontend display için.
     *
     * Brand isimler `name` ana tabloda (translate edilmez).  Description
     * + meta_* translation tablosunda.
     */
    public function up(): void
    {
        Schema::create('ships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ship_company_id')->constrained()->restrictOnDelete();

            // Identity
            $table->string('slug', 80)->unique();
            $table->string('name', 200);                       // brand — translate edilmez

            // Rating (bizim eklediğimiz, eski sistemde yok)
            $table->unsignedTinyInteger('star_rating')->nullable();  // 1-7

            // Optional admin metadata
            $table->string('local_agent', 200)->nullable();    // TR temsilcisi
            $table->string('flag_country_code', 2)->nullable(); // ISO-3166-1 alpha-2
            $table->string('imo_number', 20)->nullable();      // gerçek IMO ID

            // Technical specs (Tab 2 — Gemi Profili)
            $table->smallInteger('year_built')->unsigned()->nullable();
            $table->unsignedInteger('passenger_capacity')->nullable();
            $table->unsignedInteger('crew_count')->nullable();
            $table->unsignedSmallInteger('deck_count')->nullable();
            $table->unsignedInteger('tonnage')->nullable();    // GRT
            $table->decimal('length_m', 6, 2)->nullable();
            $table->decimal('beam_m', 6, 2)->nullable();
            $table->decimal('cruise_speed_knots', 4, 1)->nullable();

            // Facilities — JSON array of slug strings
            // (config/ship_facilities.php master list'inden seçim)
            // Örnek: ['casino', 'pool', 'wifi', 'spa', 'sports']
            $table->json('facilities')->nullable();

            // Status + ordering
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ship_company_id', 'sort_order']);
            $table->index(['is_active', 'star_rating']);
        });

        Schema::create('ship_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ship_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['ship_id', 'language_id'], 'uniq_ship_language');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ship_translations');
        Schema::dropIfExists('ships');
    }
};
