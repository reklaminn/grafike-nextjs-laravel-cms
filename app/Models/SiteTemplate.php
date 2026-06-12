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
     *
     * Industry vs. vertical modules:
     *   - `industry` is content-flavour (drives AI templates + site_template
     *     seed selection).  A "tourism" tenant gets travel-themed page
     *     templates by default.
     *   - `tours` / `commerce` are vertical modules — separate transactional
     *     features (booking engine, cart, payments).  An admin still has
     *     to enable them via the tenant's "Modüller" panel.
     *   Industry suggests modules but does not auto-activate them.
     */
    public const INDUSTRIES = [
        'clinic'      => 'Klinik / Sağlık',
        'lawyer'      => 'Avukat / Hukuk',
        'salon'       => 'Güzellik Salonu / Berber',
        'hotel'       => 'Otel / Konaklama',
        'tourism'     => 'Turizm / Tur Acentesi',
        'ecommerce'   => 'E-Ticaret',
        'real_estate' => 'Emlak',
        'corporate'   => 'Genel Kurumsal',
    ];

    /**
     * Hint which vertical modules typically pair with each industry.
     * Surfaced in the admin UI as "Turizm seçtin — Tours modülünü de
     * etkinleştirmek ister misin?" guidance, without auto-activating.
     */
    public const INDUSTRY_MODULE_HINTS = [
        'tourism'   => ['tours'],
        'hotel'     => ['tours'],     // package + daily tours common
        'ecommerce' => ['commerce'],
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
