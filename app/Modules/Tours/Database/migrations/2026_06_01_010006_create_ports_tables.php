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
     * Port master — cruise/ferry rotalarında ziyaret edilen limanlar.
     * Eski sistemde 200+ port kaydı vardı (Pire, Mikonos, İstanbul,
     * Bermuda, Oban-İskoçya, vs.).  Editorial entity:
     *   - Geo coordinates (auto-map için kritik)
     *   - Population, video_url (editöryel detay)
     *   - country_code (CDN flag için)
     *
     * `name` translation tablosunda (Pire/Piraeus/Πειραιάς farkları için).
     */
    public function up(): void
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->id();

            $table->string('slug', 100)->unique();
            $table->string('country_code', 2)->nullable();    // ISO-3166-1 alpha-2

            // Geo — kesin koordinatlar (auto-map için)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Editorial metadata
            $table->unsignedInteger('population')->nullable();
            $table->string('video_url', 500)->nullable();     // YouTube/Vimeo embed
            $table->string('timezone', 50)->nullable();       // örn. 'Europe/Athens'

            // Status + ordering
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('country_code');
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('port_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('port_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->string('name', 200);                       // örn. "Pire" / "Piraeus"
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['port_id', 'language_id'], 'uniq_port_language');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('port_translations');
        Schema::dropIfExists('ports');
    }
};
