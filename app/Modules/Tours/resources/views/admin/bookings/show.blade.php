@extends('admin.layouts.app')
@section('title', 'Rezervasyon ' . $booking->booking_ref)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.tour-bookings.index') }}" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-mono">{{ $booking->booking_ref }}</h1>
            <p class="text-sm text-gray-500">
                {{ $booking->tourDate?->tour?->translations->first()?->title ?? $booking->tourDate?->tour?->slug }}
            </p>
        </div>
    </div>
    @php
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
    <span class="text-sm px-3 py-1 rounded-full font-medium {{ $statusColor }}">
        {{ $booking->status->label() }}
    </span>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Detail --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Customer --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fas fa-user text-indigo-500"></i> Müşteri
            </h2>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-gray-400">Ad Soyad</dt><dd class="text-gray-800">{{ $booking->customer_snapshot['full_name'] ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Email</dt><dd class="text-gray-800">{{ $booking->customer_snapshot['email'] ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Telefon</dt><dd class="text-gray-800">{{ $booking->customer_snapshot['phone'] ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Ülke</dt><dd class="text-gray-800">{{ $booking->customer_snapshot['country'] ?? '—' }}</dd></div>
                <div class="col-span-2"><dt class="text-xs text-gray-400">Adres</dt><dd class="text-gray-700 text-sm">{{ $booking->customer_snapshot['address'] ?? '—' }}</dd></div>
                @if(!empty($booking->customer_snapshot['notes']))
                <div class="col-span-2"><dt class="text-xs text-gray-400">Müşteri notu</dt><dd class="text-gray-700 italic">{{ $booking->customer_snapshot['notes'] }}</dd></div>
                @endif
            </dl>
            @if($booking->isGuest())
                <p class="mt-3 text-xs text-amber-600"><i class="fas fa-info-circle"></i> Guest booking — Member kaydı yok.</p>
            @endif
        </div>

        {{-- Passengers --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fas fa-users text-indigo-500"></i> Yolcular ({{ $booking->passengers->count() }})
            </h2>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-3 py-2">Ad Soyad</th><th class="px-3 py-2">Tip</th>
                        <th class="px-3 py-2">Kimlik</th><th class="px-3 py-2">Kabin</th>
                        <th class="px-3 py-2 text-right">Fiyat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($booking->passengers as $p)
                        <tr>
                            <td class="px-3 py-2">
                                <span class="text-gray-800">{{ $p->fullName() }}</span>
                                @if($p->is_lead)<span class="ml-1 text-[10px] text-pink-600">★ LEAD</span>@endif
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ $p->passenger_type?->label() }}</td>
                            <td class="px-3 py-2 text-xs font-mono text-gray-500">
                                {{ strtoupper($p->id_type) }}: {{ $p->id_number ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-600">
                                {{-- Phase 1.5.b: cabin master + category translation --}}
                                @php
                                    $cabin = $p->cabin;
                                    $cabinName = $cabin?->translations->first()?->name
                                        ?? $cabin?->category?->translations?->first()?->name;
                                @endphp
                                {{ $cabinName ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-right text-gray-700">
                                {{ number_format($p->price / 100, 2, ',', '.') }} {{ $booking->currency }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Extras --}}
        @if($booking->extras->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fas fa-plus-circle text-indigo-500"></i> Ek Hizmetler
            </h2>
            <table class="min-w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    @foreach($booking->extras as $extra)
                        <tr>
                            <td class="py-2 text-gray-700">{{ $extra->name_snapshot }}</td>
                            <td class="py-2 text-xs text-gray-500">x{{ $extra->quantity }}</td>
                            <td class="py-2 text-right text-gray-700">
                                {{ number_format($extra->subtotal / 100, 2, ',', '.') }} {{ $booking->currency }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Refunds --}}
        @if($booking->refunds->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fas fa-undo text-purple-500"></i> İadeler
            </h2>
            @foreach($booking->refunds as $r)
                <div class="p-3 border border-gray-200 rounded-lg mb-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">{{ number_format($r->amount_requested / 100, 2, ',', '.') }} {{ $booking->currency }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">{{ $r->status?->label() }}</span>
                    </div>
                    @if($r->reason_text)
                        <p class="text-xs text-gray-500 mt-1">{{ $r->reason_text }}</p>
                    @endif
                </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- RIGHT: Sidebar --}}
    <div class="space-y-4">

        {{-- Totals --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fas fa-receipt text-indigo-500"></i> Tutarlar
            </h2>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd>{{ number_format($booking->subtotal / 100, 2, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Ek hizmetler</dt><dd>{{ number_format($booking->extras_total / 100, 2, ',', '.') }}</dd></div>
                @if($booking->discount_total)
                    <div class="flex justify-between text-emerald-600"><dt>İndirim</dt><dd>{{ number_format($booking->discount_total / 100, 2, ',', '.') }}</dd></div>
                @endif
                @if($booking->tax_total)
                    <div class="flex justify-between text-gray-500"><dt>Vergi</dt><dd>{{ number_format($booking->tax_total / 100, 2, ',', '.') }}</dd></div>
                @endif
                <div class="flex justify-between font-semibold pt-2 border-t">
                    <dt>Toplam</dt><dd>{{ number_format($booking->total_amount / 100, 2, ',', '.') }} {{ $booking->currency }}</dd>
                </div>
                <div class="flex justify-between text-emerald-600">
                    <dt>Ödenen</dt><dd>{{ number_format($booking->amount_paid / 100, 2, ',', '.') }}</dd>
                </div>
                @if($booking->amount_refunded)
                    <div class="flex justify-between text-purple-600">
                        <dt>İade</dt><dd>{{ number_format($booking->amount_refunded / 100, 2, ',', '.') }}</dd>
                    </div>
                @endif
                @if(! $booking->isFullyPaid())
                    <div class="flex justify-between text-amber-600 font-medium pt-2 border-t">
                        <dt>Kalan</dt><dd>{{ number_format(($booking->total_amount - $booking->amount_paid) / 100, 2, ',', '.') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Departure --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fas fa-calendar text-indigo-500"></i> Departure
            </h2>
            @if($booking->tourDate)
                <div class="text-sm text-gray-800">{{ $booking->tourDate->starts_at->isoFormat('D MMMM YYYY HH:mm') }}</div>
                @if($booking->tourDate->ends_at)
                    <div class="text-xs text-gray-500">→ {{ $booking->tourDate->ends_at->isoFormat('D MMM YYYY HH:mm') }}</div>
                @endif
                <a href="{{ route('admin.tour-dates.manifest', $booking->tourDate) }}"
                   class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs hover:bg-emerald-700">
                    <i class="fas fa-file-excel"></i> Manifest İndir (XLSX)
                </a>
            @endif
        </div>

        {{-- Timestamps --}}
        <div class="bg-gray-50 rounded-xl border p-4 text-xs text-gray-500 space-y-1">
            <p><strong>Oluşturuldu:</strong> {{ $booking->created_at?->isoFormat('D MMM YYYY HH:mm') }}</p>
            @if($booking->reserved_at)<p><strong>Rezerve:</strong> {{ $booking->reserved_at->isoFormat('D MMM HH:mm') }}</p>@endif
            @if($booking->confirmed_at)<p><strong>Onay:</strong> {{ $booking->confirmed_at->isoFormat('D MMM HH:mm') }}</p>@endif
            @if($booking->cancelled_at)<p><strong>İptal:</strong> {{ $booking->cancelled_at->isoFormat('D MMM HH:mm') }}</p>@endif
            @if($booking->hold_expires_at && $booking->status->value === 'reserved')
                <p class="text-amber-600"><strong>Hold süresi:</strong> {{ $booking->hold_expires_at->diffForHumans() }}</p>
            @endif
        </div>

        {{-- Actions --}}
        @if(in_array($booking->status->value, ['reserved', 'confirmed']))
        <div class="bg-white rounded-xl shadow-sm border border-red-100 p-5">
            <h2 class="font-semibold text-red-600 mb-2 text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i> Tehlikeli Eylemler
            </h2>
            <form method="POST" action="{{ route('admin.tour-bookings.cancel', $booking) }}" class="space-y-3">
                @csrf
                <input type="text" name="reason" placeholder="İptal nedeni (opsiyonel, müşteriye gözükmez)"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
                <button type="submit"
                        onclick="return confirm('Bu rezervasyon iptal edilsin mi? Kapasite serbest bırakılacak.')"
                        class="w-full px-3 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg text-xs hover:bg-red-100">
                    <i class="fas fa-ban mr-1"></i> Rezervasyonu İptal Et
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
