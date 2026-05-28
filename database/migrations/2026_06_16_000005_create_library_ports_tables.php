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
     * Global liman master'ı (eski sistemde ~1000 liman).  Tenant `ports`
     * tablosunun merkezi kaynağı.  Geo koordinatlar + editöryel metadata
     * + `cover_url`/`gallery_urls` referans görseller + `legacy_id`.
     */
    public function up(): void
    {
        Schema::createIfNotExists('library_ports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();

            $table->string('slug', 100)->unique();
            $table->string('country_code', 2)->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->unsignedInteger('population')->nullable();
            $table->string('video_url', 500)->nullable();
            $table->string('timezone', 50)->nullable();

            $table->string('cover_url', 500)->nullable();
            $table->json('gallery_urls')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('country_code');
            $table->index(['is_active', 'sort_order']);
        });

        Schema::createIfNotExists('library_port_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_port_id')
                ->constrained('library_ports')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->string('name', 200);
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['library_port_id', 'language_id'], 'uniq_lib_port_lang');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_port_translations');
        Schema::dropIfExists('library_ports');
    }
};
