{{--
    AI ile Sayfa Oluştur — Pages index'inden açılan wizard modal.

    Workflow:
      1. Admin promptu yazar + dil seçer.
      2. "Önizle" → preview JSON döner (sayfa kaydedilmez).
      3. Önizleme paneli: title, slug, kullanılan blok sayısı, blok type'ları.
      4. "Oluştur ve Düzenle" → auto_save=true ile yeni sayfa kaydedilir
         ve admin direkt edit ekranına yönlendirilir.
      5. "Tekrar Üret" → preview'ı atıp prompt'a geri döner.

    Variables (via include):
      $languages  Language collection (id, code, name)
--}}
<div x-show="open" x-cloak
     class="fixed inset-0 z-[80] flex items-center justify-center bg-black/50 p-4"
     @click.self="open = false; reset();"
     @keydown.escape.window="open = false; reset();">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-gray-200 p-6 max-h-[90vh] overflow-y-auto">

        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-wand-magic-sparkles text-purple-500"></i>
                    AI ile Sayfa Oluştur
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Sayfayı tarif edin — AI uygun blok şablonlarını seçip içerikleri üretir.
                    Önce önizlersiniz, sonra kaydetmeden önce gözden geçirirsiniz.
                </p>
            </div>
            <button type="button" @click="open = false; reset();"
                    class="text-gray-400 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- ── Prompt step ───────────────────────────────────────── --}}
        <div x-show="!preview" class="space-y-4">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">
                        Sayfa Tanımı <span class="text-red-500">*</span>
                    </label>
                    {{-- Boost butonu --}}
                    <button type="button"
                            @click="boostPrompt()"
                            :disabled="boosting || prompt.trim().length < 5 || loading"
                            title="Kısa tarifinizi AI ile ayrıntılı prompta dönüştürür"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border transition-all
                                   bg-gradient-to-r from-amber-50 to-yellow-50 border-amber-200 text-amber-700
                                   hover:from-amber-100 hover:to-yellow-100 hover:border-amber-300
                                   disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fas" :class="boosting ? 'fa-spinner fa-spin' : 'fa-bolt'"></i>
                        <span x-text="boosting ? 'Geliştiriliyor…' : '✨ Promptu Geliştir'"></span>
                    </button>
                </div>
                <textarea x-model="prompt" rows="5"
                          maxlength="1000"
                          :placeholder="boosting ? 'AI promptunuzu geliştiriyor…' : 'Örn: Diş kliniği için \'Hizmetlerimiz\' sayfası. Hero alanı, 4 hizmet kartı (implant, gülüş tasarımı, ortodonti, diş beyazlatma), randevu butonu olan CTA bölümü.'"
                          :class="boosting ? 'opacity-50' : ''"
                          :disabled="boosting"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition-opacity"></textarea>
                <div class="flex items-center justify-between mt-1">
                    <p class="text-[11px] text-gray-400">
                        <span x-text="prompt.length"></span>/1000 karakter — ne kadar detaylı yazarsanız o kadar iyi sonuç.
                    </p>
                    <p x-show="boosted" x-cloak class="text-[11px] text-amber-600 flex items-center gap-1">
                        <i class="fas fa-check-circle"></i> Prompt AI ile geliştirildi
                    </p>
                </div>
                <div x-show="boostError" x-cloak
                     class="mt-1 text-xs text-red-600 flex items-center gap-1">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span x-text="boostError"></span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dil</label>
                    <select x-model="languageId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Seçim yok —</option>
                        @foreach($languages as $lang)
                            <option value="{{ $lang->id }}" data-code="{{ $lang->code }}">
                                {{ $lang->name ?? $lang->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">İçerik dili (locale)</label>
                    <select x-model="locale"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="tr">Türkçe</option>
                        <option value="en">İngilizce</option>
                        <option value="de">Almanca</option>
                        <option value="ru">Rusça</option>
                        <option value="ar">Arapça</option>
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
                        :disabled="loading || prompt.trim().length < 5"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center gap-2">
                    <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-eye'"></i>
                    <span x-text="loading ? 'Üretiliyor… ~10s' : 'Önizle'"></span>
                </button>
            </div>
        </div>

        {{-- ── Preview step ──────────────────────────────────────── --}}
        <div x-show="preview" x-cloak class="space-y-4">
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 text-sm text-emerald-800 flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span>AI sayfanızı üretti. Aşağıdaki özeti inceleyin, sonra "Oluştur ve Düzenle"ye basın.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <p class="text-[11px] uppercase text-gray-500 font-semibold mb-1">Başlık</p>
                    <p class="text-sm text-gray-900 font-medium" x-text="preview?.title || '—'"></p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <p class="text-[11px] uppercase text-gray-500 font-semibold mb-1">URL Slug</p>
                    <p class="text-sm font-mono text-gray-900" x-text="preview?.slug || '—'"></p>
                </div>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                <p class="text-[11px] uppercase text-gray-500 font-semibold mb-2">
                    Bloklar (<span x-text="(preview?.picked_template_ids || []).length"></span>)
                </p>
                <div class="space-y-1 max-h-48 overflow-y-auto">
                    <template x-for="(block, i) in flatBlocks()" :key="i">
                        <div class="flex items-start gap-2 text-xs bg-white border border-gray-200 rounded px-2 py-1.5">
                            <span class="font-mono text-gray-400" x-text="(i+1).toString().padStart(2,'0')"></span>
                            <span class="font-medium text-indigo-700" x-text="block.type"></span>
                            <span class="text-gray-500" x-text="block.variation ? '/'+block.variation : ''"></span>
                            <span class="text-gray-700 truncate flex-1"
                                  x-text="firstContentString(block.content) || '(içerik özeti yok)'"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="status" x-cloak
                 class="text-xs rounded-lg p-3"
                 :class="statusOk ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                  : 'bg-red-50 text-red-700 border border-red-200'">
                <i class="fas" :class="statusOk ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i>
                <span x-text="status"></span>
            </div>

            <div class="flex items-center justify-between gap-2 pt-2">
                <button type="button" @click="preview = null; status = ''"
                        class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-1"></i> Tekrar Üret
                </button>
                <button type="button" @click="generate(true)"
                        :disabled="loading"
                        class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2 shadow-sm">
                    <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                    <span x-text="loading ? 'Kaydediliyor…' : 'Oluştur ve Düzenle'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function aiPageWizard() {
    return {
        open: false,
        prompt: '',
        languageId: '',
        locale: 'tr',
        loading: false,
        status: '',
        statusOk: false,
        preview: null,
        // Boost state
        boosting: false,
        boosted: false,
        boostError: '',

        reset() {
            this.prompt = '';
            this.languageId = '';
            this.locale = 'tr';
            this.loading = false;
            this.status = '';
            this.statusOk = false;
            this.preview = null;
            this.boosting = false;
            this.boosted = false;
            this.boostError = '';
        },

        async boostPrompt() {
            const raw = this.prompt.trim();
            if (raw.length < 5 || this.boosting) return;

            this.boosting   = true;
            this.boostError = '';
            this.boosted    = false;

            try {
                const r = await fetch(@js(route('admin.ai.boost-prompt', [], false)), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ prompt: raw, locale: this.locale || 'tr' }),
                });
                const data = await r.json().catch(() => ({ ok: false, message: 'Geçersiz yanıt' }));

                if (!r.ok || !data.ok) {
                    this.boostError = data.message || 'Prompt geliştirilemedi.';
                    return;
                }

                this.prompt = data.boosted || raw;
                this.boosted = true;
            } catch (e) {
                this.boostError = e.message || 'Ağ hatası.';
            } finally {
                this.boosting = false;
            }
        },

        async generate(autoSave) {
            if (this.prompt.trim().length < 5) {
                this.status = 'Lütfen daha açıklayıcı bir tanım yazın (en az 5 karakter).';
                this.statusOk = false;
                return;
            }
            this.loading = true;
            this.status = '';
            this.statusOk = false;
            try {
                // Async mod: istek job'u kuyruğa atar, hemen döner;
                // sonuç status endpoint'i poll'lanarak alınır → 504 riski yok.
                const r = await fetch(@js(route('admin.ai.pages.generate', [], false)), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        prompt: this.prompt,
                        language_id: this.languageId || null,
                        locale: this.locale || 'tr',
                        auto_save: autoSave,
                        async: true,
                    }),
                });
                const data = await r.json().catch(() => ({ ok: false, message: 'Geçersiz yanıt' }));

                if (!r.ok || !data.ok) {
                    this.showError(data);
                    return;
                }

                // Kuyruk modu: status_url'i poll'la
                if (data.mode === 'queued' && data.status_url) {
                    this.status = 'AI sayfanızı hazırlıyor… (kuyrukta)';
                    this.statusOk = true;
                    const final = await this.pollStatus(data.status_url);

                    if (!final || final.status === 'failed') {
                        this.showError(final || { message: 'Üretim zaman aşımına uğradı.' });
                        return;
                    }
                    this.applyResult(final, autoSave);
                    return;
                }

                // Sync yanıt (queue=sync ortamı) — eski davranış
                this.applyResult(data, autoSave);
            } catch (e) {
                this.status = e.message || 'Ağ hatası.';
                this.statusOk = false;
            } finally {
                this.loading = false;
            }
        },

        /** status endpoint'ini done/failed gelene dek poll'la (max ~4 dk). */
        async pollStatus(url) {
            const messages = [
                'AI sayfanızı hazırlıyor…',
                'Bloklar seçiliyor…',
                'İçerik yazılıyor…',
                'Son rötuşlar yapılıyor…',
            ];
            for (let i = 0; i < 120; i++) {
                await new Promise(res => setTimeout(res, 2000));
                this.status = messages[Math.min(Math.floor(i / 8), messages.length - 1)];
                this.statusOk = true;

                try {
                    const r = await fetch(url, {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' },
                    });
                    const s = await r.json().catch(() => null);
                    if (s && (s.status === 'done' || s.status === 'failed')) return s;
                    if (r.status === 404) return null;
                } catch (e) { /* geçici ağ hatası — poll devam */ }
            }
            return null;
        },

        applyResult(data, autoSave) {
            if (autoSave && data.redirect_url) {
                this.status = 'Sayfa oluşturuldu, edit ekranına yönlendiriliyorsunuz…';
                this.statusOk = true;
                window.location.href = data.redirect_url;
                return;
            }
            this.preview = data.preview;
            this.status = '';
        },

        showError(data) {
            let msg = (data && data.message) || 'AI cevap üretemedi.';
            if (data && data.error_code === 'quota_exceeded' && data.limit != null) {
                msg = `${data.message} (kalan ${data.limit - data.used}/${data.limit})`;
            }
            this.status = msg;
            this.statusOk = false;
        },

        /** Walk the region-based sections_json and yield blocks for the preview list. */
        flatBlocks() {
            const sections = this.preview?.sections_json;
            if (!sections || typeof sections !== 'object') return [];
            const regions = sections.regions || {};
            const out = [];
            ['header', 'body', 'footer'].forEach(region => {
                (regions[region] || []).forEach(row => {
                    (row.columns || []).forEach(col => {
                        (col.blocks || []).forEach(block => out.push(block));
                    });
                });
            });
            return out;
        },

        /** Find the first non-empty string in a block's content map for the preview row. */
        firstContentString(content) {
            if (!content || typeof content !== 'object') return '';
            for (const v of Object.values(content)) {
                if (typeof v === 'string' && v.trim().length > 0) {
                    return v.length > 80 ? v.slice(0, 80) + '…' : v;
                }
            }
            return '';
        },
    };
}
</script>
@endpush
