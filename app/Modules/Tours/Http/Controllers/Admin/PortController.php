<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Http\Requests\Admin\StorePortRequest;
use App\Modules\Tours\Models\Port;
use App\Modules\Tours\Models\PortTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Port master CRUD.  Cruise/ferry itinerary stop'larının kanonik kaynağı.
 *
 * country_code → flagcdn.com CDN flag URL'i otomatik üretir (eski sistem
 * elle URL girilirdi, yeni sistem ISO kodundan render eder).
 */
class PortController extends Controller
{
    public function index(Request $request): View
    {
        $defaultLanguage = Language::active()->orderBy('sort_order')->first();

        $query = Port::query()->with('translations')->withCount('destinations');

        if ($country = $request->string('country')->toString()) {
            $query->where('country_code', strtoupper($country));
        }
        if ($search = $request->string('q')->toString()) {
            $query->where('slug', 'like', "%{$search}%");
        }

        $ports = $query->ordered()->paginate(30)->withQueryString();

        return view('tours::admin.ports.index', [
            'ports'           => $ports,
            'defaultLanguage' => $defaultLanguage,
            'filters'         => $request->only(['country', 'q']),
        ]);
    }

    public function create(): View
    {
        $languages = Language::active()->orderBy('sort_order')->get();

        return view('tours::admin.ports.form', [
            'port'         => new Port(['is_active' => true]),
            'languages'    => $languages,
            'translations' => collect(),
        ]);
    }

    public function store(StorePortRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $port = DB::transaction(function () use ($data) {
            $port = Port::create([
                'slug'         => $data['slug'],
                'country_code' => isset($data['country_code']) ? strtoupper($data['country_code']) : null,
                'latitude'     => $data['latitude']   ?? null,
                'longitude'    => $data['longitude']  ?? null,
                'population'   => $data['population'] ?? null,
                'video_url'    => $data['video_url']  ?? null,
                'timezone'     => $data['timezone']   ?? null,
                'sort_order'   => (int) ($data['sort_order'] ?? 0),
                'is_active'    => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($port, $data['translations']);

            return $port;
        });

        return redirect()
            ->route('admin.ports.edit', $port)
            ->with('success', 'Liman oluşturuldu.');
    }

    public function edit(Port $port): View
    {
        $languages    = Language::active()->orderBy('sort_order')->get();
        $translations = $port->translations->keyBy('language_id');

        return view('tours::admin.ports.form', [
            'port'         => $port,
            'languages'    => $languages,
            'translations' => $translations,
        ]);
    }

    public function update(StorePortRequest $request, Port $port): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $port) {
            $port->update([
                'slug'         => $data['slug'],
                'country_code' => isset($data['country_code']) ? strtoupper($data['country_code']) : null,
                'latitude'     => $data['latitude']   ?? null,
                'longitude'    => $data['longitude']  ?? null,
                'population'   => $data['population'] ?? null,
                'video_url'    => $data['video_url']  ?? null,
                'timezone'     => $data['timezone']   ?? null,
                'sort_order'   => (int) ($data['sort_order'] ?? 0),
                'is_active'    => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($port, $data['translations']);
        });

        return redirect()
            ->route('admin.ports.edit', $port)
            ->with('success', 'Liman güncellendi.');
    }

    public function destroy(Port $port): RedirectResponse
    {
        // Itinerary stop'larında kullanılıyor mu?
        $stopCount = DB::table('tour_itinerary_stops')->where('port_id', $port->id)->count();
        if ($stopCount > 0) {
            return back()->with('error', "Bu liman {$stopCount} tur rotasında kullanılıyor.");
        }

        $port->delete();

        return redirect()
            ->route('admin.ports.index')
            ->with('success', 'Liman silindi.');
    }

    private function syncTranslations(Port $port, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            PortTranslation::updateOrCreate(
                ['port_id' => $port->id, 'language_id' => $languageId],
                [
                    'name'              => trim((string) ($entry['name'] ?? '')),
                    'short_description' => $entry['short_description'] ?? null,
                    'long_description'  => $entry['long_description']  ?? null,
                    'meta_title'        => $entry['meta_title']        ?? null,
                    'meta_description'  => $entry['meta_description']  ?? null,
                ]
            );
        }
    }
}
