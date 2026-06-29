<?php

namespace App\Services\ModuleRenderer\Modules;

use App\Models\Article;
use App\Models\Menu;
use App\Models\Page;
use App\Models\SiteSetting;

class CorporateMenuModule extends BaseModule
{
    public function getName(): string
    {
        return 'Kurumsal Menü';
    }

    protected function getViewName(): string
    {
        return 'frontend.modules.corporate-menu';
    }

    protected function getData(array $config, Page $page, ?Article $article): array
    {
        $languageId = $page->language_id ?? 1;

        // Load the corporate/institutional menu
        $menu = Menu::where('location', 'header')
            // language_id = X VEYA NULL — null-dil menüler (StarterSeeder/Industry) düşmesin
            ->where(fn ($q) => $q->where('language_id', $languageId)->orWhereNull('language_id'))
            ->where('is_active', true)
            ->with(['items' => function ($q) {
                $q->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with('page');
            }])
            ->first();

        return [
            'menu' => $menu,
            'menuItems' => $menu?->items ?? collect(),
            'companyName' => SiteSetting::get('site.company_name', ''),
            'phone' => SiteSetting::get('contact.phone', ''),
            'email' => SiteSetting::get('contact.email', ''),
            'currentSlug' => $page->slug,
            'socialLinks' => [
                'facebook' => SiteSetting::get('social.facebook'),
                'instagram' => SiteSetting::get('social.instagram'),
                'twitter' => SiteSetting::get('social.twitter'),
                'youtube' => SiteSetting::get('social.youtube'),
                'linkedin' => SiteSetting::get('social.linkedin'),
            ],
        ];
    }
}
