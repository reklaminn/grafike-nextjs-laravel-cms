{{-- Şablona Dönüştürme Sihirbazı — adım adım modal. İçerik JS ile #cw_body'ye
     render edilir (script.blade.php). Varsayılan gizli. --}}
<div id="convert_wizard" class="fixed inset-0 z-[90] hidden items-center justify-center bg-black/50 p-4">
    <div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
            <h3 id="cw_title" class="text-sm font-semibold text-gray-800">
                <i class="fas fa-hat-wizard mr-1.5 text-violet-500"></i> Şablona Dönüştürme Sihirbazı
            </h3>
            <button type="button" id="cw_close" class="rounded p-1.5 text-gray-400 hover:bg-gray-100" title="İptal et (geri al)">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <div id="cw_body" class="flex-1 overflow-y-auto px-5 py-4 text-sm"></div>

        <div class="flex items-center justify-between gap-2 border-t border-gray-100 bg-gray-50/70 px-5 py-3">
            <button type="button" id="cw_cancel"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-100">
                İptal (geri al)
            </button>
            <div class="flex items-center gap-2">
                <button type="button" id="cw_skip"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">
                    Atla →
                </button>
                <button type="button" id="cw_next"
                        class="rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                    İleri →
                </button>
            </div>
        </div>
    </div>
</div>
