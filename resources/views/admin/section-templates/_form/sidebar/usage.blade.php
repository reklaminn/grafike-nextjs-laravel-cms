@php
    // Sayfalar dil başına ayrı satır (unique slug+language_id) → aynı /home her
    // dilde tekrar görünüyordu. Slug'a göre tekilleştir; varyant sayısını rozetle.
    $allUsage      = collect($usagePages ?? []);
    $variantCounts = $allUsage->groupBy('slug')->map->count();
    $uniquePages   = $allUsage->unique('slug')->values();
@endphp
<div class="rounded-xl border border-sky-200 bg-sky-50 p-6 shadow-sm">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-base font-semibold text-sky-900">Bu Şablonu Kullanan Sayfalar</h3>
        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-sky-700">{{ $uniquePages->count() }}</span>
    </div>

    @if($uniquePages->isNotEmpty())
        <div class="mt-4 space-y-2">
            @foreach($uniquePages as $usedPage)
                @php($variants = $variantCounts[$usedPage['slug']] ?? 1)
                <a href="{{ route('admin.pages.edit', $usedPage['id']) }}"
                   class="flex items-center justify-between gap-3 rounded-lg border border-sky-100 bg-white px-3 py-2 text-sm text-sky-900 hover:bg-sky-100">
                    <span class="min-w-0 truncate">
                        <i class="fas fa-file-lines mr-1.5 text-sky-500"></i>{{ $usedPage['title'] }}
                        @if($variants > 1)
                            <span class="ml-1 rounded-full bg-sky-100 px-1.5 py-0.5 text-[10px] font-medium text-sky-600" title="{{ $variants }} dil varyantında kullanılıyor">{{ $variants }} dil</span>
                        @endif
                    </span>
                    <span class="shrink-0 font-mono text-xs text-sky-500">/{{ $usedPage['slug'] }}</span>
                </a>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-sky-800">
            Bu şablonda yapacağın değişiklikler yukarıdaki sayfalardaki block render sonucunu etkileyebilir.
        </p>
    @else
        <p class="mt-3 text-sm text-sky-800">Bu şablon henüz hiçbir sayfada kullanılmıyor.</p>
    @endif
</div>
