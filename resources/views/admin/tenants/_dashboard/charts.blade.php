{{-- ── Grafikler: istek trendi + paket dağılımı + AI trendi ────────────────
     Chart.js verisi @js() ile enjekte edilir (brace-trap güvenli). --}}
<div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-3">
    {{-- İstek trendi (30 gün) --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-2">
        <h2 class="mb-3 text-sm font-semibold text-gray-800">
            <i class="fas fa-chart-line mr-1.5 text-indigo-500"></i> İstek Trendi (30 gün)
        </h2>
        <div style="position:relative;height:240px;"><canvas id="chart-req-trend"></canvas></div>
    </div>

    {{-- Paket + durum dağılımı --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-sm font-semibold text-gray-800">
            <i class="fas fa-box-open mr-1.5 text-indigo-500"></i> Paket Dağılımı
        </h2>
        <div style="position:relative;height:180px;"><canvas id="chart-pkg"></canvas></div>
        <div class="mt-3 flex flex-wrap gap-1.5 border-t border-gray-50 pt-3 text-xs">
            @foreach($dashboard['status_dist'] as $st => $cnt)
                @php($isActive = $st === 'active')
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 {{ $isActive ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isActive ? 'bg-green-500' : 'bg-yellow-500' }}"></span>
                    {{ $isActive ? 'Aktif' : ($st === 'suspended' ? 'Askıda' : ucfirst($st)) }}: {{ $cnt }}
                </span>
            @endforeach
        </div>
    </div>
</div>

@if(!empty($dashboard['ai_trend']))
    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-sm font-semibold text-gray-800">
            <i class="fas fa-robot mr-1.5 text-emerald-500"></i> AI Kullanımı (30 gün) — istek & maliyet
        </h2>
        <div style="position:relative;height:240px;"><canvas id="chart-ai-trend"></canvas></div>
    </div>
@endif

@include('admin.ai-dashboard._chartjs-loader')

<script>
window.tenantDash = {
    trend:   @js($dashboard['trend']),
    aiTrend: @js($dashboard['ai_trend']),
    pkg:     @js($dashboard['package_dist']),
};
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;
    const D = window.tenantDash || {};
    const palette = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6', '#ec4899', '#84cc16'];

    // İstek trendi
    const rt = document.getElementById('chart-req-trend');
    if (rt && D.trend && (D.trend.labels || []).length) {
        new Chart(rt.getContext('2d'), {
            type: 'line',
            data: {
                labels: D.trend.labels.map(d => d.slice(5)),
                datasets: [
                    { label: 'İstek', data: D.trend.requests, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.10)', fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
                    { label: 'Giriş', data: D.trend.logins, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.06)', fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
            },
        });
    }

    // Paket dağılımı (donut)
    const pk = document.getElementById('chart-pkg');
    if (pk && D.pkg) {
        const labels = Object.keys(D.pkg), data = Object.values(D.pkg);
        if (labels.length) {
            new Chart(pk.getContext('2d'), {
                type: 'doughnut',
                data: { labels: labels, datasets: [{ data: data, backgroundColor: labels.map((_, i) => palette[i % palette.length]), borderWidth: 2, borderColor: '#fff' }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } } },
            });
        }
    }

    // AI trendi (bar + line)
    const at = document.getElementById('chart-ai-trend');
    if (at && (D.aiTrend || []).length) {
        new Chart(at.getContext('2d'), {
            data: {
                labels: D.aiTrend.map(d => (d.date || '').slice(5)),
                datasets: [
                    { label: 'AI İstek', type: 'bar', data: D.aiTrend.map(d => d.requests), backgroundColor: 'rgba(99,102,241,0.6)', borderRadius: 4, yAxisID: 'y' },
                    { label: 'Maliyet ($)', type: 'line', data: D.aiTrend.map(d => d.cost), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.35, pointRadius: 2, yAxisID: 'y1' },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { beginAtZero: true, position: 'left', ticks: { precision: 0 } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: v => '$' + v.toFixed(2) } },
                },
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
            },
        });
    }
});
</script>
