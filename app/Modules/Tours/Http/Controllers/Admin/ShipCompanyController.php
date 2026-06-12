<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Http\Requests\Admin\StoreShipCompanyRequest;
use App\Modules\Tours\Models\ShipCompany;
use App\Modules\Tours\Models\ShipCompanyTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ShipCompany CRUD — gemi firmaları.
 *
 * Brand name ana tabloda (anlamlı yer çünkü pazarlama materyalinde
 * standart form), description + meta translation tablosunda.
 *
 * uses_cabin_groups toggle: büyük filolar için cabin selection bundle
 * pattern'ini aktif eder.  CabinGroup CRUD bu firmaların altında.
 *
 * Logo upload Spatie media-library 'logo' collection (singleFile).
 */
class ShipCompanyController extends Controller
{
    public function index(): View
    {
        $defaultLanguage = Language::active()->orderBy('sort_order')->first();

        $companies = ShipCompany::query()
            ->with(['translations', 'media'])
            ->withCount(['ships', 'cabinGroups'])
            ->ordered()
            ->paginate(30)
            ->withQueryString();

        return view('tours::admin.ship-companies.index', compact('companies', 'defaultLanguage'));
    }

    public function create(): View
    {
        $languages = Language::active()->orderBy('sort_order')->get();

        return view('tours::admin.ship-companies.form', [
            'company'      => new ShipCompany(['is_active' => true]),
            'languages'    => $languages,
            'translations' => collect(),
        ]);
    }

    public function store(StoreShipCompanyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $company = DB::transaction(function () use ($request, $data) {
            $company = ShipCompany::create([
                'slug'              => $data['slug'],
                'name'              => $data['name'],
                'company_type'      => $data['company_type'] ?? null,
                'operator'          => $data['operator']     ?? null,
                'founded_year'      => $data['founded_year'] ?? null,
                'headquarters'      => $data['headquarters'] ?? null,
                'website'           => $data['website']      ?? null,
                'uses_cabin_groups' => (bool) ($data['uses_cabin_groups'] ?? false),
                'sort_order'        => (int) ($data['sort_order'] ?? 0),
                'is_active'         => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($company, $data['translations']);

            if ($request->hasFile('logo')) {
                $company->addMediaFromRequest('logo')->toMediaCollection('logo');
            }

            return $company;
        });

        return redirect()
            ->route('admin.ship-companies.edit', $company)
            ->with('success', 'Gemi firması oluşturuldu.');
    }

    public function edit(ShipCompany $shipCompany): View
    {
        $languages    = Language::active()->orderBy('sort_order')->get();
        $translations = $shipCompany->translations->keyBy('language_id');

        return view('tours::admin.ship-companies.form', [
            'company'      => $shipCompany,
            'languages'    => $languages,
            'translations' => $translations,
        ]);
    }

    public function update(StoreShipCompanyRequest $request, ShipCompany $shipCompany): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data, $shipCompany) {
            $shipCompany->update([
                'slug'              => $data['slug'],
                'name'              => $data['name'],
                'company_type'      => $data['company_type'] ?? null,
                'operator'          => $data['operator']     ?? null,
                'founded_year'      => $data['founded_year'] ?? null,
                'headquarters'      => $data['headquarters'] ?? null,
                'website'           => $data['website']      ?? null,
                'uses_cabin_groups' => (bool) ($data['uses_cabin_groups'] ?? false),
                'sort_order'        => (int) ($data['sort_order'] ?? 0),
                'is_active'         => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($shipCompany, $data['translations']);

            if ($request->hasFile('logo')) {
                $shipCompany->clearMediaCollection('logo');
                $shipCompany->addMediaFromRequest('logo')->toMediaCollection('logo');
            }
        });

        return redirect()
            ->route('admin.ship-companies.edit', $shipCompany)
            ->with('success', 'Gemi firması güncellendi.');
    }

    public function destroy(ShipCompany $shipCompany): RedirectResponse
    {
        if ($shipCompany->ships()->exists()) {
            return back()->with('error', 'Bu firmaya bağlı gemiler var, önce gemileri başka firmaya taşıyın.');
        }

        $shipCompany->delete();

        return redirect()
            ->route('admin.ship-companies.index')
            ->with('success', 'Gemi firması silindi.');
    }

    private function syncTranslations(ShipCompany $company, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            ShipCompanyTranslation::updateOrCreate(
                ['ship_company_id' => $company->id, 'language_id' => $languageId],
                [
                    'description'      => $entry['description']      ?? null,
                    'meta_title'       => $entry['meta_title']       ?? null,
                    'meta_description' => $entry['meta_description'] ?? null,
                ]
            );
        }
    }
}
