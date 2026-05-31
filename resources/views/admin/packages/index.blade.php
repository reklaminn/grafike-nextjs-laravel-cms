@extends('admin.layouts.app')

@section('title', 'Paketler')
@section('page-title', 'Abonelik Paketleri')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Tenant'lara atanan abonelik paketleri ve limitleri.</p>
        <a href="{{ route('admin.packages.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
            <i class="fas fa-plus"></i> Yeni Paket
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Paket</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kullanıcı</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Depolama</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">İstek/gün</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">AI Planı</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Modüller</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Tenant</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($packages as $pkg)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-800">{{ $pkg->label }}</span>
                            @if($pkg->is_default)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 font-medium">varsayılan</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 font-mono">{{ $pkg->key }}</div>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-700">
                        {{ $pkg->max_users === null ? '<span class="text-emerald-600">∞</span>' : $pkg->max_users }}
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-700">
                        @if($pkg->max_storage_mb === null)
                            <span class="text-emerald-600">∞</span>
                        @elseif($pkg->max_storage_mb >= 1024)
                            {{ round($pkg->max_storage_mb / 1024, 1) }} GB
                        @else
                            {{ $pkg->max_storage_mb }} MB
                        @endif
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-700">
                        {{ $pkg->max_requests_per_day === null ? '<span class="text-gray-400">—</span>' : number_format($pkg->max_requests_per_day) }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full bg-purple-50 text-purple-700">{{ $pkg->ai_plan }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">
                        {{ empty($pkg->modules) ? '—' : implode(', ', $pkg->modules) }}
                    </td>
                    <td class="px-5 py-3 text-right">
                        @php $count = $tenantCounts[$pkg->key] ?? 0; @endphp
                        @if($count > 0)
                            <a href="{{ route('admin.tenants.index') }}?package={{ $pkg->key }}"
                               class="text-sm font-semibold text-indigo-600 hover:underline">{{ $count }}</a>
                        @else
                            <span class="text-sm text-gray-400">0</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.packages.edit', $pkg) }}"
                           class="text-sm text-indigo-600 hover:text-indigo-800 mr-3">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        @if(($tenantCounts[$pkg->key] ?? 0) === 0)
                        <form method="POST" action="{{ route('admin.packages.destroy', $pkg) }}"
                              class="inline" onsubmit="return confirm('\'{{ $pkg->label }}\' paketini silmek istediğinizden emin misiniz?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        @else
                        <span class="text-sm text-gray-300 cursor-not-allowed" title="Tenant'lar var, silinemez">
                            <i class="fas fa-trash-alt"></i>
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center text-gray-400">
                        Henüz paket tanımlanmamış.
                        <a href="{{ route('admin.packages.create') }}" class="text-indigo-600 hover:underline">Yeni paket ekle</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-400 mt-3">
        Limit değerleri anında etkilidir (5 dakikalık önbellek). Tenant'lara atanan paket <strong>değişmez</strong> —
        sadece o paketin limitleri güncellenir.
    </p>
</div>
@endsection
