<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * NO-OP — moved to database/migrations/tenant/0002_create_articles_table.php
     *
     * `articles` is a tenant table, provisioned per-tenant via `tenants:migrate`.
     */
    public function up(): void {}

    public function down(): void {}
};
