{{--
    AI Kullanım Özeti paneli (FAZ 3.4).

    Inputs (via include):
      $tenant   App\Models\Tenant
      $plan     ['name','label','monthly_requests','monthly_tokens','monthly_cost_usd']
      $usage    ['requests','tokens','cost_usd','period']
--}}
@php
    $byokExempt = $tenant->isUsingByokAi()
        && $tenant->preferredAiProvider()
        && $tenant->hasAiApiKey($tenant->preferredAiProvider());

    // Helper: percent (0-100) used of a limit; null limit = unlimited
    $pct = function ($used, $limit) {
        if ($limit === null || $limit <= 0) {
            return null; // unlimited
        }
        return min(100, max(0, round(($used / $limit) * 100)));
    };

    $reqPct  = $pct($usage['requests'], $plan['monthly_requests']);
    $tokPct  = $pct($usage['tokens'],   $plan['monthly_tokens']);
    $costPct = $plan['monthly_cost_usd'] !== null
        ? min(100, max(0, round(($usage['cost_usd'] / $plan['monthly_cost_usd']) * 100)))
        : null;

    $color = function ($pct) {
        if ($pct === null) return 'bg-gray-300';
        if ($pct >= 95)    return 'bg-red-500';
        if ($pct >= 75)    return 'bg-yellow-500';
        return 'bg-emerald-500';
    };

    $fmt = fn ($n) => number_format($n, 0, ',', '.');
@endphp

<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-chart-line text-emerald-500"></i> AI Kullanımı
            <span class="text-xs text-gray-400 font-normal ml-2">({{ $usage['period'] }})</span>
        </h2>
        <span class="text-xs px-2 py-1 rounded-full
            {{ $plan['name'] === 'enterprise' ? 'bg-purple-100 text-purple-700' :
               ($plan['name'] === 'pro'        ? 'bg-indigo-100 text-indigo-700' :
               ($plan['name'] === 'starter'    ? 'bg-blue-100 text-blue-700' :
                                                  'bg-gray-100 text-gray-600')) }}">
            <i class="fas fa-tag mr-1"></i> {{ $plan['label'] }} planı
        </span>
    </div>

    @if($byokExempt)
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg p-3 text-xs mb-4">
            <i class="fas fa-info-circle mr-1"></i>
            Bu site BYOK kullanıyor (kendi API anahtarı). Sistemin kotasından düşmüyor.
        </div>
    @endif

    <div class="space-y-4">
        {{-- Requests --}}
        <div>
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="text-gray-600">
                    <i class="fas fa-paper-plane text-gray-400 mr-1"></i> İstek sayısı
                </span>
                <span class="font-mono text-xs text-gray-700">
                    {{ $fmt($usage['requests']) }}
                    @if($plan['monthly_requests'] !== null)
                        / {{ $fmt($plan['monthly_requests']) }}
                    @else
                        / <span class="text-gray-400">sınırsız</span>
                    @endif
                </span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full {{ $color($reqPct) }} transition-all"
                     style="width: {{ $reqPct ?? 0 }}%"></div>
            </div>
        </div>

        {{-- Tokens --}}
        <div>
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="text-gray-600">
                    <i class="fas fa-coins text-gray-400 mr-1"></i> Token
                </span>
                <span class="font-mono text-xs text-gray-700">
                    {{ $fmt($usage['tokens']) }}
                    @if($plan['monthly_tokens'] !== null)
                        / {{ $fmt($plan['monthly_tokens']) }}
                    @else
                        / <span class="text-gray-400">sınırsız</span>
                    @endif
                </span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full {{ $color($tokPct) }} transition-all"
                     style="width: {{ $tokPct ?? 0 }}%"></div>
            </div>
        </div>

        {{-- Cost --}}
        <div>
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="text-gray-600">
                    <i class="fas fa-dollar-sign text-gray-400 mr-1"></i> Maliyet (USD)
                </span>
                <span class="font-mono text-xs text-gray-700">
                    ${{ number_format($usage['cost_usd'], 4) }}
                    @if($plan['monthly_cost_usd'] !== null)
                        / ${{ number_format($plan['monthly_cost_usd'], 2) }}
                    @else
                        / <span class="text-gray-400">sınırsız</span>
                    @endif
                </span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full {{ $color($costPct) }} transition-all"
                     style="width: {{ $costPct ?? 0 }}%"></div>
            </div>
        </div>
    </div>

    @if($reqPct !== null && $reqPct >= 90 || ($tokPct !== null && $tokPct >= 90) || ($costPct !== null && $costPct >= 90))
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-xs text-yellow-800">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Kota dolma noktasına yaklaşıyorsunuz. Plan yükseltme için aşağıdaki "AI Ayarları"ndan plan seçebilirsiniz veya BYOK ile kendi anahtarınızı kullanabilirsiniz.
        </div>
    @endif

    <p class="text-[11px] text-gray-400 mt-3">
        BYOK kullanan tenant'lar bu sayaçlardan muaftır. Kota her ayın 1'inde sıfırlanır.
    </p>
</div>
