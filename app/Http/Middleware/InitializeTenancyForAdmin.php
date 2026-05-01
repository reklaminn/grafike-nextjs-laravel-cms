<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin-side tenant context initializer.
 *
 * Central domain admin routes are NOT covered by stancl's domain middleware.
 * This middleware reads `active_tenant` from the session and initializes the
 * correct tenant DB connection so that Page, Article, Menu, etc. queries run
 * against the selected tenant's database.
 *
 * Applied to all protected admin routes. Routes that don't need tenant context
 * (e.g. TenantController's index/create) work correctly because central models
 * (Admin, Theme, Language…) carry `protected $connection = 'central'` and are
 * unaffected by which tenant is active.
 */
class InitializeTenancyForAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();
        $tenantId = session('active_tenant');

        if ($admin && ! $admin->isAgencyAdmin()) {
            if (! $tenantId || ! $admin->canAccessTenant($tenantId)) {
                $tenantId = $admin->defaultTenantId();

                if ($tenantId) {
                    session(['active_tenant' => $tenantId]);
                } else {
                    session()->forget('active_tenant');

                    abort(403, 'Bu yöneticiye atanmış site bulunmuyor.');
                }
            }
        }

        if ($tenantId) {
            try {
                tenancy()->initialize($tenantId);
            } catch (\Throwable) {
                // Tenant no longer exists or DB unreachable — clear stale session key
                session()->forget('active_tenant');
            }
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        // End tenancy context after the request is finished so it does not
        // leak into subsequent requests on the same PHP process (e.g. Octane).
        if (session('active_tenant') && tenancy()->initialized) {
            tenancy()->end();
        }
    }
}
