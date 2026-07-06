<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('articles', 'brand')) {
            return;
        }

        Schema::table('articles', function (Blueprint $table) {
            // Ürün markası (ör. Estetik Dermal 5 distribütör markası). Kategori
            // filtresiyle aynı desen — bkz. 0016_add_category_to_articles_table.
            $table->string('brand', 150)->nullable()->index()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
