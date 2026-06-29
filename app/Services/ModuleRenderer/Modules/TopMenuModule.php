<?php

namespace App\Services\ModuleRenderer\Modules;

use App\Models\Article;
use App\Models\Menu;
use App\Models\Page;

class TopMenuModule extends BaseModule
{
    public function getName(): string
    {
        return 'Üst Menü';
    }

    protected function getViewName(): string
    {
        return 'frontend.modules.top-menu';
    }

    protected function getData(array $config, Page $page, ?Article $article): array
    {
        // Load header menu for current language
        $languageId = $page->language_id ?? 1;

        $menu = Menu::where('location', 'header')
            // language_id = X VEYA NULL — null-dil menüler (StarterSeeder/Industry) düşmesin
            ->where(fn ($q) => $q->where('language_id', $languageId)->orWhereNull('language_id'))
            ->where('is_active', true)
            ->with(['items' => function ($q) {
                $q->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with(['children' => function ($q2) {
                        $q2->where('is_active', true)->orderBy('sort_order');
                    }, 'page']);
            }])
            ->first();

        return [
            'menu' => $menu,
            'menuItems' => $menu?->items ?? collect(),
            'currentSlug' => $page->slug,
            'template' => $config['temp'] ?? null,
        ];
    }
}
