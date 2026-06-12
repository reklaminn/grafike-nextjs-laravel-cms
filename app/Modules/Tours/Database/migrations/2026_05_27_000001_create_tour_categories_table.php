<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module
     *
     * Hierarchical tour categories (e.g. "Greek Islands" → "3-Day Cruises").
     * Hierarchy resolved via staudenmeir/laravel-adjacency-list (same pattern
     * as Pages and Articles in the core CMS).
     */
    public function up(): void
    {
        Schema::create('tour_categories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('parent_id')->nullable();

            // Multi-language category labels — see tour_category_translations
            // table below.  `slug` is stored once here (URL is language-
            // agnostic per tenant policy) and unique across the tenant.
            $table->string('slug', 200)->unique();

            // Sort order amongst siblings — manually orderable in admin UI.
            $table->integer('sort_order')->default(0);

            // Visibility flag; hidden categories still keep tours but are
            // not listed on the frontend.
            $table->boolean('is_active')->default(true);

            // Optional cover image is handled via Spatie media-library
            // (no column here; lookup via `getFirstMediaUrl('cover')`).

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')->on('tour_categories')
                ->nullOnDelete();

            $table->index(['parent_id', 'sort_order']);
        });

        Schema::create('tour_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_category_id')
                ->constrained()->cascadeOnDelete();

            // language_id is a CENTRAL DB ref — no FK constraint
            // (cross-DB constraints aren't enforceable in MySQL/MariaDB).
            $table->unsignedBigInteger('language_id');

            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['tour_category_id', 'language_id'], 'uniq_category_language');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_category_translations');
        Schema::dropIfExists('tour_categories');
    }
};
