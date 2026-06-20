<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Günlük tenant kullanım kaydı (central DB).
     *
     * Redis istek/giriş sayaçları yalnızca 2 gün TTL'li olduğundan, kalıcı
     * zaman serisi için `cms:rollup-usage` komutu (saatlik cron) bu tabloya
     * yazar. requests/logins gün boyunca büyür; storage_mb/db_mb/users ise
     * o günkü EN SON anlık görüntüdür (pano bunları taramadan okur).
     */
    public function up(): void
    {
        Schema::create('tenant_usage_daily', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->date('date');
            $table->unsignedInteger('requests')->default(0);
            $table->unsignedInteger('logins')->default(0);
            $table->float('storage_mb')->default(0);
            $table->float('db_mb')->default(0);
            $table->unsignedInteger('users')->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_usage_daily');
    }
};
