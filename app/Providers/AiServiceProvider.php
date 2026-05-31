<?php

namespace App\Providers;

use App\Services\Ai\AiBlockEditor;
use App\Services\Ai\AiManager;
use App\Services\Ai\AiModelRouter;
use App\Services\Ai\AiPageGenerator;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\AiSectionTemplateGenerator;
use App\Services\Ai\AiSeoGenerator;
use App\Services\Ai\AiTranslator;
use App\Services\Ai\AiUsageReporter;
use App\Services\Ai\TenantAiResolver;
use App\Models\AiPlan;
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

            // DB planları öncelikli; tablo yoksa (migration henüz koşmadı) config'e düşer.
            try {
                $plans = AiPlan::allKeyed();
                $defaultPlan = AiPlan::defaultKey();
            } catch (\Throwable) {
                $plans = $cfg['plans'] ?? [];
                $defaultPlan = $cfg['default_plan'] ?? 'free';
            }

            return new AiQuotaService(
                plans:       $plans,
                defaultPlan: $defaultPlan,
                pricing:     $cfg['pricing'] ?? [],
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

        $this->app->singleton(AiTranslator::class, function ($app) {
            return new AiTranslator($app->make(AiModelRouter::class));
        });

        $this->app->singleton(AiSectionTemplateGenerator::class, function ($app) {
            return new AiSectionTemplateGenerator($app->make(AiModelRouter::class));
        });

        // Read-only analytics — no dependencies, just queries ai_usage.
        $this->app->singleton(AiUsageReporter::class);
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
            AiTranslator::class,
            AiSectionTemplateGenerator::class,
            AiUsageReporter::class,
        ];
    }
}
