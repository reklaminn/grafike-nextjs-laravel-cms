<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    /** Central DB — themes are shared across all tenants */
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'module',
        'name',
        'slug',
        'engine',
        'description',
        'assets_json',
        'tokens_json',
        'settings_schema_json',
        'preview_image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'assets_json' => 'array',
            'tokens_json' => 'array',
            'settings_schema_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function sectionTemplates()
    {
        return $this->hasMany(SectionTemplate::class);
    }

    public function pageTemplates()
    {
        return $this->hasMany(PageTemplate::class);
    }

    public function siteTemplates()
    {
        return $this->hasMany(SiteTemplate::class);
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

    /**
     * Hide GLOBAL rows that belong to a vertical module the active tenant does
     * NOT have enabled. Own rows (tenant_id set) are never module-gated.
     * Pass null to disable gating (agency / no active site → see everything).
     *
     * Chain after visibleTo(): ->visibleTo($id)->visibleForModules($modules)
     */
    public function scopeVisibleForModules($query, ?array $modules)
    {
        if ($modules === null) {
            return $query;
        }

        return $query->where(function ($q) use ($modules) {
            $q->whereNotNull('tenant_id')   // tenant-owned rows pass through
              ->orWhereNull('module');      // generic global rows
            if (! empty($modules)) {
                $q->orWhereIn('module', $modules); // module-specific globals the tenant has
            }
        });
    }
}
