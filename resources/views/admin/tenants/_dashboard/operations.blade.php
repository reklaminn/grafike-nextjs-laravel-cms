{{-- ── Operasyon kartları ──────────────────────────────────────────────── --}}
<div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">

    {{-- En çok kullanan siteler (bugün) --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-sm font-semibold text-gray-800">
            <i class="fas fa-fire mr-1.5 text-orange-500"></i> Bugün En Çok Kullanan
        </h2>
        @if(empty($dashboard['top_requests']))
            <p class="py-4 text-center text-xs text-gray-400">Bugün henüz trafik yok.</p>
        @else
            @php($maxReq = max(1, collect($dashboard['top_requests'])->max('requests')))
            <div class="space-y-2">
                @foreach($dashboard['top_requests'] as $r)
                    <a href="{{ route('admin.tenants.show', $r['id']) }}" class="block">
                        <div class="mb-0.5 flex items-center justify-between text-xs">
                            <span class="truncate font-medium text-gray-700">{{ $r['name'] }}</span>
                            <span class="ml-2 flex-shrink-0 font-mono text-gray-500">{{ number_format($r['requests']) }}@if($r['req_limit']) <span class="text-gray-300">/ {{ number_format($r['req_limit']) }}</span>@endif</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-indigo-400" style="width: {{ min(100, (int) round($r['requests'] / $maxReq * 100)) }}%"></div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Aktif kota uzatmaları --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-sm font-semibold text-gray-800">
            <i class="fas fa-arrow-up-right-dots mr-1.5 text-sky-500"></i> Aktif Kota Uzatmaları
        </h2>
        @if(empty($dashboard['quota_ext']))
            <p class="py-4 text-center text-xs text-gray-400">Aktif geçici kota yok.</p>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($dashboard['quota_ext'] as $q)
                    <a href="{{ route('admin.tenants.show', $q['tenant_id']) }}" class="flex items-center justify-between py-2 text-xs hover:bg-gray-50">
                        <div class="min-w-0">
                            <span class="font-medium text-gray-700">{{ $q['tenant_name'] }}</span>
                            <span class="ml-1 text-emerald-600">+{{ number_format($q['extra']) }}/gün</span>
                            @if($q['reason'])<span class="ml-1 text-gray-400">· {{ \Illuminate\Support\Str::limit($q['reason'], 30) }}</span>@endif
                        </div>
                        <span class="ml-2 flex-shrink-0 rounded px-1.5 py-0.5 {{ $q['days_left'] <= 1 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500' }}">
                            @if($q['days_left'] <= 0) bugün biter @else {{ $q['days_left'] }} gün @endif
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Yedek uyarıları --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-sm font-semibold text-gray-800">
            <i class="fas fa-shield-halved mr-1.5 text-rose-500"></i> Yedek Uyarıları
        </h2>
        @if(empty($dashboard['backup_alerts']))
            <p class="py-4 text-center text-xs text-gray-400"><i class="fas fa-circle-check mr-1 text-green-400"></i> Tüm sitelerin güncel yedeği var.</p>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($dashboard['backup_alerts'] as $a)
                    <a href="{{ route('admin.tenants.show', $a['id']) }}" class="flex items-center justify-between py-2 text-xs hover:bg-gray-50">
                        <span class="truncate font-medium text-gray-700">{{ $a['name'] }}</span>
                        <span class="ml-2 flex-shrink-0 {{ $a['last'] === null ? 'font-medium text-red-600' : 'text-gray-500' }}">
                            @if($a['last'] === null) yedek yok @else {{ $a['days_ago'] }} gün önce @endif
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Son eklenen siteler --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-sm font-semibold text-gray-800">
            <i class="fas fa-clock-rotate-left mr-1.5 text-violet-500"></i> Son Eklenen Siteler
        </h2>
        @if(empty($dashboard['recent']))
            <p class="py-4 text-center text-xs text-gray-400">Henüz site yok.</p>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($dashboard['recent'] as $t)
                    <a href="{{ route('admin.tenants.show', $t['id']) }}" class="flex items-center justify-between py-2 text-xs hover:bg-gray-50">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded bg-violet-100 text-[10px] font-bold uppercase text-violet-600">{{ substr($t['name'], 0, 1) }}</span>
                            <span class="truncate font-medium text-gray-700">{{ $t['name'] }}</span>
                        </div>
                        <span class="ml-2 flex-shrink-0 text-gray-400">{{ $t['created_at']?->format('d.m.Y') }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Pano alt bilgi --}}
<div class="mb-4 flex items-center justify-between">
    <h2 class="text-sm font-semibold text-gray-700"><i class="fas fa-list mr-1.5 text-gray-400"></i> Tüm Siteler</h2>
    <span class="text-[11px] text-gray-400">
        Pano verisi {{ $dashboard['generated_at']?->diffForHumans() }} hesaplandı · ağır metrikler saatlik güncellenir
    </span>
</div>
