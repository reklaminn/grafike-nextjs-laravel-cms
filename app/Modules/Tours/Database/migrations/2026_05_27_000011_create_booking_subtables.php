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
     * Booking sub-tables grouped into one migration:
     *   booking_passengers — one row per traveller on the booking
     *   booking_extras     — selected add-ons (frozen at booking time)
     *   vouchers           — issued PDF voucher metadata; the PDF file
     *                        itself lives in Spatie media-library on R2
     *   refunds            — partial/full refund records (filed before
     *                        Iyzico is hit, updated by webhook in Phase 2)
     */
    public function up(): void
    {
        Schema::create('booking_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();

            // PassengerType enum value
            $table->string('passenger_type', 30);

            // Identity (TR market collects TCKN; foreigners use passport)
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('id_type', 20)->default('tckn');    // 'tckn' | 'passport'
            $table->string('id_number', 50)->nullable();
            $table->string('nationality', 3)->nullable();       // ISO-3166-1 alpha-2/3
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // For cruises: which cabin this passenger sleeps in.
            // Nullable for package/daily tours.
            $table->foreignId('tour_cabin_type_id')
                ->nullable()
                ->constrained('tour_cabin_types')
                ->nullOnDelete();

            // Lead-passenger flag — the one who receives all email
            // correspondence (typically the booker themselves).
            $table->boolean('is_lead')->default(false);

            // Tier resolved at booking time — snapshot so price changes
            // on the parent tour don't retroactively modify history.
            $table->foreignId('price_tier_id')
                ->nullable()
                ->constrained('tour_price_tiers')
                ->nullOnDelete();

            // Final price charged for THIS passenger (minor units).
            $table->unsignedInteger('price')->default(0);

            // Free-text notes from the booking form
            // (special requests, allergies, mobility needs).
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('booking_id');
            $table->index(['booking_id', 'is_lead']);
        });

        Schema::create('booking_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();

            // FK to the master extra; restrict-delete so an admin can't
            // delete an extra that's been sold to bookings.
            $table->foreignId('tour_extra_id')
                ->constrained('tour_extras')
                ->restrictOnDelete();

            // Snapshot fields — name and price frozen at booking time.
            $table->string('name_snapshot', 255);
            $table->unsignedInteger('unit_price')->default(0);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedInteger('subtotal')->default(0);

            $table->timestamps();

            $table->index('booking_id');
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();

            // Unique alphanumeric code printed on the voucher PDF
            // (e.g. "V-7BHKQ").  Operators scan this at check-in.
            $table->string('code', 30)->unique();

            // Issuance lifecycle
            $table->enum('status', ['draft', 'issued', 'redeemed', 'voided'])->default('draft');
            $table->dateTime('issued_at')->nullable();
            $table->dateTime('redeemed_at')->nullable();
            $table->dateTime('voided_at')->nullable();

            // Snapshot of the rendered PDF metadata — the actual file
            // lives in Spatie media-library under the 'voucher_pdf'
            // collection.  Storing the URL here lets emails embed it
            // without a join.
            $table->string('pdf_url', 1000)->nullable();
            $table->string('pdf_hash', 64)->nullable();   // sha-256 for tamper detection

            // Optional operator who redeemed (admin_id, central DB ref)
            $table->unsignedBigInteger('redeemed_by_admin_id')->nullable();

            $table->timestamps();

            $table->index(['status', 'issued_at']);
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();

            // Status enum mirrors App\Modules\Tours\Enums\RefundStatus
            $table->string('status', 30)->default('requested');

            // Amounts in minor units, same currency as the parent booking.
            $table->unsignedInteger('amount_requested');
            $table->unsignedInteger('amount_approved')->default(0);
            $table->unsignedInteger('amount_refunded')->default(0);

            // Required when cancellation policy enforces partial refund —
            // captured so the customer can see how the number was derived.
            $table->string('reason_code', 50)->nullable();
            $table->text('reason_text')->nullable();

            // Gateway correlation IDs (Iyzico paymentTransactionId, etc.).
            // Filled by Phase 2 IyzicoGateway::refund() + webhook.
            $table->string('gateway_request_id', 100)->nullable();
            $table->string('gateway_response_code', 50)->nullable();
            $table->json('gateway_payload')->nullable();

            // Who filed the request — null for customer-initiated, set
            // for admin-initiated.
            $table->unsignedBigInteger('requested_by_admin_id')->nullable();  // central DB ref
            $table->unsignedBigInteger('processed_by_admin_id')->nullable();  // central DB ref

            $table->dateTime('requested_at')->nullable();
            $table->dateTime('processed_at')->nullable();

            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index(['status', 'requested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('booking_extras');
        Schema::dropIfExists('booking_passengers');
    }
};
