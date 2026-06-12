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

        // Çift kaydet: "Kaydet ve yeni grup" → yeni form; default → düzenle
        if ($request->input('save_action') === 'new') {
            return redirect()
                ->route('admin.tours.price-groups.create', $tour)
                ->with('success', 'Fiyat grubu oluşturuldu. Yeni grup ekleyebilirsiniz.');
        }

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

        if ($request->input('save_action') === 'new') {
            return redirect()
                ->route('admin.tours.price-groups.create', $tour)
                ->with('success', 'Fiyat grubu güncellendi. Yeni grup ekleyebilirsiniz.');
        }

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
        $group->load(['translations', 'cabinPrices.cabin.translations', 'dates']);

        $languages    = Language::active()->orderBy('sort_order')->get();
        $translations = $group->translations->keyBy('language_id');

        // Kabin dropdown seçenekleri ("Kabin Seçiniz") — bu turun gemisinin
        // kabinleri.  Non-cruise turda boş; oda satırı serbest metin (room_label).
        $cabinOptions = ($tour->ship?->cabins ?? collect())->map(fn ($c) => [
            'id'    => $c->id,
            'label' => $c->translations->first()?->name ?? $c->code ?? ('Kabin #' . $c->id),
            'deck'  => $c->deck_name,
        ])->values();

        // Oda satırları — Alpine x-data init verisi (JSON).
        //   Edit  → mevcut cabinPrices'tan
        //   Create→ cruise ise ship.cabins'ten ön-doldur, değilse 1 boş satır
        $roomRows = $this->buildRoomRows($tour, $group);

        $selectedDateIds = $group->dates->pluck('id')->toArray();

        return view('tours::admin.price-groups.form', [
            'tour'               => $tour,
            'group'              => $group,
            'languages'          => $languages,
            'translations'       => $translations,
            'cabinOptions'       => $cabinOptions,
            'roomRows'           => $roomRows,
            'selectedDateIds'    => $selectedDateIds,
            'calculationMethods' => CalculationMethod::cases(),
        ]);
    }

    /**
     * Form'daki oda satırlarının başlangıç verisi (Alpine JSON).
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildRoomRows(Tour $tour, TourPriceGroup $group): array
    {
        // Edit: mevcut fiyat satırları
        if ($group->exists && $group->cabinPrices->isNotEmpty()) {
            return $group->cabinPrices->sortBy('sort_order')->values()->map(fn ($cp) => [
                'id'                 => $cp->id,
                'room_label'         => $cp->room_label
                    ?? $cp->cabin?->translations->first()?->name
                    ?? $cp->cabin?->code,
                'deck_label'         => $cp->deck_label ?? $cp->cabin?->deck_name,
                'cabin_id'           => $cp->cabin_id,
                'price_definition'   => $cp->price_definition,
                'calculation_method' => $cp->calculation_method?->value ?? 'standart_doublex2',
                'currency'           => $cp->currency ?? $tour->currency,
                'price_single'       => $cp->price_single,
                'price_double'       => $cp->price_double,
                'price_triple'       => $cp->price_triple,
                'price_quad'         => $cp->price_quad,
                'price_child'        => $cp->price_child,
                'price_baby'         => $cp->price_baby,
                'child_age_min'      => $cp->child_age_min ?? 2,
                'child_age_max'      => $cp->child_age_max ?? 11,
                'baby_age_min'       => $cp->baby_age_min ?? 0,
                'baby_age_max'       => $cp->baby_age_max ?? 1,
                'is_active'          => (bool) $cp->is_active,
            ])->all();
        }

        // Create + cruise: gemi kabinlerinden ön-doldur (kolaylık)
        $cabins = $tour->ship?->cabins ?? collect();
        if ($cabins->isNotEmpty()) {
            return $cabins->map(fn ($c) => $this->blankRow($tour, [
                'room_label' => $c->translations->first()?->name ?? $c->code,
                'deck_label' => $c->deck_name,
                'cabin_id'   => $c->id,
            ]))->all();
        }

        // Create + non-cruise: tek boş satır
        return [$this->blankRow($tour, ['room_label' => 'Standart Oda'])];
    }

    /** @return array<string, mixed> */
    private function blankRow(Tour $tour, array $overrides = []): array
    {
        return array_merge([
            'id'                 => null,
            'room_label'         => '',
            'deck_label'         => null,
            'cabin_id'           => null,
            'price_definition'   => null,
            'calculation_method' => 'standart_doublex2',
            'currency'           => $tour->currency,
            'price_single'       => null,
            'price_double'       => null,
            'price_triple'       => null,
            'price_quad'         => null,
            'price_child'        => null,
            'price_baby'         => null,
            'child_age_min'      => 2,
            'child_age_max'      => 11,
            'baby_age_min'       => 0,
            'baby_age_max'       => 1,
            'is_active'          => true,
        ], $overrides);
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
     * Oda satırları sync — tekrarlı (repeatable) editör.
     *
     * Her satır kendi opsiyonel `id`'sini taşır:
     *   - id mevcut + gruba ait → güncelle
     *   - id yok → yeni oluştur
     * Submit'te olmayan eski satırlar silinir (booking referansı varsa korunur).
     *
     * sort_order satır sırasına göre atanır (admin'in dizdiği gibi).
     */
    private function syncCabinPrices(TourPriceGroup $group, array $rows): void
    {
        $keptIds = [];

        foreach (array_values($rows) as $i => $row) {
            // Tamamen boş satırı atla (room_label + tüm fiyatlar boş)
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $attrs = [
                'cabin_id'           => $this->nullableInt($row['cabin_id'] ?? null),
                'room_label'         => trim((string) ($row['room_label'] ?? '')) ?: null,
                'deck_label'         => trim((string) ($row['deck_label'] ?? '')) ?: null,
                'price_definition'   => $row['price_definition'] ?? null,
                'calculation_method' => $row['calculation_method'] ?? 'standart_doublex2',
                'currency'           => isset($row['currency']) && $row['currency'] !== ''
                    ? strtoupper($row['currency'])
                    : null,
                'price_single'       => $this->nullableInt($row['price_single']  ?? null),
                'price_double'       => $this->nullableInt($row['price_double']  ?? null),
                'price_triple'       => $this->nullableInt($row['price_triple']  ?? null),
                'price_quad'         => $this->nullableInt($row['price_quad']    ?? null),
                'price_child'        => $this->nullableInt($row['price_child']   ?? null),
                'price_baby'         => $this->nullableInt($row['price_baby']    ?? null),
                'child_age_min'      => $this->nullableInt($row['child_age_min'] ?? null),
                'child_age_max'      => $this->nullableInt($row['child_age_max'] ?? null),
                'baby_age_min'       => $this->nullableInt($row['baby_age_min']  ?? null),
                'baby_age_max'       => $this->nullableInt($row['baby_age_max']  ?? null),
                'sort_order'         => $i,
                'is_active'          => (bool) ($row['is_active'] ?? true),
            ];

            $id = $this->nullableInt($row['id'] ?? null);

            if ($id !== null) {
                $existing = $group->cabinPrices()->whereKey($id)->first();
                if ($existing) {
                    $existing->update($attrs);
                    $keptIds[] = $existing->id;
                    continue;
                }
            }

            $created = $group->cabinPrices()->create($attrs);
            $keptIds[] = $created->id;
        }

        // Submit'te olmayan eski satırları sil (booking referansı yoksa)
        $stale = $group->cabinPrices()->whereNotIn('id', $keptIds ?: [0])->get();
        foreach ($stale as $cp) {
            $referenced = \DB::table('booking_passengers')
                ->where('tour_cabin_price_id', $cp->id)
                ->exists();
            if (! $referenced) {
                $cp->delete();
            }
        }
    }

    /**
     * Satır tamamen boş mu? (room_label yok + hiçbir fiyat girilmemiş + kabin yok)
     */
    private function rowIsEmpty(array $row): bool
    {
        $hasLabel = trim((string) ($row['room_label'] ?? '')) !== '';
        $hasCabin = ($row['cabin_id'] ?? '') !== '';
        $hasPrice = false;
        foreach (['price_single', 'price_double', 'price_triple', 'price_quad', 'price_child', 'price_baby'] as $k) {
            if (($row[$k] ?? '') !== '') {
                $hasPrice = true;
                break;
            }
        }

        return ! $hasLabel && ! $hasCabin && ! $hasPrice;
    }

    private function nullableInt(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        return (int) $v;
    }
}
