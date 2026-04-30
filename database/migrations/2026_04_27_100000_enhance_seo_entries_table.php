<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * NO-OP — columns included in database/migrations/tenant/0003_create_seo_entries_table.php
     *
     * schema_type, structured_data, og_image, og_type are baked into the
     * tenant seo_entries table from the start.
     */
    public function up(): void {}

    public function down(): void {}
};
