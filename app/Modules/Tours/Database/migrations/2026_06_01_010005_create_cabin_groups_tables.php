<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.a
     *
     * Company-level cabin selection bundle.  Tour pricing setup'ında
     * toplu cabin seçimi için kullanılır (admin tek tek seçmek yerine
     * bir grubu seçer → o gruptaki cabin'ler matrix'e yerleştirilir).
     *
     * Sadece ShipCompany.uses_cabin_groups=true olduğunda anlamlı.
     * Küçük firmalar için bu mekanizma atlanır.
     *
     * `cabin_group_cabin` pivot: aynı CabinGroup birden çok geminin
     * cabin'lerini kapsayabilir (firma geneli kabin paketi mantığı).
     * Tour pricing setup'ta filtre Tour.ship ∩ CabinGroup.cabins olarak
     * çalışır — tour'un kendi gemisinin cabin'leri görünür.
     */
    public function up(): void
    {
        Schema::create('cabin_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ship_company_id')->constrained()->cascadeOnDelete();

            $table->string('slug', 80);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['ship_company_id', 'slug'], 'uniq_cabin_group_company_slug');
            $table->index(['ship_company_id', 'sort_order']);
        });

        Schema::create('cabin_group_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabin_group_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->string('name', 200);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['cabin_group_id', 'language_id'], 'uniq_cabin_group_language');
            $table->index('language_id');
        });

        // Pivot — Cabin ↔ CabinGroup m2m
        Schema::create('cabin_group_cabin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabin_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cabin_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['cabin_group_id', 'cabin_id'], 'uniq_cabin_group_cabin');
            $table->index('cabin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabin_group_cabin');
        Schema::dropIfExists('cabin_group_translations');
        Schema::dropIfExists('cabin_groups');
    }
};
