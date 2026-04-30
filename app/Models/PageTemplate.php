<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageTemplate extends Model
{
    use HasFactory;

    /** Central DB — page templates are shared across all tenants */
    protected $connection = 'central';

    protected $fillable = [
        'theme_id',
        'name',
        'slug',
        'page_type',
        'sections_json',
        'default_settings_json',
        'preview_image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sections_json' => 'array',
            'default_settings_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
