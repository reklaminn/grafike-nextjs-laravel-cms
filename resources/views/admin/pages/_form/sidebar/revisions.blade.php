@if(isset($page) && $page->exists)
@php $revisions = $page->revisions()->limit(10)->get(); @endphp
@if($revisions->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-base font-semibold text-gray-800 mb-4">
        <i class="fas fa-clock-rotate-left mr-2 text-amber-500"></i>Revizyon Geçmişi
    </h3>

    <div class="space-y-2">
        @foreach($revisions as $revision)
            <div class="flex items-center justify-between gap-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2">
                <div class="min-w-0">
                    <div class="text-xs font-medium text-gray-700 truncate">
                        {{ $revision->reason ?? 'Elle kaydedildi' }}
                    </div>
                    <div class="text-[11px] text-gray-400">
                        {{ $revision->created_at?->diffForHumans() }}
                    </div>
                </div>
                <form method="POST"
                      action="{{ route('admin.pages.restore-revision', [$page, $revision], false) }}"
                      onsubmit="return confirm('Bu revizyon geri yüklensin mi?')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-1 text-[11px] font-medium text-amber-700 hover:bg-amber-100">
                        <i class="fas fa-rotate-left"></i> Geri yükle
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endif
@endif
