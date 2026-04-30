<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB: design_assets, design_asset_backups, smtp_profiles
     *
     * These are per-tenant: each site has its own CSS/JS assets, design backups,
     * and SMTP configuration.
     */
    public function up(): void
    {
        Schema::create('design_assets', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50); // css, js, template_css
            $table->string('name');
            $table->longText('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });

        Schema::create('design_asset_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_asset_id')->constrained()->onDelete('cascade');
            $table->longText('content');
            $table->timestamp('created_at');
        });

        Schema::create('smtp_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(587);
            $table->string('encryption', 10)->default('tls'); // tls, ssl, none
            $table->string('username');
            $table->text('password');
            $table->string('from_email');
            $table->string('from_name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_profiles');
        Schema::dropIfExists('design_asset_backups');
        Schema::dropIfExists('design_assets');
    }
};
