@extends('admin.layouts.app')
@section('title', ($group->exists ? 'Fiyat Grubu Düzenle' : 'Yeni Fiyat Grubu') . ' — ' . ($tour->translations->first()?->title ?? $tour->slug))

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
            @if($tour->ship)· Gemi: <strong>{{ $tour->ship->name }}</strong>@endif
        </p>
    </div>
</div>

@include('tours::admin.partials._help', [
    'title' => 'Fiyat grubu nasıl oluşturulur?',
    'intro' => 'Bir fiyat grubu = adlandırılmış fiyat seti ("Yaz 2026", "Erken Rezervasyon"). Aynı tarihe birden çok grup atanabilir; bir grup birden çok tarihte geçerli olabilir.',
    'steps' => [
        '<strong>Opsiyon Adı</strong> + (varsa) açıklama girin.',
        '<strong>Tarihler</strong>: bu grubun geçerli olacağı departure tarihlerini işaretleyin.',
        '<strong>Oda Fiyatları</strong>: her oda/kabin için bir satır ekleyin — oda adı, hesaplama yöntemi ve kişi-bazlı (Single/Double/Triple/Quad/Çocuk/Bebek) fiyatları girin.',
        'Fiyat boş bırakılırsa "Sorunuz" olarak gösterilir (manuel teklif).',
    ],
    'note' => 'Hesaplama yöntemi: Standart (Doublex2) = double × kişi; Kişi Toplama = her pozisyon kendi fiyatı; Tek Kabin = sabit kabin fiyatı.',
])

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
      x-data="{
          rooms: {{ \Illuminate\Support\Js::from($roomRows) }},
          cabinOptions: {{ \Illuminate\Support\Js::from($cabinOptions) }},
          addRoom() {
              this.rooms.push({
                  id: null, room_label: '', deck_label: '', cabin_id: '',
                  price_definition: '', calculation_method: 'standart_doublex2',
                  currency: '{{ $tour->currency }}',
                  price_single: '', price_double: '', price_triple: '', price_quad: '',
                  price_child: '', price_baby: '',
                  child_age_min: 2, child_age_max: 11, baby_age_min: 0, baby_age_max: 1,
                  is_active: true,
              });
          },
          onCabinChange(room) {
              // Kabin seçilince oda adı + güverte boşsa otomatik doldur
              const opt = this.cabinOptions.find(o => String(o.id) === String(room.cabin_id));
              if (opt) {
                  if (!room.room_label) room.room_label = opt.label;
                  if (!room.deck_label && opt.deck) room.deck_label = opt.deck;
              }
          }
      }"
      class="space-y-6">
    @csrf
    @if($group->exists)@method('PUT')@endif

    {{-- ── Grup Bilgileri ── --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-tag text-indigo-500"></i> Grup Bilgileri
        </h2>

        {{-- Opsiyon adı + açıklama (per language) --}}
        @foreach($languages as $i => $lang)
            @php $tr = $translations[$lang->id] ?? null; @endphp
            <div class="border border-gray-200 rounded-lg p-3 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] uppercase font-semibold text-gray-500">{{ $lang->name }} — Opsiyon Adı</span>
                    <span class="text-[10px] font-mono text-gray-400">{{ $lang->code }}</span>
                </div>
                <input type="hidden" name="translations[{{ $i }}][language_id]" value="{{ $lang->id }}">
                <input type="text" name="translations[{{ $i }}][name]" required
                       value="{{ old("translations.$i.name", $tr->name ?? '') }}"
                       placeholder="Opsiyon adı (Yaz 2026 Fiyatları)"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <input type="text" name="translations[{{ $i }}][description]"
                       value="{{ old("translations.$i.description", $tr->description ?? '') }}"
                       placeholder="Açıklama (opsiyonel)"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        @endforeach

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-2">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sıra</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $group->sort_order ?? 99999) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Min. Kişi</label>
                <input type="number" name="min_persons" min="1" value="{{ old('min_persons', $group->min_persons) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Kontenjan
                    <span class="text-gray-400 cursor-help" title="Bu gruba ayrılan kontenjan. Boş = tarih kontenjanını kullan.">
                        <i class="fas fa-circle-question"></i>
                    </span>
                </label>
                <input type="number" name="capacity_quota" min="0" value="{{ old('capacity_quota', $group->capacity_quota) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="flex flex-col justify-end gap-1 pb-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="adult_priority" value="0">
                    <input type="checkbox" name="adult_priority" value="1"
                           {{ old('adult_priority', $group->adult_priority ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 rounded">
                    <span class="text-xs text-gray-700">Yetişkin önceliği</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $group->is_active ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 rounded">
                    <span class="text-xs text-gray-700">Aktif</span>
                </label>
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Kampanya Metni</label>
                <input type="text" name="campaign_text" value="{{ old('campaign_text', $group->campaign_text) }}"
                       placeholder="Erken rezervasyon avantajı — %20 indirim 31 Mart'a kadar"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>
    </div>

    {{-- ── Tarih Seçiniz ── --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-calendar-check text-indigo-500"></i> Tarih Seçiniz
            <span class="text-xs text-gray-400 font-normal">— bu grup hangi tarihlerde geçerli?</span>
        </h2>

        @if($tour->dates->isEmpty())
            <p class="text-sm text-amber-600">
                Henüz departure tarihi yok — önce
                <a href="{{ route('admin.tours.dates.bulk-create', $tour) }}" class="underline">tarih ekleyin</a>.
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
                            @if($date->ends_at)<div class="text-gray-400">→ {{ $date->ends_at->format('d.m.Y') }}</div>@endif
                        </div>
                    </label>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Oda Fiyatları (repeatable) ── --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-bed text-indigo-500"></i> Oda / Kabin Fiyatları
                <span class="text-xs text-gray-400 font-normal" x-text="`(${rooms.length} oda)`"></span>
            </h2>
            <button type="button" @click="addRoom()"
                    class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs hover:bg-indigo-100 font-medium">
                <i class="fas fa-plus mr-1"></i> Oda Ekle
            </button>
        </div>

        <p class="text-xs text-gray-500">
            Fiyatlar <strong>kuruş</strong> cinsinden (100 = 1 TL). Boş = "Sorunuz".
            Çocuk/bebek yaş aralıkları sadece o fiyat dolu satırlarda anlamlıdır.
        </p>

        <div class="space-y-4">
            <template x-for="(room, idx) in rooms" :key="idx">
                <div class="border border-gray-200 rounded-lg p-4 space-y-3 bg-gray-50/50">
                    <input type="hidden" :name="`cabin_prices[${idx}][id]`" :value="room.id ?? ''">

                    {{-- Üst satır: oda adı + kabin + güverte + sil --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-4">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Oda Adı / Türü *</label>
                            <input type="text" :name="`cabin_prices[${idx}][room_label]`" x-model="room.room_label"
                                   placeholder="Standart İç Kabin"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Kabin (master, opsiyonel)</label>
                            <select :name="`cabin_prices[${idx}][cabin_id]`" x-model="room.cabin_id"
                                    @change="onCabinChange(room)"
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                <option value="">— Kabin Seçiniz —</option>
                                <template x-for="opt in cabinOptions" :key="opt.id">
                                    <option :value="opt.id" x-text="opt.label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Güverte / Kat</label>
                            <input type="text" :name="`cabin_prices[${idx}][deck_label]`" x-model="room.deck_label"
                                   placeholder="Deck 7"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                        </div>
                        <div class="md:col-span-1 flex justify-end">
                            <button type="button" @click="rooms.splice(idx, 1)" x-show="rooms.length > 1"
                                    class="text-red-500 hover:text-red-700 px-2 py-1.5" title="Odayı sil">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Orta satır: fiyat tanımı + hesaplama + yaş aralıkları --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-4">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Fiyat Tanımı</label>
                            <input type="text" :name="`cabin_prices[${idx}][price_definition]`" x-model="room.price_definition"
                                   placeholder="1 Tam 1 Yarım gibi"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Hesaplama Yöntemi</label>
                            <select :name="`cabin_prices[${idx}][calculation_method]`" x-model="room.calculation_method"
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                @foreach($calculationMethods as $cm)
                                    <option value="{{ $cm->value }}" title="{{ $cm->description() }}">{{ $cm->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Çocuk Yaş</label>
                            <div class="flex items-center gap-1">
                                <input type="number" min="0" max="30" :name="`cabin_prices[${idx}][child_age_min]`" x-model="room.child_age_min"
                                       class="w-full px-1 py-1.5 border border-gray-300 rounded text-sm" title="Min">
                                <span class="text-gray-400 text-xs">-</span>
                                <input type="number" min="0" max="30" :name="`cabin_prices[${idx}][child_age_max]`" x-model="room.child_age_max"
                                       class="w-full px-1 py-1.5 border border-gray-300 rounded text-sm" title="Max">
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Bebek Yaş</label>
                            <div class="flex items-center gap-1">
                                <input type="number" min="0" max="30" :name="`cabin_prices[${idx}][baby_age_min]`" x-model="room.baby_age_min"
                                       class="w-full px-1 py-1.5 border border-gray-300 rounded text-sm" title="Min">
                                <span class="text-gray-400 text-xs">-</span>
                                <input type="number" min="0" max="30" :name="`cabin_prices[${idx}][baby_age_max]`" x-model="room.baby_age_max"
                                       class="w-full px-1 py-1.5 border border-gray-300 rounded text-sm" title="Max">
                            </div>
                        </div>
                    </div>

                    {{-- Alt satır: person-tier fiyatları --}}
                    <div class="grid grid-cols-2 md:grid-cols-7 gap-2 pt-2 border-t border-gray-200">
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-1">👤 Tek</label>
                            <input type="number" min="0" :name="`cabin_prices[${idx}][price_single]`" x-model="room.price_single"
                                   placeholder="—" class="w-full px-2 py-1 border border-gray-300 rounded text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-1">👥 Çift</label>
                            <input type="number" min="0" :name="`cabin_prices[${idx}][price_double]`" x-model="room.price_double"
                                   placeholder="—" class="w-full px-2 py-1 border border-gray-300 rounded text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-1">3. Kişi</label>
                            <input type="number" min="0" :name="`cabin_prices[${idx}][price_triple]`" x-model="room.price_triple"
                                   placeholder="—" class="w-full px-2 py-1 border border-gray-300 rounded text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-1">4. Kişi</label>
                            <input type="number" min="0" :name="`cabin_prices[${idx}][price_quad]`" x-model="room.price_quad"
                                   placeholder="—" class="w-full px-2 py-1 border border-gray-300 rounded text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-1">🧒 Çocuk</label>
                            <input type="number" min="0" :name="`cabin_prices[${idx}][price_child]`" x-model="room.price_child"
                                   placeholder="—" class="w-full px-2 py-1 border border-gray-300 rounded text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-1">👶 Bebek</label>
                            <input type="number" min="0" :name="`cabin_prices[${idx}][price_baby]`" x-model="room.price_baby"
                                   placeholder="—" class="w-full px-2 py-1 border border-gray-300 rounded text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-1">Para Birimi</label>
                            <select :name="`cabin_prices[${idx}][currency]`" x-model="room.currency"
                                    class="w-full px-1 py-1 border border-gray-300 rounded text-xs font-mono">
                                <option value="TRY">TRY</option>
                                <option value="EUR">EUR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" :name="`cabin_prices[${idx}][is_active]`" value="0">
                        <input type="checkbox" :name="`cabin_prices[${idx}][is_active]`" value="1" x-model="room.is_active"
                               class="h-3.5 w-3.5 text-indigo-600 rounded">
                        <span class="text-xs text-gray-600">Aktif</span>
                    </label>
                </div>
            </template>
        </div>

        <button type="button" @click="addRoom()"
                class="text-xs text-indigo-600 hover:underline">
            <i class="fas fa-plus mr-1"></i> Bir oda daha ekle
        </button>
    </div>

    {{-- ── Çift kaydet butonu ── --}}
    <div class="flex justify-end gap-2">
        <a href="{{ route('admin.tours.price-groups.index', $tour) }}"
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">İptal</a>
        <button type="submit" name="save_action" value="new"
                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 font-medium">
            <i class="fas fa-plus mr-1"></i> Kaydet ve Yeni Grup
        </button>
        <button type="submit" name="save_action" value="continue"
                class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
            <i class="fas fa-save mr-1"></i> Kaydet ve Devam Et
        </button>
    </div>
</form>
@endsection
