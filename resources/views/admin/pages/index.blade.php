@extends('admin.layouts.app')

@section('title', 'Sayfalar')
@section('page-title', 'Sayfalar')

@section('content')
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-gray-500">Tüm sayfaları yönetin, düzenleyin ve yeni sayfalar ekleyin.</p>
        </div>
        <a href="{{ route('admin.pages.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus"></i>
            Yeni Sayfa
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row items-end gap-4">
            <div class="flex-1 w-full">
                <label class="block text-xs font-medium text-gray-500 mb-1">Ara</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Sayfa adı ile ara..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div class="w-full sm:w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">Durum</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tümü</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Yayında</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Taslak</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Arşivlenmiş</option>
                </select>
            </div>
            <div class="w-full sm:w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">Dil</label>
                <select name="language_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tüm Diller</option>
                    @foreach($languages as $lang)
                        <option value="{{ $lang->id }}" {{ request('language_id') == $lang->id ? 'selected' : '' }}>
                            {{ $lang->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-search mr-1"></i> Filtrele
                </button>
                @if(request()->hasAny(['search', 'status', 'language_id']))
                    <a href="{{ route('admin.pages.index') }}"
                       class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Temizle</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Pages Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sayfa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dil</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Yazı</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Güncelleme</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pages as $page)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($page->getFirstMediaUrl('cover', 'thumb'))
                                        <img src="{{ $page->getFirstMediaUrl('cover', 'thumb') }}"
                                             class="w-10 h-10 rounded-lg object-cover" alt="">
                                    @else
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-file-alt text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.pages.edit', $page) }}"
                                           class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                            {{ $page->title }}
                                        </a>
                                        <p class="text-xs text-gray-400">/{{ $page->slug }}</p>
                                        @if($page->isSystemPage())
                                            <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700">
                                                <i class="fas fa-lock"></i>
                                                {{ $page->system_key === 'home' ? 'Default Anasayfa' : 'Sistem Sayfası' }}
                                            </span>
                                        @endif
                                        @if($page->parent)
                                            <p class="text-xs text-gray-400">
                                                <i class="fas fa-level-up-alt fa-rotate-90 mr-1"></i>{{ $page->parent->title }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @switch($page->status)
                                    @case('published')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Yayında
                                        </span>
                                        @break
                                    @case('draft')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span> Taslak
                                        </span>
                                        @break
                                    @case('archived')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Arşiv
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">{{ $page->language?->name ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm text-gray-600">{{ $page->articles_count }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-500">{{ $page->updated_at?->diffForHumans() }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.pages.edit', $page) }}"
                                       class="p-2 text-gray-400 hover:text-indigo-600 transition-colors"
                                       title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($page->isSystemPage())
                                        <span class="p-2 text-gray-300" title="Sistem sayfası silinemez">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}"
                                              onsubmit="return confirm('Bu sayfayı silmek istediğinize emin misiniz?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                                                    title="Sil">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-file-alt text-4xl mb-3"></i>
                                    <p class="text-sm">Henüz sayfa bulunmuyor.</p>
                                    <a href="{{ route('admin.pages.create') }}"
                                       class="inline-flex items-center gap-1 mt-3 text-sm text-indigo-600 hover:text-indigo-700">
                                        <i class="fas fa-plus"></i> İlk sayfanızı oluşturun
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($pages->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $pages->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
