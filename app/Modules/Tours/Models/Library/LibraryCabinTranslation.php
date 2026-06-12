<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models\Library;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CENTRAL DB — library_cabin_translations (Phase 1.5.h)
 */
class LibraryCabinTranslation extends Model
{
    protected $connection = 'central';
    protected $table = 'library_cabin_translations';

    protected $fillable = [
        'library_cabin_id', 'language_id', 'name', 'description',
    ];

    protected function casts(): array
    {
        return ['language_id' => 'integer'];
    }

    public function cabin(): BelongsTo
    {
        return $this->belongsTo(LibraryCabin::class, 'library_cabin_id');
    }
}
