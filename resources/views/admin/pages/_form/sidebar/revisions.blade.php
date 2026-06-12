@if(isset($page) && $page->exists)
@php $revisions = $page->revisions()->limit(10)->get(); @endphp
@if($revisions->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-base font-semibold text-gray-800 mb-4">
        <i class="fas fa-clock-rotate-left mr-2 text-amber-500"></i>Revizyon Geçmişi
    </h3>

    @php
        $fieldLabels = [
            'title'         => 'Başlık',
            'sections_json' => 'Bloklar',
            'layout_json'   => 'Eski düzen',
            'custom_css'    => 'CSS',
            'custom_js'     => 'JS',
        ];
    @endphp

    <div class="space-y-2">
        @foreach($revisions as $revision)
            @php
                $snapshot      = $revision->snapshot ?? [];
                $changedFields = $snapshot['changed_fields'] ?? [];
                $blockCount    = count(\App\Support\FrontendSections::flattenBlocks($snapshot['sections_json'] ?? []));
                $reasonLabel   = match (true) {
                    str_starts_with((string) $revision->reason, 'restore-from-revision') => 'Geri yükleme öncesi',
                    $revision->reason === 'pre-update' => 'Kayıt öncesi otomatik',
                    default => $revision->reason ?? 'Elle kaydedildi',
                };
            @endphp
            <div class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <div class="text-xs font-medium text-gray-700 truncate">{{ $reasonLabel }}</div>
                        <div class="text-[11px] text-gray-400">
                            {{ $revision->created_at?->diffForHumans() }}
                            @if($revision->admin?->name) · {{ $revision->admin->name }} @endif
                            @if($blockCount > 0) · {{ $blockCount }} blok @endif
                        </div>
                    </div>
                    <form method="POST"
                          action="{{ route('admin.pages.restore-revision', [$page, $revision], false) }}"
                          onsubmit="return confirm('Bu revizyon geri yüklensin mi?\n\n(Geri yüklemeden önce mevcut durum otomatik kaydedilir.)')">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-1 text-[11px] font-medium text-amber-700 hover:bg-amber-100">
                            <i class="fas fa-rotate-left"></i> Geri yükle
                        </button>
                    </form>
                </div>

                {{-- Bu revizyonda hangi alanlar değişmişti --}}
                @if(!empty($changedFields))
                    <div class="mt-1.5 flex flex-wrap gap-1">
                        @foreach($changedFields as $field)
                            <span class="rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium text-blue-700">
                                {{ $fieldLabels[$field] ?? $field }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif
@endif
