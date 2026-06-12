<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Http\Requests\Admin\StoreCabinCategoryRequest;
use App\Modules\Tours\Models\CabinCategory;
use App\Modules\Tours\Models\CabinCategoryTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Endüstri standart kabin tipi kategorileri (Inside / Outside / Balcony /
 * Ocean View / Suite + custom).  5 default seed Phase 1.5.a'da geldi.
 *
 * Bu sayfa operatörün özel kategori eklemesi (örn. "Junior Suite") ve
 * çevirilerini düzenlemesi için.
 */
class CabinCategoryController extends Controller
{
    public function index(): View
    {
        $defaultLanguage = Language::active()->orderBy('sort_order')->first();

        $categories = CabinCategory::query()
            ->with('translations')
            ->withCount('cabins')
            ->ordered()
            ->paginate(30)
            ->withQueryString();

        return view('tours::admin.cabin-categories.index', compact('categories', 'defaultLanguage'));
    }

    public function create(): View
    {
        $languages = Language::active()->orderBy('sort_order')->get();

        return view('tours::admin.cabin-categories.form', [
            'category'     => new CabinCategory(['is_active' => true]),
            'languages'    => $languages,
            'translations' => collect(),
        ]);
    }

    public function store(StoreCabinCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $category = CabinCategory::create([
                'slug'       => $data['slug'],
                'icon'       => $data['icon']       ?? null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active'  => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($category, $data['translations']);
        });

        return redirect()
            ->route('admin.cabin-categories.index')
            ->with('success', 'Kategori oluşturuldu.');
    }

    public function edit(CabinCategory $cabinCategory): View
    {
        $languages    = Language::active()->orderBy('sort_order')->get();
        $translations = $cabinCategory->translations->keyBy('language_id');

        return view('tours::admin.cabin-categories.form', [
            'category'     => $cabinCategory,
            'languages'    => $languages,
            'translations' => $translations,
        ]);
    }

    public function update(StoreCabinCategoryRequest $request, CabinCategory $cabinCategory): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $cabinCategory) {
            $cabinCategory->update([
                'slug'       => $data['slug'],
                'icon'       => $data['icon']       ?? null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active'  => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($cabinCategory, $data['translations']);
        });

        return redirect()
            ->route('admin.cabin-categories.index')
            ->with('success', 'Kategori güncellendi.');
    }

    public function destroy(CabinCategory $cabinCategory): RedirectResponse
    {
        $usageCount = $cabinCategory->cabins()->count();

        if ($usageCount > 0) {
            return back()->with('error', "Bu kategoride {$usageCount} kabin var, önce başka kategoriye taşıyın.");
        }

        $cabinCategory->delete();

        return redirect()
            ->route('admin.cabin-categories.index')
            ->with('success', 'Kategori silindi.');
    }

    private function syncTranslations(CabinCategory $category, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            CabinCategoryTranslation::updateOrCreate(
                ['cabin_category_id' => $category->id, 'language_id' => $languageId],
                [
                    'name'        => trim((string) ($entry['name'] ?? '')),
                    'description' => $entry['description'] ?? null,
                ]
            );
        }
    }
}
