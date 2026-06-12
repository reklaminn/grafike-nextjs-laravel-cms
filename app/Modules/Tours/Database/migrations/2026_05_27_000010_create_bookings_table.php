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
     * Booking schema (Phase 1 lock).  The lifecycle is enforced by
     * BookingStateMachine (Phase 2); this migration just establishes
     * the shape so other Phase 1 work (price quoting, manifests, etc.)
     * can compile against the final column set.
     *
     * Guest checkout: `member_id` is nullable.  Anonymous bookings carry
     * the visitor's contact info in `customer_snapshot` JSON so the
     * booking is self-contained even when the optional Member record
     * is later deleted.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Human-friendly reference shown on emails, vouchers,
            // operations dashboards (e.g. "TUR-2026-0001234").
            // Globally unique within the tenant DB.
            $table->string('booking_ref', 50)->unique();

            // Pointer to the departure.  Cascade-restrict: an admin who
            // tries to delete a tour_date that still has bookings will
            // see a friendly error rather than silent data loss.
            $table->foreignId('tour_date_id')
                ->constrained('tour_dates')
                ->restrictOnDelete();

            // Optional member (logged-in customer); guest bookings leave
            // this null.  Member model lives in the tenant DB too, so
            // a real FK constraint is safe.
            $table->foreignId('member_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete();

            // Lifecycle — see App\Modules\Tours\Enums\BookingStatus.
            // String column (not ENUM) for forward-compat with new states.
            $table->string('status', 30)->default('pending');

            // Money — minor units, currency snapshotted from the tour
            // at booking time (so later currency edits on the tour don't
            // rewrite history).
            $table->string('currency', 3);
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('extras_total')->default(0);
            $table->integer('discount_total')->default(0);   // signed (negative reduces)
            $table->unsignedInteger('tax_total')->default(0);
            $table->unsignedInteger('total_amount')->default(0);
            $table->unsignedInteger('amount_paid')->default(0);
            $table->unsignedInteger('amount_refunded')->default(0);

            // Deposit / balance flow — `deposit_due_amount` is what the
            // user must pay immediately; the rest is invoiced N days
            // before departure (Phase 5 reminders cron).
            $table->unsignedInteger('deposit_due_amount')->default(0);
            $table->dateTime('balance_due_at')->nullable();

            // Locale at the moment of booking (for re-rendering emails
            // in the user's original language).
            $table->string('locale', 10)->default('tr');

            // Snapshot of the customer's contact info at booking time —
            // even guest bookings keep enough to reach them later.
            // Shape: { full_name, email, phone, country_code, address,
            //          notes, marketing_opt_in }
            $table->json('customer_snapshot');

            // Lifecycle timestamps for UX + analytics.
            $table->dateTime('reserved_at')->nullable();   // Pending → Reserved
            $table->dateTime('confirmed_at')->nullable();  // Reserved → Confirmed
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('hold_expires_at')->nullable(); // when Reserved auto-flips to Expired

            // Internal admin notes (not shown to customer).
            $table->text('internal_notes')->nullable();

            // Source / channel tagging for marketing attribution.
            $table->string('source', 100)->nullable();      // 'web' | 'phone' | 'agent' | …
            $table->json('utm_params')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index('tour_date_id');
            $table->index('member_id');
            $table->index('hold_expires_at'); // queried by ExpireUnpaidReservation job
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
