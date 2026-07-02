@extends('admin.layouts.app')
@section('title', 'Rezervasyonlar')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            Rezervasyonlar
            @if($pendingCount > 0)
                <span class="ml-2 align-middle inline-flex px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">{{ $pendingCount }} yeni</span>
            @endif
        </h1>
        <p class="text-sm text-gray-500">Gelen konaklama talepleri — onayla, iptal et, WhatsApp'tan yanıtla.</p>
    </div>
    <a href="{{ route('admin.lodging.reservations.export', request()->query()) }}"
       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 font-medium">
        <i class="fas fa-file-csv mr-1"></i> CSV
    </a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif

<form method="GET" class="flex flex-wrap items-end gap-3 mb-5 bg-white rounded-xl shadow-sm border p-4">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Durum</label>
        <select name="status" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Tümü</option>
            @foreach($statuses as $s)
                <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Oda Tipi</label>
        <select name="room_type" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Tümü</option>
            @foreach($roomTypes as $rt)
                <option value="{{ $rt->id }}" @selected((string)($filters['room_type'] ?? '') === (string) $rt->id)>{{ $rt->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Girişten itibaren</label>
        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
               class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">Filtrele</button>
    <a href="{{ route('admin.lodging.reservations.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Temizle</a>
</form>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($reservations->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">Kayıt bulunamadı.</div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Kod</th>
                    <th class="px-4 py-3">Misafir</th>
                    <th class="px-4 py-3">Oda</th>
                    <th class="px-4 py-3">Tarih</th>
                    <th class="px-4 py-3">Gece</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 text-right">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($reservations as $r)
                    <tr class="hover:bg-gray-50 {{ $r->status->value === 'pending' ? 'bg-amber-50/40' : '' }}">
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $r->code }}</td>
                        <td class="px-4 py-3 text-gray-800">
                            {{ $r->guest_name }}
                            <div class="text-xs text-gray-400">{{ $r->guest_phone }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $r->roomType?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $r->checkin->format('d.m.Y') }} → {{ $r->checkout->format('d.m.Y') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $r->nights }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full {{ $r->status->badgeClasses() }} text-xs font-medium">{{ $r->status->label() }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.lodging.reservations.show', $r) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Detay</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $reservations->links() }}</div>
    @endif
</div>
@endsection
