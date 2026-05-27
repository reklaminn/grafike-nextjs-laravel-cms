@extends('admin.layouts.app')
@section('title', 'Limanlar')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Limanlar</h1>
        <p class="text-sm text-gray-500">Cruise/feribot rotalarında kullanılan liman master listesi.</p>
    </div>
    <a href="{{ route('admin.ports.create') }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
        <i class="fas fa-plus mr-1"></i> Yeni Liman
    </a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl shadow-sm border p-4 mb-4 flex gap-3 items-end">
    <div class="flex-1">
        <label class="block text-xs uppercase font-semibold text-gray-500 mb-1">Ara</label>
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="liman slug…"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>
    <div class="w-32">
        <label class="block text-xs uppercase font-semibold text-gray-500 mb-1">Ülke kodu</label>
        <input type="text" name="country" value="{{ $filters['country'] ?? '' }}" placeholder="TR"
               maxlength="2"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase font-mono">
    </div>
    <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">Filtrele</button>
</form>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($ports->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">Liman bulunamadı.</div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3"></th>
                    <th class="px-4 py-3">Liman</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Koordinat</th>
                    <th class="px-4 py-3">Destinasyon</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 text-right">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($ports as $port)
                    @php
                        $tr = $defaultLanguage ? $port->translationFor($defaultLanguage->id) : $port->translations->first();
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            @if($port->flagUrl())
                                <img src="{{ $port->flagUrl() }}" alt="{{ $port->country_code }}"
                                     class="w-7 h-5 rounded shadow-sm border border-gray-200" loading="lazy">
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-800 font-medium">{{ $tr->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $port->slug }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500 font-mono">
                            @if($port->hasGeo())
                                {{ number_format((float) $port->latitude, 4) }}, {{ number_format((float) $port->longitude, 4) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $port->destinations_count ?? 0 }}</td>
                        <td class="px-4 py-3">
                            @if($port->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">Aktif</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 text-xs font-medium">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.ports.edit', $port) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Düzenle</a>
                            <form action="{{ route('admin.ports.destroy', $port) }}" method="POST" class="inline ml-3"
                                  onsubmit="return confirm('Limanı silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Sil</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $ports->links() }}</div>
    @endif
</div>
@endsection
