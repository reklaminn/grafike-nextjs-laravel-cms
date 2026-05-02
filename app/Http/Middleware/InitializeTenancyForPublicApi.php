<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;
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

        return app(InitializeTenancyByDomain::class)->handle($request, $next);
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
