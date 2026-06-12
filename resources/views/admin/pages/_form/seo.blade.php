<!-- SEO -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
     x-data="seoPanel({
        open: {{ isset($page) && $page->seo ? 'true' : 'false' }},
        url:  @js(isset($page) && $page->exists ? route('admin.pages.ai.seo-meta', $page, false) : null),
        csrf: @js(csrf_token()),
     })">
    <button type="button" @click="open = !open" class="flex items-center justify-between w-full">
        <h3 class="text-base font-semibold text-gray-800">SEO Ayarları</h3>
        <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-gray-400"></i>
    </button>

    <div x-show="open" x-collapse class="mt-4 space-y-4">

        @if(isset($page) && $page->exists)
            {{-- AI generate row --}}
            <div class="flex items-center gap-2 p-3 rounded-lg bg-gradient-to-r from-purple-50 to-indigo-50 border border-indigo-100">
                <i class="fas fa-magic text-indigo-500"></i>
                <div class="flex-1">
                    <p class="text-xs font-medium text-gray-700">SEO meta'yı AI ile üret</p>
                    <p class="text-[11px] text-gray-500">Sayfa içeriğinden Title, Description ve Keywords önerir. Kaydetmeden önce gözden geçirin.</p>
                </div>
                <button type="button"
                        @click="generate()"
                        :disabled="loading || !url"
                        class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-bolt'"></i>
                    <span x-text="loading ? 'Üretiliyor…' : 'AI ile Üret'"></span>
                </button>
            </div>

            <div x-show="status" x-cloak class="text-xs rounded-lg p-2 -mt-2"
                 :class="statusOk ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                  : 'bg-red-50 text-red-700 border border-red-200'">
                <i class="fas" :class="statusOk ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i>
                <span x-text="status"></span>
            </div>
        @endif

        <div>
            <label for="seo_title" class="block text-sm font-medium text-gray-700 mb-1">Meta Başlık</label>
            <input type="text" id="seo_title" name="seo_title"
                   value="{{ old('seo_title', $page->seo?->meta_title ?? '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   maxlength="70">
            <p class="mt-1 text-xs text-gray-400">Maks. 70 karakter önerilir</p>
        </div>

        <div>
            <label for="seo_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Açıklama</label>
            <textarea id="seo_description" name="seo_description" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                      maxlength="160"
            >{{ old('seo_description', $page->seo?->meta_description ?? '') }}</textarea>
            <p class="mt-1 text-xs text-gray-400">Maks. 160 karakter önerilir</p>
        </div>

        <div>
            <label for="seo_keywords" class="block text-sm font-medium text-gray-700 mb-1">Anahtar Kelimeler</label>
            <input type="text" id="seo_keywords" name="seo_keywords"
                   value="{{ old('seo_keywords', $page->seo?->meta_keywords ?? '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="kelime1, kelime2, kelime3">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="seo_h1" class="block text-sm font-medium text-gray-700 mb-1">H1 Geçersiz Kıl</label>
                <input type="text" id="seo_h1" name="seo_h1"
                       value="{{ old('seo_h1', $page->seo?->h1_override ?? '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div>
                <label for="seo_canonical" class="block text-sm font-medium text-gray-700 mb-1">Canonical URL</label>
                <input type="url" id="seo_canonical" name="seo_canonical"
                       value="{{ old('seo_canonical', $page->seo?->canonical_url ?? '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="seo_noindex" value="0">
            <input type="checkbox" id="seo_noindex" name="seo_noindex" value="1"
                   {{ old('seo_noindex', $page->seo?->is_noindex ?? false) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="seo_noindex" class="text-sm text-gray-700">noindex (Arama motorlarından gizle)</label>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function seoPanel(cfg) {
        return {
            open:     cfg.open,
            url:      cfg.url,
            csrf:     cfg.csrf,
            loading:  false,
            status:   '',
            statusOk: false,

            async generate() {
                if (!this.url || this.loading) return;
                this.loading  = true;
                this.status   = '';
                this.statusOk = false;
                try {
                    const r = await fetch(this.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await r.json().catch(() => ({ ok: false, message: 'Geçersiz yanıt' }));

                    if (!r.ok || !data.ok) {
                        // Quota exceeded gets a friendlier sentence with upgrade hint
                        let msg = data.message || 'AI cevap üretemedi.';
                        if (data.error_code === 'quota_exceeded') {
                            msg = `${data.message} (kalan ${data.limit - data.used}/${data.limit})`;
                        }
                        this.status   = msg;
                        this.statusOk = false;
                        return;
                    }

                    // Fill the form
                    const meta = data.meta || {};
                    if (meta.title       !== undefined) document.getElementById('seo_title').value       = meta.title;
                    if (meta.description !== undefined) document.getElementById('seo_description').value = meta.description;
                    if (meta.keywords    !== undefined) document.getElementById('seo_keywords').value    = meta.keywords;

                    this.status   = 'SEO meta üretildi. Gözden geçirip Kaydet\'e tıklayın.';
                    this.statusOk = true;
                } catch (e) {
                    this.status   = e.message || 'Ağ hatası.';
                    this.statusOk = false;
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
@endpush
