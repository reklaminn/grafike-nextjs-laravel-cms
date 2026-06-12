<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminTenantAccess extends Model
{
    protected $connection = 'central';

    protected $table = 'admin_tenant_access';

    protected $fillable = [
        'admin_id',
        'tenant_id',
        'role',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
