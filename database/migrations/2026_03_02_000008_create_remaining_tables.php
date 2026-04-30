<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Central DB — only `currencies` remains here.
     *
     * The following tables were moved to database/migrations/tenant/0006_create_remaining_tables.php:
     *   redirects, site_settings, translations, reviews, template_snippets,
     *   member_groups, members, legacy_id_map
     *
     * `currencies` stays central: exchange rates are global and shared across tenants.
     */
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 10)->unique();
            $table->string('symbol', 10);
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
