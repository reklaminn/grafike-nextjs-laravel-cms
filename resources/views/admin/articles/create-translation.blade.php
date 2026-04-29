@extends('admin.layouts.app')

@section('title', 'Çeviri Oluştur: ' . $article->title)
@section('page-title')
    <span class="text-gray-400 font-normal text-sm">Çeviri Oluştur →</span>
    {{ $article->title }}
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css">
<style>
    .ql-toolbar.ql-snow   { border-radius: 0.5rem 0.5rem 0 0; border-color: #d1d5db; background: #f9fafb; }
    .ql-container.ql-snow { border-radius: 0 0 0.5rem 0.5rem; border-color: #d1d5db; }
    [data-quill] .ql-editor { min-height: 200px; font-size: 14px; }
</style>
@endpush

@section('content')

{{-- Source info banner --}}
<div class="mb-6 flex items-start gap-4 rounded-xl border border-indigo-200 bg-indigo-50 p-4">
    <div class="mt-0.5 flex-shrink-0 text-indigo-500"><i class="fas fa-language text-xl"></i></div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-indigo-800 mb-1">
            "{{ $article->title }}" yazısının çevirisi oluşturuluyor
        </p>
        <p class="text-xs text-indigo-600">
            Kaynak dil: <strong>{{ $article->language?->name }}</strong>
            &nbsp;·&nbsp;
            İçerik kopyalanıp seçtiğiniz dile göre kaydedilecektir.
        </p>
    </div>
    <div class="flex-shrink-0">
        <button type="button" id="btn-ai-translate"
                class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 flex items-center gap-1.5 disabled:opacity-50">
            <i class="fas fa-magic"></i> ✨ AI ile Çevir
        </button>
    </div>
</div>

@if($availableLanguages->isEmpty())
    <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-6 text-center text-yellow-800">
        <i class="fas fa-circle-info text-2xl mb-2"></i>
        <p class="font-semibold">Tüm aktif dillerde çeviri zaten mevcut.</p>
    </div>
@else

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4">
            <div class="flex items-center gap-2 font-semibold text-red-800 mb-2">
                <i class="fas fa-circle-exclamation"></i> Kayıt başarısız:
            </div>
            <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data" id="translation-form">
        @csrf

        {{-- Translation link fields --}}
        <input type="hidden" name="parent_article_id" value="{{ $article->id }}">

        {{-- Target language + AI trigger --}}
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
            <span class="text-xs text-gray-400">Kaynak: {{ $article->language?->name }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main content --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Title + Slug --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h3 class="text-base font-semibold text-gray-800">Temel Bilgiler</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Başlık <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title"
                               value="{{ old('title', $article->title) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                        <input type="text" name="slug"
                               value="{{ old('slug') }}"
                               placeholder="Boş bırakırsanız başlıktan üretilir"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Özet</label>
                        <textarea name="excerpt" id="excerpt" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                        >{{ old('excerpt', $article->excerpt) }}</textarea>
                    </div>
                </div>

                {{-- Body --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">İçerik</h3>
                    <div data-quill>
                        <div id="quill-editor">{!! old('body', $article->body) !!}</div>
                    </div>
                    <input type="hidden" name="body" id="body-hidden" value="{{ old('body', $article->body) }}">
                </div>

                {{-- SEO --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h3 class="text-base font-semibold text-gray-800">SEO</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SEO Başlık</label>
                        <input type="text" name="seo_title" id="seo_title"
                               value="{{ old('seo_title', $article->seo?->meta_title) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SEO Açıklama</label>
                        <textarea name="seo_description" id="seo_description" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                        >{{ old('seo_description', $article->seo?->meta_description) }}</textarea>
                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                {{-- Status / Save --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Durum</h3>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-4 focus:ring-2 focus:ring-indigo-500">
                        <option value="draft" selected>Taslak</option>
                        <option value="published">Yayında</option>
                    </select>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sayfa</label>
                        <select name="page_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">— Seçin —</option>
                            @foreach($pages as $p)
                                <option value="{{ $p->id }}" {{ $p->id == $article->page_id ? 'selected' : '' }}>{{ $p->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-1"></i> Taslak Kaydet
                    </button>
                </div>

                {{-- Source article link --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-xs text-blue-700">
                    <p class="font-semibold mb-1"><i class="fas fa-link mr-1"></i> Kaynak Yazı</p>
                    <p class="mb-2">{{ $article->title }}</p>
                    <a href="{{ route('admin.articles.edit', $article) }}"
                       class="text-blue-600 underline">Kaynağı düzenle →</a>
                </div>

            </div>
        </div>
    </form>

@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
(function () {
    // Quill editor setup
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: { toolbar: [[{header:[1,2,3,false]}],'bold','italic','underline',{list:'ordered'},{list:'bullet'},'link'] },
    });

    document.getElementById('translation-form')?.addEventListener('submit', function() {
        document.getElementById('body-hidden').value = quill.root.innerHTML;
    });

    // AI translate
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
                    type: 'article',
                    id: {{ $article->id }},
                    target_language_id: langId,
                }),
            });

            const data = await res.json();

            if (data.result) {
                const r = data.result;
                if (r.title) document.getElementById('title').value = r.title;
                if (r.excerpt) document.getElementById('excerpt').value = r.excerpt;
                if (r.body) {
                    quill.root.innerHTML = r.body;
                    document.getElementById('body-hidden').value = r.body;
                }
                if (r.seo_title) document.getElementById('seo_title').value = r.seo_title;
                if (r.seo_description) document.getElementById('seo_description').value = r.seo_description;

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

    document.getElementById('language_id')?.addEventListener('change', function () {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic"></i> ✨ AI ile Çevir';
        btn.classList.replace('bg-green-600', 'bg-indigo-600');
        btn.classList.replace('hover:bg-green-700', 'hover:bg-indigo-700');
    });
})();
</script>
@endpush
@endsection
