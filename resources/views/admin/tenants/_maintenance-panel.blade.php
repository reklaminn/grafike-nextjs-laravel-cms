{{--
    Bakım / Yakında Modu paneli.

    Açıkken tenant sitesi public ziyaretçilere "Yakında" sayfası gösterir; gizli
    bypass linki (?onizleme=<token>) ile login gerektirmeden gerçek site görülür.

    Variables (include ile):
      - $tenant   Tenant instance
--}}
@php
    $mtOn     = $tenant->isUnderMaintenance();
    $mtToken  = $tenant->maintenancePreviewToken();
    $mtDomain = $tenant->primaryDomain();
    $mtLink   = ($mtToken && $mtDomain) ? "https://{$mtDomain}/tr?onizleme={$mtToken}" : null;
@endphp
<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-hard-hat text-amber-500"></i> Bakım / Yakında Modu
        </h2>
        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $mtOn ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
            {{ $mtOn ? 'AÇIK — site kapalı' : 'KAPALI — site açık' }}
        </span>
    </div>

    <p class="text-xs text-gray-500 mb-4">
        Açıkken ziyaretçiler "Yakında" sayfası görür. Aşağıdaki gizli linkle girip gerçek
        siteyi görebilirsin (login gerekmez); link tarayıcına kaydedilir, sonra normal gezersin.
    </p>

    <form method="POST" action="{{ route('admin.tenants.maintenance.update', $tenant) }}" class="space-y-3">
        @csrf @method('PUT')

        <label class="flex items-center gap-3 rounded-lg bg-amber-50 px-3 py-2.5 text-sm cursor-pointer">
            <input type="checkbox" name="maintenance" value="1" {{ $mtOn ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
            <span class="font-medium text-amber-900">Bakım modunu aç (siteyi ziyaretçilere kapat)</span>
        </label>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Başlık (opsiyonel)</label>
            <input type="text" name="maintenance_title" maxlength="120"
                   value="{{ old('maintenance_title', $tenant->getAttribute('maintenance_title')) }}"
                   placeholder="Çok Yakında"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Mesaj (opsiyonel)</label>
            <textarea name="maintenance_message" rows="2" maxlength="600"
                      placeholder="Sitemiz yenileniyor. Kısa süre içinde tekrar yayında olacağız."
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-400 resize-none">{{ old('maintenance_message', $tenant->getAttribute('maintenance_message')) }}</textarea>
        </div>

        <button type="submit" class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors">
            <i class="fas fa-save mr-1"></i> Kaydet
        </button>
    </form>

    @if($mtLink)
        <div class="mt-4 border-t border-gray-100 pt-4" x-data="{ copied: false }">
            <label class="block text-xs font-medium text-gray-600 mb-1">
                <i class="fas fa-key text-amber-500 mr-1"></i> Gizli bypass linki
            </label>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ $mtLink }}" onclick="this.select()"
                       class="flex-1 px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-xs text-gray-700 font-mono">
                <button type="button"
                        @click="navigator.clipboard.writeText(@js($mtLink)); copied = true; setTimeout(() => copied = false, 1500)"
                        class="px-3 py-2 bg-gray-100 text-gray-700 text-xs rounded-lg hover:bg-gray-200 whitespace-nowrap">
                    <span x-show="!copied"><i class="fas fa-copy mr-1"></i>Kopyala</span>
                    <span x-show="copied" x-cloak class="text-emerald-600"><i class="fas fa-check mr-1"></i>Kopyalandı</span>
                </button>
            </div>
            <p class="mt-1.5 text-[11px] text-gray-400">Bu linki bilen herkes bakım modunu bypass eder — gizli tut.</p>
        </div>
    @else
        <p class="mt-4 text-xs text-gray-400 border-t border-gray-100 pt-4">
            Bakım modunu bir kez kaydedince gizli bypass linki burada oluşur.
        </p>
    @endif
</div>
