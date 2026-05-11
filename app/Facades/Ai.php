<?php

namespace App\Facades;

use App\Services\Ai\AiManager;
use App\Services\Ai\Contracts\AiProvider;
use Illuminate\Support\Facades\Facade;

/**
 * Convenience facade for the AI manager.
 *
 * @method static AiProvider provider(?string $name = null)
 * @method static string     resolveModel(string $tier, ?string $providerName = null)
 * @method static string     defaultProvider()
 * @method static void       extend(string $driver, \Closure $factory)
 * @method static void       flush()
 *
 * @see AiManager
 */
class Ai extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AiManager::class;
    }
}
