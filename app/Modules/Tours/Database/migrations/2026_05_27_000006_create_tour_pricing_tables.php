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
     * Three small auxiliary tables grouped into one migration because
     * they all relate 1-to-many off `tours` and would otherwise create
     * migration noise.
     *
     *   tour_price_tiers — per-passenger-type price (adult / child / …)
     *                      + optional early-bird / time-window conditions
     *                      stored as a JSON rule blob.
     *   tour_extras      — opt-in add-ons (transfer, insurance, photo pkg)
     *   tour_includes    — "Dahil olanlar / Dahil olmayanlar" bullets
     *                      (included=true / false flag distinguishes them)
     */
    public function up(): void
    {
        Schema::create('tour_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();

            // adult | child | infant | senior (matches PassengerType enum)
            $table->string('passenger_type', 30);

            // Optional cabin-scoped tier — null = applies to all cabins,
            // set = applies only to a specific cabin type on cruise tours.
            $table->foreignId('tour_cabin_type_id')
                ->nullable()
                ->constrained('tour_cabin_types')
                ->cascadeOnDelete();

            // Price expressed in minor units.  Stored as signed so a
            // "child = -50% of adult" tier could carry a negative value,
            // but normal usage is positive absolute price for that tier.
            $table->integer('price')->default(0);

            // Conditions JSON — admin-editable rule for when this tier
            // applies. Shape example:
            //   { "min_age": 0, "max_age": 11, "early_bird_days": 30 }
            // The QuoteService (Phase 2) reads this to select the
            // best-matching tier for a passenger.
            $table->json('conditions_json')->nullable();

            // Defaults to non-discounted; ranked higher than other tiers.
            $table->integer('priority')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['tour_id', 'passenger_type']);
        });

        Schema::create('tour_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();

            $table->string('code', 50);
            $table->string('name', 255);
            $table->text('description')->nullable();

            // Pricing model — flat per-booking OR per-passenger.
            $table->enum('pricing_mode', ['per_booking', 'per_passenger'])->default('per_booking');
            $table->integer('price')->default(0); // minor units

            // Required/optional flag — required extras pre-checked in UI.
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);

            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['tour_id', 'code'], 'uniq_extra_tour_code');
            $table->index(['tour_id', 'sort_order']);
        });

        Schema::create('tour_includes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            // True = "Dahil olanlar" bullet, False = "Dahil olmayanlar"
            $table->boolean('included')->default(true);

            $table->string('text', 500);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['tour_id', 'language_id', 'included']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_includes');
        Schema::dropIfExists('tour_extras');
        Schema::dropIfExists('tour_price_tiers');
    }
};
