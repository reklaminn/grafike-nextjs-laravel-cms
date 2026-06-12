@extends('admin.layouts.app')

@section('title', 'Döviz Kurları')
@section('page-title', 'Döviz Kurları')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <form method="POST" action="{{ route('admin.currencies.fetch-rates', [], false) }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 text-green-700 text-sm font-medium rounded-lg hover:bg-green-100 transition-colors">
                    <i class="fas fa-sync-alt"></i> TCMB'den Güncelle
                </button>
            </form>
            <a href="{{ route('admin.currencies.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus"></i> Yeni Döviz
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kod</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ad</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Sembol</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Kur (TRY)</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Durum</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($currencies as $currency)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-bold text-gray-800">{{ $currency->code }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $currency->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $currency->symbol }}</td>
                            <td class="px-6 py-4 text-sm text-right font-mono text-gray-800">{{ number_format($currency->exchange_rate, 4) }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $currency->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $currency->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.currencies.edit', $currency) }}" class="p-1.5 text-gray-400 hover:text-indigo-600">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.currencies.destroy', $currency) }}"
                                          onsubmit="return confirm('Bu dövizi silmek istediğinize emin misiniz?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">Henüz döviz tanımlanmamış.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
