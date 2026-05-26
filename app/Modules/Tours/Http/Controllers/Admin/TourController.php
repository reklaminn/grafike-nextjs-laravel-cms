<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Enums\TourType;
use App\Modules\Tours\Http\Requests\Admin\StoreTourRequest;
use App\Modules\Tours\Models\Tour;
use App\Modules\Tours\Models\TourCategory;
use App\Modules\Tours\Models\TourTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TourController extends Controller
{
    public function index(Request $request): View
    {
        $defaultLanguage = Language::active()->orderBy('sort_order')->first();

        $query = Tour::query()
            ->with(['translations', 'category.translations'])
            ->withCount('dates');

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
        $languages  = Language::active()->orderBy('sort_order')->get();
        $categories = TourCategory::query()->with('translations')->orderBy('sort_order')->get();

        return view('tours::admin.tours.form', [
            'tour'         => new Tour(['type' => TourType::Package->value, 'currency' => 'TRY', 'status' => 'draft']),
            'languages'    => $languages,
            'categories'   => $categories,
            'translations' => collect(),
            'types'        => TourType::cases(),
        ]);
    }

    public function store(StoreTourRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $tour = DB::transaction(function () use ($request, $data) {
            $tour = Tour::create([
                'type'             => $data['type'],
                'slug'             => $data['slug'],
                'tour_category_id' => $data['tour_category_id'] ?? null,
                'status'           => $data['status'],
                'currency'         => strtoupper($data['currency']),
                'base_price'       => (int) $data['base_price'],
                'capacity_default' => (int) $data['capacity_default'],
                'type_config'      => $request->typeConfigArray(),
                'sort_order'       => (int) ($data['sort_order'] ?? 0),
                'is_featured'      => (bool) ($data['is_featured'] ?? false),
            ]);

            $this->syncTranslations($tour, $data['translations']);
            $this->refreshSearchIndex($tour);

            return $tour;
        });

        return redirect()
            ->route('admin.tours.edit', $tour)
            ->with('success', 'Tur oluşturuldu.');
    }

    public function edit(Tour $tour): View
    {
        $tour->load(['translations', 'cabinTypes', 'dates']);

        $languages  = Language::active()->orderBy('sort_order')->get();
        $categories = TourCategory::query()->with('translations')->orderBy('sort_order')->get();

        return view('tours::admin.tours.form', [
            'tour'         => $tour,
            'languages'    => $languages,
            'categories'   => $categories,
            'translations' => $tour->translations->keyBy('language_id'),
            'types'        => TourType::cases(),
        ]);
    }

    public function update(StoreTourRequest $request, Tour $tour): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data, $tour) {
            $tour->update([
                'type'             => $data['type'],
                'slug'             => $data['slug'],
                'tour_category_id' => $data['tour_category_id'] ?? null,
                'status'           => $data['status'],
                'currency'         => strtoupper($data['currency']),
                'base_price'       => (int) $data['base_price'],
                'capacity_default' => (int) $data['capacity_default'],
                'type_config'      => $request->typeConfigArray(),
                'sort_order'       => (int) ($data['sort_order'] ?? 0),
                'is_featured'      => (bool) ($data['is_featured'] ?? false),
            ]);

            $this->syncTranslations($tour, $data['translations']);
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
