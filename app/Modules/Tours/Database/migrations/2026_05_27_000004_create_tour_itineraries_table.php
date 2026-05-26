<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module
     *
     * Day-by-day rota for a tour.  Modeled as a parent `tour_itineraries`
     * row (one per language version) plus per-day `tour_itinerary_days`
     * rows.  This split lets a single tour have separate localised
     * itineraries (TR vs EN copy can differ in style without losing the
     * day-number relationship).
     *
     * For daily tours, an itinerary may still exist with a single day
     * row holding the timeline (09:00 pickup → 12:00 lunch → 17:00 drop-off).
     */
    public function up(): void
    {
        Schema::create('tour_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            // Optional summary header shown above the day list
            $table->string('title', 255)->nullable();
            $table->text('summary')->nullable();

            $table->timestamps();

            $table->unique(['tour_id', 'language_id'], 'uniq_itinerary_tour_language');
            $table->index('language_id');
        });

        Schema::create('tour_itinerary_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_itinerary_id')
                ->constrained('tour_itineraries')->cascadeOnDelete();

            $table->unsignedSmallInteger('day_number');  // 1-indexed (Day 1, Day 2, …)
            $table->string('title', 255);
            $table->longText('description')->nullable();

            // Optional location data — cruise itineraries usually have
            // a port name; package tours have a city; dailies use this
            // for the meeting point.
            $table->string('location', 255)->nullable();
            $table->decimal('geo_lat', 10, 7)->nullable();
            $table->decimal('geo_lng', 10, 7)->nullable();

            // For cruise itineraries: arrival / departure clock times.
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();

            // Free-text meal note ("Breakfast, Lunch on board") so the
            // frontend can render meal icons without a separate table.
            $table->string('meals', 100)->nullable();

            $table->timestamps();

            $table->unique(['tour_itinerary_id', 'day_number'], 'uniq_itinerary_day_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_itinerary_days');
        Schema::dropIfExists('tour_itineraries');
    }
};
