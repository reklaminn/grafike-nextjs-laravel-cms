{{--
    KPI tile partial — kullanım dashboard'larında üst satırda gösterilir.

    Inputs:
      $label  String   "İstek", "Token", "Maliyet"
      $value  String   formatted value (caller formatları zaten yaptı)
      $icon   String   FontAwesome icon class (örn "fa-paper-plane")
      $delta  ?float   önceki dönemle karşılaştırma yüzdesi (null = gösterme)
      $color  String   indigo|amber|emerald|red|gray — Tailwind palette
--}}
@php
    $color = $color ?? 'gray';
    $bg = "bg-{$color}-50";
    $iconBg = "bg-{$color}-100";
    $iconText = "text-{$color}-600";
@endphp
<div class="rounded-xl shadow-sm border border-gray-200 p-4 {{ $bg }}">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg {{ $iconBg }} flex items-center justify-center">
            <i class="fas {{ $icon }} {{ $iconText }}"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-500 uppercase font-medium">{{ $label }}</p>
            <p class="text-xl font-bold text-gray-900 font-mono leading-tight">{{ $value }}</p>
        </div>
    </div>
    @if ($delta !== null)
        <p class="mt-2 text-[11px]">
            @if ($delta > 0)
                <span class="text-red-600"><i class="fas fa-arrow-up"></i> %{{ $delta }}</span>
            @elseif ($delta < 0)
                <span class="text-emerald-600"><i class="fas fa-arrow-down"></i> %{{ abs($delta) }}</span>
            @else
                <span class="text-gray-500">değişmedi</span>
            @endif
            <span class="text-gray-400 ml-1">geçen aya göre</span>
        </p>
    @endif
</div>
