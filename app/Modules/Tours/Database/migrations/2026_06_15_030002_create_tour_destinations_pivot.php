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
     * tour_destinations m2m pivot.
     *
     * Bir tur birden çok destinasyona bağlanabilir (örn. "Akdeniz Yaz
     * Klasiği" turu Yunan Adaları + İtalya'yı kapsayabilir).  Önceki
     * sürümde destinasyon bilgisi `tours.type_config` JSON içinde
     * gevşek tutuluyordu; artık ilişkisel + filtrelenebilir.
     *
     * sort_order — admin'in destinasyon sırasını UI'da belirleyip
     * frontend listesinde de aynı sırayı görmek istemesi için.
     */
    public function up(): void
    {
        Schema::create('tour_destinations', function (Blueprint $table) {
            $table->foreignId('tour_id')
                ->constrained('tours')
                ->cascadeOnDelete();

            $table->foreignId('destination_id')
                ->constrained('destinations')
                ->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->primary(['tour_id', 'destination_id'], 'pk_tour_destinations');
            $table->index(['destination_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_destinations');
    }
};
