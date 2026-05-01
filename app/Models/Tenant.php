<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * The tenant ID is a string slug (e.g. "nuhcicek") supplied explicitly by
     * the admin on creation. We must override the GeneratesIds trait here
     * because config('tenancy.id_generator') = null leaves the
     * UniqueIdentifierGenerator unbound, which makes getIncrementing() return
     * true — causing Eloquent to treat the key as an auto-increment integer
     * and silently cast the slug to 0.
     */
    public $incrementing = false;

    protected $keyType = 'string';

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
     * Return tenant name (stored in data JSON).
     */
    public function getNameAttribute(): ?string
    {
        return $this->data['name'] ?? null;
    }

    public function getStatusAttribute(): string
    {
        return $this->data['status'] ?? 'active';
    }

    public function getThemeIdAttribute(): ?int
    {
        $val = $this->data['theme_id'] ?? null;
        return $val ? (int) $val : null;
    }

    /**
     * Convenience accessor for the primary domain.
     */
    public function primaryDomain(): ?string
    {
        return $this->domains()->first()?->domain;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereJsonContains('data->status', 'active')
                     ->orWhereNull('data->status');
    }
}
