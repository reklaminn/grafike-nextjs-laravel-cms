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
     * Info-only extras master.  Eski sistem Tab 3'teki "Ekstra Aktivite ve
     * Ücretler" dropdown'unun kaynağı.
     *
     * "Bilgi amaçlı gösterilecek ancak online tahsil edilmeyecek" ücretler —
     * cruise/tour müşterilerine bilgilendirme amacıyla gösterilir, ödeme
     * çevrimdışı yapılır:
     *   - Vize ücreti (Schengen)
     *   - Havaalanı vergisi
     *   - Yakıt ek ücreti
     *   - Liman vergisi
     *   - Servis ücreti
     *
     * Tour'a bağlanışı: mevcut `tour_extras` tablosundaki `pricing_mode`
     * enum'una `info_only` değeri eklenecek (Phase 1.5.c).  Master'dan
     * seçilen kayıt copy-snapshot olarak tour_extras'a yazılır.
     *
     * `default_amount` / `default_currency` — admin tour eklerken hızlı
     * doldurmak için, override edilebilir.
     */
    public function up(): void
    {
        Schema::create('tenant_info_extras', function (Blueprint $table) {
            $table->id();

            $table->string('slug', 80)->unique();
            $table->unsignedInteger('default_amount')->default(0); // minor units
            $table->string('default_currency', 3)->default('EUR');
            $table->boolean('per_person')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('tenant_info_extra_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_info_extra_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            $table->string('name', 200);                       // "Vize Ücreti"
            $table->text('description')->nullable();           // optional açıklama

            $table->timestamps();

            $table->unique(['tenant_info_extra_id', 'language_id'], 'uniq_info_extra_language');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_info_extra_translations');
        Schema::dropIfExists('tenant_info_extras');
    }
};
