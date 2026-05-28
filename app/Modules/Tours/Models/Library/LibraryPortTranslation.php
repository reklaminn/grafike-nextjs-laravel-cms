<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models\Library;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CENTRAL DB — library_port_translations (Phase 1.5.h)
 */
class LibraryPortTranslation extends Model
{
    protected $connection = 'central';
    protected $table = 'library_port_translations';

    protected $fillable = [
        'library_port_id', 'language_id',
        'name', 'short_description', 'long_description',
        'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return ['language_id' => 'integer'];
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(LibraryPort::class, 'library_port_id');
    }
}
