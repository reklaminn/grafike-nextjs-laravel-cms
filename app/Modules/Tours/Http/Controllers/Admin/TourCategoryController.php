<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Http\Requests\Admin\StoreTourCategoryRequest;
use App\Modules\Tours\Models\TourCategory;
use App\Modules\Tours\Models\TourCategoryTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * CRUD for hierarchical TourCategory + per-language translations.
 *
 * Standard Laravel resource controller pattern (mirrors core
 * PageController / ArticleController) so an admin already familiar
 * with the kurumsal CMS finds Tours admin's shape predictable.
 */
class TourCategoryController extends Controller
{
    public function index(): View
    {
        $defaultLanguage = Language::active()->orderBy('sort_order')->first();

        $categories = TourCategory::query()
            ->with(['translations', 'children.translations'])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('tours::admin.categories.index', compact('categories', 'defaultLanguage'));
    }

    public function create(): View
    {
        $languages = Language::active()->orderBy('sort_order')->get();
        $parents   = TourCategory::query()->orderBy('sort_order')->get();

        return view('tours::admin.categories.form', [
            'category'    => new TourCategory(),
            'languages'   => $languages,
            'parents'     => $parents,
            'translations' => [],
        ]);
    }

    public function store(StoreTourCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $category = TourCategory::create([
                'slug'       => $data['slug'],
                'parent_id'  => $data['parent_id'] ?? null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active'  => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($category, $data['translations']);
        });

        return redirect()
            ->route('admin.tour-categories.index')
            ->with('success', 'Kategori oluşturuldu.');
    }

    public function edit(TourCategory $tourCategory): View
    {
        $languages = Language::active()->orderBy('sort_order')->get();
        $parents   = TourCategory::query()
            ->where('id', '!=', $tourCategory->id)
            ->orderBy('sort_order')
            ->get();

        $translations = $tourCategory->translations->keyBy('language_id');

        return view('tours::admin.categories.form', [
            'category'     => $tourCategory,
            'languages'    => $languages,
            'parents'      => $parents,
            'translations' => $translations,
        ]);
    }

    public function update(StoreTourCategoryRequest $request, TourCategory $tourCategory): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $tourCategory) {
            $tourCategory->update([
                'slug'       => $data['slug'],
                'parent_id'  => $data['parent_id'] ?? null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active'  => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($tourCategory, $data['translations']);
        });

        return redirect()
            ->route('admin.tour-categories.index')
            ->with('success', 'Kategori güncellendi.');
    }

    public function destroy(TourCategory $tourCategory): RedirectResponse
    {
        if ($tourCategory->tours()->exists()) {
            return back()->with('error', 'Bu kategoride turlar var, önce başka bir kategoriye taşıyın.');
        }

        $tourCategory->delete();

        return redirect()
            ->route('admin.tour-categories.index')
            ->with('success', 'Kategori silindi.');
    }

    private function syncTranslations(TourCategory $category, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            TourCategoryTranslation::updateOrCreate(
                [
                    'tour_category_id' => $category->id,
                    'language_id'      => $languageId,
                ],
                [
                    'name'             => trim((string) ($entry['name'] ?? '')),
                    'description'      => $entry['description']      ?? null,
                    'meta_title'       => $entry['meta_title']       ?? null,
                    'meta_description' => $entry['meta_description'] ?? null,
                ]
            );
        }
    }
}
