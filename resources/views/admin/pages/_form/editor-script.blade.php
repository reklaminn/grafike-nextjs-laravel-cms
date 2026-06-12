@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css">
<style>
    /* Quill overrides inside the block settings modal */
    .ql-toolbar { border: none !important; border-bottom: 1px solid #e5e7eb !important; background: #f9fafb; padding: 6px 8px !important; }
    .ql-container { border: none !important; font-family: inherit; font-size: 0.875rem; }
    .ql-editor { min-height: 110px; padding: 10px 12px; }
    .ql-editor.ql-blank::before { color: #9ca3af; font-style: normal; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
@endpush

@push('scripts')
<script>
/**
 * blockFieldInput(parentRef, fieldKey, fieldSchema)
 *
 * Reusable Alpine sub-component for rendering a single schema field.
 * Intended to be used as:
 *   <div x-data="blockFieldInput(parentRef, fieldKey, fieldSchema)">
 *       @@include('admin.pages._form._field-types')
 *   </div>
 *
 * parentRef  — the reactive object that owns the value (e.g. block.content or repeater item)
 * fieldKey   — the key inside parentRef to read/write
 * fieldSchema — the field's schema object ({ type, label, options, ... })
 */
function blockFieldInput(parentRef, fieldKey, fieldSchema) {
    return {
        parentRef,
        fieldKey,
        fieldSchema,

        // Media picker state
        mediaPickerOpen: false,
        mediaItems: [],
        mediaSearch: '',
        mediaLoading: false,

        // Quill
        quillInstance: null,

        get type() {
            return (this.fieldSchema?.type || 'text');
        },

        // ── Media picker ────────────────────────────────────────────────────
        openMediaPicker() {
            this.mediaPickerOpen = true;
            this.mediaSearch = '';
            if (!this.mediaItems.length) {
                this.loadMedia();
            }
        },

        closeMediaPicker() {
            this.mediaPickerOpen = false;
        },

        async loadMedia() {
            this.mediaLoading = true;
            try {
                const resp = await fetch('/admin/media?type=image&per_page=96', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });
                if (resp.ok) {
                    const json = await resp.json();
                    this.mediaItems = json.data || [];
                }
            } catch (e) {
                console.error('[blockFieldInput] Media load error:', e);
            } finally {
                this.mediaLoading = false;
            }
        },

        filteredMedia() {
            if (!this.mediaSearch) return this.mediaItems;
            const q = this.mediaSearch.toLowerCase();
            return this.mediaItems.filter((m) =>
                (m.file_name || m.name || '').toLowerCase().includes(q)
            );
        },

        selectMedia(item) {
            this.parentRef[this.fieldKey] = item.url || item.original_url || '';
            this.closeMediaPicker();
        },

        // ── Quill rich-text ─────────────────────────────────────────────────
        initQuill(el) {
            if (!el || this.quillInstance) return;
            if (typeof Quill === 'undefined') {
                console.warn('[blockFieldInput] Quill not loaded');
                return;
            }

            this.quillInstance = new Quill(el, {
                theme: 'snow',
                placeholder: 'İçerik girin…',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ header: [2, 3, false] }],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link', 'clean'],
                    ],
                },
            });

            // Set initial HTML
            const initial = this.parentRef[this.fieldKey];
            if (initial) {
                this.quillInstance.root.innerHTML = initial;
            }

            // Sync changes back to data
            const pk = this.fieldKey;
            const pr = this.parentRef;
            this.quillInstance.on('text-change', () => {
                pr[pk] = this.quillInstance.root.innerHTML;
            });
        },
    };
}

function frontendSectionEditor({ initialRegions = null, availableTemplates = [], fieldErrors = {} }) {
    return {
        regions: { header: [], body: [], footer: [] },
        regionNames: ['header', 'body', 'footer'],
        availableTemplates,
        fieldErrors,
        pickerModalOpen: false,
        pickerSearch: '',
        pickerTarget: null,
        openBlockMenuFor: null,
        openFrontendJson: false,
        settingsModalOpen: false,
        settingsTarget: null,
        settingsTab: 'content',
        settingsDraft: null,
        columnSettingsModalOpen: false,
        columnSettingsTarget: null,
        columnSettingsTab: 'layout',
        columnSettingsDraft: null,
        rowSettingsModalOpen: false,
        rowSettingsTarget: null,
        rowSettingsTab: 'layout',
        rowSettingsDraft: null,
        initialSerializedRegions: null,

        // Sürükle-bırak state'i: dragBlock/dragRow kaynak konumu tutar,
        // *Over anahtarları hedef vurgusu için kullanılır.
        dragBlock: null,
        dragBlockOver: null,
        dragRow: null,
        dragRowOver: null,

        // AI block-edit state (FAZ 3.6 streaming)
        aiAction: 'shorten',
        aiCustomPrompt: '',
        aiLoading: false,
        aiStreaming: false,   // true while SSE stream is open
        aiStreamText: '',     // accumulated raw text (typewriter preview)
        aiAbortController: null,
        aiStatus: '',
        aiStatusOk: false,

        // Şablon kataloğu canlı senkronizasyon state'i
        templateSyncToast: '',
        templateSyncToastVisible: false,
        catalogRefreshing: false,

        // Form submit (kaydet) sırasında beforeunload uyarısını bastır
        suppressUnloadWarning: false,

        init() {
            this.regions = this.normalizeRegions(initialRegions);
            this.normalizeSortOrder();
            this.initialSerializedRegions = this.serializedRegions;

            // Listen for AI translation results — when the translate-content
            // endpoint returns a fully-translated sections_json (FAZ 4.6),
            // it dispatches an "ai-translate-sections" event with the new
            // structure; we hot-swap the editor's regions so the translated
            // blocks render immediately.
            window.addEventListener('ai-translate-sections', (event) => {
                const incoming = event?.detail?.sections_json;
                if (!incoming || typeof incoming !== 'object') return;
                this.regions = this.normalizeRegions(incoming);
                this.normalizeSortOrder();
                this.syncSerializedRegions();
            });

            // Kaydedilmemiş bölüm değişikliği varken sekme kapanır/sayfa
            // değişirse tarayıcı onayı iste — kaydet ile ayrılırken sessiz.
            window.addEventListener('beforeunload', (event) => {
                if (this.suppressUnloadWarning || !this.sectionsJsonIsDirty()) return;
                event.preventDefault();
                event.returnValue = '';
            });

            // Başka bir sekmede şablon kaydedildiğinde (edit.blade.php
            // localStorage sinyali yazar) kataloğu ve blokları tazele.
            // storage event'i yalnızca diğer sekmelerde tetiklenir.
            window.addEventListener('storage', (event) => {
                if (event.key !== 'grafike:section-template-updated' || !event.newValue) return;
                let info = null;
                try { info = JSON.parse(event.newValue); } catch (e) { /* bozuk sinyal — adsız yenile */ }
                this.refreshTemplateCatalog(info?.name || null);
            });

            this.$nextTick(() => {
                this.syncSerializedRegions();

                const form = this.$root.closest('form');
                if (!form || form.dataset.frontendSectionsSyncBound === '1') {
                    return;
                }

                form.dataset.frontendSectionsSyncBound = '1';
                form.addEventListener('submit', () => {
                    this.suppressUnloadWarning = true;
                    this.syncSerializedRegions();
                }, { capture: true });
                form.addEventListener('formdata', (event) => {
                    event.formData.set('sections_json', this.syncSerializedRegions());
                    event.formData.set('sections_json_dirty', this.sectionsJsonIsDirty() ? '1' : '0');
                });

                this.$watch('regions', () => {
                    this.syncSerializedRegions();
                });
            });
        },

        get serializedRegions() {
            return JSON.stringify({
                version: 2,
                regions: this.serializeRegions(),
            }, null, 2);
        },

        syncSerializedRegions() {
            const value = this.serializedRegions;

            if (this.$refs?.sectionsJsonInput) {
                this.$refs.sectionsJsonInput.value = value;
            }

            if (this.$refs?.sectionsJsonDirtyInput) {
                this.$refs.sectionsJsonDirtyInput.value = this.sectionsJsonIsDirty() ? '1' : '0';
            }

            return value;
        },

        queueSerializedRegionsSync() {
            if (typeof this.$nextTick === 'function') {
                this.$nextTick(() => this.syncSerializedRegions());
                return;
            }

            this.syncSerializedRegions();
        },

        sectionsJsonIsDirty() {
            return this.initialSerializedRegions !== null
                && this.serializedRegions !== this.initialSerializedRegions;
        },

        // ── Şablon kataloğu canlı senkronizasyonu ────────────────────────
        //
        // catalog-json endpoint'inden güncel şablonları çeker, bu şablonları
        // kullanan blokların schema/html_template gibi şablon-türevi
        // alanlarını yeniler. Kullanıcının girdiği content KORUNUR — yalnızca
        // yeni schema alanları için default değerler eklenir.
        async refreshTemplateCatalog(updatedName = null) {
            if (this.catalogRefreshing) return false;
            this.catalogRefreshing = true;

            try {
                const response = await fetch(@js(route('admin.section-templates.catalog-json', [], false)), {
                    headers: { 'Accept': 'application/json' },
                });
                if (!response.ok) return false;

                const data = await response.json();
                if (!Array.isArray(data.templates)) return false;

                this.availableTemplates = data.templates;
                this.rehydrateBlocksFromCatalog();
                this.showTemplateSyncToast(updatedName
                    ? `"${updatedName}" şablonu güncellendi — bloklar yenilendi`
                    : 'Şablon kataloğu yenilendi');
                return true;
            } catch (e) {
                return false;
            } finally {
                this.catalogRefreshing = false;
            }
        },

        rehydrateBlocksFromCatalog() {
            this.regionNames.forEach((region) => {
                (this.regions[region] || []).forEach((row) => {
                    (row.columns || []).forEach((column) => {
                        (column.blocks || []).forEach((block) => {
                            this.applyTemplateToBlock(block);
                        });
                    });
                });
            });

            // Ayarlar modalı açıksa draft da tazelensin — yeni alanlar anında görünür
            if (this.settingsDraft) {
                this.applyTemplateToBlock(this.settingsDraft);
            }

            this.queueSerializedRegionsSync();
        },

        applyTemplateToBlock(block) {
            if (!block?.section_template_id) return;
            const template = this.getTemplateById(block.section_template_id);
            if (!template) return;

            block.type          = template.type || block.type;
            block.variation     = template.variation || block.variation;
            block.render_mode   = template.render_mode || block.render_mode;
            block.component_key = template.component_key ?? block.component_key;
            block.template_name = template.name || block.template_name;
            block.schema        = template.schema || {};
            block.html_template = template.html_template || null;
            block.content       = {
                ...(template.default_content || {}),
                ...(block.content || {}),
            };
        },

        // Blok schema'sı katalogdaki güncel şablondan farklı mı?
        // (modal açıkken başka tarayıcıdan şablon değiştirilmiş olabilir)
        blockSchemaIsStale(block) {
            if (!block?.section_template_id) return false;
            const template = this.getTemplateById(block.section_template_id);
            if (!template) return false;
            return JSON.stringify(block.schema || {}) !== JSON.stringify(template.schema || {});
        },

        showTemplateSyncToast(message) {
            this.templateSyncToast = message;
            this.templateSyncToastVisible = true;
            clearTimeout(this._templateSyncToastTimer);
            this._templateSyncToastTimer = setTimeout(() => {
                this.templateSyncToastVisible = false;
            }, 4000);
        },

        getTemplateById(templateId) {
            return this.availableTemplates.find((template) => String(template.id) === String(templateId));
        },

        getTemplateByType(type) {
            return this.availableTemplates.find((template) => template.type === type);
        },

        regionLabel(region) {
            return {
                header: 'Header',
                body: 'Body',
                footer: 'Footer',
            }[region] || region;
        },

        regionShellClass(region) {
            return {
                header: 'border-blue-200 bg-blue-50/40',
                body: 'border-green-200 bg-green-50/40',
                footer: 'border-purple-200 bg-purple-50/40',
            }[region] || 'border-gray-200 bg-gray-50/40';
        },

        regionBadgeClass(region) {
            return {
                header: 'bg-blue-200 text-blue-800',
                body: 'bg-green-200 text-green-800',
                footer: 'bg-purple-200 text-purple-800',
            }[region] || 'bg-gray-200 text-gray-800';
        },

        regionButtonClass(region) {
            return {
                header: 'bg-blue-50 text-blue-700 hover:bg-blue-100',
                body: 'bg-green-50 text-green-700 hover:bg-green-100',
                footer: 'bg-purple-50 text-purple-700 hover:bg-purple-100',
            }[region] || 'bg-gray-50 text-gray-700 hover:bg-gray-100';
        },

        rowShellClass(region) {
            return {
                header: 'border-blue-200',
                body: 'border-green-200',
                footer: 'border-purple-200',
            }[region] || 'border-gray-200';
        },

        columnClassLabel(column) {
            const width = column?.width;
            if (width === '' || width === null || width === undefined) {
                return 'Yok';
            }
            return `col-${Math.min(12, Math.max(1, Number(width)))}`;
        },

        editorColumnCanvasStyle(column) {
            const rawWidth = column?.width;
            if (rawWidth === '' || rawWidth === null || rawWidth === undefined) {
                return {
                    gridColumn: 'span 12 / span 12',
                };
            }

            const bounded = Math.min(12, Math.max(1, Number(rawWidth)));

            return {
                gridColumn: `span ${bounded} / span ${bounded}`,
            };
        },

        columnLayoutSummary(column) {
            if (!column) return 'Yok';

            const parts = [];
            const width = column?.width;
            if (width !== '' && width !== null && width !== undefined) {
                parts.push(`col-${Math.min(12, Math.max(1, Number(width)))}`);
            }

            ['sm', 'md', 'lg', 'xl'].forEach((breakpoint) => {
                const value = column?.responsive?.[breakpoint];
                if (value !== '' && value !== null && value !== undefined) {
                    parts.push(`col-${breakpoint}-${value}`);
                }
            });

            return parts.length ? parts.join(' ') : 'Yok';
        },

        canMoveColumn(row, columnIndex, direction) {
            const columns = row?.columns || [];
            const targetIndex = columnIndex + direction;
            return targetIndex >= 0 && targetIndex < columns.length;
        },

        normalizeRegions(initialRegions) {
            const output = {
                header: [],
                body: [],
                footer: [],
            };

            const sourceRegions = initialRegions?.regions || output;

            Object.keys(output).forEach((region) => {
                output[region] = (sourceRegions[region] || []).map((row, rowIndex) => ({
                    _uid: row._uid || this.generateUid('row'),
                    id: row.id || `row_${region}_${rowIndex + 1}`,
                    type: 'row',
                    is_active: row.is_active !== false,
                    _expanded: row._expanded !== false,
                    container: row.container || '',
                    wrapper_tag: row.wrapper_tag || '',
                    css_class: row.css_class || '',
                    element_id: row.element_id || '',
                    inline_style: row.inline_style || '',
                    custom_attributes: row.custom_attributes || '',
                    columns: (row.columns || []).map((column, columnIndex) => ({
                        _uid: column._uid || this.generateUid('column'),
                        id: column.id || `col_${region}_${rowIndex + 1}_${columnIndex + 1}`,
                        width: column.width ?? column?.responsive?.xs ?? null,
                        is_active: column.is_active !== false,
                        responsive: {
                            xs: column.width ?? column?.responsive?.xs ?? '',
                            sm: column?.responsive?.sm ?? '',
                            md: column?.responsive?.md ?? '',
                            lg: column?.responsive?.lg ?? '',
                            xl: column?.responsive?.xl ?? '',
                        },
                        css_class: column.css_class || '',
                        element_id: column.element_id || '',
                        inline_style: column.inline_style || '',
                        custom_attributes: column.custom_attributes || '',
                        blocks: (column.blocks || []).map((block, blockIndex) => this.hydrateBlock(block, region, rowIndex, columnIndex, blockIndex)),
                    })),
                }));
            });

            return output;
        },

        hydrateBlock(block, region, rowIndex, columnIndex, blockIndex) {
            const template = this.getTemplateById(block.section_template_id);
            const hasSchema = block.schema && typeof block.schema === 'object' && Object.keys(block.schema).length > 0;

            return {
                _uid: block._uid || this.generateUid('block'),
                id: block.id || `block_${block.type || 'item'}_${rowIndex + 1}_${columnIndex + 1}_${blockIndex + 1}`,
                type: template?.type || block.type || '',
                variation: template?.variation || block.variation || 'default',
                render_mode: template?.render_mode || block.render_mode || 'html',
                section_template_id: block.section_template_id || template?.id || null,
                template_name: template?.name || block.template_name || '',
                component_key: template?.component_key || block.component_key || null,
                schema: template?.schema || (hasSchema ? block.schema : {}),
                content: JSON.parse(JSON.stringify({
                    ...(template?.default_content || {}),
                    ...(block.content || {}),
                })),
                is_active: block.is_active !== false,
                sort_order: block.sort_order || (blockIndex + 1),
                wrapper_tag: block.wrapper_tag || '',
                css_class: block.css_class || '',
                element_id: block.element_id || '',
                inline_style: block.inline_style || '',
                custom_attributes: block.custom_attributes || '',
                html_template: template?.html_template || block.html_template || null,
                html_override: block.html_override || '',
            };
        },

        generateUid(prefix) {
            return `${prefix}_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
        },

        createRow(region, rowIndex) {
            return {
                _uid: this.generateUid('row'),
                id: `row_${region}_${rowIndex + 1}`,
                type: 'row',
                is_active: true,
                _expanded: true,
                container: '',
                wrapper_tag: '',
                css_class: '',
                element_id: '',
                inline_style: '',
                custom_attributes: '',
                columns: [],
            };
        },

        toggleRowExpand(region, rowIndex) {
            const row = this.regions[region][rowIndex];
            row._expanded = row._expanded === false ? true : false;
        },

        blockSummary(block) {
            const content = block.content || {};
            const summaryKey = ['title', 'subtitle', 'description', 'eyebrow', 'button_text']
                .find((key) => typeof content[key] === 'string' && String(content[key]).trim() !== '');

            if (!summaryKey) {
                return 'Hazır alanlar bu kartın içinde düzenlenir.';
            }

            return String(content[summaryKey]);
        },

        fieldLabel(fieldName, fieldSchema = {}) {
            return fieldSchema.label || fieldSchema.name || fieldName;
        },

        repeaterFieldSchema(fieldSchema = {}) {
            return fieldSchema.fields || fieldSchema.item_schema || {};
        },

        schemaDefaultValue(fieldSchema = {}) {
            if (Object.prototype.hasOwnProperty.call(fieldSchema, 'default')) {
                return JSON.parse(JSON.stringify(fieldSchema.default));
            }

            const type = fieldSchema.type || 'text';

            if (type === 'boolean') return false;
            if (type === 'number') return 0;
            if (type === 'repeater') return [];

            return '';
        },

        createRepeaterItem(fieldSchema = {}) {
            const item = { _uid: this.generateUid('item') };
            Object.entries(this.repeaterFieldSchema(fieldSchema)).forEach(([fieldName, childSchema]) => {
                item[fieldName] = this.schemaDefaultValue(childSchema);
            });

            return item;
        },

        normalizeRepeaterItems(block) {
            if (!block?.schema || !block?.content) return;

            Object.entries(block.schema).forEach(([fieldName, fieldSchema]) => {
                if ((fieldSchema?.type || 'text') !== 'repeater') return;

                if (!Array.isArray(block.content[fieldName])) {
                    block.content[fieldName] = [];
                }

                block.content[fieldName] = block.content[fieldName].map((item) => {
                    const normalized = item && typeof item === 'object' && !Array.isArray(item)
                        ? { ...item }
                        : {};

                    normalized._uid = normalized._uid || this.generateUid('item');

                    Object.entries(this.repeaterFieldSchema(fieldSchema)).forEach(([childName, childSchema]) => {
                        if (!Object.prototype.hasOwnProperty.call(normalized, childName)) {
                            normalized[childName] = this.schemaDefaultValue(childSchema);
                        }
                    });

                    return normalized;
                });
            });
        },

        ensureRepeaterContent(block, fieldName) {
            if (!block?.content) return;
            if (!Array.isArray(block.content[fieldName])) {
                block.content[fieldName] = [];
            }
        },

        addRepeaterItem(block, fieldName, fieldSchema) {
            this.ensureRepeaterContent(block, fieldName);
            block.content[fieldName].push(this.createRepeaterItem(fieldSchema));
        },

        removeRepeaterItem(block, fieldName, itemIndex) {
            this.ensureRepeaterContent(block, fieldName);
            block.content[fieldName].splice(itemIndex, 1);
        },

        duplicateRepeaterItem(block, fieldName, itemIndex) {
            this.ensureRepeaterContent(block, fieldName);
            const source = block.content[fieldName][itemIndex];
            if (!source) return;

            const clone = JSON.parse(JSON.stringify(source));
            clone._uid = this.generateUid('item');
            block.content[fieldName].splice(itemIndex + 1, 0, clone);
        },

        moveRepeaterItem(block, fieldName, itemIndex, direction) {
            this.ensureRepeaterContent(block, fieldName);
            const items = block.content[fieldName];
            const targetIndex = itemIndex + direction;
            if (targetIndex < 0 || targetIndex >= items.length) return;

            [items[itemIndex], items[targetIndex]] = [items[targetIndex], items[itemIndex]];
        },

        createColumn(region, rowIndex, columnIndex) {
            return {
                _uid: this.generateUid('column'),
                id: `col_${region}_${rowIndex + 1}_${columnIndex + 1}`,
                width: null,
                is_active: true,
                responsive: { xs: '', sm: '', md: '', lg: '', xl: '' },
                css_class: '',
                element_id: '',
                inline_style: '',
                custom_attributes: '',
                blocks: [],
            };
        },

        normalizeResponsive(column) {
            column.responsive = column.responsive || { xs: '', sm: '', md: '', lg: '', xl: '' };
            if (column.width !== '' && column.width !== null && column.width !== undefined) {
                column.width = Math.min(12, Math.max(1, Number(column.width)));
                column.responsive.xs = column.width;
            } else {
                column.width = null;
                column.responsive.xs = '';
            }
            ['sm', 'md', 'lg', 'xl'].forEach((key) => {
                if (column.responsive[key] === null || column.responsive[key] === undefined) {
                    column.responsive[key] = '';
                }
            });
        },

        getFilteredTemplates(search) {
            const query = String(search || '').trim().toLowerCase();

            if (!query) {
                return this.availableTemplates;
            }

            return this.availableTemplates.filter((template) =>
                [template.name, template.type, template.variation]
                    .filter(Boolean)
                    .some((value) => String(value).toLowerCase().includes(query))
            );
        },

        groupedTemplates(search) {
            const TYPE_LABELS = {
                'header': 'Header', 'footer': 'Footer', 'hero': 'Hero / Banner',
                'hero-banner': 'Hero Banner', 'slider': 'Slider', 'rich-text': 'Rich Text',
                'content-block': 'İçerik Bloğu', 'article-list': 'Yazı Liste',
                'features': 'Özellik Alanı', 'cta': 'CTA', 'gallery': 'Galeri',
                'testimonials': 'Testimonials', 'cards': 'Kart Grubu',
                'spacer': 'Boşluk', 'video-embed': 'Video', 'page-header': 'Sayfa Başlığı',
                'menu': 'Menü',
            };
            const filtered = this.getFilteredTemplates(search);
            const groups = {};
            filtered.forEach(t => {
                const key = t.type || 'other';
                if (!groups[key]) groups[key] = { type: key, label: TYPE_LABELS[key] || key, templates: [] };
                groups[key].templates.push(t);
            });
            return Object.values(groups);
        },

        getRegionPresets(region) {
            const presets = [];

            if (region === 'body') {
                if (this.getTemplateByType('hero')) presets.push({ key: 'hero', label: 'Hero' });
                if (this.getTemplateByType('rich-text')) {
                    presets.push({ key: 'rich-text', label: 'Tanıtım Metni' });
                    presets.push({ key: 'two-column-content', label: '2 Kolon İçerik' });
                }
                if (this.getTemplateByType('features')) presets.push({ key: 'features', label: 'Özellik Alanı' });
                if (this.getTemplateByType('article-list')) presets.push({ key: 'article-list', label: 'Yazı Liste' });
            }

            if (region === 'header' && this.getTemplateByType('rich-text')) {
                presets.push({ key: 'header-basic', label: 'Basit Header' });
            }

            if (region === 'footer' && this.getTemplateByType('rich-text')) {
                presets.push({ key: 'footer-basic', label: 'Basit Footer' });
            }

            return presets;
        },

        addRow(region) {
            this.regions[region].push(this.createRow(region, this.regions[region].length));
            this.normalizeSortOrder();
        },

        createBlockFromTemplate(template, region, rowIndex, columnIndex, blockIndex, overrides = {}) {
            const isShellBlock = ['header', 'footer'].includes(String(template?.type || '').toLowerCase());

            return this.hydrateBlock({
                id: `${template.type}_${blockIndex + 1}`,
                type: template.type,
                variation: template.variation,
                render_mode: template.render_mode,
                section_template_id: template.id,
                template_name: template.name,
                component_key: template.component_key || null,
                schema: template.schema || {},
                content: {
                    ...(template.default_content || {}),
                    ...(overrides.content || {}),
                },
                is_active: true,
                wrapper_tag: isShellBlock ? '' : (overrides.wrapper_tag ?? ''),
                ...overrides,
            }, region, rowIndex, columnIndex, blockIndex);
        },

        applyPreset(region, presetKey) {
            const rows = this.buildPresetRows(region, presetKey, this.regions[region].length);
            if (!rows.length) return;
            this.regions[region].push(...rows);
            this.normalizeSortOrder();
        },

        buildPresetRows(region, presetKey, startIndex) {
            const richText = this.getTemplateByType('rich-text');
            const hero = this.getTemplateByType('hero');
            const features = this.getTemplateByType('features');
            const articleList = this.getTemplateByType('article-list');

            const createRowWithColumns = (widths, blockFactory) => {
                const rowIndex = startIndex;

                return {
                    _uid: this.generateUid('row'),
                    id: `row_${region}_${rowIndex + 1}`,
                    type: 'row',
                    is_active: true,
                    _expanded: true,
                    columns: widths.map((width, columnIndex) => ({
                        ...this.createColumn(region, rowIndex, columnIndex),
                        width,
                        responsive: { xs: width, sm: '', md: '', lg: '', xl: '' },
                        blocks: blockFactory(columnIndex),
                    })),
                };
            };

            switch (presetKey) {
                case 'hero':
                    if (!hero) return [];
                    return [createRowWithColumns([12], () => [
                        this.createBlockFromTemplate(hero, region, startIndex, 0, 0),
                    ])];

                case 'rich-text':
                    if (!richText) return [];
                    return [createRowWithColumns([12], () => [
                        this.createBlockFromTemplate(richText, region, startIndex, 0, 0),
                    ])];

                case 'features':
                    if (!features) return [];
                    return [createRowWithColumns([12], () => [
                        this.createBlockFromTemplate(features, region, startIndex, 0, 0),
                    ])];

                case 'article-list':
                    if (!articleList) return [];
                    return [createRowWithColumns([12], () => [
                        this.createBlockFromTemplate(articleList, region, startIndex, 0, 0),
                    ])];

                case 'two-column-content':
                    if (!richText) return [];
                    return [createRowWithColumns([6, 6], (columnIndex) => [
                        this.createBlockFromTemplate(richText, region, startIndex, columnIndex, 0, {
                            content: {
                                title: columnIndex === 0 ? 'Sol içerik' : 'Sağ içerik',
                            },
                        }),
                    ])];

                case 'header-basic':
                    if (!richText) return [];
                    return [createRowWithColumns([12], () => [
                        this.createBlockFromTemplate(richText, region, startIndex, 0, 0, {
                            content: { title: 'Header alanı' },
                        }),
                    ])];

                case 'footer-basic':
                    if (!richText) return [];
                    return [createRowWithColumns([12], () => [
                        this.createBlockFromTemplate(richText, region, startIndex, 0, 0, {
                            content: { title: 'Footer alanı' },
                        }),
                    ])];

                default:
                    return [];
            }
        },

        removeRow(region, rowIndex) {
            this.regions[region].splice(rowIndex, 1);
            this.normalizeSortOrder();
        },

        moveRow(region, rowIndex, direction) {
            const targetIndex = rowIndex + direction;
            if (targetIndex < 0 || targetIndex >= this.regions[region].length) return;
            [this.regions[region][rowIndex], this.regions[region][targetIndex]] = [this.regions[region][targetIndex], this.regions[region][rowIndex]];
            this.normalizeSortOrder();
        },

        // ── Satır sürükle-bırak ──────────────────────────────────────────
        startRowDrag(event, region, rowIndex) {
            this.dragRow = { region, rowIndex };
            event.dataTransfer.effectAllowed = 'move';
            // Firefox sürüklemeyi başlatmak için data ister
            event.dataTransfer.setData('text/plain', 'row');
            const card = event.target.closest('[data-row-card]');
            if (card && event.dataTransfer.setDragImage) {
                event.dataTransfer.setDragImage(card, 24, 24);
            }
        },

        endRowDrag() {
            this.dragRow = null;
            this.dragRowOver = null;
        },

        rowDropKey(region, rowIndex) {
            return region + ':' + (rowIndex === null ? 'end' : rowIndex);
        },

        rowDragOver(event, region, rowIndex) {
            if (!this.dragRow) return;
            event.preventDefault();
            this.dragRowOver = this.rowDropKey(region, rowIndex);
        },

        dropRow(region, rowIndex = null) {
            const src = this.dragRow;
            this.endRowDrag();
            if (!src) return;

            const srcRows = this.regions[src.region];
            if (!Array.isArray(srcRows)) return;
            if (!Array.isArray(this.regions[region])) this.regions[region] = [];
            const dstRows = this.regions[region];

            let target = rowIndex === null ? dstRows.length : rowIndex;
            if (srcRows === dstRows && target === src.rowIndex) return;

            const [moved] = srcRows.splice(src.rowIndex, 1);
            if (!moved) return;
            dstRows.splice(Math.min(target, dstRows.length), 0, moved);
            this.normalizeSortOrder();
        },

        addColumn(region, rowIndex) {
            const row = this.regions[region][rowIndex];
            row.columns.push(this.createColumn(region, rowIndex, row.columns.length));
            this.normalizeSortOrder();
        },

        applyColumnPreset(region, rowIndex, widths) {
            const row = this.regions[region][rowIndex];
            row.columns = widths.map((width, columnIndex) => ({
                ...this.createColumn(region, rowIndex, columnIndex),
                width,
                responsive: { xs: width, sm: '', md: '', lg: '', xl: '' },
            }));
            this.normalizeSortOrder();
        },

        removeColumn(region, rowIndex, columnIndex) {
            this.regions[region][rowIndex].columns.splice(columnIndex, 1);
            this.normalizeSortOrder();
        },

        moveColumn(region, rowIndex, columnIndex, direction) {
            const columns = this.regions[region][rowIndex].columns;
            const targetIndex = columnIndex + direction;
            if (targetIndex < 0 || targetIndex >= columns.length) return;
            [columns[columnIndex], columns[targetIndex]] = [columns[targetIndex], columns[columnIndex]];
            this.normalizeSortOrder();
        },

        openColumnSettings(region, rowIndex, columnIndex) {
            this.columnSettingsTarget = { region, rowIndex, columnIndex };
            this.columnSettingsTab = 'layout';
            this.columnSettingsDraft = JSON.parse(JSON.stringify(this.regions?.[region]?.[rowIndex]?.columns?.[columnIndex] || null));
            if (this.columnSettingsDraft) {
                this.normalizeResponsive(this.columnSettingsDraft);
                this.columnSettingsDraft.width = this.columnSettingsDraft.width === null || this.columnSettingsDraft.width === undefined
                    ? ''
                    : String(this.columnSettingsDraft.width);
                ['sm', 'md', 'lg', 'xl'].forEach((key) => {
                    this.columnSettingsDraft.responsive[key] = this.columnSettingsDraft.responsive[key] === null || this.columnSettingsDraft.responsive[key] === undefined || this.columnSettingsDraft.responsive[key] === ''
                        ? ''
                        : String(this.columnSettingsDraft.responsive[key]);
                });
            }
            this.columnSettingsModalOpen = true;
        },

        closeColumnSettings() {
            this.columnSettingsModalOpen = false;
            this.columnSettingsTarget = null;
            this.columnSettingsDraft = null;
        },

        saveColumnSettings() {
            if (!this.columnSettingsTarget || !this.columnSettingsDraft) return;
            const { region, rowIndex, columnIndex } = this.columnSettingsTarget;
            this.normalizeResponsive(this.columnSettingsDraft);
            this.regions[region][rowIndex].columns[columnIndex] = {
                ...this.regions[region][rowIndex].columns[columnIndex],
                ...JSON.parse(JSON.stringify(this.columnSettingsDraft)),
            };
            this.normalizeSortOrder();
            this.closeColumnSettings();
        },

        get settingsColumn() {
            return this.columnSettingsDraft;
        },

        openRowSettings(region, rowIndex) {
            this.rowSettingsTarget = { region, rowIndex };
            this.rowSettingsTab = 'layout';
            this.rowSettingsDraft = JSON.parse(JSON.stringify(this.regions?.[region]?.[rowIndex] || null));
            this.rowSettingsModalOpen = true;
        },

        closeRowSettings() {
            this.rowSettingsModalOpen = false;
            this.rowSettingsTarget = null;
            this.rowSettingsDraft = null;
        },

        saveRowSettings() {
            if (!this.rowSettingsTarget || !this.rowSettingsDraft) return;
            const { region, rowIndex } = this.rowSettingsTarget;
            this.regions[region][rowIndex] = {
                ...this.regions[region][rowIndex],
                ...JSON.parse(JSON.stringify(this.rowSettingsDraft)),
            };
            this.normalizeSortOrder();
            this.closeRowSettings();
        },

        get settingsRow() {
            return this.rowSettingsDraft;
        },

        addBlockByTemplate(region, rowIndex, columnIndex, templateId) {
            const column = this.regions[region][rowIndex].columns[columnIndex];
            const template = this.getTemplateById(templateId);
            if (!template) return;
            const isShellBlock = ['header', 'footer'].includes(String(template?.type || '').toLowerCase());

            column.blocks.push(this.hydrateBlock({
                id: `${template.type}_${column.blocks.length + 1}`,
                type: template.type,
                variation: template.variation,
                render_mode: template.render_mode,
                section_template_id: template.id,
                template_name: template.name,
                component_key: template.component_key || null,
                schema: template.schema || {},
                content: JSON.parse(JSON.stringify(template.default_content || {})),
                is_active: true,
                wrapper_tag: isShellBlock ? '' : null,
            }, region, rowIndex, columnIndex, column.blocks.length));

            this.normalizeSortOrder();
        },

        openBlockPicker(region, rowIndex, columnIndex) {
            this.pickerTarget = { region, rowIndex, columnIndex };
            this.pickerSearch = '';
            this.pickerModalOpen = true;
            this.$nextTick(() => this.$refs.pickerSearchInput?.focus());
        },

        closeBlockPicker() {
            this.pickerModalOpen = false;
            this.pickerSearch = '';
            this.pickerTarget = null;
        },

        pickTemplate(templateId) {
            if (!this.pickerTarget) return;
            const { region, rowIndex, columnIndex } = this.pickerTarget;
            this.addBlockByTemplate(region, rowIndex, columnIndex, templateId);
            this.closeBlockPicker();
        },

        focusBlock(blockId) {
            if (!blockId) return;

            for (const region of this.regionNames) {
                const rows = this.regions[region] || [];

                for (let rowIndex = 0; rowIndex < rows.length; rowIndex += 1) {
                    const row = rows[rowIndex];

                    for (let columnIndex = 0; columnIndex < (row.columns || []).length; columnIndex += 1) {
                        const column = row.columns[columnIndex];

                        for (let blockIndex = 0; blockIndex < (column.blocks || []).length; blockIndex += 1) {
                            const block = column.blocks[blockIndex];

                            if (String(block.id) !== String(blockId)) {
                                continue;
                            }

                            row._expanded = true;

                            this.$nextTick(() => {
                                const element = document.getElementById(`builder-block-${block.id}`);
                                if (element) {
                                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    element.classList.add('ring-2', 'ring-amber-300', 'ring-offset-2');
                                    window.setTimeout(() => {
                                        element.classList.remove('ring-2', 'ring-amber-300', 'ring-offset-2');
                                    }, 1800);
                                }

                                this.openBlockSettings(region, rowIndex, columnIndex, blockIndex);
                            });

                            return;
                        }
                    }
                }
            }
        },

        openBlockSettings(region, rowIndex, columnIndex, blockIndex) {
            this.settingsTarget = { region, rowIndex, columnIndex, blockIndex };
            this.settingsTab = 'content';
            this.settingsDraft = JSON.parse(JSON.stringify(this.regions?.[region]?.[rowIndex]?.columns?.[columnIndex]?.blocks?.[blockIndex] || null));
            this.normalizeRepeaterItems(this.settingsDraft);
            this.settingsModalOpen = true;
        },

        closeBlockSettings() {
            this.settingsModalOpen = false;
            this.settingsTarget = null;
            this.settingsDraft = null;
        },

        saveBlockSettings() {
            if (!this.settingsTarget || !this.settingsDraft) return;
            const { region, rowIndex, columnIndex, blockIndex } = this.settingsTarget;
            this.regions[region][rowIndex].columns[columnIndex].blocks[blockIndex] = {
                ...this.regions[region][rowIndex].columns[columnIndex].blocks[blockIndex],
                ...JSON.parse(JSON.stringify(this.settingsDraft)),
            };
            this.normalizeSortOrder();
            this.closeBlockSettings();
        },

        // ── AI block-edit — streaming (FAZ 3.6) ─────────────────────────
        //
        // Streams SSE from admin.ai.stream.block-edit. Delta chunks build a
        // live typewriter preview; the done event carries the fully-merged
        // content object which replaces settingsDraft.content.
        async applyAiTransform() {
            if (!this.settingsDraft || this.aiLoading) return;

            const action = this.aiAction;
            const customPrompt = action === 'custom' ? (this.aiCustomPrompt || '').trim() : null;
            if (action === 'custom' && !customPrompt) {
                this.aiStatus = 'Özel komut için bir talimat yazın.';
                this.aiStatusOk = false;
                return;
            }

            this.aiLoading    = true;
            this.aiStreaming  = true;
            this.aiStreamText = '';
            this.aiStatus     = '';
            this.aiStatusOk   = false;
            this.aiAbortController = new AbortController();

            const url = @js(route('admin.ai.stream.block-edit', [], false));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    signal: this.aiAbortController.signal,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'text/event-stream',
                    },
                    body: JSON.stringify({
                        action,
                        custom_prompt: customPrompt,
                        content: this.settingsDraft.content || {},
                        schema: Array.isArray(this.settingsDraft.schema_json)
                                ? this.settingsDraft.schema_json
                                : (this.settingsDraft.schema || null),
                        section_template_id: this.settingsDraft.section_template_id
                                             || this.settingsDraft.template_id || null,
                    }),
                });

                if (!response.ok) {
                    // Error before stream opened (e.g. 402 quota exceeded).
                    const errText = await response.text().catch(() => '');
                    // Try to parse SSE error event from body.
                    const m = errText.match(/data:\s*(\{.*\})/);
                    const errData = m ? JSON.parse(m[1]) : {};
                    throw new Error(errData.message || `HTTP ${response.status}`);
                }

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });

                    // Process complete SSE messages (terminated by \n\n).
                    const parts = buffer.split('\n\n');
                    buffer = parts.pop(); // last incomplete chunk stays in buffer

                    for (const part of parts) {
                        let eventType = 'message';
                        let dataLine  = '';

                        for (const line of part.split('\n')) {
                            if (line.startsWith('event:')) {
                                eventType = line.slice(6).trim();
                            } else if (line.startsWith('data:')) {
                                dataLine = line.slice(5).trim();
                            }
                        }

                        if (!dataLine) continue;

                        let payload;
                        try { payload = JSON.parse(dataLine); } catch { continue; }

                        if (eventType === 'delta') {
                            this.aiStreamText += payload.text || '';
                        } else if (eventType === 'done') {
                            if (payload.content) {
                                this.settingsDraft.content = payload.content;
                                this.aiStatus    = 'Blok içeriği AI ile güncellendi. Kaydetmeden önce gözden geçirin.';
                                this.aiStatusOk  = true;
                            } else {
                                this.aiStatus   = payload.note || 'AI yanıt üretti ama içerik uygulanamadı.';
                                this.aiStatusOk = false;
                            }
                        } else if (eventType === 'error') {
                            throw new Error(payload.message || 'AI hatası.');
                        }
                    }
                }
            } catch (e) {
                if (e.name === 'AbortError') {
                    this.aiStatus   = 'İptal edildi.';
                    this.aiStatusOk = false;
                } else {
                    this.aiStatus   = e.message || 'Ağ hatası.';
                    this.aiStatusOk = false;
                }
            } finally {
                this.aiLoading         = false;
                this.aiStreaming        = false;
                this.aiAbortController = null;
            }
        },

        abortAiTransform() {
            this.aiAbortController?.abort();
        },

        get settingsBlock() {
            return this.settingsDraft;
        },

        blockRenderedHtml(block) {
            if (!block) return '';
            const template = String(block.html_override || block.html_template || '').trim();

            if (!template) {
                return '<div class="text-xs text-gray-500">Bu block için HTML template tanımlı değil.</div>';
            }

            return this.renderTemplateString(template, block.content || {}, block.schema || {});
        },

        renderTemplateString(template, content = {}, schema = {}) {
            const rawPattern = new RegExp('\\{\\{\\{\\s*([a-zA-Z0-9_]+)\\s*\\}\\}\\}', 'g');
            const safePattern = new RegExp('\\{\\{\\s*([a-zA-Z0-9_]+)\\s*\\}\\}', 'g');

            return template.replace(rawPattern, (_match, key) => {
                const repeaterHtml = this.renderRepeaterPlaceholder(key, content, schema);
                if (repeaterHtml !== null) {
                    return repeaterHtml;
                }

                return String(content?.[key] ?? '');
            }).replace(safePattern, (_match, key) => {
                return this.escapeHtml(content?.[key] ?? '');
            });
        },

        renderRepeaterPlaceholder(key, content = {}, schema = {}) {
            if (!String(key).endsWith('_html')) {
                return null;
            }

            const baseKey = String(key).replace(/_html$/, '');
            const fieldSchema = schema?.[baseKey] || {};
            const items = content?.[baseKey];

            if ((fieldSchema.type || 'text') !== 'repeater' || !Array.isArray(items) || !fieldSchema.item_template) {
                return null;
            }

            return items.map((item) => {
                const itemContent = item && typeof item === 'object' && !Array.isArray(item) ? item : {};
                return this.renderTemplateString(String(fieldSchema.item_template), itemContent, {});
            }).join('');
        },

        blockCodePreview(block) {
            const wrapperTag = block?.wrapper_tag || '';
            const classAttr = block?.css_class ? ` class="${block.css_class}"` : '';
            const idAttr = block?.element_id ? ` id="${block.element_id}"` : '';
            const styleAttr = block?.inline_style ? ` style="${block.inline_style}"` : '';
            const extraAttr = block?.custom_attributes ? ` ${block.custom_attributes}` : '';
            const inner = this.blockRenderedHtml(block);

            if (!wrapperTag) {
                return inner;
            }

            return `<${wrapperTag}${idAttr}${classAttr}${styleAttr}${extraAttr}>${inner}</${wrapperTag}>`;
        },

        rowPreview(row) {
            if (!row) return '';
            const wrapperTag = row.wrapper_tag || '';
            const container = row.container || '';
            const classValue = [container, row.css_class].filter(Boolean).join(' ');
            const idAttr = row.element_id ? ` id="${row.element_id}"` : '';
            const styleAttr = row.inline_style ? ` style="${row.inline_style}"` : '';
            const extraAttr = row.custom_attributes ? ` ${row.custom_attributes}` : '';
            const classAttr = classValue ? ` class="${classValue}"` : '';

            if (!wrapperTag && !classAttr && !idAttr && !styleAttr && !extraAttr) {
                return 'Row wrapper yok. Blocklar dogrudan render edilir.';
            }

            const tag = wrapperTag || 'div';

            return `<${tag}${idAttr}${classAttr}${styleAttr}${extraAttr}>...</${tag}>`;
        },

        columnPreview(column) {
            if (!column) return '';
            const classes = [];
            if (column?.responsive?.xs || column?.width) {
                classes.push(this.columnClassLabel(column));
            }
            ['sm', 'md', 'lg', 'xl'].forEach((bp) => {
                if (column?.responsive?.[bp]) {
                    classes.push(`col-${bp}-${column.responsive[bp]}`);
                }
            });
            if (column?.css_class) classes.push(column.css_class);
            const idAttr = column?.element_id ? ` id="${column.element_id}"` : '';
            const styleAttr = column?.inline_style ? ` style="${column.inline_style}"` : '';
            const extraAttr = column?.custom_attributes ? ` ${column.custom_attributes}` : '';
            const classAttr = classes.length ? ` class="${classes.join(' ')}"` : '';

            if (!classAttr && !idAttr && !styleAttr && !extraAttr) {
                return 'Column wrapper yok. Blocklar dogrudan render edilir.';
            }

            return `<div${idAttr}${classAttr}${styleAttr}${extraAttr}>...</div>`;
        },

        escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        },

        serializeContent(value) {
            if (Array.isArray(value)) {
                return value.map((item) => this.serializeContent(item));
            }

            if (value && typeof value === 'object') {
                return Object.entries(value).reduce((output, [key, item]) => {
                    if (key === '_uid') {
                        return output;
                    }

                    output[key] = this.serializeContent(item);
                    return output;
                }, {});
            }

            return value;
        },

        removeBlock(region, rowIndex, columnIndex, blockIndex) {
            this.regions[region][rowIndex].columns[columnIndex].blocks.splice(blockIndex, 1);
            this.normalizeSortOrder();
        },

        duplicateBlock(region, rowIndex, columnIndex, blockIndex) {
            const blocks = this.regions[region][rowIndex].columns[columnIndex].blocks;
            const source = blocks[blockIndex];
            if (!source) return;

            const clone = JSON.parse(JSON.stringify(source));
            clone._uid = this.generateUid('block');
            clone.id = `${source.type || 'block'}_${blocks.length + 1}`;
            blocks.splice(blockIndex + 1, 0, clone);
            this.normalizeSortOrder();
        },

        moveBlock(region, rowIndex, columnIndex, blockIndex, direction) {
            const blocks = this.regions[region][rowIndex].columns[columnIndex].blocks;
            const targetIndex = blockIndex + direction;
            if (targetIndex < 0 || targetIndex >= blocks.length) return;
            [blocks[blockIndex], blocks[targetIndex]] = [blocks[targetIndex], blocks[blockIndex]];
            this.normalizeSortOrder();
        },

        // ── Blok sürükle-bırak (kolon içi + kolonlar/bölgeler arası) ─────
        startBlockDrag(event, region, rowIndex, columnIndex, blockIndex) {
            this.dragBlock = { region, rowIndex, columnIndex, blockIndex };
            event.dataTransfer.effectAllowed = 'move';
            // Firefox sürüklemeyi başlatmak için data ister
            event.dataTransfer.setData('text/plain', 'block');
            const card = event.target.closest('[data-block-card]');
            if (card && event.dataTransfer.setDragImage) {
                event.dataTransfer.setDragImage(card, 16, 16);
            }
        },

        endBlockDrag() {
            this.dragBlock = null;
            this.dragBlockOver = null;
        },

        blockDropKey(region, rowIndex, columnIndex, blockIndex) {
            return [region, rowIndex, columnIndex, blockIndex === null ? 'end' : blockIndex].join(':');
        },

        blockDragOver(event, region, rowIndex, columnIndex, blockIndex = null) {
            if (!this.dragBlock) return;
            event.preventDefault();
            this.dragBlockOver = this.blockDropKey(region, rowIndex, columnIndex, blockIndex);
        },

        blockIsDragSource(region, rowIndex, columnIndex, blockIndex) {
            return this.dragBlock
                && this.dragBlock.region === region
                && this.dragBlock.rowIndex === rowIndex
                && this.dragBlock.columnIndex === columnIndex
                && this.dragBlock.blockIndex === blockIndex;
        },

        dropBlock(region, rowIndex, columnIndex, blockIndex = null) {
            const src = this.dragBlock;
            this.endBlockDrag();
            if (!src) return;

            const srcBlocks = this.regions[src.region]?.[src.rowIndex]?.columns?.[src.columnIndex]?.blocks;
            const dstColumn = this.regions[region]?.[rowIndex]?.columns?.[columnIndex];
            if (!Array.isArray(srcBlocks) || !dstColumn) return;
            if (!Array.isArray(dstColumn.blocks)) dstColumn.blocks = [];

            let target = blockIndex === null ? dstColumn.blocks.length : blockIndex;
            if (srcBlocks === dstColumn.blocks && target === src.blockIndex) return;

            const [moved] = srcBlocks.splice(src.blockIndex, 1);
            if (!moved) return;
            dstColumn.blocks.splice(Math.min(target, dstColumn.blocks.length), 0, moved);
            this.normalizeSortOrder();
        },

        serializeRegions() {
            const output = {
                header: [],
                body: [],
                footer: [],
            };

            Object.keys(output).forEach((region) => {
                output[region] = (this.regions[region] || []).map((row, rowIndex) => ({
                    id: row.id || `row_${region}_${rowIndex + 1}`,
                    type: 'row',
                    is_active: row.is_active !== false,
                    container: row.container || null,
                    wrapper_tag: row.wrapper_tag || null,
                    css_class: row.css_class || null,
                    element_id: row.element_id || null,
                    inline_style: row.inline_style || null,
                    custom_attributes: row.custom_attributes || null,
                    columns: (row.columns || []).map((column, columnIndex) => ({
                        id: column.id || `col_${region}_${rowIndex + 1}_${columnIndex + 1}`,
                        width: (column?.width !== '' && column?.width !== null && column?.width !== undefined)
                            ? Number(column.width)
                            : null,
                        is_active: column.is_active !== false,
                        responsive: {
                            xs: (column?.width !== '' && column?.width !== null && column?.width !== undefined)
                                ? Number(column.width)
                                : null,
                            sm: column?.responsive?.sm || null,
                            md: column?.responsive?.md || null,
                            lg: column?.responsive?.lg || null,
                            xl: column?.responsive?.xl || null,
                        },
                        css_class: column.css_class || null,
                        element_id: column.element_id || null,
                        inline_style: column.inline_style || null,
                        custom_attributes: column.custom_attributes || null,
                        blocks: (column.blocks || []).map((block, blockIndex) => ({
                            id: block.id || `block_${block.type || 'item'}_${blockIndex + 1}`,
                            type: block.type,
                            variation: block.variation,
                            render_mode: block.render_mode,
                            section_template_id: block.section_template_id,
                            component_key: block.component_key,
                            is_active: block.is_active !== false,
                            sort_order: block.sort_order || (blockIndex + 1),
                            content: this.serializeContent(block.content || {}),
                            wrapper_tag: block.wrapper_tag || null,
                            css_class: block.css_class || null,
                            element_id: block.element_id || null,
                            inline_style: block.inline_style || null,
                            custom_attributes: block.custom_attributes || null,
                            html_override: block.html_override || null,
                        })),
                    })),
                }));
            });

            return output;
        },

        normalizeSortOrder() {
            this.regionNames.forEach((region) => {
                (this.regions[region] || []).forEach((row, rowIndex) => {
                    row.id = row.id || `row_${region}_${rowIndex + 1}`;
                    row.container = row.container || '';
                    row.wrapper_tag = row.wrapper_tag || '';
                    row.css_class = row.css_class || '';
                    row.element_id = row.element_id || '';
                    row.inline_style = row.inline_style || '';
                    row.custom_attributes = row.custom_attributes || '';

                    (row.columns || []).forEach((column, columnIndex) => {
                        column.id = column.id || `col_${region}_${rowIndex + 1}_${columnIndex + 1}`;
                        this.normalizeResponsive(column);
                        column.css_class = column.css_class || '';
                        column.element_id = column.element_id || '';
                        column.inline_style = column.inline_style || '';
                        column.custom_attributes = column.custom_attributes || '';

                        (column.blocks || []).forEach((block, blockIndex) => {
                            block.sort_order = blockIndex + 1;
                            block.wrapper_tag = block.wrapper_tag || '';
                            block.css_class = block.css_class || '';
                            block.element_id = block.element_id || '';
                            block.inline_style = block.inline_style || '';
                            block.custom_attributes = block.custom_attributes || '';
                            block.html_override = block.html_override || '';
                        });
                    });
                });
            });

            this.queueSerializedRegionsSync();
        },
    };
}
</script>
@endpush
