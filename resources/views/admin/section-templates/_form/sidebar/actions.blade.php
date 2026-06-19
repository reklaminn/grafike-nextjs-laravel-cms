<div class="sticky bottom-4 z-20 flex gap-3 rounded-xl border border-gray-200 bg-white/95 p-3 shadow-lg backdrop-blur">
    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
        <i class="fas fa-save"></i> Kaydet
    </button>
    <a href="{{ route('admin.section-templates.index') }}"
       class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200">
        İptal
    </a>
    @if(isset($sectionTemplate) && $sectionTemplate->exists)
        <form method="POST" action="{{ route('admin.section-templates.duplicate', $sectionTemplate, false) }}" class="ml-auto">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100">
                <i class="fas fa-copy"></i> Klonla
            </button>
        </form>
    @endif
</div>
