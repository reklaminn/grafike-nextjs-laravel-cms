@extends('admin.layouts.app')
@section('title', 'Çeviri Yönetimi')
@section('page-title', 'Çeviri Yönetimi')

@section('content')
<div x-data="{
    selectedIds: [],
    toggleAll(checked, ids) {
        this.selectedIds = checked ? [...ids] : [];
    },
    toggle(id) {
        const i = this.selectedIds.indexOf(id);
        if (i >= 0) this.selectedIds.splice(i, 1);
        else this.selectedIds.push(id);
    },
}">

    {{-- Top bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-gray-500 mt-1">Eksik çevirileri bulun ve toplu çeviri oluşturun.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.translations.index', ['type' => 'pages', 'missing_lang' => $langFilter]) }}"
               class="px-3 py-2 text-sm rounded-lg {{ $type === 'pages' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-file mr-1"></i> Sayfalar
            </a>
            <a href="{{ route('admin.translations.index', ['type' => 'articles', 'missing_lang' => $langFilter]) }}"
               class="px-3 py-2 text-sm rounded-lg {{ $type === 'articles' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-newspaper mr-1"></i> Yazılar
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-3 text-green-800 text-sm flex items-center gap-2">
            <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6 flex flex-wrap items-center gap-3">
        <span class="text-sm font-medium text-gray-700">Filtrele:</span>
        <a href="{{ route('admin.translations.index', ['type' => $type]) }}"
           class="text-xs px-3 py-1.5 rounded-lg {{ !$langFilter ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            Tümü
        </a>
        @foreach($languages as $lang)
        <a href="{{ route('admin.translations.index', ['type' => $type, 'missing_lang' => $lang->code]) }}"
           class="text-xs px-3 py-1.5 rounded-lg {{ $langFilter === $lang->code ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ $lang->name }} eksik
        </a>
        @endforeach
    </div>

    {{-- Bulk action bar (visible when items selected) --}}
    <div x-show="selectedIds.length > 0" x-transition
         class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-4 flex items-center justify-between gap-4">
        <span class="text-sm text-indigo-700 font-medium">
            <span x-text="selectedIds.length"></span> öğe seçildi
        </span>
        <form method="POST" action="{{ route('admin.translations.bulk', [], false) }}" id="bulk-form">
            @csrf
            <input type="hidden" name="type" value="{{ $type === 'articles' ? 'article' : 'page' }}">
            <template x-for="id in selectedIds" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
            <div class="flex items-center gap-2">
                <select name="target_language_id"
                        class="px-3 py-1.5 border border-indigo-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                    @foreach($languages as $lang)
                    <option value="{{ $lang->id }}">{{ $lang->name }} ({{ $lang->code }})</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-4 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-plus mr-1"></i> Taslak Oluştur
                </button>
            </div>
        </form>
    </div>

    {{-- Items table --}}
    @if(empty($items))
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
            <i class="fas fa-check-circle text-5xl text-green-400 mb-3"></i>
            <p class="font-semibold text-gray-600 text-lg">Harika! Eksik çeviri yok.</p>
            <p class="text-sm mt-1">Tüm {{ $type === 'articles' ? 'yazılar' : 'sayfalar' }} aktif dillerde mevcut.</p>
        </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left w-8">
                        @php $allIds = collect($items)->pluck('id')->all(); @endphp
                        <input type="checkbox"
                               @change="toggleAll($event.target.checked, {{ json_encode($allIds) }})"
                               class="rounded border-gray-300 text-indigo-600">
                    </th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Başlık</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Kaynak Dil</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Mevcut Diller</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Eksik Diller</th>
                    <th class="px-4 py-3 text-right text-gray-600 font-medium w-32">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($items as $item)
                <tr class="hover:bg-gray-50 transition-colors" :class="selectedIds.includes({{ $item['id'] }}) ? 'bg-indigo-50' : ''">
                    <td class="px-4 py-3">
                        <input type="checkbox"
                               :checked="selectedIds.includes({{ $item['id'] }})"
                               @change="toggle({{ $item['id'] }})"
                               class="rounded border-gray-300 text-indigo-600">
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ $item['edit_url'] }}" class="font-medium text-gray-900 hover:text-indigo-600 line-clamp-1">
                            {{ $item['title'] }}
                        </a>
                        @if($type === 'articles' && isset($item['page']))
                            <span class="text-xs text-gray-400">{{ $item['page']->title }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">
                            {{ $item['language']?->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            <span class="px-1.5 py-0.5 bg-green-100 text-green-700 rounded text-[11px]">
                                {{ $item['language']?->code ?? '?' }}
                            </span>
                            @foreach($item['translations'] as $trans)
                            <span class="px-1.5 py-0.5 bg-green-100 text-green-700 rounded text-[11px]">
                                {{ $trans->language?->code ?? '?' }}
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach($item['missing'] as $lang)
                            <a href="{{ $item['translate_url'] }}?lang={{ $lang->id }}"
                               class="px-1.5 py-0.5 bg-red-100 text-red-600 rounded text-[11px] hover:bg-red-200 transition-colors"
                               title="{{ $lang->name }} çevirisi oluştur">
                                + {{ $lang->code }}
                            </a>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ $item['translate_url'] }}"
                           class="text-xs px-2.5 py-1 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-language mr-1"></i> Çevir
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
@endsection
