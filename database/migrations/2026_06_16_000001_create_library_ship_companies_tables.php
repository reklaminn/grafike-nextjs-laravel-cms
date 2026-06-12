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
     * Global "kütüphane" katalogu: gemi firmaları master'ı.  Tenant
     * (turizm acentası) DB'sindeki `ship_companies` tablosunun MERKEZİ
     * kaynağıdır.  Yeni acenta eklendiğinde buradan seçili kayıtlar
     * tenant DB'sine KOPYALANIR (database-per-tenant olduğu için cross-DB
     * FK mümkün değil → copy-on-import deseni).
     *
     * Yönetim: SADECE super-admin.  Tenant central'ı değiştiremez; kendi
     * kopyasını düzenler.  Kütüphaneye yeni firma eklenince tenant import
     * edebilir (LibraryImporter + source_library_id dedup).
     *
     * `legacy_id`: eski MariaDB kaydının id'si — LegacyImporter idempotency.
     * `language_id`: central DB diller (FK yok, tenant ile birebir eşleşir).
     */
    public function up(): void
    {
        Schema::createIfNotExists('library_ship_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();

            $table->string('slug', 80)->unique();
            $table->string('name', 200);

            $table->string('company_type', 50)->nullable();
            $table->string('operator', 200)->nullable();
            $table->smallInteger('founded_year')->unsigned()->nullable();
            $table->string('headquarters', 200)->nullable();
            $table->string('website', 500)->nullable();
            $table->string('logo_url', 500)->nullable();

            $table->boolean('uses_cabin_groups')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::createIfNotExists('library_ship_company_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_ship_company_id')
                ->constrained('library_ship_companies')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['library_ship_company_id', 'language_id'], 'uniq_lib_shipco_lang');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_ship_company_translations');
        Schema::dropIfExists('library_ship_companies');
    }
};
