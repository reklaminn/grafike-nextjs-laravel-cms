@extends('admin.layouts.app')
@section('title', $roomType->exists ? 'Oda Tipi Düzenle' : 'Yeni Oda Tipi')

@section('content')
@php $amenitiesText = old('amenities', implode("\n", $roomType->amenities ?? [])); @endphp

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ $roomType->exists ? 'Oda Tipi Düzenle' : 'Yeni Oda Tipi' }}</h1>
    <a href="{{ route('admin.lodging.room-types.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Listeye dön</a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if($errors->any())
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ $roomType->exists ? route('admin.lodging.room-types.update', $roomType) : route('admin.lodging.room-types.store') }}"
      method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($roomType->exists) @method('PUT') @endif

    <div class="bg-white rounded-xl shadow-sm border p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Ad *</label>
            <input type="text" name="name" value="{{ old('name', $roomType->name) }}" required
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $roomType->slug) }}" placeholder="boş bırak → ad'dan üretilir"
                   class="w-full rounded-lg border-gray-300 text-sm font-mono focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sıra</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $roomType->sort_order ?? 0) }}"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Kısa Özet</label>
            <input type="text" name="summary" value="{{ old('summary', $roomType->summary) }}" maxlength="500"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
            <textarea name="description" rows="4"
                      class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $roomType->description) }}</textarea>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6 grid grid-cols-2 md:grid-cols-4 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Min. Kişi</label>
            <input type="number" name="capacity_min" value="{{ old('capacity_min', $roomType->capacity_min ?? 1) }}" min="1"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Maks. Kişi</label>
            <input type="number" name="capacity_max" value="{{ old('capacity_max', $roomType->capacity_max ?? 2) }}" min="1"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">m²</label>
            <input type="number" name="size_m2" value="{{ old('size_m2', $roomType->size_m2) }}" min="0"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Adet (envanter)</label>
            <input type="number" name="unit_count" value="{{ old('unit_count', $roomType->unit_count ?? 1) }}" min="1"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Yatak Odası</label>
            <input type="number" name="bedrooms" value="{{ old('bedrooms', $roomType->bedrooms ?? 1) }}" min="0"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Banyo</label>
            <input type="number" name="bathrooms" value="{{ old('bathrooms', $roomType->bathrooms ?? 1) }}" min="0"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gecelik Fiyat</label>
            <input type="number" step="0.01" name="base_price" value="{{ old('base_price', $roomType->base_price) }}" min="0"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Para Birimi</label>
            <input type="text" name="currency" value="{{ old('currency', $roomType->currency ?? 'TRY') }}" maxlength="3"
                   class="w-full rounded-lg border-gray-300 text-sm uppercase focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Olanaklar <span class="text-gray-400">(her satıra bir tane)</span></label>
        <textarea name="amenities" rows="5" placeholder="Wi-Fi&#10;Mutfak&#10;Balkon"
                  class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $amenitiesText }}</textarea>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Görseller</label>
        @if($roomType->exists && $roomType->getMedia('gallery')->isNotEmpty())
            <div class="flex flex-wrap gap-3 mb-4">
                @foreach($roomType->getMedia('gallery') as $media)
                    <div class="relative w-28 h-28 rounded-lg overflow-hidden border">
                        <img src="{{ $media->getUrl() }}" alt="" class="w-full h-full object-cover">
                        <button type="button"
                                onclick="document.getElementById('del-media-{{ $media->id }}').submit();"
                                class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 text-xs">×</button>
                    </div>
                    <form id="del-media-{{ $media->id }}" method="POST"
                          action="{{ route('admin.lodging.room-types.media.delete', [$roomType, $media->id]) }}" class="hidden">
                        @csrf
                    </form>
                @endforeach
            </div>
        @endif
        <input type="file" name="images[]" accept="image/*" multiple
               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6 flex items-center justify-between">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $roomType->is_active ?? true))
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            Aktif (frontend'de görünür)
        </label>
        <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
            {{ $roomType->exists ? 'Güncelle' : 'Oluştur' }}
        </button>
    </div>
</form>
@endsection
