@extends('admin.layouts.app')
@section('title', 'Rezervasyon ' . $reservation->code)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $reservation->code }}</h1>
        <span class="inline-flex mt-1 px-2 py-0.5 rounded-full {{ $reservation->status->badgeClasses() }} text-xs font-medium">{{ $reservation->status->label() }}</span>
    </div>
    <a href="{{ route('admin.lodging.reservations.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Listeye dön</a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Talep Detayı</h2>
            <dl class="grid grid-cols-2 gap-y-3 gap-x-6 text-sm">
                <div><dt class="text-gray-400">Misafir</dt><dd class="text-gray-800">{{ $reservation->guest_name }}</dd></div>
                <div><dt class="text-gray-400">Telefon</dt><dd class="text-gray-800">{{ $reservation->guest_phone }}</dd></div>
                <div><dt class="text-gray-400">E-posta</dt><dd class="text-gray-800">{{ $reservation->guest_email ?? '—' }}</dd></div>
                <div><dt class="text-gray-400">Oda Tipi</dt><dd class="text-gray-800">{{ $reservation->roomType?->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-400">Giriş</dt><dd class="text-gray-800">{{ $reservation->checkin->format('d.m.Y') }}</dd></div>
                <div><dt class="text-gray-400">Çıkış</dt><dd class="text-gray-800">{{ $reservation->checkout->format('d.m.Y') }}</dd></div>
                <div><dt class="text-gray-400">Gece</dt><dd class="text-gray-800">{{ $reservation->nights }}</dd></div>
                <div><dt class="text-gray-400">Kişi</dt><dd class="text-gray-800">{{ $reservation->adults }} yetişkin{{ $reservation->children ? ', ' . $reservation->children . ' çocuk' : '' }}</dd></div>
                <div><dt class="text-gray-400">Tahmini Tutar</dt><dd class="text-gray-800">{{ $reservation->est_total !== null ? number_format((float) $reservation->est_total, 2, ',', '.') . ' ' . $reservation->currency : '—' }}</dd></div>
                <div><dt class="text-gray-400">Kaynak</dt><dd class="text-gray-800">{{ $reservation->source->label() }}</dd></div>
            </dl>
            @if($reservation->message)
                <div class="mt-4 pt-4 border-t">
                    <dt class="text-gray-400 text-sm mb-1">Misafir Mesajı</dt>
                    <dd class="text-gray-800 text-sm whitespace-pre-line">{{ $reservation->message }}</dd>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Operatör Notu</h2>
            <form method="POST" action="{{ route('admin.lodging.reservations.note', $reservation) }}" class="space-y-3">
                @csrf
                <textarea name="admin_note" rows="3"
                          class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('admin_note', $reservation->admin_note) }}</textarea>
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 font-medium">Notu Kaydet</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border p-6 space-y-3">
            <h2 class="text-sm font-semibold text-gray-700">Eylemler</h2>

            @if($reservation->status->value !== 'confirmed')
                <form method="POST" action="{{ route('admin.lodging.reservations.confirm', $reservation) }}"
                      onsubmit="return confirm('Rezervasyonu onayla? Tarihler dolu olarak işaretlenecek.');">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 font-medium">
                        <i class="fas fa-check mr-1"></i> Onayla
                    </button>
                </form>
            @endif

            @if($reservation->status->value !== 'cancelled')
                <form method="POST" action="{{ route('admin.lodging.reservations.cancel', $reservation) }}"
                      onsubmit="return confirm('Rezervasyonu iptal et?');">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm hover:bg-red-100 font-medium">
                        <i class="fas fa-times mr-1"></i> İptal Et
                    </button>
                </form>
            @endif

            @if($whatsappLink)
                <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
                   class="block text-center w-full px-4 py-2 bg-[#25D366] text-white rounded-lg text-sm hover:opacity-90 font-medium">
                    <i class="fab fa-whatsapp mr-1"></i> WhatsApp ile Yanıtla
                </a>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6 text-xs text-gray-400">
            Oluşturuldu: {{ $reservation->created_at?->format('d.m.Y H:i') }}
        </div>
    </div>
</div>
@endsection
