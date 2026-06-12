<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB: per-form outbound webhook (e.g. SendPulse "raw POST" URL).
     * When enabled, each submission is also POSTed as JSON to webhook_url.
     */
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->string('webhook_url', 1000)->nullable()->after('smtp_encryption');
            $table->boolean('webhook_enabled')->default(false)->after('webhook_url');
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['webhook_url', 'webhook_enabled']);
        });
    }
};
