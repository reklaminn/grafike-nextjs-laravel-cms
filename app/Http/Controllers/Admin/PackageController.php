<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiPlan;
use App\Models\Package;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    public function index()
    {
        $this->authorizeAgencyAdmin();

        $packages = Package::orderBy('sort_order')->get();

        // Her paket için kaç tenant kullanıyor
        $tenantCounts = Tenant::all()
            ->groupBy(fn ($t) => $t->package())
            ->map->count();

        $aiPlans = array_keys(AiPlan::allKeyed());

        return view('admin.packages.index', compact('packages', 'tenantCounts', 'aiPlans'));
    }

    public function create()
    {
        $this->authorizeAgencyAdmin();
        $aiPlans = array_keys(AiPlan::allKeyed());

        return view('admin.packages.form', [
            'package' => null,
            'aiPlans' => $aiPlans,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAgencyAdmin();

        $validated = $this->validatePackage($request);

        if ($validated['is_default']) {
            Package::where('is_default', true)->update(['is_default' => false]);
        }

        Package::create($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', "'{$validated['label']}' paketi oluşturuldu.");
    }

    public function edit(Package $package)
    {
        $this->authorizeAgencyAdmin();
        $aiPlans = array_keys(AiPlan::allKeyed());

        return view('admin.packages.form', compact('package', 'aiPlans'));
    }

    public function update(Request $request, Package $package)
    {
        $this->authorizeAgencyAdmin();

        $validated = $this->validatePackage($request, $package->key);

        if ($validated['is_default'] && ! $package->is_default) {
            Package::where('is_default', true)->update(['is_default' => false]);
        }

        $package->update($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', "'{$package->label}' paketi güncellendi.");
    }

    public function destroy(Package $package)
    {
        $this->authorizeAgencyAdmin();

        $tenantCount = Tenant::all()->filter(fn ($t) => $t->package() === $package->key)->count();
        if ($tenantCount > 0) {
            return back()->with('error', "Bu paketi {$tenantCount} tenant kullanıyor. Önce tenant'ları başka pakete taşıyın.");
        }

        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('success', "'{$package->label}' paketi silindi.");
    }

    // ─── helpers ─────────────────────────────────────────────────────────────

    private function validatePackage(Request $request, ?string $currentKey = null): array
    {
        $aiPlans = array_keys(AiPlan::allKeyed());

        $rules = [
            'key'                  => ['required', 'string', 'max:50', 'alpha_dash',
                                        Rule::unique('central.packages', 'key')->ignore($currentKey, 'key')],
            'label'                => 'required|string|max:100',
            'ai_plan'              => ['required', Rule::in(array_keys(AiPlan::allKeyed()))],
            'max_users'            => 'nullable|integer|min:1',
            'max_storage_mb'       => 'nullable|integer|min:1',
            'max_requests_per_day' => 'nullable|integer|min:1',
            'modules'              => 'nullable|array',
            'modules.*'            => 'string|max:50',
            'sort_order'           => 'required|integer|min:0',
            'is_default'           => 'boolean',
        ];

        $validated = $request->validate($rules);
        $validated['is_default'] = $request->boolean('is_default');
        $validated['modules']    = $request->input('modules', []);

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
