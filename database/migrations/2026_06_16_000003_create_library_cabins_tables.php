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
     * Global kabin master'ı (bir library_ship'in kabin envanteri).
     *
     * Önemli: tenant `cabins.cabin_category_id` FK'si yerine burada
     * `cabin_category_slug` (string) tutulur.  CabinCategory tenant'larda
     * AYNI 5 standart slug ile seed'lendiği için (ic/dis/balkonlu/okyanus/
     * suite), import sırasında slug → tenant cabin_category_id olarak
     * resolve edilir.  Böylece library tenant cabin_category id'lerine
     * bağımlı olmaz.
     */
    public function up(): void
    {
        Schema::createIfNotExists('library_cabins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();

            $table->foreignId('library_ship_id')
                ->constrained('library_ships')
                ->cascadeOnDelete();

            // CabinCategory FK yerine slug (tenant'ta slug ile resolve)
            $table->string('cabin_category_slug', 60)->nullable();

            $table->string('brand_subcategory', 100)->nullable();
            $table->string('code', 30)->nullable();
            $table->string('deck_name', 100)->nullable();
            $table->unsignedSmallInteger('max_capacity')->default(2);
            $table->unsignedInteger('base_price_per_person')->default(0);

            $table->json('image_urls')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['library_ship_id', 'sort_order'], 'idx_lib_cabin_ship_sort');
            $table->index(['library_ship_id', 'is_active'], 'idx_lib_cabin_ship_active');
        });

        Schema::createIfNotExists('library_cabin_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_cabin_id')
                ->constrained('library_cabins')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->string('name', 200);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['library_cabin_id', 'language_id'], 'uniq_lib_cabin_lang');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_cabin_translations');
        Schema::dropIfExists('library_cabins');
    }
};
