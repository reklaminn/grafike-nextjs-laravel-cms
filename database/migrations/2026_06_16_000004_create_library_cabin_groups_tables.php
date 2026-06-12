<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CENTRAL DB — Cruise Kütüphanesi (Phase 1.5.h)
     *
     * Global company-level kabin grubu master'ı + cabin m2m pivot.
     * Tenant `cabin_groups` + `cabin_group_cabin` mirror'u.
     */
    public function up(): void
    {
        Schema::createIfNotExists('library_cabin_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();

            $table->foreignId('library_ship_company_id')
                ->constrained('library_ship_companies')
                ->cascadeOnDelete();

            $table->string('slug', 80);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['library_ship_company_id', 'slug'], 'uniq_lib_cgroup_company_slug');
            $table->index(['library_ship_company_id', 'sort_order'], 'idx_lib_cgroup_company_sort');
        });

        Schema::createIfNotExists('library_cabin_group_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_cabin_group_id')
                ->constrained('library_cabin_groups')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->string('name', 200);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['library_cabin_group_id', 'language_id'], 'uniq_lib_cgroup_lang');
            $table->index('language_id');
        });

        Schema::createIfNotExists('library_cabin_group_cabin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_cabin_group_id')
                ->constrained('library_cabin_groups')
                ->cascadeOnDelete();
            $table->foreignId('library_cabin_id')
                ->constrained('library_cabins')
                ->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['library_cabin_group_id', 'library_cabin_id'], 'uniq_lib_cgroup_cabin');
            $table->index('library_cabin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_cabin_group_cabin');
        Schema::dropIfExists('library_cabin_group_translations');
        Schema::dropIfExists('library_cabin_groups');
    }
};
