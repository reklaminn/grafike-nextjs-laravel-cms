@extends('admin.layouts.app')
@section('title', 'Yönlendirme Düzenle')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Yönlendirme Düzenle</h1>
        <a href="{{ route('admin.redirects.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Geri</a>
    </div>

    {{-- Stats --}}
    <div class="bg-gray-50 border rounded-lg p-4 mb-6 flex gap-6 text-sm">
        <div><strong>Hit Sayısı:</strong> {{ number_format($redirect->hit_count) }}</div>
        <div><strong>Son Hit:</strong> {{ $redirect->last_hit_at?->format('d.m.Y H:i') ?? 'Hiç' }}</div>
    </div>

    <form method="POST" action="{{ route('admin.redirects.update', $redirect, false) }}" class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Eski URL *</label>
            <input type="text" name="from_url" value="{{ old('from_url', $redirect->from_url) }}" required
                   class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
            @error('from_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Yeni URL *</label>
            <input type="text" name="to_url" value="{{ old('to_url', $redirect->to_url) }}" required
                   class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
            @error('to_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Durum Kodu *</label>
            <select name="status_code" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
                <option value="301" {{ old('status_code', $redirect->status_code) == '301' ? 'selected' : '' }}>301 - Kalıcı</option>
                <option value="302" {{ old('status_code', $redirect->status_code) == '302' ? 'selected' : '' }}>302 - Geçici</option>
                <option value="307" {{ old('status_code', $redirect->status_code) == '307' ? 'selected' : '' }}>307 - Geçici (POST)</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="is_active"
                   {{ old('is_active', $redirect->is_active) ? 'checked' : '' }} class="rounded text-indigo-600">
            <label for="is_active" class="text-sm text-gray-700">Aktif</label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Güncelle</button>
            <a href="{{ route('admin.redirects.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">İptal</a>
        </div>
    </form>
</div>
@endsection
