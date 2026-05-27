@extends('admin.layouts.app')
@section('title', $ship->exists ? 'Gemi Düzenle' : 'Yeni Gemi')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.ships.index') }}" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h1 class="text-2xl font-bold text-gray-800">
        {{ $ship->exists ? $ship->name : 'Yeni Gemi' }}
    </h1>
    @if($ship->exists && $ship->flagUrl())
        <img src="{{ $ship->flagUrl() }}" alt="{{ $ship->flag_country_code }}"
             class="w-9 h-6 rounded shadow-sm border border-gray-200 ml-2">
    @endif
</div>

@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" enctype="multipart/form-data"
      action="{{ $ship->exists ? route('admin.ships.update', $ship) : route('admin.ships.store') }}"
      class="space-y-6">
    @csrf
    @if($ship->exists)@method('PUT')@endif

    {{-- Tab 1 — Identity --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-id-card text-indigo-500"></i> Kimlik
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Firma</label>
                <select name="ship_company_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">— Firma seçin —</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}"
                                {{ (int) old('ship_company_id', $ship->ship_company_id) === $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gemi Adı</label>
                <input type="text" name="name" value="{{ old('name', $ship->name) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       placeholder="MSC Splendida">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $ship->slug) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                       placeholder="msc-splendida">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Yıldız (1-7)</label>
                <input type="number" name="star_rating" value="{{ old('star_rating', $ship->star_rating) }}"
                       min="1" max="7"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bayrak Ülke Kodu (ISO-2)</label>
                <input type="text" name="flag_country_code" value="{{ old('flag_country_code', $ship->flag_country_code) }}"
                       maxlength="2"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase font-mono"
                       placeholder="MT">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">IMO No</label>
                <input type="text" name="imo_number" value="{{ old('imo_number', $ship->imo_number) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                       placeholder="9359806">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Yerel Acente</label>
                <input type="text" name="local_agent" value="{{ old('local_agent', $ship->local_agent) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $ship->sort_order ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div class="flex items-center pt-6 md:col-span-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       {{ old('is_active', $ship->is_active ?? true) ? 'checked' : '' }}
                       class="h-4 w-4 text-indigo-600 rounded">
                <label for="is_active" class="ml-2 text-sm text-gray-700">Aktif</label>
            </div>
        </div>
    </div>

    {{-- Tab 2 — Specs --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-cogs text-indigo-500"></i> Teknik Özellikler
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Yapım Yılı</label>
                <input type="number" name="year_built" value="{{ old('year_built', $ship->year_built) }}"
                       min="1800" max="{{ date('Y') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Yolcu Kapasitesi</label>
                <input type="number" name="passenger_capacity" value="{{ old('passenger_capacity', $ship->passenger_capacity) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Mürettebat</label>
                <input type="number" name="crew_count" value="{{ old('crew_count', $ship->crew_count) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Kat Sayısı</label>
                <input type="number" name="deck_count" value="{{ old('deck_count', $ship->deck_count) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Tonaj (GT)</label>
                <input type="number" name="tonnage" value="{{ old('tonnage', $ship->tonnage) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Uzunluk (m)</label>
                <input type="text" name="length_m" value="{{ old('length_m', $ship->length_m) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Genişlik (m)</label>
                <input type="text" name="beam_m" value="{{ old('beam_m', $ship->beam_m) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Hız (knot)</label>
                <input type="text" name="cruise_speed_knots" value="{{ old('cruise_speed_knots', $ship->cruise_speed_knots) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
            </div>
        </div>

        {{-- Facilities --}}
        <div class="pt-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                İmkanlar
                <span class="text-xs text-gray-400 font-normal">— config/ship_facilities.php'den</span>
            </label>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                @foreach($facilities as $slug => $meta)
                    <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="facilities[]" value="{{ $slug }}"
                               {{ in_array($slug, (array) old('facilities', $selectedFac), true) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 rounded">
                        <span class="text-base">{{ $meta['icon'] ?? '✦' }}</span>
                        <span class="text-sm">{{ $labels[$slug] ?? $slug }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Tab 3 — Media --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-5">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-images text-indigo-500"></i> Medya
        </h2>

        {{-- Cover --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kapak Görseli (tek dosya)</label>
            @if($ship->exists && ($coverUrl = $ship->getFirstMediaUrl('cover')))
                <img src="{{ $coverUrl }}" alt="cover" class="h-32 mb-2 rounded border border-gray-200">
            @endif
            <input type="file" name="cover" accept="image/*"
                   class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        </div>

        {{-- Gallery --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Galeri (çoklu)</label>
            @if($ship->exists)
                <div class="grid grid-cols-2 md:grid-cols-6 gap-2 mb-3">
                    @foreach($ship->getMedia('gallery') as $media)
                        <div class="relative group">
                            <img src="{{ $media->getUrl() }}" alt="" class="h-20 w-full object-cover rounded border border-gray-200">
                            <form action="{{ route('admin.ships.media.delete', ['ship' => $ship, 'mediaId' => $media->id]) }}"
                                  method="POST" class="absolute top-1 right-1"
                                  onsubmit="return confirm('Bu görseli silmek istediğinize emin misiniz?');">
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

        {{-- Deck plans --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kat Planları (görsel veya PDF)</label>
            @if($ship->exists)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3">
                    @foreach($ship->getMedia('deck_plans') as $media)
                        <div class="relative group border border-gray-200 rounded p-2">
                            @if(str_starts_with($media->mime_type ?? '', 'image/'))
                                <img src="{{ $media->getUrl() }}" alt="" class="h-20 w-full object-cover rounded">
                            @else
                                <div class="h-20 flex items-center justify-center bg-gray-50 rounded">
                                    <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                                </div>
                            @endif
                            <div class="text-[10px] text-gray-500 truncate mt-1">{{ $media->name }}</div>
                            <form action="{{ route('admin.ships.media.delete', ['ship' => $ship, 'mediaId' => $media->id]) }}"
                                  method="POST" class="absolute top-1 right-1"
                                  onsubmit="return confirm('Bu dosyayı silmek istediğinize emin misiniz?');">
                                @csrf
                                <button type="submit" class="bg-red-500 text-white rounded-full w-6 h-6 text-xs opacity-0 group-hover:opacity-100">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
            <input type="file" name="deck_plans[]" accept="image/*,.pdf" multiple
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
                <textarea name="translations[{{ $i }}][description]" rows="4"
                          placeholder="Gemi açıklaması"
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
