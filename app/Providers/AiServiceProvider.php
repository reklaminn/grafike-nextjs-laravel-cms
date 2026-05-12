<?php

namespace App\Providers;

use App\Services\Ai\AiBlockEditor;
use App\Services\Ai\AiManager;
use App\Services\Ai\AiModelRouter;
use App\Services\Ai\AiPageGenerator;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\AiSeoGenerator;
use App\Services\Ai\TenantAiResolver;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(AiManager::class, function ($app) {
            /** @var array $config */
            $config = $app['config']->get('ai', []);

            return new AiManager($config);
        });

        // Alias so other services / facades resolve the same singleton.
        $this->app->alias(AiManager::class, 'ai');

        // Tenant-aware resolver: applies BYOK + per-tenant provider/model
        // preferences before delegating to the AiManager.
        $this->app->singleton(TenantAiResolver::class, function ($app) {
            return new TenantAiResolver($app->make(AiManager::class));
        });

        // Quota tracking + enforcement. Keeps usage rows in central DB
        // and throws AiQuotaExceededException when a tenant is over its
        // monthly limit (BYOK tenants exempt).
        $this->app->singleton(AiQuotaService::class, function ($app) {
            $cfg = $app['config']->get('ai', []);

            return new AiQuotaService(
                plans:       $cfg['plans']         ?? [],
                defaultPlan: $cfg['default_plan']  ?? 'free',
                pricing:     $cfg['pricing']       ?? [],
            );
        });

        // High-level router: feature → tier + parameters, with provider
        // fallback chain. Most application code should call this rather
        // than the raw AiManager.
        $this->app->singleton(AiModelRouter::class, function ($app) {
            return new AiModelRouter(
                manager:         $app->make(AiManager::class),
                tenantResolver:  $app->make(TenantAiResolver::class),
                quota:           $app->make(AiQuotaService::class),
                config:          $app['config']->get('ai', []),
            );
        });

        // Feature-level helpers — built on the router.
        $this->app->singleton(AiSeoGenerator::class, function ($app) {
            return new AiSeoGenerator($app->make(AiModelRouter::class));
        });

        $this->app->singleton(AiBlockEditor::class, function ($app) {
            return new AiBlockEditor($app->make(AiModelRouter::class));
        });

        $this->app->singleton(AiPageGenerator::class, function ($app) {
            return new AiPageGenerator($app->make(AiModelRouter::class));
        });
    }

    public function provides(): array
    {
        return [
            AiManager::class,
            'ai',
            TenantAiResolver::class,
            AiQuotaService::class,
            AiModelRouter::class,
            AiSeoGenerator::class,
            AiBlockEditor::class,
            AiPageGenerator::class,
        ];
    }
}
