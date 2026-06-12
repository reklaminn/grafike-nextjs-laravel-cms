<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Test trait: re-routes the `central` connection to an in-memory SQLite
 * for the duration of one test, then builds just the central tables a
 * given test cares about.
 *
 * Why: production uses MariaDB for the central DB (separate from each
 * tenant's MariaDB), but the test env only sets up a sqlite default
 * connection. Without this re-route, anything that talks to `central`
 * (AiUsage, Tenant, Admin, SectionTemplate, …) tries to reach the prod
 * MariaDB host and fails.
 *
 * Usage:
 *   use UsesSqliteCentralDb;
 *
 *   protected function setUp(): void
 *   {
 *       parent::setUp();
 *       $this->setUpCentralSqlite();
 *       $this->buildCentralTable('ai_usage');
 *       $this->buildCentralTable('tenants');
 *       $this->buildCentralTable('admins');
 *       $this->buildCentralTable('admin_tenant_access');
 *   }
 *
 * Pick only the tables your test actually uses — keeps setUp fast.
 */
trait UsesSqliteCentralDb
{
    protected function setUpCentralSqlite(): void
    {
        config([
            'database.connections.central' => [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
                'foreign_key_constraints' => false, // tenant_id is a string slug, FK matching to tenants would require seeding tenants first
            ],
        ]);
        DB::purge('central');
    }

    /**
     * Build one of the well-known central tables. Each table's schema is
     * mirrored from its production migration — keep in sync when the
     * migration changes.
     */
    protected function buildCentralTable(string $name): void
    {
        match ($name) {
            'ai_usage' => $this->buildAiUsage(),
            'tenants'  => $this->buildTenants(),
            'admins'   => $this->buildAdmins(),
            'admin_tenant_access' => $this->buildAdminTenantAccess(),
            'site_templates'      => $this->buildSiteTemplates(),
            'section_templates'   => $this->buildSectionTemplates(),
            default => throw new \InvalidArgumentException("No test schema builder for central table [{$name}]"),
        };
    }

    private function buildAiUsage(): void
    {
        Schema::connection('central')->create('ai_usage', function (Blueprint $t) {
            $t->id();
            $t->string('tenant_id', 100)->nullable()->index();
            $t->string('feature', 64)->index();
            $t->string('provider', 32);
            $t->string('model', 128);
            $t->string('tier', 16)->nullable();
            $t->unsignedInteger('input_tokens')->default(0);
            $t->unsignedInteger('output_tokens')->default(0);
            $t->unsignedInteger('cached_input_tokens')->nullable();
            $t->unsignedInteger('total_tokens')->default(0);
            $t->decimal('cost_usd', 12, 6)->default(0);
            $t->boolean('byok')->default(false);
            $t->boolean('fallback_used')->default(false);
            $t->boolean('success')->default(true);
            $t->text('error_message')->nullable();
            $t->json('metadata')->nullable();
            $t->timestamp('created_at')->useCurrent();
        });
    }

    private function buildTenants(): void
    {
        Schema::connection('central')->create('tenants', function (Blueprint $t) {
            $t->string('id', 100)->primary();
            $t->json('data')->nullable();
            $t->timestamps();
        });
    }

    private function buildAdmins(): void
    {
        Schema::connection('central')->create('admins', function (Blueprint $t) {
            $t->id();
            $t->string('name', 255);
            $t->string('username', 255)->unique();
            $t->string('email', 255)->unique();
            $t->string('password', 255);
            $t->string('role', 50)->default('agency'); // for isAgencyAdmin() check
            $t->timestamp('email_verified_at')->nullable();
            $t->rememberToken();
            $t->timestamps();
        });
    }

    private function buildAdminTenantAccess(): void
    {
        Schema::connection('central')->create('admin_tenant_access', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('admin_id');
            $t->string('tenant_id', 100);
            $t->string('role', 50)->default('member');
            $t->boolean('is_default')->default(false);
            $t->timestamps();
            $t->unique(['admin_id', 'tenant_id']);
        });
    }

    private function buildSiteTemplates(): void
    {
        Schema::connection('central')->create('site_templates', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('theme_id')->nullable();
            $t->string('name', 255);
            $t->string('slug', 255)->unique();
            $t->string('industry', 64)->nullable()->index();
            $t->text('description')->nullable();
            $t->string('summary', 255)->nullable();
            $t->json('snapshot_json')->nullable();
            $t->string('preview_image', 500)->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    private function buildSectionTemplates(): void
    {
        Schema::connection('central')->create('section_templates', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('theme_id')->nullable();
            $t->string('name', 255);
            $t->string('type', 64)->index();
            $t->string('variation', 100)->nullable();
            $t->string('render_mode', 32)->default('html');
            $t->text('html_template')->nullable();
            $t->json('schema_json')->nullable();
            $t->json('default_content_json')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }
}
