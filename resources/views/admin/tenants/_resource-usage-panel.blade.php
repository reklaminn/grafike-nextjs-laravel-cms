@php
    $ru = $resourceUsage ?? [];
    $storagePct = ($ru['storage_quota_mb'] ?? null)
        ? min(100, (int) round(($ru['storage_used_mb'] / max(1, $ru['storage_quota_mb'])) * 100))
        : null;
    $usersPct = ($ru['max_users'] ?? null)
        ? min(100, (int) round((($ru['users_count'] ?? 0) / max(1, $ru['max_users'])) * 100))
        : null;
@endphp
<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-gauge-high text-emerald-500"></i> Kaynak Kullanımı
        </h2>
        <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700">{{ $ru['package_label'] ?? '—' }} paketi</span>
    </div>

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

    {{-- Depolama --}}
    <div class="mb-4">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
            <span>Depolama</span>
            <span>{{ $ru['storage_used_mb'] ?? 0 }} MB / {{ ($ru['storage_quota_mb'] ?? null) === null ? '∞' : $ru['storage_quota_mb'].' MB' }}</span>
        </div>
        @if($storagePct !== null)
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full {{ $storagePct >= 100 ? 'bg-red-500' : ($storagePct >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $storagePct }}%"></div>
            </div>
        @endif
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
