<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-language display copy for a TourCategory.
 * `language_id` references the central DB `languages` table (no FK).
 */
class TourCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_category_id', 'language_id',
        'name', 'description', 'meta_title', 'meta_description',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class, 'tour_category_id');
    }
}
