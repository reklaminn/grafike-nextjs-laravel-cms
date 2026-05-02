<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Language;
use App\Models\Page;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::orderBy('sort_order')->get();

        if (tenancy()->initialized) {
            $languages->each(function (Language $language): void {
                $language->pages_count = Page::where('language_id', $language->id)->count();
                $language->articles_count = Article::where('language_id', $language->id)->count();
            });
        }

        return view('admin.languages.index', compact('languages'));
    }

    public function create()
    {
        return view('admin.languages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => ['required', 'string', 'max:5', Rule::unique('central.languages', 'code')],
            'locale' => 'nullable|string|max:10',
            'direction' => 'required|in:ltr,rtl',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Language::create($validated);
        Cache::forget('active_languages');

        return redirect()->route('admin.languages.index')
            ->with('success', 'Dil oluşturuldu.');
    }

    public function edit(Language $language)
    {
        return view('admin.languages.edit', compact('language'));
    }

    public function update(Request $request, Language $language)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => ['required', 'string', 'max:5', Rule::unique('central.languages', 'code')->ignore($language->id)],
            'locale' => 'nullable|string|max:10',
            'direction' => 'required|in:ltr,rtl',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $language->update($validated);
        Cache::forget('active_languages');

        return redirect()->route('admin.languages.index')
            ->with('success', 'Dil güncellendi.');
    }

    public function destroy(Language $language)
    {
        if (tenancy()->initialized && (
            Page::where('language_id', $language->id)->exists()
            || Article::where('language_id', $language->id)->exists()
        )) {
            return back()->with('error', 'Bu dile ait sayfa veya yazı var. Önce onları silin.');
        }

        $language->delete();
        Cache::forget('active_languages');

        return redirect()->route('admin.languages.index')
            ->with('success', 'Dil silindi.');
    }

    // Translation management
    public function translations(Request $request)
    {
        $query = Translation::with('language');

        if ($group = $request->input('group')) {
            $query->where('group', $group);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");
            });
        }

        $translations = $query->orderBy('group')->orderBy('key')->paginate(30)->withQueryString();
        $groups = Translation::select('group')->distinct()->pluck('group');
        $languages = Language::active()->get();

        return view('admin.languages.translations', compact('translations', 'groups', 'languages'));
    }

    public function saveTranslation(Request $request)
    {
        $validated = $request->validate([
            'language_id' => ['required', Rule::exists('central.languages', 'id')],
            'group' => 'required|string|max:50',
            'key' => 'required|string|max:255',
            'value' => 'required|string|max:5000',
        ]);

        Translation::updateOrCreate(
            [
                'language_id' => $validated['language_id'],
                'group' => $validated['group'],
                'key' => $validated['key'],
            ],
            ['value' => $validated['value']]
        );

        return back()->with('success', 'Çeviri kaydedildi.');
    }

    public function deleteTranslation(Translation $translation)
    {
        $translation->delete();

        return back()->with('success', 'Çeviri silindi.');
    }
}
