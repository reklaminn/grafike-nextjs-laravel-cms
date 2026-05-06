<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCmsCache extends Command
{
    protected $signature = 'cms:cache-clear {--sitemap : Clear only sitemap cache} {--settings : Clear only settings cache}';

    protected $description = 'Clear CMS caches (SEO, layout, sitemap, settings)';

    public function handle(): int
    {
        if ($this->option('sitemap')) {
            Cache::forget('sitemap_xml');
            Cache::forget('llms_txt');
            Cache::forget('llms_full_txt');
            $this->info('Sitemap + llms.txt cache cleared.');

            return self::SUCCESS;
        }

        if ($this->option('settings')) {
            $this->clearSettingsCache();
            $this->info('Settings cache cleared.');

            return self::SUCCESS;
        }

        // Clear all CMS caches
        $this->info('Clearing all CMS caches...');

        // Sitemap + LLMs
        Cache::forget('sitemap_xml');
        Cache::forget('llms_txt');
        Cache::forget('llms_full_txt');
        $this->line('  - Sitemap + llms.txt cache cleared');

        // Languages
        Cache::forget('active_languages');
        $this->line('  - Languages cache cleared');

        // Settings
        $this->clearSettingsCache();
        $this->line('  - Settings cache cleared');

        // Flush all SEO resolve caches
        // Since we use prefix-based keys, we need to flush by pattern
        // For file/database cache drivers, we'd need to clear broader
        if (config('cache.default') === 'redis') {
            $prefix = config('cache.prefix', '');
            $keys = Cache::getRedis()->keys("{$prefix}seo_resolve_*");
            foreach ($keys as $key) {
                Cache::forget(str_replace($prefix, '', $key));
            }
            $keys = Cache::getRedis()->keys("{$prefix}layout_*");
            foreach ($keys as $key) {
                Cache::forget(str_replace($prefix, '', $key));
            }
            $this->line('  - SEO & Layout caches cleared (Redis pattern)');
        } else {
            // For file/array drivers, clear the entire cache
            Cache::flush();
            $this->line('  - All cache flushed (file driver)');
        }

        $this->newLine();
        $this->info('CMS cache cleared successfully!');

        return self::SUCCESS;
    }

    protected function clearSettingsCache(): void
    {
        $settings = \App\Models\SiteSetting::all();
        foreach ($settings as $setting) {
            Cache::forget("setting_{$setting->key}");
        }
    }
}
