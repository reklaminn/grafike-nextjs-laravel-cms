<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * NO-OP — moved to database/migrations/tenant/0007_create_design_assets_table.php
     *
     * `design_assets`, `design_asset_backups`, `smtp_profiles` are tenant tables.
     * The sitemap columns on seo_entries are also in the tenant migration 0003.
     */
    public function up(): void {}

    public function down(): void {}
};
