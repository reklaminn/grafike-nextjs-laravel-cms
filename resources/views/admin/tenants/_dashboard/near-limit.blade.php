{{-- ── Limite yakın / aşan siteler ──────────────────────────────────────── --}}
<div class="mb-5 rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
        <h2 class="text-sm font-semibold text-gray-800">
            <i class="fas fa-gauge-high mr-1.5 text-amber-500"></i> Limite Yakın / Aşan Siteler
        </h2>
        <span class="text-xs text-gray-400">disk · istek · kullanıcı — eşik %80</span>
    </div>

    @if(empty($dashboard['near_limit']))
        <div class="px-5 py-8 text-center text-sm text-gray-400">
            <i class="fas fa-circle-check mr-1 text-green-400"></i> Tüm siteler limitlerinin altında.
        </div>
    @else
        <div class="divide-y divide-gray-50">
            @foreach($dashboard['near_limit'] as $r)
                @php
                    $over = $r['level'] === 'over';
                    $pct  = min(100, (int) round($r['max_ratio'] * 100));
                    $b    = $r['bottleneck'];
                @endphp
                <a href="{{ route('admin.tenants.show', $r['id']) }}"
                   class="flex items-center gap-4 px-5 py-3 transition-colors hover:bg-gray-50">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-xs font-bold uppercase text-indigo-600">
                        {{ substr($r['name'], 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="truncate font-medium text-gray-800">{{ $r['name'] }}</span>
                            <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $over ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $over ? 'AŞILDI' : 'YAKIN' }}
                            </span>
                            <span class="hidden text-xs text-gray-400 sm:inline">· {{ $r['package_label'] }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full {{ $over ? 'bg-red-500' : 'bg-amber-400' }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <div class="text-sm font-semibold {{ $over ? 'text-red-600' : 'text-amber-600' }}">%{{ $pct }}</div>
                        @if($b)
                            <div class="text-[11px] text-gray-400">{{ $b['label'] }}: {{ $b['used'] }}{{ $b['unit'] ? ' '.$b['unit'] : '' }} / {{ $b['limit'] }}</div>
                        @endif
                    </div>
                    <i class="fas fa-chevron-right text-xs text-gray-300"></i>
                </a>
            @endforeach
        </div>
    @endif
</div>
