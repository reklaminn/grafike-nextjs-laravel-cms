<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyForPublicApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $this->resolvePreviewTenantId($request);

        if ($tenantId) {
            try {
                tenancy()->initialize($tenantId);
            } catch (TenantCouldNotBeIdentifiedException) {
                abort(404);
            }

            return $next($request);
        }

        if ($this->isCentralDomain($request->getHost())) {
            abort(404);
        }

        // Catch the domain-not-found exception so it becomes a clean 404 instead
        // of an unhandled exception. This is the defensive last line — normally
        // UseSiteHostHeader has already overridden the Host header to the real
        // tenant domain before this middleware runs. Without the try/catch, every
        // request that arrives with an unresolvable host (Docker internal names,
        // stale container names, health-check pings, …) floods Sentry with
        // TenantCouldNotBeIdentifiedOnDomainException events.
        try {
            return app(InitializeTenancyByDomain::class)->handle($request, $next);
        } catch (TenantCouldNotBeIdentifiedOnDomainException) {
            abort(404);
        }
    }

    private function resolvePreviewTenantId(Request $request): ?string
    {
        $tenantId = $request->headers->get('X-Tenant-ID')
            ?: $request->headers->get('X-Tenant')
            ?: $request->query('tenant')
            ?: $request->query('tenant_id')
            // Backward/typo tolerance for manual preview checks.
            ?: $request->query('tanent');

        if (! is_string($tenantId) || ! preg_match('/^[a-zA-Z0-9_-]+$/', $tenantId)) {
            return null;
        }

        return $tenantId;
    }

    private function isCentralDomain(string $host): bool
    {
        $host = $this->normalizeHost($host);

        return collect(config('tenancy.central_domains', []))
            ->map(fn ($domain) => $this->normalizeHost((string) $domain))
            ->contains($host);
    }

    private function normalizeHost(string $host): string
    {
        $host = preg_replace('#^https?://#', '', trim($host));
        $host = explode(',', $host)[0];

        return strtolower(explode(':', $host)[0]);
    }
}
