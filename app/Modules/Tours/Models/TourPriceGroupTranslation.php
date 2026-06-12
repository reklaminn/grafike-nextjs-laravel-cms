<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-language display copy for a TourPriceGroup.
 * `language_id` references the central DB `languages` table (no FK).
 */
class TourPriceGroupTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_price_group_id', 'language_id',
        'name', 'description',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(TourPriceGroup::class, 'tour_price_group_id');
    }
}
