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
     * Global cabin category master.  5 default kategori seed edilir
     * (İç / Dış / Balkonlu / Okyanus Manzaralı / Suite).  Admin yeni
     * kategori ekleyebilir (örn. "Promo İç", "Junior Suite") ama
     * default seed her tenant'a düşer.
     *
     * Cabin → CabinCategory zorunlu FK ilişkisi vardır — her cabin
     * bir kategorye ait olmak zorunda (endüstri standart tip).
     */
    public function up(): void
    {
        Schema::create('cabin_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->integer('sort_order')->default(0);
            $table->string('icon', 50)->nullable();       // emoji veya icon class (🛏 / fa-bed)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cabin_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabin_category_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->string('name', 100);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['cabin_category_id', 'language_id'], 'uniq_cabin_cat_language');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabin_category_translations');
        Schema::dropIfExists('cabin_categories');
    }
};
