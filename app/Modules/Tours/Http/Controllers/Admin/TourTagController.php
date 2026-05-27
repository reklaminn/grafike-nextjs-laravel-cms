<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Http\Requests\Admin\StoreTourTagRequest;
use App\Modules\Tours\Models\TourTag;
use App\Modules\Tours\Models\TourTagTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Pazarlama etiketleri CRUD'u (TourCategory pattern'inde).
 *
 * 12 default tag Phase 1.5.a TourTagSeeder ile yüklendi; bu sayfa
 * operatörün özel etiketler eklemesi / icon set güncellemesi /
 * sıralama değiştirmesi için.
 */
class TourTagController extends Controller
{
    public function index(): View
    {
        $defaultLanguage = Language::active()->orderBy('sort_order')->first();

        $tags = TourTag::query()
            ->with('translations')
            ->ordered()
            ->paginate(30)
            ->withQueryString();

        return view('tours::admin.tags.index', compact('tags', 'defaultLanguage'));
    }

    public function create(): View
    {
        $languages = Language::active()->orderBy('sort_order')->get();

        return view('tours::admin.tags.form', [
            'tag'          => new TourTag(['is_active' => true]),
            'languages'    => $languages,
            'translations' => collect(),
        ]);
    }

    public function store(StoreTourTagRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $tag = TourTag::create([
                'slug'       => $data['slug'],
                'icon'       => $data['icon']       ?? null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active'  => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($tag, $data['translations']);
        });

        return redirect()
            ->route('admin.tour-tags.index')
            ->with('success', 'Etiket oluşturuldu.');
    }

    public function edit(TourTag $tourTag): View
    {
        $languages    = Language::active()->orderBy('sort_order')->get();
        $translations = $tourTag->translations->keyBy('language_id');

        return view('tours::admin.tags.form', [
            'tag'          => $tourTag,
            'languages'    => $languages,
            'translations' => $translations,
        ]);
    }

    public function update(StoreTourTagRequest $request, TourTag $tourTag): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $tourTag) {
            $tourTag->update([
                'slug'       => $data['slug'],
                'icon'       => $data['icon']       ?? null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active'  => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($tourTag, $data['translations']);
        });

        return redirect()
            ->route('admin.tour-tags.index')
            ->with('success', 'Etiket güncellendi.');
    }

    public function destroy(TourTag $tourTag): RedirectResponse
    {
        // Tag'in kullanıldığı tur var mı kontrol — pivot tablo (Phase 1.5.c).
        $usageCount = DB::table('tour_marketing_tags')
            ->where('tour_tag_id', $tourTag->id)
            ->count();

        if ($usageCount > 0) {
            return back()->with('error', "Bu etiket {$usageCount} turda kullanılıyor, önce kaldırın.");
        }

        $tourTag->delete();

        return redirect()
            ->route('admin.tour-tags.index')
            ->with('success', 'Etiket silindi.');
    }

    private function syncTranslations(TourTag $tag, array $translations): void
    {
        foreach ($translations as $entry) {
            $languageId = (int) ($entry['language_id'] ?? 0);
            if ($languageId === 0) {
                continue;
            }

            TourTagTranslation::updateOrCreate(
                ['tour_tag_id' => $tag->id, 'language_id' => $languageId],
                [
                    'name'        => trim((string) ($entry['name'] ?? '')),
                    'description' => $entry['description'] ?? null,
                ]
            );
        }
    }
}
