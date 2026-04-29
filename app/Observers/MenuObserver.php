<?php

namespace App\Observers;

use App\Models\Menu;
use App\Services\FrontendRevalidator;
use Illuminate\Support\Facades\Cache;

class MenuObserver
{
    public function saved(Menu $menu): void
    {
        $this->clearMenuCache($menu);
        app(FrontendRevalidator::class)->tags(['menus', "menu-{$menu->location}"]);
    }

    public function deleted(Menu $menu): void
    {
        $this->clearMenuCache($menu);
        app(FrontendRevalidator::class)->tags(['menus', "menu-{$menu->location}"]);
    }

    protected function clearMenuCache(Menu $menu): void
    {
        Cache::forget("menu_{$menu->location}_{$menu->language_id}");
        Cache::forget('sitemap_xml');
    }
}
