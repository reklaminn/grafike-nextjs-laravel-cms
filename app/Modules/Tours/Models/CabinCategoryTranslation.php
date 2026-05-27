<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-language display copy for a CabinCategory.
 * `language_id` references the central DB `languages` table (no FK).
 */
class CabinCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'cabin_category_id', 'language_id',
        'name', 'description',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CabinCategory::class, 'cabin_category_id');
    }
}
