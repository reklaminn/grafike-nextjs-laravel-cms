<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Http\Requests\Admin\StoreCabinRequest;
use App\Modules\Tours\Models\Cabin;
use App\Modules\Tours\Models\CabinCategory;
use App\Modules\Tours\Models\CabinTranslation;
use App\Modules\Tours\Models\Ship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Cabin envanteri CRUD (per ship).
 *
 * Cabin → Ship FK zorunlu (her cabin bir geminin).
 * Cabin → CabinCategory FK zorunlu (industry-standard tip).
 * brand_subcategory opsiyonel (örn. "Yacht Club Deluxe Suite").
 *
 * Media: cover + gallery + floor_plan (PDF veya görsel).
 */
class CabinController extends Controller
{
    public function index(Request $request): View
    {
        $defaultLanguage = Language::active()->orderBy('sort_order')->first();

        $query = Cabin::query()
            ->with(['ship.company', 'category.translations', 'translations', 'media']);

        if ($shipId = $request->integer('ship')) {
            $query->where('ship_id', $shipId);
        }
        if ($categoryId = $request->integer('category')) {
            $query->where('cabin_category_id', $categoryId);
        }
        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('deck_name', 'like', "%{$search}%")
                  ->orWhere('brand_subcategory', 'like', "%{$search}%");
            });
        }

        $cabins     = $query->ordered()->paginate(30)->withQueryString();
        $ships      = Ship::query()->ordered()->get(['id', 'name', 'ship_company_id']);
        $categories = CabinCategory::query()->with('translations')->ordered()->get();

        return view('tours::admin.cabins.index', [
            'cabins'          => $cabins,
            'ships'           => $ships,
            'categories'      => $categories,
            'defaultLanguage' => $defaultLanguage,
            'filters'         => $request->only(['ship', 'category', 'q']),
        ]);
    }

    public function create(Request $request): View
    {
        $cabin = new Cabin(['is_active' => true]);

        // Allow preselecting ship via query param (deep link from ship detail)
        if ($shipId = $request->integer('ship')) {
            $cabin->ship_id = $shipId;
        }

        return $this->form($cabin);
    }

    public function store(StoreCabinRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $cabin = DB::transaction(function () use ($request, $data) {
            $cabin = Cabin::create($this->extractAttrs($data));

            $this->syncTranslations($cabin, $data['translations']);
            $this->handleMedia($cabin, $request);

            return $cabin;
        });

        return redirect()
            ->route('admin.cabins.edit', $cabin)
            ->with('success', 'Kabin oluşturuldu.');
    }

    public function edit(Cabin $cabin): View
    {
        return $this->form($cabin);
    }

    public function update(StoreCabinRequest $request, Cabin $cabin): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data, $cabin) {
            $cabin->update($this->extractAttrs($data));

            $this->syncTranslations($cabin, $data['translations']);
            $this->handleMedia($cabin, $request);
        });

        return redirect()
            ->route('admin.cabins.edit', $cabin)
            ->with('success', 'Kabin güncellendi.');
    }

    public function destroy(Cabin $cabin): RedirectResponse
    {
        // TourCabinPrice ile bağlı mı? (Phase 1.5.b matrix)
        $priceCount = DB::table('tour_cabin_prices')->where('cabin_id', $cabin->id)->count();
        if ($priceCount > 0) {
            return back()->with('error', "Bu kabin {$priceCount} fiyat satırında kullanılıyor.");
        }

        $cabin->delete();

        return redirect()
            ->route('admin.cabins.index')
            ->with('success', 'Kabin silindi.');
    }

    public function deleteMedia(Cabin $cabin, int $mediaId): RedirectResponse
    {
        $media = $cabin->media()->where('id', $mediaId)->first();
        if ($media) {
            $media->delete();
        }

        return back()->with('success', 'Medya silindi.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function form(Cabin $cabin): View
    {
        $cabin->load(['translations', 'media', 'ship.company', 'category.translations']);

        $languages    = Language::active()->orderBy('sort_order')->get();
        $ships        = Ship::query()->with('company')->ordered()->get();
        $categories   = CabinCategory::query()->with('translations')->ordered()->get();
        $translations = $cabin->translations->keyBy('language_id');

        return view('tours::admin.cabins.form', compact(
            'cabin', 'languages', 'ships', 'categories', 'translations'
        ));
    }

    private function extractAttrs(array $data): array
    {
        return [
            'ship_id'                => (int) $data['ship_id'],
            'cabin_category_id'      => (int) $data['cabin_category_id'],
            'brand_subcategory'      => $data['brand_subcategory'] ?? null,
            'code'                   => $data['code']              ?? null,
            'deck_name'              => $data['deck_name']         ?? null,
            'max_capacity'           => $data['max_capacity']      ?? null,
            'base_price_per_person'  => $data['base_price_per_person'] ?? null,
            'sort_order'             => (int) ($data['sort_order'] ?? 0),
            'is_active'              => (bool) ($data['is_active'] ?? true),
        ];
    }

    private function syncTranslations(Cabin $cabin, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            CabinTranslation::updateOrCreate(
                ['cabin_id' => $cabin->id, 'language_id' => $languageId],
                [
                    'name'        => trim((string) ($entry['name'] ?? '')),
                    'description' => $entry['description'] ?? null,
                ]
            );
        }
    }

    private function handleMedia(Cabin $cabin, Request $request): void
    {
        if ($request->hasFile('cover')) {
            $cabin->clearMediaCollection('cover');
            $cabin->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $cabin->addMedia($file)->toMediaCollection('gallery');
            }
        }

        if ($request->hasFile('floor_plan')) {
            $cabin->clearMediaCollection('floor_plan');
            $cabin->addMediaFromRequest('floor_plan')->toMediaCollection('floor_plan');
        }
    }
}
