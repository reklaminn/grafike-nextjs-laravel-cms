<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models\Library;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CENTRAL DB — library_ship_translations (Phase 1.5.h)
 */
class LibraryShipTranslation extends Model
{
    protected $connection = 'central';
    protected $table = 'library_ship_translations';

    protected $fillable = [
        'library_ship_id', 'language_id',
        'description', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return ['language_id' => 'integer'];
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(LibraryShip::class, 'library_ship_id');
    }
}
