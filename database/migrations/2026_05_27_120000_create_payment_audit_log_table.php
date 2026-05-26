<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Central DB — Payments compliance log
     *
     * Minimal audit trail of every payment event (init / capture /
     * refund / webhook) so a tenant DB deletion can NEVER lose the
     * paper trail.  Used by:
     *   - Daily reconciliation cron (Phase 5)
     *   - Iyzico chargeback investigations (months after the fact)
     *   - KVKK / tax compliance (years after the fact)
     *
     * The log is append-only — there is no `up`-then-`down` flow on
     * individual rows; lifecycle changes append new rows tagged with
     * an `event` discriminator.
     */
    public function up(): void
    {
        // Use the same connection name the rest of the app uses for
        // the central DB.  Migrations under database/migrations/ run
        // against the default connection, which is the central one in
        // this project (see config/database.php).
        Schema::create('payment_audit_log', function (Blueprint $table) {
            $table->id();

            // Which tenant owned this transaction — string slug to
            // match Tenant.id.
            $table->string('tenant_id', 100)->index();

            // Gateway slug ('iyzico' for now)
            $table->string('gateway', 50);

            // Discriminator for the event being logged.  Kept open
            // (string column, not enum) for forward-compat.
            //   'init', 'init_failed', 'capture', 'capture_failed',
            //   'refund', 'refund_completed', 'webhook'
            $table->string('event', 50)->index();

            // Cross-DB correlation keys — both ours and the gateway's
            // so reconciliation can match in either direction.
            $table->string('conversation_id', 80);
            $table->string('gateway_payment_id', 100)->nullable();

            // Business reference for human ops (e.g. the booking_ref
            // from the Tours module). Stored as plain text so we can
            // grep for it without traversing tenant DBs.
            $table->string('payable_type', 255)->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->string('payable_ref', 100)->nullable();

            // Money snapshot — enough to settle disputes without
            // accessing tenant DB.
            $table->string('currency', 3)->nullable();
            $table->unsignedInteger('amount')->nullable();

            $table->string('status', 30)->nullable();

            // Free-form JSON of the gateway response.  Bounded by the
            // application (we strip card-like fields) but otherwise
            // verbatim so chargebacks can be defended with raw evidence.
            $table->json('payload')->nullable();

            // Source IP of the webhook / browser callback — helps
            // identify replay attempts against the audit log itself.
            $table->ipAddress('source_ip')->nullable();

            // Append-only timestamp; no `updated_at`.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['conversation_id']);
            $table->index(['gateway_payment_id']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['payable_ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_audit_log');
    }
};
