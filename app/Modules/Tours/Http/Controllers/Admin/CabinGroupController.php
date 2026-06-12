<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Http\Requests\Admin\StoreCabinGroupRequest;
use App\Modules\Tours\Models\Cabin;
use App\Modules\Tours\Models\CabinGroup;
use App\Modules\Tours\Models\CabinGroupTranslation;
use App\Modules\Tours\Models\ShipCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * CabinGroup CRUD — şirket-bazında toplu cabin seçim bundle'ı.
 *
 * Tipik akış (kullanıcı tanımı):
 *   1. Admin → Gemi Firması (Azamara) → "Yeni Kabin Grubu" → "Paket Tur Kabinleri"
 *   2. Azamara'nın gemilerindeki cabin'lerden istediklerini bu gruba ekler
 *   3. Tour pricing setup'ında grup seçer → Tour.ship ile kesişim alır
 *
 * Sadece ShipCompany.uses_cabin_groups=true firmalar için anlamlı, ama
 * UI'da tüm firmalar listede gözükür (toggle kapalıysa kullanılmaz).
 */
class CabinGroupController extends Controller
{
    public function index(Request $request): View
    {
        $defaultLanguage = Language::active()->orderBy('sort_order')->first();

        $query = CabinGroup::query()
            ->with(['company', 'translations'])
            ->withCount('cabins');

        if ($companyId = $request->integer('company')) {
            $query->where('ship_company_id', $companyId);
        }

        $groups    = $query->orderBy('sort_order')->paginate(30)->withQueryString();
        $companies = ShipCompany::query()->ordered()->get(['id', 'name', 'uses_cabin_groups']);

        return view('tours::admin.cabin-groups.index', [
            'groups'          => $groups,
            'companies'       => $companies,
            'defaultLanguage' => $defaultLanguage,
            'filters'         => $request->only(['company']),
        ]);
    }

    public function create(Request $request): View
    {
        $group = new CabinGroup(['is_active' => true]);
        if ($companyId = $request->integer('company')) {
            $group->ship_company_id = $companyId;
        }

        return $this->form($group);
    }

    public function store(StoreCabinGroupRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $group = DB::transaction(function () use ($data) {
            $group = CabinGroup::create([
                'ship_company_id' => (int) $data['ship_company_id'],
                'slug'            => $data['slug'],
                'sort_order'      => (int) ($data['sort_order'] ?? 0),
                'is_active'       => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($group, $data['translations']);
            $this->syncCabins($group, $data['cabin_ids'] ?? []);

            return $group;
        });

        return redirect()
            ->route('admin.cabin-groups.edit', $group)
            ->with('success', 'Kabin grubu oluşturuldu.');
    }

    public function edit(CabinGroup $cabinGroup): View
    {
        return $this->form($cabinGroup);
    }

    public function update(StoreCabinGroupRequest $request, CabinGroup $cabinGroup): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $cabinGroup) {
            $cabinGroup->update([
                'ship_company_id' => (int) $data['ship_company_id'],
                'slug'            => $data['slug'],
                'sort_order'      => (int) ($data['sort_order'] ?? 0),
                'is_active'       => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($cabinGroup, $data['translations']);
            $this->syncCabins($cabinGroup, $data['cabin_ids'] ?? []);
        });

        return redirect()
            ->route('admin.cabin-groups.edit', $cabinGroup)
            ->with('success', 'Kabin grubu güncellendi.');
    }

    public function destroy(CabinGroup $cabinGroup): RedirectResponse
    {
        $cabinGroup->delete();

        return redirect()
            ->route('admin.cabin-groups.index')
            ->with('success', 'Kabin grubu silindi.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function form(CabinGroup $group): View
    {
        $group->load(['translations', 'cabins.ship', 'cabins.category.translations']);

        $languages    = Language::active()->orderBy('sort_order')->get();
        $companies    = ShipCompany::query()->ordered()->get(['id', 'name', 'uses_cabin_groups']);
        $translations = $group->translations->keyBy('language_id');

        // Eligible cabin pool — firmanın gemilerindeki cabin'ler
        $eligibleCabins = collect();
        if ($group->ship_company_id) {
            $eligibleCabins = Cabin::query()
                ->whereHas('ship', fn ($q) => $q->where('ship_company_id', $group->ship_company_id))
                ->with(['ship', 'category.translations', 'translations'])
                ->ordered()
                ->get();
        }
        $selectedCabins = $group->cabins()->pluck('cabins.id')->toArray();

        return view('tours::admin.cabin-groups.form', compact(
            'group', 'languages', 'companies', 'translations',
            'eligibleCabins', 'selectedCabins'
        ));
    }

    private function syncTranslations(CabinGroup $group, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            CabinGroupTranslation::updateOrCreate(
                ['cabin_group_id' => $group->id, 'language_id' => $languageId],
                [
                    'name'        => trim((string) ($entry['name'] ?? '')),
                    'description' => $entry['description'] ?? null,
                ]
            );
        }
    }

    private function syncCabins(CabinGroup $group, array $cabinIds): void
    {
        $payload = [];
        foreach (array_values($cabinIds) as $idx => $cabinId) {
            $payload[(int) $cabinId] = ['sort_order' => $idx];
        }
        $group->cabins()->sync($payload);
    }
}
