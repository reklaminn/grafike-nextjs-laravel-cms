<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models\Library;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CENTRAL DB — library_cabin_group_translations (Phase 1.5.h)
 */
class LibraryCabinGroupTranslation extends Model
{
    protected $connection = 'central';
    protected $table = 'library_cabin_group_translations';

    protected $fillable = [
        'library_cabin_group_id', 'language_id', 'name', 'description',
    ];

    protected function casts(): array
    {
        return ['language_id' => 'integer'];
    }

    public function cabinGroup(): BelongsTo
    {
        return $this->belongsTo(LibraryCabinGroup::class, 'library_cabin_group_id');
    }
}
