{{--
    Dil Sürümleri Paneli — page edit sidebar
    Requires: $page (with translations.language relation loaded)
--}}
@php
    $allLangs = \App\Models\Language::where('is_active', true)->orderBy('sort_order')->get();
    $translations = $page->translations ?? collect();
    $currentLangId = $page->language_id;

    // Map: language_id => translation page
    $translationMap = $translations->keyBy('language_id');

    // Languages missing a translation (excluding current)
    $missingLangs = $allLangs->filter(
        fn($l) => $l->id !== $currentLangId && !$translationMap->has($l->id)
    );
@endphp

@if($allLangs->count() > 1)
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
        <i class="fas fa-language text-indigo-500"></i> Dil Sürümleri
    </h3>

    <div class="space-y-2">
        {{-- Current language --}}
        <div class="flex items-center justify-between text-xs py-1.5 px-2 bg-indigo-50 rounded-lg border border-indigo-200">
            <span class="font-medium text-indigo-700">
                <i class="fas fa-check-circle mr-1 text-indigo-500"></i>
                {{ $page->language?->name ?? '—' }}
                <span class="text-indigo-400 font-normal">(bu sayfa)</span>
            </span>
            <span class="text-indigo-400 uppercase tracking-wider text-[10px]">{{ $page->language?->code }}</span>
        </div>

        {{-- Existing translations --}}
        @foreach($translations as $trans)
        <div class="flex items-center justify-between text-xs py-1.5 px-2 bg-green-50 rounded-lg border border-green-200">
            <span class="font-medium text-green-700">
                <i class="fas fa-check-circle mr-1 text-green-500"></i>
                {{ $trans->language?->name ?? '—' }}
                <span class="text-green-400 font-normal text-[10px] ml-1">{{ ucfirst($trans->status) }}</span>
            </span>
            <a href="{{ route('admin.pages.edit', $trans) }}"
               class="text-green-600 hover:text-green-800 transition-colors" title="Düzenle">
                <i class="fas fa-pen-to-square"></i>
            </a>
        </div>
        @endforeach

        {{-- Missing languages --}}
        @foreach($missingLangs as $lang)
        <div class="flex items-center justify-between text-xs py-1.5 px-2 bg-gray-50 rounded-lg border border-dashed border-gray-300">
            <span class="text-gray-500">
                <i class="fas fa-circle-plus mr-1 text-gray-400"></i>
                {{ $lang->name }}
                <span class="uppercase tracking-wider text-[10px] text-gray-400 ml-1">{{ $lang->code }}</span>
            </span>
            <a href="{{ route('admin.pages.create-translation', [$page, 'lang' => $lang->id]) }}"
               class="text-indigo-600 hover:text-indigo-800 transition-colors font-medium" title="Çeviri Ekle">
                + Çevir
            </a>
        </div>
        @endforeach
    </div>

    @if($missingLangs->isNotEmpty())
    <a href="{{ route('admin.translations.index', ['type' => 'pages']) }}"
       class="mt-3 block text-center text-xs text-gray-400 hover:text-indigo-600 transition-colors">
        Tüm çeviri durumunu gör →
    </a>
    @endif
</div>
@endif
