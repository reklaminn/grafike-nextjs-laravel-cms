@extends('admin.layouts.app')
@section('title', 'Gemiler')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gemiler</h1>
        <p class="text-sm text-gray-500">Cruise + feribot gemileri envanteri.  Her gemi bir firmaya bağlı, kabin envanteri buradan beslenir.</p>
    </div>
    <a href="{{ route('admin.ships.create') }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
        <i class="fas fa-plus mr-1"></i> Yeni Gemi
    </a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl shadow-sm border p-4 mb-4 flex gap-3 items-end">
    <div class="flex-1">
        <label class="block text-xs uppercase font-semibold text-gray-500 mb-1">Ara</label>
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="gemi adı / slug / IMO"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div class="w-56">
        <label class="block text-xs uppercase font-semibold text-gray-500 mb-1">Firma</label>
        <select name="company" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">— Hepsi —</option>
            @foreach($companies as $company)
                <option value="{{ $company->id }}" {{ ($filters['company'] ?? '') == $company->id ? 'selected' : '' }}>
                    {{ $company->name }}
                </option>
            @endforeach
        </select>
    </div>
    <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">Filtrele</button>
</form>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($ships->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">Gemi bulunamadı.</div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Kapak</th>
                    <th class="px-4 py-3">Gemi</th>
                    <th class="px-4 py-3">Firma</th>
                    <th class="px-4 py-3">Bayrak</th>
                    <th class="px-4 py-3">Yıldız</th>
                    <th class="px-4 py-3">Kabin</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 text-right">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($ships as $ship)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            @if($coverUrl = $ship->getFirstMediaUrl('cover'))
                                <img src="{{ $coverUrl }}" alt="{{ $ship->name }}" class="h-10 w-16 object-cover rounded">
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-gray-800 font-medium">{{ $ship->name }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ $ship->slug }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $ship->company?->name }}</td>
                        <td class="px-4 py-3">
                            @if($ship->flagUrl())
                                <img src="{{ $ship->flagUrl() }}" alt="{{ $ship->flag_country_code }}"
                                     class="w-7 h-5 rounded shadow-sm border border-gray-200">
                            @endif
                        </td>
                        <td class="px-4 py-3 text-amber-500">
                            @for($i = 0; $i < (int) ($ship->star_rating ?? 0); $i++)<i class="fas fa-star text-xs"></i>@endfor
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $ship->cabins_count }}</td>
                        <td class="px-4 py-3">
                            @if($ship->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">Aktif</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 text-xs font-medium">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.ships.edit', $ship) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Düzenle</a>
                            <form action="{{ route('admin.ships.destroy', $ship) }}" method="POST" class="inline ml-3"
                                  onsubmit="return confirm('Gemiyi silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Sil</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $ships->links() }}</div>
    @endif
</div>
@endsection
