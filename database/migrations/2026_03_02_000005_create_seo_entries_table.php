<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * NO-OP — moved to database/migrations/tenant/0003_create_seo_entries_table.php
     *
     * `seo_entries` is a tenant table (all SEO data is site-specific).
     */
    public function up(): void {}

    public function down(): void {}
};
