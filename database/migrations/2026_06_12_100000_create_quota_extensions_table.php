<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Geçici kota yükseltmeleri (central DB).
     *
     * Tenant'ın günlük istek limiti paket limitine ek olarak, tarih aralığı
     * aktif olan extension'ların toplamı kadar artar. Süre dolunca extension
     * kendiliğinden etkisizleşir (tarih kontrolü — job/cron gerekmez).
     */
    public function up(): void
    {
        Schema::create('quota_extensions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->unsignedInteger('extra_requests_per_day');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->index();
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // admin id
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quota_extensions');
    }
};
