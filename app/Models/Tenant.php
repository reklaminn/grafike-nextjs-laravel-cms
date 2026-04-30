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
     * Custom columns stored in the JSON `data` column.
     * Access via $tenant->name, $tenant->theme_id, etc.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
        ];
    }

    /**
     * Meta fields stored in the `data` JSON column.
     * e.g. $tenant->name, $tenant->status, $tenant->theme_id
     */
    protected function getDataColumn(): string
    {
        return 'data';
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
