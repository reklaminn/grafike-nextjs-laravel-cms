@extends('admin.layouts.app')
@section('title', 'Rota Takvimi — ' . ($tour->translations->first()?->title ?? $tour->slug))

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.tours.edit', $tour) }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Rota Takvimi</h1>
        <p class="text-sm text-gray-500">{{ $tour->translations->first()?->title ?? $tour->slug }}</p>
    </div>
</div>

@include('tours::admin.partials._help', [
    'title' => 'Rota nasıl oluşturulur?',
    'intro' => 'Gün gün gezi programı. Her durak (gece planı) bir gün numarası + liman + saat taşır. Aynı güne birden çok durak eklenebilir (sabah/öğleden sonra farklı limanlar).',
    'steps' => [
        '<strong>Tur Programı</strong> özetini girin (örn. "Çeşme - Patmos - 1 gece Mykonos - Çeşme / 2 Gece 3 Gün").',
        '<strong>Çıkış Şehri</strong> (hareket limanı) seçin.',
        '<strong>+ Durak Ekle</strong> ile her gün/durağı ekleyin: gün no, liman, varış/kalkış saati, konaklama, program.',
        'Aynı günde birden çok liman varsa aynı gün numarasını tekrar kullanın (sabah Mykonos, öğleden sonra Delos = ikisi de Gün 2).',
    ],
    'note' => 'Rota dil bazlıdır — üstteki dil sekmesinden her dil için ayrı rota girebilirsiniz.',
])

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

{{-- Dil sekmesi --}}
<div class="bg-white rounded-xl shadow-sm border mb-5 overflow-x-auto">
    <nav class="flex flex-wrap min-w-max">
        @foreach($languages as $lang)
            <a href="{{ route('admin.tours.itinerary.edit', ['tour' => $tour, 'lang' => $lang->id]) }}"
               class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors
                   {{ $languageId === $lang->id ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-500 hover:bg-gray-50' }}">
                <i class="fas fa-language mr-1"></i> {{ $lang->name }}
            </a>
        @endforeach
    </nav>
</div>

<form method="POST" action="{{ route('admin.tours.itinerary.update', $tour) }}"
      x-data="{
          stops: {{ \Illuminate\Support\Js::from($stopRows) }},
          ports: {{ \Illuminate\Support\Js::from($ports) }},
          addStop() {
              const lastDay = this.stops.length ? Number(this.stops[this.stops.length-1].day_number) : 0;
              this.stops.push({
                  day_number: lastDay + 1, point_type: 'visit', title: '', port_id: '',
                  arrival_time: '', departure_time: '', accommodation: '', description: ''
              });
          },
          onPortChange(stop) {
              const opt = this.ports.find(p => String(p.id) === String(stop.port_id));
              if (opt && !stop.title) stop.title = opt.label;
          }
      }"
      class="space-y-6">
    @csrf
    @method('PUT')
    <input type="hidden" name="language_id" value="{{ $languageId }}">

    {{-- Üst: program özeti + çıkış şehri --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-route text-indigo-500"></i> Genel
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tur Çıkış Şehri (Liman)</label>
                <select name="origin_port_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">— Liman Seçiniz —</option>
                    @foreach($ports as $p)
                        <option value="{{ $p['id'] }}" {{ (int) old('origin_port_id', $itinerary->origin_port_id ?? 0) === $p['id'] ? 'selected' : '' }}>
                            {{ $p['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rota Başlığı (opsiyonel)</label>
                <input type="text" name="title" value="{{ old('title', $itinerary->title ?? '') }}"
                       placeholder="Akdeniz 7 Gece Rotası"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tur Programı (özet)</label>
            <textarea name="summary" rows="3"
                      placeholder="Çeşme - Patmos - 1 gece Mykonos - Çeşme / 2 Gece 3 Gün"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('summary', $itinerary->summary ?? '') }}</textarea>
        </div>
    </div>

    {{-- Gün/Durak listesi --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-map-signs text-indigo-500"></i> Gün / Durak Planı
                <span class="text-xs text-gray-400 font-normal" x-text="`(${stops.length} durak)`"></span>
            </h2>
            <button type="button" @click="addStop()"
                    class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs hover:bg-indigo-100 font-medium">
                <i class="fas fa-plus mr-1"></i> Durak Ekle
            </button>
        </div>

        <template x-if="stops.length === 0">
            <p class="text-sm text-gray-500 p-4 bg-gray-50 rounded-lg text-center">
                Henüz durak yok. "Durak Ekle" ile başlayın.
            </p>
        </template>

        <div class="space-y-3">
            <template x-for="(stop, idx) in stops" :key="idx">
                <div class="border border-gray-200 rounded-lg p-4 space-y-3 bg-gray-50/50">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-1">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Gün</label>
                            <input type="number" min="1" max="365" :name="`stops[${idx}][day_number]`" x-model="stop.day_number"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Nokta Tipi</label>
                            <select :name="`stops[${idx}][point_type]`" x-model="stop.point_type"
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                <option value="visit">Ziyaret</option>
                                <option value="meeting">Buluşma/Hareket</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Liman</label>
                            <select :name="`stops[${idx}][port_id]`" x-model="stop.port_id" @change="onPortChange(stop)"
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                                <option value="">— Liman Seçiniz —</option>
                                <template x-for="p in ports" :key="p.id">
                                    <option :value="p.id" x-text="p.label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Başlık (şehir/bölge)</label>
                            <input type="text" :name="`stops[${idx}][title]`" x-model="stop.title"
                                   placeholder="Çeşme"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Konaklama</label>
                            <input type="text" :name="`stops[${idx}][accommodation]`" x-model="stop.accommodation"
                                   placeholder="Gemi / Otel"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Varış Saati</label>
                            <input type="time" :name="`stops[${idx}][arrival_time]`" x-model="stop.arrival_time"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Kalkış Saati</label>
                            <input type="time" :name="`stops[${idx}][departure_time]`" x-model="stop.departure_time"
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                        </div>
                        <div class="md:col-span-7">
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Program (o durakta yapılacaklar)</label>
                            <input type="text" :name="`stops[${idx}][description]`" x-model="stop.description"
                                   placeholder="Serbest zaman, ada turu, akşam yemeği..."
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm">
                        </div>
                        <div class="md:col-span-1 flex justify-end">
                            <button type="button" @click="stops.splice(idx, 1)"
                                    class="text-red-500 hover:text-red-700 px-2 py-1.5" title="Durağı sil">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <button type="button" @click="addStop()" class="text-xs text-indigo-600 hover:underline">
            <i class="fas fa-plus mr-1"></i> Bir durak daha ekle
        </button>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('admin.tours.edit', $tour) }}"
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Geri</a>
        <button type="submit"
                class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
            <i class="fas fa-save mr-1"></i> Rotayı Kaydet
        </button>
    </div>
</form>
@endsection
