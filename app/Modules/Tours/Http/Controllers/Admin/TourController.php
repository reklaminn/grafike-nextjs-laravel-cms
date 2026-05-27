<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Enums\PricingMode;
use App\Modules\Tours\Enums\SalesStatus;
use App\Modules\Tours\Enums\TourType;
use App\Modules\Tours\Http\Requests\Admin\StoreTourRequest;
use App\Modules\Tours\Models\Destination;
use App\Modules\Tours\Models\Ship;
use App\Modules\Tours\Models\Tour;
use App\Modules\Tours\Models\TourCategory;
use App\Modules\Tours\Models\TourTag;
use App\Modules\Tours\Models\TourTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Tour admin CRUD — 9-tab wizard pattern (Phase 2 redesign).
 *
 * Form tek POST endpoint'i, panel'ler Alpine.js x-data ile switch'leniyor.
 * Bazı tab'lar sub-resource'lara link veriyor (Tab 2 itinerary, Tab 3
 * pricing matrix, Tab 4 dates) — bu controller sadece Tab 1/5/6/7/8'i
 * persist eder; nested route'lar ayrı controller'larda.
 */
class TourController extends Controller
{
    public function index(Request $request): View
    {
        $defaultLanguage = Language::active()->orderBy('sort_order')->first();

        $query = Tour::query()
            ->with([
                'translations',
                'category.translations',
                'ship.company',
            ])
            ->withCount(['dates', 'destinations']);

        // Filters
        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('search_index', 'like', "%{$search}%");
            });
        }

        $tours = $query->orderBy('sort_order')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('tours::admin.tours.index', [
            'tours'           => $tours,
            'defaultLanguage' => $defaultLanguage,
            'filters'         => $request->only(['type', 'status', 'q']),
            'types'           => TourType::cases(),
        ]);
    }

    public function create(): View
    {
        return $this->renderForm(new Tour([
            'type'          => TourType::Package->value,
            'currency'      => 'TRY',
            'status'        => 'draft',
            'pricing_mode'  => PricingMode::PerPerson->value,
            'sales_status'  => SalesStatus::LivePayment->value,
        ]));
    }

    public function store(StoreTourRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $tour = DB::transaction(function () use ($request, $data) {
            $tour = Tour::create($this->extractAttrs($request, $data));

            $this->syncTranslations($tour, $data['translations']);
            $this->syncRelations($tour, $data);
            $this->handleMedia($tour, $request);
            $this->refreshSearchIndex($tour);

            return $tour;
        });

        return redirect()
            ->route('admin.tours.edit', $tour)
            ->with('success', 'Tur oluşturuldu.');
    }

    public function edit(Tour $tour): View
    {
        return $this->renderForm($tour);
    }

    public function update(StoreTourRequest $request, Tour $tour): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data, $tour) {
            $tour->update($this->extractAttrs($request, $data));

            $this->syncTranslations($tour, $data['translations']);
            $this->syncRelations($tour, $data);
            $this->handleMedia($tour, $request);
            $this->refreshSearchIndex($tour);
        });

        return redirect()
            ->route('admin.tours.edit', $tour)
            ->with('success', 'Tur güncellendi.');
    }

    public function destroy(Tour $tour): RedirectResponse
    {
        if ($tour->dates()->whereHas('bookings')->exists()) {
            return back()->with('error', 'Bu turda aktif rezervasyon var, önce iptal/iade akışlarını tamamlayın.');
        }

        $tour->delete();

        return redirect()->route('admin.tours.index')->with('success', 'Tur silindi.');
    }

    /**
     * "Tur Kopyala" — eski sistemde menü item'ı vardı.  Yeni tur, draft
     * status'te, "-kopya" suffix'li slug ile.  Translations + relations +
     * media kopyalanır; dates kopyalanmaz (her tur kendi tarihlerine
     * sahip olmalı).
     *
     * copied_from_tour_id ile audit trail kurulur.
     */
    public function duplicate(Tour $tour): RedirectResponse
    {
        $tour->load(['translations', 'destinations', 'marketingTags', 'secondaryCategories', 'media']);

        $copy = DB::transaction(function () use ($tour) {
            // 1) Tour kayıt — slug benzersizlik için suffix
            $suffix    = 1;
            $base      = $tour->slug . '-kopya';
            $newSlug   = $base;
            while (Tour::where('slug', $newSlug)->exists()) {
                $suffix++;
                $newSlug = "{$base}-{$suffix}";
            }

            $copy = Tour::create([
                'type'                  => $tour->type?->value,
                'slug'                  => $newSlug,
                'sku'                   => null, // sku unique, manuel girilsin
                'tour_category_id'      => $tour->tour_category_id,
                'ship_id'               => $tour->ship_id,
                'status'                => 'draft',
                'currency'              => $tour->currency,
                'base_price'            => $tour->base_price,
                'capacity_default'      => $tour->capacity_default,
                'pricing_mode'          => $tour->pricing_mode?->value,
                'sales_status'          => $tour->sales_status?->value,
                'includes_flight'       => $tour->includes_flight,
                'flight_info'           => $tour->flight_info,
                'type_config'           => $tour->type_config,
                'sort_order'            => $tour->sort_order,
                'is_featured'           => false, // kopyalar default olarak öne çıkmaz
                'structured_data_json'  => $tour->structured_data_json,
                'copied_from_tour_id'   => $tour->id,
            ]);

            // 2) Translations — "Kopya:" prefix title'da
            foreach ($tour->translations as $tr) {
                TourTranslation::create([
                    'tour_id'           => $copy->id,
                    'language_id'       => $tr->language_id,
                    'title'             => 'Kopya: ' . $tr->title,
                    'subtitle'          => $tr->subtitle,
                    'short_description' => $tr->short_description,
                    'description'       => $tr->description,
                    'highlights'        => $tr->highlights,
                    'important_info'    => $tr->important_info,
                    'meta_title'        => $tr->meta_title,
                    'meta_description'  => $tr->meta_description,
                    'og_image_url'      => $tr->og_image_url,
                ]);
            }

            // 3) M2M relations
            $copy->destinations()->sync(
                $tour->destinations->mapWithKeys(fn ($d) => [
                    $d->id => ['sort_order' => $d->pivot->sort_order ?? 0],
                ])->all()
            );
            $copy->marketingTags()->sync(
                $tour->marketingTags->mapWithKeys(fn ($t) => [
                    $t->id => ['sort_order' => $t->pivot->sort_order ?? 0],
                ])->all()
            );
            $copy->secondaryCategories()->sync(
                $tour->secondaryCategories->mapWithKeys(fn ($c) => [
                    $c->id => ['sort_order' => $c->pivot->sort_order ?? 0],
                ])->all()
            );

            // 4) Media — Spatie'nin built-in copy() yardımcısı yok,
            //    her media'yı toMediaCollection ile yeni model'e taşı.
            foreach ($tour->getMedia('cover') as $media) {
                $media->copy($copy, 'cover');
            }
            foreach ($tour->getMedia('gallery') as $media) {
                $media->copy($copy, 'gallery');
            }
            foreach ($tour->getMedia('brochure') as $media) {
                $media->copy($copy, 'brochure');
            }

            $this->refreshSearchIndex($copy);

            return $copy;
        });

        return redirect()
            ->route('admin.tours.edit', $copy)
            ->with('success', 'Tur kopyalandı.  Slug + SKU + media kontrol edip yayınlayın.');
    }

    /**
     * Media item delete — form'dan inline POST link ile çağrılır.
     */
    public function deleteMedia(Tour $tour, int $mediaId): RedirectResponse
    {
        $media = $tour->media()->where('id', $mediaId)->first();
        if ($media) {
            $media->delete();
        }

        return back()->with('success', 'Medya silindi.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function renderForm(Tour $tour): View
    {
        $tour->load([
            'translations',
            'ship.company',
            'ship.cabins.category',
            'dates',
            'priceGroups',
            'destinations.translations',
            'marketingTags.translations',
            'secondaryCategories.translations',
            'category.translations',
            'media',
            'itineraries.days',
        ]);

        $languages         = Language::active()->orderBy('sort_order')->get();
        $categories        = TourCategory::query()->with('translations')->orderBy('sort_order')->get();
        $ships             = Ship::query()->with('company')->ordered()->get();
        $allDestinations   = Destination::query()->with('translations')->ordered()->get();
        $allTags           = TourTag::query()->with('translations')->ordered()->get();
        $translations      = $tour->translations->keyBy('language_id');

        $selectedDestIds   = $tour->destinations->pluck('id')->toArray();
        $selectedTagIds    = $tour->marketingTags->pluck('id')->toArray();
        $selectedSecCatIds = $tour->secondaryCategories->pluck('id')->toArray();

        return view('tours::admin.tours.form', [
            'tour'                => $tour,
            'languages'           => $languages,
            'categories'          => $categories,
            'ships'               => $ships,
            'allDestinations'     => $allDestinations,
            'allTags'             => $allTags,
            'translations'        => $translations,
            'types'               => TourType::cases(),
            'pricingModes'        => PricingMode::cases(),
            'salesStatuses'       => SalesStatus::cases(),
            'selectedDestIds'     => $selectedDestIds,
            'selectedTagIds'      => $selectedTagIds,
            'selectedSecCatIds'   => $selectedSecCatIds,
        ]);
    }

    private function extractAttrs(StoreTourRequest $request, array $data): array
    {
        return [
            'type'                  => $data['type'],
            'slug'                  => $data['slug'],
            'sku'                   => $data['sku'] ?? null,
            'tour_category_id'      => $data['tour_category_id'] ?? null,
            'ship_id'               => $data['ship_id'] ?? null,
            'status'                => $data['status'],
            'currency'              => strtoupper($data['currency']),
            'base_price'            => (int) $data['base_price'],
            'capacity_default'      => (int) $data['capacity_default'],
            'pricing_mode'          => $data['pricing_mode'] ?? PricingMode::PerPerson->value,
            'sales_status'          => $data['sales_status'] ?? SalesStatus::LivePayment->value,
            'includes_flight'       => (bool) ($data['includes_flight'] ?? false),
            'flight_info'           => $request->flightInfoArray(),
            'type_config'           => $request->typeConfigArray(),
            'structured_data_json'  => $request->structuredDataArray(),
            'sort_order'            => (int) ($data['sort_order'] ?? 0),
            'is_featured'           => (bool) ($data['is_featured'] ?? false),
        ];
    }

    private function syncTranslations(Tour $tour, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            TourTranslation::updateOrCreate(
                ['tour_id' => $tour->id, 'language_id' => $languageId],
                [
                    'title'             => trim((string) ($entry['title'] ?? '')),
                    'subtitle'          => $entry['subtitle']          ?? null,
                    'short_description' => $entry['short_description'] ?? null,
                    'description'       => $entry['description']       ?? null,
                    'highlights'        => $entry['highlights']        ?? null,
                    'important_info'    => $entry['important_info']    ?? null,
                    'meta_title'        => $entry['meta_title']        ?? null,
                    'meta_description'  => $entry['meta_description']  ?? null,
                    'og_image_url'      => $entry['og_image_url']      ?? null,
                ]
            );
        }
    }

    /**
     * M2M sync — destinations + marketing tags + secondary categories.
     * Sort order = admin'in seçim sırası (kullanıcı UX için).
     */
    private function syncRelations(Tour $tour, array $data): void
    {
        $payload = function (array $ids): array {
            $p = [];
            foreach (array_values($ids) as $i => $id) {
                $p[(int) $id] = ['sort_order' => $i];
            }
            return $p;
        };

        $tour->destinations()->sync($payload($data['destination_ids'] ?? []));
        $tour->marketingTags()->sync($payload($data['tour_tag_ids'] ?? []));
        $tour->secondaryCategories()->sync($payload($data['secondary_category_ids'] ?? []));
    }

    private function handleMedia(Tour $tour, Request $request): void
    {
        if ($request->hasFile('cover')) {
            $tour->clearMediaCollection('cover');
            $tour->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $tour->addMedia($file)->toMediaCollection('gallery');
            }
        }

        if ($request->hasFile('brochure')) {
            $tour->clearMediaCollection('brochure');
            $tour->addMediaFromRequest('brochure')->toMediaCollection('brochure');
        }
    }

    /**
     * Denormalised search bucket — concatenated title + description
     * across every translation, lowercased.  Powers the list-page
     * search box without joining translations on every query.
     */
    private function refreshSearchIndex(Tour $tour): void
    {
        $tour->load('translations');
        $bag = [];
        foreach ($tour->translations as $tr) {
            $bag[] = $tr->title;
            $bag[] = $tr->subtitle;
            $bag[] = $tr->short_description;
        }
        $tour->search_index = mb_strtolower(trim(implode(' ', array_filter($bag))));
        $tour->saveQuietly();
    }
}
