<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CENTRAL DB — Cruise Kütüphanesi (Phase 1.5.h)
     *
     * Global gemi master'ı (örn. MSC EURIBIA).  Tenant `ships` tablosunun
     * merkezi kaynağı.  Teknik özellikler + facilities JSON birebir tenant
     * şemasını mirror'lar; ek olarak `cover_url`/`gallery_urls` (tenant
     * import'unda Spatie media'ya çekilebilecek referans görseller) +
     * `legacy_id`.
     */
    public function up(): void
    {
        Schema::createIfNotExists('library_ships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();

            $table->foreignId('library_ship_company_id')
                ->constrained('library_ship_companies')
                ->cascadeOnDelete();

            $table->string('slug', 80)->unique();
            $table->string('name', 200);

            $table->unsignedTinyInteger('star_rating')->nullable();

            $table->string('local_agent', 200)->nullable();
            $table->string('flag_country_code', 2)->nullable();
            $table->string('imo_number', 20)->nullable();

            $table->smallInteger('year_built')->unsigned()->nullable();
            $table->unsignedInteger('passenger_capacity')->nullable();
            $table->unsignedInteger('crew_count')->nullable();
            $table->unsignedSmallInteger('deck_count')->nullable();
            $table->unsignedInteger('tonnage')->nullable();
            $table->decimal('length_m', 6, 2)->nullable();
            $table->decimal('beam_m', 6, 2)->nullable();
            $table->decimal('cruise_speed_knots', 4, 1)->nullable();

            $table->json('facilities')->nullable();

            $table->string('cover_url', 500)->nullable();
            $table->json('gallery_urls')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['library_ship_company_id', 'sort_order'], 'idx_lib_ship_company_sort');
            $table->index(['is_active', 'star_rating']);
        });

        Schema::createIfNotExists('library_ship_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_ship_id')
                ->constrained('library_ships')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['library_ship_id', 'language_id'], 'uniq_lib_ship_lang');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_ship_translations');
        Schema::dropIfExists('library_ships');
    }
};
