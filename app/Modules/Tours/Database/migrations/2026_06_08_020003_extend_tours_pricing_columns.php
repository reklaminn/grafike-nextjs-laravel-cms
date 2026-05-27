<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.b
     *
     * Tour tablosuna pricing_mode + sales_status sütunları eklenir.
     * Phase 1'deki Tour modeli `type_config` JSON ile bu bilgileri
     * tutmuyordu — şimdi structured enum kolonları.
     *
     * pricing_mode (PricingMode enum):
     *   per_person / per_person_group / per_reservation
     *
     * sales_status (SalesStatus enum):
     *   live_payment / live_contact / quote_with_accommodation /
     *   quote_without_accommodation / info_only
     */
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->string('pricing_mode', 40)
                ->default('per_person')
                ->after('capacity_default');

            $table->string('sales_status', 40)
                ->default('live_payment')
                ->after('pricing_mode');

            $table->index('pricing_mode');
            $table->index('sales_status');
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropIndex(['pricing_mode']);
            $table->dropIndex(['sales_status']);
            $table->dropColumn(['pricing_mode', 'sales_status']);
        });
    }
};
