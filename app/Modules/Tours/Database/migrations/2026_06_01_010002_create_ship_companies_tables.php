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
     * Cruise / ferry operator master.  Örnekler: MSC Cruises,
     * Azamara Club Cruises, Costa Cruises, Royal Caribbean.  Eski
     * sistemde 41 firma kayıtlıydı.
     *
     * `name` doğrudan tabloda — markalar evrensel (translate edilmez).
     * Description + meta_* alanları translation tablosunda.
     *
     * `uses_cabin_groups` flag (kullanıcı kararı) — büyük filo firmaları
     * için company-level CabinGroup pattern'ini opt-in olarak açar.
     * Küçük firmalar düz ship-level cabin yönetimi kullanır.
     */
    public function up(): void
    {
        Schema::create('ship_companies', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('slug', 80)->unique();
            $table->string('name', 200);                   // brand — translate edilmez

            // Editorial metadata
            $table->string('company_type', 50)->nullable(); // Premium / Luxury / Mainstream / Expedition
            $table->string('operator', 200)->nullable();    // parent operator (NCL Holdings vb.)
            $table->smallInteger('founded_year')->unsigned()->nullable();
            $table->string('headquarters', 200)->nullable();
            $table->string('website', 500)->nullable();

            // Cabin pattern flag — uses_cabin_groups=true ise admin
            // company-level CabinGroup oluşturabilir ve tour pricing
            // setup'ta bunlardan seçim yapar.  False ise düz Ship.cabins.
            $table->boolean('uses_cabin_groups')->default(false);

            // Status + ordering
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('ship_company_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ship_company_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['ship_company_id', 'language_id'], 'uniq_ship_company_language');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ship_company_translations');
        Schema::dropIfExists('ship_companies');
    }
};
