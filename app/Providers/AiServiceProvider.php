<?php

namespace App\Providers;

use App\Services\Ai\AiManager;
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
    }

    public function provides(): array
    {
        return [AiManager::class, 'ai'];
    }
}
