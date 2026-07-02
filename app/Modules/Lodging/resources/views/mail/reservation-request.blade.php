@php
    /** @var \App\Modules\Lodging\Models\Reservation $reservation */
    $fmt = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d.m.Y') : '—';
@endphp
<x-mail::message>
# Yeni Rezervasyon Talebi

**Talep No:** {{ $reservation->code }}

@if($roomTypeName)
**Oda Tipi:** {{ $roomTypeName }}
@endif

**Misafir:** {{ $reservation->guest_name }}
**Telefon:** {{ $reservation->guest_phone }}
@if($reservation->guest_email)
**E-posta:** {{ $reservation->guest_email }}
@endif

**Giriş:** {{ $fmt($reservation->checkin) }}
**Çıkış:** {{ $fmt($reservation->checkout) }}
**Gece:** {{ $reservation->nights }}
**Kişi:** {{ $reservation->adults }} yetişkin{{ $reservation->children ? ', ' . $reservation->children . ' çocuk' : '' }}

@if($reservation->est_total !== null)
**Tahmini Tutar:** {{ number_format((float) $reservation->est_total, 2, ',', '.') }} {{ $reservation->currency }}
@endif

@if($reservation->message)
**Mesaj:**
{{ $reservation->message }}
@endif

Talep durumu: **{{ $reservation->status->label() }}**. Yönetim panelinden onaylayabilir veya iptal edebilirsiniz.
</x-mail::message>
