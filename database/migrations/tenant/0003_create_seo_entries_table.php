<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB: seo_entries
     *
     * Includes all columns from the original central migration plus the
     * enhancements from enhance_seo_entries_table and design_assets migrations
     * (schema_type, structured_data, og_image, og_type, sitemap_*).
     * language_id is a plain column (cross-DB ref to central languages table).
     */
    public function up(): void
    {
        Schema::create('seo_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seoable_id');
            $table->string('seoable_type', 255);
            $table->string('slug', 500)->index();
            $table->unsignedBigInteger('language_id')->nullable(); // central DB ref
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('h1_override', 255)->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->text('hreflang_tags')->nullable();
            $table->string('schema_type', 50)->nullable();
            $table->json('structured_data')->nullable();
            $table->string('og_image', 500)->nullable();
            $table->string('og_type', 50)->nullable();
            $table->boolean('is_noindex')->default(false);
            $table->decimal('sitemap_priority', 2, 1)->default(0.5);
            $table->string('sitemap_changefreq', 20)->default('weekly');
            $table->boolean('sitemap_exclude')->default(false);
            $table->text('page_css')->nullable();
            $table->text('page_js')->nullable();
            $table->integer('legacy_id')->nullable();
            $table->timestamps();

            $table->index(['seoable_id', 'seoable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_entries');
    }
};
