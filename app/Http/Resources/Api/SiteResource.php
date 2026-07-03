<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'site' => [
                'name' => $this['name'],
                'domain' => $this['domain'],
                // Vertical modules enabled for this tenant.  Empty array
                // means "core kurumsal CMS only" — Next.js skips the
                // Tours / Commerce / etc. dynamic bundle imports.
                'modules' => $this['modules'] ?? [],
                'theme' => $this['theme'],
                'tokens' => $this['tokens'],
                'header_variant' => $this['header_variant'],
                'footer_variant' => $this['footer_variant'],
                'locale' => $this['locale'],
                'available_locales' => $this['available_locales'],
                // Bakım/Yakında modu — bypass sonrası değer. true ise frontend
                // gerçek site yerine "Yakında" sayfası gösterir.
                'maintenance' => (bool) ($this['maintenance'] ?? false),
                'maintenance_title' => $this['maintenance_title'] ?? null,
                'maintenance_message' => $this['maintenance_message'] ?? null,
                'maintenance_until' => $this['maintenance_until'] ?? null,
            ],
        ];
    }
}
