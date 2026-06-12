<?php

namespace Tests\Unit\Models;

use App\Models\Tenant;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\UsesSqliteCentralDb;
use Tests\TestCase;

/**
 * Tenant model AI helper coverage (FAZ 1.9 + 3.3 + 3.4).
 *
 * Encryption, plan resolution, BYOK switch, and the data-JSON storage
 * shape are the core multi-tenant safety surface — a regression here
 * would either leak credentials across sites or let the wrong plan's
 * quota apply. Tests build a fresh in-memory central DB with just the
 * tenants table; no stancl bootstrap required.
 */
class TenantAiHelpersTest extends TestCase
{
    use UsesSqliteCentralDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpCentralSqlite();
        $this->buildCentralTable('tenants');
    }

    /**
     * Insert a tenants row directly via the query builder, then return
     * the model — bypasses stancl's CreateDatabase event pipeline which
     * would try to physically create a tenant DB and fail under tests.
     */
    private function fresh(string $id = 'acme', array $data = []): Tenant
    {
        DB::connection('central')->table('tenants')->insert([
            'id'         => $id,
            'data'       => json_encode($data, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Tenant::query()->findOrFail($id);
    }

    /**
     * Save the model without firing stancl event listeners. Tests just
     * want the data column persisted, not a full provisioning flow.
     */
    private function persist(Tenant $tenant): void
    {
        Tenant::withoutEvents(fn () => $tenant->save());
    }

    public function test_ai_settings_returns_empty_array_when_unset(): void
    {
        $tenant = $this->fresh();
        $this->assertSame([], $tenant->aiSettings());
    }

    public function test_default_plan_falls_back_to_config(): void
    {
        config(['ai.default_plan' => 'free']);
        $tenant = $this->fresh();

        $this->assertSame('free', $tenant->aiPlan());
    }

    public function test_set_ai_plan_persists_to_data_json(): void
    {
        $tenant = $this->fresh();
        $tenant->setAiPlan('pro');
        $this->persist($tenant);

        // Reload from DB and verify
        $fresh = Tenant::query()->findOrFail($tenant->id);
        $this->assertSame('pro', $fresh->aiPlan());
        $this->assertSame('pro', $fresh->aiSettings()['plan']);
    }

    public function test_byok_off_by_default(): void
    {
        $this->assertFalse($this->fresh()->isUsingByokAi());
    }

    public function test_byok_toggle_persists(): void
    {
        $tenant = $this->fresh();

        $settings = $tenant->aiSettings();
        $settings['use_byok'] = true;
        $tenant->setAiSettings($settings);
        $this->persist($tenant);

        $this->assertTrue(Tenant::query()->findOrFail($tenant->id)->isUsingByokAi());
    }

    public function test_set_api_key_round_trip_through_encryption(): void
    {
        $tenant = $this->fresh();
        $tenant->setAiApiKey('anthropic', 'sk-ant-secret-12345');
        $this->persist($tenant);

        $reloaded = Tenant::query()->findOrFail($tenant->id);

        $this->assertSame('sk-ant-secret-12345', $reloaded->aiApiKey('anthropic'));
        $this->assertTrue($reloaded->hasAiApiKey('anthropic'));

        // The stored value is encrypted, not the plaintext
        $stored = $reloaded->aiSettings()['api_keys']['anthropic'] ?? null;
        $this->assertIsString($stored);
        $this->assertNotSame('sk-ant-secret-12345', $stored);
        $this->assertSame('sk-ant-secret-12345', Crypt::decryptString($stored));
    }

    public function test_set_api_key_to_null_removes_it(): void
    {
        $tenant = $this->fresh();
        $tenant->setAiApiKey('openai', 'sk-test');
        $this->persist($tenant);

        $this->assertTrue(Tenant::query()->findOrFail($tenant->id)->hasAiApiKey('openai'));

        $tenant->setAiApiKey('openai', null);
        $this->persist($tenant);

        $this->assertFalse(Tenant::query()->findOrFail($tenant->id)->hasAiApiKey('openai'));
        $this->assertNull(Tenant::query()->findOrFail($tenant->id)->aiApiKey('openai'));
    }

    public function test_decrypt_failure_returns_null_silently(): void
    {
        $tenant = $this->fresh('decryptfail', [
            'ai_settings' => [
                'api_keys' => ['anthropic' => 'this-is-not-a-valid-cipher-text'],
            ],
        ]);

        // No exception; just null — caller falls back to system key.
        $this->assertNull($tenant->aiApiKey('anthropic'));
    }

    public function test_has_api_key_does_not_decrypt(): void
    {
        // hasAiApiKey only checks presence — useful for cheap UI badges
        // without paying the Crypt round-trip.
        $tenant = $this->fresh('haskeytest', [
            'ai_settings' => [
                'api_keys' => ['anthropic' => 'not-real-but-non-empty'],
            ],
        ]);

        $this->assertTrue($tenant->hasAiApiKey('anthropic'));
        // And aiApiKey() correctly returns null (decrypt fails silently)
        $this->assertNull($tenant->aiApiKey('anthropic'));
    }

    public function test_preferred_provider_round_trip(): void
    {
        $tenant = $this->fresh();
        $settings = $tenant->aiSettings();
        $settings['preferred_provider'] = 'openrouter';
        $tenant->setAiSettings($settings);
        $this->persist($tenant);

        $reloaded = Tenant::query()->findOrFail($tenant->id);
        $this->assertSame('openrouter', $reloaded->preferredAiProvider());
    }

    public function test_preferred_model_can_override_per_tier(): void
    {
        $tenant = $this->fresh();
        $settings = $tenant->aiSettings();
        $settings['models'] = [
            'anthropic' => ['simple' => 'haiku-x', 'complex' => 'sonnet-x'],
        ];
        $tenant->setAiSettings($settings);
        $this->persist($tenant);

        $reloaded = Tenant::query()->findOrFail($tenant->id);
        $this->assertSame('haiku-x', $reloaded->preferredAiModel('anthropic', 'simple'));
        $this->assertSame('sonnet-x', $reloaded->preferredAiModel('anthropic', 'complex'));
        $this->assertNull($reloaded->preferredAiModel('anthropic', 'mythical'));
        $this->assertNull($reloaded->preferredAiModel('openai', 'simple'));
    }

    public function test_two_tenants_have_independent_api_keys(): void
    {
        // The whole point of BYOK: tenant A's key never bleeds into tenant B.
        $a = $this->fresh('clinic', []);
        $b = $this->fresh('lawyer', []);

        $a->setAiApiKey('anthropic', 'tenant-A-key');
        $this->persist($a);
        $b->setAiApiKey('anthropic', 'tenant-B-key');
        $this->persist($b);

        $aFresh = Tenant::query()->findOrFail('clinic');
        $bFresh = Tenant::query()->findOrFail('lawyer');

        $this->assertSame('tenant-A-key', $aFresh->aiApiKey('anthropic'));
        $this->assertSame('tenant-B-key', $bFresh->aiApiKey('anthropic'));
    }

    public function test_two_tenants_can_have_different_plans(): void
    {
        $a = $this->fresh('clinic');
        $b = $this->fresh('lawyer');

        $a->setAiPlan('pro');     $this->persist($a);
        $b->setAiPlan('starter'); $this->persist($b);

        $this->assertSame('pro',     Tenant::query()->findOrFail('clinic')->aiPlan());
        $this->assertSame('starter', Tenant::query()->findOrFail('lawyer')->aiPlan());
    }

    public function test_set_ai_settings_does_not_overwrite_existing_ai_keys(): void
    {
        // Multiple sequential operations on ai_settings shouldn't clobber
        // each other — set plan, then set provider, then add a key; all
        // three pieces should survive.
        $tenant = $this->fresh('multi-op');

        $tenant->setAiPlan('pro');
        $this->persist($tenant);

        $tenant = Tenant::query()->findOrFail('multi-op');
        $settings = $tenant->aiSettings();
        $settings['preferred_provider'] = 'openai';
        $tenant->setAiSettings($settings);
        $this->persist($tenant);

        $tenant = Tenant::query()->findOrFail('multi-op');
        $tenant->setAiApiKey('openai', 'final-key');
        $this->persist($tenant);

        $fresh = Tenant::query()->findOrFail('multi-op');
        $this->assertSame('pro',       $fresh->aiPlan());
        $this->assertSame('openai',    $fresh->preferredAiProvider());
        $this->assertSame('final-key', $fresh->aiApiKey('openai'));
    }
}
