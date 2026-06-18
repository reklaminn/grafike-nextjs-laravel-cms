<?php

namespace App\Support;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

/**
 * Tenant-aware Spatie MediaLibrary URL generator.
 *
 * The `public` disk root is suffixed per tenant (config tenancy.filesystem
 * root_override → storage/app/public/tenant_{id}/...). Spatie's default
 * generator builds `{disk.url}/{path}` = `/storage/{path}`, which 404s
 * because that path lacks the tenant segment and the `public/storage`
 * symlink points at the central, un-suffixed root.
 *
 * Instead, route media through the app's TenantAssetController
 * (`/tenant-assets/{path}`), which resolves the file from the tenant public
 * disk (+ legacy/central fallbacks). That route identifies the tenant from a
 * `?tenant={id}` query (see InitializeTenancyForPublicApi) — required because
 * a plain <img> request on the central admin domain carries no tenant context.
 *
 * Wired in via config('media-library.url_generator') (AppServiceProvider).
 */
class TenantMediaUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        if (function_exists('tenancy') && tenancy()->initialized && tenant()) {
            $path = ltrim($this->getPathRelativeToRoot(), '/');

            // GÖRECELİ URL (domain'siz): görsel hangi domain'de render edilirse
            // o domain'den yüklenir → public sitede cms.grafcore.com GÖRÜNMEZ,
            // tenant kendi domain'inden servis eder (tenancy domain'den çözülür).
            // Admin (central domain) içinse ?tenant şart (orada domain-tenant yok),
            // tenant domain'de redundant ama zararsız.
            return '/tenant-assets/'.$path.'?tenant='.urlencode((string) tenant()->getTenantKey());
        }

        // Central / no-tenant context → default behaviour (/storage/{path}).
        return parent::getUrl();
    }
}
