@extends('admin.layouts.app')
@section('title', $tour->exists ? 'Tur Düzenle' : 'Yeni Tur')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.tours.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $tour->exists ? 'Tur Düzenle' : 'Yeni Tur' }}</h1>
    </div>
    @if($tour->exists)
        <a href="{{ route('admin.tours.dates.index', $tour) }}"
           class="px-3 py-1.5 bg-amber-100 text-amber-700 rounded-lg text-sm hover:bg-amber-200">
            <i class="fas fa-calendar-alt mr-1"></i> Departure'ları Yönet ({{ $tour->dates->count() ?? 0 }})
        </a>
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
      action="{{ $tour->exists ? route('admin.tours.update', $tour) : route('admin.tours.store') }}"
      class="space-y-6">
    @csrf
    @if($tour->exists)@method('PUT')@endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Translations --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-xl shadow-sm border p-5 space-y-5">
                <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-language text-indigo-500"></i> İçerik (Çeviriler)
                </h2>

                @foreach($languages as $i => $lang)
                    @php $tr = $translations[$lang->id] ?? null; @endphp
                    <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase font-semibold text-gray-500">{{ $lang->name }}</span>
                            <span class="text-[10px] font-mono text-gray-400">{{ $lang->code }}</span>
                        </div>

                        <input type="hidden" name="translations[{{ $i }}][language_id]" value="{{ $lang->id }}">

                        <input type="text" name="translations[{{ $i }}][title]" required
                               value="{{ old("translations.$i.title", $tr->title ?? '') }}"
                               placeholder="Başlık"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium">

                        <input type="text" name="translations[{{ $i }}][subtitle]"
                               value="{{ old("translations.$i.subtitle", $tr->subtitle ?? '') }}"
                               placeholder="Alt başlık"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">

                        <textarea name="translations[{{ $i }}][short_description]" rows="2"
                                  placeholder="Kısa açıklama (listeleme kartlarında çıkar)"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.short_description", $tr->short_description ?? '') }}</textarea>

                        <textarea name="translations[{{ $i }}][description]" rows="6"
                                  placeholder="Detaylı açıklama (HTML)"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">{{ old("translations.$i.description", $tr->description ?? '') }}</textarea>

                        <details class="text-xs">
                            <summary class="cursor-pointer text-gray-500 hover:text-gray-700">▸ Highlights / Önemli Bilgi / SEO</summary>
                            <div class="mt-3 space-y-3">
                                <textarea name="translations[{{ $i }}][highlights]" rows="3"
                                          placeholder="Highlights (bullet list / markdown)"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono">{{ old("translations.$i.highlights", $tr->highlights ?? '') }}</textarea>
                                <textarea name="translations[{{ $i }}][important_info]" rows="3"
                                          placeholder="Önemli bilgi (yaş limiti, sağlık şartı vb.)"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">{{ old("translations.$i.important_info", $tr->important_info ?? '') }}</textarea>
                                <input type="text" name="translations[{{ $i }}][meta_title]"
                                       value="{{ old("translations.$i.meta_title", $tr->meta_title ?? '') }}"
                                       placeholder="Meta title (SEO)"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
                                <textarea name="translations[{{ $i }}][meta_description]" rows="2"
                                          placeholder="Meta description (SEO)"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">{{ old("translations.$i.meta_description", $tr->meta_description ?? '') }}</textarea>
                                <input type="url" name="translations[{{ $i }}][og_image_url]"
                                       value="{{ old("translations.$i.og_image_url", $tr->og_image_url ?? '') }}"
                                       placeholder="OG image URL (sosyal paylaşım)"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
                            </div>
                        </details>
                    </div>
                @endforeach
            </div>

            {{-- Type-config JSON --}}
            <div class="bg-white rounded-xl shadow-sm border p-5 space-y-3">
                <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-code text-purple-500"></i> Tip-spesifik Konfig (type_config)
                </h2>
                <p class="text-xs text-gray-500">
                    Cruise için <code>ship_name</code>, <code>departure_port</code>; Paket için
                    <code>accommodation_type</code>, <code>transport_type</code>; Günlük için
                    <code>meeting_point</code>, <code>pickup_radius_km</code>. JSON formatında.
                </p>
                <textarea name="type_config" rows="6"
                          placeholder='{"ship_name":"Costa Mediterranea","departure_port":"İstanbul"}'
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono">{{ old('type_config', $tour->type_config ? json_encode($tour->type_config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '') }}</textarea>
            </div>
        </div>

        {{-- RIGHT: Settings --}}
        <div class="space-y-4">

            <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
                <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-cogs text-indigo-500"></i> Genel
                </h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tip *</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        @foreach($types as $t)
                            <option value="{{ $t->value }}" {{ old('type', $tour->type?->value ?? 'package') === $t->value ? 'selected' : '' }}>
                                {{ $t->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL) *</label>
                    <input type="text" name="slug" required value="{{ old('slug', $tour->slug) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                           placeholder="bodrum-cruise-7-gun">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select name="tour_category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">— Kategorisiz —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (int) old('tour_category_id', $tour->tour_category_id) === $cat->id ? 'selected' : '' }}>
                                {{ $cat->translations->first()?->name ?? $cat->slug }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durum *</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="draft"     {{ old('status', $tour->status ?? 'draft') === 'draft'     ? 'selected' : '' }}>Taslak</option>
                        <option value="published" {{ old('status', $tour->status) === 'published'             ? 'selected' : '' }}>Yayında</option>
                        <option value="archived"  {{ old('status', $tour->status) === 'archived'              ? 'selected' : '' }}>Arşivlendi</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Para Birimi *</label>
                        <input type="text" name="currency" required maxlength="3"
                               value="{{ old('currency', $tour->currency ?? 'TRY') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sıra</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $tour->sort_order ?? 0) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Baz Fiyat (kuruş cinsinden) *</label>
                    <input type="number" name="base_price" required min="0"
                           value="{{ old('base_price', $tour->base_price ?? 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <p class="text-xs text-gray-400 mt-1">Örnek: 125000 = 1.250,00 TL</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Varsayılan Kapasite *</label>
                    <input type="number" name="capacity_default" required min="0"
                           value="{{ old('capacity_default', $tour->capacity_default ?? 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <p class="text-xs text-gray-400 mt-1">Her departure tarihinde başlangıç kapasitesi (override edilebilir).</p>
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1"
                           {{ old('is_featured', $tour->is_featured ?? false) ? 'checked' : '' }}
                           class="h-4 w-4 text-pink-600 rounded">
                    <span class="text-sm text-gray-700"><i class="fas fa-star text-pink-500"></i> Anasayfada öne çıkar</span>
                </label>
            </div>

            <button type="submit"
                    class="w-full px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
                <i class="fas fa-save mr-1"></i> Kaydet
            </button>

            @if($tour->exists)
                <div class="bg-gray-50 rounded-xl border p-3 text-xs text-gray-500 space-y-1">
                    <p><strong>ID:</strong> #{{ $tour->id }}</p>
                    <p><strong>Oluşturuldu:</strong> {{ $tour->created_at?->isoFormat('D MMM YYYY HH:mm') }}</p>
                    <p><strong>Güncellendi:</strong> {{ $tour->updated_at?->isoFormat('D MMM YYYY HH:mm') }}</p>
                </div>
            @endif
        </div>
    </div>
</form>
@endsection
