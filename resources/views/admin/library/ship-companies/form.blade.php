@extends('admin.layouts.app')

@section('title', $company->exists ? 'Firma Düzenle' : 'Yeni Firma')
@section('page-title', 'Kütüphane — Gemi Firması')

@section('content')
<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('admin.library.ship-companies.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
    <h1 class="text-xl font-bold text-gray-800">{{ $company->exists ? $company->name : 'Yeni Gemi Firması' }}</h1>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST"
      action="{{ $company->exists ? route('admin.library.ship-companies.update', $company) : route('admin.library.ship-companies.store') }}"
      class="space-y-6 max-w-4xl">
    @csrf
    @if($company->exists) @method('PUT') @endif

    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700"><i class="fas fa-flag text-indigo-500 mr-1"></i> Firma Bilgileri</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ad (marka) *</label>
                <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                <input type="text" name="slug" value="{{ old('slug', $company->slug) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tip</label>
                <input type="text" name="company_type" value="{{ old('company_type', $company->company_type) }}"
                       placeholder="Premium / Luxury / Mainstream / Expedition"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Operatör</label>
                <input type="text" name="operator" value="{{ old('operator', $company->operator) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kuruluş Yılı</label>
                <input type="number" name="founded_year" value="{{ old('founded_year', $company->founded_year) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Merkez</label>
                <input type="text" name="headquarters" value="{{ old('headquarters', $company->headquarters) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Web Sitesi</label>
                <input type="text" name="website" value="{{ old('website', $company->website) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
                <input type="text" name="logo_url" value="{{ old('logo_url', $company->logo_url) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sıra</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $company->sort_order ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>
        <div class="flex items-center gap-6 pt-2">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="hidden" name="uses_cabin_groups" value="0">
                <input type="checkbox" name="uses_cabin_groups" value="1" {{ old('uses_cabin_groups', $company->uses_cabin_groups) ? 'checked' : '' }}>
                Kabin grupları kullanır
            </label>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $company->is_active ?? true) ? 'checked' : '' }}>
                Aktif
            </label>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700"><i class="fas fa-language text-indigo-500 mr-1"></i> Çeviriler</h2>
        @foreach($languages as $i => $lang)
            @php $t = $translations[$lang->id] ?? null; @endphp
            <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                <div class="text-sm font-medium text-gray-600">{{ $lang->name }}</div>
                <input type="hidden" name="translations[{{ $i }}][language_id]" value="{{ $lang->id }}">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Açıklama</label>
                    <textarea name="translations[{{ $i }}][description]" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.description", $t->description ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="translations[{{ $i }}][meta_title]" value="{{ old("translations.$i.meta_title", $t->meta_title ?? '') }}"
                           placeholder="Meta başlık" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <input type="text" name="translations[{{ $i }}][meta_description]" value="{{ old("translations.$i.meta_description", $t->meta_description ?? '') }}"
                           placeholder="Meta açıklama" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex justify-end gap-2">
        <a href="{{ route('admin.library.ship-companies.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">İptal</a>
        <button class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium"><i class="fas fa-save mr-1"></i> Kaydet</button>
    </div>
</form>
@endsection
