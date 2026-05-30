<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The tenant ID is a string slug supplied explicitly by the admin.
     * stancl's GeneratesIds trait implements getIncrementing()/getKeyType()
     * itself, so properties alone are not enough when id_generator is null.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Real DB columns on the `tenants` table (everything else goes into `data` JSON).
     * stancl stores all custom attributes in the JSON `data` column unless listed here.
     */
    public static function getCustomColumns(): array
    {
        return ['id'];
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * stancl/virtualcolumn expands the JSON `data` column into normal model
     * attributes after retrieval. Accessors must read that decoded value, not
     * `$this->data`, because the trait clears `data` after decoding.
     */
    public function getNameAttribute($value): ?string
    {
        return $value;
    }

    public function getStatusAttribute($value): string
    {
        return $value ?: 'active';
    }

    public function getThemeIdAttribute($value): ?int
    {
        return $value !== null && $value !== '' ? (int) $value : null;
    }

    /**
     * Convenience accessor for the primary domain.
     */
    public function primaryDomain(): ?string
    {
        return $this->domains()->first()?->domain;
    }

    public function adminAccesses()
    {
        return $this->hasMany(AdminTenantAccess::class, 'tenant_id', 'id');
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_tenant_access', 'tenant_id', 'admin_id')
            ->withPivot(['role', 'is_default'])
            ->withTimestamps();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereJsonContains('data->status', 'active')
                     ->orWhereNull('data->status');
    }

    // ─── AI settings (BYOK) ───────────────────────────────────────────────────
    //
    // AI configuration lives inside the tenant's `data` JSON column under the
    // `ai_settings` key. Shape:
    //
    //   ai_settings: {
    //     use_byok: bool,             // when true, system uses tenant key/models
    //     preferred_provider: string, // 'anthropic' | 'openai' | 'openrouter'
    //     api_keys: { anthropic: encrypted, openai: encrypted, ... },
    //     models:   { anthropic: { simple, complex }, openai: { ... }, ... }
    //   }
    //
    // API keys are encrypted via Laravel's Crypt facade (uses APP_KEY). The
    // raw key never leaves this model — `aiApiKey()` is the only decrypt path.

    public function aiSettings(): array
    {
        $raw = $this->getAttribute('ai_settings');

        return is_array($raw) ? $raw : [];
    }

    public function isUsingByokAi(): bool
    {
        return (bool) ($this->aiSettings()['use_byok'] ?? false);
    }

    public function preferredAiProvider(): ?string
    {
        $value = $this->aiSettings()['preferred_provider'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Tenant's AI plan name (free | starter | pro | enterprise | …).
     * Falls back to config('ai.default_plan') when unset.
     */
    public function aiPlan(): string
    {
        $value = $this->aiSettings()['plan'] ?? null;

        return is_string($value) && $value !== ''
            ? $value
            : (string) config('ai.default_plan', 'free');
    }

    public function setAiPlan(string $plan): void
    {
        $settings         = $this->aiSettings();
        $settings['plan'] = $plan;
        $this->setAiSettings($settings);
    }

    /**
     * Tenant'ın paketi (config/packages.php). Geçersiz/boşsa varsayılana düşer.
     */
    public function package(): string
    {
        $pkg = $this->getAttribute('package');
        $packages = (array) config('packages.packages', []);

        return (is_string($pkg) && isset($packages[$pkg]))
            ? $pkg
            : (string) config('packages.default', 'basic');
    }

    /** @return array<string,mixed> */
    public function packageConfig(): array
    {
        return (array) (config('packages.packages.' . $this->package(), []) ?: []);
    }

    /** İzin verilen yönetici kullanıcı sayısı; null = sınırsız. */
    public function maxAdminUsers(): ?int
    {
        $max = $this->packageConfig()['max_users'] ?? null;

        return $max === null ? null : (int) $max;
    }

    public function preferredAiModel(string $provider, string $tier): ?string
    {
        $value = $this->aiSettings()['models'][$provider][$tier] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Decrypted API key for the given provider, or null when none is stored
     * (or when decryption fails because APP_KEY rotated).
     */
    public function aiApiKey(string $provider): ?string
    {
        $encrypted = $this->aiSettings()['api_keys'][$provider] ?? null;
        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException $e) {
            report($e);

            return null;
        }
    }

    /**
     * Returns true when the tenant has *some* API key on file for the provider
     * (without decrypting it). Useful for UI badges.
     */
    public function hasAiApiKey(string $provider): bool
    {
        $encrypted = $this->aiSettings()['api_keys'][$provider] ?? null;

        return is_string($encrypted) && $encrypted !== '';
    }

    /**
     * Persist a new API key for the given provider (encrypts before save).
     * Pass an empty string to clear the stored key.
     */
    public function setAiApiKey(string $provider, ?string $apiKey): void
    {
        $settings = $this->aiSettings();
        $settings['api_keys'] = $settings['api_keys'] ?? [];

        if ($apiKey === null || $apiKey === '') {
            unset($settings['api_keys'][$provider]);
        } else {
            $settings['api_keys'][$provider] = Crypt::encryptString($apiKey);
        }

        $this->setAiSettings($settings);
    }

    /**
     * Replace the entire ai_settings sub-structure (preserves other tenant
     * data). Callers should normally use the granular setters above; this
     * is exposed for admin UI bulk-save flows.
     */
    public function setAiSettings(array $settings): void
    {
        $this->setAttribute('ai_settings', $settings);
    }

    // ─── Vertical modules (Tours, Commerce, Payments, …) ─────────────────────
    //
    // Multi-vertical support: a tenant can opt into one or more bounded
    // contexts beyond the always-on "core" CMS. Enabled modules are stored
    // as a string array under `data.modules` (alongside ai_settings).
    //
    // Module registry / definitions live in config/tenant_modules.php.
    // The plumbing (install, uninstall, migrations, admin UI) is wired
    // through App\Services\Modules\{ModuleRegistry,ModuleManager} and the
    // tenant:module:* artisan commands.
    //
    // Backward compatibility: existing tenants have no `modules` key —
    // enabledModules() returns [] and hasModule() returns false, so they
    // continue to operate as pure "kurumsal core" tenants with zero
    // behavioural change.

    /**
     * Return the list of vertical modules enabled for this tenant.
     * Empty array means "core only" (no extra verticals).
     *
     * Values are normalised to lowercase strings; duplicates are stripped.
     */
    public function enabledModules(): array
    {
        $raw = $this->getAttribute('modules');

        if (! is_array($raw)) {
            return [];
        }

        $modules = [];
        foreach ($raw as $entry) {
            if (! is_string($entry) || $entry === '') {
                continue;
            }
            $modules[] = strtolower(trim($entry));
        }

        return array_values(array_unique($modules));
    }

    /**
     * Check whether a given vertical module is enabled for this tenant.
     *
     * `core` is always-on and reported as enabled even if it is not
     * literally present in the modules array — this keeps callers simple
     * (no special-casing required).
     */
    public function hasModule(string $module): bool
    {
        $module = strtolower(trim($module));

        if ($module === '' || $module === 'core') {
            return true;
        }

        return in_array($module, $this->enabledModules(), true);
    }

    /**
     * Replace the modules array wholesale. Caller is responsible for
     * dependency resolution (use ModuleManager for safe install/uninstall
     * including dependencies and tenant DB migrations).
     */
    public function setEnabledModules(array $modules): void
    {
        $normalised = [];
        foreach ($modules as $entry) {
            if (! is_string($entry) || $entry === '') {
                continue;
            }
            $slug = strtolower(trim($entry));
            // `core` is implicit — never persisted.
            if ($slug === 'core') {
                continue;
            }
            $normalised[] = $slug;
        }

        $this->setAttribute('modules', array_values(array_unique($normalised)));
    }

    /**
     * Add a single module to the enabled list (idempotent).
     * Does NOT run migrations — use ModuleManager::install() for that.
     */
    public function enableModule(string $module): void
    {
        $module = strtolower(trim($module));

        if ($module === '' || $module === 'core') {
            return;
        }

        $modules = $this->enabledModules();
        if (! in_array($module, $modules, true)) {
            $modules[] = $module;
            $this->setEnabledModules($modules);
        }
    }

    /**
     * Remove a module from the enabled list (idempotent).
     * Does NOT drop tables — use ModuleManager::uninstall(..., dropTables: true).
     */
    public function disableModule(string $module): void
    {
        $module = strtolower(trim($module));

        if ($module === '' || $module === 'core') {
            return;
        }

        $modules = array_values(array_filter(
            $this->enabledModules(),
            fn (string $m) => $m !== $module
        ));

        $this->setEnabledModules($modules);
    }
}
