@extends('admin.layouts.app')

@section('title', $port->exists ? 'Liman Düzenle' : 'Yeni Liman')
@section('page-title', 'Kütüphane — Liman')

@section('content')
<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('admin.library.ports.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
    <h1 class="text-xl font-bold text-gray-800">{{ $port->exists ? ($port->translations->first()?->name ?? $port->slug) : 'Yeni Liman' }}</h1>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST"
      action="{{ $port->exists ? route('admin.library.ports.update', $port) : route('admin.library.ports.store') }}"
      class="space-y-6 max-w-4xl">
    @csrf
    @if($port->exists) @method('PUT') @endif

    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700"><i class="fas fa-anchor text-indigo-500 mr-1"></i> Liman Bilgileri</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                <input type="text" name="slug" value="{{ old('slug', $port->slug) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ülke Kodu (ISO-2)</label>
                <input type="text" name="country_code" value="{{ old('country_code', $port->country_code) }}" maxlength="2"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Zaman Dilimi</label>
                <input type="text" name="timezone" value="{{ old('timezone', $port->timezone) }}" placeholder="Europe/Athens"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Enlem (lat)</label>
                <input type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $port->latitude) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Boylam (lng)</label>
                <input type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $port->longitude) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nüfus</label>
                <input type="number" name="population" value="{{ old('population', $port->population) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
                <input type="text" name="video_url" value="{{ old('video_url', $port->video_url) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sıra</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $port->sort_order ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kapak Görsel URL</label>
                <input type="text" name="cover_url" value="{{ old('cover_url', $port->cover_url) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>
        <label class="inline-flex items-center gap-2 text-sm pt-1">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $port->is_active ?? true) ? 'checked' : '' }}>
            Aktif
        </label>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700"><i class="fas fa-language text-indigo-500 mr-1"></i> Çeviriler</h2>
        @foreach($languages as $i => $lang)
            @php $t = $translations[$lang->id] ?? null; @endphp
            <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                <div class="text-sm font-medium text-gray-600">{{ $lang->name }}</div>
                <input type="hidden" name="translations[{{ $i }}][language_id]" value="{{ $lang->id }}">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Ad (örn. Pire / Piraeus)</label>
                    <input type="text" name="translations[{{ $i }}][name]" value="{{ old("translations.$i.name", $t->name ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Kısa Açıklama</label>
                    <textarea name="translations[{{ $i }}][short_description]" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.short_description", $t->short_description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Uzun Açıklama</label>
                    <textarea name="translations[{{ $i }}][long_description]" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.long_description", $t->long_description ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="translations[{{ $i }}][meta_title]" value="{{ old("translations.$i.meta_title", $t->meta_title ?? '') }}"
                           placeholder="Meta başlık" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <input type="text" name="translations[{{ $i }}][meta_description]" value="{{ old("translations.$i.meta_description", $t->meta_description ?? '') }}"
                           placeholder="Meta açıklama" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('admin.library.ports.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">İptal</a>
        <button class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium"><i class="fas fa-save mr-1"></i> Kaydet</button>
    </div>
</form>
@endsection
