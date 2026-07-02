<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Lodging module.
     *
     * `room_availabilities` — date-range rows that make a room-type
     * unavailable.  Two sources feed it:
     *   • manual operator blocks   (source=manual,      status=blocked)
     *   • confirmed reservations   (source=reservation, status=booked,
     *                               reservation_id set)
     *
     * Storing ranges (start_date..end_date, checkout-exclusive) keeps the
     * table small vs one row per night.  AvailabilityService expands ranges
     * to per-day counts and compares against RoomType.unit_count.
     * (Resolves spec §9 — "tek-gün satır mı, start/end aralık mı?" → aralık.)
     */
    public function up(): void
    {
        Schema::create('room_availabilities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('room_type_id')
                ->constrained('room_types')
                ->cascadeOnDelete();

            // Inclusive start, EXCLUSIVE end (checkout day is free again) —
            // matches hotel-night semantics and Reservation.checkin/checkout.
            $table->date('start_date');
            $table->date('end_date');

            // How many units this row consumes per night (default 1).
            $table->unsignedSmallInteger('qty')->default(1);

            $table->enum('status', ['blocked', 'booked'])->default('blocked');
            $table->enum('source', ['manual', 'reservation'])->default('manual');

            // Set when source=reservation, so confirm/cancel can find & drop
            // the derived row.  Nullable + nullOnDelete (reservation delete
            // frees the dates).
            $table->foreignId('reservation_id')
                ->nullable()
                ->constrained('reservations')
                ->nullOnDelete();

            $table->string('note', 500)->nullable();

            $table->timestamps();

            $table->index(['room_type_id', 'start_date', 'end_date'], 'room_avail_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_availabilities');
    }
};
