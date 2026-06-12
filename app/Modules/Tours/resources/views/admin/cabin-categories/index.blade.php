@extends('admin.layouts.app')
@section('title', 'Kabin Kategorileri')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kabin Kategorileri</h1>
        <p class="text-sm text-gray-500">
            Endüstri standart kabin tipleri (Inside, Outside, Balcony, Ocean View, Suite).
            Geminin tüm kabinleri bunlardan birine bağlanır.
        </p>
    </div>
    <a href="{{ route('admin.cabin-categories.create') }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
        <i class="fas fa-plus mr-1"></i> Yeni Kategori
    </a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($categories->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">Henüz kategori yok.</div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">İkon</th>
                    <th class="px-4 py-3">Kabin Sayısı</th>
                    <th class="px-4 py-3">Sıra</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 text-right">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($categories as $category)
                    @php
                        $tr = $defaultLanguage ? $category->translationFor($defaultLanguage->id) : $category->translations->first();
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-800 font-medium">{{ $tr->name ?? '— çevirisi yok —' }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $category->slug }}</td>
                        <td class="px-4 py-3">
                            @if($category->icon)
                                <i class="fas {{ $category->icon }} text-gray-400"></i>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $category->cabins_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3">
                            @if($category->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">Aktif</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 text-xs font-medium">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.cabin-categories.edit', $category) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Düzenle</a>
                            <form action="{{ route('admin.cabin-categories.destroy', $category) }}" method="POST" class="inline ml-3"
                                  onsubmit="return confirm('Kategoriyi silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Sil</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $categories->links() }}</div>
    @endif
</div>
@endsection
