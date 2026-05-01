<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Menu;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Observers\ArticleObserver;
use App\Observers\MenuObserver;
use App\Observers\PageObserver;
use App\Observers\SiteSettingObserver;
use App\View\Composers\FrontendComposer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Force both the scheme AND the root URL so all generated URLs
        // (form actions, route(), asset(), redirect()) are always https://.
        //
        // forceScheme alone can still be overridden if the URL generator's root
        // was already seeded with http:// from the request before our boot ran.
        // forceRootUrl replaces the root completely — nothing can override it.
        //
        // We key off APP_URL so local dev (http://localhost) is unaffected.
        $appUrl = config('app.url', env('APP_URL', ''));
        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
            URL::forceRootUrl($appUrl);
        }

        // Register model observers for cache invalidation
        Page::observe(PageObserver::class);
        Article::observe(ArticleObserver::class);
        SiteSetting::observe(SiteSettingObserver::class);
        Menu::observe(MenuObserver::class);

        // Register view composer for frontend layouts
        View::composer('frontend.layouts.*', FrontendComposer::class);
    }
}
