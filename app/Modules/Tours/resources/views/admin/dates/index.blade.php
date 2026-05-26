@extends('admin.layouts.app')
@section('title', 'Departure Tarihleri — ' . ($tour->translations->first()?->title ?? $tour->slug))

@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.tours.edit', $tour) }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Departure Tarihleri</h1>
            <p class="text-sm text-gray-500">{{ $tour->translations->first()?->title ?? $tour->slug }}</p>
        </div>
    </div>
    <a href="{{ route('admin.tours.dates.create', $tour) }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
        <i class="fas fa-plus mr-1"></i> Yeni Tarih
    </a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($dates->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">
            Henüz departure tarihi yok.
            <a href="{{ route('admin.tours.dates.create', $tour) }}" class="text-indigo-600 hover:underline">İlkini ekleyin</a>.
        </div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Tarih</th>
                    <th class="px-4 py-3">Kapasite</th>
                    <th class="px-4 py-3">Rezervasyon</th>
                    <th class="px-4 py-3">Fiyat (override)</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 text-right">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($dates as $date)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $date->starts_at->isoFormat('D MMMM YYYY') }}</div>
                            <div class="text-xs text-gray-400">
                                {{ $date->starts_at->isoFormat('HH:mm') }}
                                @if($date->ends_at) — {{ $date->ends_at->isoFormat('D MMM HH:mm') }}@endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs">{{ $date->capacity_left }}/{{ $date->capacity_total }}</span>
                            @if($date->capacity_total > 0)
                                @php $pct = max(0, min(100, round(($date->capacity_left / $date->capacity_total) * 100))); @endphp
                                <div class="w-24 h-1 bg-gray-200 rounded mt-1">
                                    <div class="h-1 rounded {{ $pct < 20 ? 'bg-red-500' : ($pct < 50 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $date->bookings_count }}</td>
                        <td class="px-4 py-3 text-gray-700">
                            @if($date->price_override !== null)
                                {{ number_format($date->price_override / 100, 2, ',', '.') }} {{ $tour->currency }}
                            @else
                                <span class="text-xs text-gray-400">— baz fiyat —</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php $color = match($date->status) {
                                'open'      => 'bg-green-100 text-green-700',
                                'sold_out'  => 'bg-red-100 text-red-700',
                                'cancelled' => 'bg-gray-100 text-gray-600',
                                default     => 'bg-yellow-100 text-yellow-700',
                            }; @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $color }}">
                                {{ ['open'=>'Açık','closed'=>'Kapalı','sold_out'=>'Doldu','cancelled'=>'İptal'][$date->status] ?? $date->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.tours.dates.edit', [$tour, $date]) }}"
                               class="text-xs text-indigo-600 hover:underline mr-3">
                                <i class="fas fa-edit"></i> Düzenle
                            </a>
                            <form method="POST" action="{{ route('admin.tours.dates.destroy', [$tour, $date]) }}" class="inline">
                                @csrf @method('DELETE')
                                <button onclick="return confirm('Bu tarih silinsin mi?')"
                                        class="text-xs text-red-600 hover:underline">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="mt-4">{{ $dates->links() }}</div>
@endsection
