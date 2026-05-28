@extends('admin.layouts.app')
@section('title', ($group->exists ? 'Fiyat Grubu Düzenle' : 'Yeni Fiyat Grubu') . ' — ' . ($tour->translations->first()?->title ?? $tour->slug))

@php
    // Para birimi seçenekleri — Tour form ile aynı liste.
    $currencyOptions = ['TRY', 'EUR', 'USD'];

    // Cabins koleksiyonunu rows-to-render olarak hazırla.
    // Cruise: ship.cabins listesinden her biri.
    // Non-cruise: tek "_generic" sentinel row (cabin_id=null).
    $matrixRows = [];
    if ($tour->ship && $cabins->isNotEmpty()) {
        foreach ($cabins as $cabin) {
            $matrixRows[] = [
                'key'    => $cabin->id,
                'cabin'  => $cabin,
                'label'  => $cabin->translations->first()?->name ?? $cabin->code ?? ('Cabin #' . $cabin->id),
                'sub'    => $cabin->category?->translations->first()?->name ?? '',
            ];
        }
    } else {
        $matrixRows[] = [
            'key'   => '_generic',
            'cabin' => null,
            'label' => 'Genel Fiyat (kabinsiz)',
            'sub'   => 'Paket / günlük / ferry turlar',
        ];
    }
@endphp

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.tours.price-groups.index', $tour) }}" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            {{ $group->exists ? 'Fiyat Grubu Düzenle' : 'Yeni Fiyat Grubu' }}
        </h1>
        <p class="text-xs text-gray-500">
            Tur: <strong>{{ $tour->translations->first()?->title ?? $tour->slug }}</strong>
            @if($tour->ship)
                · Gemi: <strong>{{ $tour->ship->name }}</strong>
            @endif
        </p>
    </div>
</div>

@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST"
      action="{{ $group->exists
          ? route('admin.tours.price-groups.update', ['tour' => $tour, 'price_group' => $group])
          : route('admin.tours.price-groups.store', $tour) }}"
      class="space-y-6">
    @csrf
    @if($group->exists)@method('PUT')@endif

    {{-- Identity --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-cog text-indigo-500"></i> Grup Ayarları
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min Kişi Sayısı</label>
                <input type="number" name="min_persons" value="{{ old('min_persons', $group->min_persons) }}" min="1"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <p class="text-xs text-gray-400 mt-1">Bu fiyatın geçerli olması için min yolcu (örn. çift için min 2).</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kontenjan Kotası</label>
                <input type="number" name="capacity_quota" value="{{ old('capacity_quota', $group->capacity_quota) }}" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <p class="text-xs text-gray-400 mt-1">Bu grup için ayrılan kontenjan (boş = limitsiz).</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $group->sort_order ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <p class="text-xs text-gray-400 mt-1">Düşük değer = öncelik (QuoteService bunu uygular).</p>
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kampanya Metni</label>
                <input type="text" name="campaign_text" value="{{ old('campaign_text', $group->campaign_text) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       placeholder="Erken rezervasyon avantajı — %20 indirim 31 Mart'a kadar">
            </div>
        </div>

        <div class="flex flex-wrap gap-6 pt-2 border-t border-gray-100">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $group->is_active ?? true) ? 'checked' : '' }}
                       class="h-4 w-4 text-indigo-600 rounded">
                <span class="text-sm text-gray-700">Aktif</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="adult_priority" value="0">
                <input type="checkbox" name="adult_priority" value="1"
                       {{ old('adult_priority', $group->adult_priority ?? true) ? 'checked' : '' }}
                       class="h-4 w-4 text-indigo-600 rounded">
                <span class="text-sm text-gray-700">Yetişkin önceliği (1st adult = single price)</span>
            </label>
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
                <input type="text" name="translations[{{ $i }}][name]" required
                       value="{{ old("translations.$i.name", $tr->name ?? '') }}"
                       placeholder="Grup adı (Yaz 2026 Standart)"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <textarea name="translations[{{ $i }}][description]" rows="2"
                          placeholder="Açıklama (opsiyonel)"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.description", $tr->description ?? '') }}</textarea>
            </div>
        @endforeach
    </div>

    {{-- Date assignments --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-calendar-check text-indigo-500"></i> Atanmış Tarihler
        </h2>
        <p class="text-xs text-gray-500">
            Bu grup hangi departure tarihlerine geçerli? Boş bırakılırsa hiçbir tarihte aktif olmaz.
        </p>

        @if($tour->dates->isEmpty())
            <p class="text-sm text-amber-600">
                Henüz tarih yok — önce <a href="{{ route('admin.tours.dates.index', $tour) }}" class="underline">departure ekleyin</a>.
            </p>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 max-h-64 overflow-y-auto p-2 bg-gray-50 rounded-lg">
                @foreach($tour->dates as $date)
                    <label class="flex items-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded hover:bg-indigo-50 cursor-pointer">
                        <input type="checkbox" name="date_ids[]" value="{{ $date->id }}"
                               {{ in_array($date->id, $selectedDateIds, true) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 rounded">
                        <div class="text-xs">
                            <div class="font-medium">{{ $date->starts_at?->format('d.m.Y') }}</div>
                            @if($date->ends_at)
                                <div class="text-gray-400">→ {{ $date->ends_at->format('d.m.Y') }}</div>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Cabin Matrix Grid --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-table text-indigo-500"></i> Fiyat Matrisi
            </h2>
            <span class="text-xs text-gray-500">
                @if($tour->ship)
                    {{ $cabins->count() }} kabin × 6 fiyat sütunu
                @else
                    Genel fiyat (kabinsiz)
                @endif
            </span>
        </div>
        <p class="text-xs text-gray-500">
            Fiyatlar <strong>kuruş</strong> cinsinden tam sayı.  100 = 1 TL.  Boş = "Sorunuz" (manuel teklif).
            Çocuk / bebek yaş aralıkları sadece child / baby fiyatı dolu satırlarda anlam taşır.
        </p>

        <div class="overflow-x-auto">
            <table class="min-w-full text-xs border-collapse">
                <thead class="bg-gray-50 text-gray-600 uppercase text-[10px]">
                    <tr>
                        <th class="px-2 py-2 text-left sticky left-0 bg-gray-50 min-w-[180px]">Kabin</th>
                        <th class="px-2 py-2 text-left min-w-[200px]">Hesaplama</th>
                        <th class="px-2 py-2 min-w-[60px]">Ccy</th>
                        <th class="px-2 py-2 min-w-[100px]" title="Tek kişi">1ki</th>
                        <th class="px-2 py-2 min-w-[100px]" title="Çift kişi">2ki</th>
                        <th class="px-2 py-2 min-w-[100px]" title="Üçüncü kişi">3ki</th>
                        <th class="px-2 py-2 min-w-[100px]" title="Dördüncü kişi">4ki</th>
                        <th class="px-2 py-2 min-w-[100px]">Çocuk</th>
                        <th class="px-2 py-2 min-w-[80px]" title="Çocuk yaş aralığı">Yaş ç</th>
                        <th class="px-2 py-2 min-w-[100px]">Bebek</th>
                        <th class="px-2 py-2 min-w-[80px]" title="Bebek yaş aralığı">Yaş b</th>
                        <th class="px-2 py-2">Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($matrixRows as $i => $row)
                        @php
                            $existing = $existingPrices[$row['key']] ?? null;
                            $cabinIdVal = $row['key']; // 'cabin_id' input value (id veya '_generic')
                        @endphp
                        <tr class="border-t border-gray-100 hover:bg-indigo-50/30">
                            <td class="px-2 py-2 sticky left-0 bg-white">
                                <div class="font-medium text-gray-800">{{ $row['label'] }}</div>
                                @if($row['sub'])
                                    <div class="text-[10px] text-gray-400">{{ $row['sub'] }}</div>
                                @endif
                                <input type="hidden" name="cabin_prices[{{ $i }}][cabin_id]" value="{{ $cabinIdVal }}">
                            </td>
                            <td class="px-2 py-2">
                                <select name="cabin_prices[{{ $i }}][calculation_method]"
                                        class="w-full px-2 py-1 border border-gray-300 rounded text-xs">
                                    @foreach($calculationMethods as $cm)
                                        <option value="{{ $cm->value }}"
                                                title="{{ $cm->description() }}"
                                                {{ old("cabin_prices.$i.calculation_method", $existing->calculation_method?->value ?? 'standart_doublex2') === $cm->value ? 'selected' : '' }}>
                                            {{ $cm->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-2 py-2">
                                @php $rowCcy = old("cabin_prices.$i.currency", $existing->currency ?? $tour->currency); @endphp
                                <select name="cabin_prices[{{ $i }}][currency]"
                                        class="w-full px-1 py-1 border border-gray-300 rounded text-xs font-mono">
                                    @foreach($currencyOptions as $ccy)
                                        <option value="{{ $ccy }}" {{ $rowCcy === $ccy ? 'selected' : '' }}>{{ $ccy }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" min="0" name="cabin_prices[{{ $i }}][price_single]"
                                       value="{{ old("cabin_prices.$i.price_single", $existing->price_single ?? '') }}"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-xs font-mono"
                                       placeholder="—">
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" min="0" name="cabin_prices[{{ $i }}][price_double]"
                                       value="{{ old("cabin_prices.$i.price_double", $existing->price_double ?? '') }}"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-xs font-mono"
                                       placeholder="—">
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" min="0" name="cabin_prices[{{ $i }}][price_triple]"
                                       value="{{ old("cabin_prices.$i.price_triple", $existing->price_triple ?? '') }}"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-xs font-mono"
                                       placeholder="—">
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" min="0" name="cabin_prices[{{ $i }}][price_quad]"
                                       value="{{ old("cabin_prices.$i.price_quad", $existing->price_quad ?? '') }}"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-xs font-mono"
                                       placeholder="—">
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" min="0" name="cabin_prices[{{ $i }}][price_child]"
                                       value="{{ old("cabin_prices.$i.price_child", $existing->price_child ?? '') }}"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-xs font-mono"
                                       placeholder="—">
                            </td>
                            <td class="px-2 py-2">
                                <div class="flex items-center gap-1">
                                    <input type="number" min="0" max="30" name="cabin_prices[{{ $i }}][child_age_min]"
                                           value="{{ old("cabin_prices.$i.child_age_min", $existing->child_age_min ?? 2) }}"
                                           class="w-12 px-1 py-1 border border-gray-300 rounded text-xs font-mono"
                                           title="Min yaş">
                                    <span class="text-gray-400 text-xs">-</span>
                                    <input type="number" min="0" max="30" name="cabin_prices[{{ $i }}][child_age_max]"
                                           value="{{ old("cabin_prices.$i.child_age_max", $existing->child_age_max ?? 11) }}"
                                           class="w-12 px-1 py-1 border border-gray-300 rounded text-xs font-mono"
                                           title="Max yaş">
                                </div>
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" min="0" name="cabin_prices[{{ $i }}][price_baby]"
                                       value="{{ old("cabin_prices.$i.price_baby", $existing->price_baby ?? '') }}"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-xs font-mono"
                                       placeholder="—">
                            </td>
                            <td class="px-2 py-2">
                                <div class="flex items-center gap-1">
                                    <input type="number" min="0" max="30" name="cabin_prices[{{ $i }}][baby_age_min]"
                                           value="{{ old("cabin_prices.$i.baby_age_min", $existing->baby_age_min ?? 0) }}"
                                           class="w-12 px-1 py-1 border border-gray-300 rounded text-xs font-mono"
                                           title="Min yaş">
                                    <span class="text-gray-400 text-xs">-</span>
                                    <input type="number" min="0" max="30" name="cabin_prices[{{ $i }}][baby_age_max]"
                                           value="{{ old("cabin_prices.$i.baby_age_max", $existing->baby_age_max ?? 1) }}"
                                           class="w-12 px-1 py-1 border border-gray-300 rounded text-xs font-mono"
                                           title="Max yaş">
                                </div>
                            </td>
                            <td class="px-2 py-2 text-center">
                                <input type="hidden" name="cabin_prices[{{ $i }}][is_active]" value="0">
                                <input type="checkbox" name="cabin_prices[{{ $i }}][is_active]" value="1"
                                       {{ old("cabin_prices.$i.is_active", $existing->is_active ?? true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-indigo-600 rounded">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <details class="text-xs text-gray-500 pt-2">
            <summary class="cursor-pointer hover:text-gray-700">▸ Hesaplama yöntemleri kısaca</summary>
            <ul class="mt-2 pl-4 space-y-1 list-disc">
                @foreach($calculationMethods as $cm)
                    <li><strong>{{ $cm->label() }}:</strong> {{ $cm->description() }}</li>
                @endforeach
            </ul>
        </details>
    </div>

    <div class="flex justify-end">
        <button type="submit"
                class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
            <i class="fas fa-save mr-1"></i> Kaydet
        </button>
    </div>
</form>
@endsection
