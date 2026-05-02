@extends('admin.layouts.app')

@section('title', 'Yeni Menü')
@section('page-title', 'Yeni Menü Oluştur')

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.menus.store', [], false) }}">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Menü Adı *</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konum *</label>
                    <select name="location" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="header">Header (Üst Menü)</option>
                        <option value="footer">Footer (Alt Menü)</option>
                        <option value="sidebar">Sidebar (Yan Menü)</option>
                        <option value="mobile">Mobil Menü</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dil *</label>
                    <select name="language_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        @foreach($languages as $lang)
                            <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600">
                    <span class="text-sm text-gray-700">Aktif</span>
                </label>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-1"></i> Oluştur
                    </button>
                    <a href="{{ route('admin.menus.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">İptal</a>
                </div>
            </div>
        </form>
    </div>
@endsection
