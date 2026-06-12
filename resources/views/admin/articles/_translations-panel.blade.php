{{--
    Dil Sürümleri Paneli — article edit sidebar
    Requires: $article (with translations.language relation loaded)
--}}
@php
    $allLangs = \App\Models\Language::where('is_active', true)->orderBy('sort_order')->get();
    $translations = $article->translations ?? collect();
    $currentLangId = $article->language_id;
    $translationMap = $translations->keyBy('language_id');
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
        <div class="flex items-center justify-between text-xs py-1.5 px-2 bg-indigo-50 rounded-lg border border-indigo-200">
            <span class="font-medium text-indigo-700">
                <i class="fas fa-check-circle mr-1 text-indigo-500"></i>
                {{ $article->language?->name ?? '—' }}
                <span class="text-indigo-400 font-normal">(bu yazı)</span>
            </span>
            <span class="uppercase tracking-wider text-[10px] text-indigo-400">{{ $article->language?->code }}</span>
        </div>

        @foreach($translations as $trans)
        <div class="flex items-center justify-between text-xs py-1.5 px-2 bg-green-50 rounded-lg border border-green-200">
            <span class="font-medium text-green-700">
                <i class="fas fa-check-circle mr-1 text-green-500"></i>
                {{ $trans->language?->name ?? '—' }}
                <span class="text-green-400 font-normal text-[10px] ml-1">{{ ucfirst($trans->status) }}</span>
            </span>
            <a href="{{ route('admin.articles.edit', $trans->id, false) }}"
               class="text-green-600 hover:text-green-800 transition-colors" title="Düzenle">
                <i class="fas fa-pen-to-square"></i>
            </a>
        </div>
        @endforeach

        @foreach($missingLangs as $lang)
        <div class="flex items-center justify-between text-xs py-1.5 px-2 bg-gray-50 rounded-lg border border-dashed border-gray-300">
            <span class="text-gray-500">
                <i class="fas fa-circle-plus mr-1 text-gray-400"></i>
                {{ $lang->name }}
                <span class="uppercase tracking-wider text-[10px] text-gray-400 ml-1">{{ $lang->code }}</span>
            </span>
            <a href="{{ route('admin.articles.create-translation', [$article->id, 'lang' => $lang->id], false) }}"
               class="text-indigo-600 hover:text-indigo-800 transition-colors font-medium">
                + Çevir
            </a>
        </div>
        @endforeach
    </div>

    @if($missingLangs->isNotEmpty())
    <a href="{{ route('admin.translations.index', ['type' => 'articles']) }}"
       class="mt-3 block text-center text-xs text-gray-400 hover:text-indigo-600 transition-colors">
        Tüm çeviri durumunu gör →
    </a>
    @endif
</div>
@endif
