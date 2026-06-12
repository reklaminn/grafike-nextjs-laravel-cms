<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sistem geneli merkezi ayarlar tablosu (central DB).
 *
 * Superadmin panelinden yönetilen, tüm tenant'lar için geçerli olan
 * sistem ayarlarını saklar. Örnek: AI provider API anahtarları.
 *
 * API anahtarları gibi hassas değerler type='encrypted' ile şifreli saklanır.
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create('central_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string | encrypted | boolean
            $table->string('group', 50)->default('general');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('central_settings');
    }
};
