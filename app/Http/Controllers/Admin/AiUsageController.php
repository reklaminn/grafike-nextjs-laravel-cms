<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\AiUsageReporter;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Per-tenant AI usage detail screen. Shows KPI tiles, 30-day trend chart,
 * feature/provider breakdowns, top expensive calls, and the recent
 * activity timeline.
 *
 * Mounted at /admin/tenants/{tenant}/ai-usage so it sits naturally
 * inside the existing tenant detail navigation; agency admin or tenant
 * admin can both view (read-only).
 */
class AiUsageController extends Controller
{
    public function show(Tenant $tenant, AiUsageReporter $reporter, AiQuotaService $quotaService)
    {
        $this->authorizeTenantAccess($tenant);

        try {
            $totals     = $reporter->totalsForCurrentMonth($tenant);
            $dailyTrend = $reporter->dailyTrend($tenant, days: 30);
            $features   = $reporter->featureBreakdown($tenant);
            $providers  = $reporter->providerBreakdown($tenant);
            $topCalls   = $reporter->topExpensiveCalls($tenant, limit: 10);
            $recent     = $reporter->recentCalls($tenant, limit: 25);
            $plan       = $quotaService->planFor($tenant);
        } catch (Throwable $e) {
            report($e);
            // Soft-fail so the page still renders if the central DB happens
            // to be transiently unavailable on a fresh install.
            $totals = $dailyTrend = $features = $providers = [];
            $topCalls = $recent = [];
            $plan     = ['name' => $tenant->aiPlan(), 'label' => '?', 'monthly_requests' => null, 'monthly_tokens' => null, 'monthly_cost_usd' => null];
        }

        return view('admin.tenants.ai-usage', compact(
            'tenant', 'totals', 'dailyTrend', 'features', 'providers',
            'topCalls', 'recent', 'plan'
        ));
    }

    private function authorizeTenantAccess(Tenant $tenant): void
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin?->canAccessTenant($tenant), 403);
    }
}
