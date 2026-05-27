@extends('admin.layouts.app')
@section('title', 'Destinasyonlar')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Destinasyonlar</h1>
        <p class="text-sm text-gray-500">Geo-aggregated tur grupları — Yunan Adaları, Akdeniz, Karayipler vs.</p>
    </div>
    <a href="{{ route('admin.destinations.create') }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
        <i class="fas fa-plus mr-1"></i> Yeni Destinasyon
    </a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($destinations->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">Henüz destinasyon yok.</div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Destinasyon</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Limanlar</th>
                    <th class="px-4 py-3">Tur Tipleri</th>
                    <th class="px-4 py-3">Öne Çıkan</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 text-right">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($destinations as $destination)
                    @php
                        $tr = $defaultLanguage ? $destination->translationFor($defaultLanguage->id) : $destination->translations->first();
                        $tourTypes = $destination->compatible_tour_types ?: ['cruise','package','daily','ferry'];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-800 font-medium">{{ $tr->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $destination->slug }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $destination->ports_count }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-500">{{ implode(', ', $tourTypes) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($destination->is_featured)
                                <i class="fas fa-star text-amber-400"></i>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($destination->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">Aktif</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 text-xs font-medium">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.destinations.edit', $destination) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Düzenle</a>
                            <form action="{{ route('admin.destinations.destroy', $destination) }}" method="POST" class="inline ml-3"
                                  onsubmit="return confirm('Destinasyonu silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Sil</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $destinations->links() }}</div>
    @endif
</div>
@endsection
