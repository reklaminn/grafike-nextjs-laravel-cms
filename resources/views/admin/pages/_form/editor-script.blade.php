@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/foldgutter.min.css">
<style>
    /* Quill overrides inside the block settings modal */
    .ql-toolbar { border: none !important; border-bottom: 1px solid #e5e7eb !important; background: #f9fafb; padding: 6px 8px !important; }
    .ql-container { border: none !important; font-family: inherit; font-size: 0.875rem; }
    .ql-editor { min-height: 110px; padding: 10px 12px; }
    .ql-editor.ql-blank::before { color: #9ca3af; font-style: normal; }
    /* HTML Override CodeMirror */
    .html-override-cm .CodeMirror { height: 220px; font-size: 12px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; border: 1px solid #d1d5db; border-radius: 0.5rem; }
    .html-override-cm .CodeMirror-focused { border-color: transparent; box-shadow: 0 0 0 2px #f59e0b; }
    /* Katlama (fold) göstergeleri */
    .html-override-cm .CodeMirror-foldgutter { width: 14px; }
    .html-override-cm .CodeMirror-foldmarker { color: #b45309; background: #fef3c7; border-radius: 3px; padding: 0 4px; font-family: ui-sans-serif, system-ui, sans-serif; font-size: 11px; text-shadow: none; cursor: pointer; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/foldcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/foldgutter.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/xml-fold.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/brace-fold.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/comment-fold.min.js"></script>
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
        mediaSearchTimer: null,
        mediaCollection: '',
        mediaCollections: [],
        mediaLoading: false,        // ilk yükleme
        mediaLoadingMore: false,    // sonsuz kaydırma
        mediaPage: 1,
        mediaLastPage: 1,
        mediaTotal: 0,
        mediaUploading: false,
        mediaUploadError: '',
        mediaActive: null,          // sağ panelde gösterilen aktif öğe
        mediaDragOver: false,
        mediaAltDraft: '',
        mediaNameDraft: '',
        mediaSavingMeta: false,
        mediaRenaming: false,
        mediaGenAltLoading: false,
        mediaChosen: [],            // çoklu seçim: seçilen url'ler

        // Icon picker state
        iconPickerOpen: false,
        iconSearch: '',

        // Page link picker state
        pagePickerOpen: false,
        pageSearch: '',
        pagesLoading: false,
        pages: [],

        // Quill
        quillInstance: null,

        get type() {
            return (this.fieldSchema?.type || 'text');
        },

        // ── Media picker ────────────────────────────────────────────────────
        get mediaMultiple() {
            return this.fieldSchema?.multiple === true;
        },

        get mediaHasMore() {
            return this.mediaPage < this.mediaLastPage;
        },

        _csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        },

        openMediaPicker() {
            this.mediaPickerOpen = true;
            this.mediaSearch = '';
            this.mediaCollection = '';
            this.mediaUploadError = '';
            this.mediaActive = null;
            this.mediaDragOver = false;
            // Çoklu modda mevcut değer(ler)i ön-seç.
            this.mediaChosen = this.mediaMultiple && Array.isArray(this.parentRef[this.fieldKey])
                ? [...this.parentRef[this.fieldKey]]
                : [];
            this.loadMedia({ reset: true });
        },

        closeMediaPicker() {
            this.mediaPickerOpen = false;
        },

        // Arama + collection + sayfa parametreleriyle medya listesini çeker.
        // reset=true → ilk sayfa (listeyi sıfırla); false → sonraki sayfayı ekle.
        async loadMedia({ reset = true } = {}) {
            if (reset) {
                this.mediaPage = 1;
                this.mediaLoading = true;
            } else {
                if (this.mediaLoadingMore || !this.mediaHasMore) return;
                this.mediaLoadingMore = true;
                this.mediaPage += 1;
            }

            try {
                const params = new URLSearchParams({
                    type: 'image',
                    per_page: '40',
                    page: String(this.mediaPage),
                });
                if (this.mediaSearch) params.set('q', this.mediaSearch);
                if (this.mediaCollection) params.set('collection', this.mediaCollection);

                const resp = await fetch('/admin/media?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                });
                if (resp.ok) {
                    const json = await resp.json();
                    const items = json.data || [];
                    this.mediaItems = reset ? items : this.mediaItems.concat(items);
                    this.mediaLastPage = json.meta?.last_page ?? 1;
                    this.mediaTotal = json.meta?.total ?? this.mediaItems.length;
                    if (reset && Array.isArray(json.meta?.collections)) {
                        this.mediaCollections = json.meta.collections;
                    }
                    if (reset && !this.mediaActive && this.mediaItems.length) {
                        this.focusMedia(this.mediaItems[0]);
                    }
                }
            } catch (e) {
                console.error('[blockFieldInput] Media load error:', e);
            } finally {
                this.mediaLoading = false;
                this.mediaLoadingMore = false;
            }
        },

        // Debounce'lu server-side arama (yazarken 350ms bekler).
        onMediaSearchInput() {
            clearTimeout(this.mediaSearchTimer);
            this.mediaSearchTimer = setTimeout(() => this.loadMedia({ reset: true }), 350);
        },

        setMediaCollection(name) {
            this.mediaCollection = name;
            this.loadMedia({ reset: true });
        },

        // Grid altına yaklaşınca sonraki sayfayı yükle (sonsuz kaydırma).
        onMediaScroll(e) {
            const el = e.target;
            if (el.scrollTop + el.clientHeight >= el.scrollHeight - 240) {
                this.loadMedia({ reset: false });
            }
        },

        // Sağ panelde göster + alt/ad taslaklarını hazırla.
        focusMedia(item) {
            this.mediaActive = item;
            this.mediaAltDraft = item.alt_text || '';
            this.mediaNameDraft = item.name || item.file_name || '';
        },

        isChosen(item) {
            return this.mediaChosen.includes(item.url);
        },

        toggleChosen(item) {
            const i = this.mediaChosen.indexOf(item.url);
            if (i === -1) this.mediaChosen.push(item.url);
            else this.mediaChosen.splice(i, 1);
        },

        // Grid'de bir öğeye tıklama: çoklu modda seçimi değiştirir,
        // tekli modda sağ panele odaklar.
        onItemClick(item) {
            if (this.mediaMultiple) this.toggleChosen(item);
            else this.focusMedia(item);
        },

        // Çift tık / "Kullan": tekli modda alana ata + kapat.
        useMedia(item) {
            this.parentRef[this.fieldKey] = item.url || item.original_url || '';
            this.closeMediaPicker();
        },

        // Onay butonu — modaa göre tekli/çoklu.
        confirmSelection() {
            if (this.mediaMultiple) {
                this.parentRef[this.fieldKey] = [...this.mediaChosen];
            } else if (this.mediaActive) {
                this.parentRef[this.fieldKey] = this.mediaActive.url || '';
            }
            this.closeMediaPicker();
        },

        // Klavye navigasyonu: ok tuşları aktifi gezer, Enter kullanır.
        onMediaKey(e) {
            if (!this.mediaItems.length) return;
            const idx = this.mediaActive ? this.mediaItems.findIndex((m) => m.url === this.mediaActive.url) : -1;
            if (['ArrowRight', 'ArrowLeft', 'ArrowDown', 'ArrowUp'].includes(e.key)) {
                e.preventDefault();
                const step = (e.key === 'ArrowRight') ? 1 : (e.key === 'ArrowLeft') ? -1 : (e.key === 'ArrowDown') ? 5 : -5;
                const next = Math.min(Math.max(idx + step, 0), this.mediaItems.length - 1);
                this.focusMedia(this.mediaItems[next]);
                // yakına kayan görseli görünür kıl
                this.$nextTick(() => document.getElementById('media-cell-' + this.mediaItems[next].id)?.scrollIntoView({ block: 'nearest' }));
            } else if (e.key === 'Enter' && this.mediaActive) {
                e.preventDefault();
                this.mediaMultiple ? this.confirmSelection() : this.useMedia(this.mediaActive);
            }
        },

        // Görsel yüklenince doğal ölçüyü öğeye yaz (detay panelinde gösterilir).
        captureDims(item, el) {
            if (el && el.naturalWidth) {
                item.width = el.naturalWidth;
                item.height = el.naturalHeight;
            }
        },

        formatSize(bytes) {
            if (!bytes) return '';
            const kb = bytes / 1024;
            return kb < 1024 ? Math.round(kb) + ' KB' : (kb / 1024).toFixed(1) + ' MB';
        },

        // ── Yükleme (dosya seç / sürükle-bırak / panodan yapıştır) ──────────
        onMediaDrop(e) {
            this.mediaDragOver = false;
            const files = e.dataTransfer?.files;
            if (files && files.length) this.uploadFiles(files);
        },

        onMediaPaste(e) {
            const items = e.clipboardData?.items || [];
            const files = [];
            for (const it of items) {
                if (it.kind === 'file' && it.type.startsWith('image/')) {
                    const f = it.getAsFile();
                    if (f) files.push(f);
                }
            }
            if (files.length) {
                e.preventDefault();
                this.uploadFiles(files);
            }
        },

        onMediaFileInput(event) {
            // event.target.files CANLI bir FileList; value='' onu boşaltır.
            // Bu yüzden value'yu temizlemeden ÖNCE sabit bir diziye kopyala,
            // yoksa aşağıdaki length kontrolü 0 görür ve yükleme hiç başlamaz.
            const files = Array.from(event.target.files || []);
            event.target.value = ''; // aynı dosya tekrar seçilebilsin
            if (files.length) this.uploadFiles(files);
        },

        // Tek veya çok dosyayı sırayla yükler; yenileri listeye ekler,
        // sonuncusunu aktif yapar + tekli modda alana atar.
        async uploadFiles(fileList) {
            const files = Array.from(fileList).filter((f) => f.type.startsWith('image/'));
            if (!files.length) {
                this.mediaUploadError = 'Yalnızca görsel dosyaları yüklenebilir.';
                return;
            }

            this.mediaUploadError = '';
            this.mediaUploading = true;
            let last = null;

            for (const file of files) {
                try {
                    const fd = new FormData();
                    fd.append('file', file);
                    const resp = await fetch('/admin/media/upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this._csrf(),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: fd,
                    });
                    const json = await resp.json().catch(() => ({}));
                    if (!resp.ok || !json.success) {
                        this.mediaUploadError = json.error || `Yükleme başarısız (HTTP ${resp.status}).`;
                        continue;
                    }
                    const item = {
                        id: json.id ?? ('up_' + Date.now()),
                        url: json.url,
                        thumbnail_url: json.url,
                        name: json.name,
                        file_name: json.file_name || json.name,
                        mime_type: json.mime,
                        size: json.size,
                        alt_text: '',
                        is_image: true,
                    };
                    this.mediaItems.unshift(item);
                    last = item;
                } catch (e) {
                    console.error('[blockFieldInput] Media upload error:', e);
                    this.mediaUploadError = 'Yükleme sırasında bir hata oluştu.';
                }
            }

            if (last) {
                this.focusMedia(last);
                if (this.mediaMultiple) {
                    if (!this.mediaChosen.includes(last.url)) this.mediaChosen.push(last.url);
                } else {
                    this.parentRef[this.fieldKey] = last.url; // hemen alana ata (modal açık kalır)
                }
            }
            this.mediaUploading = false;
        },

        // ── Aktif öğe işlemleri (ad/alt kaydet, AI alt, sil) ────────────────
        async saveMediaMeta() {
            if (!this.mediaActive || String(this.mediaActive.id).startsWith('up_')) return;
            this.mediaSavingMeta = true;
            try {
                const resp = await fetch('/admin/media/' + this.mediaActive.id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this._csrf(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        name: this.mediaNameDraft,
                        custom_properties: { alt_text: this.mediaAltDraft },
                    }),
                });
                const json = await resp.json().catch(() => ({}));
                if (resp.ok && json.success) {
                    this.mediaActive.name = json.name;
                    this.mediaActive.alt_text = json.alt_text;
                }
            } catch (e) {
                console.error('[blockFieldInput] Media meta save error:', e);
            } finally {
                this.mediaSavingMeta = false;
            }
        },

        // Dosya adını URL-güvenli (slug) yap: file_name'i slug'lar, diski taşır,
        // bu sitedeki içeriklerde eski URL'i yenisiyle değiştirir (link kırılmaz).
        async renameMediaFile() {
            if (!this.mediaActive || String(this.mediaActive.id).startsWith('up_')) return;
            if (!window.confirm('Dosya adı URL-güvenli (slug) yapılacak ve dosya taşınacak. Bu görseli kullanan sayfa/yazı içeriklerindeki linkler otomatik güncellenir. Devam?')) return;
            this.mediaRenaming = true;
            try {
                const resp = await fetch('/admin/media/' + this.mediaActive.id + '/rename', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this._csrf(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ _method: 'PUT', name: this.mediaNameDraft }),
                });
                const json = await resp.json().catch(() => ({}));
                if (resp.ok && json.success) {
                    this.mediaActive.name = json.name;
                    this.mediaActive.file_name = json.file_name;
                    this.mediaActive.url = json.url;
                    const it = (this.mediaItems || []).find(m => m.id === this.mediaActive.id);
                    if (it) { it.file_name = json.file_name; it.url = json.url; it.thumbnail_url = json.url; }
                    window.alert(json.renamed
                        ? ('Dosya adı: ' + json.file_name + (json.refs_updated ? (' · ' + json.refs_updated + ' içerik linki güncellendi') : ''))
                        : 'Dosya adı zaten URL-güvenli.');
                } else {
                    window.alert('Yeniden adlandırılamadı: ' + (json.error || 'hata'));
                }
            } catch (e) {
                console.error('[blockFieldInput] Media rename error:', e);
            } finally {
                this.mediaRenaming = false;
            }
        },

        async generateActiveAlt() {
            if (!this.mediaActive || String(this.mediaActive.id).startsWith('up_')) return;
            this.mediaGenAltLoading = true;
            try {
                const resp = await fetch('/admin/media/' + this.mediaActive.id + '/generate-alt', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this._csrf(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await resp.json().catch(() => ({}));
                if (resp.ok && json.ok) {
                    this.mediaAltDraft = json.alt || '';
                } else {
                    this.mediaUploadError = json.message || 'AI alt metni üretilemedi.';
                }
            } catch (e) {
                console.error('[blockFieldInput] Alt generate error:', e);
            } finally {
                this.mediaGenAltLoading = false;
            }
        },

        async deleteMedia(item) {
            if (!confirm('Bu görseli kütüphaneden kalıcı olarak silmek istiyor musunuz?')) return;
            if (String(item.id).startsWith('up_')) {
                this.mediaItems = this.mediaItems.filter((m) => m.url !== item.url);
                return;
            }
            try {
                const resp = await fetch('/admin/media/' + item.id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this._csrf(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ _method: 'DELETE' }),
                });
                const json = await resp.json().catch(() => ({}));
                if (resp.ok && json.success) {
                    this.mediaItems = this.mediaItems.filter((m) => m.id !== item.id);
                    if (this.mediaActive && this.mediaActive.id === item.id) {
                        this.mediaActive = this.mediaItems[0] || null;
                        if (this.mediaActive) this.focusMedia(this.mediaActive);
                    }
                }
            } catch (e) {
                console.error('[blockFieldInput] Media delete error:', e);
            }
        },

        // ── Icon picker (Font Awesome) ──────────────────────────────────────
        // Curated set of common business/corporate FA solid icons.
        get iconCatalog() {
            return [
                'fa-star','fa-heart','fa-check','fa-circle-check','fa-xmark','fa-plus','fa-minus',
                'fa-phone','fa-mobile-screen','fa-envelope','fa-location-dot','fa-map','fa-globe','fa-clock',
                'fa-calendar','fa-calendar-check','fa-user','fa-users','fa-user-doctor','fa-user-tie','fa-user-group',
                'fa-house','fa-building','fa-hospital','fa-store','fa-briefcase','fa-handshake','fa-award','fa-trophy',
                'fa-medal','fa-certificate','fa-shield','fa-shield-halved','fa-lock','fa-thumbs-up','fa-gem',
                'fa-bolt','fa-fire','fa-lightbulb','fa-rocket','fa-gear','fa-gears','fa-wrench','fa-screwdriver-wrench',
                'fa-chart-line','fa-chart-pie','fa-chart-column','fa-magnifying-glass','fa-eye','fa-bullseye','fa-flag',
                'fa-graduation-cap','fa-book','fa-pen','fa-pen-nib','fa-camera','fa-image','fa-video','fa-music',
                'fa-truck','fa-car','fa-plane','fa-ship','fa-box','fa-boxes-stacked','fa-cart-shopping','fa-bag-shopping',
                'fa-tag','fa-tags','fa-percent','fa-gift','fa-credit-card','fa-wallet','fa-coins','fa-money-bill',
                'fa-leaf','fa-seedling','fa-tree','fa-sun','fa-droplet','fa-recycle','fa-earth-europe',
                'fa-heart-pulse','fa-stethoscope','fa-syringe','fa-pills','fa-tooth','fa-spa','fa-hand-holding-heart',
                'fa-comments','fa-comment-dots','fa-headset','fa-paper-plane','fa-bell','fa-thumbtack',
                'fa-list-check','fa-clipboard-check','fa-file-lines','fa-folder','fa-database','fa-server','fa-cloud',
                'fa-wifi','fa-code','fa-laptop','fa-desktop','fa-palette','fa-wand-magic-sparkles','fa-puzzle-piece',
                'fa-arrow-right','fa-arrow-up','fa-circle-arrow-right','fa-angles-right','fa-link','fa-share-nodes',
                'fa-quote-left','fa-hashtag','fa-infinity','fa-crown','fa-key','fa-compass','fa-anchor','fa-cube',
            ];
        },

        openIconPicker() {
            this.iconPickerOpen = true;
            this.iconSearch = '';
        },

        closeIconPicker() {
            this.iconPickerOpen = false;
        },

        filteredIcons() {
            const q = (this.iconSearch || '').toLowerCase().trim();
            if (!q) return this.iconCatalog;
            return this.iconCatalog.filter((ic) => ic.includes(q));
        },

        // Saklanan değerden render edilebilir tam class üretir.
        // "fa-star" → "fas fa-star"; zaten "fas/far/fab ..." içeriyorsa dokunma.
        iconClass(value) {
            const v = (value || '').trim();
            if (!v) return '';
            if (/\b(fa-solid|fa-regular|fa-brands|fas|far|fab|fa-light|fa-thin|fa-duotone)\b/.test(v)) return v;
            return 'fas ' + v;
        },

        iconValueMatches(ic) {
            const v = (this.parentRef[this.fieldKey] || '').trim();
            return v === ic || v === 'fas ' + ic;
        },

        selectIcon(ic) {
            // TAM class sakla ("fas fa-star"). Next.js component path ikonu
            // dogrudan <i className=icon> ile basar -> stil prefix'i sart.
            // HTML template prefix'li "fas ..." kullansa bile cift "fas" zararsiz.
            this.parentRef[this.fieldKey] = ic ? this.iconClass(ic) : '';
            this.closeIconPicker();
        },

        // ── Page link picker (iç sayfa) ─────────────────────────────────────
        // Katalog tüm page_link alanlarınca paylaşılır: tek fetch, window cache.
        async ensurePagesLoaded() {
            if (window.__pageCatalog) {
                this.pages = window.__pageCatalog;
                return;
            }
            if (window.__pageCatalogPromise) {
                this.pagesLoading = true;
                this.pages = await window.__pageCatalogPromise;
                this.pagesLoading = false;
                return;
            }

            this.pagesLoading = true;
            window.__pageCatalogPromise = (async () => {
                try {
                    const resp = await fetch(@js(route('admin.pages.catalog-json', [], false)), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    });
                    if (!resp.ok) return [];
                    const json = await resp.json();
                    window.__pageCatalog = json.data || [];
                    return window.__pageCatalog;
                } catch (e) {
                    console.error('[blockFieldInput] Page catalog load error:', e);
                    return [];
                }
            })();

            this.pages = await window.__pageCatalogPromise;
            this.pagesLoading = false;
        },

        openPagePicker() {
            this.pagePickerOpen = true;
            this.pageSearch = '';
            this.ensurePagesLoaded();
        },

        closePagePicker() {
            this.pagePickerOpen = false;
        },

        filteredPages() {
            const q = (this.pageSearch || '').toLowerCase().trim();
            if (!q) return this.pages;
            return this.pages.filter((p) =>
                (p.title || '').toLowerCase().includes(q) ||
                (p.path || '').toLowerCase().includes(q)
            );
        },

        matchedPageTitle(value) {
            const v = (value || '').trim();
            if (!v) return '';
            const match = (this.pages || []).find((p) => p.path === v);
            return match ? match.title : '';
        },

        selectPage(pg) {
            this.parentRef[this.fieldKey] = pg.path;
            this.closePagePicker();
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

        // Repeater item aç/kapa durumu — content'e sızmasın diye _uid ile
        // ayrı tutulur (serializeContent yalnızca _uid'i ayıklıyor).
        expandedRepeaterItems: {},

        // HTML Override CodeMirror (Kod tabı). Modal yeniden kullanıldığı için
        // tek instance; blok/tab değişince değer senkronlanır.
        htmlOverrideCM: null,
        htmlOverrideSyncing: false,
        htmlOverrideCloned: false, // "kopyalandı" geçici buton geri bildirimi

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

            // Klavye kısayolları: Cmd/Ctrl+S kaydet, Esc açık modalı kapat.
            window.addEventListener('keydown', (event) => this.handleEditorKeydown(event));

            // HTML Override CodeMirror: Kod tabı açılınca veya blok değişince
            // editörü kur/tazele ve değeri senkronla (modal gizliyken init
            // edilen CM'in refresh edilmesi şart).
            this.$watch('settingsTab', (tab) => {
                if (tab === 'code') {
                    this.$nextTick(() => this.syncHtmlOverrideEditor());
                }
            });
            this.$watch('settingsDraft', () => {
                if (this.settingsTab === 'code') {
                    this.$nextTick(() => this.syncHtmlOverrideEditor());
                }
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

        // ── Klavye kısayolları ──────────────────────────────────────────────
        anySettingsOpen() {
            return this.settingsModalOpen || this.rowSettingsModalOpen
                || this.columnSettingsModalOpen || this.pickerModalOpen;
        },

        closeTopmostModal() {
            // En içteki/öncelikli modaldan dışa doğru kapat.
            if (this.pickerModalOpen) { this.closeBlockPicker(); return true; }
            if (this.settingsModalOpen) { this.closeBlockSettings(); return true; }
            if (this.columnSettingsModalOpen) { this.closeColumnSettings(); return true; }
            if (this.rowSettingsModalOpen) { this.closeRowSettings(); return true; }
            return false;
        },

        handleEditorKeydown(event) {
            // Cmd/Ctrl+S → kaydet (formu gönder)
            if ((event.metaKey || event.ctrlKey) && (event.key === 's' || event.key === 'S')) {
                event.preventDefault();
                // Açık modal varsa önce ayarları uygula, sonra kaydet
                if (this.settingsModalOpen) this.saveBlockSettings();
                const form = this.$root.closest('form');
                if (form) {
                    this.suppressUnloadWarning = true;
                    // requestSubmit → native doğrulama + submit event'i çalışır
                    (form.requestSubmit ? form.requestSubmit() : form.submit());
                }
                return;
            }

            // Esc → açık modalı kapat. Alt picker'lar (medya/ikon/sayfa) kendi
            // escape handler'larında stopPropagation yaptığı için buraya ulaşmaz.
            if (event.key === 'Escape' && this.anySettingsOpen()) {
                if (this.closeTopmostModal()) event.preventDefault();
            }
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

        // Sistem/menü token'ı mı? Bunlar blok içeriği DEĞİL — renderer menüden/site
        // ayarlarından otomatik doldurur. Block Ayarları formunda düzenlenebilir
        // input olarak GÖSTERİLMEMELİ (yoksa boş text input görünür + kafa karıştırır).
        isSystemFieldKey(key) {
            const k = String(key || '');
            const SYS = ['site_name', 'site_domain', 'theme_slug', 'logo_url', 'favicon_url',
                'phone', 'email', 'address', 'whatsapp_number', 'working_hours', 'tax_id', 'footer_text'];
            if (SYS.includes(k)) return true;
            // menü tokenları: menu_{key}_html / _items_html / _name
            return /^menu_[a-z0-9_]+_(items_html|html|name)$/.test(k);
        },

        // Block Ayarları formunda gösterilecek şema alanları (sistem/menü token'ları hariç).
        visibleSchemaFields(schema) {
            return Object.entries(schema || {}).filter(([key]) => ! this.isSystemFieldKey(key));
        },

        repeaterFieldSchema(fieldSchema = {}) {
            const raw = fieldSchema.fields || fieldSchema.item_schema || {};
            // İki format da kabul edilir:
            //  - OBJE map {key:{type,label}}        (sihirbaz/buildRepeaterItem üretir)
            //  - DİZİ [{key,type,label}]            (elle/JSON ile girilebilir)
            // Dizi gelirse key'e göre map'e çevir; aksi halde alanlar 0,1,2… diye
            // anahtarlanıp form inputları yanlış bağlanır ve değerler BOŞ görünür.
            if (Array.isArray(raw)) {
                const map = {};
                raw.forEach((f) => {
                    if (f && typeof f === 'object' && f.key) {
                        const { key, ...rest } = f;
                        map[key] = rest;
                    }
                });
                return map;
            }
            return (raw && typeof raw === 'object') ? raw : {};
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
            const item = this.createRepeaterItem(fieldSchema);
            block.content[fieldName].push(item);
            this.expandedRepeaterItems[item._uid] = true;
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
            this.expandedRepeaterItems[clone._uid] = true;
        },

        moveRepeaterItem(block, fieldName, itemIndex, direction) {
            this.ensureRepeaterContent(block, fieldName);
            const items = block.content[fieldName];
            const targetIndex = itemIndex + direction;
            if (targetIndex < 0 || targetIndex >= items.length) return;

            [items[itemIndex], items[targetIndex]] = [items[targetIndex], items[itemIndex]];
        },

        // ── Repeater item akıllı etiket + aç/kapa ───────────────────────
        // İlk dolu metin alanından kısa özet üretir: "Item #1 — Hizmetlerimiz"
        repeaterItemLabel(item, fieldSchema) {
            const schema = this.repeaterFieldSchema(fieldSchema);
            const names = Object.keys(schema);
            const preferred = ['title', 'name', 'label', 'heading'].filter((n) => names.includes(n));
            const ordered = [...preferred, ...names.filter((n) => !preferred.includes(n))];

            for (const name of ordered) {
                const type = schema[name]?.type || 'text';
                if (!['text', 'textarea', 'rich-text', 'html'].includes(type)) continue;
                const raw = item?.[name];
                if (typeof raw !== 'string' || !raw.trim()) continue;
                const text = raw.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                if (!text) continue;
                return text.length > 40 ? text.slice(0, 40) + '…' : text;
            }

            return '';
        },

        // 2'den fazla item varsa varsayılan kapalı; kullanıcı tercihi _uid ile saklanır
        repeaterItemExpanded(item, itemCount) {
            const state = this.expandedRepeaterItems[item?._uid];
            if (state !== undefined) return state;
            return itemCount <= 2;
        },

        toggleRepeaterItem(item, itemCount) {
            if (!item?._uid) return;
            this.expandedRepeaterItems[item._uid] = !this.repeaterItemExpanded(item, itemCount);
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
            // CM düzenlemesi henüz settingsDraft'a yansımadıysa son değeri al.
            if (this.htmlOverrideCM && this.settingsDraft.render_mode === 'html') {
                this.settingsDraft.html_override = this.htmlOverrideCM.getValue();
            }
            const { region, rowIndex, columnIndex, blockIndex } = this.settingsTarget;
            this.regions[region][rowIndex].columns[columnIndex].blocks[blockIndex] = {
                ...this.regions[region][rowIndex].columns[columnIndex].blocks[blockIndex],
                ...JSON.parse(JSON.stringify(this.settingsDraft)),
            };
            this.normalizeSortOrder();
            this.closeBlockSettings();
        },

        // ── HTML Override CodeMirror (Kod tabı) ─────────────────────────
        // Modal yeniden kullanıldığı için tek CM instance; blok/tab değişince
        // değer senkronlanır. Modal gizliyken init edilirse refresh şart.
        mountHtmlOverrideEditor(textarea) {
            if (this.htmlOverrideCM || typeof CodeMirror === 'undefined' || !textarea) return;
            const cm = CodeMirror.fromTextArea(textarea, {
                mode: 'htmlmixed',
                lineNumbers: true,
                lineWrapping: true,
                tabSize: 2,
                // Katlama: <style>/<script>/tag ve {} blokları gutter okuyla aç-kapa
                foldGutter: true,
                gutters: ['CodeMirror-linenumbers', 'CodeMirror-foldgutter'],
                extraKeys: { 'Ctrl-Q': c => c.foldCode(c.getCursor()), 'Cmd-Q': c => c.foldCode(c.getCursor()) },
            });
            cm.on('change', () => {
                if (this.htmlOverrideSyncing) return;
                if (this.settingsDraft) this.settingsDraft.html_override = cm.getValue();
            });
            this.htmlOverrideCM = cm;
        },

        syncHtmlOverrideEditor() {
            if (! this.htmlOverrideCM) {
                this.mountHtmlOverrideEditor(this.$refs.htmlOverrideTextarea);
                if (! this.htmlOverrideCM) return; // CodeMirror henüz yüklenmemiş
            }
            const cm = this.htmlOverrideCM;
            if (! this.settingsDraft) return;
            const val = this.settingsDraft.html_override || '';
            if (cm.getValue() !== val) {
                this.htmlOverrideSyncing = true;
                cm.setValue(val);
                this.htmlOverrideSyncing = false;
            }
            this.$nextTick(() => cm.refresh());
        },

        // Üretilen HTML kodunu (placeholder'lar çözülmüş çıktı) HTML Override
        // alanına klonlar; render_mode'u html'e çevirir ki override görünür/etkin
        // olsun. Override doluysa üzerine yazmadan önce onay ister.
        cloneGeneratedToOverride() {
            if (! this.settingsDraft) return;
            const generated = (this.blockCodePreview(this.settingsDraft) || '').trim();
            if (! generated) return;
            const current = (this.settingsDraft.html_override || '').trim();
            if (current && current !== generated &&
                ! window.confirm('HTML Override alanında içerik var. Üretilen kodla değiştirilsin mi?')) {
                return;
            }
            this.settingsDraft.render_mode = 'html';
            this.settingsDraft.html_override = generated;
            this.$nextTick(() => this.syncHtmlOverrideEditor());
            this.htmlOverrideCloned = true;
            setTimeout(() => { this.htmlOverrideCloned = false; }, 2000);
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
