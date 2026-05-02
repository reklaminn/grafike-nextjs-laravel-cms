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
                $request->server->set('HTTP_HOST', $siteHost);
                $request->server->set('SERVER_NAME', $siteHost);
            }
        }

        return $next($request);
    }

    private function resolveSiteHost(Request $request): ?string
    {
        return $request->headers->get('X-Site-Host')
            ?: $request->headers->get('X-Forwarded-Host')
            ?: $this->hostFromUrl(config('cms.frontend_url'))
            ?: $this->hostFromUrl(env('CMS_FRONTEND_URL'))
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
        return in_array($this->normalizeHost($host), [
            'app1',
            'app2',
            'app3',
            'grafike_cms_app1',
            'grafike_cms_app2',
            'grafike_cms_app3',
        ], true);
    }
}
