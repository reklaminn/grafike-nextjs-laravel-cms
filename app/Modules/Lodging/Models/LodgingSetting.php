<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * LodgingSetting — single-row per-tenant module config.  Access the row
 * (creating a default on first use) via LodgingSetting::current().
 */
class LodgingSetting extends Model
{
    protected $fillable = [
        'notification_email',
        'whatsapp_number',
        'reservation_code_prefix',
        'default_currency',
    ];

    /**
     * The tenant's single settings row, lazily created with defaults.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'reservation_code_prefix' => 'RZ',
            'default_currency'        => 'TRY',
        ]);
    }
}
