<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate an admin route group on the active tenant having a specific
 * vertical module enabled.
 *
 * Usage in route definitions:
 *
 *   Route::middleware(['admin.auth', 'tenant.admin', 'tenant.module:tours'])
 *       ->prefix('admin/tours')
 *       ->name('admin.tours.')
 *       ->group(function () { … });
 *
 * Behaviour:
 *   - No active_tenant in session → 403 with friendly hint ("Select a site
 *     from the sidebar selector first").
 *   - Active tenant exists but does NOT have the module → 404 (the
 *     route is invisible because the module isn't installed; surfacing
 *     it as 404 prevents leaking which modules exist on which tenants).
 *   - Active tenant has the module → next middleware.
 */
class RequireTenantModule
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $tenantId = session('active_tenant');

        if (! $tenantId) {
            abort(403, 'Önce bir site seçmelisiniz (sol üst seçici).');
        }

        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant || ! method_exists($tenant, 'hasModule') || ! $tenant->hasModule($module)) {
            // 404 to avoid exposing module-status to admins of other tenants
            abort(404);
        }

        return $next($request);
    }
}
