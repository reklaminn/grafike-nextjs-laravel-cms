@extends('admin.layouts.app')

@section('title', 'Düzenle: ' . $page->title)
@section('page-title')
    Sayfayı Düzenle
    <span class="text-gray-400 font-normal text-sm ml-2">#{{ $page->id }}</span>
@endsection

@section('content')
    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4">
            <div class="flex items-center gap-2 font-semibold text-red-800 mb-2">
                <i class="fas fa-circle-exclamation"></i> Kayıt başarısız — lütfen hataları düzeltin:
            </div>
            <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Use a relative URL (no scheme) so the form always submits to the current
         origin (HTTPS) regardless of what route() generates for the scheme. --}}
    <form method="POST" action="{{ route('admin.pages.update', $page, false) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.pages._form')
    </form>

    @include('admin.pages._form.preview-panel')

    {{-- Sub-pages section --}}
    @if($page->children->count() > 0)
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">
                <i class="fas fa-sitemap mr-2 text-indigo-500"></i>
                Alt Sayfalar ({{ $page->children->count() }})
            </h3>
            <div class="space-y-2">
                @foreach($page->children->sortBy('sort_order') as $child)
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full {{ $child->status === 'published' ? 'bg-green-400' : 'bg-yellow-400' }}"></span>
                            <a href="{{ route('admin.pages.edit', $child) }}" class="text-sm text-gray-700 hover:text-indigo-600">
                                {{ $child->title }}
                            </a>
                        </div>
                        <span class="text-xs text-gray-400">/{{ $child->slug }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @php
        $activeFrontendBlocks = collect($frontendEditorSections ?? [])
            ->filter(fn (array $block) => ($block['is_active'] ?? true) === true)
            ->values();

        $activeArticleListBlocks = $activeFrontendBlocks
            ->filter(fn (array $block) => ($block['type'] ?? null) === 'article-list')
            ->values();
    @endphp

    {{-- Articles section --}}
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800">
                <i class="fas fa-newspaper mr-2 text-green-500"></i>
                Bu Sayfadaki Yazılar
            </h3>
            <a href="{{ route('admin.articles.create', ['page_id' => $page->id]) }}"
               class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-50 text-green-700 text-xs font-medium rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-plus"></i> Yazı Ekle
            </a>
        </div>
        @php $pageArticles = $page->articles()->latest()->limit(10)->get(); @endphp
        @forelse($pageArticles as $article)
            <div class="flex items-center justify-between px-4 py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <div class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full {{ $article->status === 'published' ? 'bg-green-400' : 'bg-yellow-400' }}"></span>
                    <a href="{{ route('admin.articles.edit', $article) }}" class="text-sm text-gray-700 hover:text-indigo-600">
                        {{ \Illuminate\Support\Str::limit($article->title, 50) }}
                    </a>
                </div>
                <span class="text-xs text-gray-400">{{ $article->created_at?->diffForHumans() }}</span>
            </div>
        @empty
            @php
                $usesArticleListSection = \App\Support\FrontendSections::containsType($page->sections_json, 'article-list');
            @endphp
            @if($usesArticleListSection && isset($siteArticles) && $siteArticles->count() > 0)
                <div class="space-y-3">
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                        Bu sayfa doğrudan <code>page->articles()</code> ilişkisini değil, <strong>article-list</strong> section'ını kullanıyor.
                        Yani frontend'de gördüğün kartlar bu sayfaya bağlı yazılar değil, aynı site içindeki seed edilmiş yazılardan geliyor.
                    </div>
                    @if($activeArticleListBlocks->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach($activeArticleListBlocks as $block)
                                <button type="button"
                                        onclick="window.dispatchEvent(new CustomEvent('frontend-block-focus', { detail: { blockId: '{{ $block['id'] ?? '' }}' } }))"
                                        class="inline-flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700 hover:bg-amber-100">
                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                    {{ $block['template_name'] ?? 'Article List' }} bloğuna git
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @foreach($siteArticles as $article)
                        <div class="flex items-center justify-between px-4 py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                            <div class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full {{ $article->status === 'published' ? 'bg-green-400' : 'bg-yellow-400' }}"></span>
                                <a href="{{ route('admin.articles.edit', $article) }}" class="text-sm text-gray-700 hover:text-indigo-600">
                                    {{ \Illuminate\Support\Str::limit($article->title, 50) }}
                                </a>
                            </div>
                            <span class="text-xs text-gray-400">{{ $article->page?->title ?? '—' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400 py-4 text-center">Bu sayfada henüz yazı bulunmuyor.</p>
            @endif
        @endforelse
    </div>

    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800">
                <i class="fas fa-cubes mr-2 text-amber-500"></i>
                Aktif Bloklar
            </h3>
            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                {{ $activeFrontendBlocks->count() }} aktif block
            </span>
        </div>

        <p class="mb-4 text-xs text-gray-500">
            Bu özet, sayfada yeni builder ile kullanılan tüm aktif block’ları tek yerde gösterir.
        </p>

        @forelse($activeFrontendBlocks as $block)
            <div class="flex items-start justify-between gap-4 px-4 py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <div class="min-w-0">
                    <button type="button"
                            onclick="window.dispatchEvent(new CustomEvent('frontend-block-focus', { detail: { blockId: '{{ $block['id'] ?? '' }}' } }))"
                            class="text-left text-sm font-medium text-gray-800 hover:text-amber-700">
                        {{ $block['template_name'] ?? \Illuminate\Support\Str::headline($block['type'] ?? 'Block') }}
                    </button>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                        <span class="rounded-full bg-gray-100 px-2 py-0.5">{{ $block['region'] ?? 'body' }}</span>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5">{{ $block['type'] ?? 'unknown' }}</span>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5">{{ $block['variation'] ?? 'default' }}</span>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5">{{ $block['render_mode'] ?? 'html' }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button"
                            onclick="window.dispatchEvent(new CustomEvent('frontend-block-focus', { detail: { blockId: '{{ $block['id'] ?? '' }}' } }))"
                            class="inline-flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700 hover:bg-amber-100">
                        <i class="fas fa-arrow-up-right-from-square"></i>
                        Bloğa git
                    </button>
                    <div class="text-right text-xs text-gray-400">
                    <div>{{ $block['row_id'] ?? 'row' }}</div>
                    <div>{{ $block['column_id'] ?? 'column' }}</div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400 py-4 text-center">Bu sayfada henüz aktif block bulunmuyor.</p>
        @endforelse
    </div>
@endsection
