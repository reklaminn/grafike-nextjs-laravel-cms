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
     * tour_marketing_tags m2m pivot.
     *
     * Pazarlama etiketleri (TourTag — "Erken Rezervasyon", "Aile Dostu",
     * "Romantik", vb.) bir turun TourCategory'sinden ayrı bir endeksleme
     * boyutudur.  Kategori taksonomik (cruise > akdeniz > 7-gece),
     * etiket ise duygusal/segment (couples / families / luxury / budget).
     *
     * 12 default etiket TourTagSeeder ile Phase 1.5.a'da zaten eklendi.
     * Bu pivot bunları Tour'a bağlar.
     */
    public function up(): void
    {
        Schema::create('tour_marketing_tags', function (Blueprint $table) {
            $table->foreignId('tour_id')
                ->constrained('tours')
                ->cascadeOnDelete();

            $table->foreignId('tour_tag_id')
                ->constrained('tour_tags')
                ->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->primary(['tour_id', 'tour_tag_id'], 'pk_tour_marketing_tags');
            $table->index(['tour_tag_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_marketing_tags');
    }
};
