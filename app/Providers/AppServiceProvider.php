<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Menu;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Observers\ArticleObserver;
use App\Observers\DomainObserver;
use App\Observers\MenuObserver;
use App\Observers\PageObserver;
use App\Observers\SiteSettingObserver;
use App\View\Composers\FrontendComposer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Database\Models\Domain;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS URLs so all generated URLs (form actions, route(), asset(),
        // redirect()) always use https://.
        //
        // Two layers of defence:
        //   1. If APP_URL already starts with https://, force it as the root URL —
        //      works for both artisan commands and HTTP requests.
        //   2. In non-local / non-testing environments ALWAYS force the scheme to
        //      https, even when APP_URL=http:// (stale config cache after deploy).
        //      The ForceHttps middleware will call forceRootUrl with the correct host
        //      per-request.
        $appUrl = config('app.url', env('APP_URL', ''));
        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
            URL::forceRootUrl($appUrl);
        } elseif (! app()->environment(['local', 'testing'])) {
            // Stale cache or mis-set APP_URL — still force https scheme.
            URL::forceScheme('https');
        }

        // Register model observers for cache invalidation
        Page::observe(PageObserver::class);
        Article::observe(ArticleObserver::class);
        SiteSetting::observe(SiteSettingObserver::class);
        Menu::observe(MenuObserver::class);

        // Tenant domain CRUD → regenerate the Traefik dynamic-config file so
        // the reverse proxy picks up new tenant Host() routers (each with its
        // own ACME HTTP-01 cert) without a Traefik restart.
        // See app/Services/TraefikDynamicConfig.php for the file format.
        Domain::observe(DomainObserver::class);

        // Register view composer for frontend layouts
        View::composer('frontend.layouts.*', FrontendComposer::class);
    }
}
