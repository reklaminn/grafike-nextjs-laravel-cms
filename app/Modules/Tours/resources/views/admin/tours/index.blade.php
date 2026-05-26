@extends('admin.layouts.app')
@section('title', 'Turlar')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Turlar</h1>
        <p class="text-sm text-gray-500">Cruise, paket ve günlük tur kataloğu.</p>
    </div>
    <a href="{{ route('admin.tours.create') }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 font-medium">
        <i class="fas fa-plus mr-1"></i> Yeni Tur
    </a>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>@endif

{{-- Filters --}}
<form method="GET" class="mb-4 bg-white border rounded-xl p-3 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Ara</label>
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Başlık / slug…"
               class="px-3 py-1.5 border border-gray-300 rounded text-sm w-56">
    </div>
    <div>
        <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Tip</label>
        <select name="type" class="px-3 py-1.5 border border-gray-300 rounded text-sm">
            <option value="">— Tümü —</option>
            @foreach($types as $t)
                <option value="{{ $t->value }}" {{ ($filters['type'] ?? '') === $t->value ? 'selected' : '' }}>
                    {{ $t->label() }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Durum</label>
        <select name="status" class="px-3 py-1.5 border border-gray-300 rounded text-sm">
            <option value="">— Tümü —</option>
            <option value="draft"     {{ ($filters['status'] ?? '') === 'draft'     ? 'selected' : '' }}>Taslak</option>
            <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>Yayında</option>
            <option value="archived"  {{ ($filters['status'] ?? '') === 'archived'  ? 'selected' : '' }}>Arşivlendi</option>
        </select>
    </div>
    <button type="submit" class="px-4 py-1.5 bg-gray-700 text-white rounded text-sm hover:bg-gray-800">Filtrele</button>
    @if(array_filter($filters))
        <a href="{{ route('admin.tours.index') }}" class="text-xs text-gray-500 hover:underline">Temizle</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    @if($tours->isEmpty())
        <div class="p-10 text-center text-sm text-gray-500">
            Henüz tur yok.
            <a href="{{ route('admin.tours.create') }}" class="text-indigo-600 hover:underline">İlk turu ekleyin</a>.
        </div>
    @else
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Tur</th>
                    <th class="px-4 py-3">Tip</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Tarih</th>
                    <th class="px-4 py-3">Fiyat</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 text-right">Eylem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($tours as $tour)
                    @php
                        $title = optional($tour->translations->firstWhere('language_id', $defaultLanguage?->id))->title
                            ?? optional($tour->translations->first())->title ?? '(başlıksız)';
                        $catName = optional($tour->category?->translations->firstWhere('language_id', $defaultLanguage?->id))->name
                            ?? optional($tour->category?->translations->first())->name;
                    @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $title }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ $tour->slug }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $tour->isCruise() ? 'bg-blue-100 text-blue-700'
                                   : ($tour->isPackage() ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                {{ $tour->type->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $catName ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $tour->dates_count }} tarih</td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ number_format($tour->base_price / 100, 2, ',', '.') }} {{ $tour->currency }}
                        </td>
                        <td class="px-4 py-3">
                            @php $statusColor = match($tour->status) {
                                'published' => 'bg-green-100 text-green-700',
                                'archived'  => 'bg-gray-100 text-gray-600',
                                default     => 'bg-yellow-100 text-yellow-700',
                            }; @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColor }}">
                                {{ ['draft'=>'Taslak','published'=>'Yayında','archived'=>'Arşivlendi'][$tour->status] ?? $tour->status }}
                            </span>
                            @if($tour->is_featured)
                                <span class="text-[10px] text-pink-600 ml-1"><i class="fas fa-star"></i></span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.tours.dates.index', $tour) }}" class="text-xs text-gray-600 hover:underline mr-3">
                                <i class="fas fa-calendar-alt"></i> Tarihler
                            </a>
                            <a href="{{ route('admin.tours.edit', $tour) }}" class="text-xs text-indigo-600 hover:underline mr-3">
                                <i class="fas fa-edit"></i> Düzenle
                            </a>
                            <form method="POST" action="{{ route('admin.tours.destroy', $tour) }}" class="inline">
                                @csrf @method('DELETE')
                                <button onclick="return confirm('Tur silinsin mi?')" class="text-xs text-red-600 hover:underline">
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

<div class="mt-4">{{ $tours->links() }}</div>
@endsection
