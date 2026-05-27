<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Http\Requests\Admin\StoreDestinationRequest;
use App\Modules\Tours\Models\Destination;
use App\Modules\Tours\Models\DestinationTranslation;
use App\Modules\Tours\Models\Port;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Destination master CRUD + Port m2m pivot UI.
 *
 * Bir destinasyon birden çok port'u kapsar (Yunan Adaları → Pire, Mikonos,
 * Patmos).  compatible_tour_types JSON ile cruise/package/daily filter
 * yapılabilir (boş = hepsi uygun).
 */
class DestinationController extends Controller
{
    public function index(): View
    {
        $defaultLanguage = Language::active()->orderBy('sort_order')->first();

        $destinations = Destination::query()
            ->with(['translations', 'ports.translations'])
            ->withCount('ports')
            ->ordered()
            ->paginate(30)
            ->withQueryString();

        return view('tours::admin.destinations.index', compact('destinations', 'defaultLanguage'));
    }

    public function create(): View
    {
        $languages = Language::active()->orderBy('sort_order')->get();
        $allPorts  = Port::query()->with('translations')->ordered()->get();

        return view('tours::admin.destinations.form', [
            'destination'   => new Destination(['is_active' => true]),
            'languages'     => $languages,
            'translations'  => collect(),
            'allPorts'      => $allPorts,
            'selectedPorts' => [],
        ]);
    }

    public function store(StoreDestinationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $destination = DB::transaction(function () use ($data) {
            $destination = Destination::create([
                'slug'                  => $data['slug'],
                'latitude'              => $data['latitude']  ?? null,
                'longitude'             => $data['longitude'] ?? null,
                'compatible_tour_types' => $data['compatible_tour_types'] ?? null,
                'sort_order'            => (int) ($data['sort_order'] ?? 0),
                'is_active'             => (bool) ($data['is_active'] ?? true),
                'is_featured'           => (bool) ($data['is_featured'] ?? false),
            ]);

            $this->syncTranslations($destination, $data['translations']);
            $this->syncPorts($destination, $data['port_ids'] ?? []);

            return $destination;
        });

        return redirect()
            ->route('admin.destinations.edit', $destination)
            ->with('success', 'Destinasyon oluşturuldu.');
    }

    public function edit(Destination $destination): View
    {
        $languages     = Language::active()->orderBy('sort_order')->get();
        $allPorts      = Port::query()->with('translations')->ordered()->get();
        $translations  = $destination->translations->keyBy('language_id');
        $selectedPorts = $destination->ports()->pluck('ports.id')->toArray();

        return view('tours::admin.destinations.form', [
            'destination'   => $destination,
            'languages'     => $languages,
            'translations'  => $translations,
            'allPorts'      => $allPorts,
            'selectedPorts' => $selectedPorts,
        ]);
    }

    public function update(StoreDestinationRequest $request, Destination $destination): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $destination) {
            $destination->update([
                'slug'                  => $data['slug'],
                'latitude'              => $data['latitude']  ?? null,
                'longitude'             => $data['longitude'] ?? null,
                'compatible_tour_types' => $data['compatible_tour_types'] ?? null,
                'sort_order'            => (int) ($data['sort_order'] ?? 0),
                'is_active'             => (bool) ($data['is_active'] ?? true),
                'is_featured'           => (bool) ($data['is_featured'] ?? false),
            ]);

            $this->syncTranslations($destination, $data['translations']);
            $this->syncPorts($destination, $data['port_ids'] ?? []);
        });

        return redirect()
            ->route('admin.destinations.edit', $destination)
            ->with('success', 'Destinasyon güncellendi.');
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        // Tur kullanımı kontrol (Phase 1.5.c pivot)
        $tourCount = DB::table('tour_destinations')->where('destination_id', $destination->id)->count();
        if ($tourCount > 0) {
            return back()->with('error', "Bu destinasyon {$tourCount} turda kullanılıyor.");
        }

        $destination->delete();

        return redirect()
            ->route('admin.destinations.index')
            ->with('success', 'Destinasyon silindi.');
    }

    private function syncTranslations(Destination $destination, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            DestinationTranslation::updateOrCreate(
                ['destination_id' => $destination->id, 'language_id' => $languageId],
                [
                    'name'             => trim((string) ($entry['name'] ?? '')),
                    'description'      => $entry['description']      ?? null,
                    'meta_title'       => $entry['meta_title']       ?? null,
                    'meta_description' => $entry['meta_description'] ?? null,
                ]
            );
        }
    }

    /**
     * Pivot sync — admin'in seçtiği sırayla sort_order verir, böylece
     * frontend liman listesi aynı sırayla görünür.
     */
    private function syncPorts(Destination $destination, array $portIds): void
    {
        $payload = [];
        foreach (array_values($portIds) as $idx => $portId) {
            $payload[(int) $portId] = ['sort_order' => $idx];
        }
        $destination->ports()->sync($payload);
    }
}
