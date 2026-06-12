<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Payments module
     *
     * Polymorphic transactions table:
     *   - payable_type / payable_id point at the business object being
     *     paid for (Booking from Tours module, Order from Commerce in
     *     Phase 6, …).  Storing the FQCN lets one transactions table
     *     serve every module without coupling.
     *
     *   - gateway carries the name from PaymentGateway::name()
     *     ('iyzico' for now).
     *
     *   - conversation_id is our own correlation key sent to the
     *     gateway; gateway_payment_id is the gateway's reply.
     *
     *   - amount + currency + status are the canonical financial truth
     *     within the tenant DB; the central payment_audit_log mirrors
     *     a minimal subset so reconciliation survives tenant deletion.
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            // Polymorphic owner
            $table->string('payable_type', 255);
            $table->unsignedBigInteger('payable_id');

            // Gateway identity + correlation
            $table->string('gateway', 50);                    // e.g. 'iyzico'
            $table->string('conversation_id', 80)->unique();  // ours, idempotency key
            $table->string('gateway_payment_id', 100)->nullable();           // their parent id
            $table->string('gateway_payment_txn_id', 100)->nullable();       // their inner txn id (refund target)

            // Lifecycle — values from App\Modules\Payments\Enums\PaymentStatus
            $table->string('status', 30)->default('pending');

            // Money — minor units, snapshotted at init time so a later
            // gateway dispute always has the historic amount.
            $table->string('currency', 3);
            $table->unsignedInteger('amount');                 // requested
            $table->unsignedInteger('amount_captured')->default(0);
            $table->unsignedInteger('amount_refunded')->default(0);

            // 3DS / fraud diagnostics — surfaced in admin debug view.
            $table->string('three_ds_mode', 20)->nullable();   // '3D' | 'NON_3D' | etc.
            $table->string('fraud_status', 30)->nullable();    // gateway-specific
            $table->boolean('sandbox')->default(false);

            // Error capture for failed attempts
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();

            // Raw last-response JSON — invaluable for debugging
            // gateway-side disputes.  Use sparingly in code; readers
            // should prefer the typed columns above.
            $table->json('raw_response')->nullable();

            // Lifecycle timestamps
            $table->dateTime('initialized_at')->nullable();
            $table->dateTime('captured_at')->nullable();
            $table->dateTime('failed_at')->nullable();

            $table->timestamps();

            $table->index(['payable_type', 'payable_id']);
            $table->index(['gateway', 'status']);
            $table->index('status');
            $table->index('gateway_payment_id');
        });

        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();

            // FK to the parent transaction we are refunding.
            $table->foreignId('payment_transaction_id')
                ->constrained('payment_transactions')
                ->cascadeOnDelete();

            // Gateway correlation
            $table->string('conversation_id', 80)->unique();
            $table->string('gateway_refund_id', 100)->nullable();

            // Status mirrors RefundStatus enum from Tours module — same
            // labels, kept here as plain string so Payments stays
            // decoupled (Tours can refund a booking; Commerce can refund
            // an order; each module owns its own RefundStatus enum).
            $table->string('status', 30)->default('requested');

            $table->string('currency', 3);
            $table->unsignedInteger('amount_requested');
            $table->unsignedInteger('amount_refunded')->default(0);

            $table->string('reason_code', 50)->nullable();
            $table->text('reason_text')->nullable();

            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();

            $table->json('raw_response')->nullable();

            $table->dateTime('requested_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->timestamps();

            $table->index(['payment_transaction_id', 'status']);
            $table->index('gateway_refund_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
        Schema::dropIfExists('payment_transactions');
    }
};
