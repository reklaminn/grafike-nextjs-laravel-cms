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
     * tour_category_tour pivot — secondary kategoriler.
     *
     * Tour modelinde `tour_category_id` PRIMARY kategori olarak kalır
     * (URL slug üretimi, breadcrumb, default filtreleme bunu kullanır).
     * Bu pivot ek (secondary) kategorileri tutar — bir tur birden çok
     * koleksiyonda görünebilsin diye (örn. "Romantik Cruise" +
     * "Akdeniz Cruise" + "7-Gece Seyahatler" gibi).
     *
     * Frontend kategori sayfaları sorgu zamanı `WHERE primary_id = ?
     * OR EXISTS (pivot)` ile birleştirir.
     */
    public function up(): void
    {
        Schema::create('tour_category_tour', function (Blueprint $table) {
            $table->foreignId('tour_id')
                ->constrained('tours')
                ->cascadeOnDelete();

            $table->foreignId('tour_category_id')
                ->constrained('tour_categories')
                ->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->primary(['tour_id', 'tour_category_id'], 'pk_tour_category_tour');
            $table->index(['tour_category_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_category_tour');
    }
};
