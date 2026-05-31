@php
    $ru = $resourceUsage ?? [];
    // Depolama = dosya + tenant DB boyutu (disk darboğazı bütünsel ölçülür).
    $diskUsed = round(($ru['storage_used_mb'] ?? 0) + ($ru['db_size_mb'] ?? 0), 1);
    $storagePct = ($ru['storage_quota_mb'] ?? null)
        ? min(100, (int) round(($diskUsed / max(1, $ru['storage_quota_mb'])) * 100))
        : null;
    $usersPct = ($ru['max_users'] ?? null)
        ? min(100, (int) round((($ru['users_count'] ?? 0) / max(1, $ru['max_users'])) * 100))
        : null;
    $up = $ru['upgrade'] ?? null;
@endphp
<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-gauge-high text-emerald-500"></i> Kaynak Kullanımı
        </h2>
        <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700">{{ $ru['package_label'] ?? '—' }} paketi</span>
    </div>

    {{-- Yükseltme önerisi --}}
    @if($up && in_array($up['level'] ?? 'ok', ['warn', 'over'], true))
        @php
            $over = ($up['level'] === 'over');
            $b = $up['bottleneck'] ?? null;
            $recKey = $up['recommended'] ?? null;
            $recLabel = $recKey ? (config("packages.packages.{$recKey}.label") ?? ucfirst($recKey)) : null;
        @endphp
        <div class="mb-4 p-3 rounded-lg border {{ $over ? 'bg-red-50 border-red-200 text-red-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
            <div class="flex items-start gap-2">
                <i class="fas {{ $over ? 'fa-circle-exclamation' : 'fa-triangle-exclamation' }} mt-0.5"></i>
                <div class="text-sm">
                    <div class="font-medium">
                        {{ $over ? 'Paket limiti aşıldı' : 'Paket limitine yaklaşıldı' }}
                        @if($b)
                            <span class="font-normal">— {{ $b['label'] }}: {{ $b['used'] }}{{ $b['unit'] }} / {{ $b['limit'] ?? '∞' }} (%{{ (int) round((($b['ratio'] ?? 0)) * 100) }})</span>
                        @endif
                    </div>
                    @if($recLabel)
                        <div class="text-xs mt-0.5">Önerilen paket: <strong>{{ $recLabel }}</strong></div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Yönetici kullanıcı --}}
    <div class="mb-4">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
            <span>Yönetici kullanıcı</span>
            <span>{{ $ru['users_count'] ?? 0 }} / {{ ($ru['max_users'] ?? null) === null ? '∞' : $ru['max_users'] }}</span>
        </div>
        @if($usersPct !== null)
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full {{ $usersPct >= 100 ? 'bg-red-500' : 'bg-indigo-500' }}" style="width: {{ $usersPct }}%"></div>
            </div>
        @endif
    </div>

    {{-- Depolama (dosya + DB) --}}
    <div class="mb-4">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
            <span>Depolama</span>
            <span>{{ $diskUsed }} MB / {{ ($ru['storage_quota_mb'] ?? null) === null ? '∞' : $ru['storage_quota_mb'].' MB' }}</span>
        </div>
        @if($storagePct !== null)
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full {{ $storagePct >= 100 ? 'bg-red-500' : ($storagePct >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $storagePct }}%"></div>
            </div>
        @endif
        <div class="text-[11px] text-gray-400 mt-1">
            Dosya {{ $ru['storage_used_mb'] ?? 0 }} MB · Veritabanı {{ $ru['db_size_mb'] ?? 0 }} MB
        </div>
    </div>

    {{-- Bugün --}}
    <div class="grid grid-cols-2 gap-3 text-center">
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="text-lg font-bold text-gray-800">{{ number_format($ru['requests_today'] ?? 0) }}</div>
            <div class="text-xs text-gray-500">Bugün istek{{ ($ru['requests_quota'] ?? null) ? ' / '.number_format($ru['requests_quota']) : '' }}</div>
        </div>
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="text-lg font-bold text-gray-800">{{ number_format($ru['logins_today'] ?? 0) }}</div>
            <div class="text-xs text-gray-500">Bugün üye girişi</div>
        </div>
    </div>

    <p class="text-[11px] text-gray-400 mt-3">
        Depolama/kullanıcı kotaları pakete bağlıdır. Günlük istek limiti yalnızca pakette tanımlıysa uygulanır (varsayılan: kapalı, yalnızca sayım).
    </p>
</div>
