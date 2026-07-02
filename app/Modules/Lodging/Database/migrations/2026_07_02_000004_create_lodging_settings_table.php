<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Lodging module.
     *
     * `lodging_settings` — single-row per-tenant config.  Keeps the module
     * generic: the notification recipient, WhatsApp number and code prefix
     * are DATA, not hardcoded per property.  A row is lazily created by
     * LodgingSetting::current() on first access.
     */
    public function up(): void
    {
        Schema::create('lodging_settings', function (Blueprint $table) {
            $table->id();

            // Where new-request emails go.  Falls back to nothing (no mail)
            // if unset — never crashes.
            $table->string('notification_email', 255)->nullable();

            // Admin WhatsApp in international format (e.g. 905551112233) —
            // powers the "WhatsApp ile yanıtla" deep link in the inbox.
            $table->string('whatsapp_number', 32)->nullable();

            // Reservation code prefix, e.g. "VS" → VS-2607-A3F.
            $table->string('reservation_code_prefix', 8)->default('RZ');

            $table->string('default_currency', 3)->default('TRY');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lodging_settings');
    }
};
