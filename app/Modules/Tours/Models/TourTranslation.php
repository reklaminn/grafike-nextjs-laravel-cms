<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-language display copy for a Tour.
 * `language_id` references the central DB `languages` table (no FK).
 */
class TourTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id', 'language_id',
        'title', 'subtitle', 'short_description', 'description',
        'highlights', 'important_info',
        'meta_title', 'meta_description', 'og_image_url',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}
