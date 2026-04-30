<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * NO-OP — moved to database/migrations/tenant/0001_create_pages_table.php
     *
     * `pages` and `page_revisions` are tenant tables. stancl/tenancy creates them
     * in each tenant's isolated database via `php artisan tenants:migrate`.
     * They must NOT exist in the central (shared) database.
     */
    public function up(): void {}

    public function down(): void {}
};
