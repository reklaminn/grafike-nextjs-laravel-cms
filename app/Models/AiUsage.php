<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per AI call (success OR failure) for billing, quota, and the
 * usage dashboard (FAZ 3.7).
 *
 * Stored in the central DB so the agency can query all tenants at once.
 * BYOK rows are written too (with byok = true) but excluded from quota
 * arithmetic — the tenant paid the vendor directly.
 */
class AiUsage extends Model
{
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'ai_usage';

    public $timestamps = false; // only created_at, set in migration default

    protected $fillable = [
        'tenant_id',
        'feature',
        'provider',
        'model',
        'tier',
        'input_tokens',
        'output_tokens',
        'cached_input_tokens',
        'total_tokens',
        'cost_usd',
        'byok',
        'fallback_used',
        'success',
        'error_message',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'input_tokens'        => 'integer',
        'output_tokens'       => 'integer',
        'cached_input_tokens' => 'integer',
        'total_tokens'        => 'integer',
        'cost_usd'            => 'decimal:6',
        'byok'                => 'boolean',
        'fallback_used'       => 'boolean',
        'success'             => 'boolean',
        'metadata'            => 'array',
        'created_at'          => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /** Scope: rows that count toward the agency quota (i.e. not BYOK). */
    public function scopeBillable($query)
    {
        return $query->where('byok', false);
    }

    /** Scope: rows in [start, end). Both endpoints required to keep indexes useful. */
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }
}
