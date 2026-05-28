<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module / Phase 1.5.g (tamamlama)
     *
     * 1) Gezi Süresi (Tab 1) — tours.duration_value + duration_unit
     *    Eski sistem "2 Gece" gibi sayı+birim. Frontend kart + filtre.
     *
     * 2) Nokta tipi (Tab 2/7) — tour_itinerary_stops.point_type
     *    meeting (buluşma/hareket 🚶) vs visit (ziyaret). Tab 7 auto-map için.
     *
     * 3) Tarihe özel kampanya (Tab 4) — tour_date_campaign pivot
     *    Her departure'a farklı kampanya (TourTag campaign tipi) atanabilir.
     *
     * Idempotent.
     */
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (! Schema::hasColumn('tours', 'duration_value')) {
                $table->unsignedSmallInteger('duration_value')->nullable()->after('capacity_default');
            }
            if (! Schema::hasColumn('tours', 'duration_unit')) {
                $table->string('duration_unit', 10)->nullable()->after('duration_value'); // night/day/hour
            }
        });

        Schema::table('tour_itinerary_stops', function (Blueprint $table) {
            if (! Schema::hasColumn('tour_itinerary_stops', 'point_type')) {
                $table->string('point_type', 10)->default('visit')->after('port_id'); // meeting/visit
            }
        });

        Schema::createIfNotExists('tour_date_campaign', function (Blueprint $table) {
            $table->foreignId('tour_date_id')->constrained('tour_dates')->cascadeOnDelete();
            $table->foreignId('tour_tag_id')->constrained('tour_tags')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['tour_date_id', 'tour_tag_id'], 'pk_tour_date_campaign');
            $table->index('tour_tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_date_campaign');
        Schema::table('tour_itinerary_stops', function (Blueprint $table) {
            $table->dropColumn('point_type');
        });
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn(['duration_value', 'duration_unit']);
        });
    }
};
