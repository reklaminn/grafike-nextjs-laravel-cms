<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'seoable_id', 'seoable_type', 'slug', 'language_id', 'meta_title',
        'meta_description', 'meta_keywords', 'h1_override', 'canonical_url',
        'hreflang_tags', 'is_noindex', 'page_css', 'page_js', 'legacy_id',
        'schema_type', 'structured_data',
        'og_image', 'og_type',
        'sitemap_priority', 'sitemap_changefreq', 'sitemap_exclude',
    ];

    protected function casts(): array
    {
        return [
            'is_noindex'       => 'boolean',
            'sitemap_exclude'  => 'boolean',
            'sitemap_priority' => 'float',
            'hreflang_tags'    => 'array',
            'structured_data'  => 'array',
        ];
    }

    public function seoable()
    {
        return $this->morphTo();
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
