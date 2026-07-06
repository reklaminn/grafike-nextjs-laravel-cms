<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Article extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'title', 'body', 'content_json', 'excerpt', 'page_id', 'language_id', 'parent_article_id',
        'status', 'sort_order', 'slug', 'external_url', 'link_target', 'template',
        'listing_variant', 'detail_variant', 'category', 'brand',
        'content_type_id', 'form_id', 'is_featured', 'meta_description', 'extra_info',
        'published_at', 'display_date', 'author_id', 'custom_css', 'custom_js', 'legacy_id',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'display_date' => 'date',
            'is_featured'  => 'boolean',
            'content_json' => 'array',
        ];
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    public function parentArticle()
    {
        return $this->belongsTo(Article::class, 'parent_article_id');
    }

    public function translations()
    {
        return $this->hasMany(Article::class, 'parent_article_id');
    }

    public function seo()
    {
        return $this->morphOne(SeoEntry::class, 'seoable');
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('videos');
    }
}
