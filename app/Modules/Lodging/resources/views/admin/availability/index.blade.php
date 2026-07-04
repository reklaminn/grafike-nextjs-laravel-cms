@extends('admin.layouts.app')
@section('title', 'Müsaitlik')

@section('content')
<div class="mb-4">
    <h1 class="text-2xl font-bold text-gray-800">Müsaitlik</h1>
    <p class="text-sm text-gray-500">Oda tipini seçin, dolu/bakım tarih aralıklarını bloklayın. Onaylı rezervasyonlar otomatik dolu görünür.</p>
</div>

<div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800 leading-relaxed">
    <strong>Nasıl çalışır?</strong> Oda tipleri <strong>adet bazlı</strong> satılır — müsaitlik
    <em>eklemenize gerek yok</em>. Blok koymadığınız sürece tüm günler oda adediniz kadar
    <strong>müsait</strong>tir ve site takviminde açık görünür. Bu sayfa yalnızca
    <strong>kapatmak</strong> içindir: bakım, uzun konaklama veya elle dolu işaretlemek istediğiniz
    tarihleri bloklarsınız; onaylı rezervasyonlar adetten otomatik düşülür.
    Yani <strong>boş sayfa = her şey müsait</strong> demektir, eksik değil.
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif
@if($errors->any())<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

@if($roomTypes->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border p-10 text-center text-sm text-gray-500">
        Önce bir <a href="{{ route('admin.lodging.room-types.create') }}" class="text-indigo-600 hover:underline">oda tipi</a> oluşturun.
    </div>
@else
<div class="flex items-center gap-3 mb-6">
    <span class="text-sm text-gray-600">Oda tipi:</span>
    <form method="GET" action="{{ route('admin.lodging.availability.index') }}">
        <select name="room_type" onchange="this.form.submit()"
                class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach($roomTypes as $rt)
                <option value="{{ $rt->id }}" @selected($selected && $selected->id === $rt->id)>{{ $rt->name }}</option>
            @endforeach
        </select>
    </form>
</div>

@if($selected)
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Yeni Blok Ekle</h2>
            <form method="POST" action="{{ route('admin.lodging.availability.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="room_type_id" value="{{ $selected->id }}">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Başlangıç</label>
                    <input type="date" name="start_date" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Bitiş (çıkış günü — hariç)</label>
                    <input type="date" name="end_date" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Adet ({{ $selected->unit_count }} üzerinden)</label>
                    <input type="number" name="qty" value="{{ $selected->unit_count }}" min="1" max="{{ $selected->unit_count }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Not</label>
                    <input type="text" name="note" maxlength="500" placeholder="Bakım, uzun süreli misafir…"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
                    Blokla
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50 text-sm font-semibold text-gray-700">
                Yaklaşan Bloklar — {{ $selected->name }}
            </div>
            @if($rows->isEmpty())
                <div class="p-8 text-center text-sm text-gray-500">
                    Blok yok — <strong>{{ $selected->unit_count }} adet</strong> üzerinden tüm günler müsait.
                    Kapatmak istediğiniz tarihleri soldan bloklayın.
                </div>
            @else
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-4 py-3">Aralık</th>
                        <th class="px-4 py-3">Adet</th>
                        <th class="px-4 py-3">Tür</th>
                        <th class="px-4 py-3">Not</th>
                        <th class="px-4 py-3 text-right">Eylem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($rows as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">
                                {{ $row->start_date->format('d.m.Y') }} → {{ $row->end_date->format('d.m.Y') }}
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $row->qty }}</td>
                            <td class="px-4 py-3">
                                @if($row->source === 'reservation')
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">Rezervasyon</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">Manuel blok</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ $row->reservation?->code ?? $row->note ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($row->source === 'manual')
                                    <form action="{{ route('admin.lodging.availability.destroy', $row) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Bloğu kaldır?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Kaldır</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endif
@endif
@endsection
