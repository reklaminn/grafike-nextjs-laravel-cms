<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('articles', 'category')) {
            return;
        }

        Schema::table('articles', function (Blueprint $table) {
            // Ürün/yazı kategorisi (ör. Estetik Dermal 14 ürün kategorisi).
            // Listeleme API'si + kategori-filtreli ızgara bu alanı kullanır.
            $table->string('category', 150)->nullable()->index()->after('detail_variant');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
