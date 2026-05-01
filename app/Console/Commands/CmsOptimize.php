<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CmsOptimize extends Command
{
    protected $signature = 'cms:optimize {--clear : Clear all caches instead of building them}';

    protected $description = 'Optimize CMS for production (cache config, routes, views, CMS data)';

    public function handle(): int
    {
        if ($this->option('clear')) {
            return $this->clearAll();
        }

        $this->info('Optimizing CMS for production...');
        $this->newLine();

        // Laravel optimizations — run these first and unconditionally so that
        // config/route/view caches are always built even if CMS DB caches fail.
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        $this->call('event:cache');

        // CMS-specific optimizations — DB-dependent; failures are non-fatal.
        $this->info('Building CMS caches...');

        try {
            // Pre-warm SEO cache
            $seoEntries = \App\Models\SeoEntry::with('seoable')->get();
            foreach ($seoEntries as $entry) {
                \Illuminate\Support\Facades\Cache::put(
                    "seo_resolve_{$entry->slug}_",
                    [
                        'type' => 'content',
                        'entity_type' => $entry->seoable_type,
                        'entity' => $entry->seoable,
                        'seo' => $entry,
                    ],
                    config('cms.cache.ttl', 600)
                );
            }
            $this->line("  Warmed {$seoEntries->count()} SEO entries");

            // Pre-warm settings cache
            $settings = \App\Models\SiteSetting::all();
            foreach ($settings as $setting) {
                \Illuminate\Support\Facades\Cache::put(
                    "setting_{$setting->key}",
                    $setting->value,
                    600
                );
            }
            $this->line("  Warmed {$settings->count()} settings");

            // Pre-warm languages cache
            $languages = \App\Models\Language::active()->orderBy('sort_order')->get();
            \Illuminate\Support\Facades\Cache::put('active_languages', $languages, 3600);
            $this->line("  Warmed {$languages->count()} languages");
        } catch (\Throwable $e) {
            $this->warn("  DB cache warm skipped: {$e->getMessage()}");
        }

        $this->newLine();
        $this->info('CMS optimization complete!');

        return self::SUCCESS;
    }

    protected function clearAll(): int
    {
        $this->info('Clearing all caches...');

        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('event:clear');
        $this->call('cache:clear');
        $this->call('cms:cache-clear');

        $this->newLine();
        $this->info('All caches cleared!');

        return self::SUCCESS;
    }
}
