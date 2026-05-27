@extends('admin.layouts.app')
@section('title', 'Kabin Grupları')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kabin Grupları</h1>
        <p class="text-sm text-gray-500">Şirket-bazında kabin bundle.  Tour pricing setup'ta toplu seçim için kullanılır.</p>
    </div>
    <a href="{{ route('admin.cabin-groups.create') }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
        <i class="fas fa-plus mr-1"></i> Yeni Grup
    </a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

<form method="GET" class="bg-white rounded-xl shadow-sm border p-4 mb-4 flex gap-3 items-end">
    <div class="w-64">
        <label class="block text-xs uppercase font-semibold text-gray-500 mb-1">Firma</label>
        <select name="company" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">— Hepsi —</option>
            @foreach($companies as $company)
                <option value="{{ $company->id }}" {{ ($filters['company'] ?? '') == $company->id ? 'selected' : '' }}>
                    {{ $company->name }}{{ $company->uses_cabin_groups ? '' : ' (grup kullanmıyor)' }}
                </option>
            @endforeach
        </select>
    </div>
    <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">Filtrele</button>
</form>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($groups->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">Henüz grup yok.</div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Grup</th>
                    <th class="px-4 py-3">Firma</th>
                    <th class="px-4 py-3">Kabin Sayısı</th>
                    <th class="px-4 py-3">Sıra</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 text-right">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($groups as $group)
                    @php
                        $tr = $defaultLanguage ? $group->translationFor($defaultLanguage->id) : $group->translations->first();
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="text-gray-800 font-medium">{{ $tr->name ?? '—' }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ $group->slug }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $group->company?->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $group->cabins_count }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $group->sort_order }}</td>
                        <td class="px-4 py-3">
                            @if($group->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">Aktif</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 text-xs font-medium">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.cabin-groups.edit', $group) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Düzenle</a>
                            <form action="{{ route('admin.cabin-groups.destroy', $group) }}" method="POST" class="inline ml-3"
                                  onsubmit="return confirm('Grubu silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Sil</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $groups->links() }}</div>
    @endif
</div>
@endsection
