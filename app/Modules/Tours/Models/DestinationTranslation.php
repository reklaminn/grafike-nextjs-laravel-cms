<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-language display copy for a Destination.
 * `language_id` references the central DB `languages` table (no FK).
 */
class DestinationTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'destination_id', 'language_id',
        'name', 'description', 'meta_title', 'meta_description',
    ];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
