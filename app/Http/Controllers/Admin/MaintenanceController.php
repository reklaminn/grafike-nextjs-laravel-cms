<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Language;
use App\Models\Page;
use App\Models\SeoEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MaintenanceController extends Controller
{
    public function index()
    {
        // Orphan detection
        $orphans = [];

        // Pages without language
        // Language is on central DB — whereDoesntHave() generates a cross-DB subquery
        // that fails when tenant connection is active. Fetch valid IDs first instead.
        $validLanguageIds = Language::pluck('id');
        $orphans['pages_no_language'] = Page::whereNotNull('language_id')
            ->whereNotIn('language_id', $validLanguageIds)
            ->count();

        // Articles without page
        $orphans['articles_no_page'] = Article::whereDoesntHave('page')
            ->count();

        // SEO entries without target
        $orphans['seo_no_target'] = SeoEntry::where(function ($q) {
            $q->where('seoable_type', 'App\\Models\\Page')
                ->whereNotIn('seoable_id', Page::select('id'));
        })->orWhere(function ($q) {
            $q->where('seoable_type', 'App\\Models\\Article')
                ->whereNotIn('seoable_id', Article::select('id'));
        })->count();

        // Unused media
        $orphans['unused_media'] = Media::whereNull('model_id')->count();

        // Soft-deleted records
        $orphans['trashed_pages'] = Page::onlyTrashed()->count();
        $orphans['trashed_articles'] = Article::onlyTrashed()->count();

        // Total
        $orphans['total'] = array_sum($orphans);

        // DB stats
        $stats = [
            'pages' => Page::count(),
            'articles' => Article::count(),
            'seo_entries' => SeoEntry::count(),
            'media' => Media::count(),
        ];

        return view('admin.maintenance.index', compact('orphans', 'stats'));
    }

    public function cleanup(Request $request)
    {
        $request->validate([
            'type' => 'required|in:pages_no_language,articles_no_page,seo_no_target,unused_media,trashed_pages,trashed_articles',
        ]);

        $type = $request->type;
        $count = 0;

        DB::transaction(function () use ($type, &$count) {
            switch ($type) {
                case 'pages_no_language':
                    $validLanguageIds = Language::pluck('id');
                    $count = Page::whereNotNull('language_id')
                        ->whereNotIn('language_id', $validLanguageIds)
                        ->forceDelete();
                    break;

                case 'articles_no_page':
                    $count = Article::whereDoesntHave('page')
                        ->forceDelete();
                    break;

                case 'seo_no_target':
                    $count = SeoEntry::where(function ($q) {
                        $q->where('seoable_type', 'App\\Models\\Page')
                            ->whereNotIn('seoable_id', Page::select('id'));
                    })->orWhere(function ($q) {
                        $q->where('seoable_type', 'App\\Models\\Article')
                            ->whereNotIn('seoable_id', Article::select('id'));
                    })->delete();
                    break;

                case 'unused_media':
                    $count = Media::whereNull('model_id')->delete();
                    break;

                case 'trashed_pages':
                    $count = Page::onlyTrashed()->forceDelete();
                    break;

                case 'trashed_articles':
                    $count = Article::onlyTrashed()->forceDelete();
                    break;
            }
        });

        return redirect()
            ->route('admin.maintenance.index')
            ->with('success', "{$count} kayıt temizlendi.");
    }
}
