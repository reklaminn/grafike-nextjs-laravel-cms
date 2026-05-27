<?php

declare(strict_types=1);

namespace App\Services\Modules;

use InvalidArgumentException;

/**
 * Read-only view over config/tenant_modules.php.
 *
 * Centralises lookups so that callers never need to know the config key
 * shape.  Adding a new vertical means editing the config file once;
 * registry consumers automatically pick it up.
 */
class ModuleRegistry
{
    /**
     * Raw definitions from config/tenant_modules.php → 'definitions'.
     * Keyed by module slug.
     *
     * @var array<string, array<string, mixed>>
     */
    private array $definitions;

    public function __construct(?array $definitions = null)
    {
        // Allow DI override in tests; default to config.
        $this->definitions = $definitions ?? (array) config('tenant_modules.definitions', []);
    }

    /**
     * Every known module slug (whether user-installable or internal).
     *
     * @return array<int, string>
     */
    public function all(): array
    {
        return array_keys($this->definitions);
    }

    /**
     * Modules that admins can toggle in the UI.  Dependency-only modules
     * (e.g. `payments`) and not-yet-shipped modules (e.g. `commerce`
     * during Phase 0) are filtered out.
     *
     * @return array<int, string>
     */
    public function userInstallable(): array
    {
        return array_values(array_filter(
            $this->all(),
            fn (string $slug) => (bool) ($this->definitions[$slug]['user_installable'] ?? false)
        ));
    }

    public function exists(string $module): bool
    {
        return isset($this->definitions[strtolower(trim($module))]);
    }

    /**
     * Full definition row for a module (label, description, paths, etc.).
     *
     * @return array<string, mixed>
     */
    public function definition(string $module): array
    {
        $slug = strtolower(trim($module));

        if (! isset($this->definitions[$slug])) {
            throw new InvalidArgumentException("Unknown tenant module: {$module}");
        }

        return $this->definitions[$slug];
    }

    public function label(string $module): string
    {
        return (string) ($this->definition($module)['label'] ?? $module);
    }

    public function description(string $module): string
    {
        return (string) ($this->definition($module)['description'] ?? '');
    }

    /**
     * Absolute filesystem path containing the module's migration files.
     * Returns null when the module has no migrations.
     */
    public function migrationsPath(string $module): ?string
    {
        $rel = (string) ($this->definition($module)['migrations_path'] ?? '');

        if ($rel === '') {
            return null;
        }

        // Allow either absolute paths or paths relative to the app root.
        if (str_starts_with($rel, DIRECTORY_SEPARATOR)) {
            return $rel;
        }

        return base_path($rel);
    }

    public function serviceProvider(string $module): ?string
    {
        $value = $this->definition($module)['service_provider'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Seeder class names to run after the module's migrations.  Returns
     * empty array when the module has no seeders configured.
     *
     * Seeders are run inside the tenant context after migrations have
     * applied — see ModuleManager::install().
     *
     * @return array<int, class-string>
     */
    public function seeders(string $module): array
    {
        $list = $this->definition($module)['seeders'] ?? [];

        if (! is_array($list)) {
            return [];
        }

        return array_values(array_filter(
            $list,
            fn ($entry) => is_string($entry) && class_exists($entry),
        ));
    }

    /**
     * Direct prerequisite modules (does NOT recurse).
     *
     * @return array<int, string>
     */
    public function requires(string $module): array
    {
        $list = $this->definition($module)['requires'] ?? [];

        if (! is_array($list)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($entry) => is_string($entry) ? strtolower(trim($entry)) : '',
            $list
        )));
    }

    /**
     * Transitive prerequisites in install-order (deps before dependents).
     * The target module itself is the last entry in the returned array.
     *
     * Throws on circular dependencies or unknown modules in the chain.
     *
     * @return array<int, string>
     */
    public function resolveInstallOrder(string $module): array
    {
        $slug    = strtolower(trim($module));
        $visited = [];
        $order   = [];

        $walk = function (string $current, array $stack) use (&$walk, &$visited, &$order) {
            if (in_array($current, $stack, true)) {
                throw new InvalidArgumentException(
                    'Circular module dependency: ' . implode(' → ', [...$stack, $current])
                );
            }
            if (isset($visited[$current])) {
                return;
            }
            if (! $this->exists($current)) {
                throw new InvalidArgumentException("Unknown tenant module in dependency chain: {$current}");
            }

            foreach ($this->requires($current) as $dep) {
                $walk($dep, [...$stack, $current]);
            }

            $visited[$current] = true;
            $order[]           = $current;
        };

        $walk($slug, []);

        return $order;
    }
}
