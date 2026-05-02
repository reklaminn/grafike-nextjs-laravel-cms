@extends('admin.layouts.app')

@section('title', 'Yeni Form')
@section('page-title', 'Yeni Form Oluştur')

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.forms.store', [], false) }}">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Form Adı *</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
                    <textarea name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alıcı E-posta</label>
                    <input type="email" name="notification_email" value="{{ old('notification_email') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-1"></i> Oluştur
                    </button>
                    <a href="{{ route('admin.forms.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">İptal</a>
                </div>
            </div>
        </form>
    </div>
@endsection
