{{-- AI ile Oluştur/Düzenle — editör modalı. JS script.blade.php'de. Gizli başlar. --}}
<div id="ai_template_modal" class="fixed inset-0 z-[95] hidden items-center justify-center bg-black/50 p-4">
    <div class="flex w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
            <h3 class="text-sm font-semibold text-gray-800">
                <i class="fas fa-wand-magic-sparkles mr-1.5 text-violet-500"></i> AI ile Oluştur / Düzenle
            </h3>
            <button type="button" id="ai_tm_close" class="rounded p-1.5 text-gray-400 hover:bg-gray-100">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <div class="space-y-3 px-5 py-4">
            <div class="flex gap-3 text-xs">
                <label class="inline-flex items-center gap-1.5">
                    <input type="radio" name="ai_tm_mode" value="edit" checked> Mevcudu düzenle
                </label>
                <label class="inline-flex items-center gap-1.5">
                    <input type="radio" name="ai_tm_mode" value="create"> Sıfırdan oluştur
                </label>
            </div>
            <textarea id="ai_tm_prompt" rows="4"
                      placeholder="Düzenle örn: Başlığı büyüt, butonları altın renk yap. — Oluştur örn: Kurumsal hero, 2 buton, sağda görsel."
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-violet-400 focus:ring-2 focus:ring-violet-500"></textarea>
            <p class="text-[11px] text-gray-400">Sonuç editöre basılır; beğenmezsen toolbar'daki <strong>Dönüşümü Geri Al</strong> ile geri alırsın. Kaydetmeden önce gözden geçir.</p>
            <div id="ai_tm_status" class="hidden rounded-md p-2 text-xs"></div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-gray-100 bg-gray-50/70 px-5 py-3">
            <button type="button" id="ai_tm_cancel" class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-100">İptal</button>
            <button type="button" id="ai_tm_run" class="rounded-lg bg-violet-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-violet-700 disabled:opacity-50">
                <i class="fas fa-wand-magic-sparkles mr-1"></i> Üret
            </button>
        </div>
    </div>
</div>
