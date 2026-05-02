@extends('admin.layouts.app')
@section('title', 'Çeviri Yönetimi')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Çeviri Yönetimi</h1>
    <a href="{{ route('admin.languages.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Diller</a>
</div>

{{-- Add Translation --}}
<form method="POST" action="{{ route('admin.languages.save-translation', [], false) }}" class="bg-white rounded-xl shadow-sm border p-5 mb-6">
    @csrf
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Yeni Çeviri Ekle</h3>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <select name="language_id" required class="px-3 py-2 border rounded-lg text-sm">
            @foreach($languages as $lang)
                <option value="{{ $lang->id }}">{{ $lang->name }}</option>
            @endforeach
        </select>
        <input type="text" name="group" required placeholder="Grup (ör: frontend)" class="px-3 py-2 border rounded-lg text-sm" value="{{ request('group') }}">
        <input type="text" name="key" required placeholder="Anahtar (ör: welcome)" class="px-3 py-2 border rounded-lg text-sm">
        <input type="text" name="value" required placeholder="Çeviri değeri" class="px-3 py-2 border rounded-lg text-sm">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Ekle</button>
    </div>
</form>

{{-- Filters --}}
<form method="GET" class="flex gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Anahtar veya değer ara..."
           class="flex-1 px-4 py-2 border rounded-lg text-sm focus:ring-indigo-500">
    <select name="group" class="px-4 py-2 border rounded-lg text-sm">
        <option value="">Tüm Gruplar</option>
        @foreach($groups as $g)
            <option value="{{ $g }}" {{ request('group') === $g ? 'selected' : '' }}>{{ $g }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">Filtrele</button>
</form>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Dil</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Grup</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Anahtar</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Değer</th>
                <th class="text-right px-4 py-3 font-medium text-gray-600">İşlem</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($translations as $t)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 uppercase text-xs font-bold text-gray-500">{{ $t->language?->code ?? '?' }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 bg-gray-100 rounded text-xs">{{ $t->group }}</span></td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $t->key }}</td>
                    <td class="px-4 py-3 text-gray-800">{{ Str::limit($t->value, 60) }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.languages.delete-translation', $t) }}" class="inline" onsubmit="return confirm('Silinsin mi?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline text-xs">Sil</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Çeviri bulunamadı.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $translations->links() }}</div>
@endsection
