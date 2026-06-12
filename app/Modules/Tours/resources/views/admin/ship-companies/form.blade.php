@extends('admin.layouts.app')
@section('title', $company->exists ? 'Gemi Firması Düzenle' : 'Yeni Gemi Firması')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.ship-companies.index') }}" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h1 class="text-2xl font-bold text-gray-800">
        {{ $company->exists ? 'Gemi Firması Düzenle' : 'Yeni Gemi Firması' }}
    </h1>
</div>

@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
    <ul class="text-sm text-red-700 list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" enctype="multipart/form-data"
      action="{{ $company->exists ? route('admin.ship-companies.update', $company) : route('admin.ship-companies.store') }}"
      class="space-y-6">
    @csrf
    @if($company->exists)@method('PUT')@endif

    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-ship text-indigo-500"></i> Firma Bilgileri
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Firma Adı</label>
                <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       placeholder="MSC Cruises">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $company->slug) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                       placeholder="msc-cruises">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Firma Tipi</label>
                <input type="text" name="company_type" value="{{ old('company_type', $company->company_type) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       placeholder="cruise, ferry, …">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Operatör</label>
                <input type="text" name="operator" value="{{ old('operator', $company->operator) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kuruluş Yılı</label>
                <input type="number" name="founded_year" value="{{ old('founded_year', $company->founded_year) }}"
                       min="1800" max="{{ date('Y') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Merkez</label>
                <input type="text" name="headquarters" value="{{ old('headquarters', $company->headquarters) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       placeholder="Cenova, İtalya">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                <input type="url" name="website" value="{{ old('website', $company->website) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       placeholder="https://www.msccruises.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $company->sort_order ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div class="flex flex-col gap-2 pt-2">
                <label class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $company->is_active ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 rounded">
                    <span class="ml-2 text-sm text-gray-700">Aktif</span>
                </label>
                <label class="flex items-center">
                    <input type="hidden" name="uses_cabin_groups" value="0">
                    <input type="checkbox" name="uses_cabin_groups" value="1"
                           {{ old('uses_cabin_groups', $company->uses_cabin_groups ?? false) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 rounded">
                    <span class="ml-2 text-sm text-gray-700">Kabin grupları kullan (büyük filolar için)</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Logo --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-image text-indigo-500"></i> Logo
        </h2>
        @if($company->exists && ($logoUrl = $company->getFirstMediaUrl('logo')))
            <div class="flex items-center gap-4">
                <img src="{{ $logoUrl }}" alt="logo" class="h-16 border border-gray-200 rounded-lg p-2 bg-gray-50">
                <p class="text-xs text-gray-500">Yeni dosya yüklemeniz, mevcut logoyu değiştirir.</p>
            </div>
        @endif
        <input type="file" name="logo" accept="image/*"
               class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        <p class="text-xs text-gray-400">PNG/JPG/SVG kabul edilir, max 5MB.</p>
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
                          placeholder="Firma açıklaması (frontend gemi firması detay sayfası)"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old("translations.$i.description", $tr->description ?? '') }}</textarea>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="translations[{{ $i }}][meta_title]"
                           value="{{ old("translations.$i.meta_title", $tr->meta_title ?? '') }}"
                           placeholder="Meta title (SEO)"
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
