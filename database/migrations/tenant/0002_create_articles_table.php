<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB: articles
     *
     * Cross-DB references (language_id, author_id) are stored as plain unsignedBigInteger
     * without FK constraints. page_id references the tenant DB's own pages table.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 500);
            $table->longText('body')->nullable();
            $table->json('content_json')->nullable();
            $table->text('excerpt')->nullable();
            $table->foreignId('page_id')->constrained('pages')->onDelete('cascade');
            $table->unsignedBigInteger('language_id')->nullable();        // central DB ref
            $table->unsignedBigInteger('parent_article_id')->nullable()->comment('Translation link');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->integer('sort_order')->default(0);
            $table->string('slug', 500);
            $table->string('external_url', 500)->nullable();
            $table->enum('link_target', ['_self', '_blank'])->default('_self');
            $table->text('template')->nullable();
            $table->string('listing_variant', 100)->nullable();
            $table->string('detail_variant', 100)->nullable();
            $table->unsignedTinyInteger('content_type_id')->default(0);
            $table->unsignedBigInteger('form_id')->nullable();            // tenant DB ref (no FK for flexibility)
            $table->boolean('is_featured')->default(false);
            $table->text('meta_description')->nullable();
            $table->text('extra_info')->nullable();
            $table->datetime('published_at')->nullable();
            $table->string('display_date', 100)->nullable();
            $table->unsignedBigInteger('author_id')->nullable();          // central DB ref
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->integer('legacy_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_article_id')->references('id')->on('articles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
