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
}
