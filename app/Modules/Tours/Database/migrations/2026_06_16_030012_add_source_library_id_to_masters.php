<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.h (Cruise Kütüphanesi)
     *
     * Her tenant master tablosuna nullable `source_library_id` ekler:
     * kaydın hangi central `library_*` kaydından kopyalandığının izi.
     *
     * Amaçlar:
     *   - Idempotent import: aynı library kaydı iki kez import edilirse
     *     duplicate yerine upsert (source_library_id ile match).
     *   - "Kütüphaneden güncelle" (ileride): tenant kopyası ↔ kaynak bağı.
     *   - Tenant'ın kendi eklediği kayıt: source_library_id = NULL
     *     (tenant silebilir; import edilen kayıtlar korunur).
     *
     * FK YOK — central DB'deki bir tabloya tenant DB'sinden FK verilemez
     * (database-per-tenant).  Sadece index'li referans değer.
     */
    private const TABLES = [
        'ship_companies',
        'ships',
        'cabins',
        'cabin_groups',
        'ports',
        'destinations',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'source_library_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('source_library_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'source_library_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('source_library_id');
            });
        }
    }
};
