<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_entries', function (Blueprint $table) {
            // Structured data (JSON-LD)
            $table->string('schema_type', 50)->nullable()->after('hreflang_tags');
            $table->json('structured_data')->nullable()->after('schema_type');

            // Open Graph overrides
            $table->string('og_image', 500)->nullable()->after('structured_data');
            $table->string('og_type', 50)->nullable()->after('og_image');
        });
    }

    public function down(): void
    {
        Schema::table('seo_entries', function (Blueprint $table) {
            $table->dropColumn(['schema_type', 'structured_data', 'og_image', 'og_type']);
        });
    }
};
