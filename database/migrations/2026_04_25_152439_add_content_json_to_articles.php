<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * NO-OP — content_json is included in database/migrations/tenant/0002_create_articles_table.php
     *
     * `articles` is a tenant table; the column is baked into the tenant migration from the start.
     */
    public function up(): void {}

    public function down(): void {}
};
