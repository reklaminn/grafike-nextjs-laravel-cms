@extends('admin.layouts.app')
@section('title', $cabin->exists ? 'Kabin Düzenle' : 'Yeni Kabin')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.cabins.index') }}" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h1 class="text-2xl font-bold text-gray-800">
        {{ $cabin->exists ? ($cabin->translations->first()?->name ?? $cabin->code ?? 'Kabin') : 'Yeni Kabin' }}
    </h1>
</div>

@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" enctype="multipart/form-data"
      action="{{ $cabin->exists ? route('admin.cabins.update', $cabin) : route('admin.cabins.store') }}"
      class="space-y-6">
    @csrf
    @if($cabin->exists)@method('PUT')@endif

    {{-- Identity --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-door-open text-indigo-500"></i> Genel
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gemi</label>
                <select name="ship_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">— Gemi seçin —</option>
                    @foreach($ships as $ship)
                        <option value="{{ $ship->id }}"
                                {{ (int) old('ship_id', $cabin->ship_id) === $ship->id ? 'selected' : '' }}>
                            {{ $ship->name }} ({{ $ship->company?->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kabin Kategorisi</label>
                <select name="cabin_category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">— Kategori seçin —</option>
                    @foreach($categories as $cat)
                        @php $catTr = $cat->translations->first(); @endphp
                        <option value="{{ $cat->id }}"
                                {{ (int) old('cabin_category_id', $cabin->cabin_category_id) === $cat->id ? 'selected' : '' }}>
                            {{ $catTr->name ?? $cat->slug }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alt Kategori (brand-specific)
                    <span class="text-xs text-gray-400 font-normal">opsiyonel</span>
                </label>
                <input type="text" name="brand_subcategory" value="{{ old('brand_subcategory', $cabin->brand_subcategory) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       placeholder="Yacht Club Deluxe Suite">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kabin Kodu</label>
                <input type="text" name="code" value="{{ old('code', $cabin->code) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                       placeholder="JS-7042">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deck (kat)</label>
                <input type="text" name="deck_name" value="{{ old('deck_name', $cabin->deck_name) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       placeholder="Deck 7 – Verandah">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Kişi</label>
                <input type="number" name="max_capacity" value="{{ old('max_capacity', $cabin->max_capacity) }}"
                       min="1" max="20"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Baz Fiyat (kişi başı, kuruş)
                </label>
                <input type="number" name="base_price_per_person" value="{{ old('base_price_per_person', $cabin->base_price_per_person) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                       placeholder="150000">
                <p class="text-xs text-gray-400 mt-1">100 = 1 TL.  Tour pricing matrix burayı override eder.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $cabin->sort_order ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>

        <div class="flex items-center">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $cabin->is_active ?? true) ? 'checked' : '' }}
                   class="h-4 w-4 text-indigo-600 rounded">
            <label for="is_active" class="ml-2 text-sm text-gray-700">Aktif</label>
        </div>
    </div>

    {{-- Media --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-5">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-images text-indigo-500"></i> Medya
        </h2>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kapak Görseli</label>
            @if($cabin->exists && ($coverUrl = $cabin->getFirstMediaUrl('cover')))
                <img src="{{ $coverUrl }}" alt="cover" class="h-32 mb-2 rounded border border-gray-200">
            @endif
            <input type="file" name="cover" accept="image/*"
                   class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Galeri</label>
            @if($cabin->exists)
                <div class="grid grid-cols-2 md:grid-cols-6 gap-2 mb-3">
                    @foreach($cabin->getMedia('gallery') as $media)
                        <div class="relative group">
                            <img src="{{ $media->getUrl() }}" alt="" class="h-20 w-full object-cover rounded border border-gray-200">
                            <form action="{{ route('admin.cabins.media.delete', ['cabin' => $cabin, 'mediaId' => $media->id]) }}"
                                  method="POST" class="absolute top-1 right-1"
                                  onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                                @csrf
                                <button type="submit" class="bg-red-500 text-white rounded-full w-6 h-6 text-xs opacity-0 group-hover:opacity-100">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
            <input type="file" name="gallery[]" accept="image/*" multiple
                   class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kat Planı (PDF / görsel — tek dosya)</label>
            @if($cabin->exists && ($floorPlan = $cabin->getFirstMedia('floor_plan')))
                <div class="mb-2 flex items-center gap-2">
                    @if(str_starts_with($floorPlan->mime_type ?? '', 'image/'))
                        <img src="{{ $floorPlan->getUrl() }}" class="h-20 rounded">
                    @else
                        <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                        <a href="{{ $floorPlan->getUrl() }}" target="_blank" class="text-xs text-indigo-600 underline">{{ $floorPlan->name }}</a>
                    @endif
                </div>
            @endif
            <input type="file" name="floor_plan" accept="image/*,.pdf"
                   class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
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
                       placeholder="Kabin adı (Junior Suite Balcony Deck 7)"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <textarea name="translations[{{ $i }}][description]" rows="3"
                          placeholder="Açıklama"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.description", $tr->description ?? '') }}</textarea>
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
