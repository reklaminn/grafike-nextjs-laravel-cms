<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiLanguage;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MenuResource;
use App\Models\Menu;

class MenuController extends Controller
{
    use ResolvesApiLanguage;

    public function index()
    {
        // Tenant context is established by stancl middleware.
        // All Menu queries run on the current tenant's DB — no site_id filter needed.
        $language = $this->resolveLanguage();

        $menus = Menu::query()
            // language_id = X VEYA NULL — IndustryTemplateApplier/StarterSeeder
            // menüleri language_id=null kurar; katı filtre bunları düşürüp menüyü
            // boş bırakıyordu (header/footer çıkmıyordu).
            ->when($language, fn ($query) => $query->where(fn ($q) => $q
                ->where('language_id', $language->id)
                ->orWhereNull('language_id')))
            ->where('is_active', true)
            ->with([
                'items' => fn ($query) => $query
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with([
                        'children' => fn ($childQuery) => $childQuery
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->with('page'),
                        'page',
                    ]),
            ])
            ->orderBy('location')
            ->orderBy('name')
            ->get();

        return MenuResource::collection($menus);
    }

    public function show(string $location)
    {
        $language = $this->resolveLanguage();

        $menu = Menu::query()
            ->where('location', $location)
            // language_id = X VEYA NULL (bkz. index) — null-dil menüler düşmesin.
            ->when($language, fn ($query) => $query->where(fn ($q) => $q
                ->where('language_id', $language->id)
                ->orWhereNull('language_id')))
            ->where('is_active', true)
            ->with([
                'items' => fn ($query) => $query
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with([
                        'children' => fn ($childQuery) => $childQuery
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->with('page'),
                        'page',
                    ]),
            ])
            ->firstOrFail();

        return MenuResource::make($menu);
    }
}
