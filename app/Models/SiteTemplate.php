<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteTemplate extends Model
{
    use HasFactory;

    /** Central DB — site templates are shared across all tenants */
    protected $connection = 'central';

    protected $fillable = [
        'theme_id',
        'name',
        'slug',
        'industry',
        'description',
        'summary',
        'snapshot_json',
        'preview_image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_json' => 'array',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer',
        ];
    }

    /**
     * Canonical industry codes used by the gallery + seeders. Free-text
     * values still work but the UI groups by these.
     */
    public const INDUSTRIES = [
        'clinic'      => 'Klinik / Sağlık',
        'lawyer'      => 'Avukat / Hukuk',
        'salon'       => 'Güzellik Salonu / Berber',
        'hotel'       => 'Otel / Konaklama',
        'real_estate' => 'Emlak',
        'corporate'   => 'Genel Kurumsal',
    ];

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForIndustry($query, string $industry)
    {
        return $query->where('industry', $industry);
    }

    public function industryLabel(): ?string
    {
        return $this->industry ? (self::INDUSTRIES[$this->industry] ?? $this->industry) : null;
    }
}
