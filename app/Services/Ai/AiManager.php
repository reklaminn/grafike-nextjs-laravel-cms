<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\AiProvider;
use App\Services\Ai\Providers\AnthropicProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use App\Services\Ai\Providers\OpenRouterProvider;
use Closure;
use InvalidArgumentException;

/**
 * Central registry + factory for AI providers.
 *
 * Resolves providers lazily on first use and memoizes them per request.
 * Custom drivers can be registered via `extend()` (e.g. in a service
 * provider for a tenant-specific gateway).
 *
 * Usage (without facade):
 *   $ai = app(AiManager::class);
 *   $response = $ai->provider('anthropic')->generate($request);
 *   // or with model-tier resolution:
 *   $model = $ai->resolveModel('complex'); // uses default provider
 */
class AiManager
{
    /** @var array<string, AiProvider> */
    private array $resolved = [];

    /** @var array<string, Closure(array $config, string $name): AiProvider> */
    private array $customDrivers = [];

    public function __construct(private readonly array $config)
    {
    }

    /**
     * Return the resolved provider instance. Pass null to use the default.
     */
    public function provider(?string $name = null): AiProvider
    {
        $name ??= $this->defaultProvider();

        return $this->resolved[$name] ??= $this->resolve($name);
    }

    /**
     * Resolve the model name for a tier ("simple" | "complex") on the
     * given provider (or default). Caller passes the result to AiRequest.
     */
    public function resolveModel(string $tier, ?string $providerName = null): string
    {
        $providerName ??= $this->defaultProvider();
        $models = $this->config['providers'][$providerName]['models'] ?? null;

        if (! is_array($models) || ! isset($models[$tier])) {
            throw new InvalidArgumentException(
                "AI provider [{$providerName}] has no model defined for tier [{$tier}]."
            );
        }

        return $models[$tier];
    }

    public function defaultProvider(): string
    {
        return $this->config['default_provider'] ?? 'anthropic';
    }

    /**
     * Register a custom driver. Useful for tests or vendor-specific gateways.
     *
     * @param  Closure(array $config, string $name): AiProvider  $factory
     */
    public function extend(string $driver, Closure $factory): void
    {
        $this->customDrivers[$driver] = $factory;
        // Drop any cached instance that used the old factory.
        foreach ($this->config['providers'] ?? [] as $providerName => $providerConfig) {
            if (($providerConfig['driver'] ?? $providerName) === $driver) {
                unset($this->resolved[$providerName]);
            }
        }
    }

    /**
     * For tests: clear the memoized instances so config changes are picked up.
     */
    public function flush(): void
    {
        $this->resolved = [];
    }

    private function resolve(string $name): AiProvider
    {
        $providerConfig = $this->config['providers'][$name] ?? null;
        if (! is_array($providerConfig)) {
            throw new InvalidArgumentException("AI provider [{$name}] is not configured.");
        }

        $driver = $providerConfig['driver'] ?? $name;

        if (isset($this->customDrivers[$driver])) {
            return ($this->customDrivers[$driver])($providerConfig, $name);
        }

        return match ($driver) {
            'anthropic'  => new AnthropicProvider($providerConfig),
            'openai'     => new OpenAiProvider($providerConfig),
            'openrouter' => new OpenRouterProvider($providerConfig),
            default      => throw new InvalidArgumentException(
                "Unknown AI driver [{$driver}] for provider [{$name}]. ".
                'Register it with AiManager::extend().',
            ),
        };
    }
}
