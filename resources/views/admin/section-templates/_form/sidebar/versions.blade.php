<div class="rounded-xl border border-gray-200 bg-white shadow-sm"
     x-data="sectionVersionPanel('{{ route('admin.section-templates.versions', $sectionTemplate) }}')">

    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
        <h3 class="text-sm font-semibold text-gray-800">
            <i class="fas fa-clock-rotate-left mr-1.5 text-indigo-400"></i> Versiyon Geçmişi
        </h3>
        <button type="button" @click="toggle()"
                class="rounded p-1.5 text-xs text-gray-400 hover:bg-gray-100">
            <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>
    </div>

    {{-- Save version button --}}
    <div class="border-b border-gray-100 px-4 py-3">
        <form method="POST" action="{{ route('admin.section-templates.save-version', $sectionTemplate, false) }}"
              class="flex items-center gap-2">
            @csrf
            <input type="text" name="label" placeholder="Opsiyonel etiket (örn: v2 hero)"
                   class="min-w-0 flex-1 rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <button type="submit"
                    class="shrink-0 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                <i class="fas fa-bookmark mr-1"></i> Kaydet
            </button>
        </form>
        <p class="mt-1 text-[10px] text-gray-400">Mevcut html_template + schema + default_content anlık kopyasını alır.</p>
    </div>

    {{-- Version list --}}
    <div x-show="open" x-collapse>
        <div class="max-h-72 overflow-y-auto divide-y divide-gray-50">
            <template x-if="loading">
                <div class="px-4 py-6 text-center text-xs text-gray-400">
                    <i class="fas fa-circle-notch fa-spin mr-1"></i> Yükleniyor…
                </div>
            </template>
            <template x-if="!loading && versions.length === 0">
                <div class="px-4 py-6 text-center text-xs text-gray-400">Henüz kaydedilmiş versiyon yok.</div>
            </template>
            <template x-for="v in versions" :key="v.id">
                <div class="flex items-start gap-2 px-4 py-2.5 hover:bg-gray-50">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium text-gray-700" x-text="v.label || ('Versiyon #' + v.id)"></p>
                        <p class="mt-0.5 text-[10px] text-gray-400">
                            <span x-text="v.created_at"></span>
                            <template x-if="v.reason !== 'manual'">
                                <span class="ml-1 rounded-full bg-gray-100 px-1.5 text-gray-500" x-text="v.reason"></span>
                            </template>
                        </p>
                    </div>
                    <form method="POST"
                          :action="restoreUrl.replace('__version__', v.id)"
                          onsubmit="return confirm('Bu versiyon geri yüklenecek. Mevcut içerik bir snapshot\'a alınır. Devam?')">
                        @csrf
                        <button type="submit"
                                class="shrink-0 rounded-lg bg-amber-50 px-2 py-1 text-[10px] font-medium text-amber-700 hover:bg-amber-100">
                            <i class="fas fa-rotate-left"></i> Yükle
                        </button>
                    </form>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function sectionVersionPanel(versionsUrl) {
    return {
        open: false,
        loading: false,
        versions: [],
        restoreUrl: '{{ route('admin.section-templates.restore-version', [$sectionTemplate, '__version__']) }}',

        toggle() {
            this.open = !this.open;
            if (this.open && this.versions.length === 0) this.load();
        },

        async load() {
            this.loading = true;
            try {
                const res = await fetch(versionsUrl, { headers: { 'Accept': 'application/json' } });
                this.versions = await res.json();
            } catch (_) {}
            this.loading = false;
        },
    };
}
</script>
@endpush
