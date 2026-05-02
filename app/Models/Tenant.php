<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

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
}
