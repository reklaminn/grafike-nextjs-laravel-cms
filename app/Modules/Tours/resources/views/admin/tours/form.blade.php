@extends('admin.layouts.app')
@section('title', $tour->exists ? 'Tur Düzenle' : 'Yeni Tur')

@php
    // Form için ortak data — Alpine.js x-data bunları okur.
    $tabs = [
        1 => ['icon' => 'fa-cogs',         'label' => 'Genel'],
        2 => ['icon' => 'fa-route',        'label' => 'Rota Takvimi'],
        3 => ['icon' => 'fa-tags',         'label' => 'Genel Fiyatlar'],
        4 => ['icon' => 'fa-calendar-alt', 'label' => 'Tarih & Fiyatlar'],
        5 => ['icon' => 'fa-align-left',   'label' => 'Açıklamalar'],
        6 => ['icon' => 'fa-search',       'label' => 'SEO'],
        7 => ['icon' => 'fa-map-marked-alt','label' => 'Harita'],
        8 => ['icon' => 'fa-images',       'label' => 'Resimler'],
        9 => ['icon' => 'fa-comments',     'label' => 'Yorumlar'],
    ];
    $flightInfo  = $tour->flight_info ?? [];

    // Para birimi seçenekleri — ileride kur tablosundan (admin.currencies)
    // dinamik beslenebilir.  Şimdilik 3 ana para birimi.
    $currencyOptions = [
        'TRY' => '₺ Türk Lirası (TRY)',
        'EUR' => '€ Euro (EUR)',
        'USD' => '$ Amerikan Doları (USD)',
    ];
    $currentCurrency = old('currency', $tour->currency ?? 'TRY');

    $typeConfigJson = $tour->type_config
        ? json_encode($tour->type_config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        : '';
    $structuredJson = $tour->structured_data_json
        ? json_encode($tour->structured_data_json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        : '';
    $initialTab = (int) request('tab', $errors->any() ? 1 : 1);
@endphp

@section('content')
<div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.tours.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-bold text-gray-800">
            {{ $tour->exists ? ($translations->first()?->title ?? 'Tur Düzenle') : 'Yeni Tur' }}
        </h1>
        @if($tour->exists && $tour->copiedFromTour)
            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">
                Kopya: <a href="{{ route('admin.tours.edit', $tour->copiedFromTour) }}" class="underline">#{{ $tour->copiedFromTour->id }}</a>
            </span>
        @endif
    </div>
    @if($tour->exists)
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.tours.duplicate', $tour) }}"
                  onsubmit="return confirm('Bu turu kopyala?');">
                @csrf
                <button type="submit" class="px-3 py-1.5 bg-purple-100 text-purple-700 rounded-lg text-sm hover:bg-purple-200">
                    <i class="fas fa-copy mr-1"></i> Tur Kopyala
                </button>
            </form>
            <a href="{{ route('admin.tours.dates.index', $tour) }}"
               class="px-3 py-1.5 bg-amber-100 text-amber-700 rounded-lg text-sm hover:bg-amber-200">
                <i class="fas fa-calendar-alt mr-1"></i> Departure'lar ({{ $tour->dates->count() ?? 0 }})
            </a>
        </div>
    @endif
</div>

@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif

<form method="POST" enctype="multipart/form-data"
      x-data="{
          activeTab: {{ $initialTab }},
          slug: @js(old('slug', $tour->slug ?? '')),
          slugTouched: {{ $tour->exists ? 'true' : 'false' }},
          slugify(text) {
              const map = { 'ç':'c','ğ':'g','ı':'i','ö':'o','ş':'s','ü':'u' };
              return (text || '').toString().toLowerCase()
                  .replace(/[çğıöşü]/g, m => map[m])
                  .replace(/[^a-z0-9\s-]/g, '')
                  .trim()
                  .replace(/\s+/g, '-')
                  .replace(/-+/g, '-')
                  .replace(/^-+|-+$/g, '');
          }
      }"
      action="{{ $tour->exists ? route('admin.tours.update', $tour) : route('admin.tours.store') }}">
    @csrf
    @if($tour->exists)@method('PUT')@endif

    {{-- Tab nav --}}
    <div class="bg-white rounded-xl shadow-sm border mb-5 overflow-x-auto">
        <nav class="flex flex-wrap min-w-max">
            @foreach($tabs as $num => $tab)
                <button type="button" @click="activeTab = {{ $num }}"
                        :class="activeTab === {{ $num }} ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                        class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                    <span class="text-xs font-mono text-gray-400 mr-1">{{ $num }}.</span>
                    <i class="fas {{ $tab['icon'] }} mr-1"></i>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ═══ Tab 1: Genel Bilgiler ═══════════════════════════════════════ --}}
    <div x-show="activeTab === 1" x-cloak class="space-y-6">

        @include('tours::admin.partials._help', [
            'title' => 'Genel bilgiler nasıl doldurulur?',
            'intro' => 'Bu sekme turun temel kimliği. Aşağıdaki sırayla ilerleyin:',
            'steps' => [
                '<strong>Başlık</strong>: Turun adını her dil için yazın (en üstte).',
                '<strong>Tip</strong>: Cruise / Paket / Günlük / Feribot. Cruise seçerseniz <strong>Gemi</strong> alanı zorunlu olur.',
                '<strong>Slug</strong>: URL için kısa ad (otomatik değil, elle girin — örn. <code>bodrum-cruise-7-gun</code>).',
                'Sağdaki <strong>Fiyatlandırma</strong> kartından temel fiyat + para birimi + satış durumunu seçin.',
                'Etiketler ve ek kategoriler isteğe bağlı — frontend filtre menüsünü besler.',
            ],
            'note' => 'Tur kaydedildikten sonra Rota, Fiyat Grupları ve Tarihler sekmeleri aktif olur.',
        ])

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT col --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Başlık + alt başlık — turun en kritik alanı, en üstte --}}
                <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
                    <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-heading text-indigo-500"></i> Tur Başlığı
                        <span class="text-gray-400 cursor-help" title="Her aktif dil için ayrı başlık girin. Liste, kart ve detay sayfasında görünür. En az bir dilde başlık zorunludur.">
                            <i class="fas fa-circle-question text-xs"></i>
                        </span>
                    </h2>
                    @foreach($languages as $i => $lang)
                        @php $trH = $translations[$lang->id] ?? null; @endphp
                        <div class="border border-gray-200 rounded-lg p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] uppercase font-semibold text-gray-500">{{ $lang->name }}</span>
                                <span class="text-[10px] font-mono text-gray-400">{{ $lang->code }}</span>
                            </div>
                            <input type="hidden" name="translations[{{ $i }}][language_id]" value="{{ $lang->id }}">
                            <input type="text" name="translations[{{ $i }}][title]" required
                                   value="{{ old("translations.$i.title", $trH->title ?? '') }}"
                                   placeholder="Tur başlığı * (örn. MSC Akdeniz 7 Gece)"
                                   @if($i === 0) @input="if (!slugTouched) slug = slugify($event.target.value)" @endif
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium">
                            @if($i === 0)
                                <p class="text-[10px] text-gray-400">
                                    <i class="fas fa-link mr-0.5"></i>
                                    İlk dilin başlığından URL (slug) otomatik üretilir — Kimlik kartından düzenleyebilirsiniz.
                                </p>
                            @endif
                            <input type="text" name="translations[{{ $i }}][subtitle]"
                                   value="{{ old("translations.$i.subtitle", $trH->subtitle ?? '') }}"
                                   placeholder="Alt başlık (opsiyonel — örn. Erken rezervasyon avantajıyla)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    @endforeach
                    <p class="text-xs text-gray-400">
                        Uzun açıklamalar (kısa açıklama, detay, öne çıkanlar) <strong>5. Açıklamalar</strong> sekmesinde.
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
                    <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-id-card text-indigo-500"></i> Kimlik & Sınıflandırma
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tip *</label>
                            @php $selectedType = old('type', $tour->type?->value); @endphp
                            <select name="type" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm {{ $selectedType ? '' : 'text-gray-400' }}">
                                <option value="" {{ $selectedType ? '' : 'selected' }} disabled>— Tur tipini seçin —</option>
                                @foreach($types as $t)
                                    <option value="{{ $t->value }}" {{ $selectedType === $t->value ? 'selected' : '' }}>
                                        {{ $t->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Cruise seçerseniz gemi alanı zorunlu olur.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durum *</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="draft"     {{ old('status', $tour->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Taslak</option>
                                <option value="published" {{ old('status', $tour->status) === 'published' ? 'selected' : '' }}>Yayında</option>
                                <option value="archived"  {{ old('status', $tour->status) === 'archived'  ? 'selected' : '' }}>Arşivlendi</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Slug (URL) *
                                <span class="text-gray-400 cursor-help" title="Başlıktan otomatik üretilir. Elle değiştirirseniz otomatik güncelleme durur.">
                                    <i class="fas fa-circle-question text-xs"></i>
                                </span>
                            </label>
                            <input type="text" name="slug" required
                                   x-model="slug" @input="slugTouched = true"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                                   placeholder="bodrum-cruise-7-gun">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                SKU <span class="text-xs text-gray-400 font-normal">opsiyonel</span>
                            </label>
                            <input type="text" name="sku" value="{{ old('sku', $tour->sku) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                                   placeholder="CRZ-MED-2026-07">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Primary Kategori</label>
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Gemi
                                <span class="text-xs text-amber-600 font-normal">(cruise için zorunlu)</span>
                            </label>
                            <select name="ship_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">— Gemi yok —</option>
                                @foreach($ships as $ship)
                                    <option value="{{ $ship->id }}" {{ (int) old('ship_id', $tour->ship_id) === $ship->id ? 'selected' : '' }}>
                                        {{ $ship->name }} ({{ $ship->company?->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Secondary categories m2m --}}
                <div class="bg-white rounded-xl shadow-sm border p-5 space-y-3">
                    <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-folder-tree text-indigo-500"></i> Ek Kategoriler
                        <span class="text-xs text-gray-400 font-normal">(secondary — primary'ye ek)</span>
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 max-h-48 overflow-y-auto">
                        @foreach($categories as $cat)
                            <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="secondary_category_ids[]" value="{{ $cat->id }}"
                                       {{ in_array($cat->id, $selectedSecCatIds, true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-indigo-600 rounded">
                                <span class="text-sm truncate">{{ $cat->translations->first()?->name ?? $cat->slug }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Etiketler m2m — Pazarlama + Kampanya (tek pivot, tag_type'a göre 2 grup) --}}
                @php
                    $marketingTags = $allTags->where('tag_type', 'marketing');
                    $campaignTags  = $allTags->where('tag_type', 'campaign');
                @endphp
                <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
                    <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-tags text-indigo-500"></i> Pazarlama Etiketleri
                        <span class="text-xs text-gray-400 font-normal">(özellik filtresi)</span>
                    </h2>
                    @if($marketingTags->isEmpty())
                        <p class="text-sm text-amber-600">Henüz etiket yok — <a href="{{ route('admin.tour-tags.create') }}" class="underline">ekleyin</a>.</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($marketingTags as $tag)
                                @php $trTag = $tag->translations->first(); @endphp
                                <label class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-full hover:bg-gray-50 cursor-pointer text-xs">
                                    <input type="checkbox" name="tour_tag_ids[]" value="{{ $tag->id }}"
                                           {{ in_array($tag->id, $selectedTagIds, true) ? 'checked' : '' }}
                                           class="h-3 w-3 text-indigo-600 rounded">
                                    @if($tag->icon)<span class="text-gray-500">{{ $tag->icon }}</span>@endif
                                    <span>{{ $trTag->name ?? $tag->slug }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <div class="border-t border-gray-100 pt-4">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 mb-2">
                            <i class="fas fa-bullhorn text-amber-500"></i> Kampanyalar
                            <span class="text-xs text-gray-400 font-normal">(zaman/promosyon)</span>
                        </h3>
                        @if($campaignTags->isEmpty())
                            <p class="text-xs text-gray-400">Kampanya etiketi yok.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach($campaignTags as $tag)
                                    @php $trTag = $tag->translations->first(); @endphp
                                    <label class="inline-flex items-center gap-2 px-3 py-1.5 border border-amber-200 bg-amber-50/40 rounded-full hover:bg-amber-50 cursor-pointer text-xs">
                                        <input type="checkbox" name="tour_tag_ids[]" value="{{ $tag->id }}"
                                               {{ in_array($tag->id, $selectedTagIds, true) ? 'checked' : '' }}
                                               class="h-3 w-3 text-amber-600 rounded">
                                        @if($tag->icon)<span class="text-amber-600">{{ $tag->icon }}</span>@endif
                                        <span>{{ $trTag->name ?? $tag->slug }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Flight info (conditional) --}}
                <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="includes_flight" value="0">
                        <input type="checkbox" id="includes_flight" name="includes_flight" value="1"
                               x-data="{}" @change="$dispatch('flight-toggle', $event.target.checked)"
                               {{ old('includes_flight', $tour->includes_flight ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 rounded">
                        <span class="text-sm font-medium text-gray-700">
                            <i class="fas fa-plane text-indigo-500"></i> Uçaklı paket (havayolu bilgilerini gir)
                        </span>
                    </label>

                    <div x-data="{ show: {{ old('includes_flight', $tour->includes_flight ?? false) ? 'true' : 'false' }} }"
                         @flight-toggle.window="show = $event.detail"
                         x-show="show" x-cloak
                         class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input type="text" name="flight_airline"
                               value="{{ old('flight_airline', $flightInfo['airline'] ?? '') }}"
                               placeholder="Havayolu (THY, Pegasus)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <input type="text" name="flight_code"
                               value="{{ old('flight_code', $flightInfo['code'] ?? '') }}"
                               placeholder="Uçuş kodu (TK1234)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
                        <input type="text" name="flight_departure"
                               value="{{ old('flight_departure', $flightInfo['departure'] ?? '') }}"
                               placeholder="Kalkış havalimanı (IST)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <input type="text" name="flight_arrival"
                               value="{{ old('flight_arrival', $flightInfo['arrival'] ?? '') }}"
                               placeholder="Varış havalimanı (BCN)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>

                {{-- Type-config JSON (advanced) --}}
                <details class="bg-white rounded-xl shadow-sm border p-5">
                    <summary class="font-semibold text-gray-700 cursor-pointer flex items-center gap-2">
                        <i class="fas fa-code text-purple-500"></i> Tip-spesifik Konfig (JSON, advanced)
                    </summary>
                    <textarea name="type_config" rows="6"
                              placeholder='{"departure_port":"İstanbul","return_port":"İzmir"}'
                              class="w-full mt-3 px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono">{{ old('type_config', $typeConfigJson) }}</textarea>
                </details>
            </div>

            {{-- RIGHT col --}}
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
                    <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-dollar-sign text-indigo-500"></i> Fiyat & Kapasite
                    </h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Fiyatlama Modu
                            <span class="text-gray-400 cursor-help" title="Kişi başı: her yolcuya fiyat. Grup: belirli kişi sayısına paket fiyat. Rezervasyon: kabin/tur başına tek fiyat (kişi sayısından bağımsız).">
                                <i class="fas fa-circle-question text-xs"></i>
                            </span>
                        </label>
                        <select name="pricing_mode" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            @foreach($pricingModes as $m)
                                <option value="{{ $m->value }}" {{ old('pricing_mode', $tour->pricing_mode?->value) === $m->value ? 'selected' : '' }}
                                        title="{{ $m->description() }}">
                                    {{ $m->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Satış Durumu
                            <span class="text-gray-400 cursor-help" title="Online ödeme / iletişimle satış / konaklamalı-konaklamasız teklif / sadece bilgi. Frontend'deki 'Rezervasyon Yap' butonunun davranışını belirler.">
                                <i class="fas fa-circle-question text-xs"></i>
                            </span>
                        </label>
                        <select name="sales_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            @foreach($salesStatuses as $s)
                                <option value="{{ $s->value }}" {{ old('sales_status', $tour->sales_status?->value) === $s->value ? 'selected' : '' }}>
                                    {{ $s->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Para Birimi *</label>
                            <select name="currency" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                @foreach($currencyOptions as $code => $label)
                                    <option value="{{ $code }}" {{ $currentCurrency === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sıra</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', $tour->sort_order ?? 0) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Baz Fiyat (kuruş) *</label>
                        <input type="number" name="base_price" required min="0"
                               value="{{ old('base_price', $tour->base_price ?? 0) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
                        <p class="text-xs text-gray-400 mt-1">100 = 1 TL.  Matrix fiyat varsa override eder.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Varsayılan Kapasite *</label>
                        <input type="number" name="capacity_default" required min="0"
                               value="{{ old('capacity_default', $tour->capacity_default ?? 0) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer pt-2 border-t border-gray-100">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1"
                               {{ old('is_featured', $tour->is_featured ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-pink-600 rounded">
                        <span class="text-sm text-gray-700"><i class="fas fa-star text-pink-500"></i> Öne çıkan</span>
                    </label>
                </div>

                @if($tour->exists)
                    <div class="bg-gray-50 rounded-xl border p-3 text-xs text-gray-500 space-y-1">
                        <p><strong>ID:</strong> #{{ $tour->id }}</p>
                        <p><strong>Oluşturuldu:</strong> {{ $tour->created_at?->isoFormat('D MMM YYYY HH:mm') }}</p>
                        <p><strong>Güncellendi:</strong> {{ $tour->updated_at?->isoFormat('D MMM YYYY HH:mm') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ Tab 2: Rota Takvimi ═════════════════════════════════════════ --}}
    <div x-show="activeTab === 2" x-cloak class="space-y-6">

        @include('tours::admin.partials._help', [
            'title' => 'Rota takvimi nasıl oluşturulur?',
            'intro' => 'Rota = gün gün gezi programı (Gün 1, Gün 2, …). Cruise & paket turlar için, günlük turda gerekmez.',
            'steps' => [
                'Önce turu <strong>kaydedin</strong> — rota turun üstüne eklenir.',
                'Her dil için ayrı bir <strong>rota başlığı</strong> tanımlanabilir (TR / EN farklı anlatım).',
                'Gün ekleyin: <strong>Gün 1</strong>, <strong>Gün 2</strong> … sırayla.',
                'Her güne <strong>liman / şehir</strong> + varış-kalkış saati atayın (cruise için kritik).',
                'Aynı limanda birden çok gece olursa art arda günlere aynı limanı yazın (1,1,1,2,3,3 deseni).',
            ],
            'note' => 'Aynı güne birden çok liman (multi-stop) düşebilir — aynı gün numarasını tekrar kullanın.',
        ])

        <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-route text-indigo-500"></i> Rota & Günler
                </h2>
                @if($tour->exists)
                    <a href="{{ route('admin.tours.itinerary.edit', $tour) }}"
                       class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs hover:bg-indigo-700 font-medium">
                        <i class="fas fa-pen mr-1"></i> Rota Editörünü Aç
                    </a>
                @endif
            </div>

            @if(!$tour->exists)
                <p class="text-sm text-gray-500 p-4 bg-gray-50 rounded-lg">Önce turu kaydedin, sonra rota girin.</p>
            @elseif($tour->itineraries->isEmpty())
                <div class="p-6 text-center bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">Henüz rota girilmemiş.</p>
                    <a href="{{ route('admin.tours.itinerary.edit', $tour) }}" class="text-indigo-600 hover:underline text-sm">
                        Rota editörünü aç →
                    </a>
                </div>
            @else
                @foreach($tour->itineraries as $itinerary)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-gray-700">
                                {{ $itinerary->title ?? 'Rota' }}
                                <span class="text-[10px] font-mono text-gray-400 ml-1">lang #{{ $itinerary->language_id }}</span>
                            </h3>
                            <a href="{{ route('admin.tours.itinerary.edit', ['tour' => $tour, 'lang' => $itinerary->language_id]) }}"
                               class="text-xs text-indigo-600 hover:underline">Düzenle</a>
                        </div>
                        <ul class="space-y-1 text-sm text-gray-600">
                            @foreach($itinerary->days as $day)
                                <li class="flex items-center gap-2">
                                    <span class="font-mono text-xs text-gray-400 w-8">G{{ $day->day_number }}</span>
                                    <span>{{ $day->title }}</span>
                                    <span class="text-xs text-gray-400">({{ $day->stops->count() }} durak)</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- ═══ Tab 3: Genel Fiyatlar ═══════════════════════════════════════ --}}
    <div x-show="activeTab === 3" x-cloak class="space-y-6">

        @include('tours::admin.partials._help', [
            'title' => 'Genel Fiyatlar ne içerir?',
            'intro' => 'Baz fiyat + para birimi 1. sekmede. Burada ek ücretler ve fiyat açıklaması var:',
            'steps' => [
                '<strong>Bilgi Amaçlı Ücretler</strong>: online tahsil EDİLMEZ (vize, havaalanı vergisi). Sadece "Bilmeniz gerekenler" olarak gösterilir.',
                '<strong>Online Ekstralar</strong>: müşteri sepete ekler ve öder (transfer, sigorta, içecek paketi).',
                '<strong>Fiyat Açıklaması</strong>: "Doluluğa göre değişebilir…" gibi disclaimer (dil bazlı).',
            ],
            'note' => 'Kabin/oda fiyat matrisi 4. Tarih & Fiyatlar sekmesindeki fiyat gruplarındadır.',
        ])

        {{-- Ekstralar (Alpine) — info-only + booking, iki ayrı liste --}}
        <div class="bg-white rounded-xl shadow-sm border p-5 space-y-6"
             x-data="{
                 infoMaster: {{ \Illuminate\Support\Js::from($infoExtraMaster) }},
                 infoExtras: {{ \Illuminate\Support\Js::from($infoExtraRows) }},
                 bookingExtras: {{ \Illuminate\Support\Js::from($bookingExtraRows) }},
                 addInfo() { this.infoExtras.push({ id:null, info_extra_id:'', name:'', price:'', currency:'{{ $tour->currency }}', per_person:false }); },
                 addBooking() { this.bookingExtras.push({ id:null, name:'', description:'', price:'', pricing_mode:'per_booking', is_required:false }); },
                 onInfoMaster(row) {
                     const m = this.infoMaster.find(x => String(x.id) === String(row.info_extra_id));
                     if (m) { if(!row.name) row.name = m.label; if(!row.price) row.price = m.amount; if(m.currency) row.currency = m.currency; row.per_person = m.per_person; }
                 }
             }">

            {{-- Info-only --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-circle-info text-amber-500"></i> Bilgi Amaçlı Ücretler
                        <span class="text-xs text-gray-400 font-normal">(online tahsil edilmez)</span>
                    </h3>
                    <button type="button" @click="addInfo()" class="px-3 py-1.5 bg-amber-50 text-amber-700 rounded-lg text-xs hover:bg-amber-100 font-medium">
                        <i class="fas fa-plus mr-1"></i> Ücret Ekle
                    </button>
                </div>
                <template x-if="infoExtras.length === 0">
                    <p class="text-xs text-gray-400 p-3 bg-gray-50 rounded">Henüz bilgi amaçlı ücret yok (vize, havaalanı vergisi vb.).</p>
                </template>
                <template x-for="(row, idx) in infoExtras" :key="'i'+idx">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end border border-gray-100 rounded-lg p-2">
                        <div class="md:col-span-3">
                            <label class="block text-[10px] text-gray-500 mb-1">Hazır kalem</label>
                            <select :name="`info_extras[${idx}][info_extra_id]`" x-model="row.info_extra_id" @change="onInfoMaster(row)"
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs">
                                <option value="">— Seçiniz —</option>
                                <template x-for="m in infoMaster" :key="m.id">
                                    <option :value="m.id" x-text="m.label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[10px] text-gray-500 mb-1">Ücret Adı</label>
                            <input type="text" :name="`info_extras[${idx}][name]`" x-model="row.name" placeholder="Vize Ücreti"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] text-gray-500 mb-1">Fiyat (kuruş)</label>
                            <input type="number" min="0" :name="`info_extras[${idx}][price]`" x-model="row.price"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs font-mono">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] text-gray-500 mb-1">Kur</label>
                            <select :name="`info_extras[${idx}][currency]`" x-model="row.currency"
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs font-mono">
                                <option value="TRY">TRY</option><option value="EUR">EUR</option><option value="USD">USD</option>
                            </select>
                        </div>
                        <div class="md:col-span-1 flex items-center gap-1 pb-1.5">
                            <input type="hidden" :name="`info_extras[${idx}][per_person]`" value="0">
                            <input type="checkbox" :name="`info_extras[${idx}][per_person]`" value="1" x-model="row.per_person" class="h-3.5 w-3.5 text-amber-600 rounded">
                            <span class="text-[10px] text-gray-500">Kişi başı</span>
                        </div>
                        <div class="md:col-span-1 flex justify-end pb-1">
                            <button type="button" @click="infoExtras.splice(idx,1)" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Booking --}}
            <div class="space-y-3 border-t border-gray-100 pt-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-cart-plus text-indigo-500"></i> Online Ekstralar
                        <span class="text-xs text-gray-400 font-normal">(sepete eklenir, ödenir)</span>
                    </h3>
                    <button type="button" @click="addBooking()" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs hover:bg-indigo-100 font-medium">
                        <i class="fas fa-plus mr-1"></i> Ekstra Ekle
                    </button>
                </div>
                <template x-if="bookingExtras.length === 0">
                    <p class="text-xs text-gray-400 p-3 bg-gray-50 rounded">Henüz online ekstra yok (transfer, sigorta vb.).</p>
                </template>
                <template x-for="(row, idx) in bookingExtras" :key="'b'+idx">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end border border-gray-100 rounded-lg p-2">
                        <div class="md:col-span-3">
                            <label class="block text-[10px] text-gray-500 mb-1">Ekstra Adı</label>
                            <input type="text" :name="`booking_extras[${idx}][name]`" x-model="row.name" placeholder="Havalimanı Transferi"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-[10px] text-gray-500 mb-1">Açıklama</label>
                            <input type="text" :name="`booking_extras[${idx}][description]`" x-model="row.description"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] text-gray-500 mb-1">Fiyat (kuruş)</label>
                            <input type="number" min="0" :name="`booking_extras[${idx}][price]`" x-model="row.price"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs font-mono">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] text-gray-500 mb-1">Mod</label>
                            <select :name="`booking_extras[${idx}][pricing_mode]`" x-model="row.pricing_mode"
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs">
                                <option value="per_booking">Rezervasyon başı</option>
                                <option value="per_passenger">Kişi başı</option>
                            </select>
                        </div>
                        <div class="md:col-span-1 flex justify-end items-center gap-1 pb-1">
                            <input type="hidden" :name="`booking_extras[${idx}][is_required]`" value="0">
                            <input type="checkbox" :name="`booking_extras[${idx}][is_required]`" value="1" x-model="row.is_required" class="h-3.5 w-3.5 text-indigo-600 rounded" title="Zorunlu">
                            <button type="button" @click="bookingExtras.splice(idx,1)" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Fiyat disclaimer (per-language) --}}
        <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
            <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-triangle-exclamation text-gray-400"></i> Fiyat Açıklaması
            </h3>
            @foreach($languages as $i => $lang)
                @php $trD = $translations[$lang->id] ?? null; @endphp
                <div>
                    <label class="block text-[10px] uppercase font-semibold text-gray-500 mb-1">{{ $lang->name }}</label>
                    <textarea name="translations[{{ $i }}][price_disclaimer]" rows="2"
                              placeholder="Fiyatlar doluluğa göre değişebilir. Rezervasyon talebinde güncel fiyat iletilir."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.price_disclaimer", $trD->price_disclaimer ?? '') }}</textarea>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ═══ Tab 4: Tarih & Fiyatlar ═════════════════════════════════════ --}}
    <div x-show="activeTab === 4" x-cloak class="space-y-6">

        @include('tours::admin.partials._help', [
            'title' => 'Departure tarihleri ile fiyat grupları nasıl bağlanır?',
            'intro' => 'Departure = turun gerçekleştiği somut kalkış tarihi. Her tarihin kendi kapasitesi var.',
            'steps' => [
                '<strong>Tarih Yönetimi</strong>\'ne gidip kalkış/dönüş tarihi + kapasite girin.',
                'Sonra <strong>3. Genel Fiyatlar</strong> sekmesinde bir fiyat grubu oluşturun.',
                'Fiyat grubunu bu tarihlere atayın — böylece o tarihte hangi fiyatın geçerli olduğu belli olur.',
                'Bir tarihe birden çok grup atanabilir (örn. erken rezervasyon + standart); öncelik sırasını grup sort_order belirler.',
            ],
        ])

        <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-indigo-500"></i> Departure Tarihleri
            </h2>
            @if(!$tour->exists)
                <p class="text-sm text-gray-500">Önce turu kaydedin, sonra tarih ekleyin.</p>
            @else
                <p class="text-xs text-gray-500">
                    Bu turun {{ $tour->dates->count() }} tarihi var.  Tarih bazlı kapasite + fiyat override
                    + PriceGroup ataması nested route'ta yönetilir.
                </p>
                <a href="{{ route('admin.tours.dates.index', $tour) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-lg text-sm hover:bg-amber-600">
                    <i class="fas fa-arrow-right"></i> Tarih Yönetimi'ne Git
                </a>
                @if($tour->dates->isNotEmpty())
                    <div class="mt-3 border-t pt-3">
                        <table class="min-w-full text-xs">
                            <thead><tr class="text-left text-gray-500 uppercase">
                                <th class="py-1">Tarih</th><th class="py-1">Bitiş</th><th class="py-1">Kapasite</th><th class="py-1">Status</th>
                            </tr></thead>
                            <tbody class="divide-y">
                                @foreach($tour->dates->take(10) as $d)
                                    <tr>
                                        <td class="py-1">{{ $d->starts_at?->format('Y-m-d') }}</td>
                                        <td class="py-1">{{ $d->ends_at?->format('Y-m-d') }}</td>
                                        <td class="py-1">{{ $d->capacity_total }}</td>
                                        <td class="py-1">{{ $d->status }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>

        {{-- Fiyat Grupları (pricing engine — eski sistem Tab 4) --}}
        <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-tags text-indigo-500"></i> Fiyat Grupları
                </h2>
                @if($tour->exists)
                    <a href="{{ route('admin.tours.price-groups.create', $tour) }}"
                       class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs hover:bg-indigo-700 font-medium">
                        <i class="fas fa-plus mr-1"></i> Yeni Fiyat Grubu
                    </a>
                @endif
            </div>
            <p class="text-xs text-gray-500">
                Adlandırılmış pricing senaryosu ("Yaz 2026", "Erken Rezervasyon") — oda/kabin × kişi-tier
                matrisi taşır, departure tarihlerine atanır.
            </p>

            @if(!$tour->exists)
                <p class="text-sm text-gray-500 p-4 bg-gray-50 rounded-lg">Önce turu + tarihleri kaydedin.</p>
            @elseif($tour->priceGroups->isEmpty())
                <div class="p-6 text-center bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">Henüz fiyat grubu yok.</p>
                    <a href="{{ route('admin.tours.price-groups.create', $tour) }}" class="text-indigo-600 hover:underline text-sm">İlkini oluştur →</a>
                </div>
            @else
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-3 py-2">Grup</th><th class="px-3 py-2">Tarih</th>
                            <th class="px-3 py-2">Satır</th><th class="px-3 py-2 text-right">Eylem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($tour->priceGroups as $pg)
                            @php $pgTr = $pg->translations->first(); @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    <div class="font-medium text-gray-800">{{ $pgTr->name ?? "PG-{$pg->id}" }}</div>
                                    @if($pg->campaign_text)<div class="text-[10px] text-amber-600 italic truncate max-w-[280px]">{{ $pg->campaign_text }}</div>@endif
                                </td>
                                <td class="px-3 py-2 text-gray-500">{{ $pg->dates()->count() }}</td>
                                <td class="px-3 py-2 text-gray-500">{{ $pg->cabinPrices()->count() }}</td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ route('admin.tours.price-groups.edit', ['tour' => $tour, 'price_group' => $pg]) }}"
                                       class="text-indigo-600 hover:underline text-xs">Düzenle</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ═══ Tab 5: Açıklamalar (Translations) ═══════════════════════════ --}}
    <div x-show="activeTab === 5" x-cloak class="space-y-6">

        @include('tours::admin.partials._help', [
            'title' => 'Açıklama alanları ne işe yarar?',
            'intro' => 'Turun pazarlama metinleri. Başlık 1. sekmede; burada uzun içerik var:',
            'steps' => [
                '<strong>Kısa açıklama</strong>: Liste/kart üzerinde görünen 1-2 cümle.',
                '<strong>Detaylı açıklama</strong>: Tur detay sayfasının ana metni (HTML destekli).',
                '<strong>Öne çıkanlar</strong>: Madde madde özellikler (her satır bir madde).',
                '<strong>Önemli bilgi</strong>: Yaş limiti, sağlık şartı, vize uyarısı gibi notlar.',
            ],
            'note' => 'Her dil için ayrı doldurun. Boş bırakılan diller frontend\'de gizlenir.',
        ])

        <div class="bg-white rounded-xl shadow-sm border p-5 space-y-5">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-align-left text-indigo-500"></i> İçerik Metinleri
            </h2>

            @foreach($languages as $i => $lang)
                @php $tr = $translations[$lang->id] ?? null; @endphp
                <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase font-semibold text-gray-500">{{ $lang->name }}</span>
                        <span class="text-[10px] font-mono text-gray-400">{{ $lang->code }}</span>
                    </div>

                    <label class="block text-xs font-medium text-gray-600">
                        Kısa açıklama
                        <span class="text-gray-400 cursor-help" title="Liste ve kart görünümünde gösterilir. 1-2 cümle, max ~160 karakter ideal.">
                            <i class="fas fa-circle-question"></i>
                        </span>
                    </label>
                    <textarea name="translations[{{ $i }}][short_description]" rows="2"
                              placeholder="Kart üzerinde görünen kısa tanıtım"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.short_description", $tr->short_description ?? '') }}</textarea>

                    <label class="block text-xs font-medium text-gray-600">
                        Detaylı açıklama
                        <span class="text-gray-400 cursor-help" title="Tur detay sayfasının ana gövdesi. HTML etiketleri kullanılabilir.">
                            <i class="fas fa-circle-question"></i>
                        </span>
                    </label>
                    <textarea name="translations[{{ $i }}][description]" rows="6"
                              placeholder="Detaylı açıklama (HTML destekli)"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.description", $tr->description ?? '') }}</textarea>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Öne çıkanlar</label>
                            <textarea name="translations[{{ $i }}][highlights]" rows="4"
                                      placeholder="Her satır bir madde&#10;Örn: Tüm öğünler dahil&#10;Limanlarda rehberli tur"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">{{ old("translations.$i.highlights", $tr->highlights ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Önemli bilgi</label>
                            <textarea name="translations[{{ $i }}][important_info]" rows="4"
                                      placeholder="Yaş limiti, sağlık şartları, vize uyarısı"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">{{ old("translations.$i.important_info", $tr->important_info ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ═══ Tab 6: SEO ══════════════════════════════════════════════════ --}}
    <div x-show="activeTab === 6" x-cloak class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border p-5 space-y-5">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-search text-indigo-500"></i> SEO Meta Bilgileri
            </h2>

            @foreach($languages as $i => $lang)
                @php $tr = $translations[$lang->id] ?? null; @endphp
                <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase font-semibold text-gray-500">{{ $lang->name }}</span>
                        <span class="text-[10px] font-mono text-gray-400">{{ $lang->code }}</span>
                    </div>
                    {{-- language_id Tab 1 başlık bloğunda emit ediliyor; aynı $i index'i
                         tüm sekmelerde translations[$i] payload'unu tek array'de birleştirir. --}}
                    <input type="text" name="translations[{{ $i }}][meta_title]"
                           value="{{ old("translations.$i.meta_title", $tr->meta_title ?? '') }}"
                           placeholder="Meta title (60 karakter altı önerilir)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <textarea name="translations[{{ $i }}][meta_description]" rows="3"
                              placeholder="Meta description (160 karakter altı önerilir)"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.meta_description", $tr->meta_description ?? '') }}</textarea>
                    <input type="url" name="translations[{{ $i }}][og_image_url]"
                           value="{{ old("translations.$i.og_image_url", $tr->og_image_url ?? '') }}"
                           placeholder="OG image URL (sosyal paylaşım önizleme)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
                </div>
            @endforeach

            <details class="border border-gray-200 rounded-lg p-4">
                <summary class="text-sm font-medium text-gray-700 cursor-pointer">
                    <i class="fas fa-code text-purple-500 mr-1"></i> Structured Data JSON-LD (advanced)
                </summary>
                <textarea name="structured_data_json" rows="8"
                          placeholder='{"@@context":"https://schema.org","@@type":"TouristTrip","name":"..."}'
                          class="w-full mt-3 px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono">{{ old('structured_data_json', $structuredJson) }}</textarea>
            </details>
        </div>
    </div>

    {{-- ═══ Tab 7: Harita & Destinasyonlar ══════════════════════════════ --}}
    <div x-show="activeTab === 7" x-cloak class="space-y-6">

        @include('tours::admin.partials._help', [
            'title' => 'Destinasyon seçimi ne için?',
            'intro' => 'Destinasyon = turun coğrafi kapsamı (Akdeniz, Yunan Adaları, Karayipler).',
            'steps' => [
                'Turun uğradığı bölgeleri işaretleyin — bir tur birden çok destinasyona ait olabilir.',
                'Frontend\'de bölge landing sayfaları (örn. /destinasyon/akdeniz) bu turları otomatik listeler.',
                'Seçim sırası frontend\'deki gösterim sırasını belirler.',
            ],
            'note' => 'Destinasyon master listesini Cruise Yönetimi → Destinasyonlar\'dan yönetebilirsiniz.',
        ])

        <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-map-marked-alt text-indigo-500"></i> Destinasyonlar
            </h2>
            <p class="text-xs text-gray-500">
                Bu turun kapsadığı destinasyonlar.  Seçim sırası frontend listesinde aynı sırada görünür.
            </p>

            @if($allDestinations->isEmpty())
                <p class="text-sm text-amber-600">
                    Henüz destinasyon yok — <a href="{{ route('admin.destinations.create') }}" class="underline">ekleyin</a>.
                </p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 max-h-96 overflow-y-auto p-2 bg-gray-50 rounded-lg">
                    @foreach($allDestinations as $dest)
                        @php $trDest = $dest->translations->first(); @endphp
                        <label class="flex items-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded hover:bg-indigo-50 cursor-pointer">
                            <input type="checkbox" name="destination_ids[]" value="{{ $dest->id }}"
                                   {{ in_array($dest->id, $selectedDestIds, true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 rounded">
                            <span class="text-sm truncate">{{ $trDest->name ?? $dest->slug }}</span>
                            @if($dest->is_featured)<i class="fas fa-star text-amber-400 text-xs"></i>@endif
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ Tab 8: Resimler ═════════════════════════════════════════════ --}}
    <div x-show="activeTab === 8" x-cloak class="space-y-6">

        @include('tours::admin.partials._help', [
            'title' => 'Hangi görsel nerede kullanılır?',
            'steps' => [
                '<strong>Kapak</strong>: Liste/kart ve detay sayfası üst görseli (tek dosya, yatay önerilir).',
                '<strong>Galeri</strong>: Detay sayfasındaki foto galerisi (çoklu yükleme).',
                '<strong>Broşür</strong>: İndirilebilir PDF (tek dosya).',
            ],
            'note' => 'Görseller kaydetmeden önce yüklenmez — dosya seçip "Kaydet" deyin. Mevcut görselin üstüne fareyle gelince silme (×) çıkar.',
        ])

        <div class="bg-white rounded-xl shadow-sm border p-5 space-y-5">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-images text-indigo-500"></i> Görseller & Broşür
            </h2>

            {{-- Cover --}}
            @php
                $coverUrl = $tour->exists ? $tour->getFirstMediaUrl('cover') : null;
                $coverMedia = $tour->exists ? $tour->getFirstMedia('cover') : null;
            @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kapak Görseli (tek dosya)</label>
                @if($coverUrl)
                    <div class="relative inline-block mb-2">
                        <img src="{{ $coverUrl }}" alt="cover" class="h-32 rounded border border-gray-200">
                        @if($coverMedia)
                            <form action="{{ route('admin.tours.media.delete', ['tour' => $tour, 'mediaId' => $coverMedia->id]) }}"
                                  method="POST" class="absolute top-1 right-1"
                                  onsubmit="return confirm('Kapağı silmek istediğinize emin misiniz?');">
                                @csrf
                                <button type="submit" class="bg-red-500 text-white rounded-full w-7 h-7 text-xs">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
                <input type="file" name="cover" accept="image/*"
                       class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>

            {{-- Gallery --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Galeri (çoklu)</label>
                @if($tour->exists)
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-2 mb-3">
                        @foreach($tour->getMedia('gallery') as $media)
                            <div class="relative group">
                                <img src="{{ $media->getUrl() }}" alt="" class="h-20 w-full object-cover rounded border border-gray-200">
                                <form action="{{ route('admin.tours.media.delete', ['tour' => $tour, 'mediaId' => $media->id]) }}"
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

            {{-- Brochure --}}
            @php
                $brochure = $tour->exists ? $tour->getFirstMedia('brochure') : null;
            @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Broşür PDF (tek dosya)</label>
                @if($brochure)
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                        <a href="{{ $brochure->getUrl() }}" target="_blank" class="text-sm text-indigo-600 underline">{{ $brochure->name }}</a>
                        <form action="{{ route('admin.tours.media.delete', ['tour' => $tour, 'mediaId' => $brochure->id]) }}"
                              method="POST" class="inline"
                              onsubmit="return confirm('Broşürü silmek istediğinize emin misiniz?');">
                            @csrf
                            <button type="submit" class="text-xs text-red-600 hover:underline">Sil</button>
                        </form>
                    </div>
                @endif
                <input type="file" name="brochure" accept=".pdf"
                       class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>
        </div>
    </div>

    {{-- ═══ Tab 9: Yorumlar ═════════════════════════════════════════════ --}}
    <div x-show="activeTab === 9" x-cloak class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-comments text-indigo-500"></i> Yorumlar
            </h2>
            <div class="p-6 text-center bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">
                    <i class="fas fa-info-circle text-gray-400 mr-1"></i> Yorum modülü
                </p>
                <p class="text-xs text-gray-500">
                    Bu tur için bırakılan yorumlar burada listelenecek.  Phase 5'te yorum onay/moderasyon
                    akışı eklenecek; şimdilik genel <a href="{{ route('admin.reviews.index') }}" class="underline">yorumlar paneline</a>
                    gidip filtre ile bu turun yorumlarını görebilirsiniz.
                </p>
            </div>
        </div>
    </div>

    {{-- Sticky save bar --}}
    <div class="sticky bottom-0 bg-white border-t border-gray-200 -mx-4 px-4 py-3 mt-6 flex items-center justify-between shadow-lg">
        <p class="text-xs text-gray-500" x-show="activeTab !== 9">
            <i class="fas fa-info-circle text-gray-400 mr-1"></i>
            Tüm sekmeler tek formda — Kaydet butonu tüm değişiklikleri commit eder.
        </p>
        <button type="submit"
                class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
            <i class="fas fa-save mr-1"></i> Kaydet
        </button>
    </div>
</form>
@endsection
