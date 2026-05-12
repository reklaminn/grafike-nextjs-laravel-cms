@extends('admin.layouts.app')
@section('title', ($tenant->name ?? $tenant->id).' — AI Kullanım')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-800">{{ $tenant->name ?? $tenant->id }}</h1>
        <p class="text-sm text-gray-500">
            <i class="fas fa-chart-line mr-1 text-emerald-500"></i>
            AI Kullanım Detayı &middot; <span class="font-mono">{{ $totals['period'] ?? '' }}</span>
        </p>
    </div>
    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 font-medium">
        <i class="fas fa-tag mr-1"></i>{{ $plan['label'] ?? '—' }}
    </span>
</div>

{{-- ─── KPI tiles ─────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @include('admin.ai-dashboard._kpi-tile', [
        'label' => 'İstek',
        'value' => number_format($totals['requests'] ?? 0),
        'icon'  => 'fa-paper-plane',
        'delta' => $totals['requests_delta_pct'] ?? null,
        'color' => 'indigo',
    ])
    @include('admin.ai-dashboard._kpi-tile', [
        'label' => 'Token',
        'value' => number_format($totals['tokens'] ?? 0),
        'icon'  => 'fa-coins',
        'delta' => $totals['tokens_delta_pct'] ?? null,
        'color' => 'amber',
    ])
    @include('admin.ai-dashboard._kpi-tile', [
        'label' => 'Maliyet',
        'value' => '$'.number_format($totals['cost_usd'] ?? 0, 4),
        'icon'  => 'fa-dollar-sign',
        'delta' => $totals['cost_delta_pct'] ?? null,
        'color' => 'emerald',
    ])
    @include('admin.ai-dashboard._kpi-tile', [
        'label' => 'Hata oranı',
        'value' => ($totals['error_rate'] ?? 0).'%',
        'icon'  => 'fa-triangle-exclamation',
        'delta' => null,
        'color' => (($totals['error_rate'] ?? 0) > 5 ? 'red' : 'gray'),
    ])
</div>

{{-- ─── Charts row ─────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border p-5 lg:col-span-2">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Son 30 Gün — Günlük Trend</h3>
        <canvas id="chart-daily" style="height: 250px"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Feature Dağılımı</h3>
        @if (empty($features))
            <p class="text-xs text-gray-400">Bu ay hiç AI isteği yok.</p>
        @else
            <canvas id="chart-features" style="height: 250px"></canvas>
        @endif
    </div>
</div>

{{-- ─── Provider + BYOK row ────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Sağlayıcı Kullanımı</h3>
        @if (empty($providers))
            <p class="text-xs text-gray-400">Veri yok.</p>
        @else
            <ul class="space-y-2 text-sm">
                @foreach ($providers as $p)
                    <li class="flex items-center justify-between gap-2">
                        <span class="capitalize font-medium text-gray-800">{{ $p['provider'] }}</span>
                        <span class="text-xs text-gray-500">
                            {{ number_format($p['requests']) }} istek · ${{ number_format($p['cost'], 4) }}
                            @if ($p['fallback_pct'] > 0)
                                <span class="ml-1 text-yellow-600">·{{ $p['fallback_pct'] }}% fallback</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">BYOK Oranı</h3>
        <div class="flex items-end gap-3">
            <span class="text-3xl font-bold text-emerald-600">{{ $totals['byok_ratio'] ?? 0 }}%</span>
            <span class="text-xs text-gray-500 mb-1">BYOK üzerinden çalışan istekler</span>
        </div>
        @if (($totals['byok_ratio'] ?? 0) >= 50)
            <p class="text-[11px] text-emerald-700 mt-2">
                <i class="fas fa-check-circle"></i> Bu site büyük ölçüde kendi API anahtarını kullanıyor; agency kotası düşmüyor.
            </p>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Sağlayıcı Geçişi</h3>
        <div class="flex items-end gap-3">
            <span class="text-3xl font-bold text-{{ ($totals['fallback_count'] ?? 0) > 5 ? 'red' : 'gray' }}-600">
                {{ $totals['fallback_count'] ?? 0 }}
            </span>
            <span class="text-xs text-gray-500 mb-1">primary→fallback geçişi</span>
        </div>
        @if (($totals['fallback_count'] ?? 0) > 0)
            <p class="text-[11px] text-yellow-700 mt-2">
                <i class="fas fa-info-circle"></i> Primary sağlayıcıda transient hata oldu.
            </p>
        @endif
    </div>
</div>

{{-- ─── Top expensive calls + Feature table ────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Feature Detayı</h3>
        @if (empty($features))
            <p class="text-xs text-gray-400">Henüz veri yok.</p>
        @else
            <table class="w-full text-xs">
                <thead class="text-gray-500">
                    <tr><th class="text-left py-1">Feature</th><th class="text-right">İstek</th><th class="text-right">Token</th><th class="text-right">Maliyet</th></tr>
                </thead>
                <tbody>
                    @foreach ($features as $f)
                        <tr class="border-t border-gray-100">
                            <td class="py-1.5 font-mono text-gray-800">{{ $f['feature'] }}</td>
                            <td class="text-right">{{ number_format($f['requests']) }}</td>
                            <td class="text-right">{{ number_format($f['tokens']) }}</td>
                            <td class="text-right font-mono">${{ number_format($f['cost'], 4) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">En Pahalı Son 10 İstek</h3>
        @if (empty($topCalls))
            <p class="text-xs text-gray-400">Henüz veri yok.</p>
        @else
            <ul class="space-y-1.5 text-xs">
                @foreach ($topCalls as $c)
                    <li class="flex items-center justify-between gap-2 py-1 border-b border-gray-100 last:border-0">
                        <div class="flex-1 min-w-0">
                            <span class="font-mono text-indigo-700">{{ $c->feature }}</span>
                            <span class="text-gray-400"> · {{ $c->model }}</span>
                            <span class="text-gray-400 ml-1">{{ $c->created_at?->format('d.m H:i') }}</span>
                        </div>
                        <span class="font-mono text-gray-800 whitespace-nowrap">${{ number_format($c->cost_usd, 5) }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

{{-- ─── Recent activity timeline ─────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Son 25 İstek</h3>
    @if (empty($recent))
        <p class="text-xs text-gray-400">Henüz veri yok.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="text-gray-500">
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 px-2">Tarih</th>
                        <th class="text-left py-2 px-2">Feature</th>
                        <th class="text-left py-2 px-2">Sağlayıcı</th>
                        <th class="text-left py-2 px-2">Model</th>
                        <th class="text-right py-2 px-2">Token</th>
                        <th class="text-right py-2 px-2">Maliyet</th>
                        <th class="text-center py-2 px-2">Durum</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recent as $r)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-1.5 px-2 text-gray-600 font-mono">{{ $r->created_at?->format('d.m H:i') }}</td>
                            <td class="py-1.5 px-2 font-mono text-indigo-700">{{ $r->feature }}</td>
                            <td class="py-1.5 px-2 capitalize">{{ $r->provider }}</td>
                            <td class="py-1.5 px-2 text-gray-500 font-mono text-[10px]">{{ $r->model }}</td>
                            <td class="py-1.5 px-2 text-right font-mono">{{ number_format($r->total_tokens) }}</td>
                            <td class="py-1.5 px-2 text-right font-mono">${{ number_format($r->cost_usd, 5) }}</td>
                            <td class="py-1.5 px-2 text-center">
                                @if ($r->success)
                                    <span class="inline-flex items-center gap-1 text-emerald-600"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-red-600" title="{{ $r->error_message }}">
                                        <i class="fas fa-xmark"></i>
                                    </span>
                                @endif
                                @if ($r->byok)
                                    <span class="ml-1 text-[10px] px-1 bg-emerald-100 text-emerald-700 rounded">BYOK</span>
                                @endif
                                @if ($r->fallback_used)
                                    <span class="ml-1 text-[10px] px-1 bg-yellow-100 text-yellow-700 rounded">fallback</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@include('admin.ai-dashboard._chartjs-loader')

<script>
window.aiUsageData = {
    daily: @js($dailyTrend),
    features: @js($features),
};
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    const data = window.aiUsageData || {};

    // ── Daily trend (bar — requests + line — cost) ──────────────
    if (data.daily && data.daily.length) {
        const labels = data.daily.map(d => d.date.slice(5)); // MM-DD
        new Chart(document.getElementById('chart-daily').getContext('2d'), {
            type: 'bar',
            data: {
                labels,
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
                    y:  { beginAtZero: true, position: 'left',  ticks: { precision: 0 } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: v => '$'+v.toFixed(2) } },
                },
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
            },
        });
    }

    // ── Feature donut ──────────────────────────────────────────
    const featuresEl = document.getElementById('chart-features');
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
                    tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.parsed} istek` } },
                },
            },
        });
    }
});
</script>
@endsection
