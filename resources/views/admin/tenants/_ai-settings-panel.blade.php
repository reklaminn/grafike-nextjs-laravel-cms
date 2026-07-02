{{--
    AI Ayarları (BYOK) paneli.

    Stancl/virtualcolumn expands `data` JSON into model attributes. Tenant
    AI helpers (`isUsingByokAi`, `aiApiKey`, `preferredAiProvider`, …) hide
    that complexity; this view only reads from them.

    Variables (passed via include):
      - $tenant     Tenant instance
      - $providers  array<string,array> from config('ai.providers')
      - $canManage  bool — agency admin gate; non-managers see read-only view
--}}
<div class="bg-white rounded-xl shadow-sm border p-5"
     x-data="aiSettingsPanel({
        defaultProvider: @js($tenant->preferredAiProvider() ?? array_key_first($providers)),
        providers: @js(array_keys($providers)),
        testUrl: @js(route('admin.tenants.ai-settings.test', $tenant, false)),
        csrf: @js(csrf_token()),
     })">

    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-robot text-purple-500"></i> AI Ayarları (BYOK)
        </h2>
        <span class="text-xs px-2 py-1 rounded-full
            {{ $tenant->isUsingByokAi() ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
            {{ $tenant->isUsingByokAi() ? 'Kendi API anahtarı kullanılıyor' : 'Sistem API anahtarı kullanılıyor' }}
        </span>
    </div>

    <p class="text-xs text-gray-500 mb-4">
        Bu site için ayrı bir API anahtarı tanımlayabilirsiniz. Anahtar tanımlandığında AI istekleri
        sistemin değil sitenin kendi anahtarı üzerinden çalışır; sitenin AI maliyeti
        <strong>doğrudan sahibine</strong> faturalanır ve sizin kota'nızdan düşmez.
        Anahtarlar veritabanına şifrelenerek kaydedilir.
    </p>

    @if($canManage)
    <form method="POST" action="{{ route('admin.tenants.ai-settings.update', $tenant, false) }}"
          class="space-y-5">
        @csrf @method('PUT')

        {{-- Plan selector (agency only) --}}
        @if(!empty($plans))
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-tag mr-1 text-gray-400"></i> Plan
                </label>
                <select name="plan"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    @foreach($plans as $planKey => $planConfig)
                        <option value="{{ $planKey }}" {{ $tenant->aiPlan() === $planKey ? 'selected' : '' }}>
                            {{ $planConfig['label'] ?? ucfirst($planKey) }}
                            @if(($planConfig['monthly_requests'] ?? null) !== null)
                                — {{ number_format($planConfig['monthly_requests']) }} istek
                            @else
                                — sınırsız istek
                            @endif
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">
                    Plan değişikliği bir sonraki AI isteğinden itibaren geçerli olur.
                </p>
            </div>
        @endif

        {{-- Master toggle --}}
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="hidden" name="use_byok" value="0">
            <input type="checkbox" name="use_byok" value="1"
                   {{ $tenant->isUsingByokAi() ? 'checked' : '' }}
                   class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <span>
                <span class="text-sm font-medium text-gray-700">Bu site kendi API anahtarını kullansın</span>
                <span class="block text-xs text-gray-500">
                    Kapalıysa sistem geneline tanımlı (.env) anahtar kullanılır. BYOK aktifken kota kontrol edilmez.
                </span>
            </span>
        </label>

        {{-- Otomatik SEO meta --}}
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="hidden" name="auto_seo_meta" value="0">
            <input type="checkbox" name="auto_seo_meta" value="1"
                   {{ ($tenant->aiSettings()['auto_seo_meta'] ?? false) ? 'checked' : '' }}
                   class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <span>
                <span class="text-sm font-medium text-gray-700">Yayınlamada otomatik SEO meta üret</span>
                <span class="block text-xs text-gray-500">
                    Sayfa yayına alındığında meta başlık/açıklama boşsa AI arka planda doldurur
                    (kota kullanır; sonradan SEO sekmesinden düzenlenebilir).
                </span>
            </span>
        </label>

        {{-- Preferred provider --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tercih Edilen Sağlayıcı</label>
            <select name="preferred_provider"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">— Sistem varsayılanı —</option>
                @foreach(array_keys($providers) as $p)
                    <option value="{{ $p }}" {{ $tenant->preferredAiProvider() === $p ? 'selected' : '' }}>
                        {{ ucfirst($p) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Per-provider keys + models --}}
        <div class="space-y-4">
            @foreach($providers as $name => $cfg)
                @php $hasKey = $tenant->hasAiApiKey($name); @endphp
                <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700 capitalize">
                            <i class="fas fa-key mr-1 text-gray-400"></i> {{ $name }}
                        </h3>
                        @if($hasKey)
                            <span class="text-xs text-emerald-600 flex items-center gap-1">
                                <i class="fas fa-check-circle"></i> Anahtar kayıtlı
                            </span>
                        @else
                            <span class="text-xs text-gray-400 flex items-center gap-1">
                                <i class="fas fa-circle-notch"></i> Anahtar yok
                            </span>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">API Anahtarı</label>
                        <div class="flex gap-2">
                            <input type="password" name="api_keys[{{ $name }}]"
                                   autocomplete="new-password"
                                   placeholder="{{ $hasKey ? '••••••••••• (değiştirmek için yeni anahtar girin)' : 'API anahtarınızı yapıştırın' }}"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500">
                            @if($hasKey)
                                <button type="button"
                                        @click="testKey('{{ $name }}')"
                                        class="px-3 py-2 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-xs font-medium hover:bg-blue-100 whitespace-nowrap">
                                    <i class="fas fa-flask"></i> Test
                                </button>
                                <label class="px-3 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg text-xs font-medium hover:bg-red-100 whitespace-nowrap cursor-pointer flex items-center gap-1">
                                    <input type="checkbox" name="clear[{{ $name }}]" value="1" class="hidden">
                                    <i class="fas fa-trash"></i> Sil
                                </label>
                            @endif
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">
                            Anahtar veritabanına şifrelenerek (Laravel Crypt) kaydedilir; bu sayfada geri okunamaz.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Basit istek modeli</label>
                            <input type="text" name="models[{{ $name }}][simple]"
                                   value="{{ $tenant->preferredAiModel($name, 'simple') ?? '' }}"
                                   placeholder="{{ $cfg['models']['simple'] ?? '' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Karmaşık istek modeli</label>
                            <input type="text" name="models[{{ $name }}][complex]"
                                   value="{{ $tenant->preferredAiModel($name, 'complex') ?? '' }}"
                                   placeholder="{{ $cfg['models']['complex'] ?? '' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between">
            <div x-show="testStatus" x-cloak
                 class="text-xs"
                 :class="testStatusOk ? 'text-emerald-600' : 'text-red-600'">
                <i class="fas" :class="testStatusOk ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i>
                <span x-text="testStatus"></span>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium ml-auto">
                <i class="fas fa-save mr-1"></i> AI Ayarlarını Kaydet
            </button>
        </div>
    </form>
    @else
        {{-- Read-only summary for non-managers --}}
        <ul class="text-sm text-gray-700 space-y-2">
            <li>
                <span class="text-gray-500 mr-1">Tercih edilen sağlayıcı:</span>
                <span class="font-medium">{{ $tenant->preferredAiProvider() ?? 'sistem varsayılanı' }}</span>
            </li>
            @foreach(array_keys($providers) as $p)
                <li class="text-xs">
                    <span class="capitalize">{{ $p }}:</span>
                    @if($tenant->hasAiApiKey($p))
                        <span class="text-emerald-600">anahtar kayıtlı</span>
                    @else
                        <span class="text-gray-400">anahtar yok</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @push('scripts')
    <script>
        function aiSettingsPanel(cfg) {
            return {
                testStatus: '',
                testStatusOk: false,

                async testKey(provider) {
                    this.testStatus = 'Test isteği gönderiliyor…';
                    this.testStatusOk = false;
                    try {
                        const r = await fetch(cfg.testUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': cfg.csrf,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ provider }),
                        });
                        const data = await r.json();
                        if (data.ok) {
                            this.testStatusOk = true;
                            this.testStatus = `✓ ${data.provider}/${data.model} cevap verdi (in=${data.usage.input_tokens}, out=${data.usage.output_tokens}).`;
                        } else {
                            this.testStatusOk = false;
                            this.testStatus = '✗ ' + (data.message || 'Bilinmeyen hata');
                        }
                    } catch (e) {
                        this.testStatusOk = false;
                        this.testStatus = '✗ ' + (e.message || 'Bağlantı hatası');
                    }
                },
            };
        }
    </script>
    @endpush
</div>
