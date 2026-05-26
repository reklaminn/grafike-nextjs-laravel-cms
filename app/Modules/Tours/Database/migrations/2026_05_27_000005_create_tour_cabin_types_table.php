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
     * Cruise-only: cabin categories on a ship (Inside / Outside / Balcony
     * / Suite / …).  Each cabin type has its own capacity (number of
     * cabins × beds-per-cabin) and a price modifier relative to the
     * tour's base_price.
     *
     * For non-cruise tour types this table is simply empty.  Validation
     * that "type=cruise → at least one cabin_type exists" lives at the
     * application layer (CruiseTour child model in Phase 1+), not DB.
     */
    public function up(): void
    {
        Schema::create('tour_cabin_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();

            // Stable code shown on booking documents (e.g. "BAL", "STE").
            $table->string('code', 30);

            // Display name & description (single-language for now; cabin
            // descriptions are short and rarely translated — Phase 1 keeps
            // them on the parent row.  Promote to a translation table
            // later if multi-language pressure emerges).
            $table->string('name', 200);
            $table->text('description')->nullable();

            // Capacity slicing
            $table->unsignedSmallInteger('beds_per_cabin')->default(2);
            $table->unsignedSmallInteger('cabin_count')->default(1);
            $table->unsignedSmallInteger('capacity_total')->default(2);  // bed × cabin

            // Price modifier in minor units. Final cabin price =
            // tour.base_price (or tour_date.price_override) + this value.
            // Signed integer so suites can be +ve and inside cabins -ve.
            $table->integer('price_modifier')->default(0);

            // Optional ship deck label (e.g. "Deck 7 — Lido")
            $table->string('deck', 100)->nullable();

            // For sorting in the cabin selector UI (lowest first)
            $table->integer('sort_order')->default(0);

            // Visibility / availability flags
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['tour_id', 'code'], 'uniq_cabin_tour_code');
            $table->index(['tour_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_cabin_types');
    }
};
