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
     * Destination master — frontend landing page'leri için geo-aggregated
     * tur grupları.  Örnekler: "Yunan Adaları", "Doğu Akdeniz", "Bermuda".
     *
     * Phase 1'deki `tour_categories` (hiyerarşik yapısal) MODELDEN AYRI —
     * destination'ın kendi domain davranışı var:
     *   - Port'larla m2m (geo aggregation)
     *   - compatible_tour_types ile filtre (cruise/package/daily)
     *   - Frontend landing page için zengin içerik (gallery + cover + lat/lng)
     *
     * "Gemi Destinasyonları" ayrı bir entity DEĞİL (kullanıcı netleştirdi) —
     * tek Destination tablosu, compatible_tour_types ile cruise filter.
     */
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();

            $table->string('slug', 100)->unique();

            // Region centroid (opsiyonel — harita için)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Hangi tour tipleri için uygun?  Boş array = hepsi
            //   ['cruise', 'package']
            //   ['cruise', 'ferry']
            //   ['package']
            $table->json('compatible_tour_types')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
            $table->index('is_featured');
        });

        Schema::create('destination_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['destination_id', 'language_id'], 'uniq_dest_language');
            $table->index('language_id');
        });

        // Pivot — Destination ↔ Port m2m
        Schema::create('destination_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('port_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['destination_id', 'port_id'], 'uniq_dest_port');
            $table->index('port_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_ports');
        Schema::dropIfExists('destination_translations');
        Schema::dropIfExists('destinations');
    }
};
