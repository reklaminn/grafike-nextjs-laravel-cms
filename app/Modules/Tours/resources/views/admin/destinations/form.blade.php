@extends('admin.layouts.app')
@section('title', $destination->exists ? 'Destinasyon Düzenle' : 'Yeni Destinasyon')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.destinations.index') }}" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h1 class="text-2xl font-bold text-gray-800">
        {{ $destination->exists ? 'Destinasyon Düzenle' : 'Yeni Destinasyon' }}
    </h1>
</div>

@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

@php
    $tourTypes = ['cruise' => 'Cruise', 'package' => 'Paket Tur', 'daily' => 'Günlük Tur', 'ferry' => 'Feribot'];
    $currentTypes = old('compatible_tour_types', $destination->compatible_tour_types ?? []);
@endphp

<form method="POST"
      action="{{ $destination->exists ? route('admin.destinations.update', $destination) : route('admin.destinations.store') }}"
      class="space-y-6">
    @csrf
    @if($destination->exists)@method('PUT')@endif

    {{-- Identity --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-map-marked-alt text-indigo-500"></i> Genel
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $destination->slug) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                       placeholder="yunan-adalari">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Enlem (latitude)</label>
                <input type="text" name="latitude" value="{{ old('latitude', $destination->latitude) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                       placeholder="37.5">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Boylam (longitude)</label>
                <input type="text" name="longitude" value="{{ old('longitude', $destination->longitude) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                       placeholder="25.3">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $destination->sort_order ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div class="flex items-center pt-6">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       {{ old('is_active', $destination->is_active ?? true) ? 'checked' : '' }}
                       class="h-4 w-4 text-indigo-600 rounded">
                <label for="is_active" class="ml-2 text-sm text-gray-700">Aktif</label>
            </div>

            <div class="flex items-center pt-6">
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" id="is_featured" name="is_featured" value="1"
                       {{ old('is_featured', $destination->is_featured ?? false) ? 'checked' : '' }}
                       class="h-4 w-4 text-amber-500 rounded">
                <label for="is_featured" class="ml-2 text-sm text-gray-700">Öne çıkan</label>
            </div>
        </div>

        {{-- Compatible tour types --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Uyumlu Tur Tipleri
                <span class="text-xs text-gray-400 font-normal">— hiçbiri seçilmezse tüm tipler uygun sayılır</span>
            </label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach($tourTypes as $value => $label)
                    <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="compatible_tour_types[]" value="{{ $value }}"
                               {{ in_array($value, (array) $currentTypes, true) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 rounded">
                        <span class="text-sm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Ports m2m --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-anchor text-indigo-500"></i> Limanlar
        </h2>
        <p class="text-xs text-gray-500">
            Bu destinasyona dahil olan liman'lar.  Seçim sırası frontend listesinin sırasını belirler.
        </p>

        @if($allPorts->isEmpty())
            <p class="text-sm text-amber-600">Henüz liman yok — önce <a href="{{ route('admin.ports.create') }}" class="underline">liman ekleyin</a>.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 max-h-96 overflow-y-auto p-2 bg-gray-50 rounded-lg">
                @foreach($allPorts as $port)
                    @php $tr = $port->translations->first(); @endphp
                    <label class="flex items-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded hover:bg-indigo-50 cursor-pointer">
                        <input type="checkbox" name="port_ids[]" value="{{ $port->id }}"
                               {{ in_array($port->id, $selectedPorts, true) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 rounded">
                        @if($port->flagUrl())
                            <img src="{{ $port->flagUrl() }}" class="w-4 h-3 rounded shadow-sm" loading="lazy">
                        @endif
                        <span class="text-sm">{{ $tr->name ?? $port->slug }}</span>
                    </label>
                @endforeach
            </div>
        @endif
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
                       placeholder="Destinasyon adı (Yunan Adaları)"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <textarea name="translations[{{ $i }}][description]" rows="3"
                          placeholder="Açıklama"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.description", $tr->description ?? '') }}</textarea>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="translations[{{ $i }}][meta_title]"
                           value="{{ old("translations.$i.meta_title", $tr->meta_title ?? '') }}"
                           placeholder="Meta title"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
                    <input type="text" name="translations[{{ $i }}][meta_description]"
                           value="{{ old("translations.$i.meta_description", $tr->meta_description ?? '') }}"
                           placeholder="Meta description"
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
