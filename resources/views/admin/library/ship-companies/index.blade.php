@extends('admin.layouts.app')

@section('title', 'Kütüphane — Gemi Firmaları')
@section('page-title', 'Kütüphane — Gemi Firmaları')

@section('content')
<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('admin.library.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
    <h1 class="text-xl font-bold text-gray-800">Gemi Firmaları</h1>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

<div class="bg-white rounded-xl shadow-sm border">
    <div class="flex items-center justify-between px-6 py-4 border-b gap-3">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Ara (slug / ad)…"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-64">
            <button class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200"><i class="fas fa-search"></i></button>
        </form>
        <a href="{{ route('admin.library.ship-companies.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus"></i> Yeni Firma
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ad</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tip</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Gemi</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Durum</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($companies as $company)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm font-medium text-gray-800">
                            {{ $company->name }}
                            @if($company->legacy_id)<span class="ml-2 text-[10px] text-gray-400">#legacy:{{ $company->legacy_id }}</span>@endif
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500 font-mono">{{ $company->slug }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $company->company_type ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm text-center text-gray-500">{{ $company->ships_count }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="text-xs px-2 py-1 rounded {{ $company->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $company->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.library.ship-companies.edit', $company) }}" class="text-indigo-600 hover:text-indigo-800 px-2"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('admin.library.ship-companies.destroy', $company) }}" class="inline"
                                  onsubmit="return confirm('Bu firma kütüphaneden silinsin mi? (Tenant kopyaları etkilenmez)')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700 px-2"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">Henüz kütüphane firması yok. Yükleme ile içeri alabilir veya elle ekleyebilirsiniz.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">{{ $companies->links() }}</div>
</div>
@endsection
