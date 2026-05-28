@extends('admin.layouts.app')

@section('title', 'Kolon Eşleme')
@section('page-title', 'Kolon Eşleme — ' . $spec['label'])

@php
    // Alan etiketleri (TR)
    $labels = [
        'slug' => 'Slug', 'name' => 'Ad', 'company_type' => 'Firma Tipi', 'operator' => 'Operatör',
        'founded_year' => 'Kuruluş Yılı', 'headquarters' => 'Merkez', 'website' => 'Web Sitesi',
        'logo_url' => 'Logo URL', 'uses_cabin_groups' => 'Kabin Grupları Kullanır', 'is_active' => 'Aktif',
        'is_featured' => 'Öne Çıkan', 'sort_order' => 'Sıra', 'country_code' => 'Ülke Kodu',
        'latitude' => 'Enlem', 'longitude' => 'Boylam', 'population' => 'Nüfus', 'video_url' => 'Video URL',
        'timezone' => 'Zaman Dilimi', 'cover_url' => 'Kapak Görsel', 'compatible_tour_types' => 'Uygun Tur Tipleri',
        'star_rating' => 'Yıldız', 'local_agent' => 'TR Temsilci', 'flag_country_code' => 'Bayrak Ülke',
        'imo_number' => 'IMO No', 'year_built' => 'Yapım Yılı', 'passenger_capacity' => 'Yolcu Kapasitesi',
        'crew_count' => 'Mürettebat', 'deck_count' => 'Güverte Sayısı', 'tonnage' => 'Tonaj (GRT)',
        'length_m' => 'Uzunluk (m)', 'beam_m' => 'Genişlik (m)', 'cruise_speed_knots' => 'Hız (knot)',
        'facilities' => 'Olanaklar', 'cabin_category_slug' => 'Kabin Kategori Slug', 'brand_subcategory' => 'Marka Alt Tip',
        'code' => 'Kabin Kodu', 'deck_name' => 'Güverte Adı', 'max_capacity' => 'Maks Kapasite',
        'base_price_per_person' => 'Kişi Başı Fiyat', 'description' => 'Açıklama', 'short_description' => 'Kısa Açıklama',
        'long_description' => 'Uzun Açıklama', 'meta_title' => 'Meta Başlık', 'meta_description' => 'Meta Açıklama',
    ];

    // Otomatik tahmin: alan adını başlıkla normalize ederek eşle
    $norm = static fn ($s) => preg_replace('/[^a-z0-9]/', '', mb_strtolower((string) $s));
    $aliases = [
        'name' => ['ad', 'isim', 'baslik', 'title'], 'slug' => ['slug', 'seo'],
        'description' => ['aciklama', 'description', 'detay'], 'country_code' => ['ulke', 'country', 'countrycode'],
        'latitude' => ['lat', 'enlem'], 'longitude' => ['lng', 'lon', 'boylam'],
        'founded_year' => ['kurulus', 'yil'], 'website' => ['web', 'site', 'url'],
    ];
    $guess = function (string $field) use ($headers, $norm, $aliases) {
        $target = $norm($field);
        foreach ($headers as $h) {
            if ($norm($h) === $target) return $h;
        }
        foreach (($aliases[$field] ?? []) as $alias) {
            foreach ($headers as $h) {
                if ($norm($h) === $norm($alias)) return $h;
            }
        }
        return '';
    };
    $guessId = function (array $cands) use ($headers, $norm) {
        foreach ($cands as $c) {
            foreach ($headers as $h) {
                if ($norm($h) === $norm($c)) return $h;
            }
        }
        return '';
    };
@endphp

@section('content')
<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('admin.library.import.form') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-bold text-gray-800">Kolon Eşleme — {{ $spec['label'] }}</h1>
        <p class="text-sm text-gray-500">{{ number_format($dataCount) }} veri satırı bulundu. Hangi kolon hangi alana gitsin?</p>
    </div>
</div>

{{-- Önizleme --}}
<div class="bg-white rounded-xl shadow-sm border mb-5 overflow-x-auto">
    <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase border-b">Önizleme (ilk {{ count($samples) }} satır)</div>
    <table class="w-full text-xs">
        <thead class="bg-gray-50 border-b">
            <tr>@foreach($headers as $h)<th class="px-3 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">{{ $h }}</th>@endforeach</tr>
        </thead>
        <tbody class="divide-y">
            @foreach($samples as $row)
                <tr>@foreach($headers as $i => $h)<td class="px-3 py-2 text-gray-600 whitespace-nowrap max-w-[200px] truncate">{{ \Illuminate\Support\Str::limit((string)($row[$i] ?? ''), 40) }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
</div>

<form method="POST" action="{{ route('admin.library.import.run') }}" class="bg-white rounded-xl shadow-sm border p-6 space-y-6 max-w-3xl">
    @csrf
    <input type="hidden" name="entity" value="{{ $entity }}">
    <input type="hidden" name="token" value="{{ $token }}">

    {{-- Kimlik / ilişki --}}
    <div class="space-y-4">
        <h2 class="font-semibold text-gray-700 text-sm"><i class="fas fa-fingerprint text-indigo-500 mr-1"></i> Kimlik & İlişki</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-700 mb-1">Eski ID (legacy_id) <span class="text-gray-400 text-xs">— tavsiye edilir</span></label>
                @include('admin.library.import._select', ['name' => 'map[legacy_id]', 'headers' => $headers, 'selected' => $guessId(['id','legacy_id','eski_id'])])
                <p class="text-[11px] text-gray-400 mt-1">Tekrar import'ta aynı kaydı eşler (duplicate önler).</p>
            </div>
            @if($spec['parent'])
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Üst {{ $spec['parent']['label'] }} (legacy_id) <span class="text-red-500">*</span></label>
                    @include('admin.library.import._select', ['name' => 'map[parent_legacy_id]', 'headers' => $headers, 'selected' => $guessId(['company_id','ship_company_id','firma_id','ship_id','gemi_id','parent_id'])])
                    <p class="text-[11px] text-gray-400 mt-1">Önce {{ $spec['parent']['label'] }} import edilmiş olmalı.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Dil --}}
    <div>
        <label class="block text-sm text-gray-700 mb-1"><i class="fas fa-language text-indigo-500 mr-1"></i> Çeviri alanları hangi dile yazılsın?</label>
        <select name="language_id" class="w-full md:w-1/2 px-3 py-2 border border-gray-300 rounded-lg text-sm">
            @foreach($languages as $lang)
                <option value="{{ $lang->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $lang->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Scalar alanlar --}}
    <div class="space-y-4">
        <h2 class="font-semibold text-gray-700 text-sm"><i class="fas fa-table-columns text-indigo-500 mr-1"></i> Alanlar</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($spec['scalars'] as $field => $type)
                <div>
                    <label class="block text-sm text-gray-700 mb-1">
                        {{ $labels[$field] ?? $field }}
                        @if(($spec['slugField'] ?? null) === $field || $field === 'name')<span class="text-red-500">*</span>@endif
                        <span class="text-[11px] text-gray-400">({{ $type }})</span>
                    </label>
                    @include('admin.library.import._select', ['name' => "map[$field]", 'headers' => $headers, 'selected' => $guess($field)])
                </div>
            @endforeach
        </div>
    </div>

    {{-- Çeviri alanları --}}
    <div class="space-y-4">
        <h2 class="font-semibold text-gray-700 text-sm"><i class="fas fa-align-left text-indigo-500 mr-1"></i> Çeviri Alanları (seçili dile)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($spec['translations'] as $field => $type)
                <div>
                    <label class="block text-sm text-gray-700 mb-1">{{ $labels[$field] ?? $field }}</label>
                    @include('admin.library.import._select', ['name' => "map[$field]", 'headers' => $headers, 'selected' => $guess($field)])
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-2 border-t">
        <a href="{{ route('admin.library.import.form') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">İptal</a>
        <button class="px-5 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 font-medium">
            <i class="fas fa-database mr-1"></i> {{ number_format($dataCount) }} Satırı Import Et
        </button>
    </div>
</form>
@endsection
