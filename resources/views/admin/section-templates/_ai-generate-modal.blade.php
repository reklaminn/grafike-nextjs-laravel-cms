{{--
    AI ile Section Template Oluştur — wizard modal.

    Workflow:
      Step 1 (prompt): admin tarif yazar (renk, stil, dil hint'leri ile).
      Step 2 (preview): generated name + type + variation + warnings + HTML preview iframe.
      Step 3 (commit):  "Kaydet ve Düzenle" → SectionTemplate kaydı (is_active=false) + redirect.

    No external state — pure Alpine scoped inside aiSectionTemplateWizard().
--}}
<div x-show="open" x-cloak
     class="fixed inset-0 z-[80] flex items-center justify-center bg-black/50 p-4"
     @click.self="open = false; reset();"
     @keydown.escape.window="open = false; reset();">
    <div class="w-full max-w-3xl rounded-2xl bg-white shadow-2xl border border-gray-200 p-6 max-h-[92vh] overflow-y-auto">

        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-wand-magic-sparkles text-purple-500"></i>
                    AI ile Block Şablonu Oluştur
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Bir blok şablonunu tarif edin — AI Tailwind CSS ile yeniden kullanılabilir HTML şablonu, schema
                    ve örnek içerikle birlikte üretir. Kaydetmeden önce HTML'i önizleyebilirsiniz.
                </p>
            </div>
            <button type="button" @click="open = false; reset();"
                    class="text-gray-400 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- ── Step 1: prompt ─────────────────────────────────────── --}}
        <div x-show="!preview" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Block Tarifi <span class="text-red-500">*</span>
                </label>
                <textarea x-model="prompt" rows="5"
                          maxlength="1500"
                          placeholder="Örn: 3 kolonlu hizmet kartı bölümü. Her kartta ikon (URL), başlık, kısa açıklama ve 'Detay' linki olsun. Üstte ortalı ana başlık + alt başlık. Mobile'da kartlar tek sütun olmalı."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                <p class="text-[11px] text-gray-400 mt-1">
                    <span x-text="prompt.length"></span>/1500 — ne kadar somut yazarsanız o kadar iyi sonuç.
                </p>
            </div>

            {{-- Referans Görsel --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Referans Görsel <span class="text-gray-400 font-normal text-xs">(opsiyonel — AI benzer tasarım üretir)</span>
                </label>
                <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-indigo-400 transition-colors cursor-pointer"
                     @click="$refs.imageInput.click()"
                     @dragover.prevent
                     @drop.prevent="handleImageDrop($event)">
                    <template x-if="!imagePreview">
                        <div class="text-gray-400 text-sm">
                            <i class="fas fa-image text-2xl mb-2 block"></i>
                            PNG, JPG, WebP — maks. 4MB
                        </div>
                    </template>
                    <template x-if="imagePreview">
                        <div class="relative inline-block">
                            <img :src="imagePreview" class="max-h-32 rounded-lg mx-auto object-contain">
                            <button type="button"
                                    @click.stop="clearImage()"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>
                    <input type="file" x-ref="imageInput" class="hidden"
                           accept="image/jpeg,image/png,image/webp,image/gif"
                           @change="handleImageSelect($event)">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Renk paleti (opsiyonel)</label>
                    <select x-model="colorScheme" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">— Nötr —</option>
                        <option value="indigo + slate (kurumsal)">Kurumsal (indigo + slate)</option>
                        <option value="emerald + zinc (yeşil-doğal)">Doğal (emerald + zinc)</option>
                        <option value="amber + neutral (sıcak)">Sıcak (amber + neutral)</option>
                        <option value="rose + gray (yumuşak)">Yumuşak (rose + gray)</option>
                        <option value="black + white (minimal)">Minimal (black + white)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Stil tercihi</label>
                    <select x-model="style" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">— Standart —</option>
                        <option value="minimal düz, kart efekti yok">Minimal</option>
                        <option value="shadow ve subtle border'lar">Kart efektli</option>
                        <option value="gradient backgrounds">Gradient</option>
                        <option value="büyük tipografi, az dekorasyon">Editorial</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Default içerik dili</label>
                    <select x-model="language" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="Türkçe">Türkçe</option>
                        <option value="İngilizce">İngilizce</option>
                    </select>
                </div>
            </div>

            <div x-show="status" x-cloak
                 class="text-xs rounded-lg p-3"
                 :class="statusOk ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                  : 'bg-red-50 text-red-700 border border-red-200'">
                <i class="fas" :class="statusOk ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i>
                <span x-text="status"></span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" @click="open = false; reset();"
                        class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                    İptal
                </button>
                <button type="button" @click="generate(false)"
                        :disabled="loading || prompt.trim().length < 10"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center gap-2">
                    <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-eye'"></i>
                    <span x-text="loading ? 'Üretiliyor… ~15s' : 'Önizle'"></span>
                </button>
            </div>
        </div>

        {{-- ── Step 2: preview ────────────────────────────────────── --}}
        <div x-show="preview" x-cloak class="space-y-4">
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 text-sm text-emerald-800 flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span>Şablon üretildi. HTML önizlemesi aşağıda — beğenirseniz kaydedip düzenleyebilirsiniz.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <p class="text-[11px] uppercase text-gray-500 font-semibold mb-1">Ad</p>
                    <p class="text-sm text-gray-900 font-medium" x-text="preview?.name || '—'"></p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <p class="text-[11px] uppercase text-gray-500 font-semibold mb-1">Type</p>
                    <p class="text-sm font-mono text-gray-900" x-text="preview?.type || '—'"></p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <p class="text-[11px] uppercase text-gray-500 font-semibold mb-1">Variation</p>
                    <p class="text-sm font-mono text-gray-900" x-text="preview?.variation || '—'"></p>
                </div>
            </div>

            {{-- Warnings --}}
            <template x-if="(preview?.warnings || []).length > 0">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-xs font-semibold text-yellow-800 mb-1 flex items-center gap-1">
                        <i class="fas fa-exclamation-triangle"></i> Uyarılar
                    </p>
                    <ul class="text-xs text-yellow-700 list-disc list-inside space-y-0.5">
                        <template x-for="(w, i) in preview.warnings" :key="i">
                            <li x-text="w"></li>
                        </template>
                    </ul>
                    <p class="text-[10px] text-yellow-600 mt-1">
                        Bu uyarılar otomatik düzeltildi (eksik schema key'i eklendi, vb.). Yine de kaydetmeden gözden geçirin.
                    </p>
                </div>
            </template>

            {{-- Schema fields summary --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                <p class="text-[11px] uppercase text-gray-500 font-semibold mb-2">
                    Schema alanları (<span x-text="Object.keys(preview?.schema_json || {}).length"></span>)
                </p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="[key, def] in Object.entries(preview?.schema_json || {})" :key="key">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-50 border border-indigo-200 rounded text-xs">
                            <span class="font-mono text-indigo-700" x-text="key"></span>
                            <span class="text-gray-400" x-text="'('+(def.type || 'text')+')'"></span>
                        </span>
                    </template>
                </div>
            </div>

            {{-- HTML preview iframe --}}
            <div>
                <p class="text-[11px] uppercase text-gray-500 font-semibold mb-2">Canlı önizleme (Tailwind CDN ile)</p>
                <iframe x-ref="previewIframe"
                        class="w-full rounded-lg border border-gray-200 bg-white"
                        style="height: 360px"
                        sandbox="allow-same-origin"></iframe>
            </div>

            {{-- HTML source toggle --}}
            <details class="bg-gray-900 text-gray-100 rounded-lg p-3 group">
                <summary class="text-xs cursor-pointer flex items-center gap-2 select-none">
                    <i class="fas fa-code text-gray-400 group-open:rotate-90 transition-transform"></i>
                    <span>HTML kaynağını göster</span>
                </summary>
                <pre class="mt-2 text-[11px] font-mono overflow-x-auto whitespace-pre-wrap break-all"
                     x-text="preview?.html_template || ''"></pre>
            </details>

            <div x-show="status" x-cloak
                 class="text-xs rounded-lg p-3"
                 :class="statusOk ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                  : 'bg-red-50 text-red-700 border border-red-200'">
                <i class="fas" :class="statusOk ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i>
                <span x-text="status"></span>
            </div>

            <div class="flex items-center justify-between gap-2 pt-2">
                <button type="button" @click="preview = null; status = '';"
                        class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-1"></i> Tekrar Üret
                </button>
                <button type="button" @click="generate(true)"
                        :disabled="loading"
                        class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2 shadow-sm">
                    <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                    <span x-text="loading ? 'Kaydediliyor…' : 'Kaydet ve Düzenle'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function aiSectionTemplateWizard() {
    return {
        open: false,
        prompt: '',
        colorScheme: '',
        style: '',
        language: 'Türkçe',
        loading: false,
        status: '',
        statusOk: false,
        preview: null,
        imageBase64: null,
        imageMime: 'image/jpeg',
        imagePreview: null,

        reset() {
            this.prompt = '';
            this.colorScheme = '';
            this.style = '';
            this.language = 'Türkçe';
            this.loading = false;
            this.status = '';
            this.statusOk = false;
            this.preview = null;
            this.imageBase64 = null;
            this.imageMime = 'image/jpeg';
            this.imagePreview = null;
        },

        handleImageSelect(event) {
            const file = event.target.files[0];
            if (file) this.loadImage(file);
        },

        handleImageDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) this.loadImage(file);
        },

        loadImage(file) {
            if (file.size > 4 * 1024 * 1024) {
                this.status = 'Görsel 4MB\'den büyük olamaz.';
                this.statusOk = false;
                return;
            }
            this.imageMime = file.type || 'image/jpeg';
            const reader = new FileReader();
            reader.onload = (e) => {
                const dataUrl = e.target.result;
                this.imagePreview = dataUrl;
                // Strip "data:image/xxx;base64," prefix → raw base64
                this.imageBase64 = dataUrl.split(',')[1] || null;
            };
            reader.readAsDataURL(file);
        },

        clearImage() {
            this.imageBase64 = null;
            this.imageMime = 'image/jpeg';
            this.imagePreview = null;
            if (this.$refs.imageInput) this.$refs.imageInput.value = '';
        },

        async generate(autoSave) {
            if (this.prompt.trim().length < 10) {
                this.status = 'Daha açıklayıcı bir tarif yazın (en az 10 karakter).';
                this.statusOk = false;
                return;
            }
            this.loading = true;
            this.status = '';
            this.statusOk = false;
            try {
                const r = await fetch(@js(route('admin.ai.section-templates.generate', [], false)), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        prompt: this.prompt,
                        color_scheme: this.colorScheme || null,
                        style: this.style || null,
                        language: this.language || 'Türkçe',
                        auto_save: autoSave,
                        image_base64: this.imageBase64 || null,
                        image_mime: this.imageBase64 ? this.imageMime : null,
                    }),
                });
                const data = await r.json().catch(() => ({ ok: false, message: 'Geçersiz yanıt' }));

                if (!r.ok || !data.ok) {
                    let msg = data.message || 'AI cevap üretemedi.';
                    if (data.error_code === 'quota_exceeded') {
                        msg = `${data.message} (kalan ${data.limit - data.used}/${data.limit})`;
                    }
                    this.status = msg;
                    this.statusOk = false;
                    return;
                }

                if (autoSave && data.redirect_url) {
                    this.status = 'Şablon kaydedildi, düzenleme ekranına yönlendiriliyorsunuz…';
                    this.statusOk = true;
                    window.location.href = data.redirect_url;
                    return;
                }

                this.preview = data.preview;
                this.status = '';
                this.$nextTick(() => this.renderPreviewIframe());
            } catch (e) {
                this.status = e.message || 'Ağ hatası.';
                this.statusOk = false;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Render the generated html_template into the preview iframe, with
         * Tailwind CDN loaded and @verbatim{{placeholders}}@endverbatim replaced by default_content.
         */
        renderPreviewIframe() {
            const iframe = this.$refs.previewIframe;
            if (!iframe || !this.preview) return;

            const html = this.preview.html_template || '';
            const defaults = this.preview.default_content_json || {};

            // Replace @verbatim{{key}}@endverbatim and @verbatim{{{key}}}@endverbatim with default_content values.
            const rendered = html.replace(/\{{2,3}\s*([a-zA-Z][a-zA-Z0-9_]*)\s*\}{2,3}/g, (m, key) => {
                const v = defaults[key];
                return v !== undefined && v !== null ? String(v) : '';
            });

            const doc = `<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"><\/script>
<style>body{margin:0;background:#fff}</style>
</head>
<body>${rendered}</body>
</html>`;

            iframe.srcdoc = doc;
        },
    };
}
</script>
@endpush
