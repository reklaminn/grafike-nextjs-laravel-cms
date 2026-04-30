<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB: pages + page_revisions
     *
     * Cross-DB references (language_id, page_template_id, admin_id) are stored as plain
     * unsignedBigInteger columns without FK constraints — DB-level enforcement is not
     * possible across databases; application logic handles integrity.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 500);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('language_id')->nullable();     // central DB ref
            $table->unsignedBigInteger('root_page_id')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('show_in_menu')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('slug', 500);
            $table->string('external_url', 500)->nullable();
            $table->enum('link_target', ['_self', '_blank'])->default('_self');
            $table->string('module_type', 50)->nullable();
            $table->string('template', 100)->nullable();
            $table->json('layout_json')->nullable();
            $table->text('page_template')->nullable();
            $table->unsignedBigInteger('page_template_id')->nullable(); // central DB ref
            $table->string('frontend_variant', 100)->nullable();
            $table->json('sections_json')->nullable();
            $table->longText('custom_css')->nullable();
            $table->longText('custom_js')->nullable();
            $table->boolean('is_password_protected')->default(false);
            $table->string('page_password', 255)->nullable();
            $table->boolean('show_social_share')->default(false);
            $table->boolean('show_facebook_comments')->default(false);
            $table->boolean('show_breadcrumb')->default(true);
            $table->unsignedInteger('view_count')->default(0);
            $table->integer('legacy_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('pages')->onDelete('set null');
            $table->unique(['slug', 'language_id']);
        });

        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('admin_id')->nullable(); // central DB ref
            $table->json('snapshot');
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index(['page_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_revisions');
        Schema::dropIfExists('pages');
    }
};
