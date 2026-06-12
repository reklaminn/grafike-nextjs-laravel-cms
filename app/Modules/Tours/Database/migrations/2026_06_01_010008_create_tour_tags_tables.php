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
     * Marketing tag master — eski sistem Tab 1'deki "Tur Seçenekleri"
     * çoklu checkbox listesi.  TourCategory'den (yapısal hiyerarşik)
     * ve Campaign'den (tarih + indirim) AYRI bir taksonomi:
     *
     *   "Mini Cruise"           → marketing feature
     *   "Ultra Lüks Gemiler"    → marketing feature
     *   "Bayram Turları"        → marketing/seasonal
     *   "Son Dakika Fırsatları" → marketing — Campaign'le örtüşür ama tag olarak da var
     *
     * Tour ↔ TourTag m2m (Phase 1.5.c'de eklenecek tour_marketing_tags pivot).
     */
    public function up(): void
    {
        Schema::create('tour_tags', function (Blueprint $table) {
            $table->id();

            $table->string('slug', 80)->unique();
            $table->string('icon', 50)->nullable();           // emoji veya icon class
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('tour_tag_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_tag_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->string('name', 200);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['tour_tag_id', 'language_id'], 'uniq_tour_tag_language');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_tag_translations');
        Schema::dropIfExists('tour_tags');
    }
};
