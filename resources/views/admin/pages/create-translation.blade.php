@extends('admin.layouts.app')

@section('title', 'Çeviri Oluştur: ' . $page->title)
@section('page-title')
    <span class="text-gray-400 font-normal text-sm">Çeviri Oluştur →</span>
    {{ $page->title }}
@endsection

@section('content')

{{-- Source info banner --}}
<div class="mb-6 flex items-start gap-4 rounded-xl border border-indigo-200 bg-indigo-50 p-4">
    <div class="mt-0.5 flex-shrink-0 text-indigo-500"><i class="fas fa-language text-xl"></i></div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-indigo-800 mb-1">
            "{{ $page->title }}" sayfasının çevirisi oluşturuluyor
        </p>
        <p class="text-xs text-indigo-600">
            Kaynak dil: <strong>{{ $page->language?->name }}</strong>
            &nbsp;·&nbsp;
            İçerik kopyalanıp seçtiğiniz dile göre kaydedilecektir.
        </p>
    </div>
    <div class="flex-shrink-0">
        <button type="button" id="btn-ai-translate"
                class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 flex items-center gap-1.5 disabled:opacity-50"
                title="Yapay zeka ile tüm alanları otomatik çevir">
            <i class="fas fa-magic"></i> ✨ AI ile Çevir
        </button>
    </div>
</div>

@if($availableLanguages->isEmpty())
    <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-6 text-center text-yellow-800">
        <i class="fas fa-circle-info text-2xl mb-2"></i>
        <p class="font-semibold">Tüm aktif dillerde çeviri zaten mevcut.</p>
        <p class="text-sm mt-1">Yeni bir dil eklemek için <a href="{{ route('admin.languages.index') }}" class="underline">Diller</a> sayfasını ziyaret edin.</p>
    </div>
@else

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4">
            <div class="flex items-center gap-2 font-semibold text-red-800 mb-2">
                <i class="fas fa-circle-exclamation"></i> Kayıt başarısız — lütfen hataları düzeltin:
            </div>
            <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data" id="translation-form">
        @csrf

        {{-- Hidden: link this translation to the source page --}}
        <input type="hidden" name="root_page_id" value="{{ $page->root_page_id ?? $page->id }}">

        {{-- Target language selector (top) --}}
        <div class="mb-6 flex items-center gap-3 bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <label class="text-sm font-medium text-gray-700 flex-shrink-0">
                <i class="fas fa-globe mr-1 text-indigo-500"></i> Hedef Dil
            </label>
            <select name="language_id" id="language_id"
                    class="flex-1 max-w-xs px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @foreach($availableLanguages as $lang)
                    <option value="{{ $lang->id }}" {{ $lang->id == $targetLanguageId ? 'selected' : '' }}>
                        {{ $lang->name }} ({{ $lang->code }})
                    </option>
                @endforeach
            </select>
            <span class="text-xs text-gray-400">Kaynak: {{ $page->language?->name }}</span>
        </div>

        {{-- Reuse the shared page form --}}
        {{-- Pre-fill with source page values (editable) --}}
        @php
            // Fake $page context for the form partial — we want the form's
            // old() fallback to kick in with the source page data.
            // We'll pass the source as $sourcePage and output hidden defaults.
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                @include('admin.pages._form.basic-info', ['prefill' => $page])
                @include('admin.pages._form.builder-mode', ['prefill' => $page])
                @include('admin.pages._form.seo', ['prefill' => $page])
            </div>
            <div class="space-y-6">
                @include('admin.pages._form.sidebar.publish')
                @include('admin.pages._form.sidebar.cover')
                @include('admin.pages._form.sidebar.options')
            </div>
        </div>

        @include('admin.pages._form.editor-script')
    </form>

@endif

<script>
(function () {
    const btn = document.getElementById('btn-ai-translate');
    if (!btn) return;

    btn.addEventListener('click', async function () {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Çevriliyor…';

        const langId = document.getElementById('language_id')?.value;

        try {
            const res = await fetch('{{ route('admin.ai.translate-content') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    type: 'page',
                    id: {{ $page->id }},
                    target_language_id: langId,
                }),
            });

            const data = await res.json();

            if (data.result) {
                const r = data.result;
                // Fill title
                const titleEl = document.querySelector('[name="title"]');
                if (titleEl && r.title) titleEl.value = r.title;

                // Fill SEO title / description
                const seoTitleEl = document.querySelector('[name="seo_title"]');
                if (seoTitleEl && r.seo_title) seoTitleEl.value = r.seo_title;

                const seoDescEl = document.querySelector('[name="seo_description"]');
                if (seoDescEl && r.seo_description) seoDescEl.value = r.seo_description;

                btn.innerHTML = '<i class="fas fa-check"></i> Çevrildi';
                btn.classList.replace('bg-indigo-600', 'bg-green-600');
                btn.classList.replace('hover:bg-indigo-700', 'hover:bg-green-700');
            } else {
                alert('AI hatası: ' + (data.error ?? 'Bilinmeyen hata'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-magic"></i> ✨ AI ile Çevir';
            }
        } catch (e) {
            alert('İstek başarısız: ' + e.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-magic"></i> ✨ AI ile Çevir';
        }
    });

    // Re-fetch translation when language changes
    document.getElementById('language_id')?.addEventListener('change', function () {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic"></i> ✨ AI ile Çevir';
        btn.classList.replace('bg-green-600', 'bg-indigo-600');
        btn.classList.replace('hover:bg-green-700', 'hover:bg-indigo-700');
    });
})();
</script>
@endsection
