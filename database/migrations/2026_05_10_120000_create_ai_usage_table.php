<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central DB — one row per AI request (success or failure).
 *
 * Lives in the central DB (not per-tenant) so the agency can run global
 * billing/quota queries across all tenants in a single SQL.
 *
 * Indexes are tuned for the two most common queries:
 *   1. "tenant X usage this month"     → (tenant_id, created_at)
 *   2. "global aggregate by feature"   → (feature, created_at)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->create('ai_usage', function (Blueprint $table) {
            $table->id();

            // Nullable — system-level callers without a tenant context
            // (e.g. CLI ai:ping with no tenant) still get logged.
            $table->string('tenant_id', 100)->nullable()->index();

            $table->string('feature', 64)->index();
            $table->string('provider', 32);
            $table->string('model', 128);
            $table->string('tier', 16)->nullable();

            // Token counters — Anthropic reports cache_read separately so we
            // keep it for accuracy when the provider's prompt caching kicks in.
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('cached_input_tokens')->nullable();
            $table->unsignedInteger('total_tokens')->default(0);

            // USD with micro-cent precision — enough headroom for any
            // sane request size; aggregate queries use SUM() on this.
            $table->decimal('cost_usd', 12, 6)->default(0);

            // Billing flags
            $table->boolean('byok')->default(false);              // tenant's own key → doesn't count toward agency quota
            $table->boolean('fallback_used')->default(false);     // primary down, secondary served
            $table->boolean('success')->default(true);            // false rows are failures
            $table->text('error_message')->nullable();

            // Free-form for future analytics / dashboards (FAZ 3.7)
            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent()->index();

            // Composite for "tenant usage this month" queries — first range
            // filter is tenant_id, second is created_at.
            $table->index(['tenant_id', 'created_at'], 'ai_usage_tenant_period_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('ai_usage');
    }
};
