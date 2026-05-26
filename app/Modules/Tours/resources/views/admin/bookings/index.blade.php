@extends('admin.layouts.app')
@section('title', 'Rezervasyonlar')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Rezervasyonlar</h1>
    <p class="text-sm text-gray-500">Tüm tur rezervasyonları, durum + tarih bazlı filtreleme.</p>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

<form method="GET" class="mb-4 bg-white border rounded-xl p-3 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Ara</label>
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Booking ref veya email"
               class="px-3 py-1.5 border border-gray-300 rounded text-sm w-56">
    </div>
    <div>
        <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Durum</label>
        <select name="status" class="px-3 py-1.5 border border-gray-300 rounded text-sm">
            <option value="">— Hepsi —</option>
            @foreach($statuses as $s)
                <option value="{{ $s->value }}" {{ ($filters['status'] ?? '') === $s->value ? 'selected' : '' }}>
                    {{ $s->label() }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Hareket ≥</label>
        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
               class="px-3 py-1.5 border border-gray-300 rounded text-sm">
    </div>
    <div>
        <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Hareket ≤</label>
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"
               class="px-3 py-1.5 border border-gray-300 rounded text-sm">
    </div>
    <button type="submit" class="px-4 py-1.5 bg-gray-700 text-white rounded text-sm hover:bg-gray-800">Filtrele</button>
    @if(array_filter($filters))
        <a href="{{ route('admin.tour-bookings.index') }}" class="text-xs text-gray-500 hover:underline">Temizle</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($bookings->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">Filtreyle eşleşen rezervasyon yok.</div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Booking Ref</th>
                    <th class="px-4 py-3">Tur</th>
                    <th class="px-4 py-3">Hareket</th>
                    <th class="px-4 py-3">Müşteri</th>
                    <th class="px-4 py-3">Tutar</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3">Oluşturuldu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($bookings as $booking)
                    @php
                        $tour = $booking->tourDate?->tour;
                        $title = $tour?->translations->first()?->title ?? $tour?->slug;
                        $statusColor = match($booking->status->value) {
                            'confirmed' => 'bg-green-100 text-green-700',
                            'reserved'  => 'bg-blue-100 text-blue-700',
                            'cancelled' => 'bg-gray-100 text-gray-600',
                            'refunded'  => 'bg-purple-100 text-purple-700',
                            'expired'   => 'bg-orange-100 text-orange-700',
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            default     => 'bg-yellow-100 text-yellow-700',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.tour-bookings.show', $booking) }}"
                               class="font-mono text-sm text-indigo-600 hover:underline">
                                {{ $booking->booking_ref }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-gray-800 truncate max-w-xs">{{ $title ?? '—' }}</div>
                            @if($tour) <div class="text-[10px] text-gray-400 font-mono">{{ $tour->type?->value }}</div> @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $booking->tourDate?->starts_at?->isoFormat('D MMM YYYY HH:mm') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-gray-800">{{ $booking->customer_snapshot['full_name'] ?? '—' }}</div>
                            <div class="text-xs text-gray-400">{{ $booking->customer_snapshot['email'] ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ number_format($booking->total_amount / 100, 2, ',', '.') }} {{ $booking->currency }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColor }}">{{ $booking->status->label() }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $booking->created_at?->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="mt-4">{{ $bookings->links() }}</div>
@endsection
