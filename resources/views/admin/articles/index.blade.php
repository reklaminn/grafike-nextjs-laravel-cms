@extends('admin.layouts.app')

@section('title', 'Yazılar')
@section('page-title', 'Yazılar')

@section('content')
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 flex items-center gap-3 text-green-800 text-sm font-medium">
            <i class="fas fa-circle-check text-green-500"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <p class="text-sm text-gray-500">
            Tüm yazıları ve makaleleri yönetin.
            <span class="ml-1 font-medium text-gray-700">{{ $articles->total() }} yazı</span>
        </p>
        <a href="{{ route('admin.articles.create', [], false) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus"></i> Yeni Yazı
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Ara</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Yazı başlığı ile ara…"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div class="w-36">
                <label class="block text-xs font-medium text-gray-500 mb-1">Durum</label>
                <select name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tümü</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Yayında</option>
                    <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Taslak</option>
                    <option value="archived"  {{ request('status') === 'archived'  ? 'selected' : '' }}>Arşivlenmiş</option>
                </select>
            </div>

            <div class="w-44">
                <label class="block text-xs font-medium text-gray-500 mb-1">Sayfa</label>
                <select name="page_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tüm Sayfalar</option>
                    @foreach($pages as $page)
                        <option value="{{ $page->id }}" {{ request('page_id') == $page->id ? 'selected' : '' }}>
                            {{ \Illuminate\Support\Str::limit($page->title, 30) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-36">
                <label class="block text-xs font-medium text-gray-500 mb-1">Dil</label>
                <select name="language_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tüm Diller</option>
                    @foreach($languages as $lang)
                        <option value="{{ $lang->id }}" {{ request('language_id') == $lang->id ? 'selected' : '' }}>
                            {{ $lang->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-36">
                <label class="block text-xs font-medium text-gray-500 mb-1">Öne Çıkan</label>
                <select name="is_featured"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tümü</option>
                    <option value="1" {{ request('is_featured') === '1' ? 'selected' : '' }}>Öne Çıkan</option>
                    <option value="0" {{ request('is_featured') === '0' ? 'selected' : '' }}>Standart</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-search mr-1"></i> Filtrele
                </button>
                @if(request()->hasAny(['search','status','page_id','language_id','is_featured']))
                    <a href="{{ route('admin.articles.index', [], false) }}"
                       class="px-3 py-2 bg-gray-100 text-gray-600 text-sm rounded-lg hover:bg-gray-200" title="Filtreleri temizle">
                        <i class="fas fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Articles Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Yazı</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Sayfa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Dil</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Yazar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Tarih</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wide">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($articles as $article)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    {{-- Thumbnail --}}
                                    @if($article->getFirstMediaUrl('cover'))
                                        <img src="{{ $article->getFirstMediaUrl('cover') }}"
                                             class="w-10 h-10 rounded-lg object-cover flex-shrink-0" alt="">
                                    @else
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-newspaper text-gray-400 text-sm"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.articles.edit', $article->id, false) }}"
                                           class="text-sm font-medium text-gray-900 hover:text-indigo-600 block truncate max-w-xs">
                                            {{ $article->title }}
                                        </a>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            @if($article->is_featured)
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full">
                                                    <i class="fas fa-star text-xs"></i> Öne Çıkan
                                                </span>
                                            @endif
                                            @if($article->slug)
                                                <span class="text-xs text-gray-400 truncate">/{{ $article->slug }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">
                                    {{ $article->page ? \Illuminate\Support\Str::limit($article->page->title, 25) : '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($article->language)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                        {{ $article->language->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php $status = $article->status; @endphp
                                @if($status === 'published')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Yayında
                                    </span>
                                @elseif($status === 'archived')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Arşiv
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span> Taslak
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $article->author?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $article->updated_at?->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.articles.edit', $article->id, false) }}"
                                       class="p-2 text-gray-400 hover:text-indigo-600" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.articles.destroy', $article->id, false) }}"
                                          onsubmit="return confirm('Bu yazıyı silmek istediğinize emin misiniz?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600" title="Sil">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <i class="fas fa-newspaper text-4xl mb-3 block"></i>
                                <p class="text-sm">Henüz yazı bulunmuyor.</p>
                                @if(request()->hasAny(['search','status','page_id','language_id','is_featured']))
                                    <a href="{{ route('admin.articles.index', [], false) }}"
                                       class="mt-2 inline-block text-indigo-600 text-sm hover:underline">Filtreleri temizle</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($articles->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $articles->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
