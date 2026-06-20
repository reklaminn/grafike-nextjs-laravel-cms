{{-- Sabit yüzen kaydet çubuğu — sidebar kolonuna değil VIEWPORT'a sabit
     (sticky sidebar kolonu kısa olunca üste kayıyordu). Hep sağ-altta. --}}
<div class="fixed bottom-6 right-6 z-40 flex items-center gap-2 rounded-xl border border-gray-200 bg-white/95 p-2.5 shadow-xl backdrop-blur">
    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
        <i class="fas fa-save"></i> Kaydet
    </button>
    <a href="{{ route('admin.section-templates.index') }}"
       class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200">
        İptal
    </a>
    @if(isset($sectionTemplate) && $sectionTemplate->exists)
        <form method="POST" action="{{ route('admin.section-templates.duplicate', $sectionTemplate, false) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100">
                <i class="fas fa-copy"></i> Klonla
            </button>
        </form>
    @endif
</div>
