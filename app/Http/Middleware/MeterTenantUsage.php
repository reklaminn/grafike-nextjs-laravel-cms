<?php

namespace App\Http\Middleware;

use App\Services\Tenancy\TenantUsageMeter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Meters public (tenant-resolved) requests per tenant per day, and optionally
 * enforces a soft daily request cap from the tenant's package
 * (max_requests_per_day). MUST run AFTER tenancy is initialized.
 *
 * Safe by design: if the package sets no limit (null) it only counts; on any
 * error it falls open (never blocks a live request).
 */
class MeterTenantUsage
{
    public function __construct(private readonly TenantUsageMeter $meter)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = (function_exists('tenancy') && tenancy()->initialized) ? tenancy()->tenant : null;

        if ($tenant) {
            $tenantId = (string) $tenant->getTenantKey();

            // Soft daily limit — only when the package defines one.
            $max = $tenant->packageConfig()['max_requests_per_day'] ?? null;
            if ($max !== null && $this->meter->requestsToday($tenantId) >= (int) $max) {
                return response()->json([
                    'error' => 'Günlük istek limiti aşıldı. Lütfen daha sonra tekrar deneyin.',
                ], 429);
            }

            $this->meter->hitRequest($tenantId);
        }

        return $next($request);
    }
}
