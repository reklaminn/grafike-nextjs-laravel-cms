<div class="rounded-xl border border-gray-200 bg-white shadow-sm"
     x-data="sectionPreviewPanel('{{ route('admin.section-templates.preview', $sectionTemplate) }}')">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
        <h3 class="text-sm font-semibold text-gray-800">
            <i class="fas fa-eye mr-1.5 text-indigo-500"></i> Önizleme
        </h3>
        <div class="flex items-center gap-1.5">
            {{-- Device toggles --}}
            <button type="button" @click="setDevice('desktop')"
                    :class="device === 'desktop' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-400 hover:text-gray-600'"
                    class="rounded p-1.5 text-xs" title="Masaüstü">
                <i class="fas fa-desktop"></i>
            </button>
            <button type="button" @click="setDevice('tablet')"
                    :class="device === 'tablet' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-400 hover:text-gray-600'"
                    class="rounded p-1.5 text-xs" title="Tablet">
                <i class="fas fa-tablet-screen-button"></i>
            </button>
            <button type="button" @click="setDevice('mobile')"
                    :class="device === 'mobile' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-400 hover:text-gray-600'"
                    class="rounded p-1.5 text-xs" title="Mobil">
                <i class="fas fa-mobile-screen"></i>
            </button>

            <span class="mx-1 text-gray-200">|</span>

            {{-- Live toggle --}}
            <button type="button" @click="liveMode = !liveMode"
                    :class="liveMode ? 'bg-green-100 text-green-700' : 'text-gray-400 hover:text-gray-600'"
                    class="rounded px-2 py-1.5 text-xs font-medium" title="Canlı önizleme">
                <i class="fas fa-bolt"></i>
            </button>

            {{-- Refresh --}}
            <button type="button" @click="refresh()"
                    class="rounded p-1.5 text-xs text-gray-400 hover:text-gray-600" title="Yenile">
                <i class="fas fa-rotate" :class="loading ? 'fa-spin' : ''"></i>
            </button>

            {{-- Open in tab --}}
            <a href="{{ route('admin.section-templates.preview', $sectionTemplate) }}"
               target="_blank"
               class="rounded p-1.5 text-xs text-gray-400 hover:text-gray-600" title="Yeni sekmede aç">
                <i class="fas fa-arrow-up-right-from-square"></i>
            </a>
        </div>
    </div>

    {{-- Iframe wrapper --}}
    <div class="overflow-hidden bg-gray-100 p-2" style="min-height: 320px;">
        <div class="mx-auto overflow-hidden rounded-lg border border-gray-200 bg-white transition-all duration-300"
             :style="{ width: iframeWidth }">
            <iframe id="section-preview-iframe"
                    :srcdoc="srcdoc"
                    src="{{ route('admin.section-templates.preview', $sectionTemplate) }}"
                    class="block w-full"
                    style="height: 480px; border: none;"
                    x-ref="iframe">
            </iframe>
        </div>
    </div>

    {{-- Status bar --}}
    <div class="border-t border-gray-100 px-4 py-2 text-xs text-gray-400">
        <span x-show="loading"><i class="fas fa-circle-notch fa-spin mr-1"></i> Render ediliyor…</span>
        <span x-show="!loading && liveMode"><i class="fas fa-circle text-green-400 mr-1" style="font-size:7px;vertical-align:middle;"></i> Canlı</span>
        <span x-show="!loading && !liveMode">Manuel yenile</span>
        <span x-show="lastError" class="ml-2 text-red-500" x-text="lastError"></span>
    </div>
</div>

@push('scripts')
<script>
function sectionPreviewPanel(previewUrl) {
    return {
        device: 'desktop',
        liveMode: false,
        loading: false,
        srcdoc: null,
        lastError: '',
        debounceTimer: null,

        get iframeWidth() {
            return { desktop: '100%', tablet: '768px', mobile: '375px' }[this.device] ?? '100%';
        },

        init() {
            this.$watch('liveMode', (val) => {
                if (val) this.refresh();
            });
            // Listen for editor changes (dispatched from script.blade.php)
            window.addEventListener('section-template-editor-change', () => {
                if (!this.liveMode) return;
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => this.refresh(), 900);
            });
        },

        setDevice(d) {
            this.device = d;
        },

        async refresh() {
            this.loading = true;
            this.lastError = '';
            try {
                const html  = window.getHtmlValue ? window.getHtmlValue() : document.getElementById('html_template_input')?.value ?? '';
                const rawContent = document.getElementById('default_content_json_input')?.value ?? '{}';
                let content = {};
                try { content = JSON.parse(rawContent); } catch (_) {}

                // Schema'dan örnek içerik TABAN, kullanıcının default content'i
                // üstüne biner: yeni eklenen alanlar (default üretilmeden) de
                // önizlemede örnek değerle görünür; kullanıcı değerleri korunur.
                if (window.buildSchemaSampleContent) {
                    try {
                        const sample = window.buildSchemaSampleContent();
                        content = { ...sample, ...content };
                    } catch (_) {}
                }

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
                const res = await fetch(previewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'text/html',
                    },
                    body: JSON.stringify({ html_template: html, content }),
                });

                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const body = await res.text();

                // Wrap fragment in minimal HTML so iframe srcdoc renders correctly
                const theme = @json($sectionTemplate->theme?->assets_json ?? []);
                const cssLinks = (theme.css ?? []).map(u => `<link rel="stylesheet" href="${u}">`).join('');
                const jsLinks  = (theme.js  ?? []).map(u => `<script src="${u}"><\/script>`).join('');
                this.srcdoc = `<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">${cssLinks || '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">'}</head><body style="margin:0">${body}${jsLinks || '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"><\/script>'}</body></html>`;
            } catch (e) {
                this.lastError = e.message;
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
