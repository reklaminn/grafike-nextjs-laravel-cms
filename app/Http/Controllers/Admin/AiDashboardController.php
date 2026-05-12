<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Ai\AiUsageReporter;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Agency-wide AI usage dashboard (FAZ 3.7). Aggregates across all
 * tenants, surfaces top spenders, fallback-storm signals, and global
 * cost projection.
 *
 * Mounted at /admin/ai-dashboard. Agency admin only.
 */
class AiDashboardController extends Controller
{
    public function index(AiUsageReporter $reporter)
    {
        $this->authorizeAgencyAdmin();

        try {
            $totals     = $reporter->totalsForCurrentMonth(null);          // agency-wide
            $dailyTrend = $reporter->dailyTrend(null, days: 30);
            $features   = $reporter->featureBreakdown(null);
            $providers  = $reporter->providerBreakdown(null);
            $topTenants = $reporter->topTenants(limit: 10);
            $recent     = $reporter->recentCalls(null, limit: 30);
        } catch (Throwable $e) {
            report($e);
            $totals = $dailyTrend = $features = $providers = $topTenants = $recent = [];
        }

        // Resolve tenant names for the top-tenants table — keep one query.
        $tenantNames = [];
        if (! empty($topTenants)) {
            $ids = array_column($topTenants, 'tenant_id');
            $tenantNames = Tenant::query()
                ->whereIn('id', $ids)
                ->get()
                ->keyBy(fn ($t) => (string) $t->getKey())
                ->map(fn ($t) => $t->name ?? $t->id)
                ->all();
        }

        return view('admin.ai-dashboard.index', compact(
            'totals', 'dailyTrend', 'features', 'providers',
            'topTenants', 'tenantNames', 'recent'
        ));
    }

    private function authorizeAgencyAdmin(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isAgencyAdmin(), 403);
    }
}
