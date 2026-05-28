<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hybrid tenant scoping for the (central) theme + block catalog.
     *
     * `themes` and `section_templates` stay in the central DB (so per-tenant
     * `pages.sections_json` block-id references never break), but gain a
     * nullable `tenant_id`:
     *   - NULL  → global/agency row, shared & usable by every tenant (read-only
     *             for tenant admins; only super-admins manage it).
     *   - <key> → that tenant's own row (only that tenant sees/edits it).
     *
     * No FK to `tenants` on purpose: tenant ids are slugs and the rest of the
     * codebase references tenants loosely (no cross-DB FK), so we keep an
     * indexed string for fast `whereNull(tenant_id) OR tenant_id = ?` scoping.
     */
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->after('id')->index();
        });

        Schema::table('section_templates', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('section_templates', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('themes', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
