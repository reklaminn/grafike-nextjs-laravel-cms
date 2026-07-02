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
     * `reservations` — a visitor's stay request.  Structural fields (date
     * range, nights, guest counts, estimated total, status) justify a
     * dedicated table rather than the generic Form/FormSubmission system
     * (docs/rezervasyon-modulu-proje-dosyasi.md §3.3).
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            // Human-readable request number, e.g. "VS-2607-A3F".  Prefix is
            // configurable per tenant (lodging_settings.reservation_code_prefix)
            // so the module stays generic.
            $table->string('code', 40)->unique();

            // Nullable: a general enquiry may not pick a specific room-type.
            $table->foreignId('room_type_id')
                ->nullable()
                ->constrained('room_types')
                ->nullOnDelete();

            $table->string('guest_name', 255);
            $table->string('guest_phone', 40);
            $table->string('guest_email', 255)->nullable();

            // Inclusive checkin, exclusive checkout (nights = diff in days).
            $table->date('checkin');
            $table->date('checkout');
            $table->unsignedSmallInteger('nights')->default(1);

            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);

            // nights × base_price (+ optional discount rule) at request time.
            $table->decimal('est_total', 10, 2)->nullable();
            $table->string('currency', 3)->default('TRY');

            $table->text('message')->nullable();

            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->enum('source', ['web', 'whatsapp', 'phone'])->default('web');

            // Operator-only note (kept out of `message`, which is guest text).
            $table->text('admin_note')->nullable();

            // UTM / search params / arbitrary context.
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['room_type_id', 'checkin', 'checkout'], 'reservation_range_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
