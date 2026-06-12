<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models\Library;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CENTRAL DB — library_destination_translations (Phase 1.5.h)
 */
class LibraryDestinationTranslation extends Model
{
    protected $connection = 'central';
    protected $table = 'library_destination_translations';

    protected $fillable = [
        'library_destination_id', 'language_id',
        'name', 'description', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return ['language_id' => 'integer'];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(LibraryDestination::class, 'library_destination_id');
    }
}
