<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteTemplate;
use App\Models\Tenant;
use App\Services\Tenants\IndustryTemplateApplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Gallery + apply flow for industry-specific SiteTemplates (FAZ 4.3).
 *
 * Lives on the tenant detail screen: agency admin picks an industry,
 * sees the available templates for that sector, then "Apply" seeds the
 * tenant DB with that template's pages, menus, and site settings.
 *
 * Destructive by default — applying replaces existing pages with same
 * slug. The UI surfaces a confirm dialog before submitting.
 */
class IndustryTemplateController extends Controller
{
    /**
     * JSON list endpoint — gallery uses it to render cards client-side.
     */
    public function index(Request $request)
    {
        $this->authorizeAgencyAdmin();

        $industry = $request->input('industry');

        $templates = SiteTemplate::query()
            ->active()
            ->when($industry, fn ($q) => $q->where('industry', $industry))
            ->whereNotNull('industry')
            ->orderBy('industry')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'industry', 'description', 'summary', 'preview_image']);

        return response()->json([
            'industries' => SiteTemplate::INDUSTRIES,
            'templates'  => $templates->map(fn ($t) => [
                'id'             => $t->id,
                'name'           => $t->name,
                'slug'           => $t->slug,
                'industry'       => $t->industry,
                'industry_label' => $t->industryLabel(),
                'description'    => $t->description,
                'summary'        => $t->summary,
                'preview_image'  => $t->preview_image,
            ]),
        ]);
    }

    /**
     * Apply a chosen SiteTemplate to the given tenant.
     */
    public function apply(
        Request $request,
        Tenant $tenant,
        IndustryTemplateApplier $applier,
    ): RedirectResponse {
        $this->authorizeAgencyAdmin();

        $validated = $request->validate([
            'site_template_id' => ['required', 'integer', Rule::exists('central.site_templates', 'id')],
            'mode'             => ['nullable', Rule::in(['replace', 'merge'])],
        ]);

        $template = SiteTemplate::query()->findOrFail($validated['site_template_id']);
        if (empty($template->industry)) {
            return back()->with('error', 'Bu şablon bir sektör paketi değil.');
        }

        try {
            $summary = $applier->apply(
                tenant: $tenant,
                template: $template,
                options: ['replace' => ($validated['mode'] ?? 'replace') === 'replace'],
            );
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Şablon uygulanamadı: '.$e->getMessage());
        }

        $msg = sprintf(
            '«%s» şablonu uygulandı: %d sayfa, %d menü, %d ayar yazıldı.',
            $template->name,
            $summary['pages'],
            $summary['menus'],
            $summary['settings'],
        );

        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('success', $msg);
    }

    private function authorizeAgencyAdmin(): void
    {
        abort_unless(
            Auth::guard('admin')->user()?->isAgencyAdmin(),
            403,
            'Sektör şablonu uygulamak için ajans yöneticisi olmanız gerekiyor.'
        );
    }
}
