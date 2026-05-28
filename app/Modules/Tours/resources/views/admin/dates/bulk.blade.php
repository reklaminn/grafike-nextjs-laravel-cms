@extends('admin.layouts.app')
@section('title', 'Toplu Tarih Ekle')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.tours.dates.index', $tour) }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Toplu Departure Tarihi Ekle</h1>
        <p class="text-sm text-gray-500">{{ $tour->translations->first()?->title ?? $tour->slug }}</p>
    </div>
</div>

@include('tours::admin.partials._help', [
    'title' => 'Toplu tarih nasıl eklenir?',
    'intro' => 'Cruise / günlük turlarda her sefer ayrı bir kalkış tarihidir. Birden çok tarihi tek seferde girin:',
    'steps' => [
        '<strong>Gece sayısı</strong> girin — her tarihin bitişi otomatik (kalkış + gece) hesaplanır. Günlük turda 0 bırakın.',
        '<strong>Kalkış saati</strong>, <strong>kapasite</strong> ve <strong>durum</strong> tüm tarihler için ortaktır.',
        '<strong>+ Tarih Ekle</strong> ile istediğiniz kadar kalkış tarihi ekleyin.',
        'Kaydet — her tarih için ayrı bir departure oluşturulur. Zaten var olan tarihler atlanır.',
    ],
    'note' => 'Tek bir özel departure için (farklı kapasite/fiyat) sağ üstteki tekli ekleme formunu kullanın.',
])

@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('admin.tours.dates.bulk-store', $tour) }}"
      x-data="{ dates: {{ \Illuminate\Support\Js::from(old('dates', [''])) }} }"
      class="bg-white rounded-xl shadow-sm border p-6 space-y-6 max-w-3xl">
    @csrf

    {{-- Shared settings --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Gece Sayısı
                <span class="text-gray-400 cursor-help" title="Bitiş = kalkış + gece. Günlük tur için 0 bırakın (bitiş boş kalır).">
                    <i class="fas fa-circle-question text-xs"></i>
                </span>
            </label>
            <input type="number" name="nights" min="0" max="365"
                   value="{{ old('nights', 7) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kalkış Saati</label>
            <input type="time" name="depart_time"
                   value="{{ old('depart_time', '17:00') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Toplam Kapasite *</label>
            <input type="number" name="capacity_total" required min="0"
                   value="{{ old('capacity_total', $tour->capacity_default) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Durum *</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="open"      {{ old('status', 'open') === 'open' ? 'selected' : '' }}>Açık (Sellable)</option>
                <option value="closed"    {{ old('status') === 'closed' ? 'selected' : '' }}>Kapalı</option>
                <option value="sold_out"  {{ old('status') === 'sold_out' ? 'selected' : '' }}>Doldu</option>
                <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>İptal</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Fiyat Override (kuruş)</label>
            <input type="number" name="price_override" min="0"
                   value="{{ old('price_override') }}"
                   placeholder="Boş = tur baz fiyatı"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notlar (tümüne ortak)</label>
            <input type="text" name="notes" value="{{ old('notes') }}"
                   placeholder="Sadece admin görür"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
    </div>

    {{-- Multi-date repeater --}}
    <div class="border-t border-gray-100 pt-4">
        <div class="flex items-center justify-between mb-3">
            <label class="text-sm font-medium text-gray-700">
                Kalkış Tarihleri
                <span class="text-gray-400" x-text="`(${dates.length})`"></span>
            </label>
            <button type="button" @click="dates.push('')"
                    class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs hover:bg-indigo-100 font-medium">
                <i class="fas fa-plus mr-1"></i> Tarih Ekle
            </button>
        </div>

        <div class="space-y-2">
            <template x-for="(d, idx) in dates" :key="idx">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono text-gray-400 w-6" x-text="idx + 1"></span>
                    <input type="date" :name="`dates[${idx}]`" x-model="dates[idx]" required
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <button type="button" @click="dates.splice(idx, 1)" x-show="dates.length > 1"
                            class="text-red-500 hover:text-red-700 px-2">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </template>
        </div>

        <button type="button" @click="dates.push('')"
                class="mt-3 text-xs text-indigo-600 hover:underline">
            <i class="fas fa-plus mr-1"></i> Bir tarih daha ekle
        </button>
    </div>

    <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
        <a href="{{ route('admin.tours.dates.index', $tour) }}"
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">İptal</a>
        <button type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
            <i class="fas fa-save mr-1"></i> Tarihleri Kaydet
        </button>
    </div>
</form>
@endsection
