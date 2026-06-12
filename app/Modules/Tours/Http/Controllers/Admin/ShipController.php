<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Http\Requests\Admin\StoreShipRequest;
use App\Modules\Tours\Models\Ship;
use App\Modules\Tours\Models\ShipCompany;
use App\Modules\Tours\Models\ShipTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Ship CRUD — tek geminin tüm teknik özelliği + medyası.
 *
 * Form: 3 mantıksal sekme (UI'da tab; verilerde tek POST):
 *   1. Identity:  marka, slug, IMO, bayrak, yıldız
 *   2. Specs:     yapım yılı, kapasite, mürettebat, tonaj, boyutlar, facilities
 *   3. Media:     cover + gallery + deck plans (Spatie media)
 *
 * Facilities: config/ship_facilities.php → 15 slug, JSON kolonunda saklı.
 */
class ShipController extends Controller
{
    public function index(Request $request): View
    {
        $defaultLanguage = Language::active()->orderBy('sort_order')->first();

        $query = Ship::query()
            ->with(['company', 'translations', 'media'])
            ->withCount('cabins');

        if ($companyId = $request->integer('company')) {
            $query->where('ship_company_id', $companyId);
        }
        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('imo_number', 'like', "%{$search}%");
            });
        }

        $ships     = $query->ordered()->paginate(30)->withQueryString();
        $companies = ShipCompany::query()->ordered()->get(['id', 'name']);

        return view('tours::admin.ships.index', [
            'ships'           => $ships,
            'companies'       => $companies,
            'defaultLanguage' => $defaultLanguage,
            'filters'         => $request->only(['company', 'q']),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Ship(['is_active' => true]));
    }

    public function store(StoreShipRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $ship = DB::transaction(function () use ($request, $data) {
            $ship = Ship::create($this->extractShipAttrs($data));

            $this->syncTranslations($ship, $data['translations']);
            $this->handleMedia($ship, $request);

            return $ship;
        });

        return redirect()
            ->route('admin.ships.edit', $ship)
            ->with('success', 'Gemi oluşturuldu.');
    }

    public function edit(Ship $ship): View
    {
        return $this->form($ship);
    }

    public function update(StoreShipRequest $request, Ship $ship): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data, $ship) {
            $ship->update($this->extractShipAttrs($data));

            $this->syncTranslations($ship, $data['translations']);
            $this->handleMedia($ship, $request);
        });

        return redirect()
            ->route('admin.ships.edit', $ship)
            ->with('success', 'Gemi güncellendi.');
    }

    public function destroy(Ship $ship): RedirectResponse
    {
        if ($ship->cabins()->exists()) {
            return back()->with('error', 'Bu geminin kabinleri var, önce kabin envanterini temizleyin.');
        }

        $ship->delete();

        return redirect()
            ->route('admin.ships.index')
            ->with('success', 'Gemi silindi.');
    }

    /**
     * Media item delete (cover/gallery/deck_plans) — small JSON endpoint
     * so the form view can use AJAX or POST link without a separate route.
     */
    public function deleteMedia(Ship $ship, int $mediaId): RedirectResponse
    {
        $media = $ship->media()->where('id', $mediaId)->first();
        if ($media) {
            $media->delete();
        }

        return back()->with('success', 'Medya silindi.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function form(Ship $ship): View
    {
        $ship->load(['translations', 'media', 'company']);

        $languages    = Language::active()->orderBy('sort_order')->get();
        $companies    = ShipCompany::query()->ordered()->get(['id', 'name']);
        $translations = $ship->translations->keyBy('language_id');
        $facilities   = (array) config('ship_facilities.available', []);
        $labels       = (array) config('ship_facilities.labels.tr', []);
        $selectedFac  = $ship->facilities ?? [];

        return view('tours::admin.ships.form', compact(
            'ship', 'languages', 'companies', 'translations',
            'facilities', 'labels', 'selectedFac'
        ));
    }

    private function extractShipAttrs(array $data): array
    {
        return [
            'ship_company_id'    => (int) $data['ship_company_id'],
            'slug'               => $data['slug'],
            'name'               => $data['name'],
            'star_rating'        => $data['star_rating']        ?? null,
            'local_agent'        => $data['local_agent']        ?? null,
            'flag_country_code'  => isset($data['flag_country_code']) ? strtoupper($data['flag_country_code']) : null,
            'imo_number'         => $data['imo_number']         ?? null,
            'year_built'         => $data['year_built']         ?? null,
            'passenger_capacity' => $data['passenger_capacity'] ?? null,
            'crew_count'         => $data['crew_count']         ?? null,
            'deck_count'         => $data['deck_count']         ?? null,
            'tonnage'            => $data['tonnage']            ?? null,
            'length_m'           => $data['length_m']           ?? null,
            'beam_m'             => $data['beam_m']             ?? null,
            'cruise_speed_knots' => $data['cruise_speed_knots'] ?? null,
            'facilities'         => $data['facilities']         ?? null,
            'sort_order'         => (int) ($data['sort_order'] ?? 0),
            'is_active'          => (bool) ($data['is_active'] ?? true),
        ];
    }

    private function syncTranslations(Ship $ship, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            ShipTranslation::updateOrCreate(
                ['ship_id' => $ship->id, 'language_id' => $languageId],
                [
                    'description'      => $entry['description']      ?? null,
                    'meta_title'       => $entry['meta_title']       ?? null,
                    'meta_description' => $entry['meta_description'] ?? null,
                ]
            );
        }
    }

    private function handleMedia(Ship $ship, Request $request): void
    {
        if ($request->hasFile('cover')) {
            $ship->clearMediaCollection('cover');
            $ship->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $ship->addMedia($file)->toMediaCollection('gallery');
            }
        }

        if ($request->hasFile('deck_plans')) {
            foreach ($request->file('deck_plans') as $file) {
                $ship->addMedia($file)->toMediaCollection('deck_plans');
            }
        }
    }
}
