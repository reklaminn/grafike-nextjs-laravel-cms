<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiPlan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AiPlanController extends Controller
{
    public function index()
    {
        $this->authorizeAgencyAdmin();

        $plans = AiPlan::orderBy('sort_order')->get();

        // Her plan için kaç tenant kullanıyor (ai_settings.plan)
        $tenantCounts = [];
        foreach (Tenant::all() as $tenant) {
            $plan = $tenant->aiPlan();
            $tenantCounts[$plan] = ($tenantCounts[$plan] ?? 0) + 1;
        }

        return view('admin.ai-plans.index', compact('plans', 'tenantCounts'));
    }

    public function create()
    {
        $this->authorizeAgencyAdmin();

        return view('admin.ai-plans.form', ['plan' => null]);
    }

    public function store(Request $request)
    {
        $this->authorizeAgencyAdmin();

        $validated = $this->validatePlan($request);

        if ($validated['is_default']) {
            AiPlan::where('is_default', true)->update(['is_default' => false]);
        }

        AiPlan::create($validated);

        return redirect()->route('admin.ai-plans.index')
            ->with('success', "'{$validated['label']}' AI planı oluşturuldu.");
    }

    public function edit(AiPlan $aiPlan)
    {
        $this->authorizeAgencyAdmin();

        return view('admin.ai-plans.form', ['plan' => $aiPlan]);
    }

    public function update(Request $request, AiPlan $aiPlan)
    {
        $this->authorizeAgencyAdmin();

        $validated = $this->validatePlan($request, $aiPlan->key);

        if ($validated['is_default'] && ! $aiPlan->is_default) {
            AiPlan::where('is_default', true)->update(['is_default' => false]);
        }

        $aiPlan->update($validated);

        return redirect()->route('admin.ai-plans.index')
            ->with('success', "'{$aiPlan->label}' AI planı güncellendi.");
    }

    public function destroy(AiPlan $aiPlan)
    {
        $this->authorizeAgencyAdmin();

        $inUse = Tenant::all()->filter(fn ($t) => $t->aiPlan() === $aiPlan->key)->count();
        if ($inUse > 0) {
            return back()->with('error', "Bu planı {$inUse} tenant kullanıyor. Önce tenant'ları başka plana taşıyın.");
        }

        $aiPlan->delete();

        return redirect()->route('admin.ai-plans.index')
            ->with('success', "'{$aiPlan->label}' AI planı silindi.");
    }

    // ─── helpers ─────────────────────────────────────────────────────────────

    private function validatePlan(Request $request, ?string $currentKey = null): array
    {
        $validated = $request->validate([
            'key'               => ['required', 'string', 'max:50', 'alpha_dash',
                                     Rule::unique('central.ai_plans', 'key')->ignore($currentKey, 'key')],
            'label'             => 'required|string|max:100',
            'monthly_requests'  => 'nullable|integer|min:1',
            'monthly_tokens'    => 'nullable|integer|min:1',
            'monthly_cost_usd'  => 'nullable|numeric|min:0',
            'sort_order'        => 'required|integer|min:0',
            'is_default'        => 'boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');

        return $validated;
    }

    private function authorizeAgencyAdmin(): void
    {
        abort_unless(
            auth('admin')->user()?->isAgencyAdmin(),
            403, 'Bu işlem için ajans yöneticisi yetkisi gereklidir.'
        );
    }
}
