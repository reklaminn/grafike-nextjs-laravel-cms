<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('label');
            $table->string('ai_plan')->default('free');
            $table->unsignedInteger('max_users')->nullable()->comment('null = sınırsız');
            $table->unsignedBigInteger('max_storage_mb')->nullable()->comment('null = sınırsız');
            $table->unsignedInteger('max_requests_per_day')->nullable()->comment('null = kapalı/sınırsız');
            $table->json('modules')->nullable()->comment('Etkin modül listesi (örn: ["tours","commerce"])');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Mevcut config/packages.php değerlerini seed et.
        $seed = [
            ['key' => 'basic',      'label' => 'Temel',        'ai_plan' => 'free',       'max_users' => 1,    'max_storage_mb' => 500,   'max_requests_per_day' => null, 'modules' => '[]',                     'sort_order' => 1, 'is_default' => 1],
            ['key' => 'standard',   'label' => 'Standart',     'ai_plan' => 'starter',    'max_users' => 3,    'max_storage_mb' => 2048,  'max_requests_per_day' => null, 'modules' => '[]',                     'sort_order' => 2, 'is_default' => 0],
            ['key' => 'pro',        'label' => 'Profesyonel',  'ai_plan' => 'pro',        'max_users' => 10,   'max_storage_mb' => 10240, 'max_requests_per_day' => null, 'modules' => '["tours","commerce"]',   'sort_order' => 3, 'is_default' => 0],
            ['key' => 'enterprise', 'label' => 'Kurumsal',     'ai_plan' => 'enterprise', 'max_users' => null, 'max_storage_mb' => null,  'max_requests_per_day' => null, 'modules' => '["tours","commerce"]',   'sort_order' => 4, 'is_default' => 0],
        ];

        foreach ($seed as $row) {
            $row['created_at'] = now();
            $row['updated_at'] = now();
            DB::table('packages')->insertOrIgnore($row);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
