<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-language editorial content for a Ship.
 * `language_id` references the central DB `languages` table (no FK).
 */
class ShipTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ship_id', 'language_id',
        'description', 'meta_title', 'meta_description',
    ];

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }
}
