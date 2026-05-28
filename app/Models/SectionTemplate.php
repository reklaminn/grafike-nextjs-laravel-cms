<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SectionTemplate extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    /** Central DB — section templates are shared across all tenants */
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'theme_id',
        'type',
        'variation',
        'name',
        'render_mode',
        'component_key',
        'legacy_module_key',
        'html_template',
        'schema_json',
        'legacy_config_map_json',
        'default_content_json',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'schema_json' => 'array',
            'legacy_config_map_json' => 'array',
            'default_content_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('preview_image')->singleFile();
    }

    public function getPreviewImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('preview_image') ?: null;
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function versions()
    {
        return $this->hasMany(SectionTemplateVersion::class)->orderByDesc('created_at');
    }

    public function recordVersion(string $reason = 'manual', ?string $label = null): SectionTemplateVersion
    {
        return $this->versions()->create([
            'admin_id'            => auth('admin')->id(),
            'label'               => $label,
            'html_template'       => $this->html_template,
            'schema_json'         => $this->schema_json,
            'default_content_json'=> $this->default_content_json,
            'reason'              => $reason,
            'created_at'          => now(),
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Restrict to rows the given tenant may see: global (tenant_id IS NULL)
     * plus the tenant's own rows.  Passing null (agency context, no active
     * site) yields only the global/shared catalog.
     */
    public function scopeVisibleTo($query, ?string $tenantId)
    {
        return $query->where(function ($q) use ($tenantId) {
            $q->whereNull('tenant_id');

            if ($tenantId !== null && $tenantId !== '') {
                $q->orWhere('tenant_id', $tenantId);
            }
        });
    }
}
