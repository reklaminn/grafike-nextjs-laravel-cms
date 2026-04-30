<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * NO-OP — moved to database/migrations/tenant/0005_create_forms_table.php
     *
     * `forms`, `form_fields`, `form_submissions` are tenant tables.
     */
    public function up(): void {}

    public function down(): void {}
};
