<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Günlük tenant kullanım kaydı (central DB). `cms:rollup-usage` doldurur,
 * tenant panosu okur. Bkz. migration: create_tenant_usage_daily_table.
 */
class TenantUsageDaily extends Model
{
    protected $connection = 'central';

    protected $table = 'tenant_usage_daily';

    protected $fillable = [
        'tenant_id', 'date', 'requests', 'logins', 'storage_mb', 'db_mb', 'users',
    ];

    protected function casts(): array
    {
        return [
            'date'       => 'date',
            'storage_mb' => 'float',
            'db_mb'      => 'float',
        ];
    }
}
