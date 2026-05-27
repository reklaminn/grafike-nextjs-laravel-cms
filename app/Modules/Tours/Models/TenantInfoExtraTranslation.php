<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-language display copy for a TenantInfoExtra.
 * `language_id` references the central DB `languages` table (no FK).
 */
class TenantInfoExtraTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_info_extra_id', 'language_id',
        'name', 'description',
    ];

    public function extra(): BelongsTo
    {
        return $this->belongsTo(TenantInfoExtra::class, 'tenant_info_extra_id');
    }
}
