@extends('admin.layouts.app')
@section('title', 'AI Kullanım — Global Dashboard')
@section('page-title', 'AI Kullanım')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-chart-pie text-emerald-500 mr-2"></i>
            AI Kullanım Dashboardu
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            Tüm tenant'lar için aggregated görünüm · <span class="font-mono">{{ $totals['period'] ?? '' }}</span>
        </p>
    </div>
    @if (! empty($totals['fallback_count']) && $totals['fallback_count'] > 10)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2 text-xs text-yellow-800">
            <i class="fas fa-triangle-exclamation"></i>
            Bu ay <strong>{{ $totals['fallback_count'] }}</strong> primary→fallback geçişi oldu — sağlayıcı sağlığını izleyin.
        </div>
    @endif
</div>

{{-- ─── KPI tiles ─────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @include('admin.ai-dashboard._kpi-tile', [
        'label' => 'Toplam İstek',
        'value' => number_format($totals['requests'] ?? 0),
        'icon'  => 'fa-paper-plane',
        'delta' => $totals['requests_delta_pct'] ?? null,
        'color' => 'indigo',
    ])
    @include('admin.ai-dashboard._kpi-tile', [
        'label' => 'Toplam Token',
        'value' => number_format($totals['tokens'] ?? 0),
        'icon'  => 'fa-coins',
        'delta' => $totals['tokens_delta_pct'] ?? null,
        'color' => 'amber',
    ])
    @include('admin.ai-dashboard._kpi-tile', [
        'label' => 'Toplam Maliyet',
        'value' => '$'.number_format($totals['cost_usd'] ?? 0, 2),
        'icon'  => 'fa-dollar-sign',
        'delta' => $totals['cost_delta_pct'] ?? null,
        'color' => 'emerald',
    ])
    @include('admin.ai-dashboard._kpi-tile', [
        'label' => 'Global Hata Oranı',
        'value' => ($totals['error_rate'] ?? 0).'%',
        'icon'  => 'fa-triangle-exclamation',
        'delta' => null,
        'color' => (($totals['error_rate'] ?? 0) > 5 ? 'red' : 'gray'),
    ])
</div>

{{-- ─── Chart row ─────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border p-5 lg:col-span-2">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Son 30 Gün — Tüm Tenant'lar</h3>
        <div style="position:relative;height:280px;"><canvas id="chart-daily-global"></canvas></div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Feature Popülerliği</h3>
        @if (empty($features))
            <p class="text-xs text-gray-400">Veri yok.</p>
        @else
            <div style="position:relative;height:280px;"><canvas id="chart-features-global"></canvas></div>
        @endif
    </div>
</div>

{{-- ─── Top tenants + Provider stats ───────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">En Yüksek 10 Tenant (Maliyet)</h3>
        @if (empty($topTenants))
            <p class="text-xs text-gray-400">Bu ay BYOK-olmayan kullanım yok.</p>
        @else
            <table class="w-full text-xs">
                <thead class="text-gray-500">
                    <tr><th class="text-left py-1">Tenant</th><th class="text-right">İstek</th><th class="text-right">Token</th><th class="text-right">Maliyet</th></tr>
                </thead>
                <tbody>
                    @foreach ($topTenants as $t)
                        <tr class="border-t border-gray-100">
                            <td class="py-1.5">
                                <a href="{{ route('admin.tenants.ai-usage', $t['tenant_id']) }}"
                                   class="font-medium text-indigo-600 hover:underline">
                                    {{ $tenantNames[$t['tenant_id']] ?? $t['tenant_id'] }}
                                </a>
                                <span class="text-gray-400 ml-1 font-mono">{{ $t['tenant_id'] }}</span>
                            </td>
                            <td class="text-right">{{ number_format($t['requests']) }}</td>
                            <td class="text-right">{{ number_format($t['tokens']) }}</td>
                            <td class="text-right font-mono font-medium">${{ number_format($t['cost'], 4) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Sağlayıcı Dağılımı</h3>
        @if (empty($providers))
            <p class="text-xs text-gray-400">Veri yok.</p>
        @else
            <ul class="space-y-2.5">
                @php $maxRequests = max(array_column($providers, 'requests')) ?: 1; @endphp
                @foreach ($providers as $p)
                    <li>
                        <div class="flex items-center justify-between text-xs mb-0.5">
                            <span class="capitalize font-medium text-gray-800">{{ $p['provider'] }}</span>
                            <span class="text-gray-500 font-mono">
                                {{ number_format($p['requests']) }} · ${{ number_format($p['cost'], 2) }}
                                @if ($p['fallback_pct'] > 0)
                                    · <span class="text-yellow-600">{{ $p['fallback_pct'] }}% fallback</span>
                                @endif
                            </span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500" style="width: {{ round(($p['requests'] / $maxRequests) * 100) }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <hr class="my-4 border-gray-200">

        <div class="flex items-center justify-between text-xs">
            <span class="text-gray-500">BYOK Oranı</span>
            <span class="font-bold text-emerald-600">{{ $totals['byok_ratio'] ?? 0 }}%</span>
        </div>
        @if (($totals['byok_ratio'] ?? 0) > 0)
            <p class="text-[11px] text-gray-500 mt-1">
                BYOK üzerinden gelen istekler agency kotasından düşmez — kendi anahtarına faturalanır.
            </p>
        @endif
    </div>
</div>

{{-- ─── Feature global table ──────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Feature Detayı (Bu Ay)</h3>
    @if (empty($features))
        <p class="text-xs text-gray-400">Veri yok.</p>
    @else
        <table class="w-full text-xs">
            <thead class="text-gray-500">
                <tr>
                    <th class="text-left py-1">Feature</th>
                    <th class="text-right">İstek</th>
                    <th class="text-right">Pay</th>
                    <th class="text-right">Token</th>
                    <th class="text-right">Maliyet</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($features as $f)
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="py-1.5 font-mono text-indigo-700">{{ $f['feature'] }}</td>
                        <td class="text-right">{{ number_format($f['requests']) }}</td>
                        <td class="text-right text-gray-500">%{{ $f['pct'] }}</td>
                        <td class="text-right">{{ number_format($f['tokens']) }}</td>
                        <td class="text-right font-mono">${{ number_format($f['cost'], 4) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@include('admin.ai-dashboard._chartjs-loader')

<script>
window.aiDashboardData = {
    daily: @js($dailyTrend),
    features: @js($features),
};
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;
    const data = window.aiDashboardData || {};

    if (data.daily && data.daily.length) {
        new Chart(document.getElementById('chart-daily-global').getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.daily.map(d => d.date.slice(5)),
                datasets: [
                    {
                        label: 'İstek',
                        type: 'bar',
                        data: data.daily.map(d => d.requests),
                        backgroundColor: 'rgba(99, 102, 241, 0.6)',
                        borderRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Maliyet ($)',
                        type: 'line',
                        data: data.daily.map(d => d.cost),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.35,
                        pointRadius: 2,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y:  { beginAtZero: true, position: 'left', ticks: { precision: 0 } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: v => '$'+v.toFixed(2) } },
                },
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
            },
        });
    }

    const featuresEl = document.getElementById('chart-features-global');
    if (featuresEl && data.features && data.features.length) {
        const palette = ['#6366f1','#10b981','#f59e0b','#ef4444','#06b6d4','#8b5cf6','#ec4899','#84cc16'];
        new Chart(featuresEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.features.map(f => f.feature),
                datasets: [{
                    data: data.features.map(f => f.requests),
                    backgroundColor: data.features.map((_, i) => palette[i % palette.length]),
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                },
            },
        });
    }
});
</script>
@endsection
