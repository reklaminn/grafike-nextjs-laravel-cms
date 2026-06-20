@php($k = $dashboard['kpis'])
{{-- ── Özet KPI şeridi ─────────────────────────────────────────────────── --}}
<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    {{-- Toplam site --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500">Toplam Site</span>
            <i class="fas fa-building text-gray-300"></i>
        </div>
        <div class="mt-2 text-2xl font-bold text-gray-900">{{ $k['total'] }}</div>
        <div class="mt-1 text-xs text-gray-400">
            <span class="text-green-600">{{ $k['active'] }} aktif</span>@if($k['suspended']) · <span class="text-yellow-600">{{ $k['suspended'] }} askıda</span>@endif
        </div>
    </div>

    {{-- Limite yakın --}}
    <div class="rounded-xl border p-4 shadow-sm {{ $k['near_limit'] ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-white' }}">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium {{ $k['near_limit'] ? 'text-amber-700' : 'text-gray-500' }}">Limite Yakın</span>
            <i class="fas fa-gauge-high {{ $k['near_limit'] ? 'text-amber-400' : 'text-gray-300' }}"></i>
        </div>
        <div class="mt-2 text-2xl font-bold {{ $k['near_limit'] ? 'text-amber-700' : 'text-gray-900' }}">{{ $k['near_limit'] }}</div>
        <div class="mt-1 text-xs {{ $k['over_limit'] ? 'text-red-600 font-medium' : 'text-gray-400' }}">
            @if($k['over_limit']){{ $k['over_limit'] }} site limit aşıldı @else eşik %80 @endif
        </div>
    </div>

    {{-- Bugünkü istek --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500">Bugünkü İstek</span>
            <i class="fas fa-bolt text-gray-300"></i>
        </div>
        <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($k['requests_today']) }}</div>
        <div class="mt-1 text-xs text-gray-400">tüm siteler toplamı</div>
    </div>

    {{-- AI maliyeti (ay) --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500">AI Maliyeti (ay)</span>
            <i class="fas fa-robot text-gray-300"></i>
        </div>
        <div class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($k['ai_cost_month'], 2) }}</div>
        <div class="mt-1 text-xs text-gray-400">faturalanan (BYOK hariç)</div>
    </div>
</div>
