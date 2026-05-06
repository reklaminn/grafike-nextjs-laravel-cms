<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pages', 'allowed_group_ids')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table) {
            // JSON array of MemberGroup IDs that may access this page.
            // NULL / empty array = public (no restriction).
            $table->json('allowed_group_ids')->nullable()->after('page_password');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('allowed_group_ids');
        });
    }
};
