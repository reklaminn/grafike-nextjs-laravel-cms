<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiLanguage;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SiteResource;
use App\Models\Language;
use App\Models\SiteSetting;
use App\Models\Theme;

class SiteController extends Controller
{
    use ResolvesApiLanguage;

    public function index()
    {
        // Tenant context is established by stancl middleware (domain-based).
        // Theme is determined by the tenant's theme_id stored in tenant metadata.
        $language = $this->resolveLanguage();
        $tenant   = tenancy()->tenant;
        $theme    = $tenant?->theme_id ? Theme::find($tenant->theme_id) : null;
        $tokens   = $theme?->tokens_json ?? [];

        // Enabled vertical modules drive frontend conditional loading
        // (Tours catalog, Commerce storefront, …).  Empty array means
        // the tenant runs core "kurumsal" only.
        $modules = $tenant && method_exists($tenant, 'enabledModules')
            ? $tenant->enabledModules()
            : [];

        return SiteResource::make([
            'name' => $tenant?->name ?? SiteSetting::get('site.title', config('cms.name', 'Grafike CMS')),
            'domain' => request()->getHost(),
            'modules' => $modules,
            'theme' => [
                'slug'   => $theme?->slug ?? 'porto-furniture',
                'engine' => $theme?->engine ?? 'next',
                'assets' => [
                    'css' => data_get($theme?->assets_json, 'css', []),
                    'js'  => data_get($theme?->assets_json, 'js', []),
                ],
            ],
            'tokens' => [
                'color_primary'    => $tokens['color_primary']    ?? SiteSetting::get('design.color_primary',    '#7c5a3a'),
                'color_secondary'  => $tokens['color_secondary']  ?? SiteSetting::get('design.color_secondary',  '#f3ede6'),
                'color_accent'     => $tokens['color_accent']     ?? SiteSetting::get('design.color_accent',     '#111827'),
                'radius_card'      => $tokens['radius_card']      ?? SiteSetting::get('design.radius_card',      '20px'),
                'radius_button'    => $tokens['radius_button']    ?? SiteSetting::get('design.radius_button',    '999px'),
                'container_width'  => $tokens['container_width']  ?? SiteSetting::get('design.container_width',  '1320px'),
            ],
            'header_variant' => SiteSetting::get('theme.header_variant', 'default-header'),
            'footer_variant' => SiteSetting::get('theme.footer_variant', 'default-footer'),
            'locale'            => $language?->code ?? app()->getLocale(),
            'available_locales' => Language::active()->get(['code', 'locale', 'name']),
        ]);
    }
}
