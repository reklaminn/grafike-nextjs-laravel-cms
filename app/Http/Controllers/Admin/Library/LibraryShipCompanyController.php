<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Models\Library\LibraryShipCompany;
use App\Modules\Tours\Models\Library\LibraryShipCompanyTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Central kütüphane — gemi firması master CRUD (super-admin).
 */
class LibraryShipCompanyController extends Controller
{
    public function index(Request $request): View
    {
        $query = LibraryShipCompany::query()->with('translations')->withCount('ships');

        if ($search = $request->string('q')->toString()) {
            $query->where(fn ($q) => $q->where('slug', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
        }

        $companies = $query->ordered()->paginate(30)->withQueryString();

        return view('admin.library.ship-companies.index', [
            'companies' => $companies,
            'filters'   => $request->only('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.library.ship-companies.form', [
            'company'      => new LibraryShipCompany(['is_active' => true]),
            'languages'    => Language::active()->orderBy('sort_order')->get(),
            'translations' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request, null);

        $company = DB::transaction(function () use ($data) {
            $company = LibraryShipCompany::create($this->attrs($data));
            $this->syncTranslations($company, $data['translations'] ?? []);

            return $company;
        });

        return redirect()
            ->route('admin.library.ship-companies.edit', $company)
            ->with('success', 'Kütüphane firması oluşturuldu.');
    }

    public function edit(LibraryShipCompany $shipCompany): View
    {
        return view('admin.library.ship-companies.form', [
            'company'      => $shipCompany,
            'languages'    => Language::active()->orderBy('sort_order')->get(),
            'translations' => $shipCompany->translations->keyBy('language_id'),
        ]);
    }

    public function update(Request $request, LibraryShipCompany $shipCompany): RedirectResponse
    {
        $data = $this->validateData($request, $shipCompany->id);

        DB::transaction(function () use ($data, $shipCompany) {
            $shipCompany->update($this->attrs($data));
            $this->syncTranslations($shipCompany, $data['translations'] ?? []);
        });

        return redirect()
            ->route('admin.library.ship-companies.edit', $shipCompany)
            ->with('success', 'Kütüphane firması güncellendi.');
    }

    public function destroy(LibraryShipCompany $shipCompany): RedirectResponse
    {
        $shipCompany->delete();

        return redirect()
            ->route('admin.library.ship-companies.index')
            ->with('success', 'Kütüphane firması silindi.');
    }

    private function validateData(Request $request, ?int $id): array
    {
        return $request->validate([
            'slug'              => ['required', 'string', 'max:80', Rule::unique('library_ship_companies', 'slug')->ignore($id)],
            'name'              => ['required', 'string', 'max:200'],
            'company_type'      => ['nullable', 'string', 'max:50'],
            'operator'          => ['nullable', 'string', 'max:200'],
            'founded_year'      => ['nullable', 'integer', 'min:1800', 'max:2100'],
            'headquarters'      => ['nullable', 'string', 'max:200'],
            'website'           => ['nullable', 'string', 'max:500'],
            'logo_url'          => ['nullable', 'string', 'max:500'],
            'uses_cabin_groups' => ['nullable', 'boolean'],
            'is_active'         => ['nullable', 'boolean'],
            'sort_order'        => ['nullable', 'integer'],
            'translations'                      => ['nullable', 'array'],
            'translations.*.language_id'        => ['required', 'integer'],
            'translations.*.description'        => ['nullable', 'string'],
            'translations.*.meta_title'         => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description'   => ['nullable', 'string'],
        ]);
    }

    private function attrs(array $data): array
    {
        return [
            'slug'              => $data['slug'],
            'name'              => $data['name'],
            'company_type'      => $data['company_type'] ?? null,
            'operator'          => $data['operator'] ?? null,
            'founded_year'      => $data['founded_year'] ?? null,
            'headquarters'      => $data['headquarters'] ?? null,
            'website'           => $data['website'] ?? null,
            'logo_url'          => $data['logo_url'] ?? null,
            'uses_cabin_groups' => (bool) ($data['uses_cabin_groups'] ?? false),
            'is_active'         => (bool) ($data['is_active'] ?? false),
            'sort_order'        => (int) ($data['sort_order'] ?? 0),
        ];
    }

    private function syncTranslations(LibraryShipCompany $company, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            LibraryShipCompanyTranslation::updateOrCreate(
                ['library_ship_company_id' => $company->id, 'language_id' => $languageId],
                [
                    'description'      => $entry['description'] ?? null,
                    'meta_title'       => $entry['meta_title'] ?? null,
                    'meta_description' => $entry['meta_description'] ?? null,
                ]
            );
        }
    }
}
