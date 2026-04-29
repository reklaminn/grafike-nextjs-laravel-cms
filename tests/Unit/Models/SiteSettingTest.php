<?php

namespace Tests\Unit\Models;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_setting(): void
    {
        SiteSetting::factory()->create([
            'key' => 'site_name',
            'value' => 'Grafike',
        ]);

        $this->assertDatabaseHas('site_settings', [
            'key' => 'site_name',
            'value' => 'Grafike',
        ]);
    }

    public function test_get_returns_value(): void
    {
        SiteSetting::factory()->create([
            'key' => 'test_key',
            'value' => 'test_value',
        ]);

        $this->assertEquals('test_value', SiteSetting::get('test_key'));
    }

    public function test_get_returns_default_when_missing(): void
    {
        $this->assertEquals('fallback', SiteSetting::get('nonexistent', 'fallback'));
    }

    public function test_set_creates_new_setting(): void
    {
        SiteSetting::set('new_key', 'new_value', 'general');

        $this->assertDatabaseHas('site_settings', [
            'key' => 'new_key',
            'value' => 'new_value',
        ]);
    }

    public function test_set_updates_existing_setting(): void
    {
        SiteSetting::factory()->create(['key' => 'update_key', 'value' => 'old']);

        SiteSetting::set('update_key', 'new');

        $this->assertDatabaseHas('site_settings', [
            'key' => 'update_key',
            'value' => 'new',
        ]);
    }

    public function test_set_clears_cache(): void
    {
        Cache::put('setting_cached_key', 'cached_value', 600);

        SiteSetting::set('cached_key', 'fresh_value');

        $this->assertNull(Cache::get('setting_cached_key'));
    }

    public function test_get_caches_result(): void
    {
        SiteSetting::factory()->create(['key' => 'cache_test', 'value' => 'original']);

        // First call caches
        SiteSetting::get('cache_test');

        // Verify cache exists — key format is "setting_{siteId}_{key}", siteId null → ""
        $this->assertTrue(Cache::has('setting__cache_test'));
    }
}
