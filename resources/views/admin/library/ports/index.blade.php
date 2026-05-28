@extends('admin.layouts.app')

@section('title', 'Kütüphane — Limanlar')
@section('page-title', 'Kütüphane — Limanlar')

@section('content')
<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('admin.library.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
    <h1 class="text-xl font-bold text-gray-800">Limanlar</h1>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif

<div class="bg-white rounded-xl shadow-sm border">
    <div class="flex items-center justify-between px-6 py-4 border-b gap-3">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Slug ara…"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-48">
            <input type="text" name="country" value="{{ $filters['country'] ?? '' }}" placeholder="Ülke (TR)" maxlength="2"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-24 uppercase">
            <button class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200"><i class="fas fa-search"></i></button>
        </form>
        <a href="{{ route('admin.library.ports.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus"></i> Yeni Liman
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ad</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Slug</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Ülke</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Koordinat</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Durum</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($ports as $port)
                    @php $name = $port->translations->first()?->name ?? $port->slug; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm font-medium text-gray-800">
                            @if($port->country_code)<img src="https://flagcdn.com/16x12/{{ strtolower($port->country_code) }}.png" class="inline-block mr-2 align-middle" alt="">@endif
                            {{ $name }}
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500 font-mono">{{ $port->slug }}</td>
                        <td class="px-6 py-3 text-sm text-center text-gray-500">{{ $port->country_code ?? '—' }}</td>
                        <td class="px-6 py-3 text-xs text-center text-gray-400">
                            {{ $port->latitude && $port->longitude ? round($port->latitude, 3) . ', ' . round($port->longitude, 3) : '—' }}
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="text-xs px-2 py-1 rounded {{ $port->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $port->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.library.ports.edit', $port) }}" class="text-indigo-600 hover:text-indigo-800 px-2"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('admin.library.ports.destroy', $port) }}" class="inline"
                                  onsubmit="return confirm('Bu liman kütüphaneden silinsin mi? (Tenant kopyaları etkilenmez)')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700 px-2"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">Henüz kütüphane limanı yok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">{{ $ports->links() }}</div>
</div>
@endsection
