@extends('admin.layouts.app')
@section('title', $group->exists ? 'Kabin Grubu Düzenle' : 'Yeni Kabin Grubu')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.cabin-groups.index') }}" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h1 class="text-2xl font-bold text-gray-800">
        {{ $group->exists ? 'Kabin Grubu Düzenle' : 'Yeni Kabin Grubu' }}
    </h1>
</div>

@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST"
      action="{{ $group->exists ? route('admin.cabin-groups.update', $group) : route('admin.cabin-groups.store') }}"
      class="space-y-6">
    @csrf
    @if($group->exists)@method('PUT')@endif

    {{-- Identity --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-layer-group text-indigo-500"></i> Grup Bilgileri
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gemi Firması</label>
                <select name="ship_company_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">— Firma seçin —</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}"
                                {{ (int) old('ship_company_id', $group->ship_company_id) === $company->id ? 'selected' : '' }}>
                            {{ $company->name }}{{ $company->uses_cabin_groups ? '' : ' (grup kullanmıyor)' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">
                    Firma değişirse kayıttan sonra eligible cabin listesi güncellenir.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $group->slug) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                       placeholder="paket-tur-kabinleri">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $group->sort_order ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>

        <div class="flex items-center">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $group->is_active ?? true) ? 'checked' : '' }}
                   class="h-4 w-4 text-indigo-600 rounded">
            <label for="is_active" class="ml-2 text-sm text-gray-700">Aktif</label>
        </div>
    </div>

    {{-- Cabin pivot --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-bed text-indigo-500"></i> Kabinler
        </h2>

        @if(!$group->ship_company_id)
            <p class="text-sm text-amber-600">
                Önce firmayı seçip kaydedin — eligible cabin listesi firma değişiminden sonra yüklenir.
            </p>
        @elseif($eligibleCabins->isEmpty())
            <p class="text-sm text-gray-500">Bu firmanın gemilerinde henüz kabin yok.</p>
        @else
            <p class="text-xs text-gray-500 mb-3">
                Bu firmanın gemilerindeki kabinler.  Seçimler grup içeriğini oluşturur — sıralama checkbox sırasına bağlıdır.
            </p>
            <div class="max-h-96 overflow-y-auto p-2 bg-gray-50 rounded-lg space-y-1">
                @foreach($eligibleCabins as $cabin)
                    @php
                        $tr    = $cabin->translations->first();
                        $catTr = $cabin->category?->translations?->first();
                    @endphp
                    <label class="flex items-center gap-3 px-3 py-2 bg-white border border-gray-200 rounded hover:bg-indigo-50 cursor-pointer">
                        <input type="checkbox" name="cabin_ids[]" value="{{ $cabin->id }}"
                               {{ in_array($cabin->id, $selectedCabins, true) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 rounded">
                        <div class="flex-1 grid grid-cols-4 gap-3 text-sm">
                            <span class="font-medium text-gray-800 truncate">{{ $tr->name ?? $cabin->code ?? '—' }}</span>
                            <span class="text-gray-500 truncate">{{ $cabin->ship?->name }}</span>
                            <span class="text-gray-500 truncate">{{ $catTr->name ?? '—' }}</span>
                            <span class="text-gray-400 text-xs font-mono">{{ $cabin->deck_name }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
        @endif
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
                       placeholder="Grup adı (Paket Tur Kabinleri)"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <textarea name="translations[{{ $i }}][description]" rows="2"
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
