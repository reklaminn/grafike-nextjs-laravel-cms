<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Enums\CalculationMethod;
use App\Modules\Tours\Http\Requests\Admin\StoreTourPriceGroupRequest;
use App\Modules\Tours\Models\Tour;
use App\Modules\Tours\Models\TourCabinPrice;
use App\Modules\Tours\Models\TourPriceGroup;
use App\Modules\Tours\Models\TourPriceGroupTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Tour-nested CRUD for TourPriceGroup (eski sistem Tab 4'ün karşılığı).
 *
 * Bir tur birden çok PriceGroup'a sahip olabilir — her grup farklı bir
 * pricing scenario (örn. "Yaz 2026 Standart", "Erken Rezervasyon -%20").
 *
 * Form 4 bölüm:
 *   1. Identity      — min_persons, adult_priority, capacity_quota, campaign_text
 *   2. Translations  — name + description per language
 *   3. Date matrix   — Tour'a ait TourDate'lere m2m atama
 *   4. Cabin matrix  — Cruise için ship.cabins her biri için price row,
 *                      non-cruise için tek generic (cabin_id NULL) satır
 */
class TourPriceGroupController extends Controller
{
    public function index(Tour $tour): View
    {
        $defaultLanguage = Language::active()->orderBy('sort_order')->first();

        $groups = $tour->priceGroups()
            ->with(['translations'])
            ->withCount(['cabinPrices', 'dates'])
            ->ordered()
            ->paginate(20);

        return view('tours::admin.price-groups.index', [
            'tour'            => $tour,
            'groups'          => $groups,
            'defaultLanguage' => $defaultLanguage,
        ]);
    }

    public function create(Tour $tour): View
    {
        return $this->renderForm($tour, new TourPriceGroup([
            'tour_id'         => $tour->id,
            'is_active'       => true,
            'adult_priority'  => true,
        ]));
    }

    public function store(StoreTourPriceGroupRequest $request, Tour $tour): RedirectResponse
    {
        $data = $request->validated();

        $group = DB::transaction(function () use ($tour, $data) {
            $group = $tour->priceGroups()->create([
                'min_persons'    => $data['min_persons']    ?? null,
                'adult_priority' => (bool) ($data['adult_priority'] ?? false),
                'capacity_quota' => $data['capacity_quota'] ?? null,
                'campaign_text'  => $data['campaign_text']  ?? null,
                'sort_order'     => (int) ($data['sort_order'] ?? 0),
                'is_active'      => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($group, $data['translations']);
            $this->syncDates($group, $data['date_ids'] ?? []);
            $this->syncCabinPrices($group, $data['cabin_prices'] ?? []);

            return $group;
        });

        return redirect()
            ->route('admin.tours.price-groups.edit', ['tour' => $tour, 'price_group' => $group])
            ->with('success', 'Fiyat grubu oluşturuldu.');
    }

    public function edit(Tour $tour, TourPriceGroup $priceGroup): View
    {
        return $this->renderForm($tour, $priceGroup);
    }

    public function update(
        StoreTourPriceGroupRequest $request,
        Tour $tour,
        TourPriceGroup $priceGroup
    ): RedirectResponse {
        $data = $request->validated();

        DB::transaction(function () use ($data, $priceGroup) {
            $priceGroup->update([
                'min_persons'    => $data['min_persons']    ?? null,
                'adult_priority' => (bool) ($data['adult_priority'] ?? false),
                'capacity_quota' => $data['capacity_quota'] ?? null,
                'campaign_text'  => $data['campaign_text']  ?? null,
                'sort_order'     => (int) ($data['sort_order'] ?? 0),
                'is_active'      => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($priceGroup, $data['translations']);
            $this->syncDates($priceGroup, $data['date_ids'] ?? []);
            $this->syncCabinPrices($priceGroup, $data['cabin_prices'] ?? []);
        });

        return redirect()
            ->route('admin.tours.price-groups.edit', ['tour' => $tour, 'price_group' => $priceGroup])
            ->with('success', 'Fiyat grubu güncellendi.');
    }

    public function destroy(Tour $tour, TourPriceGroup $priceGroup): RedirectResponse
    {
        $priceGroup->delete(); // soft delete

        return redirect()
            ->route('admin.tours.price-groups.index', $tour)
            ->with('success', 'Fiyat grubu silindi.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function renderForm(Tour $tour, TourPriceGroup $group): View
    {
        $tour->load(['ship.cabins.translations', 'ship.cabins.category.translations', 'dates']);
        $group->load(['translations', 'cabinPrices.cabin', 'dates']);

        $languages    = Language::active()->orderBy('sort_order')->get();
        $translations = $group->translations->keyBy('language_id');

        // Cabin pool for this tour:
        // - Cruise (ship_id set): tour.ship.cabins
        // - Non-cruise: empty (1 generic row will be rendered manually)
        $cabins = $tour->ship ? $tour->ship->cabins : collect();

        // Existing cabin_prices indexed by cabin_id (NULL → '_generic')
        $existingPrices = $group->cabinPrices->keyBy(function ($cp) {
            return $cp->cabin_id ?? '_generic';
        });

        $selectedDateIds = $group->dates->pluck('id')->toArray();

        return view('tours::admin.price-groups.form', [
            'tour'             => $tour,
            'group'            => $group,
            'languages'        => $languages,
            'translations'     => $translations,
            'cabins'           => $cabins,
            'existingPrices'   => $existingPrices,
            'selectedDateIds'  => $selectedDateIds,
            'calculationMethods' => CalculationMethod::cases(),
        ]);
    }

    private function syncTranslations(TourPriceGroup $group, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            TourPriceGroupTranslation::updateOrCreate(
                ['tour_price_group_id' => $group->id, 'language_id' => $languageId],
                [
                    'name'        => trim((string) ($entry['name'] ?? '')),
                    'description' => $entry['description'] ?? null,
                ]
            );
        }
    }

    private function syncDates(TourPriceGroup $group, array $dateIds): void
    {
        $payload = [];
        foreach (array_values($dateIds) as $i => $id) {
            $payload[(int) $id] = ['sort_order' => $i];
        }
        $group->dates()->sync($payload);
    }

    /**
     * Matrix grid sync — her cabin_id için 1 TourCabinPrice row.
     *
     * cabin_id = null kabul edilir (non-cruise generic), ama formda
     * "_generic" sentinel'i geliyorsa onu NULL'a çeviriyoruz.
     *
     * Form'da yer almayan cabin'ler için mevcut row'ları silmiyoruz
     * (admin grid'i tam render eder, eksik cabin = uniabsent şart).
     */
    private function syncCabinPrices(TourPriceGroup $group, array $rows): void
    {
        $seenIds = [];

        foreach ($rows as $row) {
            $cabinId = $row['cabin_id'] ?? null;
            if (is_string($cabinId) && $cabinId === '_generic') {
                $cabinId = null;
            }
            $cabinId = $cabinId !== null ? (int) $cabinId : null;

            $cabinPrice = TourCabinPrice::updateOrCreate(
                [
                    'tour_price_group_id' => $group->id,
                    'cabin_id'            => $cabinId,
                ],
                [
                    'price_definition'    => $row['price_definition']    ?? null,
                    'calculation_method'  => $row['calculation_method'],
                    'currency'            => isset($row['currency']) && $row['currency'] !== ''
                        ? strtoupper($row['currency'])
                        : null,
                    'price_single'        => $this->nullableInt($row['price_single']  ?? null),
                    'price_double'        => $this->nullableInt($row['price_double']  ?? null),
                    'price_triple'        => $this->nullableInt($row['price_triple']  ?? null),
                    'price_quad'          => $this->nullableInt($row['price_quad']    ?? null),
                    'price_child'         => $this->nullableInt($row['price_child']   ?? null),
                    'price_baby'          => $this->nullableInt($row['price_baby']    ?? null),
                    'child_age_min'       => $this->nullableInt($row['child_age_min'] ?? null),
                    'child_age_max'       => $this->nullableInt($row['child_age_max'] ?? null),
                    'baby_age_min'        => $this->nullableInt($row['baby_age_min']  ?? null),
                    'baby_age_max'        => $this->nullableInt($row['baby_age_max']  ?? null),
                    'sort_order'          => (int) ($row['sort_order'] ?? 0),
                    'is_active'           => (bool) ($row['is_active'] ?? true),
                ]
            );

            $seenIds[] = $cabinPrice->id;
        }
    }

    private function nullableInt(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        return (int) $v;
    }
}
