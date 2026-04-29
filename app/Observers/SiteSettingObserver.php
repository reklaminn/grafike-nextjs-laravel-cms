<?php

namespace App\Observers;

use App\Models\SiteSetting;
use App\Services\FrontendRevalidator;
use Illuminate\Support\Facades\Cache;

class SiteSettingObserver
{
    public function saved(SiteSetting $setting): void
    {
        Cache::forget("setting_{$setting->key}");
        app(FrontendRevalidator::class)->tags(['settings', 'site']);
    }

    public function deleted(SiteSetting $setting): void
    {
        Cache::forget("setting_{$setting->key}");
        app(FrontendRevalidator::class)->tags(['settings', 'site']);
    }
}
