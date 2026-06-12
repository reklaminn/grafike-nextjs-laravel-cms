@extends('admin.layouts.app')
@section('title', 'Fiyat Grupları — ' . ($tour->translations->first()?->title ?? $tour->slug))

@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.tours.edit', $tour) }}" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Fiyat Grupları</h1>
            <p class="text-sm text-gray-500">
                <strong>{{ $tour->translations->first()?->title ?? $tour->slug }}</strong> turunun fiyatlandırma matrisi.
            </p>
        </div>
    </div>
    <a href="{{ route('admin.tours.price-groups.create', $tour) }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
        <i class="fas fa-plus mr-1"></i> Yeni Fiyat Grubu
    </a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($groups->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">
            Bu tur için henüz fiyat grubu yok.
            <a href="{{ route('admin.tours.price-groups.create', $tour) }}" class="text-indigo-600 hover:underline">İlkini oluştur</a>.
            <p class="text-xs mt-2 text-gray-400">
                Fiyat grubu = "Yaz 2026 Standart" / "Erken Rezervasyon" gibi adlandırılmış pricing scenario.
                Her grup N TourDate'e atanır + cabin × person-tier matrix taşır.
            </p>
        </div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Grup Adı</th>
                    <th class="px-4 py-3">Min Kişi</th>
                    <th class="px-4 py-3">Kontenjan</th>
                    <th class="px-4 py-3">Tarih Sayısı</th>
                    <th class="px-4 py-3">Fiyat Satırı</th>
                    <th class="px-4 py-3">Kampanya</th>
                    <th class="px-4 py-3">Sıra</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 text-right">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($groups as $group)
                    @php
                        $tr = $defaultLanguage ? $group->translationFor($defaultLanguage->id) : $group->translations->first();
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-800 font-medium">{{ $tr->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $group->min_persons ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $group->capacity_quota ?? '∞' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $group->dates_count }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $group->cabin_prices_count }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500 truncate max-w-[160px]">{{ $group->campaign_text ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $group->sort_order }}</td>
                        <td class="px-4 py-3">
                            @if($group->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">Aktif</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 text-xs font-medium">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.tours.price-groups.edit', ['tour' => $tour, 'price_group' => $group]) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Düzenle</a>
                            <form action="{{ route('admin.tours.price-groups.destroy', ['tour' => $tour, 'price_group' => $group]) }}"
                                  method="POST" class="inline ml-3"
                                  onsubmit="return confirm('Fiyat grubunu silmek istediğinize emin misiniz?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Sil</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $groups->links() }}</div>
    @endif
</div>
@endsection
