<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module tag for GLOBAL (tenant_id IS NULL) catalog rows.
     *   - NULL      → generic, visible to every tenant.
     *   - '<module>'→ only visible to tenants that have that vertical module
     *                 enabled (e.g. 'tours' for the Turizm theme/blocks).
     *
     * Tenant-owned rows (tenant_id set) ignore this — they are always visible
     * to their owner regardless of module.
     */
    public function up(): void
    {
        // Idempotent: yarım kalmış / kaydedilmemiş bir önceki çalıştırma kolonu
        // zaten eklemiş olabilir (Duplicate column hatasını önle).
        if (! Schema::hasColumn('themes', 'module')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->string('module', 50)->nullable()->after('tenant_id')->index();
            });
        }

        if (! Schema::hasColumn('section_templates', 'module')) {
            Schema::table('section_templates', function (Blueprint $table) {
                $table->string('module', 50)->nullable()->after('tenant_id')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::table('section_templates', function (Blueprint $table) {
            $table->dropIndex(['module']);
            $table->dropColumn('module');
        });

        Schema::table('themes', function (Blueprint $table) {
            $table->dropIndex(['module']);
            $table->dropColumn('module');
        });
    }
};
