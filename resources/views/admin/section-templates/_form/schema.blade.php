@php
    $schemaArray = old('schema_json_raw')
        ? json_decode(old('schema_json_raw'), true) ?? []
        : ($sectionTemplate->schema_json ?? []);
@endphp

<div id="schema-builder" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
     x-data="schemaBuilder(@js($schemaArray))">

    <div class="flex items-center justify-between gap-3">
        <h3 class="text-base font-semibold text-gray-900">Schema Alanları</h3>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400" x-text="fields.length + ' alan'"></span>
            <button type="button" @click="sortFields()" x-show="fields.length > 1"
                    title="Alanları HTML'deki görünüm sırasına göre diz (placeholder geliş sırası; HTML'de olmayanlar sona)"
                    class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50">
                <i class="fas fa-list-ol"></i> Sırala
            </button>
            <div class="flex rounded-lg border border-gray-200 bg-gray-50 p-0.5">
                <button type="button" @click="mode='visual'"
                        :class="mode==='visual' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                        class="rounded-md px-3 py-1 text-xs font-medium transition">
                    <i class="fas fa-table-list mr-1"></i> Görsel
                </button>
                <button type="button" @click="mode='json'"
                        :class="mode==='json' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                        class="rounded-md px-3 py-1 text-xs font-medium transition">
                    <i class="fas fa-code mr-1"></i> JSON
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden textarea that submits --}}
    <textarea name="schema_json" id="schema_json_input" class="hidden" x-bind:value="serialized"></textarea>

    {{-- GÖRSEL MOD --}}
    <div x-show="mode==='visual'" class="mt-4 space-y-2">
        <template x-for="(field, idx) in fields" :key="field._uid">
            <div class="rounded-lg border bg-gray-50 transition-all"
                 :class="dragIndex === idx ? 'border-indigo-400 opacity-50' : (dragOverIndex === idx && dragIndex !== null ? 'border-indigo-400 border-dashed' : 'border-gray-200')"
                 @dragover.prevent="dragOverIndex = idx"
                 @dragleave="dragOverIndex === idx && (dragOverIndex = null)"
                 @drop.prevent="dropField(idx)">
                {{-- Field header row --}}
                <div class="flex items-center gap-2 px-3 py-2">
                    <button type="button"
                            draggable="true"
                            @dragstart="dragIndex = idx; $event.dataTransfer.effectAllowed = 'move'"
                            @dragend="dragIndex = null; dragOverIndex = null"
                            title="Sürükleyerek sırala — alan sırası İçerik tabındaki form sırasıdır"
                            class="cursor-grab active:cursor-grabbing text-gray-300 hover:text-indigo-500">
                        <i class="fas fa-grip-vertical text-xs"></i>
                    </button>

                    {{-- Key --}}
                    <input type="text" placeholder="alan_adi"
                           x-model="field.key"
                           @blur="field.key = normalizeKey(field.key)"
                           class="w-32 rounded border border-gray-300 px-2 py-1 font-mono text-xs focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400">

                    {{-- Type --}}
                    <select x-model="field.type"
                            class="rounded border border-gray-300 px-2 py-1 text-xs focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400">
                        <option value="text">text</option>
                        <option value="textarea">textarea</option>
                        <option value="url">url</option>
                        <option value="page_link">page_link (iç sayfa)</option>
                        <option value="email">email</option>
                        <option value="number">number</option>
                        <option value="boolean">boolean</option>
                        <option value="enum">enum</option>
                        <option value="icon">icon (FA seçici)</option>
                        <option value="image">image</option>
                        <option value="media_id">media_id</option>
                        <option value="color">color</option>
                        <option value="repeater">repeater</option>
                    </select>

                    {{-- HTML kullanım göstergesi --}}
                    <span x-show="field.key && fieldUsedInHtml(field)"
                          title="Bu alan HTML template'te kullanılıyor"
                          class="inline-flex shrink-0 items-center gap-1 rounded-full bg-green-50 px-1.5 py-0.5 text-[10px] font-medium text-green-700">
                        <i class="fas fa-check text-[9px]"></i> HTML
                    </span>
                    <span x-show="field.key && !fieldUsedInHtml(field)"
                          title="Bu alan HTML template'te bulunamadı — placeholder eksik olabilir"
                          class="inline-flex shrink-0 items-center gap-1 rounded-full bg-amber-50 px-1.5 py-0.5 text-[10px] font-medium text-amber-700">
                        <i class="fas fa-triangle-exclamation text-[9px]"></i> HTML'de yok
                    </span>

                    {{-- Label --}}
                    <input type="text" placeholder="Etiket"
                           x-model="field.label"
                           class="min-w-0 flex-1 rounded border border-gray-300 px-2 py-1 text-xs focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400">

                    {{-- Required toggle --}}
                    <label class="flex cursor-pointer items-center gap-1 text-xs text-gray-600">
                        <input type="checkbox" x-model="field.required" class="h-3 w-3 rounded border-gray-300 text-indigo-600">
                        Zor.
                    </label>

                    {{-- Expand toggle --}}
                    <button type="button" @click="field._open = !field._open"
                            class="text-gray-400 hover:text-gray-600">
                        <i class="fas text-xs" :class="field._open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>

                    {{-- Duplicate --}}
                    <button type="button" @click="duplicateField(idx)"
                            title="Alanı çoğalt (repeater alt alanları dahil)"
                            class="text-gray-400 hover:text-indigo-600">
                        <i class="fas fa-copy text-xs"></i>
                    </button>

                    {{-- Delete --}}
                    <button type="button" @click="removeField(idx)"
                            class="text-red-400 hover:text-red-600">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>

                {{-- Expanded options --}}
                <div x-show="field._open" x-collapse class="border-t border-gray-200 px-3 py-3">
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium text-gray-600">Max uzunluk</label>
                            <input type="number" x-model.number="field.max" placeholder="—"
                                   class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-medium text-gray-600">Min uzunluk</label>
                            <input type="number" x-model.number="field.min" placeholder="—"
                                   class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400">
                        </div>
                        <div class="col-span-2" x-show="field.type === 'enum'">
                            <label class="mb-1 block text-[11px] font-medium text-gray-600">Seçenekler (virgülle)</label>
                            <input type="text" placeholder="kırmızı,yeşil,mavi"
                                   :value="(field.options||[]).join(',')"
                                   @input="field.options = $event.target.value.split(',').map(v=>v.trim()).filter(Boolean)"
                                   class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400">
                        </div>
                        <div class="col-span-2">
                            <label class="mb-1 block text-[11px] font-medium text-gray-600">Yardım metni</label>
                            <input type="text" x-model="field.help" placeholder="Alan açıklaması"
                                   class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400">
                        </div>
                        <div x-show="field.type !== 'boolean' && field.type !== 'repeater'">
                            <label class="mb-1 block text-[11px] font-medium text-gray-600">Varsayılan</label>
                            <input type="text" x-model="field.default" placeholder="—"
                                   class="w-full rounded border border-gray-300 px-2 py-1 text-xs focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400">
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Add field + generate defaults --}}
        <div class="flex items-center gap-2 pt-1">
            <button type="button" @click="addField()"
                    class="inline-flex items-center gap-2 rounded-lg border border-dashed border-indigo-300 px-4 py-2 text-xs font-medium text-indigo-600 hover:border-indigo-400 hover:bg-indigo-50">
                <i class="fas fa-plus"></i> Alan Ekle
            </button>
            <button type="button" id="generate_defaults_btn"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-200">
                <i class="fas fa-magic"></i> Default Content Üret
            </button>
            <button type="button" @click="removeUnusedFields()"
                    x-show="unusedFields().length > 0"
                    x-cloak
                    class="inline-flex items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-xs font-medium text-amber-700 hover:bg-amber-100">
                <i class="fas fa-broom"></i>
                <span x-text="'HTML\'de olmayan ' + unusedFields().length + ' alanı temizle'"></span>
            </button>
        </div>

        <template x-if="fields.length === 0">
            <p class="rounded-lg border border-dashed border-gray-200 py-6 text-center text-xs text-gray-400">
                Henüz alan eklenmedi. "Alan Ekle" ile başla veya JSON moduna geç.
            </p>
        </template>
    </div>

    {{-- JSON MOD --}}
    <div x-show="mode==='json'" class="mt-4">
        <p class="mb-2 text-xs text-gray-500">JSON'ı doğrudan düzenleyebilirsin. Görsel moda döndüğünde otomatik parse edilir.</p>
        <textarea id="schema_json_raw_editor" rows="14" spellcheck="false"
                  x-model="rawJson"
                  @blur="syncFromRaw()"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-transparent focus:ring-2 focus:ring-indigo-500"></textarea>
        <div x-show="rawError" class="mt-1 text-xs text-red-600" x-text="rawError"></div>
    </div>
</div>

{{-- Default Content JSON --}}
<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-base font-semibold text-gray-900">Default Content JSON</h3>
        <button type="button" id="generate_defaults_btn2"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-200">
            <i class="fas fa-magic"></i> Schema'dan Üret
        </button>
    </div>
    <textarea id="default_content_json_input" name="default_content_json" rows="10" class="mt-3 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-xs focus:border-transparent focus:ring-2 focus:ring-indigo-500">{{ $defaultContentValue }}</textarea>
    <p class="mt-1 text-xs text-gray-500">Yeni block eklendiğinde ilk doldurulacak varsayılan değerler.</p>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('schemaBuilder', (initialSchema) => ({
        mode: 'visual',
        fields: [],
        rawJson: '',
        rawError: '',
        _uid: 0,
        dragIndex: null,
        dragOverIndex: null,
        htmlPlaceholders: [],

        init() {
            this.loadFromObject(initialSchema || {});
            this.$watch('fields', () => { this.rawJson = this.serialized; }, { deep: true });

            // HTML template'teki placeholder'ları takip et: alan-bazlı
            // "HTML'de var/yok" göstergesi için. HTML editörü değişince
            // 'section-template-editor-change' yayar (script.blade.php).
            this.refreshHtmlPlaceholders();
            window.addEventListener('section-template-editor-change', () => this.refreshHtmlPlaceholders());
            // İlk yüklemede CodeMirror geç initialize olabilir — kısa gecikmeyle tekrar oku
            setTimeout(() => this.refreshHtmlPlaceholders(), 600);
        },

        refreshHtmlPlaceholders() {
            this.htmlPlaceholders = (window.getHtmlPlaceholders && window.getHtmlPlaceholders()) || [];
        },

        // Alanın anahtarı HTML template'te kullanılıyor mu?
        // Doğrudan eşleşme, repeater (_html / _items_html) veya indeksli
        // (key_1_xxx) kullanımları "kullanılıyor" sayar — yanlış "yok"
        // uyarısı vermemek için geniş tutulur.
        fieldUsedInHtml(field) {
            const key = (field?.key || '').trim();
            if (!key) return true; // boş anahtar henüz tamamlanmamış — uyarma
            const ph = this.htmlPlaceholders;
            if (!ph.length) return true; // HTML henüz okunamadı — uyarma
            if (ph.includes(key)) return true;
            if (ph.includes(key + '_html') || ph.includes(key + '_items_html')) return true;
            return ph.some((p) => p.startsWith(key + '_'));
        },

        unusedFields() {
            return this.fields.filter((f) => f.key && !this.fieldUsedInHtml(f));
        },

        removeUnusedFields() {
            const unused = new Set(this.unusedFields().map((f) => f._uid));
            if (!unused.size) return;
            if (!window.confirm(unused.size + ' kullanılmayan alan silinecek. Devam edilsin mi?')) return;
            this.fields = this.fields.filter((f) => !unused.has(f._uid));
        },

        get serialized() {
            const obj = {};
            this.fields.forEach(f => {
                if (!f.key) return;
                // _extra: görsel modun bilmediği anahtarlar (repeater "fields"
                // alt şeması vb.) kaybolmasın diye aynen geri yazılır.
                const entry = { ...(f._extra || {}), type: f.type, label: f.label || f.key };
                if (f.required) entry.required = true;
                if (f.max != null && f.max !== '') entry.max = Number(f.max);
                if (f.min != null && f.min !== '') entry.min = Number(f.min);
                if (f.type === 'enum' && f.options?.length) entry.options = f.options;
                if (f.help) entry.help = f.help;
                if (f.default !== '' && f.default !== null && f.default !== undefined) entry.default = f.default;
                obj[f.key] = entry;
            });
            return JSON.stringify(obj, null, 2);
        },

        loadFromObject(schema) {
            const known = ['type', 'label', 'required', 'max', 'min', 'options', 'help', 'default'];
            this.fields = Object.entries(schema || {}).map(([key, v]) => ({
                _uid: ++this._uid,
                _open: false,
                key,
                type: v.type || 'text',
                label: v.label || '',
                required: !!v.required,
                max: v.max ?? '',
                min: v.min ?? '',
                options: Array.isArray(v.options) ? v.options : [],
                help: v.help || '',
                default: v.default ?? '',
                _extra: Object.fromEntries(Object.entries(v || {}).filter(([k]) => !known.includes(k))),
            }));
            this.rawJson = this.serialized;
        },

        addField() {
            this.fields.push({
                _uid: ++this._uid,
                _open: false,
                key: '',
                type: 'text',
                label: '',
                required: false,
                max: '',
                min: '',
                options: [],
                help: '',
                default: '',
            });
        },

        removeField(idx) {
            this.fields.splice(idx, 1);
        },

        // Alanı tüm ayarlarıyla (repeater alt alanları dahil) kopyalar;
        // key çakışmasın diye _copy soneki alır.
        duplicateField(idx) {
            const source = this.fields[idx];
            if (!source) return;

            const clone = JSON.parse(JSON.stringify(source));
            clone._uid = ++this._uid;
            if (clone.key) {
                const base = clone.key.replace(/_copy\d*$/, '') + '_copy';
                let key = base, n = 2;
                while (this.fields.some(f => f.key === key)) key = base + n++;
                clone.key = key;
            }
            this.fields.splice(idx + 1, 0, clone);
        },

        // Sürükle-bırak: alanı dragIndex'ten hedef index'e taşı.
        // Alan sırası = İçerik tabındaki form sırası (serialized obje
        // ekleme sırasını korur).
        dropField(targetIdx) {
            const from = this.dragIndex;
            this.dragIndex = null;
            this.dragOverIndex = null;

            if (from === null || from === targetIdx) return;

            const [moved] = this.fields.splice(from, 1);
            this.fields.splice(targetIdx, 0, moved);
        },

        // Alanları HTML'deki GÖRÜNÜM (placeholder geliş) sırasına göre dizer —
        // sayfa akışıyla aynı, en mantıklı sıra. window.getHtmlPlaceholders()
        // doküman sırasını verir. Repeater alanları HTML'de key_html /
        // key_items_html olarak geçtiği için o ekler de eşlenir. HTML'de hiç
        // geçmeyen alanlar sona alınır (kendi aralarında doğal alfanumerik).
        // Sürükle-bırak ile elle sıralama yine mümkün.
        sortFields() {
            const order = (typeof window.getHtmlPlaceholders === 'function')
                ? (window.getHtmlPlaceholders() || []) : [];
            const rank = (key) => {
                const k = String(key || '');
                let i = order.indexOf(k);
                if (i === -1) i = order.indexOf(k + '_html');
                if (i === -1) i = order.indexOf(k + '_items_html');
                return i === -1 ? Number.MAX_SAFE_INTEGER : i;
            };
            this.fields.sort((a, b) => {
                const ra = rank(a.key), rb = rank(b.key);
                if (ra !== rb) return ra - rb;
                // HTML'de olmayanlar / eşit rank → doğal alfanumerik
                return String(a.key || '').localeCompare(String(b.key || ''), undefined, { numeric: true, sensitivity: 'base' });
            });
        },

        normalizeKey(val) {
            return String(val || '').trim().toLowerCase()
                .replace(/[^a-z0-9_]+/g, '_').replace(/^_+|_+$/g, '');
        },

        syncFromRaw() {
            try {
                const parsed = JSON.parse(this.rawJson);
                if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                    this.loadFromObject(parsed);
                    this.rawError = '';
                }
            } catch (e) {
                this.rawError = 'Geçersiz JSON: ' + e.message;
            }
        },
    }));
});
</script>
@endpush
