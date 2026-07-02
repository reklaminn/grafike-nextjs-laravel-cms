<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Zamanlanmış yayınlama: status='scheduled' + scheduled_at dolu olan
     * sayfalar cms:publish-scheduled komutuyla (scheduler, dakikada bir)
     * vakti gelince otomatik 'published' yapılır.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('scheduled_at');
        });
    }
};
