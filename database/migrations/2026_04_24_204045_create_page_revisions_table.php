<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * NO-OP — included in database/migrations/tenant/0001_create_pages_table.php
     *
     * `page_revisions` is a tenant table (references tenant `pages`).
     */
    public function up(): void {}

    public function down(): void {}
};
