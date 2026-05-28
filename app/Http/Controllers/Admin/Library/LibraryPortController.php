<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Models\Library\LibraryPort;
use App\Modules\Tours\Models\Library\LibraryPortTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Central kütüphane — liman master CRUD (super-admin).  ~1000 liman.
 */
class LibraryPortController extends Controller
{
    public function index(Request $request): View
    {
        $query = LibraryPort::query()->with('translations');

        if ($country = $request->string('country')->toString()) {
            $query->where('country_code', strtoupper($country));
        }
        if ($search = $request->string('q')->toString()) {
            $query->where('slug', 'like', "%{$search}%");
        }

        $ports = $query->ordered()->paginate(40)->withQueryString();

        return view('admin.library.ports.index', [
            'ports'   => $ports,
            'filters' => $request->only(['country', 'q']),
        ]);
    }

    public function create(): View
    {
        return view('admin.library.ports.form', [
            'port'         => new LibraryPort(['is_active' => true]),
            'languages'    => Language::active()->orderBy('sort_order')->get(),
            'translations' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request, null);

        $port = DB::transaction(function () use ($data) {
            $port = LibraryPort::create($this->attrs($data));
            $this->syncTranslations($port, $data['translations'] ?? []);

            return $port;
        });

        return redirect()
            ->route('admin.library.ports.edit', $port)
            ->with('success', 'Kütüphane limanı oluşturuldu.');
    }

    public function edit(LibraryPort $port): View
    {
        return view('admin.library.ports.form', [
            'port'         => $port,
            'languages'    => Language::active()->orderBy('sort_order')->get(),
            'translations' => $port->translations->keyBy('language_id'),
        ]);
    }

    public function update(Request $request, LibraryPort $port): RedirectResponse
    {
        $data = $this->validateData($request, $port->id);

        DB::transaction(function () use ($data, $port) {
            $port->update($this->attrs($data));
            $this->syncTranslations($port, $data['translations'] ?? []);
        });

        return redirect()
            ->route('admin.library.ports.edit', $port)
            ->with('success', 'Kütüphane limanı güncellendi.');
    }

    public function destroy(LibraryPort $port): RedirectResponse
    {
        $port->delete();

        return redirect()
            ->route('admin.library.ports.index')
            ->with('success', 'Kütüphane limanı silindi.');
    }

    private function validateData(Request $request, ?int $id): array
    {
        return $request->validate([
            'slug'         => ['required', 'string', 'max:100', Rule::unique('library_ports', 'slug')->ignore($id)],
            'country_code' => ['nullable', 'string', 'size:2'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'population'   => ['nullable', 'integer', 'min:0'],
            'video_url'    => ['nullable', 'string', 'max:500'],
            'timezone'     => ['nullable', 'string', 'max:50'],
            'cover_url'    => ['nullable', 'string', 'max:500'],
            'is_active'    => ['nullable', 'boolean'],
            'sort_order'   => ['nullable', 'integer'],
            'translations'                     => ['nullable', 'array'],
            'translations.*.language_id'       => ['required', 'integer'],
            'translations.*.name'              => ['nullable', 'string', 'max:200'],
            'translations.*.short_description' => ['nullable', 'string'],
            'translations.*.long_description'  => ['nullable', 'string'],
            'translations.*.meta_title'        => ['nullable', 'string', 'max:255'],
            'translations.*.meta_description'  => ['nullable', 'string'],
        ]);
    }

    private function attrs(array $data): array
    {
        return [
            'slug'         => $data['slug'],
            'country_code' => isset($data['country_code']) ? strtoupper($data['country_code']) : null,
            'latitude'     => $data['latitude'] ?? null,
            'longitude'    => $data['longitude'] ?? null,
            'population'   => $data['population'] ?? null,
            'video_url'    => $data['video_url'] ?? null,
            'timezone'     => $data['timezone'] ?? null,
            'cover_url'    => $data['cover_url'] ?? null,
            'is_active'    => (bool) ($data['is_active'] ?? false),
            'sort_order'   => (int) ($data['sort_order'] ?? 0),
        ];
    }

    private function syncTranslations(LibraryPort $port, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            LibraryPortTranslation::updateOrCreate(
                ['library_port_id' => $port->id, 'language_id' => $languageId],
                [
                    'name'              => trim((string) ($entry['name'] ?? '')),
                    'short_description' => $entry['short_description'] ?? null,
                    'long_description'  => $entry['long_description'] ?? null,
                    'meta_title'        => $entry['meta_title'] ?? null,
                    'meta_description'  => $entry['meta_description'] ?? null,
                ]
            );
        }
    }
}
