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
                lineWrapping: true,
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
    const undoConversionButton = document.getElementById('undo_conversion');
    const diffPanel = document.getElementById('html_schema_diff');
    const diffOkBadge = document.getElementById('diff_ok_badge');
    const diffMissingSchemaWrapper = document.getElementById('diff_missing_schema_wrapper');
    const diffMissingSchema = document.getElementById('diff_missing_schema');
    const diffUnusedSchemaWrapper = document.getElementById('diff_unused_schema_wrapper');
    const diffUnusedSchema = document.getElementById('diff_unused_schema');
    const findAssetFieldsButton = document.getElementById('find_asset_fields');
    const assetFieldPanel = document.getElementById('asset_field_panel');
    const assetFieldList = document.getElementById('asset_field_list');
    const assetFieldHelp = document.getElementById('asset_field_help');
    const applyAllAssetFieldsButton = document.getElementById('apply_all_asset_fields');
    const generateDefaultsBtns = [
        document.getElementById('generate_defaults_btn'),
        document.getElementById('generate_defaults_btn2'),
    ].filter(Boolean);

    let repeatCandidates = [];
    let preConvert = null; // 'Şablona Dönüştür' öncesi anlık kopya (Dönüşümü Geri Al)

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

    // Sistem/menü placeholder'lari SCHEMA ALANI DEGILDIR — render aninda
    // SectionTemplateRenderer tarafindan site ayarlari + menulerden cozulur.
    // Bunlari schema'ya yakalamak (logo_url, menu_header_html gibi) sistem
    // degerini icerik default'uyla gecersiz kilar/kirletir. Disla.
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
        // Zaten placeholder içeren değer/metni TEKRAR kaydetme. Aksi halde
        // "Şablona Dönüştür" (düzleştirme) yapıldıktan SONRA "Repeat Alan Bul"
        // çalıştırılınca mevcut {{...}}'ler iç içe placeholder'a dönüşüp item
        // bozuluyor → frontend'de boş render. (Doğrusu: repeat'i ÖNCE çalıştır.)
        const hasPh = (v) => /\{\{.*?\}\}/.test(String(v || ''));
        item.querySelectorAll('*').forEach(el => {
            if (el.tagName === 'SCRIPT' || el.tagName === 'STYLE') return;
            if (el.hasAttribute('href') && !hasPh(el.getAttribute('href'))) el.setAttribute('href', reg(el.tagName.toLowerCase() === 'a' ? 'button_url' : 'link_url', 'text', el.getAttribute('href') || '#'));
            if (el.hasAttribute('src') && !hasPh(el.getAttribute('src'))) { const k = el.tagName.toLowerCase() === 'img' ? 'image_url' : 'media_url'; el.setAttribute('src', reg(k, inferType(k), el.getAttribute('src') || inferDefaultValue(k, 'image'))); }
            if (el.hasAttribute('alt') && !hasPh(el.getAttribute('alt'))) el.setAttribute('alt', reg('image_alt', 'text', el.getAttribute('alt') || 'Görsel açıklaması'));
            const style = el.getAttribute('style') || '';
            const bgMatch = style.match(/background-image\s*:\s*url\((['"]?)(.*?)\1\)/i);
            if (bgMatch?.[2] && !hasPh(bgMatch[2])) el.setAttribute('style', style.replace(bgMatch[2], reg('background_image_url', 'image', bgMatch[2])));
        });
        const walker = doc.createTreeWalker(item, NodeFilter.SHOW_TEXT);
        const textNodes = [];
        while (walker.nextNode()) textNodes.push(walker.currentNode);
        textNodes.forEach(node => {
            const raw = node.nodeValue || '', trimmed = raw.trim(), parent = node.parentElement;
            if (!parent || !trimmed || parent.tagName === 'SCRIPT' || parent.tagName === 'STYLE') return;
            if (hasPh(trimmed)) return; // zaten placeholder → tekrar kaydetme
            const key = inferTextBaseKey(parent, trimmed);
            node.nodeValue = raw.replace(trimmed, reg(key, inferType(key), trimmed, /_html$/i.test(key)));
        });
        return { itemTemplate: item.outerHTML, itemSchema, itemDefaults };
    };

    const applyGeneratedData = (generatedSchema, generatedDefaults, generatedTemplate = null) => {
        // Template değişiyorsa (tam dönüşüm / repeat uygula) ÖNCEKİ hali sakla →
        // "Dönüşümü Geri Al" ile oturum içinde geri dönülebilsin.
        if (generatedTemplate !== null) {
            preConvert = {
                html: getHtmlValue(),
                schema: schemaInput?.value || '',
                defaults: defaultContentInput?.value || '',
            };
            if (undoConversionButton) undoConversionButton.classList.remove('hidden');
        }
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

    // Bir repeat adayını verilen anahtarla uygular (manuel buton + sihirbaz ortak).
    // Başarılıysa true; grup bulunamazsa false.
    const applyRepeatCandidateWith = (candidate, rawKey) => {
        if (!candidate) return false;
        const { root } = parseTemplateRoot();
        const parent = root ? resolveElementPath(root, candidate.parentPath) : null;
        if (!root || !parent) return false;
        const items = Array.from(parent.children).filter(c => elementSignature(c) === candidate.signature);
        if (items.length < 2) return false;
        const currentSchema = parseJsonObject(schemaInput?.value || '');
        const fieldKey = nextUniqueKey(normalizeKey(rawKey, candidate.key), currentSchema);
        const firstItem = buildRepeaterItem(items[0]);
        if (!firstItem) return false;
        const defaultItems = items.map(item => buildRepeaterItem(item)?.itemDefaults || {});
        const placeholder = document.createTextNode(createPlaceholderToken(`${fieldKey}_html`, true));
        parent.insertBefore(placeholder, items[0]);
        items.forEach(item => item.remove());
        applyGeneratedData({ [fieldKey]: { type: 'repeater', label: labelize(fieldKey), repeat_kind: candidate.kind, item_template: firstItem.itemTemplate, fields: firstItem.itemSchema } }, { [fieldKey]: defaultItems }, root.innerHTML.trim());
        return true;
    };

    if (applyRepeatCandidateButton) applyRepeatCandidateButton.addEventListener('click', () => {
        const candidate = repeatCandidates[Number(repeatCandidateSelect?.value)];
        if (!candidate) { window.alert('Önce repeat adayı seç.'); return; }
        if (! applyRepeatCandidateWith(candidate, repeatFieldKeyInput?.value)) {
            window.alert('Repeat uygulanamadı — grup bulunamadı veya HTML değişmiş. Tekrar "Repeat Alan Bul" de.');
        }
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
    // Eksik görsel/asset alanı analizörü
    // Ana şablonu VE her repeater item_template'ini tarar; placeholder OLMAYAN
    // sabit görsel referanslarını (inline style url() — background kısayolu
    // dahil, img/source [src], video [poster], [data-src]) bulup tek tıkla
    // düzenlenebilir "image" alanına çevirir (token + şema alanı + default).
    // ────────────────────────────────────────────────────

    let assetCandidates = [];

    const IMG_EXT_RE = /\.(jpe?g|png|gif|webp|avif|svg|bmp|ico)(\?|#|$)/i;

    const containsPlaceholder = (v) => { placeholderRegex.lastIndex = 0; return placeholderRegex.test(String(v ?? '')); };

    // bir değer düzenlemeye değer bir varlık mı? (zaten placeholder / data: / css
    // değişkeni olanları dışla; uzantı veya bilinen asset yolu/URL ara)
    const looksLikeAsset = (v) => {
        const s = String(v ?? '').trim();
        if (!s || containsPlaceholder(s)) return false;
        if (/^data:/i.test(s) || /^var\(/i.test(s) || /^#/.test(s)) return false;
        return IMG_EXT_RE.test(s) || /^(https?:)?\/\//i.test(s)
            || /^\/?assets\//i.test(s) || /^\/?(img|images|uploads|media|storage)\//i.test(s);
    };

    // CSS metnindeki url(...) referanslarını çıkar
    const extractCssUrls = (cssText) => {
        const out = [], re = /url\(\s*(['"]?)([^'")]+)\1\s*\)/gi;
        let m;
        while ((m = re.exec(cssText)) !== null) out.push({ match: m[0], inner: m[2].trim() });
        return out;
    };

    // Bir DOM kökünü tarar; placeholder olmayan sabit görselleri döndürür.
    const scanDomForAssets = (root, scopeInfo) => {
        const found = [];
        root.querySelectorAll('*').forEach(el => {
            if (el.tagName === 'SCRIPT' || el.tagName === 'STYLE') return;
            const path = getElementPath(el, root);
            if (path.length === 0) return;
            ['src', 'poster', 'data-src'].forEach(attr => {
                if (!el.hasAttribute(attr)) return;
                const v = el.getAttribute(attr);
                if (looksLikeAsset(v)) {
                    found.push({ ...scopeInfo, kind: 'attr', attr, prop: `${el.tagName.toLowerCase()} [${attr}]`, currentValue: v, path });
                }
            });
            const style = el.getAttribute('style') || '';
            if (style) extractCssUrls(style).forEach(u => {
                if (looksLikeAsset(u.inner)) {
                    found.push({ ...scopeInfo, kind: 'style-url', prop: 'style url()', currentValue: u.inner, path });
                }
            });
        });
        return found;
    };

    const findAssetCandidates = () => {
        const out = [];
        const { root } = parseTemplateRoot();
        if (root) scanDomForAssets(root, { scope: 'main', scopeLabel: 'Ana şablon', repeaterKey: null }).forEach(c => out.push(c));
        const schema = parseJsonObject(schemaInput?.value || '');
        Object.entries(schema).forEach(([key, field]) => {
            if (!field || field.type !== 'repeater' || !field.item_template) return;
            const doc = new DOMParser().parseFromString(`<div id="ri">${field.item_template}</div>`, 'text/html');
            const riRoot = doc.getElementById('ri');
            if (riRoot) scanDomForAssets(riRoot, { scope: 'repeater', scopeLabel: `Repeater: ${key}`, repeaterKey: key }).forEach(c => out.push(c));
        });
        return out;
    };

    const suggestAssetKey = (cand, targetSchema) => {
        let base = 'image';
        if (cand.kind === 'attr' && cand.attr === 'poster') base = 'poster';
        else if (cand.kind === 'style-url') base = cand.scope === 'repeater' ? 'image' : 'background_image';
        return nextUniqueKey(normalizeKey(base, 'image'), targetSchema || {});
    };

    const withSuggested = (cand) => {
        const schema = parseJsonObject(schemaInput?.value || '');
        const target = cand.scope === 'repeater' ? (schema[cand.repeaterKey]?.fields || {}) : schema;
        return { ...cand, suggestedKey: suggestAssetKey(cand, target) };
    };

    // Bulunan değeri elemanda token ile değiştir (attr veya style url içi).
    const applyTokenToElement = (el, cand, token) => {
        if (cand.kind === 'attr') {
            if (el.getAttribute(cand.attr) == null) return false;
            el.setAttribute(cand.attr, token);
            return true;
        }
        const style = el.getAttribute('style') || '';
        if (style.includes(cand.currentValue)) {
            el.setAttribute('style', style.replace(cand.currentValue, token));
            return true;
        }
        return false;
    };

    const syncAlpineSchema = () => {
        if (!window.Alpine) return;
        const alpineEl = document.querySelector('[x-data^="schemaBuilder"]');
        if (alpineEl?._x_dataStack?.[0]) alpineEl._x_dataStack[0].loadFromObject(parseJsonObject(schemaInput?.value || ''));
    };

    // Tek adayı uygula: token yerleştir + image alanı + default değer.
    // Repeater ise alanı repeater.fields'e ekler ve TÜM default item'lara değeri
    // koyar (mevcut görünümü korur; sonra her slayt ayrı düzenlenebilir).
    const applyAssetField = (cand, rawKey) => {
        const schema = parseJsonObject(schemaInput?.value || '');
        const defaults = parseJsonObject(defaultContentInput?.value || '');

        if (cand.scope === 'main') {
            const { root } = parseTemplateRoot();
            const el = root ? resolveElementPath(root, cand.path) : null;
            if (!root || !el) { window.alert('Görsel elemanı şablonda bulunamadı (HTML değişmiş olabilir). Tekrar tara.'); return false; }
            const key = nextUniqueKey(normalizeKey(rawKey, 'image'), schema);
            if (!applyTokenToElement(el, cand, createPlaceholderToken(key, false))) { window.alert('Görsel değeri elemanda bulunamadı. Tekrar tara.'); return false; }
            schema[key] = { type: 'image', label: labelize(key) };
            defaults[key] = cand.currentValue;
            if (schemaInput) schemaInput.value = JSON.stringify(schema, null, 2);
            if (defaultContentInput) defaultContentInput.value = JSON.stringify(defaults, null, 2);
            setHtmlValue(root.innerHTML.trim());
        } else {
            const field = schema[cand.repeaterKey];
            if (!field || field.type !== 'repeater' || !field.item_template) { window.alert('Repeater bulunamadı. Tekrar tara.'); return false; }
            const doc = new DOMParser().parseFromString(`<div id="ri">${field.item_template}</div>`, 'text/html');
            const riRoot = doc.getElementById('ri');
            const el = riRoot ? resolveElementPath(riRoot, cand.path) : null;
            if (!riRoot || !el) { window.alert('Görsel elemanı item_template içinde bulunamadı. Tekrar tara.'); return false; }
            field.fields = field.fields || {};
            const key = nextUniqueKey(normalizeKey(rawKey, 'image'), field.fields);
            if (!applyTokenToElement(el, cand, createPlaceholderToken(key, false))) { window.alert('Görsel değeri item_template içinde bulunamadı. Tekrar tara.'); return false; }
            field.fields[key] = { type: 'image', label: labelize(key) };
            field.item_template = riRoot.innerHTML.trim();
            if (Array.isArray(defaults[cand.repeaterKey])) {
                defaults[cand.repeaterKey] = defaults[cand.repeaterKey].map(it => {
                    const item = (it && typeof it === 'object' && !Array.isArray(it)) ? { ...it } : {};
                    if (item[key] === undefined) item[key] = cand.currentValue;
                    return item;
                });
            }
            if (schemaInput) schemaInput.value = JSON.stringify(schema, null, 2);
            if (defaultContentInput) defaultContentInput.value = JSON.stringify(defaults, null, 2);
        }

        updateSchemaDiff();
        syncAlpineSchema();
        window.dispatchEvent(new CustomEvent('section-template-editor-change'));
        return true;
    };

    const renderAssetCandidates = () => {
        if (!assetFieldPanel || !assetFieldList) return;
        assetFieldPanel.classList.remove('hidden');
        assetFieldList.innerHTML = '';
        if (assetCandidates.length === 0) {
            if (assetFieldHelp) assetFieldHelp.textContent = 'Şemaya bağlı olmayan sabit görsel bulunamadı — tüm görseller zaten alan/placeholder.';
            applyAllAssetFieldsButton?.classList.add('hidden');
            return;
        }
        if (assetFieldHelp) assetFieldHelp.textContent = `${assetCandidates.length} sabit görsel bulundu. Anahtarı kontrol edip "Alan Yap" de — token + image şeması + default eklenir.`;
        applyAllAssetFieldsButton?.classList.remove('hidden');
        assetCandidates.forEach((cand, i) => {
            const row = document.createElement('div');
            row.className = 'asset-field-row rounded-lg border border-sky-200 bg-white px-3 py-2';
            const shown = cand.currentValue.length > 64 ? cand.currentValue.slice(0, 61) + '…' : cand.currentValue;
            row.innerHTML = `
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-sky-100 px-2 py-0.5 font-semibold text-sky-800">${escapeText(cand.scopeLabel)}</span>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-700">${escapeText(cand.prop)}</span>
                    <code class="text-gray-600">${escapeText(shown)}</code>
                </div>
                <div class="mt-2 flex flex-wrap items-end gap-2">
                    <div>
                        <label class="mb-1 block font-medium">Alan anahtarı</label>
                        <input type="text" class="asset-field-key w-44 rounded-lg border border-sky-300 bg-white px-2 py-1.5 text-xs focus:ring-2 focus:ring-sky-500" value="${escapeText(cand.suggestedKey)}">
                    </div>
                    <button type="button" class="asset-field-apply inline-flex items-center gap-1.5 rounded-lg bg-sky-100 px-3 py-2 text-xs font-medium text-sky-800 hover:bg-sky-200" data-idx="${i}">
                        <i class="fas fa-plus"></i> Alan Yap
                    </button>
                </div>`;
            assetFieldList.appendChild(row);
        });
        assetFieldList.querySelectorAll('.asset-field-apply').forEach(btn => {
            btn.addEventListener('click', () => {
                const cand = assetCandidates[Number(btn.dataset.idx)];
                if (!cand) return;
                const keyName = btn.closest('.asset-field-row')?.querySelector('.asset-field-key')?.value || cand.suggestedKey;
                if (applyAssetField(cand, keyName)) {
                    assetCandidates = findAssetCandidates().map(withSuggested);
                    renderAssetCandidates();
                }
            });
        });
    };

    if (findAssetFieldsButton) findAssetFieldsButton.addEventListener('click', () => {
        assetCandidates = findAssetCandidates().map(withSuggested);
        renderAssetCandidates();
    });

    if (applyAllAssetFieldsButton) applyAllAssetFieldsButton.addEventListener('click', () => {
        if (assetCandidates.length === 0) return;
        const keyByIdx = {};
        assetFieldList?.querySelectorAll('.asset-field-apply').forEach(btn => {
            keyByIdx[Number(btn.dataset.idx)] = btn.closest('.asset-field-row')?.querySelector('.asset-field-key')?.value;
        });
        let applied = 0;
        assetCandidates.slice().forEach((cand, i) => { if (applyAssetField(cand, keyByIdx[i] || cand.suggestedKey)) applied++; });
        assetCandidates = findAssetCandidates().map(withSuggested);
        renderAssetCandidates();
        if (applied > 0) window.alert(`${applied} görsel alanı eklendi.`);
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
        // Zaten {{...}} içeren değer/metni TEKRAR sarma — repeater collapse'tan
        // sonra ({{{x_html}}} varken) bu fonksiyon çalışınca placeholder'ı bozmasın.
        const hasPh = (v) => /\{\{.*?\}\}/.test(String(v || ''));
        root.querySelectorAll('*').forEach(el => {
            if (el.tagName === 'SCRIPT' || el.tagName === 'STYLE') return;
            const rp = inferRepeatPrefix(el);
            if (el.hasAttribute('href') && !hasPh(el.getAttribute('href'))) el.setAttribute('href', reg(`${rp}${el.tagName.toLowerCase() === 'a' ? 'button_url' : 'link_url'}`, 'text', el.getAttribute('href') || '#'));
            if (el.hasAttribute('src') && !hasPh(el.getAttribute('src'))) { const k = `${rp}${el.tagName.toLowerCase() === 'img' ? 'image_url' : 'media_url'}`; el.setAttribute('src', reg(k, 'image', el.getAttribute('src') || inferDefaultValue(k, 'image'))); }
            if (el.hasAttribute('alt') && !hasPh(el.getAttribute('alt'))) el.setAttribute('alt', reg(`${rp}image_alt`, 'text', el.getAttribute('alt') || 'Görsel açıklaması'));
            const style = el.getAttribute('style') || '';
            const bgMatch = style.match(/background-image\s*:\s*url\((['"]?)(.*?)\1\)/i);
            if (bgMatch?.[2] && !hasPh(bgMatch[2])) el.setAttribute('style', style.replace(bgMatch[2], reg(`${rp}background_image_url`, 'image', bgMatch[2])));
        });
        const walker = doc.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        const textNodes = [];
        while (walker.nextNode()) textNodes.push(walker.currentNode);
        textNodes.forEach(node => {
            const raw = node.nodeValue || '', trimmed = raw.trim(), parent = node.parentElement;
            if (!parent || !trimmed || parent.tagName === 'SCRIPT' || parent.tagName === 'STYLE') return;
            if (hasPh(trimmed)) return; // zaten placeholder
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

    // Dönüşümü Geri Al — dönüşümden hemen önceki HTML + şema + içerik haline döner
    // (oturum içi; kaydetmeden). Kalıcı geri dönüş için sağdaki Versiyon Geçmişi.
    if (undoConversionButton) undoConversionButton.addEventListener('click', () => {
        if (!preConvert) return;
        if (!window.confirm('Şablona Dönüştür öncesi HTML + şema + içerik geri yüklenecek (kaydetmeden). Devam?')) return;
        setHtmlValue(preConvert.html);
        if (schemaInput) schemaInput.value = preConvert.schema;
        if (defaultContentInput) defaultContentInput.value = preConvert.defaults;
        if (window.Alpine) {
            const alpineEl = document.querySelector('[x-data^="schemaBuilder"]');
            if (alpineEl?._x_dataStack?.[0]) alpineEl._x_dataStack[0].loadFromObject(parseJsonObject(schemaInput?.value || ''));
        }
        updateSchemaDiff();
        preConvert = null;
        undoConversionButton.classList.add('hidden');
        window.dispatchEvent(new CustomEvent('section-template-editor-change'));
    });

    // ════════════════════════════════════════════════════════════════════
    // ŞABLONA DÖNÜŞTÜRME SİHİRBAZI (adım adım, önizlemeli, atlanabilir)
    //   1) Tekrar grupları → repeater   2) Şablona Dönüştür (metin+link+görsel)
    //   3) Eksik görsel (kalan)          → Özet
    // Canlı editör üstünde çalışır; açılışta snapshot alır, İptal'de geri yükler.
    // Mevcut tespit/uygula fonksiyonlarını yeniden kullanır. En sonda + try/catch
    // ile izole — bir hata diğer butonları (yukarıda bağlandı) etkilemez.
    // ════════════════════════════════════════════════════════════════════
    const cwModal = document.getElementById('convert_wizard');
    if (cwModal) {
        const cwBody   = document.getElementById('cw_body');
        const cwTitle  = document.getElementById('cw_title');
        const cwSkip   = document.getElementById('cw_skip');
        const cwNext   = document.getElementById('cw_next');
        const cwCancel = document.getElementById('cw_cancel');
        const cwCloseBtn = document.getElementById('cw_close');
        const cwOpenBtn = document.getElementById('open_convert_wizard');

        let cwStep = 1;
        let cwSnap = null;
        let cwRepCands = [];
        let cwTextPlan = null;
        let cwAssetCands = [];

        const cwRestore = () => {
            if (!cwSnap) return;
            setHtmlValue(cwSnap.html);
            if (schemaInput) schemaInput.value = cwSnap.schema;
            if (defaultContentInput) defaultContentInput.value = cwSnap.defaults;
            syncAlpineSchema(); updateSchemaDiff();
        };
        const cwHide = () => { cwModal.classList.add('hidden'); cwModal.classList.remove('flex'); };
        const cwOpen = () => {
            cwSnap = { html: getHtmlValue(), schema: schemaInput?.value || '', defaults: defaultContentInput?.value || '' };
            cwStep = 1;
            cwModal.classList.remove('hidden');
            cwModal.classList.add('flex');
            cwRender();
        };

        const cwSchemaRows = () => {
            const s = parseJsonObject(schemaInput?.value || '');
            return Object.entries(s).map(([k, f]) => ({ key: k, type: (f && f.type) || 'text', sub: (f && f.type === 'repeater' && f.fields) ? Object.keys(f.fields) : null }));
        };

        // ── Adım 1: Tekrar ───────────────────────────────────────────────
        const cwRenderRepeat = () => {
            cwTitle.textContent = '1/3 · Tekrar Alanları (slider / liste / kart)';
            cwRepCands = findRepeatCandidates();
            if (!cwRepCands.length) {
                cwBody.innerHTML = '<div class="py-8 text-center text-sm text-gray-400"><i class="fas fa-circle-info mr-1"></i> Tekrar eden grup bulunamadı. <strong>İleri</strong> ile devam.</div>';
                cwNext.textContent = 'İleri →';
                return;
            }
            cwBody.innerHTML = '<p class="mb-3 text-xs text-gray-500">Tekrar eden gruplar tek bir çoğaltılabilir (repeater) alana iner. İşaretle, anahtarı düzenle.</p>' +
                cwRepCands.map((c, i) => {
                    let fields = [];
                    try {
                        const el = new DOMParser().parseFromString('<div id="cwx">' + c.sampleHtml + '</div>', 'text/html').getElementById('cwx');
                        const first = el ? el.firstElementChild : null;
                        const built = first ? buildRepeaterItem(first) : null;
                        fields = built ? Object.keys(built.itemSchema) : [];
                    } catch (_) {}
                    return '<label class="mb-2 flex items-start gap-2 rounded-lg border border-gray-200 p-2.5 hover:bg-gray-50">' +
                        '<input type="checkbox" class="cw-rep mt-1" data-i="' + i + '"' + (c.priority >= 70 ? ' checked' : '') + '>' +
                        '<div class="min-w-0 flex-1">' +
                            '<div class="text-xs font-medium text-gray-800">' + escapeText(c.label) + '</div>' +
                            '<div class="mt-1.5 flex items-center gap-1.5"><span class="text-[11px] text-gray-400">anahtar:</span>' +
                            '<input type="text" class="cw-rep-key w-44 rounded border border-gray-300 px-1.5 py-0.5 text-xs" data-i="' + i + '" value="' + escapeText(c.key) + '"></div>' +
                            (fields.length ? '<div class="mt-1 text-[11px] text-gray-400">alanlar: ' + escapeText(fields.join(', ')) + '</div>' : '') +
                        '</div></label>';
                }).join('');
            cwNext.textContent = 'Uygula ve Devam →';
        };
        const cwApplyRepeat = () => {
            const checks = Array.from(cwBody.querySelectorAll('.cw-rep:checked'));
            checks.forEach(chk => {
                const i = Number(chk.dataset.i);
                const sel = cwRepCands[i];
                if (!sel) return;
                const keyInput = cwBody.querySelector('.cw-rep-key[data-i="' + i + '"]');
                // Önceki apply DOM'u kaydırmış olabilir → taze tespitte imzayla eşleştir.
                const fresh = findRepeatCandidates().find(c => c.signature === sel.signature && c.parentSignature === sel.parentSignature);
                if (fresh) applyRepeatCandidateWith(fresh, (keyInput && keyInput.value) || sel.key);
            });
        };

        // ── Adım 2: Şablona Dönüştür (metin+link+görsel) ─────────────────
        const cwRenderText = () => {
            cwTitle.textContent = '2/3 · Metin & Görsel Alanları';
            if (generateModeSelect) generateModeSelect.value = 'merge'; // repeater'ı koru
            cwTextPlan = transformRawHtmlToTemplate(getHtmlValue());
            const keys = cwTextPlan ? Object.keys(cwTextPlan.schema) : [];
            if (!keys.length) {
                cwBody.innerHTML = '<div class="py-8 text-center text-sm text-gray-400"><i class="fas fa-circle-info mr-1"></i> Alanlaştırılacak statik metin/görsel kalmadı. <strong>İleri</strong>.</div>';
                cwNext.textContent = 'İleri →';
                return;
            }
            cwBody.innerHTML = '<p class="mb-3 text-xs text-gray-500">Statik metin, link ve görseller düzenlenebilir alanlara çevrilecek. Adların ince ayarını sonra <strong>Şema Alanları</strong>\'ndan yapabilirsin.</p>' +
                '<div class="space-y-1">' + keys.map(k => {
                    const t = (cwTextPlan.schema[k] && cwTextPlan.schema[k].type) || 'text';
                    const dv = String(cwTextPlan.defaults[k] == null ? '' : cwTextPlan.defaults[k]);
                    return '<div class="flex items-center gap-2 rounded border border-gray-100 px-2 py-1 text-xs">' +
                        '<span class="font-mono text-indigo-700">' + escapeText(k) + '</span>' +
                        '<span class="rounded bg-gray-100 px-1.5 text-[10px] text-gray-500">' + escapeText(t) + '</span>' +
                        '<span class="ml-auto truncate text-gray-400" style="max-width:55%">' + escapeText(dv.slice(0, 60)) + '</span>' +
                    '</div>';
                }).join('') + '</div>';
            cwNext.textContent = 'Uygula (' + keys.length + ' alan) ve Devam →';
        };
        const cwApplyText = () => {
            if (cwTextPlan && Object.keys(cwTextPlan.schema).length) {
                if (generateModeSelect) generateModeSelect.value = 'merge';
                applyGeneratedData(cwTextPlan.schema, cwTextPlan.defaults, cwTextPlan.template);
            }
        };

        // ── Adım 3: Eksik görsel ─────────────────────────────────────────
        const cwRenderAsset = () => {
            cwTitle.textContent = '3/3 · Eksik Görseller';
            cwAssetCands = findAssetCandidates().map(withSuggested);
            if (!cwAssetCands.length) {
                cwBody.innerHTML = '<div class="py-8 text-center text-sm text-gray-400"><i class="fas fa-circle-check mr-1 text-green-400"></i> Şemaya bağlı olmayan sabit görsel yok. <strong>İleri</strong> → özet.</div>';
                cwNext.textContent = 'İleri →';
                return;
            }
            cwBody.innerHTML = '<p class="mb-3 text-xs text-gray-500">Hâlâ alana bağlı olmayan sabit görseller. İşaretle, anahtarı düzenle.</p>' +
                cwAssetCands.map((c, i) =>
                    '<label class="mb-2 flex items-start gap-2 rounded-lg border border-gray-200 p-2.5 hover:bg-gray-50">' +
                    '<input type="checkbox" class="cw-asset mt-1" data-i="' + i + '" checked>' +
                    '<div class="min-w-0 flex-1">' +
                        '<div class="text-xs text-gray-700">' + escapeText(c.scopeLabel) + ' · ' + escapeText(c.prop) + '</div>' +
                        '<div class="truncate text-[11px] text-gray-400">' + escapeText(c.currentValue) + '</div>' +
                        '<div class="mt-1.5 flex items-center gap-1.5"><span class="text-[11px] text-gray-400">anahtar:</span>' +
                        '<input type="text" class="cw-asset-key w-44 rounded border border-gray-300 px-1.5 py-0.5 text-xs" data-i="' + i + '" value="' + escapeText(c.suggestedKey) + '"></div>' +
                    '</div></label>').join('');
            cwNext.textContent = 'Uygula ve Devam →';
        };
        const cwApplyAsset = () => {
            const checks = Array.from(cwBody.querySelectorAll('.cw-asset:checked'));
            checks.forEach(chk => {
                const i = Number(chk.dataset.i);
                const cand = cwAssetCands[i];
                if (!cand) return;
                const keyInput = cwBody.querySelector('.cw-asset-key[data-i="' + i + '"]');
                applyAssetField(cand, (keyInput && keyInput.value) || cand.suggestedKey);
            });
        };

        // ── Özet ─────────────────────────────────────────────────────────
        const cwRenderSummary = () => {
            cwTitle.textContent = 'Özet · Oluşan Alanlar';
            const rows = cwSchemaRows();
            cwBody.innerHTML = rows.length
                ? '<p class="mb-3 text-xs text-gray-500">Şema alanları (Bitir\'e basınca editöre + şemaya işlenir):</p><div class="space-y-1">' +
                    rows.map(r => '<div class="flex items-center gap-2 rounded border border-gray-100 px-2 py-1 text-xs">' +
                        '<span class="font-mono text-indigo-700">' + escapeText(r.key) + '</span>' +
                        '<span class="rounded bg-gray-100 px-1.5 text-[10px] text-gray-500">' + escapeText(r.type) + '</span>' +
                        (r.sub ? '<span class="text-[11px] text-gray-400">→ ' + escapeText(r.sub.join(', ')) + '</span>' : '') +
                    '</div>').join('') + '</div>'
                : '<div class="py-6 text-center text-sm text-gray-400">Hiç alan oluşmadı. İptal ile çıkabilirsin.</div>';
            cwNext.textContent = '✓ Bitir';
        };

        const cwRender = () => {
            if (cwSkip) cwSkip.classList.toggle('hidden', cwStep >= 4);
            if (cwStep === 1) cwRenderRepeat();
            else if (cwStep === 2) cwRenderText();
            else if (cwStep === 3) cwRenderAsset();
            else cwRenderSummary();
        };

        if (cwOpenBtn) cwOpenBtn.addEventListener('click', () => { try { cwOpen(); } catch (e) { console.error('[wizard] open', e); } });
        if (cwCancel) cwCancel.addEventListener('click', () => { cwRestore(); cwHide(); });
        if (cwCloseBtn) cwCloseBtn.addEventListener('click', () => { cwRestore(); cwHide(); });
        if (cwSkip) cwSkip.addEventListener('click', () => { cwStep++; cwRender(); });
        if (cwNext) cwNext.addEventListener('click', () => {
            try {
                if (cwStep === 1) cwApplyRepeat();
                else if (cwStep === 2) cwApplyText();
                else if (cwStep === 3) cwApplyAsset();
                else { cwHide(); return; } // Bitir — uygulanmış haliyle kapat
                cwStep++;
                cwRender();
            } catch (e) {
                console.error('[wizard] step', e);
                window.alert('Bu adım uygulanamadı: ' + (e && e.message ? e.message : e));
            }
        });
    }
});
</script>
@endpush
