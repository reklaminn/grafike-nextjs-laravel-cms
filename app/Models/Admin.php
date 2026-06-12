<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /** Central DB — admins are global across all tenants */
    protected $connection = 'central';

    protected $guard_name = 'admin';

    protected $fillable = [
        'username', 'name', 'email', 'password', 'legacy_password',
        'avatar', 'last_login_ip', 'last_login_at',
    ];

    protected $hidden = [
        'password', 'legacy_password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function tenantAccesses()
    {
        return $this->hasMany(AdminTenantAccess::class);
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'admin_tenant_access', 'admin_id', 'tenant_id')
            ->withPivot(['role', 'is_default'])
            ->withTimestamps();
    }

    public function isAgencyAdmin(): bool
    {
        return $this->hasRole('super-admin') || ! $this->tenantAccesses()->exists();
    }

    public function canAccessTenant(Tenant|string $tenant): bool
    {
        if ($this->isAgencyAdmin()) {
            return true;
        }

        $tenantId = $tenant instanceof Tenant ? $tenant->getTenantKey() : $tenant;

        return $this->tenantAccesses()
            ->where('tenant_id', $tenantId)
            ->exists();
    }

    public function defaultTenantId(): ?string
    {
        return $this->tenantAccesses()
            ->where('is_default', true)
            ->value('tenant_id')
            ?? $this->tenantAccesses()->orderBy('tenant_id')->value('tenant_id');
    }
}
