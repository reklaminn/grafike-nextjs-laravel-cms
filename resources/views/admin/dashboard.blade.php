@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    {{-- ── Stat Cards ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

        {{-- Pages --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Sayfalar</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ number_format($stats['total_pages'] ?? 0) }}</p>
                    <p class="mt-0.5 text-xs text-gray-400">{{ number_format($stats['published_pages'] ?? 0) }} yayında</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 ml-3">
                    <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Articles --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Yazılar</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ number_format($stats['total_articles'] ?? 0) }}</p>
                    <p class="mt-0.5 text-xs text-gray-400">{{ number_format($stats['published_articles'] ?? 0) }} yayında</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0 ml-3">
                    <i class="fas fa-newspaper text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Media --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Medya</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ number_format($stats['total_media'] ?? 0) }}</p>
                    @php
                        $bytes = $stats['total_media_size'] ?? 0;
                        $humanSize = match(true) {
                            $bytes >= 1073741824 => round($bytes / 1073741824, 1) . ' GB',
                            $bytes >= 1048576    => round($bytes / 1048576, 1) . ' MB',
                            $bytes >= 1024       => round($bytes / 1024, 0) . ' KB',
                            default              => $bytes . ' B',
                        };
                    @endphp
                    <p class="mt-0.5 text-xs text-gray-400">{{ $humanSize }} toplam</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0 ml-3">
                    <i class="fas fa-photo-video text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Form Submissions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Mesajlar</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ number_format($stats['new_submissions'] ?? 0) }}</p>
                    <p class="mt-0.5 text-xs text-gray-400">
                        {{ number_format($stats['today_submissions'] ?? 0) }} bugün
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0 ml-3">
                    <i class="fas fa-envelope text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- ── System Health + Quick Actions ────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- System Health --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-heartbeat text-gray-400 text-sm"></i>
                Sistem Durumu
            </h2>
            <div class="space-y-3">
                @foreach($health as $check)
                    @php
                        $dot = match($check['status']) {
                            'ok'      => 'bg-green-400',
                            'warning' => 'bg-yellow-400',
                            default   => 'bg-red-400',
                        };
                        $badge = match($check['status']) {
                            'ok'      => 'text-green-700 bg-green-50 ring-green-200',
                            'warning' => 'text-yellow-700 bg-yellow-50 ring-yellow-200',
                            default   => 'text-red-700 bg-red-50 ring-red-200',
                        };
                        $label = match($check['status']) {
                            'ok'      => 'Tamam',
                            'warning' => 'Uyarı',
                            default   => 'Hata',
                        };
                    @endphp
                    <div class="grid grid-cols-[minmax(100px,1fr)_auto] items-center gap-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $dot }} flex-shrink-0"></span>
                            <span class="truncate text-sm text-gray-700">{{ $check['label'] }}</span>
                        </div>
                        <div class="flex min-w-0 items-center justify-end gap-2">
                            <span class="max-w-[150px] truncate text-right text-xs text-gray-400" title="{{ $check['detail'] }}">
                                {{ $check['detail'] }}
                            </span>
                            <span class="inline-flex shrink-0 items-center rounded-md px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $badge }}">
                                {{ $label }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-bolt text-gray-400 text-sm"></i>
                Hızlı İşlemler
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a href="{{ route('admin.pages.create') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-lg border-2 border-dashed border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors">
                    <i class="fas fa-plus-circle text-2xl text-indigo-500"></i>
                    <span class="text-sm font-medium text-gray-700 text-center">Yeni Sayfa</span>
                </a>
                <a href="{{ route('admin.articles.create') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-lg border-2 border-dashed border-gray-200 hover:border-green-300 hover:bg-green-50 transition-colors">
                    <i class="fas fa-pen-fancy text-2xl text-green-500"></i>
                    <span class="text-sm font-medium text-gray-700 text-center">Yeni Yazı</span>
                </a>
                <a href="{{ route('admin.media.index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-lg border-2 border-dashed border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-colors">
                    <i class="fas fa-images text-2xl text-purple-500"></i>
                    <span class="text-sm font-medium text-gray-700 text-center">Medya</span>
                </a>
                <a href="{{ route('admin.forms.index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-lg border-2 border-dashed border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-colors">
                    <i class="fas fa-inbox text-2xl text-orange-500"></i>
                    <span class="text-sm font-medium text-gray-700 text-center">Form Mesajları</span>
                </a>
                <a href="{{ route('admin.menus.index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-lg border-2 border-dashed border-gray-200 hover:border-yellow-300 hover:bg-yellow-50 transition-colors">
                    <i class="fas fa-bars text-2xl text-yellow-500"></i>
                    <span class="text-sm font-medium text-gray-700 text-center">Menüler</span>
                </a>
                <a href="{{ route('admin.section-templates.index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-lg border-2 border-dashed border-gray-200 hover:border-pink-300 hover:bg-pink-50 transition-colors">
                    <i class="fas fa-puzzle-piece text-2xl text-pink-500"></i>
                    <span class="text-sm font-medium text-gray-700 text-center">Blok Şablonlar</span>
                </a>
                <a href="{{ route('admin.redirects.index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-lg border-2 border-dashed border-gray-200 hover:border-teal-300 hover:bg-teal-50 transition-colors">
                    <i class="fas fa-random text-2xl text-teal-500"></i>
                    <span class="text-sm font-medium text-gray-700 text-center">Yönlendirmeler</span>
                </a>
                <a href="{{ route('admin.sitemap.index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-lg border-2 border-dashed border-gray-200 hover:border-cyan-300 hover:bg-cyan-50 transition-colors">
                    <i class="fas fa-sitemap text-2xl text-cyan-500"></i>
                    <span class="text-sm font-medium text-gray-700 text-center">Sitemap</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ── Recent Activity ──────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Pages --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-800">Son Güncellenen Sayfalar</h2>
                <a href="{{ route('admin.pages.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                    Tümü <i class="fas fa-arrow-right ml-0.5"></i>
                </a>
            </div>
            @forelse($recentPages as $page)
                <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($page->status === 'published')
                            <span class="w-2 h-2 rounded-full bg-green-400 flex-shrink-0"
                                  title="Yayında"></span>
                        @elseif($page->status === 'draft')
                            <span class="w-2 h-2 rounded-full bg-yellow-400 flex-shrink-0"
                                  title="Taslak"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"
                                  title="{{ $page->status }}"></span>
                        @endif
                        <a href="{{ route('admin.pages.edit', $page) }}"
                           class="text-sm text-gray-700 hover:text-indigo-600 truncate">
                            {{ \Illuminate\Support\Str::limit($page->title, 45) }}
                        </a>
                    </div>
                    <span class="text-xs text-gray-400 flex-shrink-0 ml-2">{{ $page->updated_at?->diffForHumans() }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-6 text-center">Henüz sayfa bulunmuyor.</p>
            @endforelse
        </div>

        {{-- Recent Articles --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-800">Son Güncellenen Yazılar</h2>
                <a href="{{ route('admin.articles.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                    Tümü <i class="fas fa-arrow-right ml-0.5"></i>
                </a>
            </div>
            @forelse($recentArticles as $article)
                <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($article->status === 'published')
                            <span class="w-2 h-2 rounded-full bg-green-400 flex-shrink-0"
                                  title="Yayında"></span>
                        @elseif($article->status === 'draft')
                            <span class="w-2 h-2 rounded-full bg-yellow-400 flex-shrink-0"
                                  title="Taslak"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"
                                  title="{{ $article->status }}"></span>
                        @endif
                        <a href="{{ route('admin.articles.edit', $article) }}"
                           class="text-sm text-gray-700 hover:text-indigo-600 truncate">
                            {{ \Illuminate\Support\Str::limit($article->title, 45) }}
                        </a>
                    </div>
                    <span class="text-xs text-gray-400 flex-shrink-0 ml-2">{{ $article->updated_at?->diffForHumans() }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-6 text-center">Henüz yazı bulunmuyor.</p>
            @endforelse
        </div>

    </div>

@endsection
