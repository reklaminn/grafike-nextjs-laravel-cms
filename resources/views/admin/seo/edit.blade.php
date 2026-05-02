@extends('admin.layouts.app')
@section('title', 'SEO Düzenle')

@section('content')
<div class="max-w-3xl" x-data="{
    activeTab: 'basic',
    generateSd: false,
    generateHreflang: false,
    sdJson: {{ json_encode(old('structured_data', $seoEntry->structured_data ? json_encode($seoEntry->structured_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '')) }},
    hreflangJson: {{ json_encode(old('hreflang_tags', $seoEntry->hreflang_tags ? json_encode($seoEntry->hreflang_tags, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '')) }},
    hreflangRows: [],
    schemaType: '{{ old('schema_type', $seoEntry->schema_type ?? 'WebPage') }}',

    async doGenerateSd() {
        this.generateSd = true;
        try {
            const r = await fetch('{{ route('admin.seo.generate-structured-data', $seoEntry) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
                body: JSON.stringify({ schema_type: this.schemaType }),
            });
            const d = await r.json();
            if (d.schema_json) { this.sdJson = d.schema_json; }
        } finally { this.generateSd = false; }
    },

    async doGenerateHreflang() {
        this.generateHreflang = true;
        try {
            const r = await fetch('{{ route('admin.seo.generate-hreflang', $seoEntry) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            });
            const d = await r.json();
            if (d.tags_json) { this.hreflangJson = d.tags_json; }
            if (d.message)   { alert(d.message); }
        } finally { this.generateHreflang = false; }
    },
}">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">SEO Düzenle</h1>
        <a href="{{ route('admin.seo.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Geri</a>
    </div>

    {{-- Entity Info --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm">
        <strong>İlişkili:</strong>
        {{ str_contains($seoEntry->seoable_type, 'Page') ? 'Sayfa' : 'Yazı' }} —
        {{ $seoEntry->seoable?->title ?? 'Silinmiş' }}
    </div>

    {{-- SERP Preview --}}
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6" x-data="{
        title: '{{ addslashes(old('meta_title', $seoEntry->meta_title ?? $seoEntry->seoable?->title ?? '')) }}',
        desc: '{{ addslashes(old('meta_description', $seoEntry->meta_description ?? '')) }}',
        slug: '{{ old('slug', $seoEntry->slug ?? '') }}'
    }">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">
            <i class="fab fa-google mr-1 text-blue-500"></i> Google SERP Önizleme
        </h3>
        <div class="bg-white border rounded-lg p-4 max-w-2xl">
            <div class="text-lg text-blue-800 hover:underline cursor-pointer truncate" x-text="title || 'Sayfa Başlığı'" style="font-family: arial, sans-serif;"></div>
            <div class="text-sm text-green-700 mt-0.5" style="font-family: arial, sans-serif;" x-text="'{{ url('/') }}/' + slug"></div>
            <div class="text-sm text-gray-600 mt-1 line-clamp-2" style="font-family: arial, sans-serif;" x-text="desc || 'Meta açıklama buraya gelecek...'"></div>
        </div>
        <div class="flex gap-4 mt-3 text-xs text-gray-400">
            <span>Başlık: <strong :class="title.length > 60 ? 'text-red-500' : 'text-green-600'" x-text="title.length + '/60'"></strong></span>
            <span>Açıklama: <strong :class="desc.length > 160 ? 'text-red-500' : 'text-green-600'" x-text="desc.length + '/160'"></strong></span>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex gap-1 mb-6 bg-gray-100 p-1 rounded-lg">
        <button type="button" @click="activeTab='basic'"
            :class="activeTab==='basic' ? 'bg-white shadow text-indigo-600' : 'text-gray-600 hover:text-gray-800'"
            class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition">
            Temel SEO
        </button>
        <button type="button" @click="activeTab='og'"
            :class="activeTab==='og' ? 'bg-white shadow text-indigo-600' : 'text-gray-600 hover:text-gray-800'"
            class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition">
            Open Graph
        </button>
        <button type="button" @click="activeTab='hreflang'"
            :class="activeTab==='hreflang' ? 'bg-white shadow text-indigo-600' : 'text-gray-600 hover:text-gray-800'"
            class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition">
            Hreflang
        </button>
        <button type="button" @click="activeTab='schema'"
            :class="activeTab==='schema' ? 'bg-white shadow text-indigo-600' : 'text-gray-600 hover:text-gray-800'"
            class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition">
            Structured Data
        </button>
        <button type="button" @click="activeTab='sitemap'"
            :class="activeTab==='sitemap' ? 'bg-white shadow text-indigo-600' : 'text-gray-600 hover:text-gray-800'"
            class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition">
            Sitemap
        </button>
    </div>

    <form method="POST" action="{{ route('admin.seo.update', $seoEntry, false) }}" class="space-y-5">
        @csrf @method('PUT')

        {{-- ─── Tab: Temel SEO ──────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'basic'" class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-sm">{{ url('/') }}/</span>
                    <input type="text" name="slug" value="{{ old('slug', $seoEntry->slug) }}" required
                           class="flex-1 px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
                </div>
                @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title <span class="text-gray-400 font-normal">(max 70)</span></label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $seoEntry->meta_title) }}" maxlength="70"
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description <span class="text-gray-400 font-normal">(max 160)</span></label>
                <textarea name="meta_description" rows="3" maxlength="160"
                          class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">{{ old('meta_description', $seoEntry->meta_description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Keywords</label>
                <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $seoEntry->meta_keywords) }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500" placeholder="kelime1, kelime2">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">H1 Override</label>
                    <input type="text" name="h1_override" value="{{ old('h1_override', $seoEntry->h1_override) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Canonical URL</label>
                    <input type="url" name="canonical_url" value="{{ old('canonical_url', $seoEntry->canonical_url) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500" placeholder="https://...">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_noindex" value="0">
                <input type="checkbox" name="is_noindex" value="1" id="is_noindex"
                       {{ old('is_noindex', $seoEntry->is_noindex) ? 'checked' : '' }} class="rounded text-indigo-600">
                <label for="is_noindex" class="text-sm text-gray-700">Noindex — Arama motorlarından gizle</label>
            </div>

            {{-- Advanced --}}
            <details class="border rounded-lg">
                <summary class="px-4 py-3 cursor-pointer text-sm font-medium text-gray-700 hover:bg-gray-50">Sayfa CSS / JS</summary>
                <div class="p-4 space-y-4 border-t">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sayfa CSS</label>
                        <textarea name="page_css" rows="4" class="w-full px-3 py-2 border rounded-lg text-sm font-mono focus:ring-indigo-500">{{ old('page_css', $seoEntry->page_css) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sayfa JS</label>
                        <textarea name="page_js" rows="4" class="w-full px-3 py-2 border rounded-lg text-sm font-mono focus:ring-indigo-500">{{ old('page_js', $seoEntry->page_js) }}</textarea>
                    </div>
                </div>
            </details>
        </div>

        {{-- ─── Tab: Open Graph ─────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'og'" class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-700">
                Boş bırakılan alanlar otomatik olarak Meta Title, Meta Description ve kapak görselinden doldurulur.
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">OG Image URL</label>
                <input type="url" name="og_image" value="{{ old('og_image', $seoEntry->og_image) }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500" placeholder="https://... (1200×630 önerilir)">
                @if($seoEntry->og_image)
                    <img src="{{ $seoEntry->og_image }}" alt="OG önizleme" class="mt-2 rounded h-24 object-cover">
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">OG Type</label>
                <select name="og_type" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
                    @foreach(['website' => 'Website', 'article' => 'Article', 'product' => 'Product', 'service' => 'Service'] as $val => $label)
                        <option value="{{ $val }}" {{ old('og_type', $seoEntry->og_type ?? 'website') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ─── Tab: Hreflang ───────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'hreflang'" class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Hreflang Etiketleri</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Çok dilli sayfalar için dil/bölge alternatifleri. Sayfaları <code class="bg-gray-100 px-1 rounded">root_page_id</code> ile bağladıktan sonra otomatik üretin.
                    </p>
                </div>
                <button type="button" @click="doGenerateHreflang()"
                    :disabled="generateHreflang"
                    class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                    <span x-show="!generateHreflang">✨ Otomatik Üret</span>
                    <span x-show="generateHreflang">Üretiliyor...</span>
                </button>
            </div>

            {{-- JSON editor --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">JSON (locale → URL)</label>
                <textarea name="hreflang_tags" rows="8"
                          x-model="hreflangJson"
                          class="w-full px-3 py-2 border rounded-lg text-xs font-mono focus:ring-indigo-500"
                          placeholder='{
  "tr": "https://example.com/tr/hakkimizda",
  "en": "https://example.com/en/about-us",
  "x-default": "https://example.com/tr/hakkimizda"
}'></textarea>
                <p class="text-xs text-gray-400 mt-1">Geçerli JSON • x-default eklenmeyi unutmayın</p>
            </div>

            {{-- Live table preview --}}
            <template x-if="hreflangJson">
                <div class="border rounded-lg overflow-hidden">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-gray-600 font-medium">Dil/Bölge</th>
                                <th class="px-3 py-2 text-left text-gray-600 font-medium">URL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="[lang, url] in Object.entries((() => { try { return JSON.parse(hreflangJson); } catch(e) { return {}; } }()))" :key="lang">
                                <tr class="border-t">
                                    <td class="px-3 py-2 font-mono text-indigo-600" x-text="lang"></td>
                                    <td class="px-3 py-2 text-gray-600 truncate max-w-xs" x-text="url"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>

        {{-- ─── Tab: Structured Data ────────────────────────────────────────── --}}
        <div x-show="activeTab === 'schema'" class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Structured Data (JSON-LD)</h3>
                    <p class="text-xs text-gray-500 mt-1">Schema.org formatında yapılandırılmış veri. Google rich result'ları için.</p>
                </div>
                <a href="https://search.google.com/test/rich-results" target="_blank"
                   class="shrink-0 text-xs text-blue-600 hover:underline flex items-center gap-1">
                    <i class="fas fa-external-link-alt text-[10px]"></i> Test Et
                </a>
            </div>

            <div class="flex gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Schema Türü</label>
                    <select name="schema_type" x-model="schemaType" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
                        <option value="">— Seçin —</option>
                        @foreach($schemaTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('schema_type', $seoEntry->schema_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" @click="doGenerateSd()" :disabled="generateSd || !schemaType"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 whitespace-nowrap">
                        <span x-show="!generateSd">✨ Generate</span>
                        <span x-show="generateSd">Üretiliyor...</span>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">JSON-LD</label>
                <textarea name="structured_data" rows="16"
                          x-model="sdJson"
                          class="w-full px-3 py-2 border rounded-lg text-xs font-mono focus:ring-indigo-500"
                          placeholder='{ "@@context": "https://schema.org", "@@type": "WebPage", ... }'></textarea>
                <p class="text-xs text-gray-400 mt-1">Bu içerik sayfanın &lt;head&gt; etiketine <code>&lt;script type="application/ld+json"&gt;</code> olarak eklenir.</p>
            </div>
        </div>

        {{-- ─── Tab: Sitemap ────────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'sitemap'" class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Öncelik (Priority)</label>
                    <input type="number" name="sitemap_priority" step="0.1" min="0" max="1"
                           value="{{ old('sitemap_priority', $seoEntry->sitemap_priority ?? 0.5) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">0.0 – 1.0 (Ana sayfa: 1.0)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Değişim Sıklığı</label>
                    <select name="sitemap_changefreq" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-indigo-500">
                        @foreach(['always','hourly','daily','weekly','monthly','yearly','never'] as $freq)
                            <option value="{{ $freq }}" {{ old('sitemap_changefreq', $seoEntry->sitemap_changefreq ?? 'weekly') === $freq ? 'selected' : '' }}>{{ ucfirst($freq) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="sitemap_exclude" value="0">
                <input type="checkbox" name="sitemap_exclude" value="1" id="sitemap_exclude"
                       {{ old('sitemap_exclude', $seoEntry->sitemap_exclude ?? false) ? 'checked' : '' }} class="rounded text-red-500">
                <label for="sitemap_exclude" class="text-sm text-gray-700 text-red-700">Sitemap'ten Çıkar</label>
            </div>
        </div>

        {{-- Save --}}
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                <i class="fas fa-save mr-1"></i> Kaydet
            </button>
            <a href="{{ route('admin.seo.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">İptal</a>
        </div>
    </form>
</div>
@endsection
