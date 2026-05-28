<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.g (liman akışı)
     *
     * Eski sistem Tab 1 "Bölgeler" = bu turun ziyaret ettiği limanlar
     * (1000+ liman master'ından seçilen ~6'lık alt küme).  Rota Takvimi
     * (Tab 2) per-stop dropdown'u BU listeden besleniyor — tüm master
     * değil.  Performans + tutarlılık.
     *
     * tour_ports m2m: tour_id + port_id + sort_order.
     */
    public function up(): void
    {
        Schema::createIfNotExists('tour_ports', function (Blueprint $table) {
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->foreignId('port_id')->constrained('ports')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->primary(['tour_id', 'port_id'], 'pk_tour_ports');
            $table->index(['port_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_ports');
    }
};
