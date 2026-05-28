<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards admin pages whose data lives ONLY in a tenant database
 * (e.g. design_assets, smtp_profiles).
 *
 * `InitializeTenancyForAdmin` (alias `tenant.admin`) initializes tenancy when a
 * site is selected, but for an *agency* admin with no active site it is a no-op
 * — tenancy stays uninitialized and the default DB connection points at the
 * CENTRAL database.  A tenant-scoped model queried in that state silently reads
 * (and `firstOrCreate` even WRITES) rows in the central DB, so the page shows
 * data unrelated to any real tenant — exactly the "başka tenant bilgisi"
 * symptom on the Tasarım (CSS/JS) and SMTP screens.
 *
 * This middleware MUST run after `tenant.admin`; ordering is enforced via the
 * middleware priority list in bootstrap/app.php.
 *
 * Note: non-agency (tenant) admins always have a site auto-selected by
 * `InitializeTenancyForAdmin`, so this guard only ever redirects an agency
 * admin who has not picked a site yet.
 */
class RequireActiveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! tenancy()->initialized) {
            return redirect()
                ->route('admin.tenants.index')
                ->with('error', 'Bu sayfa siteye özeldir. Lütfen önce bir site seçin.');
        }

        return $next($request);
    }
}
