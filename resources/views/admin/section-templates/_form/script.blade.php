@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/theme/dracula.min.css">
<style>
.CodeMirror { height: auto; min-height: 320px; font-size: 12px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; border-radius: 0.5rem; border: 1px solid #d1d5db; }
.CodeMirror-focused { border-color: transparent; box-shadow: 0 0 0 2px #6366f1; }
.CodeMirror-scroll { min-height: 320px; }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/edit/closetag.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/foldcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ── CodeMirror for HTML template ────────────────────────────────
    const rawTextarea = document.getElementById('html_template_input');
    let cmEditor = null;

    if (rawTextarea && typeof CodeMirror !== 'undefined') {
        // CodeMirror init'i (veya eksik/blokeli bir CDN addon'u) hata fırlatırsa
        // TÜM DOMContentLoaded geri çağrısı patlar ve hiçbir buton bağlanmazdı.
        // try/catch ile izole et: başarısızlıkta düz textarea'ya düş.
        try {
            cmEditor = CodeMirror.fromTextArea(rawTextarea, {
                mode: 'htmlmixed',
                theme: 'dracula',
                lineNumbers: true,
                lineWrapping: false,
                matchBrackets: true,
                autoCloseTags: true,
                tabSize: 2,
                indentWithTabs: false,
                extraKeys: { 'Ctrl-/': 'toggleComment', 'Cmd-/': 'toggleComment' },
            });
            cmEditor.on('change', () => {
                rawTextarea.value = cmEditor.getValue();
                updateSchemaDiff();
                window.dispatchEvent(new CustomEvent('section-template-editor-change'));
            });
        } catch (e) {
            console.error('[section-template] CodeMirror init failed, falling back to plain textarea:', e);
            cmEditor = null;
        }
    }

    // Proxy: make insertAtCursor work with CodeMirror
    const getHtmlValue = () => cmEditor ? cmEditor.getValue() : (rawTextarea?.value || '');
    const setHtmlValue = (val) => { if (cmEditor) cmEditor.setValue(val); else if (rawTextarea) rawTextarea.value = val; };
    const insertAtHtmlCursor = (value) => {
        if (!value) return;
        if (cmEditor) {
            const cursor = cmEditor.getCursor();
            cmEditor.replaceRange(value, cursor);
        } else {
            insertAtCursor(rawTextarea, value);
        }
        updateSchemaDiff();
    };

    // ── end CodeMirror ──────────────────────────────────────────────

    const variationOptions = @json($variationOptions);
    const initialVariation = @json($selectedVariation);
    const menuPlaceholdersData = @json($menuPlaceholders);

    const themeSelect = document.getElementById('theme_id_select');
    const typeSelect = document.getElementById('type_select');
    const typeCustomWrapper = document.getElementById('type_custom_wrapper');
    const typeCustomInput = document.getElementById('type_custom_input');
    const variationInput = document.getElementById('variation_input');
    const variationSuggestions = document.getElementById('variation_suggestions');
    const variationStatus = document.getElementById('variation_status');
    const normalizeVariationButton = document.getElementById('normalize_variation_button');
    const suggestVariationButton = document.getElementById('suggest_variation_button');
    const renderModeSelect = document.getElementById('render_mode_select');
    const componentKeyWrapper = document.getElementById('component_key_wrapper');
    const htmlTemplateInput = document.getElementById('html_template_input');
    const schemaInput = document.getElementById('schema_json_input');
    const defaultContentInput = document.getElementById('default_content_json_input');
    const generateButton = document.getElementById('generate_from_template');
    const menuPlaceholderSelect = document.getElementById('menu_placeholder_select');
    const insertMenuHtmlButton = document.getElementById('insert_menu_html');
    const insertMenuItemsButton = document.getElementById('insert_menu_items');
    const reloadMenuBtn = document.getElementById('reload_menu_placeholders');
    const systemPlaceholderSelect = document.getElementById('system_placeholder_select');
    const insertSystemPlaceholderButton = document.getElementById('insert_system_placeholder');
    const findRepeatCandidatesButton = document.getElementById('find_repeat_candidates');
    const repeatCandidatePanel = document.getElementById('repeat_candidate_panel');
    const repeatCandidateSelect = document.getElementById('repeat_candidate_select');
    const repeatFieldKeyInput = document.getElementById('repeat_field_key');
    const applyRepeatCandidateButton = document.getElementById('apply_repeat_candidate');
    const repeatCandidateHelp = document.getElementById('repeat_candidate_help');
    const repeatCandidateMeta = document.getElementById('repeat_candidate_meta');
    const manualRepeatTypeSelect = document.getElementById('manual_repeat_type');
    const manualRepeatKeyInput = document.getElementById('manual_repeat_key');
    const manualRepeatItemHtmlInput = document.getElementById('manual_repeat_item_html');
    const fillManualRepeatSnippetButton = document.getElementById('fill_manual_repeat_snippet');
    const applyManualRepeatButton = document.getElementById('apply_manual_repeat');
    const generateModeSelect = document.getElementById('generate_mode_select');
    const diffPanel = document.getElementById('html_schema_diff');
    const diffOkBadge = document.getElementById('diff_ok_badge');
    const diffMissingSchemaWrapper = document.getElementById('diff_missing_schema_wrapper');
    const diffMissingSchema = document.getElementById('diff_missing_schema');
    const diffUnusedSchemaWrapper = document.getElementById('diff_unused_schema_wrapper');
    const diffUnusedSchema = document.getElementById('diff_unused_schema');
    const generateDefaultsBtns = [
        document.getElementById('generate_defaults_btn'),
        document.getElementById('generate_defaults_btn2'),
    ].filter(Boolean);

    let repeatCandidates = [];

    // dispatch change event so preview panel can react
    if (defaultContentInput) {
        defaultContentInput.addEventListener('input', () => {
            window.dispatchEvent(new CustomEvent('section-template-editor-change'));
        });
    }

    // expose helpers globally for preview panel
    window.getHtmlValue = getHtmlValue;

    // ────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────

    const normalizeSlug = (value) => String(value || '')
        .trim().toLowerCase()
        .replace(/[^a-z0-9_-]+/g, '-').replace(/-+/g, '-')
        .replace(/^[-_]+|[-_]+$/g, '');

    const normalizeKey = (value, fallback = 'items') => {
        const n = String(value || '').trim().toLowerCase()
            .replace(/[^a-z0-9_]+/g, '_').replace(/^_+|_+$/g, '');
        return n || fallback;
    };

    const currentTypeValue = () => typeSelect?.value === '__custom'
        ? normalizeSlug(typeCustomInput?.value || '')
        : (typeSelect?.value || '');

    const labelize = (key) => key
        .replace(/_html$/i, '').replace(/_url$/i, ' url').replace(/_alt$/i, ' alt')
        .replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());

    // Sistem/menü placeholder'ları SCHEMA ALANI DEĞİLDİR — render anında
    // SectionTemplateRenderer tarafından site ayarları + menülerden çözülür.
    // Bunları schema'ya yakalamak ({{logo_url}}, {{{menu_header_html}}} gibi)
    // sistem değerini içerik default'uyla geçersiz kılar/kirletir. Dışla.
    const SYSTEM_TOKENS = new Set([
        'site_name', 'site_domain', 'theme_slug', 'logo_url', 'favicon_url',
        'phone', 'email', 'address', 'whatsapp_number', 'working_hours',
        'tax_id', 'footer_text',
    ]);
    const isSystemPlaceholder = (key) => {
        const k = String(key || '');
        if (SYSTEM_TOKENS.has(k)) return true;
        // menü tokenları: menu_{key}_html, menu_{key}_items_html, menu_{key}_name
        return /^menu_[a-z0-9_]+_(items_html|html|name)$/.test(k);
    };

    const escapeText = (value) => String(value ?? '')
        .replaceAll('&', '&amp;').replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');

    const inferType = (key) => {
        if (/_html$/i.test(key)) return 'textarea';
        if (/(^|_)(image|img|photo|logo|icon|avatar|banner|thumbnail|cover|background)(_|$)/i.test(key)) return /_alt$/i.test(key) ? 'text' : 'image';
        if (/^(show_|is_|has_)/i.test(key)) return 'boolean';
        if (/(count|limit|height|width|columns|order|sort)/i.test(key)) return 'number';
        if (/(body|description|subtitle|content|excerpt|caption|message|summary|text)/i.test(key)) return 'textarea';
        return 'text';
    };

    const inferDefaultValue = (key, type) => {
        if (type === 'boolean') return false;
        if (type === 'number') return 0;
        if (type === 'icon') return 'fas fa-star';
        if (type === 'color') return '#4f46e5';
        if (type === 'email') return 'ornek@domain.com';
        if (type === 'url' || type === 'page_link') return '#';
        if (type === 'image' || type === 'media_id') return `https://placehold.co/1200x800?text=${encodeURIComponent(labelize(key))}`;
        if (/_html$/i.test(key)) return /(items|cards|features|slides|logos|gallery|list)/i.test(key) ? '<div class="item-card">Tekrarlı alan örneği</div>' : '<p>İçerik buraya gelecek.</p>';
        if (/_url$/i.test(key)) return '#';
        if (/_alt$/i.test(key)) return 'Görsel açıklaması';
        if (/(icon)/i.test(key)) return 'fas fa-star';
        if (/(title|heading|name)/i.test(key)) return 'Örnek Başlık';
        if (/(description|subtitle|excerpt|caption|text|body|content|message|summary)/i.test(key)) return 'Örnek içerik';
        return '';
    };

    // Schema'dan tam örnek içerik üretir (canlı önizleme ve "Default Üret"
    // tarafından paylaşılır). Repeater alanlar 2 örnek item alır.
    const buildSchemaSampleContent = (schema) => {
        const defaults = {};
        Object.entries(schema || {}).forEach(([key, field]) => {
            const type = field.type || 'text';
            if (type === 'repeater') {
                const itemDefaults = {};
                Object.entries(field.fields || field.item_schema || {}).forEach(([k, f]) => {
                    itemDefaults[k] = inferDefaultValue(k, f.type || 'text');
                });
                defaults[key] = [itemDefaults, { ...itemDefaults }];
            } else if (type === 'enum' && Array.isArray(field.options) && field.options.length) {
                const opt = field.options[0];
                defaults[key] = (opt !== null && typeof opt === 'object') ? opt.value : opt;
            } else {
                defaults[key] = inferDefaultValue(key, type);
            }
        });
        return defaults;
    };
    window.buildSchemaSampleContent = () => buildSchemaSampleContent(parseJsonObject(schemaInput?.value || ''));

    const openBraces = '{' + '{';
    const closeBraces = '}' + '}';
    const placeholderRegex = new RegExp(`${openBraces}{?\\s*([a-zA-Z0-9_]+)\\s*}?${closeBraces}`, 'g');

    const hasPlaceholders = (t) => { placeholderRegex.lastIndex = 0; return placeholderRegex.test(t); };

    const extractPlaceholders = (template) => {
        placeholderRegex.lastIndex = 0;
        const placeholders = [], seen = new Set();
        let match;
        while ((match = placeholderRegex.exec(template)) !== null) {
            if (!seen.has(match[1])) { seen.add(match[1]); placeholders.push(match[1]); }
        }
        return placeholders;
    };

    const parseJsonObject = (value) => {
        if (!value?.trim()) return {};
        try { const p = JSON.parse(value); return p && typeof p === 'object' && !Array.isArray(p) ? p : {}; } catch { return {}; }
    };

    const schemaKeys = (schema) => !schema ? [] : Object.entries(schema).map(([key, value]) => {
        if (value && typeof value === 'object') return value.key || value.name || key;
        return key;
    }).filter(Boolean);

    const schemaHasPlaceholder = (schema, key) => {
        if (schemaKeys(schema).includes(key)) return true;
        if (/_html$/i.test(key)) return schema?.[key.replace(/_html$/i, '')]?.type === 'repeater';
        return false;
    };

    const nextUniqueKey = (baseKey, schema) => {
        if (!schema[baseKey]) return baseKey;
        let i = 2;
        while (schema[`${baseKey}_${i}`]) i++;
        return `${baseKey}_${i}`;
    };

    const createPlaceholderToken = (key, raw = false) =>
        raw ? `${openBraces}{${key}}${closeBraces}` : `${openBraces}${key}${closeBraces}`;

    // ────────────────────────────────────────────────────
    // Insert at cursor
    // ────────────────────────────────────────────────────

    const insertAtCursor = (textarea, value) => {
        if (!textarea || !value) return;
        const start = textarea.selectionStart ?? textarea.value.length;
        const end = textarea.selectionEnd ?? textarea.value.length;
        textarea.value = `${textarea.value.slice(0, start)}${value}${textarea.value.slice(end)}`;
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + value.length;
        updateSchemaDiff();
    };

    // ────────────────────────────────────────────────────
    // Variation helpers
    // ────────────────────────────────────────────────────

    const updateVariationOptions = () => {
        if (!variationSuggestions) return;
        const themeId = themeSelect?.value;
        const type = currentTypeValue();
        const currentValue = variationInput?.value || initialVariation || '';
        const options = variationOptions?.[themeId]?.[type] || [];
        variationSuggestions.innerHTML = '';
        const seen = new Set();
        [...options, currentValue].filter(Boolean).forEach(v => {
            if (seen.has(v)) return;
            seen.add(v);
            const opt = document.createElement('option');
            opt.value = v;
            variationSuggestions.appendChild(opt);
        });
        updateVariationStatus();
    };

    const updateVariationStatus = () => {
        if (!variationStatus) return;
        const themeId = themeSelect?.value;
        const type = currentTypeValue();
        const variation = normalizeSlug(variationInput?.value || '');
        variationStatus.className = 'mt-2 rounded-lg px-3 py-2 text-xs';
        if (!themeId || !type || !variation) { variationStatus.classList.add('hidden'); return; }
        const existing = (variationOptions?.[themeId]?.[type] || []).map(String);
        const isCurrentRecord = initialVariation && String(initialVariation) === variation;
        const exists = existing.includes(variation) && !isCurrentRecord;
        variationStatus.classList.remove('hidden');
        // classList.add() boşluklu çok-sınıflı string KABUL ETMEZ (InvalidCharacterError);
        // her sınıf ayrı argüman olmalı → split + spread. Bu satır variation'ı olan
        // şablonların DÜZENLEME sayfasında load anında çalışır; hata tüm DOMContentLoaded'ı
        // (ve dolayısıyla tüm buton bağlamalarını) durduruyordu.
        variationStatus.classList.add(...(exists
            ? 'border border-red-200 bg-red-50 text-red-800'
            : 'border border-green-200 bg-green-50 text-green-800').split(' '));
        variationStatus.textContent = exists ? 'Bu tema + type altında bu variation zaten var.' : (existing.includes(variation) ? 'Mevcut kayıt düzenleniyor.' : 'Bu variation yeni oluşturulabilir.');
    };

    const updateRenderModeUi = () => {
        if (componentKeyWrapper) componentKeyWrapper.style.display = renderModeSelect?.value === 'component' ? '' : 'none';
    };

    const updateTypeUi = () => {
        const isCustom = typeSelect?.value === '__custom';
        typeCustomWrapper?.classList.toggle('hidden', !isCustom);
        if (typeCustomInput) typeCustomInput.required = isCustom;
        updateVariationOptions();
    };

    if (themeSelect) themeSelect.addEventListener('change', updateVariationOptions);
    if (typeSelect) typeSelect.addEventListener('change', updateTypeUi);
    if (typeCustomInput) typeCustomInput.addEventListener('input', () => {
        const c = typeCustomInput.selectionStart;
        typeCustomInput.value = normalizeSlug(typeCustomInput.value);
        typeCustomInput.selectionStart = typeCustomInput.selectionEnd = c;
        updateVariationOptions();
    });
    if (variationInput) variationInput.addEventListener('input', updateVariationStatus);
    if (normalizeVariationButton) normalizeVariationButton.addEventListener('click', () => {
        if (variationInput) { variationInput.value = normalizeSlug(variationInput.value); updateVariationStatus(); }
    });
    if (suggestVariationButton) suggestVariationButton.addEventListener('click', () => {
        const base = normalizeSlug(variationInput?.value || currentTypeValue() || 'default');
        const existing = new Set((variationOptions?.[themeSelect?.value]?.[currentTypeValue()] || []).map(String));
        let c = base, s = 2;
        while (existing.has(c)) { c = `${base}-${s}`; s++; }
        if (variationInput) { variationInput.value = c; updateVariationOptions(); }
    });
    if (renderModeSelect) renderModeSelect.addEventListener('change', updateRenderModeUi);

    updateTypeUi();
    updateRenderModeUi();

    // ────────────────────────────────────────────────────
    // Menu placeholder insert
    // ────────────────────────────────────────────────────

    if (insertMenuHtmlButton) insertMenuHtmlButton.addEventListener('click', () => insertAtHtmlCursor(menuPlaceholderSelect?.value));
    if (insertMenuItemsButton) insertMenuItemsButton.addEventListener('click', () => insertAtHtmlCursor(menuPlaceholderSelect?.selectedOptions[0]?.dataset?.itemsToken || ''));
    if (reloadMenuBtn) reloadMenuBtn.addEventListener('click', async () => {
        reloadMenuBtn.disabled = true;
        reloadMenuBtn.querySelector('i')?.classList.add('fa-spin');
        try {
            const res = await fetch('{{ route("admin.section-templates.menu-placeholders") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            menuPlaceholderSelect.innerHTML = '<option value="">Menü seç…</option>';
            data.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.html_token;
                opt.dataset.itemsToken = p.items_token;
                opt.textContent = p.label;
                menuPlaceholderSelect.appendChild(opt);
            });
        } catch {}
        reloadMenuBtn.disabled = false;
        reloadMenuBtn.querySelector('i')?.classList.remove('fa-spin');
    });

    if (insertSystemPlaceholderButton) insertSystemPlaceholderButton.addEventListener('click', () => insertAtHtmlCursor(systemPlaceholderSelect?.value));

    // ────────────────────────────────────────────────────
    // Schema diff
    // ────────────────────────────────────────────────────

    const renderTokenList = (el, keys, tone) => {
        if (!el) return;
        el.innerHTML = '';
        keys.forEach(key => {
            const b = document.createElement('span');
            b.className = tone === 'red' ? 'rounded bg-red-100 px-1.5 py-0.5 font-mono text-red-800' : 'rounded bg-gray-100 px-1.5 py-0.5 font-mono text-gray-700';
            b.textContent = key;
            el.appendChild(b);
        });
    };

    // Schema builder (Alpine) için: HTML template'teki placeholder anahtarları.
    // Alan-bazlı "HTML'de var/yok" göstergesi bunu okur.
    window.getHtmlPlaceholders = () => extractPlaceholders(getHtmlValue());

    const updateSchemaDiff = () => {
        if (!diffPanel) return;
        const placeholders = extractPlaceholders(getHtmlValue());
        const schema = parseJsonObject(schemaInput?.value || '');
        const keys = schemaKeys(schema);
        // Sistem/menü placeholder'ları schema'da OLMAMALI (render'da çözülür) —
        // "schema'da yok" uyarısından hariç tut.
        const missingSchema = placeholders.filter(k => !isSystemPlaceholder(k) && !schemaHasPlaceholder(schema, k));
        const unusedSchema = keys.filter(k => !placeholders.includes(k) && !(schema?.[k]?.type === 'repeater' && placeholders.includes(`${k}_html`)));
        diffPanel.classList.toggle('hidden', placeholders.length === 0 && keys.length === 0);
        diffOkBadge?.classList.toggle('hidden', missingSchema.length > 0 || unusedSchema.length > 0 || (placeholders.length === 0 && keys.length === 0));
        diffMissingSchemaWrapper?.classList.toggle('hidden', missingSchema.length === 0);
        diffUnusedSchemaWrapper?.classList.toggle('hidden', unusedSchema.length === 0);
        renderTokenList(diffMissingSchema, missingSchema, 'red');
        renderTokenList(diffUnusedSchema, unusedSchema, 'gray');
    };

    if (htmlTemplateInput && !cmEditor) htmlTemplateInput.addEventListener('input', updateSchemaDiff);
    if (schemaInput) { new MutationObserver(updateSchemaDiff).observe(schemaInput, { attributes: true, childList: true, characterData: true, subtree: true }); schemaInput.addEventListener('input', updateSchemaDiff); }
    updateSchemaDiff();

    // ────────────────────────────────────────────────────
    // Default content generation
    // ────────────────────────────────────────────────────

    const generateDefaultsFromSchema = () => {
        const schema = parseJsonObject(schemaInput?.value || '');
        if (!schema || Object.keys(schema).length === 0) { window.alert('Önce schema alanları oluştur.'); return; }
        if (defaultContentInput) defaultContentInput.value = JSON.stringify(buildSchemaSampleContent(schema), null, 2);
        window.dispatchEvent(new CustomEvent('section-template-editor-change'));
    };

    generateDefaultsBtns.forEach(btn => btn?.addEventListener('click', generateDefaultsFromSchema));

    // Schema alanları değişince canlı önizlemeyi tetikle (debounce panelde).
    // schemaInput hidden textarea'ya Alpine x-bind:value="serialized" yazar;
    // MutationObserver bu değişimleri yakalar.
    if (schemaInput) {
        new MutationObserver(() => {
            window.dispatchEvent(new CustomEvent('section-template-editor-change'));
        }).observe(schemaInput, { attributes: true, childList: true, characterData: true, subtree: true });
    }
    // Default content elle düzenlenince de tetikle
    defaultContentInput?.addEventListener('input', () => {
        window.dispatchEvent(new CustomEvent('section-template-editor-change'));
    });

    // ────────────────────────────────────────────────────
    // Repeat candidates
    // ────────────────────────────────────────────────────

    const elementSignature = (el) => {
        const tag = el.tagName.toLowerCase();
        const classes = String(el.className || '').split(/\s+/).filter(Boolean).sort().join('.');
        return classes ? `${tag}.${classes}` : tag;
    };

    const classifyRepeatCandidate = (parent, first) => {
        const source = `${parent?.className || ''} ${parent?.id || ''} ${first?.className || ''} ${first?.id || ''}`.toLowerCase();
        const parentTag = (parent?.tagName || '').toLowerCase();
        const firstTag = (first?.tagName || '').toLowerCase();
        if (/owl|carousel|slide|swiper/.test(source)) return { key: 'slides', kind: 'slide', label: 'Slayt / Owl', description: 'Carousel/Owl/Swiper yapısı algılandı.', priority: 100, confidence: 'Yüksek' };
        if ((parentTag === 'ul' || parentTag === 'ol') && firstTag === 'li') return { key: 'list_items', kind: 'list', label: 'Liste', description: 'UL/OL içinde LI tekrarı algılandı.', priority: 90, confidence: 'Yüksek' };
        if (/card|box|panel/.test(source)) return { key: 'cards', kind: 'card', label: 'Card', description: 'Card/box/panel tekrarı algılandı.', priority: 80, confidence: 'Yüksek' };
        if (/feature|service|benefit|icon/.test(source)) return { key: 'features', kind: 'feature', label: 'Özellik', description: 'Feature/service/icon yapısı algılandı.', priority: 70, confidence: 'Orta' };
        if (/gallery|image|photo|logo|brand/.test(source)) return { key: /logo|brand/.test(source) ? 'logos' : 'gallery_items', kind: 'media', label: /logo|brand/.test(source) ? 'Logo / Marka' : 'Galeri', description: 'Görsel/logo tekrarları algılandı.', priority: 60, confidence: 'Orta' };
        return { key: 'items', kind: 'generic', label: 'Genel Item', description: 'Aynı tag/class tekrarı algılandı.', priority: 10, confidence: 'Düşük' };
    };

    const getElementPath = (el, root) => {
        const path = [];
        let current = el;
        while (current && current !== root) {
            const parent = current.parentElement;
            if (!parent) return [];
            path.unshift(Array.from(parent.children).indexOf(current));
            current = parent;
        }
        return path;
    };

    const resolveElementPath = (root, path) => path.reduce((cur, idx) => cur?.children?.[idx] || null, root);

    const parseTemplateRoot = () => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(`<div id="template-root">${getHtmlValue()}</div>`, 'text/html');
        return { doc, root: doc.getElementById('template-root') };
    };

    const findRepeatCandidates = () => {
        const { root } = parseTemplateRoot();
        if (!root) return [];
        const candidates = [];
        root.querySelectorAll('*').forEach(parent => {
            if (parent.tagName === 'SCRIPT' || parent.tagName === 'STYLE') return;
            const groups = Array.from(parent.children).reduce((acc, child) => {
                if (child.tagName === 'SCRIPT' || child.tagName === 'STYLE') return acc;
                const sig = elementSignature(child);
                acc[sig] = acc[sig] || [];
                acc[sig].push(child);
                return acc;
            }, {});
            Object.entries(groups).forEach(([signature, items]) => {
                if (items.length < 2) return;
                const cl = classifyRepeatCandidate(parent, items[0]);
                candidates.push({ signature, ...cl, count: items.length, parentSignature: elementSignature(parent), sampleHtml: items[0].outerHTML, parentPath: getElementPath(parent, root), label: `${cl.label} · ${signature} · ${items.length} adet · ${cl.confidence}` });
            });
        });
        return candidates.sort((a, b) => b.priority !== a.priority ? b.priority - a.priority : b.count - a.count);
    };

    const inferTextBaseKey = (el, text) => {
        const tag = (el?.tagName || '').toLowerCase();
        const len = text.trim().length;
        if (tag === 'a' || tag === 'button') return 'button_text';
        if (tag === 'h1' || tag === 'h2' || tag === 'h3') return 'title';
        if (tag === 'h4' || tag === 'h5' || tag === 'h6') return 'eyebrow';
        if (tag === 'p') return len > 120 ? 'body_html' : len > 60 ? 'description' : 'subtitle';
        if (tag === 'img') return 'image_alt';
        if (tag === 'li') return 'item_text';
        return 'text';
    };

    const buildRepeaterItem = (sourceEl) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(`<div id="ri">${sourceEl.outerHTML}</div>`, 'text/html');
        const root = doc.getElementById('ri');
        const item = root?.firstElementChild;
        const itemSchema = {}, itemDefaults = {};
        if (!item) return null;
        const reg = (baseKey, type, dv, raw = false) => {
            const key = nextUniqueKey(normalizeKey(baseKey, 'field'), itemSchema);
            itemSchema[key] = { type, label: labelize(key) };
            itemDefaults[key] = dv;
            return createPlaceholderToken(key, raw);
        };
        item.querySelectorAll('*').forEach(el => {
            if (el.tagName === 'SCRIPT' || el.tagName === 'STYLE') return;
            if (el.hasAttribute('href')) el.setAttribute('href', reg(el.tagName.toLowerCase() === 'a' ? 'button_url' : 'link_url', 'text', el.getAttribute('href') || '#'));
            if (el.hasAttribute('src')) { const k = el.tagName.toLowerCase() === 'img' ? 'image_url' : 'media_url'; el.setAttribute('src', reg(k, inferType(k), el.getAttribute('src') || inferDefaultValue(k, 'image'))); }
            if (el.hasAttribute('alt')) el.setAttribute('alt', reg('image_alt', 'text', el.getAttribute('alt') || 'Görsel açıklaması'));
            const style = el.getAttribute('style') || '';
            const bgMatch = style.match(/background-image\s*:\s*url\((['"]?)(.*?)\1\)/i);
            if (bgMatch?.[2]) el.setAttribute('style', style.replace(bgMatch[2], reg('background_image_url', 'image', bgMatch[2])));
        });
        const walker = doc.createTreeWalker(item, NodeFilter.SHOW_TEXT);
        const textNodes = [];
        while (walker.nextNode()) textNodes.push(walker.currentNode);
        textNodes.forEach(node => {
            const raw = node.nodeValue || '', trimmed = raw.trim(), parent = node.parentElement;
            if (!parent || !trimmed || parent.tagName === 'SCRIPT' || parent.tagName === 'STYLE') return;
            const key = inferTextBaseKey(parent, trimmed);
            node.nodeValue = raw.replace(trimmed, reg(key, inferType(key), trimmed, /_html$/i.test(key)));
        });
        return { itemTemplate: item.outerHTML, itemSchema, itemDefaults };
    };

    const applyGeneratedData = (generatedSchema, generatedDefaults, generatedTemplate = null) => {
        const shouldMerge = generateModeSelect?.value === 'merge';
        const curSchema = shouldMerge ? parseJsonObject(schemaInput?.value || '') : {};
        const curDefaults = shouldMerge ? parseJsonObject(defaultContentInput?.value || '') : {};
        if (schemaInput) schemaInput.value = JSON.stringify({ ...generatedSchema, ...curSchema }, null, 2);
        if (defaultContentInput) defaultContentInput.value = JSON.stringify({ ...generatedDefaults, ...curDefaults }, null, 2);
        if (generatedTemplate !== null) setHtmlValue(generatedTemplate);
        updateSchemaDiff();

        // Sync Alpine schema builder
        if (window.Alpine) {
            const alpineEl = document.querySelector('[x-data^="schemaBuilder"]');
            if (alpineEl?._x_dataStack?.[0]) {
                alpineEl._x_dataStack[0].loadFromObject(parseJsonObject(schemaInput?.value || ''));
            }
        }
    };

    const updateRepeatCandidateMeta = () => {
        if (!repeatCandidateMeta) return;
        const candidate = repeatCandidates[Number(repeatCandidateSelect?.value)];
        if (!candidate) { repeatCandidateMeta.classList.add('hidden'); repeatCandidateMeta.innerHTML = ''; return; }
        repeatCandidateMeta.classList.remove('hidden');
        repeatCandidateMeta.innerHTML = `
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-800">${escapeText(candidate.label)}</span>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-700">Güven: ${escapeText(candidate.confidence)}</span>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-700">${escapeText(String(candidate.count))} item</span>
            </div>
            <p class="mt-1">${escapeText(candidate.description)}</p>
            <p class="mt-2 text-amber-700">Placeholder: <code>${escapeText(createPlaceholderToken(`${repeatFieldKeyInput?.value || candidate.key}_html`, true))}</code></p>
            <details class="mt-2"><summary class="cursor-pointer font-semibold text-amber-800">İlk item HTML</summary>
            <pre class="mt-2 max-h-40 overflow-auto rounded bg-gray-950 p-2 text-[11px] text-amber-100">${escapeText(candidate.sampleHtml)}</pre></details>`;
    };

    const renderRepeatCandidates = () => {
        if (!repeatCandidateSelect) return;
        repeatCandidateSelect.innerHTML = '<option value="">Aday seç</option>';
        repeatCandidates.forEach((c, i) => {
            const opt = document.createElement('option');
            opt.value = String(i);
            opt.textContent = c.label;
            repeatCandidateSelect.appendChild(opt);
        });
        if (repeatCandidatePanel) repeatCandidatePanel.classList.remove('hidden');
        if (repeatCandidateHelp) repeatCandidateHelp.textContent = repeatCandidates.length > 0 ? 'Bir aday seçip anahtarı kontrol et, uygula dediğinde grup raw placeholder ile değişir.' : 'Tekrarlayan sibling grup bulunamadı.';
        if (repeatCandidates.length > 0 && repeatCandidateSelect) { repeatCandidateSelect.value = '0'; if (repeatFieldKeyInput) repeatFieldKeyInput.value = repeatCandidates[0].key; }
        updateRepeatCandidateMeta();
    };

    if (findRepeatCandidatesButton) findRepeatCandidatesButton.addEventListener('click', () => { repeatCandidates = findRepeatCandidates(); renderRepeatCandidates(); });
    if (repeatCandidateSelect) repeatCandidateSelect.addEventListener('change', () => { const c = repeatCandidates[Number(repeatCandidateSelect.value)]; if (repeatFieldKeyInput && c) repeatFieldKeyInput.value = c.key; updateRepeatCandidateMeta(); });
    if (repeatFieldKeyInput) repeatFieldKeyInput.addEventListener('input', updateRepeatCandidateMeta);

    if (applyRepeatCandidateButton) applyRepeatCandidateButton.addEventListener('click', () => {
        const candidate = repeatCandidates[Number(repeatCandidateSelect?.value)];
        if (!candidate) { window.alert('Önce repeat adayı seç.'); return; }
        const { root } = parseTemplateRoot();
        const parent = root ? resolveElementPath(root, candidate.parentPath) : null;
        if (!root || !parent) { window.alert('Repeat parent bulunamadı.'); return; }
        const items = Array.from(parent.children).filter(c => elementSignature(c) === candidate.signature);
        if (items.length < 2) { window.alert('Seçilen repeat grubu artık bulunamıyor.'); return; }
        const currentSchema = parseJsonObject(schemaInput?.value || '');
        const fieldKey = nextUniqueKey(normalizeKey(repeatFieldKeyInput?.value, candidate.key), currentSchema);
        const firstItem = buildRepeaterItem(items[0]);
        if (!firstItem) { window.alert('Repeat item dönüştürülemedi.'); return; }
        const defaultItems = items.map(item => buildRepeaterItem(item)?.itemDefaults || {});
        const placeholder = document.createTextNode(createPlaceholderToken(`${fieldKey}_html`, true));
        parent.insertBefore(placeholder, items[0]);
        items.forEach(item => item.remove());
        applyGeneratedData({ [fieldKey]: { type: 'repeater', label: labelize(fieldKey), repeat_kind: candidate.kind, item_template: firstItem.itemTemplate, fields: firstItem.itemSchema } }, { [fieldKey]: defaultItems }, root.innerHTML.trim());
    });

    // Manual repeat snippets
    const manualRepeatSnippets = {
        slides: `<div class="owl-item p-relative overflow-hidden">\n    <div class="container">\n        <h2>Slide Title</h2>\n        <p>Slide description.</p>\n        <a href="#start" class="btn btn-primary">Get Started</a>\n        <img class="img-fluid" src="img/demo/slide.jpg" alt="Slide image">\n    </div>\n</div>`,
        cards: `<div class="card border-0 box-shadow-1">\n    <img class="card-img-top" src="img/demo/card.jpg" alt="Card image">\n    <div class="card-body">\n        <h3 class="card-title">Card Title</h3>\n        <p class="card-text">Card description.</p>\n        <a href="#" class="btn btn-primary">Read More</a>\n    </div>\n</div>`,
        list_items: `<li class="d-flex align-items-start gap-2">\n    <i class="fa-solid fa-check text-primary"></i>\n    <span>List item text.</span>\n</li>`,
        features: `<div class="feature-box">\n    <div class="feature-box-icon"><img src="img/demo/icon.svg" alt="icon"></div>\n    <div class="feature-box-info">\n        <h3>Feature Title</h3>\n        <p>Feature description.</p>\n    </div>\n</div>`,
        gallery_items: `<div class="gallery-item">\n    <a href="img/demo/gallery-large.jpg">\n        <img class="img-fluid" src="img/demo/gallery-thumb.jpg" alt="Gallery image">\n    </a>\n</div>`,
        items: `<div class="item">\n    <h3>Item Title</h3>\n    <p>Item description.</p>\n    <a href="#">Item link</a>\n</div>`,
    };

    const fillManualRepeatSnippet = (force = false) => {
        const key = manualRepeatTypeSelect?.value || 'items';
        if (manualRepeatKeyInput && !manualRepeatKeyInput.value) manualRepeatKeyInput.value = key;
        if (!force && manualRepeatItemHtmlInput?.value.trim()) return;
        if (manualRepeatItemHtmlInput) manualRepeatItemHtmlInput.value = manualRepeatSnippets[key] || manualRepeatSnippets.items;
    };

    if (manualRepeatTypeSelect) manualRepeatTypeSelect.addEventListener('change', () => { if (manualRepeatKeyInput) manualRepeatKeyInput.value = manualRepeatTypeSelect.value; fillManualRepeatSnippet(false); });
    if (fillManualRepeatSnippetButton) fillManualRepeatSnippetButton.addEventListener('click', () => fillManualRepeatSnippet(true));
    fillManualRepeatSnippet(false);

    if (applyManualRepeatButton) applyManualRepeatButton.addEventListener('click', () => {
        const fieldKey = nextUniqueKey(normalizeKey(manualRepeatKeyInput?.value, manualRepeatTypeSelect?.value || 'items'), parseJsonObject(schemaInput?.value || ''));
        fillManualRepeatSnippet(false);
        const parser = new DOMParser();
        const doc = parser.parseFromString(`<div id="mr">${manualRepeatItemHtmlInput?.value || ''}</div>`, 'text/html');
        const item = doc.getElementById('mr')?.firstElementChild;
        if (!item) { window.alert('Manuel repeat için tek item HTML gir.'); return; }
        const built = buildRepeaterItem(item);
        if (!built) { window.alert('Manuel repeat item dönüştürülemedi.'); return; }
        insertAtHtmlCursor(createPlaceholderToken(`${fieldKey}_html`, true));
        applyGeneratedData({ [fieldKey]: { type: 'repeater', label: labelize(fieldKey), repeat_kind: manualRepeatTypeSelect?.value || 'items', item_template: built.itemTemplate, fields: built.itemSchema } }, { [fieldKey]: [built.itemDefaults] });
    });

    // ────────────────────────────────────────────────────
    // HTML → Template transform
    // ────────────────────────────────────────────────────

    const inferRepeatPrefix = (el) => {
        let current = el;
        while (current && current.parentElement) {
            const parent = current.parentElement;
            const siblings = Array.from(parent.children).filter(c => c.tagName === current.tagName && c.className === current.className);
            if (siblings.length > 1) {
                const idx = siblings.indexOf(current) + 1;
                const cn = current.className || '';
                const base = /slide|owl-item/i.test(cn) ? 'slide' : /card|item/i.test(cn) ? 'item' : 'group';
                return `${base}_${idx}_`;
            }
            current = parent;
        }
        return '';
    };

    const transformRawHtmlToTemplate = (template) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(`<div id="template-root">${template}</div>`, 'text/html');
        const root = doc.getElementById('template-root');
        if (!root) return null;
        const schema = {}, defaults = {};
        const reg = (baseKey, type, dv, raw = false) => {
            const key = nextUniqueKey(baseKey, schema);
            schema[key] = { type, label: labelize(key) };
            defaults[key] = dv;
            return createPlaceholderToken(key, raw);
        };
        root.querySelectorAll('*').forEach(el => {
            if (el.tagName === 'SCRIPT' || el.tagName === 'STYLE') return;
            const rp = inferRepeatPrefix(el);
            if (el.hasAttribute('href')) el.setAttribute('href', reg(`${rp}${el.tagName.toLowerCase() === 'a' ? 'button_url' : 'link_url'}`, 'text', el.getAttribute('href') || '#'));
            if (el.hasAttribute('src')) { const k = `${rp}${el.tagName.toLowerCase() === 'img' ? 'image_url' : 'media_url'}`; el.setAttribute('src', reg(k, 'image', el.getAttribute('src') || inferDefaultValue(k, 'image'))); }
            if (el.hasAttribute('alt')) el.setAttribute('alt', reg(`${rp}image_alt`, 'text', el.getAttribute('alt') || 'Görsel açıklaması'));
            const style = el.getAttribute('style') || '';
            const bgMatch = style.match(/background-image\s*:\s*url\((['"]?)(.*?)\1\)/i);
            if (bgMatch?.[2]) el.setAttribute('style', style.replace(bgMatch[2], reg(`${rp}background_image_url`, 'image', bgMatch[2])));
        });
        const walker = doc.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        const textNodes = [];
        while (walker.nextNode()) textNodes.push(walker.currentNode);
        textNodes.forEach(node => {
            const raw = node.nodeValue || '', trimmed = raw.trim(), parent = node.parentElement;
            if (!parent || !trimmed || parent.tagName === 'SCRIPT' || parent.tagName === 'STYLE') return;
            const rp = inferRepeatPrefix(parent);
            const baseKey = `${rp}${inferTextBaseKey(parent, trimmed)}`;
            node.nodeValue = raw.replace(trimmed, reg(baseKey, inferType(baseKey), trimmed, /_html$/i.test(baseKey)));
        });
        return { template: root.innerHTML.trim(), schema, defaults };
    };

    if (generateButton) generateButton.addEventListener('click', () => {
        const template = getHtmlValue();
        placeholderRegex.lastIndex = 0;
        if (hasPlaceholders(template)) {
            const { schema, defaults } = (() => {
                const s = {}, d = {};
                extractPlaceholders(template)
                    .filter(key => !isSystemPlaceholder(key))
                    .forEach(key => { const t = inferType(key); s[key] = { type: t, label: labelize(key) }; d[key] = inferDefaultValue(key, t); });
                return { schema: s, defaults: d };
            })();
            applyGeneratedData(schema, defaults);
            return;
        }
        const transformed = transformRawHtmlToTemplate(template);
        if (!transformed) { window.alert('HTML dönüştürülemedi.'); return; }
        applyGeneratedData(transformed.schema, transformed.defaults, transformed.template);
    });
});
</script>
@endpush
