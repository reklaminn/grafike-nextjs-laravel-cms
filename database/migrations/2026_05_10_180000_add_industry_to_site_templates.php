<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds an "industry" tag to site_templates so the gallery UI can filter by
 * sector (clinic, lawyer, hotel, real_estate, salon, corporate). The
 * snapshot_json keeps the page/menu/setting payload as before.
 *
 * `summary` is the short one-liner shown under the template's name in the
 * gallery card; the longer `description` (already present) is kept for the
 * detail/confirmation step.
 *
 * Central DB — site_templates is the agency-wide library.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->table('site_templates', function (Blueprint $table) {
            $table->string('industry', 64)->nullable()->after('slug')->index();
            $table->string('summary', 255)->nullable()->after('description');
            $table->unsignedInteger('sort_order')->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::connection('central')->table('site_templates', function (Blueprint $table) {
            $table->dropIndex(['industry']);
            $table->dropColumn(['industry', 'summary', 'sort_order']);
        });
    }
};
