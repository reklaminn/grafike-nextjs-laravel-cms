<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_plans', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('label');
            $table->unsignedInteger('monthly_requests')->nullable()->comment('null = sınırsız');
            $table->unsignedBigInteger('monthly_tokens')->nullable()->comment('null = sınırsız');
            $table->decimal('monthly_cost_usd', 10, 2)->nullable()->comment('null = sınırsız');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // config/ai.php'deki planları seed et.
        $seed = [
            ['key' => 'free',       'label' => 'Ücretsiz',   'monthly_requests' => 50,   'monthly_tokens' => 100000,    'monthly_cost_usd' => 0.50,  'sort_order' => 1, 'is_default' => 1],
            ['key' => 'starter',    'label' => 'Başlangıç',  'monthly_requests' => 500,  'monthly_tokens' => 1000000,   'monthly_cost_usd' => 5.00,  'sort_order' => 2, 'is_default' => 0],
            ['key' => 'pro',        'label' => 'Pro',        'monthly_requests' => 5000, 'monthly_tokens' => 10000000,  'monthly_cost_usd' => 50.00, 'sort_order' => 3, 'is_default' => 0],
            ['key' => 'enterprise', 'label' => 'Kurumsal',   'monthly_requests' => null, 'monthly_tokens' => null,      'monthly_cost_usd' => null,  'sort_order' => 4, 'is_default' => 0],
        ];

        foreach ($seed as $row) {
            $row['created_at'] = now();
            $row['updated_at'] = now();
            DB::table('ai_plans')->insertOrIgnore($row);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_plans');
    }
};
