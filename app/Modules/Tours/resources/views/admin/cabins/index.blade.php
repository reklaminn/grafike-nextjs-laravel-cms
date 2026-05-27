@extends('admin.layouts.app')
@section('title', 'Kabinler')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kabinler</h1>
        <p class="text-sm text-gray-500">Gemilerin kabin envanteri.  Tour pricing matrix'i (Tab 3) bu kabinlere referans verir.</p>
    </div>
    <a href="{{ route('admin.cabins.create') }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
        <i class="fas fa-plus mr-1"></i> Yeni Kabin
    </a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl shadow-sm border p-4 mb-4 flex gap-3 items-end flex-wrap">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs uppercase font-semibold text-gray-500 mb-1">Ara</label>
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="kod / deck / alt kategori"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div class="w-56">
        <label class="block text-xs uppercase font-semibold text-gray-500 mb-1">Gemi</label>
        <select name="ship" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">— Hepsi —</option>
            @foreach($ships as $ship)
                <option value="{{ $ship->id }}" {{ ($filters['ship'] ?? '') == $ship->id ? 'selected' : '' }}>
                    {{ $ship->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="w-48">
        <label class="block text-xs uppercase font-semibold text-gray-500 mb-1">Kategori</label>
        <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">— Hepsi —</option>
            @foreach($categories as $cat)
                @php $tr = $defaultLanguage ? $cat->translationFor($defaultLanguage->id) : $cat->translations->first(); @endphp
                <option value="{{ $cat->id }}" {{ ($filters['category'] ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $tr->name ?? $cat->slug }}
                </option>
            @endforeach
        </select>
    </div>
    <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">Filtrele</button>
</form>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($cabins->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">Kabin bulunamadı.</div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Kabin</th>
                    <th class="px-4 py-3">Gemi</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Alt Kategori</th>
                    <th class="px-4 py-3">Deck</th>
                    <th class="px-4 py-3">Kapasite</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 text-right">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($cabins as $cabin)
                    @php
                        $tr = $defaultLanguage ? $cabin->translationFor($defaultLanguage->id) : $cabin->translations->first();
                        $catTr = $defaultLanguage ? $cabin->category?->translationFor($defaultLanguage->id) : $cabin->category?->translations->first();
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="text-gray-800 font-medium">{{ $tr->name ?? '—' }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ $cabin->code }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $cabin->ship?->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $catTr->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $cabin->brand_subcategory }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $cabin->deck_name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $cabin->max_capacity }}</td>
                        <td class="px-4 py-3">
                            @if($cabin->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">Aktif</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 text-xs font-medium">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.cabins.edit', $cabin) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Düzenle</a>
                            <form action="{{ route('admin.cabins.destroy', $cabin) }}" method="POST" class="inline ml-3"
                                  onsubmit="return confirm('Kabini silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Sil</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $cabins->links() }}</div>
    @endif
</div>
@endsection
