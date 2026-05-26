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
     * Concrete departures of a tour:
     *   • cruise  — sailing date (e.g. 2026-07-15 07:00)
     *   • package — start of the multi-day program
     *   • daily   — date + intra-day time slot (multiple rows per day)
     *
     * `capacity_left` is the source of truth for availability and is
     * decremented atomically inside CapacityLockService::reserve()
     * under a SELECT ... FOR UPDATE row lock.  Booking-side concurrency
     * tests live in tests/Feature/Tours/CapacityRaceTest.php (Phase 2).
     */
    public function up(): void
    {
        Schema::create('tour_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();  // null on open-ended dailies

            // Capacity model: snapshot of "seats sold-able" at creation
            // time, decremented as bookings reserve seats.
            //   capacity_total → immutable upper bound for this departure
            //   capacity_left  → mutable, decremented by reservations
            //
            // Both default to the parent tour's capacity_default; admin
            // can override to seasonal limits (e.g. summer departures
            // get 40 seats, winter 25).
            $table->unsignedInteger('capacity_total')->default(0);
            $table->unsignedInteger('capacity_left')->default(0);

            // Per-departure price override (minor units, same currency
            // as the parent tour). Null = use parent's base_price.
            $table->unsignedInteger('price_override')->nullable();

            // Lifecycle — admin can manually close a departure
            // (status=closed) without touching capacity; the frontend
            // hides closed departures from booking flow.
            //
            //   open       — sellable
            //   closed     — admin disabled (visible in admin only)
            //   sold_out   — capacity_left dropped to 0
            //   cancelled  — departure cancelled, refunds in progress
            $table->enum('status', ['open', 'closed', 'sold_out', 'cancelled'])->default('open');

            // Notes attached to a specific departure (e.g. "Captain change",
            // "Special menu for Eid"). Surfaced on booking confirmation.
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tour_id', 'starts_at']);
            $table->index(['starts_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_dates');
    }
};
