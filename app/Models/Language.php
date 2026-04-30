<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    /** Central DB — languages are shared across all tenants */
    protected $connection = 'central';

    protected $fillable = ['name', 'code', 'locale', 'is_active', 'direction', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // NOTE: pages() and articles() relations are intentionally omitted.
    // Language lives in the central DB; pages/articles live in per-tenant DBs.
    // Cross-DB HasMany relations cannot be satisfied by Eloquent's single-connection
    // query builder. Resolve the association at the application layer instead:
    //   $pages = Page::where('language_id', $language->id)->get();  // inside tenant context
}
