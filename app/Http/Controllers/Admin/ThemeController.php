<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesCatalogToTenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ThemeRequest;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ThemeController extends Controller
{
    use ScopesCatalogToTenant;

    public function index(Request $request)
    {
        $query = Theme::query()->visibleTo($this->catalogTenantId());

        if ($request->filled('q')) {
            $search = trim((string) $request->string('q'));
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('engine', 'like', "%{$search}%");
            });
        }

        $themes = $query
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(18)
            ->withQueryString();

        return view('admin.themes.index', compact('themes'));
    }

    public function create()
    {
        return view('admin.themes.create', [
            'theme' => new Theme([
                'engine' => 'nextjs-basic-html',
                'is_active' => true,
                'assets_json' => ['css' => [], 'js' => []],
                'tokens_json' => [],
                'settings_schema_json' => [],
            ]),
        ]);
    }

    public function store(ThemeRequest $request)
    {
        $data = $this->withUploadedAssets($request, $request->validated());
        $data['tenant_id'] = $this->newCatalogOwnerId();

        $theme = Theme::create($data);

        return redirect()
            ->route('admin.themes.edit', $theme)
            ->with('success', 'Tema oluşturuldu.');
    }

    public function edit(Theme $theme)
    {
        $this->authorizeCatalogRead($theme->tenant_id);

        return view('admin.themes.edit', compact('theme'));
    }

    public function update(ThemeRequest $request, Theme $theme)
    {
        $this->authorizeCatalogWrite($theme->tenant_id);

        // tenant_id is owner-controlled, never client-supplied.
        $theme->update($this->withUploadedAssets($request, $request->validated()));

        return redirect()
            ->route('admin.themes.edit', $theme)
            ->with('success', 'Tema güncellendi.');
    }

    public function destroy(Theme $theme)
    {
        $this->authorizeCatalogWrite($theme->tenant_id);

        $theme->delete();

        return redirect()
            ->route('admin.themes.index')
            ->with('success', 'Tema silindi.');
    }

    private function withUploadedAssets(ThemeRequest $request, array $data): array
    {
        $assets = $data['assets_json'] ?? ['css' => [], 'js' => []];
        $assets['css'] = $this->normalizeAssetList($assets['css'] ?? []);
        $assets['js'] = $this->normalizeAssetList($assets['js'] ?? []);

        foreach ($request->file('css_files', []) as $file) {
            if ($file instanceof UploadedFile) {
                $assets['css'][] = $this->storeThemeAsset($file, $data['slug'] ?? 'theme', 'css');
            }
        }

        foreach ($request->file('js_files', []) as $file) {
            if ($file instanceof UploadedFile) {
                $assets['js'][] = $this->storeThemeAsset($file, $data['slug'] ?? 'theme', 'js');
            }
        }

        $data['assets_json'] = [
            'css' => array_values(array_unique($assets['css'])),
            'js' => array_values(array_unique($assets['js'])),
        ];

        unset($data['css_files'], $data['js_files']);

        return $data;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeAssetList(mixed $paths): array
    {
        if (! is_array($paths)) {
            return [];
        }

        return collect($paths)
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->map(fn (string $path) => trim($path))
            ->values()
            ->all();
    }

    private function storeThemeAsset(UploadedFile $file, string $themeSlug, string $type): string
    {
        $safeSlug = Str::slug($themeSlug) ?: 'theme';
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($originalName) ?: $type;
        $extension = strtolower($file->getClientOriginalExtension() ?: $type);
        $filename = now()->format('YmdHis') . '-' . Str::random(6) . '-' . $safeName . '.' . $extension;
        $path = $file->storeAs("themes/{$safeSlug}/{$type}", $filename, 'public');

        return tenancy()->initialized
            ? "/tenant-assets/{$path}"
            : "/storage/{$path}";
    }
}
