<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-language display copy for a Port.
 * `language_id` references the central DB `languages` table (no FK).
 */
class PortTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'port_id', 'language_id',
        'name', 'short_description', 'long_description',
        'meta_title', 'meta_description',
    ];

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }
}
