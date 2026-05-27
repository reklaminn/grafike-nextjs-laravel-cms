<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-language display copy for a TourTag.
 * `language_id` references the central DB `languages` table (no FK).
 */
class TourTagTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_tag_id', 'language_id',
        'name', 'description',
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(TourTag::class, 'tour_tag_id');
    }
}
