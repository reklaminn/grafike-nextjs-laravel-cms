{{--
    Sektör SiteTemplate galerisi paneli (FAZ 4.3).

    Tek bileşen — Alpine ile:
      - "Açılır galeri" state
      - Endpoint /admin/industry-templates JSON fetch'iyle dolar
      - Endüstri sekmeleri (clinic, lawyer, salon, hotel, real_estate, corporate)
      - Kart seçimi → onay modalı → POST apply

    $tenant geç dependency olarak — apply URL'i kuruluyor.
--}}
<div class="bg-white rounded-xl shadow-sm border p-5"
     x-data="industryTemplatePanel({
        loadUrl: @js(route('admin.industry-templates.index', [], false)),
        applyUrl: @js(route('admin.tenants.apply-industry-template', $tenant, false)),
        csrf: @js(csrf_token()),
     })"
     x-init="init()">

    <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-layer-group text-emerald-500"></i>
            Sektör Şablonu Uygula
        </h2>
        <span class="text-[10px] text-gray-400">Tek tıkla başlangıç içeriği</span>
    </div>

    <p class="text-xs text-gray-500 mb-4">
        Bu siteyi sıfırdan kurmak yerine, sektörünüze uygun hazır şablonu seçip içerikle birlikte yükleyebilirsiniz.
        Şablon uygulandıktan sonra AI ile detayları kişiselleştirin.
    </p>

    {{-- Industry tab bar --}}
    <div class="flex flex-wrap gap-1.5 mb-3">
        <template x-for="(label, code) in industries" :key="code">
            <button type="button"
                    @click="activeIndustry = code"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors"
                    :class="activeIndustry === code
                            ? 'bg-emerald-600 text-white border-emerald-700'
                            : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                    x-text="label">
            </button>
        </template>
    </div>

    {{-- Loading state --}}
    <div x-show="loading" x-cloak class="text-xs text-gray-500 py-4 flex items-center gap-2">
        <i class="fas fa-spinner fa-spin"></i> Şablonlar yükleniyor…
    </div>

    {{-- Empty state --}}
    <div x-show="!loading && filteredTemplates.length === 0" x-cloak
         class="bg-gray-50 border border-dashed border-gray-200 rounded-lg p-4 text-center text-xs text-gray-500">
        Bu sektör için henüz şablon yok. Önce <code>php artisan db:seed --class=IndustryTemplatesSeeder</code> çalıştırın.
    </div>

    {{-- Template gallery --}}
    <div x-show="!loading && filteredTemplates.length > 0" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <template x-for="tpl in filteredTemplates" :key="tpl.id">
            <button type="button"
                    @click="selectTemplate(tpl)"
                    class="text-left bg-white border border-gray-200 rounded-lg p-3 hover:border-indigo-400 hover:shadow-sm transition-all">
                <div class="aspect-video bg-gradient-to-br from-indigo-50 to-emerald-50 rounded-md mb-2 flex items-center justify-center text-3xl text-gray-300">
                    <template x-if="tpl.preview_image">
                        <img :src="tpl.preview_image" :alt="tpl.name" class="object-cover w-full h-full rounded-md">
                    </template>
                    <template x-if="!tpl.preview_image">
                        <i class="fas fa-image"></i>
                    </template>
                </div>
                <p class="text-sm font-semibold text-gray-900" x-text="tpl.name"></p>
                <p class="text-[11px] text-gray-500 mt-0.5" x-text="tpl.summary || tpl.description || ''"></p>
            </button>
        </template>
    </div>

    {{-- Status banner --}}
    <div x-show="status" x-cloak
         class="mt-3 text-xs rounded-lg p-3"
         :class="statusOk ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                          : 'bg-red-50 text-red-700 border border-red-200'">
        <i class="fas" :class="statusOk ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i>
        <span x-text="status"></span>
    </div>

    {{-- ── Confirm Modal ───────────────────────────────────────────── --}}
    <div x-show="selected" x-cloak
         class="fixed inset-0 z-[85] flex items-center justify-center bg-black/50 p-4"
         @click.self="selected = null"
         @keydown.escape.window="selected = null">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-gray-200 p-5">
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2 mb-2">
                <i class="fas fa-layer-group text-emerald-500"></i>
                Şablonu Uygula
            </h3>
            <p class="text-sm text-gray-700 mb-1" x-text="selected?.name"></p>
            <p class="text-xs text-gray-500 mb-4" x-text="selected?.description || selected?.summary || ''"></p>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-xs text-yellow-800 mb-4">
                <p class="font-semibold mb-1">
                    <i class="fas fa-exclamation-triangle"></i> Dikkat
                </p>
                <ul class="list-disc list-inside space-y-0.5">
                    <li>Şablondaki slug'lara sahip mevcut sayfalar <strong>silinip yeniden yazılır</strong>.</li>
                    <li>Ana menü ve site ayarları yeni şablona göre güncellenir.</li>
                    <li>Bu işlem geri alınamaz — önce yedek aldıysanız emin olun.</li>
                </ul>
            </div>

            <form method="POST" :action="applyUrl">
                <input type="hidden" name="_token" :value="csrf">
                <input type="hidden" name="site_template_id" :value="selected?.id">
                <input type="hidden" name="mode" value="replace">

                <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="selected = null"
                            class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                        Vazgeç
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 flex items-center gap-2">
                        <i class="fas fa-check"></i> Uygula
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function industryTemplatePanel(cfg) {
    return {
        loadUrl: cfg.loadUrl,
        applyUrl: cfg.applyUrl,
        csrf: cfg.csrf,

        loading: false,
        industries: {},
        templates: [],
        activeIndustry: 'clinic',
        selected: null,
        status: '',
        statusOk: false,

        async init() {
            this.loading = true;
            try {
                const r = await fetch(this.loadUrl, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                });
                if (!r.ok) throw new Error('HTTP ' + r.status);
                const data = await r.json();
                this.industries = data.industries || {};
                this.templates = data.templates || [];
                // Default to first industry that actually has templates
                const present = new Set(this.templates.map(t => t.industry));
                const firstWithTemplates = Object.keys(this.industries).find(k => present.has(k));
                if (firstWithTemplates) this.activeIndustry = firstWithTemplates;
            } catch (e) {
                this.status = 'Şablonlar yüklenemedi: ' + e.message;
                this.statusOk = false;
            } finally {
                this.loading = false;
            }
        },

        get filteredTemplates() {
            return this.templates.filter(t => t.industry === this.activeIndustry);
        },

        selectTemplate(tpl) {
            this.selected = tpl;
            this.status = '';
        },
    };
}
</script>
@endpush
