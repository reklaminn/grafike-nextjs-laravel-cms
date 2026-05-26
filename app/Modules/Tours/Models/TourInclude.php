<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bullet point under "Dahil olanlar" / "Dahil olmayanlar" on the tour
 * detail page.  `included=true` puts the line in the "included" bucket,
 * `included=false` in "not included".
 *
 * Per-language; language_id references the central DB.
 */
class TourInclude extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id', 'language_id',
        'included', 'text', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'included'   => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function scopeIncluded(Builder $q): Builder
    {
        return $q->where('included', true);
    }

    public function scopeExcluded(Builder $q): Builder
    {
        return $q->where('included', false);
    }
}
