<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesCatalogToTenant;
use App\Http\Controllers\Controller;
use App\Models\DesignAsset;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DesignController extends Controller
{
    use ScopesCatalogToTenant;

    public function index()
    {
        $globalCss = DesignAsset::firstOrCreate(
            ['type' => 'css', 'name' => 'global'],
            ['content' => '/* Global CSS */']
        );

        $globalJs = DesignAsset::firstOrCreate(
            ['type' => 'js', 'name' => 'global'],
            ['content' => '// Global JavaScript']
        );

        $headerScripts = DesignAsset::firstOrCreate(
            ['type' => 'header_scripts', 'name' => 'header'],
            ['content' => '']
        );

        $footerScripts = DesignAsset::firstOrCreate(
            ['type' => 'footer_scripts', 'name' => 'footer'],
            ['content' => '']
        );

        $themes = Theme::query()
            ->visibleTo($this->catalogTenantId())
            ->visibleForModules($this->catalogModuleFilter())
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(function (Theme $theme) {
                $assets = $theme->assets_json ?? [];

                return [
                    'id' => $theme->id,
                    'name' => $theme->name,
                    'slug' => $theme->slug,
                    'engine' => $theme->engine,
                    'is_active' => $theme->is_active,
                    'css_paths' => implode("\n", data_get($assets, 'css', [])),
                    'js_paths' => implode("\n", data_get($assets, 'js', [])),
                ];
            });

        return view('admin.design.index', compact('globalCss', 'globalJs', 'headerScripts', 'footerScripts', 'themes'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'assets' => 'nullable|array',
            'assets.*.id' => 'required_with:assets|exists:design_assets,id',
            'assets.*.content' => 'nullable|string|max:500000',
            'theme_assets' => 'nullable|array',
            'theme_assets.*.id' => ['required_with:theme_assets', Rule::exists('central.themes', 'id')],
            'theme_assets.*.css_paths' => 'nullable|string|max:500000',
            'theme_assets.*.js_paths' => 'nullable|string|max:500000',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->input('assets', []) as $assetData) {
                $asset = DesignAsset::find($assetData['id']);

                // Create backup before update (keep last 5)
                DB::table('design_asset_backups')->insert([
                    'design_asset_id' => $asset->id,
                    'content' => $asset->content ?? '',
                    'created_at' => now(),
                ]);

                // Clean old backups (keep last 5)
                $oldBackups = DB::table('design_asset_backups')
                    ->where('design_asset_id', $asset->id)
                    ->orderByDesc('id')
                    ->pluck('id');

                $oldBackups = $oldBackups->slice(5);

                if ($oldBackups->isNotEmpty()) {
                    DB::table('design_asset_backups')->whereIn('id', $oldBackups->all())->delete();
                }

                $asset->update(['content' => $assetData['content'] ?? '']);
            }

            foreach ($request->input('theme_assets', []) as $themeAssetData) {
                /** @var Theme|null $theme */
                $theme = Theme::find($themeAssetData['id']);

                if (! $theme) {
                    continue;
                }

                // A tenant admin may only edit asset paths on their OWN theme;
                // global (shared) themes are managed by agency admins. Silently
                // skip rows the current admin is not allowed to write.
                if (! $this->canManageGlobalCatalog() && $theme->tenant_id !== $this->catalogTenantId()) {
                    continue;
                }

                $assets = $theme->assets_json ?? [];
                $assets['css'] = $this->parsePathList($themeAssetData['css_paths'] ?? '');
                $assets['js'] = $this->parsePathList($themeAssetData['js_paths'] ?? '');

                $theme->update([
                    'assets_json' => $assets,
                ]);
            }
        });

        return redirect()
            ->route('admin.design.index')
            ->with('success', 'Tasarım dosyaları başarıyla kaydedildi.');
    }

    /**
     * @return array<int, string>
     */
    protected function parsePathList(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
