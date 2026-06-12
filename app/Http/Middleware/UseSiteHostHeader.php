<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseSiteHostHeader
{
    /**
     * Next.js SSR calls Laravel through the Docker service name (app1), but
     * tenant resolution must run against the original public site host.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantPreviewHost = $this->resolveTenantPreviewHost($request);
        $siteHost = $tenantPreviewHost ?: $this->resolveSiteHost($request);

        if ($siteHost && ($tenantPreviewHost || $this->isInternalDockerHost($request->getHost()))) {
            $siteHost = $this->normalizeHost($siteHost);

            if ($siteHost !== '') {
                $request->headers->set('host', $siteHost);
                // Also update X-Forwarded-Host in both bags so that
                // Symfony's Request::getHost() (which reads the TRUSTED
                // HTTP_X_FORWARDED_HOST server var when TrustProxies is
                // active) returns the overridden value, not the original
                // Docker-internal service name (app1/app2/app3) that
                // Nginx injects via `fastcgi_param HTTP_X_FORWARDED_HOST
                // $http_host`.
                $request->headers->set('X-Forwarded-Host', $siteHost);
                $request->server->set('HTTP_HOST', $siteHost);
                $request->server->set('HTTP_X_FORWARDED_HOST', $siteHost);
                $request->server->set('SERVER_NAME', $siteHost);
            }
        }

        return $next($request);
    }

    private function resolveSiteHost(Request $request): ?string
    {
        // Priority:
        //  1. Explicit X-Site-Host sent by the Next.js SSR client
        //  2. X-Forwarded-Host (set by Traefik or the SSR client)
        //  3. CMS_FRONTEND_URL (the public site URL)
        //  4. CENTRAL_DOMAIN env (always set in docker-compose — most reliable fallback)
        //  5. APP_URL (Laravel's own URL — last resort)
        return $request->headers->get('X-Site-Host')
            ?: $request->headers->get('X-Forwarded-Host')
            ?: $this->hostFromUrl(config('cms.frontend_url'))
            ?: $this->hostFromUrl(env('CMS_FRONTEND_URL'))
            ?: $this->hostFromUrl(env('CENTRAL_DOMAIN'))
            ?: $this->hostFromUrl(config('app.url'));
    }

    private function resolveTenantPreviewHost(Request $request): ?string
    {
        $tenantId = $request->headers->get('X-Tenant-ID');

        if (! is_string($tenantId) || ! preg_match('/^[a-zA-Z0-9_-]+$/', $tenantId)) {
            return null;
        }

        try {
            return tenancy()->central(function () use ($tenantId) {
                return Tenant::with('domains')->find($tenantId)?->domains->first()?->domain;
            });
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeHost(?string $host): string
    {
        if (! $host) {
            return '';
        }

        $host = explode(',', $host)[0];
        $host = preg_replace('#^https?://#', '', rtrim(trim($host), '/'));

        return strtolower(explode(':', $host)[0]);
    }

    private function hostFromUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return parse_url($url, PHP_URL_HOST) ?: $url;
    }

    private function isInternalDockerHost(string $host): bool
    {
        $normalized = $this->normalizeHost($host);

        // Real public domains always contain at least one dot
        // (e.g. cms.dentistkusadasi.com, smoke-test.grafike.site).
        // Internal Docker service names are single-word identifiers
        // (app, app1, app2, app3, grafike_cms_app, redis, gateway, …)
        // and so is `localhost` for local dev.
        //
        // Treating "no dot" as "internal" is intentionally broad so a new
        // internal service added later (e.g. a dedicated `api` container)
        // is detected automatically without touching this list.  No real
        // tenant domain can ever match because TLDs require a dot.
        //
        // Previous bug: the list only contained app1/app2/app3 and missed
        // the `app` service used by docker-compose.hostinger.yml's
        // frontend (`CMS_API_URL=http://app:80`).  SSR calls landed on
        // `app`, isInternalDockerHost returned false, X-Site-Host was
        // ignored, and InitializeTenancyByDomain looked up tenant
        // `domain=app` — never finding one, falling back to central and
        // serving the demo seed instead of the real tenant content.
        return $normalized !== '' && ! str_contains($normalized, '.');
    }
}
