@extends('admin.layouts.app')

@section('title', 'Menüler')
@section('page-title', 'Menüler')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Navigasyon menülerini yönetin.</p>
        <a href="{{ route('admin.menus.create', [], false) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus"></i> Yeni Menü
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($menus as $menu)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">{{ $menu->name }}</h3>
                        <p class="text-sm text-gray-400 mt-1">
                            <i class="fas fa-map-marker-alt mr-1"></i>{{ $menu->location }}
                            <span class="mx-2">·</span>
                            <i class="fas fa-language mr-1"></i>{{ $menu->language?->name ?? '-' }}
                        </p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $menu->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $menu->is_active ? 'Aktif' : 'Pasif' }}
                    </span>
                </div>

                <div class="mt-4 flex items-center gap-4 text-sm text-gray-500">
                    <span><i class="fas fa-list mr-1"></i> {{ $menu->items_count }} öğe</span>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ route('admin.menus.edit', $menu->id, false) }}"
                       class="flex-1 text-center px-3 py-2 bg-indigo-50 text-indigo-700 text-sm rounded-lg hover:bg-indigo-100 transition-colors">
                        <i class="fas fa-edit mr-1"></i> Düzenle
                    </a>
                    <form method="POST" action="{{ route('admin.menus.destroy', $menu->id, false) }}"
                          onsubmit="return confirm('Bu menüyü silmek istediğinize emin misiniz?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-400">
                <i class="fas fa-bars text-4xl mb-3"></i>
                <p>Henüz menü bulunmuyor.</p>
            </div>
        @endforelse
    </div>
@endsection
