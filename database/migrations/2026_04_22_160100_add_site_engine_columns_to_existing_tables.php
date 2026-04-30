<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * NOTE: This migration has been superseded by the database-per-tenant architecture.
     *
     * The tables it originally modified (pages, articles, menus, site_settings) are now
     * TENANT tables, managed in database/migrations/tenant/ with all columns (including
     * page_template_id, sections_json, listing_variant, etc.) baked into the tenant
     * migration files from the start.
     *
     * The site_id FK columns have been removed entirely — tenant DB isolation replaces
     * the need for site_id columns (stancl/tenancy switches the DB connection per request).
     *
     * This file is kept as a no-op so the migrations table log is not disrupted.
     */
    public function up(): void
    {
        // No-op: superseded by database/migrations/tenant/ (stancl/tenancy architecture)
    }

    public function down(): void
    {
        // No-op
    }
};
