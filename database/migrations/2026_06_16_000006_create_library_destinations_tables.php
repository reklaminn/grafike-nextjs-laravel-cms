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
     * Global destinasyon master'ı + port m2m pivot.  Tenant `destinations`
     * + `destination_ports` mirror'u.  Pivot `library_port_id` referans
     * verir; tenant import'unda destinasyonun portları da turun port
     * kütüphanesinden resolve edilir.
     */
    public function up(): void
    {
        Schema::createIfNotExists('library_destinations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();

            $table->string('slug', 100)->unique();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->json('compatible_tour_types')->nullable();

            $table->string('cover_url', 500)->nullable();
            $table->json('gallery_urls')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
            $table->index('is_featured');
        });

        Schema::createIfNotExists('library_destination_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_destination_id')
                ->constrained('library_destinations')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['library_destination_id', 'language_id'], 'uniq_lib_dest_lang');
            $table->index('language_id');
        });

        Schema::createIfNotExists('library_destination_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_destination_id')
                ->constrained('library_destinations')
                ->cascadeOnDelete();
            $table->foreignId('library_port_id')
                ->constrained('library_ports')
                ->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['library_destination_id', 'library_port_id'], 'uniq_lib_dest_port');
            $table->index('library_port_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_destination_ports');
        Schema::dropIfExists('library_destination_translations');
        Schema::dropIfExists('library_destinations');
    }
};
