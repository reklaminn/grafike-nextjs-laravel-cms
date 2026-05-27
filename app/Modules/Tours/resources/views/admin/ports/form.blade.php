@extends('admin.layouts.app')
@section('title', $port->exists ? 'Liman Düzenle' : 'Yeni Liman')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.ports.index') }}" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h1 class="text-2xl font-bold text-gray-800">
        {{ $port->exists ? 'Liman Düzenle' : 'Yeni Liman' }}
    </h1>
    @if($port->exists && $port->flagUrl())
        <img src="{{ $port->flagUrl() }}" alt="{{ $port->country_code }}"
             class="w-10 h-7 rounded shadow-sm border border-gray-200 ml-2">
    @endif
</div>

@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST"
      action="{{ $port->exists ? route('admin.ports.update', $port) : route('admin.ports.store') }}"
      class="space-y-6">
    @csrf
    @if($port->exists)@method('PUT')@endif

    {{-- Identity --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-anchor text-indigo-500"></i> Genel
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $port->slug) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                       placeholder="istanbul">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Ülke Kodu (ISO 3166-1 alpha-2)
                </label>
                <input type="text" name="country_code" value="{{ old('country_code', $port->country_code) }}"
                       maxlength="2"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase font-mono"
                       placeholder="TR">
                <p class="text-xs text-gray-400 mt-1">
                    Bayrak otomatik flagcdn.com'dan üretilir.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                <input type="text" name="timezone" value="{{ old('timezone', $port->timezone) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       placeholder="Europe/Istanbul">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Enlem (latitude)</label>
                <input type="text" name="latitude" value="{{ old('latitude', $port->latitude) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                       placeholder="41.0082">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Boylam (longitude)</label>
                <input type="text" name="longitude" value="{{ old('longitude', $port->longitude) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                       placeholder="28.9784">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nüfus</label>
                <input type="number" name="population" value="{{ old('population', $port->population) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Video URL (YouTube/Vimeo embed)</label>
                <input type="url" name="video_url" value="{{ old('video_url', $port->video_url) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       placeholder="https://www.youtube.com/watch?v=...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $port->sort_order ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>

        <div class="flex items-center">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $port->is_active ?? true) ? 'checked' : '' }}
                   class="h-4 w-4 text-indigo-600 rounded">
            <label for="is_active" class="ml-2 text-sm text-gray-700">Aktif</label>
        </div>
    </div>

    {{-- Translations --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-5">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-language text-indigo-500"></i> Çeviriler
        </h2>

        @foreach($languages as $i => $lang)
            @php $tr = $translations[$lang->id] ?? null; @endphp
            <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-semibold text-gray-500">{{ $lang->name }}</span>
                    <span class="text-[10px] font-mono text-gray-400">{{ $lang->code }}</span>
                </div>
                <input type="hidden" name="translations[{{ $i }}][language_id]" value="{{ $lang->id }}">
                <input type="text" name="translations[{{ $i }}][name]"
                       value="{{ old("translations.$i.name", $tr->name ?? '') }}"
                       placeholder="Liman adı (İstanbul)"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <textarea name="translations[{{ $i }}][short_description]" rows="2"
                          placeholder="Kısa açıklama (kart üzerinde)"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.short_description", $tr->short_description ?? '') }}</textarea>
                <textarea name="translations[{{ $i }}][long_description]" rows="4"
                          placeholder="Uzun açıklama (liman detay sayfası)"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.long_description", $tr->long_description ?? '') }}</textarea>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="translations[{{ $i }}][meta_title]"
                           value="{{ old("translations.$i.meta_title", $tr->meta_title ?? '') }}"
                           placeholder="Meta title (SEO)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
                    <input type="text" name="translations[{{ $i }}][meta_description]"
                           value="{{ old("translations.$i.meta_description", $tr->meta_description ?? '') }}"
                           placeholder="Meta description (SEO)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex justify-end">
        <button type="submit"
                class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
            <i class="fas fa-save mr-1"></i> Kaydet
        </button>
    </div>
</form>
@endsection
