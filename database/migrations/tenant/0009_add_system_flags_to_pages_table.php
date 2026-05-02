<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('system_key', 50)->nullable()->after('frontend_variant')->index();
            $table->boolean('is_system')->default(false)->after('system_key')->index();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['system_key', 'is_system']);
        });
    }
};
