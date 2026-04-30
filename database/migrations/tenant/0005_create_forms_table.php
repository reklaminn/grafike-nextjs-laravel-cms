<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB: forms + form_fields + form_submissions
     *
     * language_id is a plain column (cross-DB ref to central languages table).
     */
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_captcha')->default(true);
            $table->string('notification_email', 255)->nullable();
            $table->string('smtp_host', 255)->nullable();
            $table->integer('smtp_port')->nullable();
            $table->string('smtp_username', 255)->nullable();
            $table->string('smtp_password', 255)->nullable();
            $table->string('smtp_encryption', 10)->nullable();
            $table->boolean('allow_submissions')->default(true);
            $table->boolean('allow_listing')->default(false);
            $table->boolean('save_to_database')->default(true);
            $table->unsignedBigInteger('language_id')->nullable(); // central DB ref
            $table->integer('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->string('label', 255);
            $table->string('name', 255);
            $table->enum('type', ['text', 'email', 'textarea', 'select', 'checkbox', 'radio', 'file', 'date', 'phone', 'number', 'hidden', 'password'])->default('text');
            $table->string('placeholder', 255)->nullable();
            $table->text('default_value')->nullable();
            $table->json('options')->nullable();
            $table->string('validation_rules', 500)->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('css_class', 255)->nullable();
            $table->string('section', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('subject', 500)->nullable();
            $table->json('data');
            $table->text('reply')->nullable();
            $table->enum('status', ['new', 'read', 'replied', 'archived'])->default('new');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('recipient_email', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
