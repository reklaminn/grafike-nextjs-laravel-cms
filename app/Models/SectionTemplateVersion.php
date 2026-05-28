<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionTemplateVersion extends Model
{
    /**
     * Central DB — version history belongs to the (central) section_templates
     * catalog.  Without this, the relation/queries fall back to the default
     * connection, which is the *tenant* DB whenever a site is active, where
     * `section_template_versions` does not exist → 500 on save/restore.
     */
    protected $connection = 'central';

    public $timestamps = false;

    protected $fillable = [
        'section_template_id',
        'admin_id',
        'label',
        'html_template',
        'schema_json',
        'default_content_json',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'schema_json' => 'array',
            'default_content_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function sectionTemplate()
    {
        return $this->belongsTo(SectionTemplate::class);
    }

    public function admin()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'admin_id');
    }
}
