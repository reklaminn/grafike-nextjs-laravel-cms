{{--
    İstek kotası paneli
    Değişkenler:
      $tenant     — Tenant model
      $canManage  — bool (agency admin mi?)
--}}
@php
    $baseLimit  = $tenant->packageConfig()['max_requests_per_day'] ?? null;
    $bonus      = $tenant->activeQuotaBonus();
    $effective  = $tenant->effectiveDailyRequestLimit();
    $usedToday  = app(\App\Services\Tenancy\TenantUsageMeter::class)->requestsToday($tenant->id);
    $extensions = $tenant->quotaExtensions()->orderByDesc('ends_at')->limit(10)->get();
@endphp

<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-gauge-high text-orange-500"></i> İstek Kotası
        </h2>
        @if($tenant->isSuspended())
            <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                <i class="fas fa-ban mr-1"></i>Askıda
            </span>
        @endif
    </div>

    {{-- Bugünkü kullanım --}}
    <div class="mb-4">
        <div class="flex items-baseline justify-between text-sm">
            <span class="text-gray-500">Bugünkü istek</span>
            <span class="font-semibold text-gray-800 tabular-nums">
                {{ number_format($usedToday) }}
                @if($effective !== null) / {{ number_format($effective) }} @else / sınırsız @endif
            </span>
        </div>
        @if($effective !== null && $effective > 0)
            @php $pct = min(100, (int) round($usedToday / $effective * 100)); @endphp
            <div class="mt-1.5 h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                <div class="h-full rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                     style="width: {{ $pct }}%"></div>
            </div>
            <p class="mt-1 text-[11px] text-gray-400">
                Paket: {{ number_format((int) $baseLimit) }}/gün
                @if($bonus > 0) + geçici {{ number_format($bonus) }}/gün @endif
            </p>
        @endif
    </div>

    {{-- Aktif/geçmiş extension listesi --}}
    @if($extensions->isNotEmpty())
        <div class="mb-4 space-y-1.5">
            @foreach($extensions as $ext)
                <div class="flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-xs
                            {{ $ext->isActive() ? 'bg-emerald-50 border border-emerald-100' : 'bg-gray-50 border border-gray-100 opacity-60' }}">
                    <div class="min-w-0">
                        <span class="font-semibold {{ $ext->isActive() ? 'text-emerald-700' : 'text-gray-500' }}">
                            +{{ number_format($ext->extra_requests_per_day) }}/gün
                        </span>
                        <span class="text-gray-400 ml-1">{{ $ext->ends_at->format('d.m.Y H:i') }}'e kadar</span>
                        @if($ext->reason)
                            <span class="block truncate text-gray-400">{{ $ext->reason }}</span>
                        @endif
                    </div>
                    @if($canManage)
                        <form method="POST"
                              action="{{ route('admin.tenants.quota-extensions.destroy', [$tenant, $ext]) }}"
                              onsubmit="return confirm('Bu kota yükseltmesi kaldırılsın mı?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-gray-300 hover:text-red-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Geçici yükseltme ekle --}}
    @if($canManage && $baseLimit !== null)
        <form method="POST" action="{{ route('admin.tenants.quota-extensions.store', $tenant) }}"
              class="rounded-lg border border-dashed border-orange-200 bg-orange-50/50 p-3 space-y-2">
            @csrf
            <p class="text-xs font-semibold text-orange-800">
                <i class="fas fa-bolt mr-1"></i>Geçici Kota Yükseltmesi
            </p>
            <div class="flex gap-2">
                <input type="number" name="extra_requests_per_day" required min="100" max="1000000"
                       placeholder="Ek istek/gün" value="{{ old('extra_requests_per_day', 5000) }}"
                       class="w-full min-w-0 rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-orange-400">
                <select name="days" class="shrink-0 rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:ring-2 focus:ring-orange-400">
                    <option value="1">1 gün</option>
                    <option value="3">3 gün</option>
                    <option value="7" selected>1 hafta</option>
                    <option value="14">2 hafta</option>
                    <option value="30">1 ay</option>
                </select>
            </div>
            <input type="text" name="reason" maxlength="255" placeholder="Sebep (opsiyonel — örn. kampanya trafiği)"
                   class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-orange-400">
            <button type="submit"
                    class="w-full rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-orange-700">
                Yükseltmeyi Uygula
            </button>
            <p class="text-[11px] text-orange-700/70">Süre bitince limit otomatik olarak paket değerine döner.</p>
        </form>
    @elseif($baseLimit === null)
        <p class="text-xs text-gray-400">Bu paketin günlük istek limiti yok (sınırsız) — yükseltme gerekmez.</p>
    @endif
</div>
