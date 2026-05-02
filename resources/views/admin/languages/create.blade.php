@extends('admin.layouts.app')
@section('title', 'Yeni Dil')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Yeni Dil</h1>
        <a href="{{ route('admin.languages.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Geri</a>
    </div>

    <form method="POST" action="{{ route('admin.languages.store', [], false) }}" class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dil Adı *</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 border rounded-lg text-sm">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kod * (tr, en, de...)</label>
                <input type="text" name="code" value="{{ old('code') }}" required maxlength="5" class="w-full px-3 py-2 border rounded-lg text-sm">
                @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Locale</label>
                <input type="text" name="locale" value="{{ old('locale') }}" class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="tr_TR">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Yön *</label>
                <select name="direction" class="w-full px-3 py-2 border rounded-lg text-sm">
                    <option value="ltr">Soldan Sağa (LTR)</option>
                    <option value="rtl">Sağdan Sola (RTL)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sıra</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded text-indigo-600">
                    Aktif
                </label>
            </div>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Oluştur</button>
            <a href="{{ route('admin.languages.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">İptal</a>
        </div>
    </form>
</div>
@endsection
