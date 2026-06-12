<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Page extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, HasRecursiveRelationships;

    protected $fillable = [
        'title', 'parent_id', 'language_id', 'root_page_id', 'status', 'scheduled_at',
        'show_in_menu', 'sort_order', 'slug', 'external_url', 'link_target',
        'module_type', 'template', 'page_template_id', 'page_template', 'frontend_variant',
        'system_key', 'is_system',
        'layout_json', 'sections_json', 'custom_css', 'custom_js',
        'is_password_protected', 'page_password', 'allowed_group_ids',
        'show_social_share', 'show_facebook_comments', 'show_breadcrumb',
        'view_count', 'legacy_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'layout_json' => 'array',
            'sections_json' => 'array',
            'allowed_group_ids' => 'array',
            'is_password_protected' => 'boolean',
            'show_in_menu' => 'boolean',
            'show_social_share' => 'boolean',
            'show_facebook_comments' => 'boolean',
            'show_breadcrumb' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function isSystemPage(): bool
    {
        return (bool) $this->is_system || filled($this->system_key);
    }

    public function getParentKeyName(): string
    {
        return 'parent_id';
    }

    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('sort_order');
    }

    public function pageTemplate()
    {
        return $this->belongsTo(PageTemplate::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class)->orderBy('sort_order');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Other language versions of this page.
     * All pages sharing the same root_page_id are translation siblings.
     */
    public function translations()
    {
        return $this->hasMany(Page::class, 'root_page_id', 'root_page_id')
            ->where('id', '!=', $this->id)
            ->with('language');
    }

    /**
     * The canonical (source) page this was translated from.
     * When root_page_id == id, this IS the source page.
     */
    public function translationSource()
    {
        return $this->belongsTo(Page::class, 'root_page_id');
    }

    public function seo()
    {
        return $this->morphOne(SeoEntry::class, 'seoable');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByLanguage($query, $languageId)
    {
        return $query->where('language_id', $languageId);
    }

    public function revisions()
    {
        return $this->hasMany(PageRevision::class)->orderByDesc('created_at');
    }

    /** Revizyon snapshot'ına giren alanlar — restore da aynı listeyi kullanır. */
    public const REVISION_FIELDS = ['title', 'sections_json', 'layout_json', 'custom_css', 'custom_js'];

    public static function recordSnapshot(self $page, ?string $reason = null, ?array $changedFields = null): PageRevision
    {
        $snapshot = [];
        foreach (self::REVISION_FIELDS as $field) {
            $snapshot[$field] = $page->{$field};
        }
        $snapshot['changed_fields'] = $changedFields ?? [];

        return PageRevision::create([
            'page_id'    => $page->id,
            'admin_id'   => auth()->id(),
            'snapshot'   => $snapshot,
            'reason'     => $reason,
            'created_at' => now(),
        ]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }
}
