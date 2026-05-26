@extends('admin.layouts.app')
@section('title', $date->exists ? 'Tarih Düzenle' : 'Yeni Tarih')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.tours.dates.index', $tour) }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $date->exists ? 'Departure Tarihini Düzenle' : 'Yeni Departure' }}</h1>
        <p class="text-sm text-gray-500">{{ $tour->translations->first()?->title ?? $tour->slug }}</p>
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
      action="{{ $date->exists ? route('admin.tours.dates.update', [$tour, $date]) : route('admin.tours.dates.store', $tour) }}"
      class="bg-white rounded-xl shadow-sm border p-6 space-y-5 max-w-2xl">
    @csrf
    @if($date->exists)@method('PUT')@endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hareket Tarihi/Saati *</label>
            <input type="datetime-local" name="starts_at" required
                   value="{{ old('starts_at', $date->starts_at?->format('Y-m-d\TH:i')) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bitiş Tarihi/Saati</label>
            <input type="datetime-local" name="ends_at"
                   value="{{ old('ends_at', $date->ends_at?->format('Y-m-d\TH:i')) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <p class="text-xs text-gray-400 mt-1">Boş bırakırsanız tek-gün varsayılır.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Toplam Kapasite *</label>
            <input type="number" name="capacity_total" required min="0"
                   value="{{ old('capacity_total', $date->capacity_total ?? $tour->capacity_default) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kalan Kapasite</label>
            <input type="number" name="capacity_left" min="0"
                   value="{{ old('capacity_left', $date->capacity_left ?? $date->capacity_total ?? $tour->capacity_default) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <p class="text-xs text-gray-400 mt-1">Manuel ayar gerekirse — normalde booking akışı otomatik yönetir.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fiyat Override (kuruş)</label>
            <input type="number" name="price_override" min="0"
                   value="{{ old('price_override', $date->price_override) }}"
                   placeholder="Boş = tur baz fiyatı"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Durum *</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="open"      {{ old('status', $date->status ?? 'open') === 'open'      ? 'selected' : '' }}>Açık (Sellable)</option>
                <option value="closed"    {{ old('status', $date->status) === 'closed'              ? 'selected' : '' }}>Kapalı (Manuel)</option>
                <option value="sold_out"  {{ old('status', $date->status) === 'sold_out'            ? 'selected' : '' }}>Doldu</option>
                <option value="cancelled" {{ old('status', $date->status) === 'cancelled'           ? 'selected' : '' }}>İptal</option>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notlar</label>
        <textarea name="notes" rows="3" placeholder="Bu departure'a özel notlar (yolculara gözükmez, sadece admin)"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notes', $date->notes) }}</textarea>
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('admin.tours.dates.index', $tour) }}"
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">İptal</a>
        <button type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
            <i class="fas fa-save mr-1"></i> Kaydet
        </button>
    </div>
</form>
@endsection
