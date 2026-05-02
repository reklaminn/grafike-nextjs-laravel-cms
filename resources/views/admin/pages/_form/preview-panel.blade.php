@if(isset($page) && $page->slug)
@php
    $previewBase = rtrim(config('cms.frontend_url'), '/') . '/' . ltrim($page->slug, '/');
    $activeTenantId = session('active_tenant');

    if ($activeTenantId) {
        $previewBase .= (str_contains($previewBase, '?') ? '&' : '?') . http_build_query([
            'tenant' => $activeTenantId,
        ]);
    }
@endphp

<div x-data="{
    open: {{ session()->has('preview_refresh') ? 'true' : 'false' }},
    device: 'desktop',
    base: '{{ $previewBase }}',
    ts: {{ now()->timestamp }},
    get iframeSrc() {
        return this.base + (this.base.includes('?') ? '&' : '?') + 'preview=1&t=' + this.ts;
    },
    reload() { this.ts = Date.now(); },
    widths: { desktop: '100%', tablet: '768px', mobile: '375px' }
}"
     class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <h3 class="text-base font-semibold text-gray-800">
                <i class="fas fa-eye mr-2 text-indigo-500"></i>Canlı Önizleme
            </h3>
            <span class="text-xs text-gray-400">(kaydedilen son hal)</span>
        </div>

        <div class="flex items-center gap-3">
            {{-- Device toggle --}}
            <div class="inline-flex rounded-lg bg-gray-100 p-0.5 text-xs">
                <button type="button" @click="device = 'desktop'"
                        :class="device === 'desktop' ? 'bg-white shadow text-gray-800' : 'text-gray-500 hover:text-gray-700'"
                        class="rounded-md px-2.5 py-1.5 font-medium transition-colors" title="Desktop">
                    <i class="fas fa-desktop"></i>
                </button>
                <button type="button" @click="device = 'tablet'"
                        :class="device === 'tablet' ? 'bg-white shadow text-gray-800' : 'text-gray-500 hover:text-gray-700'"
                        class="rounded-md px-2.5 py-1.5 font-medium transition-colors" title="Tablet">
                    <i class="fas fa-tablet-screen-button"></i>
                </button>
                <button type="button" @click="device = 'mobile'"
                        :class="device === 'mobile' ? 'bg-white shadow text-gray-800' : 'text-gray-500 hover:text-gray-700'"
                        class="rounded-md px-2.5 py-1.5 font-medium transition-colors" title="Mobil">
                    <i class="fas fa-mobile-screen"></i>
                </button>
            </div>

            {{-- Refresh --}}
            <button type="button" @click="reload()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-200">
                <i class="fas fa-rotate-right"></i> Yenile
            </button>

            {{-- Open in new tab --}}
            <a :href="iframeSrc" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                <i class="fas fa-arrow-up-right-from-square"></i> Yeni sekme
            </a>

            {{-- Toggle panel --}}
            <button type="button" @click="open = !open"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-200">
                <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                <span x-text="open ? 'Gizle' : 'Göster'"></span>
            </button>
        </div>
    </div>

    <div x-show="open" x-collapse class="p-4 bg-gray-50">
        <div class="mx-auto overflow-hidden transition-all duration-300"
             :style="{ width: widths[device] }">
            <iframe
                :src="iframeSrc"
                id="page-preview-iframe"
                class="w-full rounded-lg border border-gray-200 bg-white"
                style="height: 640px; opacity: 0; transition: opacity 0.3s;"
                loading="lazy"
                @load="$el.style.opacity = '1'"
            ></iframe>
        </div>
        <p class="mt-2 text-center text-xs text-gray-400">
            Sayfayı güncelledikten sonra "Yenile" butonuna basın veya kaydet sonrası otomatik açılır.
        </p>
    </div>
</div>
@endif
