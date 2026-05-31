@extends('admin.layouts.app')

@section('title', 'AI Planları')
@section('page-title', 'AI Kullanım Planları')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Tenant'lara atanan AI kotaları — aylık istek, token ve maliyet limitleri.</p>
        <a href="{{ route('admin.ai-plans.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
            <i class="fas fa-plus"></i> Yeni Plan
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">İstek/ay</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Token/ay</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Maliyet/ay ($)</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Tenant</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($plans as $plan)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-800">{{ $plan->label }}</span>
                            @if($plan->is_default)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 font-medium">varsayılan</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 font-mono">{{ $plan->key }}</div>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-700">
                        @if($plan->monthly_requests === null)
                            <span class="text-emerald-600">∞</span>
                        @else
                            {{ number_format($plan->monthly_requests) }}
                        @endif
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-700">
                        @if($plan->monthly_tokens === null)
                            <span class="text-emerald-600">∞</span>
                        @elseif($plan->monthly_tokens >= 1000000)
                            {{ number_format($plan->monthly_tokens / 1000000, 1) }}M
                        @else
                            {{ number_format($plan->monthly_tokens / 1000, 0) }}K
                        @endif
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-700">
                        @if($plan->monthly_cost_usd === null)
                            <span class="text-emerald-600">∞</span>
                        @else
                            ${{ number_format($plan->monthly_cost_usd, 2) }}
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        @php $count = $tenantCounts[$plan->key] ?? 0; @endphp
                        @if($count > 0)
                            <span class="text-sm font-semibold text-indigo-600">{{ $count }}</span>
                        @else
                            <span class="text-sm text-gray-400">0</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.ai-plans.edit', $plan) }}"
                           class="text-sm text-indigo-600 hover:text-indigo-800 mr-3">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        @if(($tenantCounts[$plan->key] ?? 0) === 0)
                        <form method="POST" action="{{ route('admin.ai-plans.destroy', $plan) }}"
                              class="inline" onsubmit="return confirm('\'{{ $plan->label }}\' planını silmek istediğinizden emin misiniz?')">
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
                    <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                        Henüz AI planı tanımlanmamış.
                        <a href="{{ route('admin.ai-plans.create') }}" class="text-indigo-600 hover:underline">Yeni plan ekle</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-400 mt-3">
        Limitler anında etkilidir (5 dakikalık önbellek). Tenant AI planı değiştirilmezken yalnızca limitler güncellenir.
    </p>
</div>
@endsection
