<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\FormSubmission;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_pages'          => Page::count(),
            'published_pages'      => Page::where('status', 'published')->count(),
            'total_articles'       => Article::count(),
            'published_articles'   => Article::where('status', 'published')->count(),
            'total_media'          => Media::count(),
            'total_media_size'     => Media::sum('size'),
            'new_submissions'      => FormSubmission::where('status', 'new')->count(),
            'today_submissions'    => FormSubmission::whereDate('created_at', today())->count(),
        ];

        $health = $this->systemHealth();

        $recentPages = Page::latest('updated_at')->limit(5)->get(['id', 'title', 'status', 'updated_at']);
        $recentArticles = Article::latest('updated_at')->limit(5)->get(['id', 'title', 'status', 'updated_at']);

        return view('admin.dashboard', compact('stats', 'health', 'recentPages', 'recentArticles'));
    }

    // ─── System health ─────────────────────────────────────────────────────────

    private function systemHealth(): array
    {
        return [
            'database' => $this->checkDatabase(),
            'storage'  => $this->checkStorage(),
            'queue'    => $this->checkQueue(),
            'cache'    => $this->checkCache(),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'ok', 'label' => 'Veritabanı', 'detail' => 'Bağlı'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'label' => 'Veritabanı', 'detail' => 'Bağlantı hatası'];
        }
    }

    private function checkStorage(): array
    {
        try {
            $writable = is_writable(storage_path('app'));
            return [
                'status' => $writable ? 'ok' : 'warning',
                'label'  => 'Depolama',
                'detail' => $writable ? 'Yazılabilir' : 'Yazma izni yok',
            ];
        } catch (Throwable) {
            return ['status' => 'error', 'label' => 'Depolama', 'detail' => 'Kontrol edilemiyor'];
        }
    }

    private function checkQueue(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed  = DB::table('failed_jobs')->count();

            if ($failed > 0) {
                return ['status' => 'warning', 'label' => 'Kuyruk', 'detail' => "{$pending} bekliyor, {$failed} başarısız"];
            }

            return ['status' => 'ok', 'label' => 'Kuyruk', 'detail' => "{$pending} iş bekliyor"];
        } catch (Throwable) {
            // jobs table might not exist yet
            return ['status' => 'ok', 'label' => 'Kuyruk', 'detail' => 'Kontrol edilemiyor'];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'dashboard.health.ping.' . now()->timestamp;
            Cache::put($key, true, 5);
            $ok = Cache::get($key) === true;
            Cache::forget($key);

            return [
                'status' => $ok ? 'ok' : 'warning',
                'label'  => 'Önbellek',
                'detail' => $ok ? ucfirst(config('cache.default')) . ' çalışıyor' : 'Okuma/yazma hatası',
            ];
        } catch (Throwable) {
            return ['status' => 'error', 'label' => 'Önbellek', 'detail' => 'Bağlantı hatası'];
        }
    }
}
