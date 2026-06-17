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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
        // OOM / fatal hatalarını yakalayıp hangi URL'de + kaç MB'da patladığını
        // storage/logs/fatal.log'a yaz. Laravel'in kendi handler'ı OOM'da bunu
        // yapamaz (bellek kalmaz). Oku: `bash scripts/diag.sh fatal`.
        \App\Support\FatalLogger::register(storage_path('logs/fatal.log'));

        // Tenant medya URL'leri /tenant-assets/{path}?tenant={id} üzerinden
        // servis edilir (public disk tenant'a göre suffix'li; /storage/{path}
        // 404 verir). Spatie getUrl()'ü TenantMediaUrlGenerator'a yönlendir.
        config(['media-library.url_generator' => \App\Support\TenantMediaUrlGenerator::class]);
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

        // Test ortamı: prod'da tenant tabloları (pages, articles, forms…)
        // ayrı tenant DB'lerinde yaşar; testte tenancy başlatılmadığından
        // modeller default sqlite bağlantısına gider. Tenant migration'larını
        // migrator'a kaydet ki RefreshDatabase'in migrate:fresh'i bu
        // tabloları da test DB'sine kursun. (Subclass'lardaki RefreshDatabase
        // trait'i TestCase override'larını gölgelediği için hook yerine
        // burada loadMigrationsFrom kullanıyoruz.)
        if ($this->app->runningUnitTests()) {
            $this->loadMigrationsFrom(database_path('migrations/tenant'));
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

        // Superadmin panelinden tanımlanan AI API anahtarlarını config'e yükle.
        // central_settings tablosundaki şifreli kayıtlar runtime'da çözülerek
        // config('ai.providers.*.api_key') değerlerini override eder.
        // .env'de anahtar varsa ve DB'de de varsa → DB kazanır (BYOK benzeri).
        // Tablo henüz migrate edilmemişse (fresh install) sessizce geçilir.
        $this->bootAiKeysFromDb();

        // Schema::createIfNotExists($table, $callback) — idempotent create.
        //
        // Module migrations bunu Schema::create yerine kullanır.  Tenant DB
        // bad state'inde (table var ama `migrations` tablosunda kayıt yok)
        // re-run güvenli olur: tablo zaten varsa create atılır, migration
        // row Laravel migrator tarafından normal şekilde INSERT'lenir →
        // tenant self-heals.
        //
        // Tasarım notu: Schema::hasTable check'i Blueprint kapanışından
        // önce yapılır, bu yüzden boş bir Blueprint kurmaya gerek yok.
        Schema::macro('createIfNotExists', function (string $table, \Closure $callback): void {  // @phpstan-ignore-line
            /** @var \Illuminate\Database\Schema\Builder $this */
            if ($this->hasTable($table)) {
                Log::info('Schema::createIfNotExists — table exists, skipping create', [
                    'table'      => $table,
                    'connection' => $this->getConnection()->getName(),
                ]);
                return;
            }
            $this->create($table, $callback);
        });
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    /**
     * central_settings tablosundaki AI API anahtarlarını runtime config'e yükler.
     * Tablo yoksa (fresh install / migration bekliyor) sessizce geçer.
     */
    private function bootAiKeysFromDb(): void
    {
        try {
            $ai = \App\Models\CentralSetting::aiConfig();

            if ($ai['default_provider']) {
                config(['ai.default_provider' => $ai['default_provider']]);
            }
            foreach (['anthropic', 'openrouter', 'openai'] as $provider) {
                if (! empty($ai[$provider])) {
                    config(["ai.providers.{$provider}.api_key" => $ai[$provider]]);
                }
            }
        } catch (\Throwable) {
            // central_settings tablosu henüz yok veya DB bağlantısı yok.
            // .env değerleri geçerli kalır.
        }
    }
}
